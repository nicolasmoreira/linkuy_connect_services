<?php

namespace App\Controller;

use App\Entity\ActivityLog;
use App\Entity\ActivityType;
use App\Entity\User;
use App\Service\PushNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SqsWebhookController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private PushNotificationService $pushNotificationService;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, PushNotificationService $pushNotificationService)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->pushNotificationService = $pushNotificationService;
    }

    #[Route('/worker/sqs', name: 'worker_sqs', methods: ['POST'])]
    public function handleSqsMessage(Request $request): Response
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (isset($data['Type']) && $data['Type'] === 'SubscriptionConfirmation') {
            file_get_contents($data['SubscribeURL']);
            return new JsonResponse(['message' => 'Subscription confirmed'], Response::HTTP_OK);
        }

        if (!isset($data['Message'])) {
            return new JsonResponse(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $message = json_decode($data['Message'], true, 512, JSON_THROW_ON_ERROR);
        if (!isset($message['activity_type'], $message['user_id'])) {
            return new JsonResponse(['message' => 'Invalid message format'], Response::HTTP_BAD_REQUEST);
        }

        $activityType = ActivityType::tryFrom($message['activity_type']);
        if ($activityType === null) {
            return new JsonResponse(['message' => 'Invalid activity_type'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->entityManager->getRepository(User::class)->find($message['user_id']);
        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $activityLog = new ActivityLog($user, $activityType, $message);
        $this->entityManager->persist($activityLog);
        $this->entityManager->flush();

        match ($activityType) {
            ActivityType::FALL_DETECTED => $this->sendPushNotification($user, '⚠️ Alerta: Caída detectada', 'Se ha detectado una caída.'),
            ActivityType::EMERGENCY_BUTTON_PRESSED => $this->sendPushNotification($user, '🚨 Botón de emergencia activado', 'El usuario ha presionado el botón de emergencia.'),
            default => $this->logger->warning("Unhandled activity type: {$activityType->value}")
        };

        return new JsonResponse(['message' => 'Message processed'], Response::HTTP_OK);
    }

    private function sendPushNotification(User $user, string $title, string $body): void
    {
        $deviceToken = $user->getDeviceToken();
        if (!$deviceToken) {
            $this->logger->warning("No device token found for user {$user->getId()}");
            return;
        }

        $this->pushNotificationService->sendNotification($deviceToken, $title, $body);
    }
}
