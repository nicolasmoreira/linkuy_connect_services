<?php

namespace App\Controller;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    #[Route('/api/notifications', name: 'api_notifications_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $notifications = $em->getRepository(Notification::class)->findBy(['recipient' => $user], ['createdAt' => 'DESC']);

        return $this->json(array_map(static fn ($notification) => [
            'id' => $notification->getId(),
            'message' => $notification->getMessage(),
            'sent' => $notification->isSent(),
            'created_at' => $notification->getCreatedAt()->format('Y-m-d H:i:s')
        ], $notifications));
    }

    #[Route('/api/notifications/{id}', name: 'api_notifications_update', methods: ['PATCH'])]
    public function update(int $id, EntityManagerInterface $em): JsonResponse
    {
        $notification = $em->getRepository(Notification::class)->find($id);

        if (!$notification) {
            return $this->json(['message' => 'Notification not found'], Response::HTTP_NOT_FOUND);
        }

        $notification->markAsSent();
        $em->flush();

        return $this->json(['message' => 'Notification status updated']);
    }
}
