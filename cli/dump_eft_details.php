<?php

require_once dirname(__FILE__) . '/../lib/classes/Flex.php';
Flex::load();

$oAccountEFTs = Query::run("
	SELECT a.Id AS account_id,
		TRIM(dd.BankName) AS bank_name,
		dd.BSB AS bank_account_bsb,
		dd.AccountNumber AS bank_account_number,
		TRIM(dd.AccountName) AS bank_account_name
	FROM Account a
		JOIN DirectDebit dd ON (dd.Id = a.DirectDebit)
		LEFT JOIN payment_request pr ON (
			pr.account_id = a.Id
			AND pr.id = (
				SELECT MAX(id)
				FROM payment_request
				WHERE account_id = a.Id
			)
		)
	WHERE a.BillingType = (SELECT id FROM billing_type WHERE system_name = 'DIRECT_DEBIT')
		AND a.Archived IN (
			SELECT id
			FROM account_status
			WHERE const_name IN (
				'ACCOUNT_STATUS_ACTIVE',
				'ACCOUNT_STATUS_CLOSED'
			)
		)
		AND (
			dd.created_on >= NOW() - INTERVAL 3 MONTH
			OR COALESCE(pr.created_datetime, '0000-00-00 00:00:00') >= NOW() - INTERVAL 6 MONTH
		)
");

while ($aAccountEFT = $oAccountEFTs->fetch_assoc()) {
	// Dump details
	echo implode(',', $aAccountEFT) . "\n";
}