<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Controller\Adminhtml\Auth;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Renttek\KeycloakAdmin\Model\KeycloakProviderFactory;
use Renttek\KeycloakAdmin\Model\Session;
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
    ) {
    }

    public function execute(): Redirect
    {
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
            dump($user);
        } catch (Throwable $t) {
            return $this->redirectWithErrorMessage('Failed to get resource owner: ' . $t->getMessage());
        }

        // TODO: find user by email ($user->getEmail())
        dd(__LINE__);
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
