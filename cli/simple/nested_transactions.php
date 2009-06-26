<?php

// Flex Framework
require_once('../../lib/classes/Flex.php');
Flex::load();

$dacFlex	= DataAccess::getDataAccess();
$qryQuery	= new Query();

// Add 'transaction_test' Table
$strCreateTableSQL	= "	CREATE TABLE	IF NOT EXISTS	transaction_test
						(
							id		INT UNSIGNED	NOT NULL	AUTO_INCREMENT,
							name	VARCHAR(255)	NOT NULL,
							
							CONSTRAINT	pk_transaction_test_id	PRIMARY KEY	(id)
						)
						ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
if ($qryQuery->Execute($strCreateTableSQL) === false)
{
	throw new Exception($qryQuery->Error());
}
// Truncate 'transaction_test' Table
$strCreateTableSQL	= "	TRUNCATE TABLE	transaction_test;";
if ($qryQuery->Execute($strCreateTableSQL) === false)
{
	throw new Exception($qryQuery->Error());
}

// Rollback non-existent Transaction
Log::getLog()->log("\n[ ] Rollback non-existent Transaction");
Log::getLog()->log("\t[+] Return value: ".print_r($dacFlex->TransactionRollback(), true));

// Commit non-existent Transaction
Log::getLog()->log("\n[ ] Commit non-existent Transaction");
Log::getLog()->log("\t[+] Return value: ".print_r($dacFlex->TransactionCommit(), true));

// Start Transaction #1			DEPTH	= 0
Log::getLog()->log("\n[ ] Start Transaction #1 (DEPTH=0)");
$bResult	= $dacFlex->TransactionStart();
Log::getLog()->log("\t[+] Return value: ".print_r($bResult, true));
if ($bResult === false)
{
	throw new Exception("Unable to Start Transaction");
}

// Add UK to 'transaction_test' Table
Log::getLog()->log("\n[ ] Add UK to 'transaction_test' Table");
$rResult	= $qryQuery->Execute("INSERT INTO transaction_test (name) VALUES ('UK');");
Log::getLog()->log("\t[+] Return value: ".print_r($rResult, true));
if ($rResult === false)
{
	throw new Exception($qryQuery->Error());
}

// Start Transaction #2			DEPTH++	= 1
Log::getLog()->log("\n[ ] Start Transaction #2 (DEPTH=1)");
$bResult	= $dacFlex->TransactionStart();
Log::getLog()->log("\t[+] Return value: ".print_r($bResult, true));
if ($bResult === false)
{
	throw new Exception("Unable to Start Transaction");
}

// Add USA to 'transaction_test' Table
Log::getLog()->log("\n[ ] Add USA to 'transaction_test' Table");
$rResult	= $qryQuery->Execute("INSERT INTO transaction_test (name) VALUES ('USA');");
Log::getLog()->log("\t[+] Return value: ".print_r($rResult, true));
if ($rResult === false)
{
	throw new Exception($qryQuery->Error());
}

// Start Transaction #3			DEPTH++	= 2
Log::getLog()->log("\n[ ] Start Transaction #3 (DEPTH=2)");
$bResult	= $dacFlex->TransactionStart();
Log::getLog()->log("\t[+] Return value: ".print_r($bResult, true));
if ($bResult === false)
{
	throw new Exception("Unable to Start Transaction");
}

// Add NZ to 'transaction_test' Table
Log::getLog()->log("\n[ ] Add NZ to 'transaction_test' Table");
$rResult	= $qryQuery->Execute("INSERT INTO transaction_test (name) VALUES ('NZ');");
Log::getLog()->log("\t[+] Return value: ".print_r($rResult, true));
if ($rResult === false)
{
	throw new Exception($qryQuery->Error());
}

// Commit Transaction #3
Log::getLog()->log("\n[ ] Commit Transaction #3 (DEPTH=2)");
$bResult	= $dacFlex->TransactionCommit();
Log::getLog()->log("\t[+] Return value: ".print_r($bResult, true));
if ($bResult === false)
{
	throw new Exception("Unable to Commit Transaction");
}

// Rollback Transaction #2
Log::getLog()->log("\n[ ] Rollback Transaction #2 (DEPTH=1)");
$bResult	= $dacFlex->TransactionRollback();
Log::getLog()->log("\t[+] Return value: ".print_r($bResult, true));
if ($bResult === false)
{
	throw new Exception("Unable to Rollback Transaction");
}

// Verify 'transaction_test' Table Contents
Log::getLog()->log("\n[ ] Verify 'transaction_test' Table Contents");
$rResult	= $qryQuery->Execute("SELECT * FROM transaction_test;");
Log::getLog()->log("\t[+] Return value: ".print_r(($rResult === false), true));
if ($rResult === false)
{
	throw new Exception($qryQuery->Error());
}
else
{
	$arrVerify	=	array
					(
						array
						(
							'id'	=> 1,
							'name'	=> 'UK'
						)
					);
	
	$arrResultSet	= array();
	while ($arrRow = $rResult->fetch_assoc())
	{
		$arrResultSet[]	= $arrRow;
	}
	
	verifyResultSet($arrVerify, $arrResultSet);
}

// Commit Transaction #1
Log::getLog()->log("\n[ ] Commit Transaction #1 (DEPTH=0)");
$bResult	= $dacFlex->TransactionCommit();
Log::getLog()->log("\t[+] Return value: ".print_r($bResult, true));
if ($bResult === false)
{
	throw new Exception("Unable to Commit Transaction");
}

