<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Settings;
use App\Entity\User;
use App\Trait\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Tag(name: 'Settings')]
final class SettingsController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    /**
     * Get user settings.
     */
    #[OA\Response(
        response: 200,
        description: 'Settings retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'inactivity_threshold',
                            type: 'integer',
                            example: 30,
                            description: 'Umbral de inactividad en minutos'
                        ),
                        new OA\Property(
                            property: 'do_not_disturb',
                            type: 'boolean',
                            example: false,
                            description: 'Activar/desactivar modo no molestar'
                        ),
                        new OA\Property(
                            property: 'do_not_disturb_start_time',
                            type: 'string',
                            format: 'time',
                            example: '22:00:00',
                            nullable: true,
                            description: 'Hora de inicio para modo no molestar (formato HH:MM:SS)'
                        ),
                        new OA\Property(
                            property: 'do_not_disturb_end_time',
                            type: 'string',
                            format: 'time',
                            example: '07:00:00',
                            nullable: true,
                            description: 'Hora de fin para modo no molestar (formato HH:MM:SS)'
                        ),
                    ],
                ),
            ],
        ),
    )]
    #[Route('/api/settings', name: 'get_settings', methods: ['GET'])]
    public function getSettings(#[CurrentUser] User $user, EntityManagerInterface $em)
    {
        $settings = $em->getRepository(Settings::class)->findOneBy(['family' => $user->getFamily()]);

        if (!$settings) {
            return $this->notFound('Configuración no encontrada');
        }

        return $this->success([
            'inactivity_threshold' => $settings->getInactivityThreshold(),
            'do_not_disturb' => $settings->isDoNotDisturb(),
            'do_not_disturb_start_time' => $settings->getDoNotDisturbStartTime()?->format('H:i:s'),
            'do_not_disturb_end_time' => $settings->getDoNotDisturbEndTime()?->format('H:i:s'),
        ]);
    }

    /**
     * Update user settings.
     */
    #[OA\RequestBody(
        description: 'Settings to update',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'inactivity_threshold',
                    type: 'integer',
                    example: 30,
                    description: 'Umbral de inactividad en minutos (1-1440)',
                ),
                new OA\Property(
                    property: 'do_not_disturb',
                    type: 'boolean',
                    example: false,
                    description: 'Activar/desactivar modo no molestar',
                ),
                new OA\Property(
                    property: 'do_not_disturb_start_time',
                    type: 'string',
                    format: 'time',
                    example: '22:00:00',
                    nullable: true,
                    description: 'Hora de inicio para modo no molestar (formato HH:MM:SS)',
                ),
                new OA\Property(
                    property: 'do_not_disturb_end_time',
                    type: 'string',
                    format: 'time',
                    example: '07:00:00',
                    nullable: true,
                    description: 'Hora de fin para modo no molestar (formato HH:MM:SS)',
                ),
            ],
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'Settings updated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Configuración actualizada exitosamente'
                        ),
                    ],
                ),
            ],
        ),
    )]
    #[Route('/api/settings', name: 'update_settings', methods: ['PUT'])]
    public function updateSettings(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em)
    {
        $settings = $em->getRepository(Settings::class)->findOneBy(['family' => $user->getFamily()]);

        if (!$settings) {
            return $this->notFound('Configuración no encontrada');
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['inactivity_threshold'])) {
            $threshold = (int) $data['inactivity_threshold'];
            if ($threshold < 1 || $threshold > 1440) {
                return $this->error('El umbral de inactividad debe estar entre 1 y 1440 minutos');
            }
            $settings->setInactivityThreshold($threshold);
        }

        if (isset($data['do_not_disturb'])) {
            $settings->setDoNotDisturb((bool) $data['do_not_disturb']);
        }

        if (\array_key_exists('do_not_disturb_start_time', $data)) {
            if ($data['do_not_disturb_start_time'] === null) {
                $settings->setDoNotDisturbStartTime(null);
            } else {
                try {
                    $time = \DateTime::createFromFormat('H:i:s', $data['do_not_disturb_start_time']);
                    if ($time === false) {
                        return $this->error('Formato de hora inválido. Use HH:MM:SS');
                    }
                    $settings->setDoNotDisturbStartTime($time);
                } catch (\Exception $e) {
                    return $this->error('Formato de hora inválido');
                }
            }
        }

        if (\array_key_exists('do_not_disturb_end_time', $data)) {
            if ($data['do_not_disturb_end_time'] === null) {
                $settings->setDoNotDisturbEndTime(null);
            } else {
                try {
                    $time = \DateTime::createFromFormat('H:i:s', $data['do_not_disturb_end_time']);
                    if ($time === false) {
                        return $this->error('Formato de hora inválido. Use HH:MM:SS');
                    }
                    $settings->setDoNotDisturbEndTime($time);
                } catch (\Exception $e) {
                    return $this->error('Formato de hora inválido');
                }
            }
        }

        $em->flush();

        return $this->success(['message' => 'Configuración actualizada exitosamente']);
    }
}
