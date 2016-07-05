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

define ('HIPAY_RESULT_PARAM_STATUS',		'status');
define ('HIPAY_RESULT_PARAM_MESSAGE',		'message');
define ('HIPAY_RESULT_PARAM_OPERATION',		'operation');
define ('HIPAY_RESULT_PARAM_DATE',			'date');
define ('HIPAY_RESULT_PARAM_TIME',			'time');
define ('HIPAY_RESULT_PARAM_TRANSID',		'transid');
define ('HIPAY_RESULT_PARAM_AMOUNT',		'origAmount');
define ('HIPAY_RESULT_PARAM_CURRENCY',		'origCurrency');
define ('HIPAY_RESULT_PARAM_MERCHANTID',	'idForMerchant');
define ('HIPAY_RESULT_PARAM_CLIENTEMAIL',	'emailClient');

define ('HIPAY_TOKEN', 'hipay_token_');

class HimediaPayments_Hipay_MapiController extends Mage_Core_Controller_Front_Action
{
    /**
     * Singleton des Checkout Session Models zurückliefern
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Aufruf durch 'orderPlaceRedirect' zum Anzeigen der Weiterleitungsseite 
     */
    public function paymentAction()
    {
    	Mage::log('MapiController > paymentAction');
        
    	try 
        {
        	Mage::log("Modul aktiviert? '" . Mage::getStoreConfig('hipay/general/active') . "'");
        	
            $session = $this->_getCheckout();
            $order = Mage::getModel('sales/order');
            $orderId = $session->getLastRealOrderId();
            $order->loadByIncrementId($orderId);

            if (!$order->getId()) 
            {
            	$paymentUrl = "";
            	$message = Mage::helper('hipaymod')->__('No order for processing found!');
            } 
            else 
            {
	            $order->addStatusHistoryComment(Mage::helper('hipaymod')->__('Hipay payment loaded'), 
	            								Mage_Sales_Model_Order::STATE_PENDING_PAYMENT );
	        	$order->save();
	        	
	        	$paymentUrl = Mage::getUrl("hipay/mapi/redirect/");
	        	$message = Mage::helper('hipaymod')->__('You will be redirected to our secure payment page when you complete your order.');
	        	
	        	Mage::log("order ID: " . $orderId);
	        	Mage::log("payment Url: " . $paymentUrl . $orderId);
            }
                    	
			// Laden der Layout XML
			$this->loadLayout();
			$this->getLayout()->getBlock("hipaypayment.redirect")->assign("paymentUrl", $paymentUrl . $orderId)
																 ->assign("message", $message);
			$this->renderLayout();
        } 
        catch (Exception $e)
        {
            Mage::logException($e);
            parent::_redirect('checkout/cart');
        }
    }

    
    /**
     * Weiterleiten auf hipay
     */
    public function redirectAction()
    {
    	Mage::log('MapiController > redirectAction');
    	
    	try 
    	{
    		Mage::log($_SERVER['REQUEST_URI']);
    		
    		$uri = $_SERVER['REQUEST_URI'];
       		$uriArray = explode("/", $uri);
       		$redirectOrder = trim(end($uriArray));
    		
			$session = $this->_getCheckout();
    		$orderId = $session->getLastRealOrderId();
    		
    		if($orderId == $redirectOrder)
    		{
				$order = Mage::getModel('sales/order');
	        	$order->loadByIncrementId($orderId);
	    		
	        	$token = Mage::helper('hipaymod')->generateToken();
	        	Mage::log("Token: [".$token."]");
	        	$_SESSION['hipay_token_'.$token] = $orderId;
	        	
	        	$resultArray = Mage::helper('hipaymod')->getSinglePaymentUrl($order, $token);
	
	        	$paymentUrl = $resultArray["paymentUrl"];
	        	$errorMsg   = $resultArray["errorMsg"];
	        	
	        	if(!empty($paymentUrl)) 
	        	{
	        		$order->addStatusHistoryComment(Mage::helper('hipaymod')->__('The customer was redirected to Hipay'), 
	        										Mage_Sales_Model_Order::STATE_PENDING_PAYMENT );
	        		$order->save();
	        		
	        		Mage::log("redirect to '".$paymentUrl."'");
	        		parent::_redirectUrl($paymentUrl);	
	        	} 
	        	else 
	        	{
	        		Mage::log("redirect error: '".$errorMsg."'");
	        		$this->_redirect('checkout/cart');
	        	}
    		}
        	else 
        	{
        		Mage::log("Illegal redirect attempt");
        		$this->_redirect('checkout/cart');
        	}
        } 
        catch (Exception $e)
        {
            Mage::logException($e);
            parent::_redirect('checkout/cart');
        }
    }    
    
    
    /**
     * Erfolgreiche Zahlung (URL_OK) verarbeiten. 
     */
    public function successAction()
    {
        Mage::log('MapiController > successAction');
    	
    	try 
    	{
        	if($this->checkToken()) 
        	{       		
        		$this->_redirect('checkout/onepage/success');	
        	}
        	else 
        	{
				Mage::log('Invalid security token');
		        parent::_redirect('checkout/cart');
        	}
        } 
        catch (Exception $e)
        {
            Mage::logException($e);
            parent::_redirect('checkout/cart');
        }
    }

    
    /**
     * Benutzerbedigten Abbruch (URL_CANCEL) verarbeiten. 
     */
    public function cancelAction()
    {
		Mage::log('MapiController > cancelAction');
		
    	try 
    	{    		
    		$postData = $_POST;
        	if($this->checkToken())
        	{
        		#
        		# MAJ JPN le 30/11/2015
        		# Prise en compte de l'annulation dans données POST
        		# Contrôle commande + reinit du panier avec les données préchargées
        		#
	        	if(empty($postData)){
	        		// init des id panier et commande
	        		$lastQuoteId = $this->_getCheckout()->getLastQuoteId();
					$lastOrderId = $this->_getCheckout()->getLastOrderId();
					// init de l'objet panier
	        		$orderModel = Mage::getModel('sales/order')->load($lastOrderId);
			        if($orderModel->canCancel()) {	
			        	// init du panier avec son id		
			            $quote = Mage::getModel('sales/quote')->load($lastQuoteId);
			            // on active le panier et enregistrement
			            $quote->setIsActive(true)->save();
			            // on met la commande en mode cancel
			            $orderModel->cancel();
			            // ajout du status annulée + enregistrement
			            $orderModel->setStatus('canceled');
			            $orderModel->save();
			            // init du message de l'erreur
			            Mage::getSingleton('core/session')->setFailureMsg('order_failed');
			            Mage::getSingleton('checkout/session')->setFirstTimeChk('0');
			            // init du message d'erreur qui s'affichera au redirect sur la page panier
			            Mage::getSingleton('core/session')->addError(__('Your order has been canceled because you have canceled the payment process.'));
			            // on redirige le client sur le panier
			            $this->_redirect('checkout/cart', array("_forced_secure" => true));
			            return;
			        }
	        	}elseif ( $this->requestHasValidPostData()) {
	        		Mage::log('Array POST [');
	        		Mage::log($_POST);
	        		Mage::log(']');
	        		$session = $this->_getCheckout();		
					$order = Mage::getModel('sales/order');
		        	$order->loadByIncrementId($session->getLastRealOrderId());
		        	$order->cancel();
					$order->addStatusHistoryComment(Mage::helper('hipaymod')->__('Payment has been canceled by customer'),
													Mage_Sales_Model_Order::STATE_CANCELED);
		        	$order->save();			        	
		        	$order->sendOrderUpdateEmail(Mage::helper('hipaymod')->__('Your order has been canceled because you have canceled the payment process.'));
		        	$this->_redirect('checkout/onepage/failure');
	        	}
		        else
		        {
		        	Mage::log('Received data could not be validated (invalid hash key!): ' . $xml);
		        	parent::_redirect('checkout/cart');
		        }
        	}
        	else 
        	{
				Mage::log('Invalid security token');
		        parent::_redirect('checkout/cart');
        	}
    	} 
        catch (Exception $e)
        {
            Mage::logException($e);
            parent::_redirect('checkout/cart');
        }
    }


