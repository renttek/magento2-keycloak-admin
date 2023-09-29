<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class UserAttributeColumn extends Select
{
    public const USERNAME  = 'user_name';
    public const FIRSTNAME = 'first_name';
    public const LASTNAME  = 'last_name';
    public const LOCALE    = 'interface_locale';

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setInputId($value)
    {
        return $this->setId($value);
    }

    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }

        return parent::_toHtml();
    }

    private function getSourceOptions(): array
    {
        return [
            ['value' => self::USERNAME, 'label' => 'Username'],
            ['value' => self::FIRSTNAME, 'label' => 'Firstname'],
            ['value' => self::LASTNAME, 'label' => 'Lastname'],
            ['value' => self::LOCALE, 'label' => 'Locale'],
        ];
    }
}
