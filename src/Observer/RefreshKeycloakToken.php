<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Renttek\KeycloakAdmin\Model\Auth;
use Renttek\KeycloakAdmin\Model\Config;
use Renttek\KeycloakAdmin\Model\KeycloakProviderFactory;
use Renttek\KeycloakAdmin\Model\Session;
use Throwable;

class RefreshKeycloakToken implements ObserverInterface
{
    public function __construct(
        private readonly Session $session,
        private readonly DateTime $dateTime,
        private readonly KeycloakProviderFactory $keycloakProviderFactory,
        private readonly Auth $auth,
        private readonly ManagerInterface $messageManager,
        private readonly Config $config,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer): void
    {
        if (!$this->config->isEnabled() || $this->session->getRefreshToken() === null) {
            return;
        }

        $refreshToken        = $this->session->getRefreshToken();
        $expirationTimestamp = $this->session->getTokenExpiration();

        $provider = $this->keycloakProviderFactory->create();

        if ($expirationTimestamp < $this->dateTime->gmtTimestamp()) {
            $this->session->clear();
            $this->messageManager->addErrorMessage('Keycloak token is expired');
            $this->auth->logout();
            return;
        }

        try {
            $token = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $refreshToken,
            ]);
        } catch (Throwable $t) {
            $this->session->clear();
            $this->messageManager->addErrorMessage('Could not refresh keycloak token');
            $this->auth->logout();
            return;
        }

        $expirationTimestamp = $this->dateTime->gmtTimestamp() + $token->getExpires();
        $this->session->setAccessToken($token->getToken());
        $this->session->setRefreshToken($token->getRefreshToken());
        $this->session->setTokenExpiration($expirationTimestamp);
    }
}
