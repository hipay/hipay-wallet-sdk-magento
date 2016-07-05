<?php

require_once 'HimediaPayments/Hipay/Mapi/mapi_package.php';

class HimediaPayments_Hipay_Helper_Mapi 
{
	function createSinglePaymentDlg()
	{
		$params = new HIPAY_MAPI_PaymentParams();
		//The Hipay platform connection parameters. This is not the information used to connect to your Hipay 
		//account, but the specific login and password used to connect to the payment platform.
		//The login is the ID of the hipay merchant account receiving the payment, and the password is
		//the « merchant password » set within your Hipay account (site info).
		$params->setLogin('22955','mypassword');
		// The amounts will be credited to account 22955, except the taxes which will be credited to account 23192
		$params->setAccounts(22955,23192);
		// The payment interface will be in International French by default
		$params->setDefaultLang('de_DE');
		// The interface will be the Web interface
		$params->setMedia('WEB');
		//The order content is intended for people at least 16 years old.
		$params->setRating('16+');
		// This is a single payment
		$params->setPaymentMethod(HIPAY_MAPI_METHOD_SIMPLE);
		// The capture take place immediately
		$params->setCaptureDay(HIPAY_MAPI_CAPTURE_IMMEDIATE);
		// The amounts are expressed in Euros, this has to be the same currency as the merchant's account.
		$params->setCurrency('EUR');
		// The merchant-selected identifier for this order is REF6522
		$params->setIdForMerchant('REF6522');
		// Two data elements of type key=value are declared and will be returned to the merchant after the payment in the
		// notification data feed [C].
		$params->setMerchantDatas('id_client','2000');
		$params->setMerchantDatas('credit','10');
		// This order relates to the web site which the merchant declared in the Hipay platform.
		// The I.D. assigned to this website is '9'
		$params->setMerchantSiteId(9);
		// If the payment is accepted, the user will be redirected to this page
		$params->setURLOk('http://www.mywebsite.com/success.html ');
		// If the payment is refused, the user will be redirected to this page
		$params->setUrlNok('http://www.mywebsite.com/refused.html ');
		// If the user cancels the payment, he will be redirected to this page
		$params->setUrlCancel('http://www.mywebsite.com/cancel.html ');
		// The email address used to send the notifications, on top of the http notifications.
		// cf chap 19 : RECEIVING A RESULTS NOTIFICATION ABOUT A PAYMENT ACTION
		$params->setEmailAck('djoensson@hi-media.com');
		// The merchant's site will be notified of the result of the payment by a call to the script
		// "listen_hipay_notification.php"
		// cf chap 19 : RECEIVING A RESULTS NOTIFICATION ABOUT A PAYMENT ACTION
		$params->setUrlAck('http://www.mywebsite.com/listen_hipay_notification.php');
		// The background color of the interface will be #FFFFFF (default color recommended)
		$t=$params->setBackgroundColor('#FFFFFF');
		$t=$params->check();
		
		if (!$t)
		{
			echo "An error occurred while creating the paymentParams object";
			exit;
		}
		
		// ## Taxes ##
		// Tax at 19.6%
		$tax1 = new HIPAY_MAPI_Tax();
		$tax1->setTaxName('TVA (19.6)');
		$tax1->setTaxVal(19.6,true);
		$t=$tax1->check();
		if (!$t)
		{
			echo "An error occurred while creating a tax object";
			exit;
		}
		// Fixed tax of 3.50 euros
		$tax2 = new HIPAY_MAPI_Tax();
		$tax2->setTaxName('Taxe fixe');
		$tax2->setTaxVal(3.5,false);
		$t=$tax2->check();
		if (!$t)
		{
			echo "An error occurred while creating a tax object";
			exit;
		}
		// Tax at 5.5%
		$tax3 = new HIPAY_MAPI_Tax();
		$tax3->setTaxName('TVA (5.5)');
		$tax3->setTaxVal(5.5,true);
		$t=$tax3->check();
		if (!$t)
		{
			echo "An error occurred while creating a tax object";
			exit;
		}
		
//		//## Affiliates ##
//		// Affiliate who will receive 10% of all the items in the order
//		$aff1 = new HIPAY_MAPI_Affiliate();
//		$aff1->setCustomerId(331);
//		$aff1->setAccountId(59074);
//		$aff1->setValue(10.0,HIPAY_MAPI_TTARGET_ALL);
//		$t=$aff1->check();
//		if (!$t)
//		{
//			echo "An error occurred while creating an affiliate object";
//			exit;
//		}
//		// Affiliate who will receive 15% of the amount of the products, insurance and delivery amounts
//		$aff2 = new HIPAY_MAPI_Affiliate();
//		$aff2->setCustomerId(332);
//		$aff2->setAccountId(59075);
//		$aff2->setValue(15.0,HIPAY_MAPI_TTARGET_ITEM | HIPAY_MAPI_TTARGET_INSURANCE | HIPAY_MAPI_TTARGET_SHIPPING);
//		$t=$aff2->check();
//		if (!$t)
//		{
//			echo "An error occurred while creating an affiliate object";
//			exit;
//		}
		
		// ##Products (order lines) ##
		// First product: 2 copies of a book at 12.5 Euros per unit on which two taxes are applied
		//(taxes $tax3 and $tax2)
		$item1 = new HIPAY_MAPI_Product();
		$item1->setName('The Fall of Hyperion');
		$item1->setInfo('Simmons, Dan – ISBN 0575076380');
		$item1->setquantity(2);
		$item1->setRef('JV005');
		$item1->setCategory(5);
		$item1->setPrice(12.50);
		$item1->setTax(array($tax3,$tax2));
		$t=$item1->check();
		if (!$t)
		{
			echo "An error occurred while creating a product object";
			exit;
		}
		// Second product: An example of a product at 2360 Euros, on which 3 taxes are applied
		//($tax1, $tax2 and $tax3)
		$item2 = new HIPAY_MAPI_Product();
		$item2->setName('PC Linux');
		$item2->setInfo('Computer 445');
		$item2->setquantity(1);
		$item2->setRef('PC445');
		$item2->setCategory(2);
		$item2->setPrice(2360);
		$item2->setTax(array($tax1,$tax2,$tax3));
		$t=$item2->check();
		if (!$t)
		{
			echo "An error occurred while creating a product object";
			exit;
		}
		
		// ## Order object ##
		$order = new HIPAY_MAPI_Order();
		// Order title and information
		$order->setOrderTitle('order on mywebsite.com');
		$order->setOrderInfo('best products');
		// The order category is 3 (Books)
		// Refer to annex 7 to see how to find out what category your site belongs to.
		$order->setOrderCategory(3);
		// The shipping costs are 1.50 Euros excluding taxes, and $tax1 is applied
		$order->setShipping(1.50,array($tax1));
		// The insurance costs are 2 Euros excluding taxes, and $tax1 and $tax3 are applied
		$order->setInsurance(2,array($tax3,$tax1));
		// The fixed costs are 2.25 Euros excluding taxes, and $tax3 is applied to this amount
		$order->setFixedCost(2.25,array($tax3));
		// This order has two affiliates, $aff1 and $aff2
		$order->setAffiliate(array($aff1,$aff2));
		$t=$order->check();
		if (!$t)
		{
			echo "An error occurred while creating a product object";
			exit;
		}
		
		// ## Payment object ##
		try {
			$payment = new HIPAY_MAPI_SimplePayment($params,$order,array($item1,$item2));
		}
		catch (Exception $e) {
			echo "Error" .$e->getMessage();
		}
		
		// ## XML representation of this order and sending the feed to the Hipay platform ## 
		$xmlTx=$payment->getXML();
		$output=HIPAY_MAPI_SEND_XML::sendXML($xmlTx);

		// ## Processing the platform's response ##
		$r=HIPAY_MAPI_COMM_XML::analyzeResponseXML($output, &$url, &$err_msg);
		if ($r===true) {
			// The internet user is sent to the URL indicated by the Hipay platform
			//header('Location: '.$url) ;
			// echo $url;
			return $url;
		} else {
			// Une erreur est intervenue
			echo $err_msg;
			// $url_error = "/error.html";
			//header('Location: '.$url_error) ;
		}
	}
}