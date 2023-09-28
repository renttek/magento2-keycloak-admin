<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Controller\Adminhtml\Auth;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Renttek\KeycloakAdmin\Model\KeycloakProviderFactory;
use Renttek\KeycloakAdmin\Model\Session;

class Index implements HttpGetActionInterface
{
    public function __construct(
        private readonly KeycloakProviderFactory $providerFactory,
        private readonly RedirectFactory $redirectFactory,
        private readonly Session $session,
    ) {
    }

    public function execute(): Redirect
    {
        $keycloakProvider = $this->providerFactory->create();
        $authorizationUrl = $keycloakProvider->getAuthorizationUrl();

        $this->session->setState($keycloakProvider->getState());

        return $this->redirectFactory
            ->create()
            ->setUrl($authorizationUrl);
    }
}
