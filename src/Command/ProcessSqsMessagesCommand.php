<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Notification;
use App\Entity\User;
use App\Enum\ActivityType;
use App\Enum\UserType;
use App\Service\PushNotificationService;
use Aws\Sqs\SqsClient;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:process-sqs-messages',
    description: 'Process messages from SQS queue',
)]
final class ProcessSqsMessagesCommand extends Command
{
    private const MAX_MESSAGES = 10;
    private const WAIT_TIME = 20;

    public function __construct(
        private readonly SqsClient $sqsClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly PushNotificationService $pushNotificationService,
        private readonly string $queueUrl,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'max-messages',
            'm',
            InputOption::VALUE_OPTIONAL,
            'Maximum number of messages to process in one run',
            self::MAX_MESSAGES,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $maxMessages = (int) $input->getOption('max-messages');

        try {
            $result = $this->sqsClient->receiveMessage([
                'QueueUrl' => $this->queueUrl,
                'MaxNumberOfMessages' => $maxMessages,
                'WaitTimeSeconds' => self::WAIT_TIME,
            ]);

            $messages = $result->get('Messages');
            if (empty($messages)) {
                $output->writeln('No messages to process');

                return Command::SUCCESS;
            }

            $output->writeln(\sprintf('Processing %d messages', \count($messages)));

            foreach ($messages as $message) {
                try {
                    $data = json_decode($message['Body'], true, 512, \JSON_THROW_ON_ERROR);

                    if (isset($data['Type']) && $data['Type'] === 'SubscriptionConfirmation') {
                        $this->logger->info('Processing SQS subscription confirmation');
                        file_get_contents($data['SubscribeURL']);
                        $this->deleteMessage($message['ReceiptHandle']);
                        continue;
                    }

                    if (!isset($data['user_id'], $data['type'])) {
                        $this->logger->error('Invalid message format in SQS payload', ['message' => $data]);
                        $this->deleteMessage($message['ReceiptHandle']);
                        continue;
                    }

                    $user = $this->entityManager->getRepository(User::class)->find($data['user_id']);
                    if (!$user) {
                        $this->logger->error('User not found', ['user_id' => $data['user_id']]);
                        $this->deleteMessage($message['ReceiptHandle']);
                        continue;
                    }

                    $activityType = ActivityType::tryFrom($data['type']);
                    if (!$activityType) {
                        $this->logger->error('Invalid activity type', ['type' => $data['type']]);
                        $this->deleteMessage($message['ReceiptHandle']);
                        continue;
                    }

                    $caregivers = $this->entityManager->getRepository(User::class)->findBy(
                        ['family' => $user->getFamily()->getId(), 'active' => true, 'userType' => UserType::CAREGIVER],
                    );

                    match ($activityType) {
                        ActivityType::FALL_DETECTED => $this->handleFallDetected($caregivers),
                        ActivityType::INACTIVITY_ALERT => $this->handleInactivityAlert($caregivers),
                        ActivityType::EMERGENCY_BUTTON_PRESSED => $this->handleEmergencyButton($caregivers),
                        default => $this->logger->warning("Unhandled activity type: $activityType->value"),
                    };

                    $this->deleteMessage($message['ReceiptHandle']);
                } catch (\JsonException $e) {
                    $this->logger->error('Invalid JSON format in SQS payload', ['error' => $e->getMessage()]);
                    $this->deleteMessage($message['ReceiptHandle']);
                } catch (\Exception $e) {
                    $this->logger->error('Error processing SQS message', ['error' => $e->getMessage()]);
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error('Error receiving messages from SQS', ['error' => $e->getMessage()]);

            return Command::FAILURE;
        }
    }

    private function deleteMessage(string $receiptHandle): void
    {
        try {
            $this->sqsClient->deleteMessage([
                'QueueUrl' => $this->queueUrl,
                'ReceiptHandle' => $receiptHandle,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error deleting message from SQS', ['error' => $e->getMessage()]);
        }
    }

    private function handleFallDetected(?array $users): void
    {
        foreach ($users as $user) {
            try {
                if (empty($user->getDeviceToken())) {
                    $this->logger->info('User has no device token', ['user_id' => $user->getId()]);
                    continue;
                }
                $this->logger->info('Processing fall detection', ['user_id' => $user->getId()]);
                $this->pushNotificationService->sendNotification(
                    $user->getDeviceToken(),
                    '🚨Alerta: Caída detectada',
                    'Se ha detectado una caída. Por favor, verifique el estado del usuario.',
                );

                $notification = new Notification($user, 'Se ha detectado una caída. Por favor, verifique el estado del usuario.', true);
                $this->entityManager->persist($notification);
                $this->entityManager->flush();
            } catch (\Exception $e) {
                $this->logger->error('Error sending push notification', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->getId(),
                ]);
            }
        }
    }

    private function handleInactivityAlert(?array $users): void
    {
        foreach ($users as $user) {
            try {
                if (empty($user->getDeviceToken())) {
                    $this->logger->info('User has no device token', ['user_id' => $user->getId()]);
                    continue;
                }
                $this->logger->info('Processing inactivity alert', ['user_id' => $user->getId()]);
                $this->pushNotificationService->sendNotification(
                    $user->getDeviceToken(),
                    '⚠️ Alerta: Inactividad prolongada',
                    'El usuario ha estado inactivo por un período prolongado.',
                );

                $notification = new Notification($user, 'El usuario ha estado inactivo por un período prolongado', true);
                $this->entityManager->persist($notification);
                $this->entityManager->flush();
            } catch (\Exception $e) {
                $this->logger->error('Error sending push notification', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->getId(),
                ]);
            }
        }
    }

    private function handleEmergencyButton(?array $users): void
    {
        foreach ($users as $user) {
            try {
                if (empty($user->getDeviceToken())) {
                    $this->logger->info('User has no device token', ['user_id' => $user->getId()]);
                    continue;
                }
                $this->logger->info('Processing emergency button press', ['user_id' => $user->getId()]);
                $this->pushNotificationService->sendNotification(
                    $user->getDeviceToken(),
                    '🚨 Alerta: Botón de emergencia activado',
                    'Se ha presionado el botón de emergencia. Se requiere atención inmediata.',
                );

                $notification = new Notification($user, 'Se ha presionado el botón de emergencia. Se requiere atención inmediata.', true);
                $this->entityManager->persist($notification);
                $this->entityManager->flush();
            } catch (\Exception $e) {
                $this->logger->error('Error sending push notification', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->getId(),
                ]);
            }
        }
    }
}
