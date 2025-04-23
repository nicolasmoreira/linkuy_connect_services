<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Trait\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[OA\Tag(name: 'Authentication')]
final class AuthenticationController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * User login.
     */
    #[OA\RequestBody(
        description: 'User credentials',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'cuidador@unir.com'),
                new OA\Property(property: 'password', type: 'string', example: 'password123'),
            ],
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'Login successful',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'Authentication successful'),
                new OA\Property(
                    property: 'token',
                    type: 'string',
                    example: 'XXXXXXXXXXXXXXXXXXXXXXXXXXXX',
                ),
                new OA\Property(
                    property: 'user',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'email', type: 'string', example: 'cuidador@unir.com'),
                        new OA\Property(
                            property: 'role',
                            type: 'array',
                            items: new OA\Items(type: 'string', example: 'ROLE_CAREGIVER'),
                        ),
                    ],
                    type: 'object',
                ),
            ],
        ),
    )]
    #[OA\Response(
        response: 401,
        description: 'Invalid credentials',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'Invalid credentials'),
            ],
        ),
    )]
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Login is handled by LexikJWTAuthenticationBundle
        return $this->json(['message' => 'Inicio de sesión exitoso']);
    }

    /**
     * Check if the JWT token is valid.
     */
    #[OA\Response(
        response: 200,
        description: 'Token is valid',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Token válido'),
                    ],
                    type: 'object',
                ),
            ],
        ),
    )]
    #[OA\Response(
        response: 401,
        description: 'Token is invalid',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'Token inválido'),
            ],
        ),
    )]
    #[Route('/api/check-token', name: 'check_token', methods: ['GET'])]
    public function checkToken(): JsonResponse
    {
        return $this->success(['message' => 'Token válido']);
    }

    #[OA\RequestBody(
        description: 'Device token',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'device_token', type: 'string', example: 'device_token_123'),
            ],
        ),
    )]
    #[OA\Response(
        response: 200,
        description: 'Device token added successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'success'),
                new OA\Property(property: 'message', type: 'string', example: 'Device token added successfully'),
            ],
        ),
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid device token',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'error'),
                new OA\Property(property: 'message', type: 'string', example: 'Invalid device token'),
            ],
        ),
    )]
    #[Route('/api/device-token', name: 'add_device_token', methods: ['POST'])]
    public function addDeviceToken(#[CurrentUser] User $user, Request $request): JsonResponse
    {
        $deviceToken = $request->request->get('device_token');

        if (empty($deviceToken)) {
            return $this->error('Invalid device token', 400);
        }

        $user->setDeviceToken($deviceToken);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->success(['message' => 'Device token added successfully']);
    }
}
