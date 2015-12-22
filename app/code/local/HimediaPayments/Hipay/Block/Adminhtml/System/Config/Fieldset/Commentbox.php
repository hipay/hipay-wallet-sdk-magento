<?php

/**
 * Renderer for tab comment boxes in the hipay section
 */
class HimediaPayments_Block_Adminhtml_System_Config_Fieldset_Comments extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'himediapayments/system/config/fieldset/commentbox.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }
    
//	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
//    {
//        $this->setElement($element);
//        $url = $this->getUrl('catalog/product'); //
//
//        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
//                    ->setType('button')
//                    ->setClass('scalable')
//                    ->setLabel('Run Now !')
//                    ->setOnClick("setLocation('$url')")
//                    ->toHtml();
//
//        return $html;
//    }
}