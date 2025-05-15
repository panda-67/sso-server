<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginForm;
use App\Service\JWTService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/auth')]
final class AuthController extends BaseController
{
    #[Route('/login', name: 'app_show_login', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $title = 'Login';
        $form = $this->createForm(LoginForm::class);

        $html = $this->renderView('authentication/login.html.twig', [
            'loginForm' => $form->createView(),
        ]);

        if ($request->isXmlHttpRequest() || $request->getPreferredFormat() === 'json') {
            return $this->json(['title' => $title, 'html' => $html]);
        }

        return $this->renderApp($html, $title);
    }

    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function login(#[CurrentUser] ?User $user, JWTService $jwtService): JsonResponse
    {
        if (null === $user) {
            return $this->json(
                ['message' => 'missing credentials'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $token = $jwtService->createToken($user);
        $navbar = $this->renderView('pages/navbar.html.twig');

        return $this->json([
            'redirect_to' => $this->generateUrl('app_profile'),
            'navbar' => $navbar,
            'token' => $token,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