    /**
     * Fehlerbedigten Abbruch (URL_NOK) verarbeiten.
     * 
     *  @todo Fehlermeldung extrahieren und zum Verlauf hinzufügen
     */
    public function failedAction()
    {
    	Mage::log('MapiController > failedAction');
    	
        try 
    	{
    		$postData = $_POST;
        	if($this->checkToken())
        	{
        		#
        		# MAJ JPN le 30/11/2015
        		# Prise en compte de l'annulation dans données POST
        		# Contrôle commande + reinit du panier avec les données préchargées
        		#
	        	if(empty($postData)){
	        		// init des id panier et commande
	        		$lastQuoteId = $this->_getCheckout()->getLastQuoteId();
					$lastOrderId = $this->_getCheckout()->getLastOrderId();
					// init de l'objet panier
	        		$orderModel = Mage::getModel('sales/order')->load($lastOrderId);
			        if($orderModel->canCancel()) {	
			        	// init du panier avec son id		
			            $quote = Mage::getModel('sales/quote')->load($lastQuoteId);
			            // on active le panier et enregistrement
			            $quote->setIsActive(true)->save();
			            // on met la commande en mode cancel
			            $orderModel->cancel();
			            // ajout du status annulée + enregistrement
			            $orderModel->setStatus('canceled');
			            $orderModel->save();
			            // init du message de l'erreur
			            Mage::getSingleton('core/session')->setFailureMsg('order_failed');
			            Mage::getSingleton('checkout/session')->setFirstTimeChk('0');
			            // init du message d'erreur qui s'affichera au redirect sur la page panier
			            Mage::getSingleton('core/session')->addError(__('Your order has been canceled because the payment process failed.'));
			            // on redirige le client sur le panier
			            $this->_redirect('checkout/cart', array("_forced_secure" => true));
			            return;
			        }
	        	}elseif ( $this->requestHasValidPostData()) {
	        		Mage::log('Array POST [');
	        		Mage::log($_POST);
	        		Mage::log(']');
		    		$session = $this->_getCheckout();
		    		
					$order = Mage::getModel('sales/order');
		        	$order->loadByIncrementId($session->getLastRealOrderId());
		        	$order->cancel();
					$order->addStatusHistoryComment(Mage::helper('hipaymod')->__('Hipay payment failed'),
													Mage_Sales_Model_Order::STATE_CANCELED);
		        	$order->save();
		        	
		        	$order->sendOrderUpdateEmail(Mage::helper('hipaymod')->__('Your order has been canceled because the payment process failed.'));
		    	
		        	$this->_redirect('checkout/onepage/failure');
		        }
		        else
		        {
		        	Mage::log('Received data could not be validated (invalid hash key!): ' . $xml);
		        	parent::_redirect('checkout/cart');
		        }
        	}
        	else 
        	{
				Mage::log('Invalid security token');
		        parent::_redirect('checkout/cart');
        	}
    	} 
        catch (Exception $e)
        {
            Mage::logException($e);
            parent::_redirect('checkout/cart');
        }
    }

    
    /**
     * Payment Notification verarbeiten
     */
    public function notificationAction()
    {
    	
    	Mage::log('MapiController > notificationAction');

		try 
		{
	        if($this->requestHasValidPostData())
	        {
				$xml = $_POST['xml'];
				
				$obj = @new SimpleXMLElement(trim($xml));
			
				Mage::log($xml);
				
				if (isset($obj->result[0])) 
				{
					Mage::log("Processing result...");
					
					$result = $obj->result[0];
					
					$operation = $this->getResultValue(HIPAY_RESULT_PARAM_OPERATION);
					if(isset($operation)) {$error = true;}
					
					$status = $this->getResultValue(HIPAY_RESULT_PARAM_STATUS);
					if(isset($status)) {$error = true;}
				 
					$date = $this->getResultValue(HIPAY_RESULT_PARAM_DATE);
					if(isset($date)) {$error = true;}
				 	
					$time = $this->getResultValue(HIPAY_RESULT_PARAM_TIME);
					if(isset($time)) {$error = true;}
				 	
					$transid = $this->getResultValue(HIPAY_RESULT_PARAM_TRANSID);
					if(isset($transid)) {$error = true;}
				 	
					$origAmount = $this->getResultValue(HIPAY_RESULT_PARAM_AMOUNT);
					if(isset($origAmount)) {$error = true;}
				 	
					$origCurrency = $this->getResultValue(HIPAY_RESULT_PARAM_CURRENCY);
					if(isset($origCurrency)) {$error = true;}
				 	
					$idForMerchant = $this->getResultValue(HIPAY_RESULT_PARAM_MERCHANTID);
					if(isset($idForMerchant)) {$error = true;}
				 	
					$emailClient = $this->getResultValue(HIPAY_RESULT_PARAM_CLIENTEMAIL);
					if(isset($emailClient)) {$error = true;}
				 	
					if(isset($idForMerchant))
					{
						Mage::log("has idForMerchant");
						
						$order = Mage::getModel('sales/order');
			        	$order->loadByIncrementId(trim($idForMerchant));
			        	
			        	if(isset($order)) 
			        	{
			        		Mage::log("found Order");
							$historyComment = Mage::helper('hipaymod')->__('Hipay payment notification')     ." - "
															. "OPERATION: '".$operation."', "
															. "STATUS: '".$status."', "
															. "DATE: '".$date." ".$time."', "
															. "TRANSACTION ID: '".$transid."', "
															. "PAID AMOUNT: '".$origAmount." ".$origCurrency."', "
															. "CUSTOMER EMAIL: '".$emailClient."'";
// TODO: Use backend language																						 
//															. Mage::helper('hipaymod')->__('OPERATION')      . ": '".$operation."', "
//															. Mage::helper('hipaymod')->__('STATUS')         . ": '".$status."', "
//															. Mage::helper('hipaymod')->__('DATE')           . ": '".$date." ".$time."', "
//															. Mage::helper('hipaymod')->__('TRANSACTION ID') . ": '".$transid."', "
//															. Mage::helper('hipaymod')->__('PAID AMOUNT')    . ": '".$origAmount." ".$origCurrency."', "
//															. Mage::helper('hipaymod')->__('CUSTOMER EMAIL') . ": '".$emailClient."'";
			        		Mage::log("History Comment: " . $historyComment);

							$order->addStatusHistoryComment($historyComment);
			        		Mage::log("add StatusHistoryComment (notification of ".$operation.")");
			        		
			        		if($operation == "capture")
			        		{
			        			Mage::log("add StatusHistoryComment (capture)");
			        			$order->addStatusHistoryComment(Mage::helper('hipaymod')->__('The customer has successfully paid via Hipay'), 
        														Mage_Sales_Model_Order::STATE_PROCESSING
        														)->setIsCustomerNotified(true);
        						$order->save();
        						Mage::log("saved Order");
        						$order->sendNewOrderEmail();
        						Mage::log("send new Order Mail");
			        		}
							else 
							{
					        	$order->save();
					        	Mage::log("saved Order");
							}
							
			        	}
				 		else {$error = true;}
					}
					Mage::log("Done.");
					
					Mage::log("Error: " . ((int) $error));
					if(isset($error) && $error)
					{
						Mage::log("The received payment notification data is incomplete or incorrect: [" . $xml . "]");
						return false;
					}
				}
			}
			else
			{
				Mage::log('Received data could not be validated (invalid hash key!): ' . $xml);
	        	return false;
			}
    	} catch (Exception $e) {
    		Mage::log("Exception occured");
			Mage::logException($e);
			return false;
		}
    }


