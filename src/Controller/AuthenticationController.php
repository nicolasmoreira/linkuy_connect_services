<?php

namespace App\Controller;

use App\Entity\Family;
use App\Entity\User;
use App\Entity\UserType;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthenticationController extends AbstractController
{
    #[Route('/api/login', name: 'api_login')]
    public function index(): JsonResponse
    {
        return $this->json('Login endpoint');
    }

    /**
     * @throws JsonException
     */
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (!isset($data['email'], $data['password'], $data['family'], $data['role'])) {
            return $this->json(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $family = $em->getRepository(Family::class)->find($data['family']);
        if (!$family) {
            return $this->json(['message' => 'Family not found'], Response::HTTP_NOT_FOUND);
        }

        $userType = UserType::tryFrom($data['role']);
        if (!$userType) {
            return $this->json(['message' => 'Invalid role'], Response::HTTP_BAD_REQUEST);
        }

        $deviceToken = $data['device_token'] ?? null;

        $user = new User($data['email'], '', $userType, $family);
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);
        $user->setDeviceToken($deviceToken);

        $em->persist($user);
        $em->flush();

        return $this->json(['message' => 'User registered successfully'], Response::HTTP_CREATED);
    }
}
