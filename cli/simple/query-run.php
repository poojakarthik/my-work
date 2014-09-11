<?php

require_once(dirname(__FILE__).'/../../lib/classes/Flex.php');
Flex::load();

// Run a simple query
$mSimpleQueryResult	= Query::run("SELECT * FROM Account WHERE 1 ORDER BY Id DESC LIMIT 1");
$aAccount	= $mSimpleQueryResult->fetch_assoc();
$oAccount	= new Account($aAccount);

// Run a variable query
$mVariableQueryResult	= Query::run("	SELECT	<account_id>		AS account_id,		/* integer */
												<account_name>		AS account_name,	/* string */
												<balance>			AS balance,			/* float */
												<overdue>			AS overdue,			/* float returned by Callback */
												<is_active>			AS is_active,		/* boolean */
												<services>			AS services,		/* array-to-string */
												<primary_contact>	AS primary_contact,	/* ORM-to-string */
												<payment_method>	AS payment_method,	/* function-to-stdClass-to-string */
												<nulled_out>		AS nulled_out		/* null */
", array(
	'account_id'		=> (int)$oAccount->Id,
	'account_name'		=> $aAccount['BusinessName'],
	'balance'			=> $oAccount->getAccountBalance(),
	'overdue'			=> Callback::create('getOverdueBalance', $oAccount),
	'is_active'			=> !!($oAccount->Archived === 0),
	'services'			=> array('0408199295', '0733539872', true, 1.2, 88, null),
	'primary_contact'	=> Contact::getForId($oAccount->PrimaryContact),
	'payment_method'	=> Callback::create('toStdClass', Billing_Type::getForId($oAccount->BillingType)->getPaymentMethod()),
	'nulled_out'		=> null,
	'# invalid field #'	=> 'some string',
	999					=> 'Integer Key',
	'non_existent'		=> 'Non-existent Field'
));
Log::getLog()->log(print_r($mVariableQueryResult->fetch_assoc(), true));

// Intentionally invoke an error
Query::run("SELECT a toaster for fun and paint it with cheese.");

?>
