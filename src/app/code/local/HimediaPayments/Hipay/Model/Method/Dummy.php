<?php

class HimediaPayments_Hipay_Model_Method_Dummy extends Mage_Payment_Model_Method_Abstract
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
    protected $_canUseForMultishipping  = true;

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
    
}