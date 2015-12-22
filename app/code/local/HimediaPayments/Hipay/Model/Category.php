<?php
/**
 * Hipay
 *
 * NOTICE OF LICENSE
 *
 * Copyright (c) 2010, HPME - HI-MEDIA PORTE MONNAIE ELECTRONIQUE (Groupe Hi-Media, Seed Factory, 19 Avenue des Volontaires, 1160 Bruxelles - Belgium)
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 * 
 *  - Redistributions of source code must retain the above copyright notice, 
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice, 
 *    this list of conditions and the following disclaimer in the documentation 
 *    and/or other materials provided with the distribution.
 *  - Neither the name of the Hipay and HPME - HI-MEDIA PORTE MONNAIE ELECTRONIQUE 
 *    nor the names of its contributors may be used to endorse or promote products 
 *    derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Paymentnetwork
 * @package    Paymentnetwork_Hipay
 * @copyright  Copyright (c) 2010 HPME - HI-MEDIA PORTE MONNAIE ELECTRONIQUE
 * @license    http://www.opensource.org/licenses/bsd-license.php  The BSD License
 */ 

/**
 * List of individual order and product categories.
 * 
 * @author Dirk Jönsson
 * @copyright 2010 Hi-media Payments (Hi-media Deutschland AG)
 */
class HimediaPayments_Hipay_Model_Category
{
    public function toOptionArray()
    {
    	$merchantSiteId = Mage::getStoreConfig('hipay/accountsettings/merchantsiteid');
    	$accountMode    = Mage::getStoreConfig('hipay/extendedaccountsettings/accountmode');
    	
    	if(empty($merchantSiteId) || empty($accountMode)) 
    	{
    		return array(
            	array('value'=>'', 'label'=>Mage::helper('hipaymod')->__("Please enter 'Merchant Site Id' first and save config!")),
        	);
    	}
    	else 
    	{
			$categoryUrl   = Mage::helper('hipaymod')->getHipayCategoryUrl($accountMode) . $merchantSiteId;
			//$response 	   = Mage::helper('hipaymod')->sendRestCall($categoryUrl);   	
			
			$optionList = array();
			//if( $this->analyzeCategoryResponseXML($response, $optionList) )
			if( $this->fillCategoryList($categoryUrl, $optionList) )
			{
				//Mage::log($optionList);
				return $optionList;
			}
			else 
			{
				Mage::log("Hipay: Error while resolving category list by this URL '".$categoryUrl."': " .$response);
				return array(
	            	array('value'=>'', 'label'=>Mage::helper('hipaymod')->__('Error! Check your logs, please!'))
	        	);
			}
    	}
    }
    
//    /**
//     * Create option array by xml response data
//     * 
//     * @param string $xml
//     * @param array $optionList
//     * @param string $err_msg
//     */
//	protected static function analyzeCategoryResponseXML($xml, & $optionList)
//    {
//		$err_msg='';
//		try {
//			$obj = @new SimpleXMLElement(trim($xml));
//		} catch (Exception $e) {
//			return false;
//		}
//		//Mage::log($obj);
//		if (isset($obj->categoriesList)) 
//		{
//			foreach ($obj->categoriesList as $category) 
//			{
//				foreach ($category as $item) 
//				{
//					//Mage::log($item);
//					$label = $item[0];
//					$value = $item[0]->attributes();
//					
//					$optionList[] = array('value'=>$value, 'label'=>Mage::helper('hipaymod')->__(''.$label));
//				}
//			}
//			return true;
//		}
//		
//		if (isset($obj->result[0]->message)) {
//			Mage::log("Hipay: Failed to resolve order categories: ".$obj->result[0]->message);
//		}
//		return false;
//	}

    /**
     * Create option array by xml response data of given category url
     * 
     * @param string $url
     * @param array $optionList
     * @param string $err_msg
     */
	protected static function fillCategoryList($url, & $optionList)
    {
		$err_msg='';
		try {
			$obj = new SimpleXMLElement($url, NULL, TRUE); 
	    	//Mage::log("XML-Daten" . $obj->asXML());
		} catch (Exception $e) {
			return false;
		}
		//Mage::log($obj);
		if (isset($obj->categoriesList)) 
		{
			foreach ($obj->categoriesList as $category) 
			{
				foreach ($category as $item) 
				{
					//Mage::log($item);
					$label = $item[0];
					$value = $item[0]->attributes();
					
					$optionList[] = array('value'=>$value, 'label'=>$label);
				}
			}
			return true;
		}
		
		if (isset($obj->result[0]->message)) {
			Mage::log("Hipay: Failed to resolve order categories: ".$obj->result[0]->message);
		}
		return false;
	}
}
