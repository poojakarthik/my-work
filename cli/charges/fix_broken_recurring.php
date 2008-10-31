<?php

// Framework
//require_once("../../flex.require.php");
require_once("../../lib/classes/Flex.php");
Flex::load();

// Start a Transaction
DataAccess::getDataAccess()->TransactionStart();

$ubiRecurringCharge	= new StatementUpdateById("RecurringCharge", Array('LastChargedOn'=>NULL));

try
{
	$qryQuery	= new Query();
	
	// Get list of broken Recurring Charges
	$resResult	= $qryQuery->Execute("SELECT RecurringCharge.*, COUNT(Charge.Id) AS ChargeInstances, SUM(Charge.Amount) AS ChargeInstancesTotal, MAX(Charge.ChargedOn) AS ActualLastChargedOn
										FROM RecurringCharge LEFT JOIN Charge USING (Account, ChargeType)
										WHERE LastChargedOn > '2008-09-01' AND Archived = 0
										GROUP BY RecurringCharge.Id
										HAVING ChargeInstances < TotalRecursions");
	while ($arrRecurringCharge = $resResult->fetch_assoc())
	{
		CliEcho("\t + Fixing #{$arrRecurringCharge['Id']}... (LCO: {$arrRecurringCharge['LastChargedOn']};ALCO: {$arrRecurringCharge['ActualLastChargedOn']};\t\t", FALSE);
		
		// Fix the Last Charged On Date
		if (strtotime($arrRecurringCharge['LastChargedOn']) > time())
		{
			$arrRecurringCharge['LastChargedOn']	= date("Y-m-d", strtotime("-1 month", strtotime($arrRecurringCharge['LastChargedOn'])));
		}
		elseif ($arrRecurringCharge['LastChargedOn'] === $arrRecurringCharge['ActualLastChargedOn'])
		{
			$arrRecurringCharge['LastChargedOn']	= date("Y-m-d", strtotime("+1 month", strtotime($arrRecurringCharge['LastChargedOn'])));
		}
		
		CliEcho("NLCO: {$arrRecurringCharge['LastChargedOn']})", FALSE);
		
		// Just charge each person once.  Might not be correct, but it will do
		$objCharge	= new Charge();
		
		$objCharge->AccountGroup		= $arrRecurringCharge['AccountGroup'];
		$objCharge->Account				= $arrRecurringCharge['Account'];
		$objCharge->Service				= $arrRecurringCharge['Service'];
		$objCharge->CreatedBy			= $arrRecurringCharge['CreatedBy'];
		$objCharge->CreatedOn			= $arrRecurringCharge['CreatedOn'];
		$objCharge->ApprovedBy			= $arrRecurringCharge['CreatedBy'];
		$objCharge->ChargeType			= $arrRecurringCharge['ChargeType'];
		$objCharge->Description			= $arrRecurringCharge['Description'];
		$objCharge->ChargedOn			= $arrRecurringCharge['LastChargedOn'];
		$objCharge->Nature				= $arrRecurringCharge['Nature'];
		$objCharge->Notes				= '';
		$objCharge->Status				= CHARGE_APPROVED;
		$objCharge->global_tax_exempt	= FALSE;
		$objCharge->Amount				= $arrRecurringCharge['RecursionCharge'];
		
		// Save the Charge
		$objCharge->save();
		
		CliEcho("[   OK   ]");
	}
	
	throw new Exception("TEST MODE");
	
	// Commit the Transaction
	DataAccess::getDataAccess()->TransactionCommit();
}
catch (Exception $eException)
{
	// Revoke the Transaction
	DataAccess::getDataAccess()->TransactionRollback();
	throw $eException;
}

?>