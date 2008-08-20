<?php

/**
 * Version 32 of database update.
 * This version: -
 *	1:	Create credit_card_payment_config table
 *	2:	Create credit_card_payment_history table
 *	3:	Create credit_card_type table
 *	4:	Populate credit_card_type table
 */

class Flex_Rollout_Version_000032 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Create credit_card_payment_config table
		$strSQL = "
				CREATE TABLE credit_card_payment_config 
				(
					id BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
					customer_group_id BIGINT( 20 ) UNSIGNED NOT NULL COMMENT 'FK to CustomerGroup table',
					merchant_id VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Merchant Id (E.g. Secure Pay Merchant Id)',
					password VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Merchant Password',
					confirmation_text MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Body of message sent to confirm payment',
					direct_debit_text MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Body of message sent to confirm direct debit setup',
					PRIMARY KEY (id)
				) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = 'Details of credit card payment config for customer groups' ";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create credit_card_payment_config table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE credit_card_payment_config";

		// 2:	Create credit_card_payment_history table
		$strSQL = "
				CREATE TABLE credit_card_payment_history 
				(
					id BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
					account_id BIGINT( 20 ) UNSIGNED NOT NULL COMMENT 'FK to Account table',
					employee_id BIGINT( 20 ) UNSIGNED NOT NULL COMMENT 'FK to Employee table',
					contact_id BIGINT( 20 ) UNSIGNED DEFAULT NULL COMMENT 'FK to Contact table',
					receipt_number VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Receipt number for payment',
					amount decimal(13,4) default NULL,
					payment_datetime datetime default NULL COMMENT 'Date/Time of the payment',
					PRIMARY KEY (id)
				) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = 'Details of credit card payments made' ";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create credit_card_payment_history table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE credit_card_payment_history";

		// 3:	Create credit_card_type table
		$strSQL = "
				CREATE TABLE credit_card_type (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id for the table',
					name varchar(50) NOT NULL COMMENT 'Name of the card',
					description varchar(255) NOT NULL COMMENT 'Description of the card',
					const_name varchar(255) NOT NULL COMMENT 'The constant name',
					surcharge decimal(6,3) default NULL COMMENT 'The percentage surcharge added to payments made by this card type',
					valid_lengths varchar(255) NOT NULL COMMENT 'CSL of valid card number lengths',
					valid_prefixes varchar(255) NOT NULL COMMENT 'CSL of valid card number prefixes',
					cvv_length TINYINT NOT NULL COMMENT 'Length of CVV number for card type',
					minimum_amount decimal(13,4) NOT NULL COMMENT 'Minimum value of a credit card transaction',
					maximum_amount decimal(13,4) NOT NULL COMMENT 'Maximum value of a credit card transaction',
					PRIMARY KEY (id),
					UNIQUE KEY const_name (const_name)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Credit card types';
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create credit_card_type table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE credit_card_type";

		// 4:	Populate credit_card_type table
		$strSQL = "
				INSERT INTO credit_card_type (name, description, const_name, surcharge, valid_lengths, valid_prefixes, cvv_length, minimum_amount, maximum_amount) VALUES
				('VISA', 				'VISA', 			'CREDIT_CARD_TYPE_VISA', 		0.015,	'13,16', 	'4', 				3,	5.00, 	10000.00),
				('MasterCard', 			'MasterCard', 		'CREDIT_CARD_TYPE_MASTERCARD', 	0.015,	'16', 		'51,52,53,54,55',	3,	5.00, 	10000.00),
				('Bankcard', 			'Bankcard', 		'CREDIT_CARD_TYPE_BANKCARD', 	0.015,	'16', 		'56',				3,	5.00, 	10000.00),
				('American Express', 	'American Express', 'CREDIT_CARD_TYPE_AMEX', 		0.030,	'15', 		'34,37',			4,	5.00, 	10000.00),
				('Diners Club', 		'Diners Club', 		'CREDIT_CARD_TYPE_DINERS', 		0.030,	'14', 		'30,36,38',			3,	5.00, 	10000.00)
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate credit_card_type table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE credit_card_type";
	}

	function rollback()
	{
		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);
				if (!$qryQuery->Execute($this->rollbackSQL[$l]))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}

?>
