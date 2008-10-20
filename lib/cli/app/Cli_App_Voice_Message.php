<?php

// Note: Suppress errors whilst loading application as there may well be some if the 
// database model files have not yet been generated.
$_SESSION = array();
// Load Flex.php
require_once(dirname(__FILE__) . "/../../../lib/classes/Flex.php");
Flex::load();

class Cli_App_Voice_Message extends Cli
{
	public function run()
	{
		try
		{
			self::runVT();
			self::runTB();
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());
		}
	}
	
	public function runVT()
	{
		self::doForCG(2, "Your Voice talk account remains unpaid and suspension of your services will commence on the 22nd of October. To avoid suspension of your services please forward payment as soon as possible.", FILES_BASE_PATH . "/voicetalk.csv");
	}
	
	public function runTB()
	{
		self::doForCG(1, "Your Tel coe blue account remains unpaid and suspension of your services will commence on the 22nd of October. To avoid suspension of your services please forward payment as soon as possible.", FILES_BASE_PATH . "/telco_blue.csv");
	}
	
	public function doForCG($cg, $message, $name)
	{
		$this->log("Processing customer group $cg.");
		$intEffectiveTime = mktime(0,0,0,date('m'),(date('d')+2),date('Y'));
		$this->log("Effective date: " . date("Y-m-d H:i:s", $intEffectiveTime));
		$contacts = $this->listBarrableAccounts($intEffectiveTime, $cg);
		$this->log("Found " . count($contacts) . " accounts for customer group $cg.");
		
		$f = fopen($name, 'w+');
		$first = true;

		foreach ($contacts as $contact)
		{
			$accountId = self::getName($contact["AccountId"]);
			//$contactName = self::getName(trim($contact["FirstName"]));
			//$amount = Flex::framework()->GetOverdueBalance($contact["AccountId"]);

			$nr = preg_replace("/[^0-9]+/", '', $contact["Phone"]);
			
			$l = strlen($nr);
			
			$crap = false;
			
			if ($l > 10 || $l < 9)
			{
				$crap = true;
			}
			if ($l == 9)
			{
				$nr = '0' . $nr;
				$l = 10;
			}
			if ($nr[0] != '0')
			{
				$crap = true;
			}
			if (!$crap && $nr[1] == '4')
			{
				$crap = true;
			}


//			$amountx = explode('.', $amount);
//			if (count($amountx) > 1)
//			{
//				$amount = $amountx[0] . ' and ';
//				$cent = $amountx[1] . '0000000000000';
//				$cent = substr($cent, 0, 2);
//				$amount .= $cent . ' cents';
//			}
			
			$output = $nr . ',' . $message;
			if ($crap)
			{
				$output = $accountId . ',' . $message;
			}
			//$output = str_replace('[name]', $contactName, $output);
			//$output = str_replace('[amount]', $amount, $output);
			if ($crap && !$fx)
			{
				$fx = fopen($name.'.bad_phone_numbers', 'w+');
			}
			fwrite($crap ? $fx : $f, "\r\n".$output);
		}
		fclose($f);
		if ($fx)
		{
			fclose($fx);
		}
		
		$contents = explode("\r\n", trim(file_get_contents($name)));
		sort($contents);
		$f = fopen($name, 'w+');
		fwrite($f, implode("\r\n", $contents) . "\r\n");
	}
	
	public static function getName($firstName)
	{
		return $firstName ? $firstName : 'customer';
	}

	public function listBarrableAccounts($intEffectiveTime, $customerGroupId)
	{
		$db = Data_Source::get();
		$res = $db->query("SELECT DISTINCT(invoice_run_id) FROM automated_invoice_run_process WHERE completed_date IS NULL");
		if (PEAR::isError($res))
		{
			throw new Exception("Failed to list invoice runs: " . $res->getMessage());
		}
		$arrInvoiceRuns = $res->fetchCol();
	
		if (!$intEffectiveTime)
		{
			$intEffectiveTime = time();
		}
	
		// First, we need to find which invoice runs are involved (if any)
		$nr = count($arrInvoiceRuns);
		$this->log("Found " . $nr . " invoice runs for customer group $customerGroupId.");
		if (!$nr)
		{
			// No invoice runs, so no accounts
			return array();
		}
		
		$strInvoiceRuns = "InvoiceRun.Id IN (" . implode(', ', $arrInvoiceRuns) . ")";
	
		$strEffectiveDate = date("'Y-m-d'", $intEffectiveTime);
	
		$strApplicableAccountStatuses = implode(", ", array(ACCOUNT_STATUS_ACTIVE, ACCOUNT_STATUS_CLOSED, ACCOUNT_STATUS_SUSPENDED));
		$strApplicableInvoiceStatuses = implode(", ", array(INVOICE_COMMITTED, INVOICE_DISPUTED, INVOICE_PRINT));
	
		$arrColumns = array(
								'AccountId'				=> "Invoice.Account",
								//'AccountGroupId'		=> "Account.AccountGroup",
								'Phone'					=> "Contact.Phone",
								//'FirstName'				=> "Contact.FirstName",
								//'LastName'				=> "Contact.LastName",
								'Overdue'				=> "SUM(CASE WHEN $strEffectiveDate > Invoice.DueOn THEN Invoice.Balance END)",
								'minBalanceToPursue'	=> "payment_terms.minimum_balance_to_pursue",
		);
	
		$strTables	= "
				 Invoice 
			JOIN Account 
			  ON Invoice.Account = Account.Id
			 AND Account.Archived IN ($strApplicableAccountStatuses) 
			 AND NOT Account.automatic_barring_status = " . AUTOMATIC_BARRING_STATUS_BARRED . " 
			 AND (Account.LatePaymentAmnesty IS NULL OR Account.LatePaymentAmnesty < $strEffectiveDate)
			 AND Account.CustomerGroup = $customerGroupId
			 AND Account.BillingType = " . BILLING_TYPE_ACCOUNT . "
			JOIN credit_control_status 
			  ON Account.credit_control_status = credit_control_status.id
			 AND credit_control_status.can_bar = 1
			JOIN account_status 
			  ON Account.Archived = account_status.id
			 AND account_status.can_bar = 1
			JOIN CustomerGroup 
			  ON Account.CustomerGroup = CustomerGroup.Id
			JOIN payment_terms
			  ON payment_terms.customer_group_id = Account.CustomerGroup 
			JOIN Contact 
			  ON Account.PrimaryContact = Contact.Id";
	
		$strWhere	= "Account.Id IN (
			SELECT DISTINCT(Account.Id) 
			FROM InvoiceRun 
			JOIN Invoice
			  ON InvoiceRun.Id IN (SELECT DISTINCT(invoice_run_id) FROM automated_invoice_run_process WHERE completed_date IS NULL)
			 AND Invoice.Status IN ($strApplicableInvoiceStatuses) 
			 AND InvoiceRun.Id = Invoice.invoice_run_id
			JOIN Account 
			  ON Account.Id = Invoice.Account
			 AND Account.CustomerGroup = $customerGroupId
			 AND Account.Archived IN ($strApplicableAccountStatuses) 
			 AND Account.BillingType = " . BILLING_TYPE_ACCOUNT . "
			 AND (Account.LatePaymentAmnesty IS NULL OR Account.LatePaymentAmnesty < $strEffectiveDate)
			 AND NOT Account.automatic_barring_status = " . AUTOMATIC_BARRING_STATUS_BARRED . " 
			JOIN credit_control_status 
			  ON Account.credit_control_status = credit_control_status.id
			 AND credit_control_status.can_bar = 1
			JOIN account_status 
			  ON Account.Archived = account_status.id
			 AND account_status.can_bar = 1
		)";
	
		$strGroupBy	= "Invoice.Account HAVING Overdue >= minBalanceToPursue";
		$strOrderBy	= "Invoice.Account ASC";
	
		$select = array();
		foreach($arrColumns as $alias => $column) $select[] = "$column '$alias'";
		$strSQL = "SELECT " . implode(",\n       ", $select) . "\nFROM $strTables\nWHERE $strWhere\nGROUP BY $strGroupBy\nORDER BY $strOrderBy\n\n";
		
		$db = Data_Source::get();
		$res = $db->query($strSQL);
		if (PEAR::isError($res))
		{
			$this->log("\n\n$strSQL\n\n");
			throw new Exception("Failed to load contact details for barring: " . $res->getMessage());
		}
		return $res->fetchAll(MDB2_FETCHMODE_ASSOC);
	}
}



?>
