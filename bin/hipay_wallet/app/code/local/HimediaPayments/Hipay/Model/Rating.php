<?php
/**
 * The audience rating for the product
 * 
 * @author Dirk Jönsson
 * @copyright 2010 Hi-media Payments (Hi-media Deutschland AG)
 */
class HimediaPayments_Hipay_Model_Rating
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'ALL', 'label'=>Mage::helper('hipaymod')->__('For all ages')),
            array('value'=>'+12', 'label'=>Mage::helper('hipaymod')->__('For ages 12 and over')),
            array('value'=>'+16', 'label'=>Mage::helper('hipaymod')->__('For ages 16 and over')),
            array('value'=>'+18', 'label'=>Mage::helper('hipaymod')->__('For ages 18 and over')),
        );
    }

}
