<?php
class Email_Template_Logic_Invoice extends Email_Template_Logic
{


	protected static $_aVariables = array(
									'CustomerGroup'	=>	array(	'external_name'=>"",
															 	'customer_service_phone'=>"",
																'email_domain'=> ""
															),
									'Account'		=>	array(	'id'=>""
															),
									'Invoice'		=>	array(	'created_on'=>"",
															  	'billing_period'=>""
															),
									'Contact'		=>	array(	'first_name'=>""
															)
										);
	static function getVariables()
	{
		return self::$_aVariables;
	}

	function getData($iInvoiceId, $iContactId)
	{
		$aData = self::$_aVariables;
		$oInvoice = Invoice::getForId($iInvoiceId);
		$oInvoiceRun = Invoice_Run::getForId($oInvoice->invoice_run_id);
		$oContact = Contact::getForId($iContactId);
		$oCustomerGroup = Customer_Group::getForId($oInvoiceRun->customer_group_id);


		$iBillingDate			= strtotime($oInvoiceRun->BillingDate);
		$sInvoiceDate			= date('dmY', $iBillingDate);

		// Build billing period string for the email subject lines
		$sBillingPeriodEndMonth		= date("F", strtotime("-1 day", $iBillingDate));
		$sBillingPeriodEndYear		= date("Y", strtotime("-1 day", $iBillingDate));
		$sBillingPeriodStartMonth	= date("F", strtotime("-1 month", $iBillingDate));
		$sBillingPeriodStartYear	= date("Y", strtotime("-1 month", $iBillingDate));
		$sBillingPeriod				= $sBillingPeriodStartMonth;
		if ($sBillingPeriodStartYear !== $sBillingPeriodEndYear)
		{
			$sBillingPeriod	.= " {$sBillingPeriodStartYear} / {$sBillingPeriodEndMonth} {$sBillingPeriodEndYear}";
		}
		else if ($sBillingPeriodStartMonth !== $sBillingPeriodEndMonth)
		{
			$sBillingPeriod	.= " / {$sBillingPeriodEndMonth} {$sBillingPeriodEndYear}";
		}
		else
		{
			$sBillingPeriod	.= " {$sBillingPeriodStartYear}";
		}


		$aData['CustomerGroup']['external_name']= 	$oCustomerGroup->external_name;
		$aData['CustomerGroup']['customer_service_phone'] = $oCustomerGroup->customer_service_phone;
		$aData['CustomerGroup']['email_domain'] = $oCustomerGroup->email_domain;
		$aData['Invoice']['created_on'] = date("F jS, Y", strtotime($oInvoiceRun->BillingDate));
		$aData['Invoice']['billing_period']= $sBillingPeriod;
		$aData['Contact']['first_name'] = $oContact->FirstName;
		$aData['Account']['id'] = $oInvoice->Account;

		return $aData;

	}
}
?>