<?php

require_once dirname(__FILE__) . '/../lib/classes/Flex.php';
Flex::load();

$oAccountCreditCards = Query::run("
	SELECT a.Id AS account_id,
		cc.CardNumber AS encrypted_card_number,
		cc.ExpMonth AS expiry_month,
		cc.ExpYear AS expiry_year
	FROM Account a
		JOIN CreditCard cc ON (cc.Id = a.CreditCard)
		LEFT JOIN payment_request pr ON (
			pr.account_id = a.Id
			AND pr.id = (
				SELECT MAX(id)
				FROM payment_request
				WHERE account_id = a.Id
			)
		)
	WHERE a.BillingType = (SELECT id FROM billing_type WHERE system_name = 'CREDIT_CARD')
		AND a.Archived IN (
			SELECT id
			FROM account_status
			WHERE const_name IN (
				'ACCOUNT_STATUS_ACTIVE',
				'ACCOUNT_STATUS_CLOSED'
			)
		)
		AND (
			cc.created_on >= NOW() - INTERVAL 3 MONTH
			OR COALESCE(pr.created_datetime, '0000-00-00 00:00:00') >= NOW() - INTERVAL 6 MONTH
		)
");

while ($aAccountCreditCard = $oAccountCreditCards->fetch_assoc()) {
	// Decrypt Card Number
	echo implode(',', array(
		$aAccountCreditCard['account_id'],
		preg_replace('/[^0-9]/', '', Decrypt($aAccountCreditCard['encrypted_card_number'])),
		$aAccountCreditCard['expiry_month'],
		$aAccountCreditCard['expiry_year']
	)) . "\n";
}