<?php

// Framework
require_once("../../flex.require.php");

// Statements
$arrAccounts						= Array();
$arrAccounts['Account']				= "Account.Id";
$arrAccounts['CustomerGroup']		= "CustomerGroup.external_name";
$arrAccounts['AccountName']			= "Account.BusinessName";
$arrAccounts['ContactName']			= "CONCAT(Contact.FirstName, ' ', Contact.LastName)";
$arrAccounts['ContactPhone']		= "LPAD(Contact.Phone, 10, '0')";
//$arrAccounts['NotOverdue']		= "SUM(CASE WHEN CURDATE() <= Invoice.DueOn AND Invoice.Status != 106 THEN Invoice.Balance END)";
$arrAccounts['1to29DaysOverdue']		= "SUM(CASE WHEN CURDATE() BETWEEN ADDDATE(Invoice.DueOn, INTERVAL 1 DAY) AND ADDDATE(Invoice.DueOn, INTERVAL 29 DAY) THEN Invoice.Balance END)";
$arrAccounts['30to59DaysOverdue']	= "SUM(CASE WHEN CURDATE() BETWEEN ADDDATE(Invoice.DueOn, INTERVAL 30 DAY) AND ADDDATE(Invoice.DueOn, INTERVAL 59 DAY) THEN Invoice.Balance END)";
$arrAccounts['60to89DaysOverdue']	= "SUM(CASE WHEN CURDATE() BETWEEN ADDDATE(Invoice.DueOn, INTERVAL 60 DAY) AND ADDDATE(Invoice.DueOn, INTERVAL 89 DAY) THEN Invoice.Balance END)";
$arrAccounts['90PlusDaysOverdue']		= "SUM(CASE WHEN CURDATE() >= ADDDATE(Invoice.DueOn, INTERVAL 90 DAY) THEN Invoice.Balance END)";
$arrAccounts['TotalOverdue']		= "SUM(CASE WHEN CURDATE() > Invoice.DueOn THEN Invoice.Balance END)";
//$arrAccounts['TotalOutstanding']	= "SUM(Invoice.Balance)";
$selAccounts		= new StatementSelect(	"((Account LEFT JOIN Invoice ON Account.Id = Invoice.Account) LEFT JOIN CustomerGroup ON Account.CustomerGroup = CustomerGroup.Id) LEFT JOIN Contact ON Account.PrimaryContact = Contact.Id",
											$arrAccounts,
											"Account.Archived IN (".ACCOUNT_STATUS_ACTIVE.", ".ACCOUNT_STATUS_CLOSED.") AND Invoice.Status IN (".INVOICE_COMMITTED.", ".INVOICE_DISPUTED.")",
											"Invoice.Account",
											NULL,
											"Invoice.Account \n HAVING TotalOverdue > 27.0");

$selServices		= new StatementSelect(	"Service",
											"Id AS Service, ServiceType",
											"Account = <Account> AND Status IN (".SERVICE_ACTIVE.", ".SERVICE_DISCONNECTED.")",
											"(ServiceType != 102) DESC, ServiceType");

$selResponses		= new StatementSelect(	"ProvisioningResponse",
											"Type",
											"Service = <Service> AND Type IN (900, 910)",
											"EffectiveDate DESC, ImportedOn DESC",
											"1");

// Timer Variables
$intStartTime	= NULL;
$intCurrentTime	= NULL;
$intLapTime		= NULL;
$intTotalTime	= NULL;

// START REPORT
CliEcho("\n [ CUSTOMERS OWING MONEY REPORTS ] \n");

// Get List of Accounts
CliEcho(" * Retrieving Accounts...\t", FALSE);
$intStartTime		= $intCurrentTime	= time(); 
$intAccountCount	= $selAccounts->Execute();
$intLapTime			= time() - $intCurrentTime;
CliEcho("$intAccountCount found in {$intLapTime}s");
if ($intAccountCount)
{
	// Initialise Output Files
	$strDate			= date("YmdHis");
	$resActiveReport	= fopen("/home/rdavis/active_accounts_owing_{$strDate}.csv", 'w');
	$resChurnedReport	= fopen("/home/rdavis/churned_accounts_owing_{$strDate}.csv", 'w');
	
	// Write File Headers
	fwrite($resActiveReport, '"'.implode('","', array_keys($arrAccounts)).'"'."\n");
	fwrite($resChurnedReport, '"'.implode('","', array_keys($arrAccounts)).'"'."\n");
	
	// For each Account...
	$intAccountInc	= 0;
	while ($arrAccount = $selAccounts->Fetch())
	{
		$intAccountInc++;
		CliEcho("\t + ({$intAccountInc}/{$intAccountCount}){$arrAccount['Account']}...");
		
		// Find the Services for this Account
		$bolActiveReport	= FALSE;
		$intServiceCount	= $selServices->Execute($arrAccount);
		if ($intServiceCount)
		{
			while ($arrService = $selServices->Fetch())
			{
				// Is this Service a LandLine?
				if ($arrService['ServiceType'] === 102)
				{
					// Find the most recent response regarding Full Service status
					$mixResult	= $selResponses->Execute($arrService);
					if ($mixResult)
					{
						// Is the most recent response a gain or a loss?
						$arrResponse	= $selResponses->Fetch();
						if ($arrResponse['Type'] === 900)
						{
							// GAIN, therefore must go on the Active Report
							$bolActiveReport	= TRUE;
							break;
						}
						elseif ($arrResponse['Type'] === 910)
						{
							// LOSS, can still go on the Lost Report
							// Do nothing
						}
						else
						{
							// WTFM8?
							// This should never happen
							CliEcho("Unhandled Response Type '{$arrResponse['Type']}' encountered");
							exit(4);
						}
					}
					elseif ($mixResult === FALSE)
					{
						CliEcho("There was an error with \$selResponses:");
						CliEcho($selResponses->Error());
						exit(3);
					}
					else
					{
						// No Provisioning History
						// Do nothing
					}
				}
				else
				{
					// Not a LandLine, therefore must go on the Active Report
					$bolActiveReport	= TRUE;
					break;
				}
			}
		}
		elseif ($intServiceCount === FALSE)
		{
			CliEcho("There was an error with \$selServices:");
			CliEcho($selServices->Error());
			exit(2);
		}
		else
		{
			// No Services
			// Do nothing
		}
			
		// Which Report will this go on?
		if ($bolActiveReport)
		{
			// Active Report
			fwrite($resActiveReport, '"'.implode('","', $arrAccount).'"'."\n");
		}
		else
		{
			// Lost Report
			fwrite($resChurnedReport, '"'.implode('","', $arrAccount).'"'."\n");
		}
	}
	
	fclose($resActiveReport);
	fclose($resChurnedReport);
	$intTotalTime	= time() - $intStartTime;
	CliEcho("\nReports successfully generated in {$intTotalTime}s");
	exit(0);
}
elseif ($intAccountCount === 0)
{
	CliEcho("There are apparently no Accounts with debt.  If only :'(");
	exit(0);
}
else
{
	CliEcho("There was an error with \$selAccounts:");
	CliEcho($selAccounts->Error());
	exit(1);
}

?>