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

require_once 'HimediaPayments/Hipay/Mapi/mapi_package.php';

class HimediaPayments_Hipay_Helper_Data extends Mage_Core_Helper_Abstract
{
	/*
	 * Da alle benötigten Methoden von Mage_Core_Helper_Data geerbt werden,
	 * kann diese Klasse leer bleiben oder bei Bedarf durch eigene Implentierungen
	 * erweitert werden.
	 */
	
	/**
	 * Returns a payment URL based on the given order object
	 * 
	 * @param Mage_Sales_Model_Order $order
	 */
	public function getSinglePaymentUrl(Mage_Sales_Model_Order $order, $token)
	{
		$orderCategory = Mage::getStoreConfig('hipay/accountsettings/ordercategory');
		$accountmode   = Mage::getStoreConfig('hipay/extendedaccountsettings/accountmode');
				
		// ## Base params ##
		$params = $this->setupParams($order, $token);
		if($params == null) {
			exit;
		}
		
		// ## Taxes ##
//		$tax1 = $this->createTax("TVA 19.6",  19.6, true);
//		$tax2 = $this->createTax("Taxe fixe", 3.5,  false);
//		$tax3 = $this->createTax("TVA 5.5",   5.5,  true);
//		
//		if(($tax1 == null) || ($tax2 == null) || ($tax3 == null)) {
//			exit;
//		}
		
		
//		// ## Affiliates ##
//		// Affiliate who will receive 10% of all the items in the order
//		$aff1 = $this->createAffiliate(331, 59704, 10.4, HIPAY_MAPI_TTARGET_ALL);
//		// Affiliate who will receive 15% of the amount of the products, insurance and delivery amounts
//		$aff2 = $this->createAffiliate(332, 59705, 15.0, HIPAY_MAPI_TTARGET_ITEM | HIPAY_MAPI_TTARGET_INSURANCE | HIPAY_MAPI_TTARGET_SHIPPING);
//		
//		if(($aff1 == null) || ($aff2 == null)) {
//			exit;
//		}

		// ## Products (order lines) ##
		$orderInfo  	  	= Mage::helper("hipaymod")->__("Order") . " '" . $order->getRealOrderId() . "' " 
							  	. Mage::helper("hipaymod")->__("at") . " " . $order->getStore()->getFrontendName();
		$productName 	  	= Mage::helper("hipaymod")->__('Items of order') . " " . $order->getRealOrderId();
		$productInfo 	  	= "";
		$quantity 	 	  	= 1;
		$productSKU  	  	= "";
		$totalOrderAmount 	= $order->getTotalDue();
		$shippingAmount   	= $order->getShippingAmount();
		$reducedOrderAmount = $totalOrderAmount - $shippingAmount;
		
		$item = $this->createProduct($productName,				// product name
									 $productInfo,  			// product info
									 $quantity, 				// quantity
									 $productSKU,				// product reference (merchant)
									 965, 						// hipay category - TODO: richtige Kategorie ermitteln
									 $reducedOrderAmount, 		// amount
									 array()					// tax(es) - TODO: Steuern ermitteln
									 );	
		if($item == null) {
			exit;
		}									 
									 
		$items = array($item);
//		$no = 0;
//		$orderItems = $order->getAllVisibleItems();//getAllItems();
//		foreach ($orderItems as $orderItem) 
//		{
//			$no++;
//			Mage::log("count : " .$no);	
//			
//			$productOptions = ($orderItem->getProductOptions());
//			$productId = $productOptions["info_buyRequest"]["product"];
//			$quantity = $productOptions["info_buyRequest"]["qty"];
//
//			// Liste zusätzlicher Attribute als Produktinfo erstellen (z.B. "Size: Large")
//			$productInfo = "";
//			if(array_key_exists("attributes_info", $productOptions))
//			{
//				$attributesInfoArray = $productOptions["attributes_info"];
//				foreach ($attributesInfoArray as $attribute) 
//				{
//					if(!empty($productInfo)) {
//						$productInfo .= ", ";
//					}
//					$productInfo .= $attribute["label"].": ".$attribute["value"];
//				}
//			}
//			
//			$product = Mage::getModel('catalog/product')->load($productId); // Mage_Catalog_Model_Product                                                      
//			
////			Mage::log($product);
//
//			$item = $this->createProduct($product->getName(),		// product name
//										 $productInfo,  			// product info
//										 $quantity, 				// quantity
//										 $product->getSKU(),		// product reference (merchant)
//										 5, 						// hipay category - TODO: richtige Kategorie ermitteln
//										 $product->getPrice(), 		// amount
//										 array()					// tax(es) - TODO: Steuern ermitteln
//										 );		
//			if($item == null) {
//				exit;
//			}
//			$items[] = $item;
//		}	
//		Mage::log($items);	
//		
//		
//		//Mage::log( serialize($order) );
//		
//		Mage::log("Total Due      :" . $order->getTotalDue());
//		Mage::log("Base Total Due :" . $order->getBaseTotalDue());
//		
//		Mage::log($order->getFullTaxInfo());
//		Mage::log( $order->getShippingTaxAmount() );
//
//		$baseTax = $order->getBaseShippingTaxAmount();
//        $tax = $order->getShippingTaxAmount();
//        $shippingBaseAmount = $order->getBaseShippingAmount();
//        $shippingAmount = $order->getShippingAmount();
//        
//        Mage::log("Base Tax             :" . $baseTax);
//        Mage::log("Tax                  :" . $tax);
//        Mage::log("Shipping Base Amount :" . $shippingBaseAmount);
//        Mage::log("Shipping Amount      :" . $shippingAmount);
	
		
		// ## Order object ##
		$hipayorder = $this->createOrder( 
									 $orderInfo,			// Order title
									 '', 					// Order information - TODO: Bestellinfo hinzufügen
									 $orderCategory,		// The order category 
									 $shippingAmount,		// The shipping costs 
									 array (),		 		// The shipping taxes - TODO: Steuern ?
									 0,						// The insurance costs - Versicherungen?
									 array (),				// The insurance taxes - Steuern?
									 0.0,					// The fix costs       - Zusatzkosten?
									 array (),				// The fix costs taxes - Steuern?
									 array ()				// affiliates
									);
							 
		if($hipayorder == null) {
			exit;
		}
							 
		// ## Payment object ##
		$payment = $this->createSimplePayment($params, $hipayorder, $items);
		
		// ## XML representation of this order and sending the feed to the Hipay platform ## 
		$xml = $payment->getXML();
		$response = HIPAY_MAPI_SEND_XML::sendXML($xml, $this->getHipayUrl($accountmode));

		// ## Processing the platform's response ##
		$result = HIPAY_MAPI_COMM_XML::analyzeResponseXML($response, $url, $err_msg);
		
		if($result === false) {
			Mage::log($err_msg);
		}

		$resultArray = array("paymentUrl" => $url,
							 "errorMsg" => $err_msg);
		
		return $resultArray;
	}
	