// Verify Table Contents
Log::getLog()->log("\n[ ] Verify 'transaction_test' Table Contents");
$rResult	= $qryQuery->Execute("SELECT * FROM transaction_test;");
Log::getLog()->log("\t[+] Return value: ".print_r(($rResult === false), true));
if ($rResult === false)
{
	throw new Exception($qryQuery->Error());
}
else
{
	$arrVerify	=	array
					(
						array
						(
							'id'	=> 1,
							'name'	=> 'UK'
						)
					);
	
	$arrResultSet	= array();
	while ($arrRow = $rResult->fetch_assoc())
	{
		$arrResultSet[]	= $arrRow;
	}
	
	verifyResultSet($arrVerify, $arrResultSet);
}

// Start Transaction #4			DEPTH	= 0
Log::getLog()->log("\n[ ] Start Transaction #4 (DEPTH=0)");
$bResult	= $dacFlex->TransactionStart();
Log::getLog()->log("\t[+] Return value: ".print_r($bResult, true));
if ($bResult === false)
{
	throw new Exception("Unable to Start Transaction");
}

// Remove UK from 'transaction_test' Table
Log::getLog()->log("\n[ ] Remove UK from 'transaction_test' Table");
$rResult	= $qryQuery->Execute("DELETE FROM transaction_test WHERE name = 'UK';");
Log::getLog()->log("\t[+] Return value: ".print_r($rResult, true));
if ($rResult === false)
{
	throw new Exception($qryQuery->Error());
}

// Add AUS to 'transaction_test' Table
Log::getLog()->log("\n[ ] Add AUS to 'transaction_test' Table");
$rResult	= $qryQuery->Execute("INSERT INTO transaction_test (name) VALUES ('AUS');");
Log::getLog()->log("\t[+] Return value: ".print_r($rResult, true));
if ($rResult === false)
{
	throw new Exception($qryQuery->Error());
}

// Start Transaction #5			DEPTH	= 1
Log::getLog()->log("\n[ ] Start Transaction #5 (DEPTH=1)");
$bResult	= $dacFlex->TransactionStart();
Log::getLog()->log("\t[+] Return value: ".print_r($bResult, true));
if ($bResult === false)
{
	throw new Exception("Unable to Start Transaction");
}

// Add PNG to 'transaction_test' Table
Log::getLog()->log("\n[ ] Add PNG to 'transaction_test' Table");
$rResult	= $qryQuery->Execute("INSERT INTO transaction_test (name) VALUES ('PNG');");
Log::getLog()->log("\t[+] Return value: ".print_r($rResult, true));
if ($rResult === false)
{
	throw new Exception($qryQuery->Error());
}

// Rollback Transaction #6
Log::getLog()->log("\n[ ] Rollback Transaction #6 (DEPTH=0)");
$bResult	= $dacFlex->TransactionRollback();
Log::getLog()->log("\t[+] Return value: ".print_r($bResult, true));
if ($bResult === false)
{
	throw new Exception("Unable to Rollback Transaction");
}

// Verify Table Contents
Log::getLog()->log("\n[ ] Verify 'transaction_test' Table Contents");
$rResult	= $qryQuery->Execute("SELECT * FROM transaction_test;");
Log::getLog()->log("\t[+] Return value: ".print_r(($rResult === false), true));
if ($rResult === false)
{
	throw new Exception($qryQuery->Error());
}
else
{
	$arrVerify	=	array
					(
						array
						(
							'id'	=> 4,
							'name'	=> 'AUS'
						)
					);
	
	$arrResultSet	= array();
	while ($arrRow = $rResult->fetch_assoc())
	{
		$arrResultSet[]	= $arrRow;
	}
	
	verifyResultSet($arrVerify, $arrResultSet);
}

// Rollback Transaction #4
Log::getLog()->log("\n[ ] Rollback Transaction #4 (DEPTH=0)");
$bResult	= $dacFlex->TransactionRollback();
Log::getLog()->log("\t[+] Return value: ".print_r($bResult, true));
if ($bResult === false)
{
	throw new Exception("Unable to Rollback Transaction");
}

// Remove Test Table
Log::getLog()->log("\n[ ] Verify 'transaction_test' Table Contents");
$rResult	= $qryQuery->Execute("SELECT * FROM transaction_test;");
Log::getLog()->log("\t[+] Return value: ".print_r(($rResult === false), true));
if ($rResult === false)
{
	throw new Exception($qryQuery->Error());
}
else
{
	$arrVerify	=	array
					(
						array
						(
							'id'	=> 1,
							'name'	=> 'UK'
						)
					);
	
	$arrResultSet	= array();
	while ($arrRow = $rResult->fetch_assoc())
	{
		$arrResultSet[]	= $arrRow;
	}
	
	verifyResultSet($arrVerify, $arrResultSet);
}

exit(0);




function verifyResultSet($arrVerify, $arrResultSet)
{
	$intMatches	= 0;
	foreach ($arrResultSet as $arrRow)
	{
		if (in_array($arrRow, $arrVerify))
		{
			$intMatches++;
		}
		else
		{
			Log::getLog()->log("\t\t[!] Row '".implode(print_r($arrRow, true))."' does not exist in expected Result Set");
		}
	}
	
	Log::getLog()->log("\t[+] Verification Result... Expected: ".count($arrVerify)."; Found: ".count($arrResultSet)."; Matches: {$intMatches}");
	if ($intMatches != count($arrResultSet))
	{
		throw new Exception("Table Contents Verification failed");
	}
}

?>