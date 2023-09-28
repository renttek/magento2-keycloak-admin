<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Controller\Adminhtml\Auth;

use Magento\Backend\Model\Auth\StorageInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Renttek\KeycloakAdmin\Model\Auth;
use Renttek\KeycloakAdmin\Model\Config;
use Renttek\KeycloakAdmin\Model\KeycloakProviderFactory;
use Renttek\KeycloakAdmin\Model\Session;
use Renttek\KeycloakAdmin\Service\AdminUser;
use Stringable;
use Throwable;

class Callback implements HttpGetActionInterface
{
    public function __construct(
        private readonly KeycloakProviderFactory $providerFactory,
        private readonly RequestInterface $request,
        private readonly Session $session,
        private readonly UrlInterface $url,
        private readonly ManagerInterface $messageManager,
        private readonly RedirectFactory $redirectFactory,
        private readonly StorageInterface $authStorage,
        private readonly AdminUser $adminUser,
        private readonly Auth $auth,
        private readonly DateTime $dateTime,
        private readonly Config $config,
    ) {
    }

    public function execute(): Redirect
    {
        if (!$this->config->isEnabled() || $this->authStorage->isLoggedIn()) {
            return $this->redirectFactory
                ->create()
                ->setPath($this->url->getStartupPageUrl());
        }

        $provider = $this->providerFactory->create();

        $code = $this->request->getParam('code');
        if ($code === null) {
            return $this->redirectWithErrorMessage('Required parameter "code" was missing');
        }

        $sessionState = $this->session->getState();
        $requestState = $this->request->getParam('state');
        if ($sessionState === null || $sessionState !== $requestState) {
            return $this->redirectWithErrorMessage('Invalid state');
        }

        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
        } catch (Throwable $t) {
            return $this->redirectWithErrorMessage('Failed to get access token: ' . $t->getMessage());
        }

        try {
            $user = $provider->getResourceOwner($token);
        } catch (Throwable $t) {
            return $this->redirectWithErrorMessage('Failed to get resource owner: ' . $t->getMessage());
        }

        try {
            $adminUser = $this->adminUser->getOrCreateUser($user);
            $this->auth->loginByUsername($adminUser->getUserName());

            $expirationTimestamp = $this->dateTime->gmtTimestamp() + $token->getExpires();
            $this->session->setAccessToken($token->getToken());
            $this->session->setRefreshToken($token->getRefreshToken());
            $this->session->setTokenExpiration($expirationTimestamp);
        } catch (Throwable $t) {
            dd($t);
        }

        return $this->redirectFactory
            ->create()
            ->setPath($this->url->getStartupPageUrl());
    }

    private function redirectWithErrorMessage(string|Stringable $message): Redirect
    {
        $this->messageManager->addErrorMessage((string)$message);
        $url = $this->url->getUrl('admin');;

        return $this->redirectFactory
            ->create()
            ->setUrl($url);
    }
}