	/**
	 * Erzeugt die Basisparameter für den Bezahlvorgang
	 * 
	 * @param HIPAY_MAPI_PaymentParams $params
	 * @param string token
	 */
	protected function setupParams(Mage_Sales_Model_Order $order, $token)
	{
		$accountId		    = Mage::getStoreConfig('hipay/accountsettings/accountid');
		$merchantPassword   = Mage::getStoreConfig('hipay/accountsettings/merchantpassword');
		$merchantSiteId     = Mage::getStoreConfig('hipay/accountsettings/merchantsiteid');
		//$accountCurrency    = Mage::getStoreConfig('hipay/accountsettings/accountcurrency');
		$orderCurrency 		= $order->getOrderCurrency()->getData("currency_code");
		$ageClassification  = Mage::getStoreConfig('hipay/accountsettings/ageclassification');
		$notificationEmail  = Mage::getStoreConfig('hipay/accountsettings/notificationemail');

		$logoUrl			= Mage::getStoreConfig('hipay/extendedaccountsettings/logourl');
		
		$itemAccountId		= Mage::getStoreConfig('hipay/extendedaccountsettings/itemaccountid');
		$taxAccountId		= ""; //Mage::getStoreConfig('hipay/extendedaccountsettings/taxaccountid');
		$insuranceAccountId	= ""; //Mage::getStoreConfig('hipay/extendedaccountsettings/insuranceaccountid');
		$fixcostAccountId	= ""; //Mage::getStoreConfig('hipay/extendedaccountsettings/fixcostaccountid');
		$shippingAccountId	= Mage::getStoreConfig('hipay/extendedaccountsettings/shippingaccountid');
		
		$itemAccountId 		= (empty($itemAccountId)      ? $accountId     : $itemAccountId);
		$taxAccountId 		= (empty($taxAccountId) 	  ? $itemAccountId : $taxAccountId);
		$insuranceAccountId = (empty($insuranceAccountId) ? $itemAccountId : $insuranceAccountId);
		$fixcostAccountId 	= (empty($fixcostAccountId)   ? $itemAccountId : $fixcostAccountId);
		$shippingAccountId	= (empty($shippingAccountId)  ? $itemAccountId : $shippingAccountId);
		
// ## REMOVE TEST ##
//		echo "<hr>"  
//		     ."accound id:" . $accountId . ",<br />"
//             ."pw:" . $merchantPassword . ",<br />"
//             ."site:" . $merchantSiteId . ",<br />"
//             ."min age:" . $ageClassification . ",<br />"
//             ."currency:" . $accountCurrency . ",<br />"
//             ."ack email:" . $notificationEmail . ",<br />"
//             ."logo url:" . $logoUrl . ",<br />"
//             
//             ."item account:" . $itemAccountId . ",<br />"
//             ."tax account:" . $taxAccountId . ",<br />"
//             ."insurance account:" . $insuranceAccountId . ",<br />"
//             ."fixcost account:" . $fixcostAccountId . ",<br />"
//             ."shipping account:" . $shippingAccountId . "<br />"
//             ."<hr>";  
// ## REMOVE TEST - END ##		
		
		$params = new HIPAY_MAPI_PaymentParams();
		
		//The Hipay platform connection parameters. This is not the information used to connect to your Hipay 
		//account, but the specific login and password used to connect to the payment platform.
		//The login is the ID of the hipay merchant account receiving the payment, and the password is
		//the « merchant password » set within your Hipay account (site info).
		$params->setLogin($accountId, $merchantPassword);
		
		// The amounts will be credited to the defined accounts
		$params->setAccounts($itemAccountId, $taxAccountId, $insuranceAccountId, $fixcostAccountId, $shippingAccountId);
		
		// The payment interface will be in German by default
		$params->setDefaultLang('de_DE'); 
		
		// The interface will be the Web interface
		$params->setMedia('WEB');
		
		//The order content is intended for people at least (ALL, 12+, 16+, 18+) years old.
		$params->setRating($ageClassification);

		// This is a single payment
		$params->setPaymentMethod(HIPAY_MAPI_METHOD_SIMPLE);
		
		// The capture take place immediately (HIPAY_MAPI_CAPTURE_IMMEDIATE), manually (HIPAY_MAPI_CAPTURE_MANUAL)
		// or delayed (0-7 -> number of days before capture) 
		$params->setCaptureDay(HIPAY_MAPI_CAPTURE_IMMEDIATE);
		
		// The amounts are expressed in Euros, this has to be the same currency as the merchant's account.
		$params->setCurrency($orderCurrency);
		
		// The merchant-selected identifier for this order
		$params->setIdForMerchant($order->getRealOrderId());
		
		// Two data elements of type key=value are declared and will be returned to the merchant after the payment in the
		// notification data feed [C].
//		$params->setMerchantDatas('id_client','2000');
//		$params->setMerchantDatas('credit','10');

		// This order relates to the web site which the merchant declared in the Hipay platform.
		$params->setMerchantSiteId($merchantSiteId);

		// Set buyer email 
		$params->setIssuerAccountLogin($order->getCustomerEmail());

		// If the payment is accepted, the user will be redirected to this page
		$urlOk = Mage::getUrl('hipay/mapi/success/'); // creates URL 'http://www.mywebsite.com/hipay/mapi/success/'
		$params->setURLOk($urlOk . $token); // add security-token

		// If the payment is refused, the user will be redirected to this page
		$urlNok = Mage::getUrl('hipay/mapi/failed'); // creates URL 'http://www.mywebsite.com/hipay/mapi/failed/'
		$params->setUrlNok($urlNok . $token); // add security-token

		// If the user cancels the payment, he will be redirected to this page
		$urlCancel = Mage::getUrl('hipay/mapi/cancel'); // creates URL 'http://www.mywebsite.com/hipay/mapi/failed/'
		$params->setUrlCancel($urlCancel . $token); // add security-token
		
		// The email address used to send the notifications, on top of the http notifications.
		$params->setEmailAck($notificationEmail);

		// The merchant's site will be notified of the result of the payment by a call to the script
		// "listen_hipay_notification.php"
		$urlAck = Mage::getUrl('hipay/mapi/notification'); // creates URL 'http://www.mywebsite.com/hipay/mapi/notfication'
		$params->setUrlAck($urlAck);
		
		// The background color of the interface will be #FFFFFF (default color recommended)
		$params->setBackgroundColor('#FFFFFF');
		
		//The merchant’s logo URL, this logo will be displayed on the payment pages.
		$params->setLogoUrl($logoUrl);
		
		if (!$params->check())
		{
			$errorTxt = "Hipay: An error occurred while creating the HIPAY_MAPI_PaymentParams object"; 
			Mage::log($errorTxt);
			return null;
		}
		return $params;
	}
	
