<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private const KEYCLOAK_AUTH_SERVER_URL = 'renttek_keycloak/keycloak/auth_server_url';
    private const KEYCLOAK_REALM           = 'renttek_keycloak/keycloak/realm';
    private const KEYCLOAK_CLIENT_ID       = 'renttek_keycloak/keycloak/client_id';
    private const KEYCLOAK_CLINT_SECRET    = 'renttek_keycloak/keycloak/client_secret';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {
    }

    public function getKeycloakAuthServerUrl(): string
    {
        return 'http://keycloak:8080/';
        return $this->scopeConfig->getValue(self::KEYCLOAK_AUTH_SERVER_URL);
    }

    public function getKeycloakRealm(): string
    {
        return 'magento';
        return $this->scopeConfig->getValue(self::KEYCLOAK_REALM);
    }

    public function getKeycloakClientId(): string
    {
        return 'test-2';
        return $this->scopeConfig->getValue(self::KEYCLOAK_CLIENT_ID);
    }

    public function getKeycloakClientSecret(): string
    {
        return 'hAxp426iqgRSc5An9diflKDxdLv1aWh7';
        return $this->scopeConfig->getValue(self::KEYCLOAK_CLINT_SECRET);
    }
}
