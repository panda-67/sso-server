<?php

namespace App\Security;

use App\Service\JWTService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * @see https://symfony.com/doc/current/security/custom_authenticator.html
 */
class UserAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private JWTService $jwtService
    ) {}

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->getPathInfo(), '/api')
            && $request->cookies->has('jwt');
    }

    public function authenticate(Request $request): Passport
    {
        // $authHeader = $request->headers->get('Authorization');

        // if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader ?? '', $matches)) {
        //     throw new CustomUserMessageAuthenticationException('Missing or invalid Authorization header');
        // }

        // $jwt = $matches[1];

        if (!$jwt = $request->cookies->get('jwt')) {
            throw new CustomUserMessageAuthenticationException('Missing or invalid Authorization token');
        }

        $token = $this->jwtService->parseToken($jwt);

        if (!$this->jwtService->isTokenValid($token)) {
            throw new CustomUserMessageAuthenticationException('Invalid or expired token');
        }

        $claims = $token->claims();
        $userIdentifier = $claims->get('email');

        // TODO: Remove on production
        if ($_ENV['APP_ENV'] === 'dev') {
            $request->attributes->set('jwt_claims', $claims); // Forward for later
            // Or, for instant HTTPie view:
            // throw new CustomUserMessageAuthenticationException(json_encode($claims));
        }

        if (!$userIdentifier) {
            throw new CustomUserMessageAuthenticationException('JWT is missing email claim');
        }

        return new SelfValidatingPassport(new UserBadge($userIdentifier));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        // For AJAX/SPA requests
        if ($request->isXmlHttpRequest() || $request->headers->get('Accept') === 'application/json') {
            return new JsonResponse(['error' => 'Unauthenticated'], 401);
        }

        // For manual reload / browser navigation
        return new RedirectResponse('/');
    }

    // public function start(Request $request, ?AuthenticationException $authException = null): Response
    // {
    //     /*
    //      * If you would like this class to control what happens when an anonymous user accesses a
    //      * protected page (e.g. redirect to /login), uncomment this method and make this class
    //      * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
    //      *
    //      * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
    //      */
    // }
}
