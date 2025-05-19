<?php

namespace App\Controller;

use App\Service\JWTService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class OIDCController extends AbstractController
{
    public function __construct(private JWTService $jwtService) {}

    #[Route('/login', name: 'oidc_login')]
    public function index(AuthenticationUtils $authUtils): Response
    {
        $error = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('authentication/oidc/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/authorize', name: 'oidc_authorize')]
    public function authorize(Request $request): RedirectResponse
    {
        // Extract and validate request parameters
        $clientId = $request->query->get('client_id');
        $redirectUri = $request->query->get('redirect_uri');
        $responseType = $request->query->get('response_type');
        $scope = $request->query->get('scope');
        $state = $request->query->get('state');

        // TODO: Validate client_id, redirect_uri, response_type, and scope

        // Check if user is authenticated
        if (!$this->getUser()) {
            // Redirect to login page
            return $this->redirectToRoute('login_form', [
                'redirect' => $request->getUri(),
            ]);
        }

        // TODO: Prompt user for consent if necessary

        // Generate authorization code
        $authorizationCode = bin2hex(random_bytes(32));

        // TODO: Store the authorization code along with client_id, user_id, redirect_uri, and expiration

        // Redirect back to client's redirect_uri with code and state
        $queryParams = http_build_query([
            'code' => $authorizationCode,
            'state' => $state,
        ]);

        return new RedirectResponse("{$redirectUri}?{$queryParams}");
    }

    #[Route('/token', name: 'oidc_token', methods: ['POST'])]
    public function token(Request $request): JsonResponse
    {
        // Extract request parameters
        $grantType = $request->request->get('grant_type');
        $code = $request->request->get('code');
        $redirectUri = $request->request->get('redirect_uri');
        $clientId = $request->request->get('client_id');
        $clientSecret = $request->request->get('client_secret');

        // TODO: Validate grant_type, authorization code, client credentials, and redirect_uri

        // TODO: Retrieve the stored authorization code and associated data

        // TODO: Validate that the authorization code has not expired and matches the client_id and redirect_uri

        // Generate JWT access token
        $token = $this->jwtService->createToken($this->getUser());

        return new JsonResponse([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]);
    }

    #[Route('/.well-known/openid-configuration', name: 'oidc_configuration')]
    public function configuration(Request $request): JsonResponse
    {
        $issuer = $request->getSchemeAndHttpHost(); // e.g. https://sso.localhost

        $response = new JsonResponse([
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer . '/authorize',
            'token_endpoint' => $issuer . '/token',
            'userinfo_endpoint' => $issuer . '/userinfo',
            'jwks_uri' => $issuer . '/.well-known/jwks.json',
            'response_types_supported' => ['code'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'scopes_supported' => ['openid', 'profile', 'email'],
            'token_endpoint_auth_methods_supported' => ['client_secret_post'],
        ]);

        return $response;
    }
}
