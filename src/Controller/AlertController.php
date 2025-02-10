<?php

namespace App\Controller;

use App\Entity\Alert;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlertController extends AbstractController
{
    #[Route('/api/alerts', name: 'api_alerts_list', methods: ['GET'])]
    public function listAlerts(EntityManagerInterface $em): JsonResponse
    {
        $alerts = $em->getRepository(Alert::class)->findBy([], ['createdAt' => 'DESC']);

        return new JsonResponse(array_map(static fn($alert) => [
            'id' => $alert->getId(),
            'user' => $alert->getUser()->getEmail(),
            'type' => $alert->getAlertType()->value,
            'sent' => $alert->isSent(),
            'created_at' => $alert->getCreatedAt()->format('Y-m-d H:i:s')
        ], $alerts));
    }

    #[Route('/api/alerts/resolve/{id}', name: 'api_alerts_resolve', methods: ['POST'])]
    public function resolveAlert(int $id, EntityManagerInterface $em): JsonResponse
    {
        $alert = $em->getRepository(Alert::class)->find($id);

        if (!$alert) {
            return new JsonResponse(['message' => 'Alert not found'], Response::HTTP_NOT_FOUND);
        }

        $alert->markAsSent();
        $em->flush();

        return new JsonResponse(['message' => 'Alert resolved successfully']);
    }

    #[Route('/api/alerts/{id}', name: 'api_alerts_delete', methods: ['DELETE'])]
    public function deleteAlert(int $id, EntityManagerInterface $em): JsonResponse
    {
        $alert = $em->getRepository(Alert::class)->find($id);

        if (!$alert) {
            return new JsonResponse(['message' => 'Alert not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($alert);
        $em->flush();

        return new JsonResponse(['message' => 'Alert deleted successfully']);
    }
}
