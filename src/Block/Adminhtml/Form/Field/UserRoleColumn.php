<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Authorization\Model\ResourceModel\Role\Collection as RoleCollection;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;

class UserRoleColumn extends Select
{
    public function __construct(
        private readonly RoleCollectionFactory $roleCollectionFactory,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

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
        /** @var RoleCollection $collection */
        $collection = $this->roleCollectionFactory->create();
        $collection->setRolesFilter();

        return $collection->toOptionArray();
    }
}