	/**
	 * Creates a HIPAY_MAPI_Tax object, returns null if creation fails
	 * @param string $taxName
	 * @param float $taxValue
	 * @param boolean $taxIsPercentage
	 */
	protected function createTax($taxName, $taxValue, $taxIsPercentage)
	{
		$tax = new HIPAY_MAPI_Tax();
		$tax->setTaxName($taxName);
		$tax->setTaxVal($taxValue,$taxIsPercentage);
		
		if (!$tax->check())
		{
			$errorTxt = "Hipay: An error occurred while creating a HIPAY_MAPI_Tax object";
			Mage::log($errorTxt);
			return null;
		}
		return $tax;
	}
	
	/**
	 * Creates a HIPAY_MAPI_Affiliate object, returns null if creation fails
	 * @param int $customerId
	 * @param int $accountId
	 * @param float $value
	 * @param int $percentageTarget
	 */
	protected function createAffiliate($customerId, $accountId, $value, $percentageTarget)
	{
		$aff = new HIPAY_MAPI_Affiliate();
		$aff->setCustomerId($customerId);
		$aff->setAccountId($accountId);
		$aff->setValue($value, $percentageTarget);
		
		if (!$aff->check())
		{
			$errorTxt = "Hipay: An error occurred while creating an HIPAY_MAPI_Affiliate object";
			Mage::log($errorTxt);
			return null;
		}
		return aff;
	}
	
