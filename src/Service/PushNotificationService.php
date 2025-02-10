<?php

namespace App\Service;

use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Bridge\Expo\ExpoOptions;

class PushNotificationService
{
    private NotifierInterface $notifier;

    public function __construct(NotifierInterface $notifier)
    {
        $this->notifier = $notifier;
    }

    public function sendNotification(string $expoToken, string $title, string $message): void
    {
        // Crear la notificación indicando el canal 'expo'
        $notification = new Notification($title, ['expo']);

        // Establecer el contenido de la notificación
        $notification->content($message);

        // Configurar las opciones para el transport de Expo.
        // Estas opciones se definen en el array que se pasa al constructor de ExpoOptions.
        $options = [
            'title' => $title,
            'body' => $message,
            'priority' => 'high',
        ];
        $expoOptions = new ExpoOptions($expoToken, $options);

        // Asociar las opciones de Expo a la notificación
        $notification->options($expoOptions);

        // Enviar la notificación
        $this->notifier->send($notification);
    }
}
