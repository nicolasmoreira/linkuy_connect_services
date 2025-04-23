<?php

declare(strict_types=1);

namespace App\Controller;

use App\Trait\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

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
}
