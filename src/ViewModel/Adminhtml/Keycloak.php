<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\ViewModel\Adminhtml;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Renttek\KeycloakAdmin\Model\Config;

class Keycloak implements ArgumentInterface
{
    public function __construct(
        private readonly Config $config,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }
}
