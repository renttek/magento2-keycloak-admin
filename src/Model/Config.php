<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Config
{
    private const KEYCLOAK_AUTH_SERVER_URL = 'renttek_keycloak/keycloak/auth_server_url';
    private const KEYCLOAK_REALM           = 'renttek_keycloak/keycloak/realm';
    private const KEYCLOAK_CLIENT_ID       = 'renttek_keycloak/keycloak/client_id';
    private const KEYCLOAK_VERSION         = 'renttek_keycloak/keycloak/version';
    private const KEYCLOAK_CLIENT_SECRET   = 'renttek_keycloak/keycloak/client_secret';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor,
    ) {
    }

    public function getKeycloakAuthServerUrl(): string
    {
        return $this->scopeConfig->getValue(self::KEYCLOAK_AUTH_SERVER_URL);
    }

    public function getKeycloakVersion(): string
    {
        return $this->scopeConfig->getValue(self::KEYCLOAK_VERSION);
    }

    public function getKeycloakRealm(): string
    {
        return $this->scopeConfig->getValue(self::KEYCLOAK_REALM);
    }

    public function getKeycloakClientId(): string
    {
        return $this->scopeConfig->getValue(self::KEYCLOAK_CLIENT_ID);
    }

    public function getKeycloakClientSecret(): string
    {
        return $this->encryptor->decrypt(
            $this->scopeConfig->getValue(self::KEYCLOAK_CLIENT_SECRET)
        );
    }
}