    /**
     * Liefert Werte der übermittelten Daten
     * 
     * @param string $name
     */
    private function getResultValue($name)
    {
    	Mage::log("Hipay > getResultValue (".$name.")");
    	
    	if($this->requestHasValidPostData())
        {
			$xml = $_POST['xml'];
			
			$obj = @new SimpleXMLElement(trim($xml));
		
			if (isset($obj->result[0])) 
			{
				$result = $obj->result[0];
				
				if($name == HIPAY_RESULT_PARAM_STATUS && isset($result->status)) {
					$value = $result->status;
				}

				if($name == HIPAY_RESULT_PARAM_MESSAGE && isset($result->message)) {
					$value = $result->message;
				}
				
				if($name == HIPAY_RESULT_PARAM_OPERATION && isset($result->operation)) {
					$value = $result->operation;
				} 
				
				if($name == HIPAY_RESULT_PARAM_DATE && isset($result->date)) {
					$value = $result->date;
				}
			 	
				if($name == HIPAY_RESULT_PARAM_TIME && isset($result->time)) {
					$value = $result->time;
				}
			 	
				if($name == HIPAY_RESULT_PARAM_TRANSID && isset($result->transid)) {
					$value = $result->transid;
				}
			 	
				if($name == HIPAY_RESULT_PARAM_AMOUNT && isset($result->origAmount)) {
					$value = $result->origAmount;
				}
			 	
				if($name == HIPAY_RESULT_PARAM_CURRENCY && isset($result->origCurrency)) {
					$value = $result->origCurrency;
				}
			 	
				if($name == HIPAY_RESULT_PARAM_MERCHANTID && isset($result->idForMerchant)) {
					$value = $result->idForMerchant;
				}
			 	
				if($name == HIPAY_RESULT_PARAM_CLIENTEMAIL && isset($result->emailClient)) {
					$value = $result->emailClient;
				}
				
				if(isset($value)) {
					return $value;
				}
			}
        }
        
        return "?".$name."?";
    }
    
    
    /**
     * Validiert die übermittelten Daten
     *
     * @param Mage_Core_Controller_Request_Http $request
     */
    private function requestHasValidPostData()
    {
//    	Mage::log('MapiController > validateResponse');

    	try 
		{
			if(isset($_POST) && isset($_POST['xml']))
			{
				$xml = $_POST['xml'];
				
				$obj = @new SimpleXMLElement(trim($xml));
			
				if (isset($obj->result[0])) 
				{
					if(isset($obj->md5content)) 
					{
						$md5content = $obj->md5content;
						
						$startPos = strrpos($xml, "<result>");
						$endPos   = strrpos($xml, "</result>")+9;
						$data = substr($xml, $startPos, ($endPos-$startPos));
						$md5 = hash('md5', trim($data));
					}
				}
	
		        if(isset($md5) || isset($md5content) || ($md5 == $md5content))
		        {
//		        	Mage::log("Received data is valid.");
		        	return true;
		        }
		        
		        Mage::log('Received data could not be validated (invalid hash key!): ' . $xml);
		        return false;
			}
			Mage::log('Received data could not be validated (no xml data found).');
						
    	} catch (Exception $e) {
			Mage::logException($e);
		}
    	return false;
    }    
    
    /**
     * Prüft ob der Token vorhanden und valide ist.
     * @param boolean $removeToken Löschen den Token wenn true (default)
     */
    private function checkToken($removeToken = true)
    {
    	$session = $this->_getCheckout();
    	$orderId = $session->getLastRealOrderId();
       	$uri = $_SERVER['REQUEST_URI'];

       	Mage::log($uri);
       	Mage::log("OrderId: " . $orderId);
       	
       	$uriArray = explode("/", $uri);
       	$token = trim(end($uriArray));
       	
       	if(isset($_SESSION[HIPAY_TOKEN.$token])) 
       	{
			$tokenOrder = $_SESSION[HIPAY_TOKEN.$token];       		
			Mage::log("TokenOrderId: " . $tokenOrder);

       		if($removeToken) {
        		unset($_SESSION[HIPAY_TOKEN.$token]);
       		}
       		
	       	return ($orderId == $tokenOrder);
       	}
   		return false;
    }
}

	
