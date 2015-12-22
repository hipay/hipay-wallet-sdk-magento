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

class HimediaPayments_Hipay_Model_Method_Hipay extends Mage_Payment_Model_Method_Abstract
{
	/**
	 * Der Zahlart-Code muss einmalig sein, und wird zum Auslesen der
	 * System-Konfiguration unter payment/[CODE] verwendet.
	 *
	 * @var string
	 */
	protected $_code = 'hipay';
    protected $_formBlockType = 'hipaymod/form';
	

	/**
	 * Wenn die Zahlung online, also automatisch ausgeführt werden kann muss
	 * der _isGateway Indikator-Flag auf true gesetzt werden.
	 * Um Payment Module zu implementieren, bei denen kein automatisches Capture erfolgt,
	 * die Invoice aber trotzdem als bezahlt markiert wird, müssen _isGateway und
	 * _canCapture auf false gesetzt werden.
	 *
	 * @var bool
	 */
    protected $_isGateway               = false;

	/**
	 * Flag Indikator ob das Zahlart-Model authorize() unterstützt.
	 *
	 * @var bool
	 */
    protected $_canAuthorize            = false;

	/**
	 * Flag Indikator ob das Zahlart-Model capture() unterstützt.
	 *
	 * @var bool
	 */
    protected $_canCapture              = false;

	/**
	 * Flag Indikator ob das Zahlart-Model die Zahlung von Teilbeträgen unterstützt.
	 *
	 * @var bool
	 */
    protected $_canCapturePartial       = false;

	/**
	 * Flag Indikator ob das Zahlart-Model refund() unterstützt.
	 *
	 * @var bool
	 */
    protected $_canRefund               = false;

	/**
	 * Flag Indikator ob das Zahlart-Model die Rückzahlung von Teilbeträgen unterstützt.
	 *
	 * @var bool
	 */
    protected $_canRefundInvoicePartial = false;

	/**
	 * Flag Indikator ob das Zahlart-Model void() unterstützt.
	 *
	 * @var bool
	 */
    protected $_canVoid                 = false;

	/**
	 * Flag Indikator ob das Zahlart-Model bei Bestellungen im Admin-Interface, also
	 * ohne das der Kunde selber anwesend ist, verwendet werden kann (zum Beispiel für
	 * Kreditkarten-Zahlungen bei Bestellungen über das Telefon).
	 *
	 * @var bool
	 */
    protected $_canUseInternal          = true;

	/**
	 * Flag Indikator ob die Zahlart im Onepage Checkout dem Kunden angeboten werden soll.
	 *
	 * @var bool
	 */
    protected $_canUseCheckout          = true;

	/**
	 * Flag Indikator ob das Zahlart-Model im Multishipping Checkout verwendet werden kann.
	 * Es können nur Payment Module im Multishipping Checkout verwendet werden bei denen
	 * der Kunde nicht auf die Seite des Zahlungsanbieters weitergeleitet werden muss.
	 *
	 * @var bool
	 */
    protected $_canUseForMultishipping  = false;

	/**
	 * Flag Indikator ob initialize() auf der Zahlart beim Erstellen der Order
	 * aufgerufen werden soll.
	 *
	 * @var bool
	 */
    protected $_isInitializeNeeded      = false ;

	/**
	* Rückgabe der URL, auf die nach dem Klick auf den 'place order' Button weitergeleitet 
	* werden soll.
	*
	* @return string
	*/
	public function getOrderPlaceRedirectUrl()
	{
		$params = array(
		);
		
		return Mage::getUrl('hipay/mapi/payment', $params);
	}
    
	/**
     * Check whether payment method can be used
     * TODO: payment method instance is not supposed to know about quote
     * @param Mage_Sales_Model_Quote
     * @return bool
     */
	public function isAvailable($quote = null)
    {
        $checkResult = new StdClass;
        //$checkResult->isAvailable = (bool)(int)$this->getConfigData('active', ($quote ? $quote->getStoreId() : null));
        $checkResult->isAvailable = (bool)(int)Mage::getStoreConfig('hipay/general/active', ($quote ? $quote->getStoreId() : null));
        Mage::dispatchEvent('payment_method_is_active', array(
            'result'          => $checkResult,
            'method_instance' => $this,
            'quote'           => $quote,
        ));

        // disable method if it cannot implement recurring profiles management and there are recurring items in quote
        if ($checkResult->isAvailable) {
            $implementsRecurring = $this->canManageRecurringProfiles();
            // the $quote->hasRecurringItems() causes big performance impact, thus it has to be called last
            if ($quote && (!$implementsRecurring) && $quote->hasRecurringItems()) {
                $checkResult->isAvailable = false;
            }
        }
        $available = $checkResult->isAvailable;

    	Mage::log("isAvailable: " . (int)$available);
    	
    	return $available;
    }
    
    /**
     * To check billing country is allowed for the payment method
     *
     * @return bool
     */
    public function canUseForCountry($country)
    {
        /*
        for specific country, the flag will set up as 1
        */
        //if($this->getConfigData('allowspecific')==1){
    	if(Mage::getStoreConfig('hipay/general/allowspecific')==1){
            //$availableCountries = explode(',', $this->getConfigData('specificcountry'));
            $availableCountries = explode(',', Mage::getStoreConfig('hipay/general/specificcountry'));
            if(!in_array($country, $availableCountries)){
                return false;
            }

        }
        return true;
    }
}