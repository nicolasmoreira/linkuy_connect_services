<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Notifier\Bridge\Expo\ExpoOptions;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\Message\PushMessage;
use Symfony\Component\Notifier\TexterInterface;

final class PushNotificationService
{
    public function __construct(
        private readonly TexterInterface $texter,
    ) {}

    /**
     * @throws TransportExceptionInterface
     */
    public function sendNotification(string $deviceToken, string $title, string $content): void
    {
        $options = new ExpoOptions($deviceToken, ['priority' => 'high', 'badge' => 1, 'sound' => 'default']);
        $chatMessage = (new PushMessage($title, $content, $options))->transport('expo');
        $this->texter->send($chatMessage);
    }
}
