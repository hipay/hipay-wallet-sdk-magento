<?php
/**
 * Supported currencies
 * 
 * @author Dirk Jönsson
 * @copyright 2010 Hi-media Payments (Hi-media Deutschland AG)
 */
class HimediaPayments_Hipay_Model_Accountmode
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'AUD', 'label'=>Mage::helper('hipaymod')->__('Australian Dollar')),
            array('value'=>'GBP', 'label'=>Mage::helper('hipaymod')->__('British Pound')),
            array('value'=>'EUR', 'label'=>Mage::helper('hipaymod')->__('Euro')),
            array('value'=>'CAD', 'label'=>Mage::helper('hipaymod')->__('Canadian Dollar')),
            array('value'=>'SEK', 'label'=>Mage::helper('hipaymod')->__('Swedish Crone')),
            array('value'=>'USD', 'label'=>Mage::helper('hipaymod')->__('US Dollar')),     
        );
    }

}
