<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Controller\Adminhtml\Auth;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Renttek\KeycloakAdmin\Model\KeycloakProviderFactory;
use Renttek\KeycloakAdmin\Model\Session;
use Throwable;

class Callback implements HttpGetActionInterface
{
    public function __construct(
        private readonly KeycloakProviderFactory $providerFactory,
        private readonly RequestInterface $request,
        private readonly Session $session,
    ) {
    }

    public function execute(): Redirect
    {
        $provider = $this->providerFactory->create();

        $code = $this->request->getParam('code');
        if ($code === null) {
            dd('NO CODE');
        }

        $sessionState = $this->session->getState();
        $requestState = $this->request->getParam('state');
        if ($sessionState === null || $sessionState !== $requestState) {
            dd('STATE IS FUCKED');
        }

        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
        } catch (Throwable $t) {
            dd('FUCK', __LINE__, $t);
        }

        try {
            $user = $provider->getResourceOwner($token);
            dump($user);
        } catch (Throwable $t) {
            dd('FUCK', __LINE__, $t);
        }

        dd(__LINE__);
    }
}
