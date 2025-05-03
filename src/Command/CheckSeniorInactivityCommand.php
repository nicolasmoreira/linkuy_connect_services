<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Notification;
use App\Entity\Settings;
use App\Entity\User;
use App\Enum\UserType;
use App\Repository\ActivityLogRepository;
use App\Service\PushNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:check-senior-inactivity',
    description: 'Check for senior user inactivity and send notifications to caregivers',
)]
final class CheckSeniorInactivityCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly PushNotificationService $pushNotificationService,
        private readonly ActivityLogRepository $activityLogRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $inactiveUsers = $this->activityLogRepository->findInactiveUsers(Settings::DEFAULT_INACTIVITY_THRESHOLD);

            $output->writeln(\sprintf('Found %d inactive senior users', \count($inactiveUsers)));

            foreach ($inactiveUsers as $data) {
                try {
                    $this->processInactiveUser($data, $output);
                } catch (\Exception $e) {
                    $this->logger->error('Error processing inactive user', [
                        'user_id' => $data['user_id'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error('Error in inactivity check command', ['error' => $e->getMessage()]);

            return Command::FAILURE;
        }
    }

    private function processInactiveUser(array $data, OutputInterface $output): void
    {
        $output->writeln(
            \sprintf(
                'User %d has been inactive for %d minutes (threshold: %d)',
                $data['user_id'],
                (int) $data['inactivity_minutes'],
                $data['inactivity_threshold'],
            ),
        );

        // Get caregivers for this family
        $caregivers = $this->entityManager->getRepository(User::class)->findBy([
            'family' => $data['family_id'],
            'active' => true,
            'userType' => UserType::CAREGIVER,
        ]);

        $this->handleInactivityAlert($caregivers, (int) $data['inactivity_minutes']);
        $this->entityManager->flush();
    }

    private function handleInactivityAlert(?array $users, int $inactivityMinutes): void
    {
        $inactivityDuration = $inactivityMinutes >= 60
            ? FormatterHelper::formatTime($inactivityMinutes * 60) // Convert to seconds and format
            : \sprintf('%d minutos', $inactivityMinutes);

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
                    \sprintf('El usuario ha estado inactivo por %s.', $inactivityDuration),
                );

                $notification = new Notification(
                    $user,
                    \sprintf('El usuario ha estado inactivo por %s.', $inactivityDuration),
                    true,
                );
                $this->entityManager->persist($notification);
            } catch (\Exception $e) {
                $this->logger->error('Error sending push notification', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->getId(),
                ]);
            }
        }
    }
}
