<?php

#require_once('../../lib/classes/Flex.php');
#Flex::load();

$strSalesPortalBaseURL	= 'http://192.168.2.77/';

//----------------------------------------------------------------------------//
// Start a Session
//----------------------------------------------------------------------------//
$resSession	= curl_init();
curl_setopt($resSession, CURLOPT_RETURNTRANSFER	, true);
curl_setopt($resSession, CURLOPT_SSL_VERIFYPEER	, false);
curl_setopt($resSession, CURLOPT_COOKIESESSION	, true);
curl_setopt($resSession, CURLOPT_COOKIEFILE		, "/dev/null");	// Stores a cookie in memory, but doesn't retain it

//----------------------------------------------------------------------------//
// Log in

// This section is like calling the code:
//		$mixJSONResponse	= login::jsonLogin('rich', 'rich');
$arrFunctionParameters	= array
						(
							'rforrester',		// Username
							'password'		// Password (plain text, not hashed)
						);

$strObject		= 'login';
$strFunction	= 'jsonLogin';
curl_setopt($resSession, CURLOPT_URL			, $strSalesPortalBaseURL."sales/format:json/portal/{$strObject}/{$strFunction}");
curl_setopt($resSession, CURLOPT_POST			, true);
curl_setopt($resSession, CURLOPT_POSTFIELDS		, array('json'=>json_encode($arrFunctionParameters)));

$strResponse		= curl_exec($resSession);
$mixJSONResponse	= json_decode($strResponse);
if ($mixJSONResponse !== true)
{
	throw new Exception("Login failed:\n>>>\n{$strResponse}\n<<<\n".curl_errno($resSession).": ".curl_error($resSession)."\n");
}
echo "Logged In";
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Save Sale
// This section is like calling the code:
//		$mixJSONResponse	= Sale::submit($objSale);

$objSale																				= new stdClass();
$objSale->id																			= null;
$objSale->sale_type_id																	= 1;
$objSale->sale_status_id																= null;
$objSale->created_on																	= null;
$objSale->created_by																	= null;
$objSale->commission_paid_on															= null;

$objSale->sale_account																	= new stdClass();

$objSale->sale_account->id																= null;
$objSale->sale_account->state_id														= 3;
$objSale->sale_account->vendor_id														= 3;
$objSale->sale_account->bill_delivery_type_id											= 2;
$objSale->sale_account->bill_payment_type_id											= 2;
$objSale->sale_account->direct_debit_type_id											= 2;
$objSale->sale_account->sale_account_direct_debit_credit_card_id						= null;
$objSale->sale_account->sale_account_direct_debit_bank_account_id						= null;
$objSale->sale_account->sale_account_direct_debit_bank_account							= null;
$objSale->sale_account->reference_id													= null;
$objSale->sale_account->business_name													= 'Business';
$objSale->sale_account->trading_name													= 'Trading';
$objSale->sale_account->abn																= '84 085 734 992';
$objSale->sale_account->acn																= '069 346 327';
$objSale->sale_account->address_line_1													= 'Line 1';
$objSale->sale_account->address_line_2													= null;
$objSale->sale_account->suburb															= 'Suburb';
$objSale->sale_account->postcode														= '5555';

$objSale->sale_account->sale_account_direct_debit_credit_card							= new stdClass();
$objSale->sale_account->sale_account_direct_debit_credit_card->id						= null;
$objSale->sale_account->sale_account_direct_debit_credit_card->credit_card_type_id		= 1;
$objSale->sale_account->sale_account_direct_debit_credit_card->card_name				= 'Name';
$objSale->sale_account->sale_account_direct_debit_credit_card->card_number				= '4111111111111111';
$objSale->sale_account->sale_account_direct_debit_credit_card->expiry_month				= 2;
$objSale->sale_account->sale_account_direct_debit_credit_card->expiry_year				= 2010;
$objSale->sale_account->sale_account_direct_debit_credit_card->cvv						= 444;


$objSale->contacts																		= array();
$objContact																				= new stdClass();

$objContact->id																			= null;
$objContact->created_on																	= null;
$objContact->contact_title_id															= '';
$objContact->contact_status_id															= null;
$objContact->reference_id																= null;
$objContact->first_name																	= 'Ryan';
$objContact->middle_names																= '';
$objContact->last_name																	= 'Forrester';
$objContact->position_title																= '';
$objContact->username																	= null;
$objContact->password																	= null;
$objContact->date_of_birth																= null;
$objSale->contacts[]																	= $objContact;

$objSale->contacts->contact_methods	= array();
$objContactEmail																		= new stdClass();
$objContactEmail->id																	= null;
$objContactEmail->contact_method_type_id												= 1;
$objContactEmail->details																= 'noemail@telcoblue.com.au';
$objContactEmail->is_primary															= true;
$objContactFax																			= new stdClass();
$objContactFax->id																		= null;
$objContactFax->contact_method_type_id													= 2;
$objContactFax->details																	= '';
$objContactFax->is_primary																= false;
$objContactPhone																		= new stdClass();
$objContactPhone->id																	= null;
$objContactPhone->contact_method_type_id												= 3;
$objContactPhone->details																= '';
$objContactPhone->is_primary															= false;
$objContactMobile																		= new stdClass();
$objContactMobile->id																	= null;
$objContactMobile->contact_method_type_id												= 4;
$objContactMobile->details																= '';
$objContactMobile->is_primary															= false;
$objSale->contacts->contact_methods[]													= $objContactEmail;
$objSale->contacts->contact_methods[]													= $objContactMobile;
$objSale->contacts->contact_methods[]													= $objContactPhone;
$objSale->contacts->contact_methods[]													= $objContactFax;

$objSale->items	= array();
$objSaleItem																			= new stdClass();
$objSaleItem->id																		= null;
$objSaleItem->fnn																		= null;
$objSaleItem->sim_puk																	= null;
$objSaleItem->sim_state_id																= null;
$objSaleItem->dob																		= null;
$objSaleItem->current_provider															= null;
$objSaleItem->current_account_number													= null;
$objSaleItem->service_mobile_origin_id													= 1;
$objSaleItem->comments																	= null;
$objSale->items[]																		= $objSaleItem;


$arrFunctionParameters	= array
						(
							$objSale
						);

$strObject		= 'Sale';
$strFunction	= 'submit';
curl_setopt($resSession, CURLOPT_URL			, $strSalesPortalBaseURL."sales/format:json/portal/{$strObject}/{$strFunction}");
curl_setopt($resSession, CURLOPT_POST			, true);
curl_setopt($resSession, CURLOPT_POSTFIELDS		, array('json'=>json_encode($arrFunctionParameters)));

$strResponse		= curl_exec($resSession);
var_dump($strResponse, true);
flush();
$mixJSONResponse	= json_decode($strResponse);
echo "Request Data:";
var_dump($mixJSONResponse, true);
flush();

//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Log out
curl_setopt($resSession, CURLOPT_URL, $strSalesPortalBaseURL."sales/portal/logout");
curl_setopt($resSession, CURLOPT_POST			, false);
curl_setopt($resSession, CURLOPT_POSTFIELDS		, null);
curl_exec($resSession);
echo "Logged Out?";
flush();

//----------------------------------------------------------------------------//

?>