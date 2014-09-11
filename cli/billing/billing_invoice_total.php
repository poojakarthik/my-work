<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Reprint invoices for a defined list of accounts
//----------------------------------------------------------------------------//
 
 echo "<pre>\n";

// load framework
LoadFramework();

class InvoiceTotal extends ApplicationBaseClass
{
	function __construct()
	{
		parent::__construct();
		
		// Select all accounts
		$selAccounts = new StatementSelect("Account", "Id", "Archived = 0");
		if (($intCount = $selAccounts->Execute()) === FALSE)
		{
			Debug('$selAccounts died in the ass');
			return;
		}
		$arrAccounts = $selAccounts->FetchAll();
		
		// loop through the accounts
		$fltGrandTotal = 0.0;
		$i = 0;
		$fltLastTime = 0.0;
		foreach ($arrAccounts as $arrAccount)
		{
			$i++;
			$fltLastTime = microtime(TRUE);
			
			echo "+ ($i of $intCount) Working Account #".$arrAccount['Id']."...\t\t\t";
			if (($mixResult = $this->Framework->GetInvoiceTotal($arrAccount['Id'])) === FALSE)
			{
				echo "FAILED!\n";
				continue;
			}
			$fltGrandTotal += (float)$mixResult;
			$fltTimeLapse = microtime() - $fltLastTime;
			$fltLastTime = microtime();
			echo '$'.$fltGrandTotal." ($fltTimeLapse secs)\n";
		}
		
		Debug("Grand Total: $fltGrandTotal (ex. GST)");
		return;
	}
}


$ivtInvoiceTotal = new InvoiceTotal();
die;
?>
