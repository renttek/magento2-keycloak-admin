<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Model;

use Magento\Framework\Session\SessionManager;

/**
 * @method void setState(string|null $state)
 * @method string|null getState()
 * @method void setAccessToken(string|null $token)
 * @method string|null getAccessToken()
 * @method void setRefreshToken(string|null $token)
 * @method string|null getRefreshToken()
 * @method void setTokenExpiration(int|null $gmtTimestamp)
 * @method int|null getTokenExpiration()
 */
class Session extends SessionManager
{
    public function clear(): void
    {
        $this->setState(null);
        $this->setAccessToken(null);
        $this->setRefreshToken(null);
        $this->setTokenExpiration(null);
    }
}
