<?php

declare(strict_types=1);

namespace Renttek\KeycloakAdmin\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;

class UserAttributeMapping extends AbstractFieldArray
{
    public const KEYCLOAK_ATTRIBUTE = 'keycloak_attribute';
    public const MAGENTO_ATTRIBUTE  = 'magento_attribute';

    private UserAttributeColumn $userAttributeRenderer;

    /**
     * @throws LocalizedException
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn(self::KEYCLOAK_ATTRIBUTE, [
            'label' => 'Keycloak Attribute',
            'class' => 'required-entry',
        ]);
        $this->addColumn(self::MAGENTO_ATTRIBUTE, [
            'label'    => 'Magento Attribute',
            'class'    => 'required-entry',
            'renderer' => $this->getAttributeRenderer(),
        ]);

        $this->_addAfter       = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $attribute = $row->getMagentoAttribute();
        if ($attribute !== null) {
            $options['option_' . $this->getAttributeRenderer()->calcOptionHash($attribute)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }


    /**
     * @throws LocalizedException
     */
    private function getAttributeRenderer(): BlockInterface
    {
        return $this->userAttributeRenderer ??= $this->getLayout()->createBlock(
            UserAttributeColumn::class,
            '',
            ['data' => ['is_render_to_js_template' => true]]
        );
    }
}