	/**
	 * Creates a HIPAY_MAPI_Product object, returns null if creation fails
	 * @param string $name
	 * @param string $info
	 * @param int $quantity
	 * @param string $reference
	 * @param int $category
	 * @param float $price
	 * @param array $taxes
	 */
	protected function createProduct($name, $info, $quantity, $reference, $category, $price, $taxes)
	{
		$item = new HIPAY_MAPI_Product();
		$item->setName($name);
		$item->setInfo($info);
		$item->setquantity($quantity);
		$item->setRef($reference);
		$item->setCategory($category);
		$item->setPrice($price);
		$item->setTax($taxes);
		
		if (!$item->check())
		{
			$errorTxt = "Hipay: An error occurred while creating a HIPAY_MAPI_Product object";
			Mage::log($errorTxt);
			return null;
		}
		return $item;
	}
	

	/**
	 * Creates a HIPAY_MAPI_Order() object, returns null if creation fails
	 * 
	 * @param string $orderTitle Order title
	 * @param string $orderInfo Order information
	 * @param int $orderCategory The order category - e.g. 3 (Books). Refer to annex 7 in the merchant kit doc to see how to find out what category your site belongs to. 
	 * @param float $shippingAmount The shipping costs
	 * @param array $shippingTaxes The shipping taxes
	 * @param float $insuranceAmount The insurance costs 
	 * @param array $insuranceTaxes The insurance taxes
	 * @param float $fixCostAmount The fix costs
	 * @param array $fixCostTaxes The fix taxes
	 * @param array $affiliates Affiliates
	 */
	protected function createOrder($orderTitle, $orderInfo, $orderCategory, $shippingAmount, $shippingTaxes, $insuranceAmount, $insuranceTaxes, $fixCostAmount, $fixCostTaxes, $affiliates)
	{
		$order = new HIPAY_MAPI_Order();
		$order->setOrderTitle($orderTitle);
		$order->setOrderInfo($orderInfo);
		$order->setOrderCategory($orderCategory);
		$order->setShipping($shippingAmount, $shippingTaxes);
		$order->setInsurance($insuranceAmount, $insuranceTaxes);
		$order->setFixedCost($fixCostAmount, $fixCostTaxes);
		$order->setAffiliate($affiliates);
		
		if (!$order->check())
		{
			$errorTxt = "Hipay: An error occurred while creating a HIPAY_MAPI_Order object";
			Mage::log($errorTxt);
			return null;
		}
		return $order;
	}
	
	
	/**
	 * Creates a HIPAY_MAPI_SimplePayment object, return null if creation fails
	 * 
	 * @param HIPAY_MAPI_PaymentParams $params
	 * @param HIPAY_MAPI_Order $order
	 * @param array $items
	 */
	protected function createSimplePayment($params, $order, $items)
	{
		try {
			$payment = new HIPAY_MAPI_SimplePayment($params,$order,$items);
		}
		catch (Exception $ex) 
		{
			$errorTxt = "Hipay: Error" . $ex->getMessage();
			Mage::log($errorTxt);
			return null;
		}
		return $payment;
	}
	
