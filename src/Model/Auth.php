<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Model;

use Magento\Backend\Model\Auth as BackendAuth;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\Plugin\AuthenticationException as PluginAuthenticationException;
use Magento\User\Model\User;

class Auth extends BackendAuth
{
    private string $errorMessage = 'The account sign-in was incorrect or your account is disabled temporarily.';

    /**
     * @throws AuthenticationException
     */
    public function loginByUsername(string $username): void
    {
        try {
            $this->_initCredentialStorage();
            $this->getCredentialStorage()->loginByUsername($username);
            if ($this->getCredentialStorage()->getId()) {
                /** @var User $user */
                $user = $this->getCredentialStorage();
                $this->getAuthStorage()->setUser($user);
                $this->getAuthStorage()->processLogin();

                $this->_eventManager->dispatch('backend_auth_user_login_success', [
                    'user' => $this->getCredentialStorage()
                ]);
            }

            if (!$this->getAuthStorage()->getUser()) {
                parent::throwException(__($this->errorMessage));
            }
        } catch (PluginAuthenticationException $e) {
            $this->_eventManager->dispatch('backend_auth_user_login_failed', [
                'user_name' => $username,
                'exception' => $e
            ]);

            throw $e;
        } catch (LocalizedException $e) {
            $this->_eventManager->dispatch('backend_auth_user_login_failed', [
                'user_name' => $username,
                'exception' => $e
            ]);

            parent::throwException(__($e->getMessage() ?? $this->errorMessage));
        }
    }
}
