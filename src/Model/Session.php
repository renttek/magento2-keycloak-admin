<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Model;

use Magento\Framework\Session\SessionManager;

/**
 * @method void setState(string $state)
 * @method string|null getState()
 * @method void setAccessToken(string $token)
 * @method string|null getAccessToken()
 * @method void setRefreshToken(string $token)
 * @method string|null getRefreshToken()
 * @method void setTokenExpiration(int $gmtTimestamp)
 * @method int|null getTokenExpiration()
 */
class Session extends SessionManager
{
}
