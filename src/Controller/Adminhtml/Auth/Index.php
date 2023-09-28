<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Controller\Adminhtml\Auth;

use Magento\Backend\Model\Auth\StorageInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Renttek\KeycloakAdmin\Model\Config;
use Renttek\KeycloakAdmin\Model\KeycloakProviderFactory;
use Renttek\KeycloakAdmin\Model\Session;

class Index implements HttpGetActionInterface
{
    public function __construct(
        private readonly KeycloakProviderFactory $providerFactory,
        private readonly RedirectFactory $redirectFactory,
        private readonly Session $session,
        private readonly StorageInterface $auth,
        private readonly UrlInterface $url,
        private readonly Config $config,
    ) {
    }

    public function execute(): Redirect
    {
        $this->session->clear();

        if (!$this->config->isEnabled() || $this->auth->isLoggedIn()) {
            return $this->redirectFactory
                ->create()
                ->setPath($this->url->getStartupPageUrl());
        }

        $keycloakProvider = $this->providerFactory->create();
        $authorizationUrl = $keycloakProvider->getAuthorizationUrl();

        $this->session->setState($keycloakProvider->getState());

        return $this->redirectFactory
            ->create()
            ->setUrl($authorizationUrl);
    }
}
