<?php

namespace App\Service;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;

class JWTService
{
    private Configuration $config;

    public function __construct(
        private string $privateKeyPath,
        private string $publicKeyPath,
        private string $passphrase
    ) {
        $this->config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file($privateKeyPath, $passphrase),
            InMemory::file($publicKeyPath)
        );
    }

    public function createToken(string $userId, string $email): string
    {
        $now = new \DateTimeImmutable();
        $token = $this->config->builder()
            ->issuedBy('https://your-sso-server.com')
            ->permittedFor('https://your-client-app.com')
            ->identifiedBy(bin2hex(random_bytes(16)))
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify('+1 hour'))
            ->withClaim('uid', $userId)
            ->withClaim('email', $email)
            ->getToken($this->config->signer(), $this->config->signingKey());

        return $token->toString();
    }

    public function parseToken(string $jwt): Token
    {
        return $this->config->parser()->parse($jwt);
    }

    /**
     * @param mixed $token
     */
    public function validateToken($token): bool
    {
        return $this->config->validator()->validate($token, ...$this->config->validationConstraints());
    }
}
