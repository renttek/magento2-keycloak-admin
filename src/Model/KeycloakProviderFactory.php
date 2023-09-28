<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Model;

use Magento\Backend\Model\UrlInterface;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak as KeycloakProvider;

class KeycloakProviderFactory
{
    public function __construct(
        private readonly Config $config,
        private readonly UrlInterface $url,
    ) {
    }

    public function create(): KeycloakProvider
    {
        // TODO: add optional encryption

        return new KeycloakProvider([
            'authServerUrl'         => $this->config->getKeycloakAuthServerUrl(),
            'realm'                 => $this->config->getKeycloakRealm(),
            'clientId'              => $this->config->getKeycloakClientId(),
            'clientSecret'          => $this->config->getKeycloakClientSecret(),
            'redirectUri'           => $this->url->getUrl('renttek_keycloak/auth/callback'),
            'version'               => $this->config->getKeycloakVersion(),
        ]);
    }
}
