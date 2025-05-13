<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/api/profile', name: 'app_profile')]
    public function profile(Request $request): Response
    {
        $user = $this->getUser(); // should be auto-injected by Symfony if token is valid

        if ($user) {
            $claims = $request->attributes->get('jwt_claims');

            $headers = [
                'X-JWT-Issuer' => $claims->get('iss'),
                'X-JWT-Audience' => implode(',', $claims->get('aud'))
            ];

            $data = [
                'email' => $user->getUserIdentifier(),
                'message' => 'You are HERE',
            ];

            return $this->json($data, 200, $headers);
        }

        return $this->json(['message' => 'nothing to see here']);
    }
}
