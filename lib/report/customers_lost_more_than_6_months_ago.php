<?php

require_once("../../flex.require.php");

$arrAccount	= Array();
$arrAccount['Account']			= "Account.Id";
$arrAccount['Business Name']	= "Account.BusinessName";
$arrAccount['Contact']			= "CONCAT(Contact.FirstName, ' ', Contact.LastName)";
$arrAccount['Phone']			= "Contact.Phone";
$arrAccount['Mobile']			= "Contact.Mobile";
$arrAccount['ServiceCount']		= "COUNT(DISTINCT Service.Id)";
$arrAccount['CustomerGroup']	= "CustomerGroup.Name";
$selAccounts	= new StatementSelect(	"((Account JOIN Contact ON Account.PrimaryContact = Contact.Id) JOIN Service ON Account.Id = Service.Account) JOIN CustomerGroup ON Account.CustomerGroup = CustomerGroup.Id",
										$arrAccount,
										"Service.LatestCDR IS NOT NULL",
										"Account.Id",
										NULL,
										"Account.Id");

$selLastNonZeroInvoice	= new StatementSelect(	"Invoice",
												"Id, InvoiceRun, Total, Tax",
												"Account = <Account> AND Total != 0.0",
												"CreatedOn DESC",
												"1");

$selLostServices	= new StatementSelect(	"(Service JOIN ServiceTotal ON Service.Id = ServiceTotal.Service) JOIN RatePlan ON ServiceTotal.RatePlan = RatePlan.Id",
											"Service.FNN AS FNN, RatePlan.Name AS LastPlan",
											"LatestCDR <= SUBDATE(CURDATE(), INTERVAL 6 MONTH) AND Service.Account = <Account> AND ServiceTotal.InvoiceRun = <InvoiceRun>");

// Get list of Accounts
$resOutputFile	= fopen("/home/rdavis/customers_lost_more_than_6_months_ago.csv", 'w');
if ($selAccounts->Execute() === FALSE)
{
	throw new Exception($selAccounts->Error());
}
else
{
	while ($arrAccount = $selAccounts->Fetch())
	{
		// Get the last non-zero Invoice details
		if ($selLastNonZeroInvoice->Execute($arrAccount) === FALSE)
		{
			throw new Exception($selLastNonZeroInvoice->Error());
		}
		if ($arrLastInvoice = $selLastNonZeroInvoice->Fetch())
		{
			// Get List of Services lost 6+ months ago
			if ($intLostServiceCount = $selLostServices->Execute(Array('Account' => $arrAccount['Account'], 'InvoiceRun' => $arrInvoice['InvoiceRun'])))
			{
				// Are all of the Services Lost?
				if ($intLostServiceCount === $arrAccount['ServiceCount'])
				{
					// Yes -- Add this Account & its Services to the CSV file
					while ($arrService = $selLostServices->Fetch())
					{
						$arrCSVLine	= Array(
												$arrAccount['Account'],
												$arrAccount['Business Name'],
												$arrAccount['CustomerGroup'],
												$arrAccount['Contact'],
												str_pad(($arrAccount['Phone']) ? $arrAccount['Phone'] : $arrAccount['Mobile'], 10, '0', STR_PAD_LEFT),
												$arrService['FNN'],
												$arrService['LastPlan'],
												$arrLastInvoice['Total'] + $arrLastInvoice['Tax'],
											);
						fwrite($resOutputFile, '"'.implode('","', $arrCSVLine).'"'."\n");
					}
				}
			}
			elseif ($selLostServices->Error())
			{
				throw new Exception($selLostServices->Error());
			}
			else
			{
				// No Services lost 6+ months ago -- skip this Account
				continue;
			}
		}
		else
		{
			// No last non-zero Invoice -- skip this Account
			continue;
		}		
	}
}

fclose($resOutputFile);

?>