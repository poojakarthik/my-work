<?php

require_once(dirname(__FILE__).'/../../lib/classes/Flex.php');
Flex::load();

// Statement Select
// TODO

// Query
$sQuery		= "
SELECT		a.*,
			".ORM::getORMSelect('Invoice', 'i_first').",
			".ORM::getORMSelect('Invoice', 'i_last').",
			".ORM::getORMSelect('Account_Status')."

FROM		Account a
			JOIN Invoice i_first ON (a.Id = i_first.Account AND i_first.Id = (SELECT Id FROM Invoice WHERE Account = a.Id ORDER BY CreatedOn ASC, Id ASC LIMIT 1))
			JOIN Invoice i_last ON (a.Id = i_last.Account AND i_last.Id = (SELECT Id FROM Invoice WHERE Account = a.Id ORDER BY CreatedOn DESC, Id DESC LIMIT 1))
			JOIN account_status ON (a.Archived = account_status.id)
			
WHERE		a.Id IN (1000154811, 1000160069, 1000154803);
";

$oQuery	= new Query();
if (false === ($oResult = $oQuery->Execute($sQuery)))
{
	throw new Exception_Database($oQuery->Error());
}

while ($aRow = $oResult->fetch_assoc())
{
	CliEcho(print_r(array('RAW'=>$aRow, 'PARSED'=>ORM::parseORMResult($aRow)), true));
}

CliEcho('DONE');
exit(0);

?>