	/**
	 * Returns the current Hipay URL
	 * @param unknown_type $accountmode
	 */
	protected function getHipayUrl($accountmode)
	{
		if($accountmode == 'HIPAY_GATEWAY_TEST_URL') {
			return HIPAY_GATEWAY_TEST_URL;
		}
		else if($accountmode == 'HIPAY_GATEWAY_URL') {
			return HIPAY_GATEWAY_URL;
		}
		Mage::log("Hipay: Undefined account mode: '".$accountmode."'");
		return '';
	}
	
	/**
	 * Returns the current Hipay category URL
	 * @param unknown_type $accountmode
	 */
	public function getHipayCategoryUrl($accountmode)
	{
		if($accountmode == 'HIPAY_GATEWAY_TEST_URL') {
			return HIPAY_CATEGORY_TEST_URL;
		}
		else if($accountmode == 'HIPAY_GATEWAY_URL') {
			return HIPAY_CATEGORY_URL;
		}
		Mage::log("Hipay: Undefined account mode: '".$accountmode."'");
		return '';
	}
	
	/**
	 * Returns REST call response
	 * 
	 * @param string $url
	 */
	public static function sendRestCall($url="") 
	{
        if ($url=="") {
            return false;
        } else {
        	$turl=parse_url($url);	
        }
        
//        if (!isset($turl['path']))
//            $turl['path']='/';

        $curl = curl_init();
        curl_setopt($curl,CURLOPT_TIMEOUT, HIPAY_MAPI_CURL_TIMEOUT);
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_USERAGENT,"HIPAY");
        curl_setopt($curl,CURLOPT_URL, $turl['scheme'].'://'.$turl['host'].$turl['path']);
//      curl_setopt($curl,CURLOPT_POSTFIELDS,'xml='.urlencode($xml));
		curl_setopt($curl,CURLOPT_POSTFIELDS,'xml=<xml></xml>'); // prevents curl to set a "content length" of "-1" in older PHP-Versions
		
        //DEBUG
        //curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        //DEBUG ENDE
        
        if(HIPAY_MAPI_CURL_PROXY_ON === true)
        {
            curl_setopt($curl, CURLOPT_PROXY, HIPAY_MAPI_CURL_PROXY);
            curl_setopt($curl, CURLOPT_PROXYPORT, HIPAY_MAPI_CURL_PROXYPORT);
        }

        if(HIPAY_MAPI_CURL_LOG_ON === true)
        {
            $errorFileLog = fopen(HIPAY_MAPI_CURL_LOGFILE, "a+");
            curl_setopt($curl, CURLOPT_VERBOSE, true);
            curl_setopt($curl, CURLOPT_STDERR, $errorFileLog);
        }

        curl_setopt($curl, CURLOPT_HEADER, 0);

        ob_start();
        if (curl_exec($curl) !== true)
        {
            $output = $turl['scheme'].'://'.$turl['host'].$turl['path'].' is not reachable';
            $output .= '<br />Network problem ? Verify your proxy configuration in mapi_defs.php';
        }
        else {
        	$output = ob_get_contents();
        }
        
        //DEBUG
        //Mage::log("HIPAY CURL-LOG");
        //Mage::log(curl_getinfo($curl));
        //Mage::log("HIPAY CURL-LOG END");
        //DEBUG ENDE 
        
        ob_end_clean();
        curl_close($curl);
        if(HIPAY_MAPI_CURL_LOG_ON === true)
        {
            fclose($errorFileLog);
        }
        return $output;
    }
    
    /**
     * Erzeugt einen Token 
     */
    public function generateToken()
    {
    	$seed = crc32(uniqid(sha1(microtime(true) . getmypid()), true));
		mt_srand($seed);
		$n = mt_rand(1, 200);
		for ($i = 0; $i < $n; $i++) {
    		$token = mt_rand();
		}
		return $token;
    }
}