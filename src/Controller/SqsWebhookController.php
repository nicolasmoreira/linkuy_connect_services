<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Enum\ActivityType;
use App\Enum\UserType;
use App\Service\PushNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class SqsWebhookController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly PushNotificationService $pushNotificationService,
    ) {}

    /**
     * Process SQS message.
     */
    #[Route('/worker/sqs', name: 'worker_sqs', methods: ['POST'])]
    public function handleSqsMessage(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

            if (isset($data['Type']) && $data['Type'] === 'SubscriptionConfirmation') {
                $this->logger->info('Processing SQS subscription confirmation');
                file_get_contents($data['SubscribeURL']);

                return $this->json(['message' => 'Subscription confirmed']);
            }

            if (!isset($data['Message'])) {
                $this->logger->error('Missing Message field in SQS payload');

                return $this->json(['message' => 'Missing Message field'], Response::HTTP_BAD_REQUEST);
            }

            $message = json_decode($data['Message'], true, 512, \JSON_THROW_ON_ERROR);

            if (!isset($message['user_id'], $message['type'])) {
                $this->logger->error('Invalid message format in SQS payload', ['message' => $message]);

                return $this->json(['message' => 'Invalid message format'], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->entityManager->getRepository(User::class)->find($message['user_id']);
            if (!$user) {
                $this->logger->error('User not found', ['user_id' => $message['user_id']]);

                return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
            }

            $activityType = ActivityType::tryFrom($message['type']);
            if (!$activityType) {
                $this->logger->error('Invalid activity type', ['type' => $message['type']]);

                return $this->json(['message' => 'Invalid activity type'], Response::HTTP_BAD_REQUEST);
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

            return $this->json(['message' => 'Message processed']);
        } catch (\JsonException $e) {
            $this->logger->error('Invalid JSON format in SQS payload', ['error' => $e->getMessage()]);

            return $this->json(['message' => 'Invalid JSON format'], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error('Error processing SQS message', ['error' => $e->getMessage()]);

            return $this->json(['message' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function handleFallDetected(?array $users): void
    {
        foreach ($users as $user) {
            try {
                if (empty($user->getDeviceToken())) {
                    $this->logger->info('User has no device token', ['user_id' => $user->getId()]);

                    return;
                }
                $this->logger->info('Processing fall detection', ['user_id' => $user->getId()]);
                $this->pushNotificationService->sendNotification(
                    $user->getDeviceToken(),
                    'Alerta: Caída detectada',
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

                    return;
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

                    return;
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
