<?php

declare(strict_types=1);

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        $user = $token->getUser();
        $jwtToken = $this->jwtManager->create($user);

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Authentication successful',
            'token' => $jwtToken,
            'user' => [
                'id' => $user?->getId(),
                'email' => $user?->getUserIdentifier(),
                'role' => $user?->getRoles(),
            ],
        ]);
    }
}
