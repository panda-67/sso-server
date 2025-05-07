<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class AuthController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function index( #[CurrentUser] ?User $user, JWTService $jwtService): JsonResponse 
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $jwtService->createToken($user);

        return $this->json([
            'user'  => $user->getUserIdentifier(),
            'token' => $token,
        ]);
    }

    #[Route('/api/profile', name: 'api_profile')]
    public function profile(): Response
    {
        $user = $this->getUser(); // should be auto-injected by Symfony if token is valid

        if ($user) {
            return $this->json([
                'email' => $user->getUserIdentifier(),
                'message' => 'You are HERE',
            ]);
        }

        return $this->json(['message' => 'nothing to see here']);
    }
}
