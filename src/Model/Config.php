<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;

use function is_array;

class Config
{
    private const KEYCLOAK_ENABLED         = 'renttek_keycloak/keycloak/enabled';
    private const KEYCLOAK_AUTH_SERVER_URL = 'renttek_keycloak/keycloak/auth_server_url';
    private const KEYCLOAK_REALM           = 'renttek_keycloak/keycloak/realm';
    private const KEYCLOAK_CLIENT_ID       = 'renttek_keycloak/keycloak/client_id';
    private const KEYCLOAK_VERSION         = 'renttek_keycloak/keycloak/version';
    private const KEYCLOAK_CLIENT_SECRET   = 'renttek_keycloak/keycloak/client_secret';

    private const USER_ROLES_ALLOWED_FOR_LOGIN = 'renttek_keycloak/user/roles_allowed_for_login';
    private const USER_ENABLE_REGISTRATION     = 'renttek_keycloak/user/enable_user_creation';
    private const USER_DEFAULT_LOCALE          = 'renttek_keycloak/user/default_locale';
    private const USER_ATTRIBUTE_MAPPING       = 'renttek_keycloak/user/user_attribute_mapping';
    private const USER_ROLE_MAPPING            = 'renttek_keycloak/user/realm_role_to_backend_role_mapping';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::KEYCLOAK_ENABLED);
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

    public function getUserRolesAllowedForLogin(): array
    {
        $roles = $this->scopeConfig->getValue(self::USER_ROLES_ALLOWED_FOR_LOGIN);
        $roles = str_replace("\r\n", "\n", $roles);
        $roles = explode("\n", $roles);

        return array_map('trim', $roles);
    }

    public function isRegistrationEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::USER_ENABLE_REGISTRATION);
    }

    public function getDefaultLocale(): string
    {
        return $this->scopeConfig->getValue(self::USER_DEFAULT_LOCALE);
    }

    public function getUserAttributeMapping(): array
    {
        $mapping = $this->scopeConfig->getValue(self::USER_ATTRIBUTE_MAPPING);

        return is_array($mapping)
            ? $mapping
            : $this->serializer->unserialize($mapping);
    }

    public function getUserRoleMapping(): array
    {
        $mapping = $this->scopeConfig->getValue(self::USER_ROLE_MAPPING);

        return is_array($mapping)
            ? $mapping
            : $this->serializer->unserialize($mapping);
    }
}
