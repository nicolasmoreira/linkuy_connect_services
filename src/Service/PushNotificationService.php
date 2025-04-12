<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

final class PushNotificationService
{
    public function __construct(
        private readonly NotifierInterface $notifier,
        private readonly LoggerInterface $logger,
    ) {}

    public function sendNotification(string $expoToken, string $title, string $message): void
    {
        try {
            $notification = new Notification($title, ['expo']);
            $notification->content($message);
            $notification->importance(Notification::IMPORTANCE_HIGH);

            $recipient = new Recipient($expoToken);
            $this->notifier->send($notification, $recipient);
            $this->logger->info("Push notification sent successfully to token: {$expoToken}");
        } catch (\Exception $e) {
            $this->logger->error("Failed to send push notification: {$e->getMessage()}");

            throw $e;
        }
    }
}
