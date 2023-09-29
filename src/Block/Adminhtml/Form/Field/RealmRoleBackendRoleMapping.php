<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;

class RealmRoleBackendRoleMapping extends AbstractFieldArray
{
    public const KEYCLOAK_ROLE = 'keycloak_role';
    public const MAGENTO_ROLE  = 'magento_role';

    private UserRoleColumn $userRoleRenderer;

    protected function _prepareToRender(): void
    {
        $this->addColumn(self::KEYCLOAK_ROLE, [
            'label' => 'Keycloak Realm Role',
            'class' => 'required-entry',
        ]);
        $this->addColumn(self::MAGENTO_ROLE, [
            'label' => 'Magento Role',
            'class' => 'required-entry',
            'renderer' => $this->getRoleRenderer(),
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $role = $row->getMagentoRole();
        if ($role !== null) {
            $options['option_' . $this->getRoleRenderer()->calcOptionHash($role)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @throws LocalizedException
     */
    private function getRoleRenderer(): BlockInterface
    {
        return $this->userAttributeRenderer ??= $this->getLayout()->createBlock(
            UserRoleColumn::class,
            '',
            ['data' => ['is_render_to_js_template' => true]]
        );
    }
}
