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

$_mapi_dir=dirname(__FILE__);
require_once($_mapi_dir.'/mapi_defs.php');

require_once($_mapi_dir.'/mapi_defs_ext.php');

require_once($_mapi_dir.'/mapi_utils.php');
require_once($_mapi_dir.'/mapi_utf8.php');
require_once($_mapi_dir.'/mapi_xml.php');
require_once($_mapi_dir.'/mapi_send_xml.php');
require_once($_mapi_dir.'/mapi_comm_xml.php');
require_once($_mapi_dir.'/mapi_lockable.php');

require_once($_mapi_dir.'/mapi_tax.php');
require_once($_mapi_dir.'/mapi_affiliate.php');
require_once($_mapi_dir.'/mapi_item.php');
require_once($_mapi_dir.'/mapi_installment.php');
require_once($_mapi_dir.'/mapi_product.php');
require_once($_mapi_dir.'/mapi_paymentparams.php');
require_once($_mapi_dir.'/mapi_order.php');
require_once($_mapi_dir.'/mapi_payment.php');
require_once($_mapi_dir.'/mapi_multiplepayment.php');
require_once($_mapi_dir.'/mapi_simplepayment.php');
?>