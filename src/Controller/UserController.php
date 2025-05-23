<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends BaseController
{
    #[Route('/api/profile', name: 'app_profile')]
    public function profile(Request $request): Response
    {
        $title = 'Profile';
        $user = $this->getUser(); // should be auto-injected by Symfony if token is valid

        if ($user) {
            $claims = $request->attributes->get('jwt_claims');

            $headers = [
                'X-JWT-Issuer' => $claims->get('iss'),
                'X-JWT-Audience' => implode(',', $claims->get('aud'))
            ];

            $html = $this->renderView('pages/profile.html.twig', [
                'user' => $user
            ]);

            if ($request->isXmlHttpRequest() || $request->getPreferredFormat() === 'json') {
                return $this->json(['title' => $title, 'html' => $html], 200, $headers);
            }

            return $this->renderApp($html, $title);
        }

        return $this->json(['message' => 'nothing to see here']);
    }
}
