<?php

declare(strict_types=1);

namespace App\Trait;

use Symfony\Component\HttpFoundation\JsonResponse;

trait ApiResponseTrait
{
    private function success(array $data = [], int $status = 200): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'data' => $data,
        ], $status);
    }

    private function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        return $this->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    private function notFound(string $message = 'Recurso no encontrado'): JsonResponse
    {
        return $this->error($message, 404);
    }

    private function unauthorized(string $message = 'No autorizado'): JsonResponse
    {
        return $this->error($message, 401);
    }

    private function forbidden(string $message = 'Acceso denegado'): JsonResponse
    {
        return $this->error($message, 403);
    }
}
