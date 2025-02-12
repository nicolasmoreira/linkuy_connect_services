<?php

namespace App\Controller;

use App\Entity\Settings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingsController extends AbstractController
{
    #[Route('/api/settings', name: 'get_settings', methods: ['GET'])]
    public function getSettings(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $settings = $em->getRepository(Settings::class)->findOneBy(['family' => $user->getFamily()]);

        if (!$settings) {
            return $this->json(['message' => 'Settings not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'inactivity_threshold' => $settings->getInactivityThreshold(),
            'do_not_disturb' => $settings->isDoNotDisturb(),
        ]);
    }

    #[Route('/api/settings', name: 'update_settings', methods: ['PUT'])]
    public function updateSettings(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $settings = $em->getRepository(Settings::class)->findOneBy(['family' => $user->getFamily()]);

        if (!$settings) {
            return $this->json(['message' => 'Settings not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['inactivity_threshold'])) {
            $settings->setInactivityThreshold((int)$data['inactivity_threshold']);
        }
        if (isset($data['do_not_disturb'])) {
            $settings->setDoNotDisturb((bool)$data['do_not_disturb']);
        }

        $em->flush();

        return $this->json(['message' => 'Settings updated successfully']);
    }
}
