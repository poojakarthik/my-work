<?php

/**
 * Version 52 of database update.
 * This version: -
 *	1:	Add new columns to CustomerGroup table 
 */

class Flex_Rollout_Version_000052 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Add new columns to CustomerGroup table 
		$strSQL = "ALTER TABLE CustomerGroup 
					ADD bill_pay_biller_code INT(5) DEFAULT NULL COMMENT 'BillPay biller code (a 5 digit number)',
					ADD abn CHAR(11) DEFAULT NULL COMMENT 'ABN of business (11 digits)',
					ADD acn CHAR(9) DEFAULT NULL COMMENT 'ACN of company (9 digits)',
					ADD business_phone varchar(50) DEFAULT NULL COMMENT 'Phone number for business',
					ADD business_fax varchar(50) DEFAULT NULL COMMENT 'Fax number for business',
					ADD business_web varchar(255) DEFAULT NULL COMMENT 'URL of customer group external website',
					ADD business_contact_email varchar(255) DEFAULT NULL COMMENT 'Phone number for general enquiries and first contact',
					ADD business_info_email varchar(255) DEFAULT NULL COMMENT 'Email info email address',
					ADD customer_service_phone varchar(50) DEFAULT NULL COMMENT 'Phone number of customer service department',
					ADD customer_service_email varchar(255) DEFAULT NULL COMMENT 'Email address of customer service department',
					ADD customer_service_contact_name varchar(255) DEFAULT NULL COMMENT 'Contact name in customer service department',
					ADD business_payable_name varchar(255) DEFAULT NULL COMMENT 'Name payments should be made out to',
					ADD business_payable_address varchar(255) DEFAULT NULL COMMENT 'Address postal payments should mailed to',
					ADD business_friendly_name varchar(255) DEFAULT NULL COMMENT 'Friendly name for business',
					ADD business_friendly_name_possessive varchar(255) DEFAULT NULL COMMENT 'Possessive name (e.g. MyCo\'\'s payment plan...)',
					ADD credit_card_payment_phone varchar(50) DEFAULT NULL COMMENT 'Phone number customers call to pay by credit card',
					ADD faults_phone varchar(50) DEFAULT NULL COMMENT 'Phone number customers call to report faults'
					";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add columns to CustomerGroup table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE CustomerGroup 
					DROP bill_pay_biller_code,
					DROP abn,
					DROP acn,
					DROP business_phone,
					DROP business_fax,
					DROP business_web,
					DROP business_contact_email,
					DROP business_info_email,
					DROP customer_service_phone,
					DROP customer_service_email,
					DROP customer_service_contact_name,
					DROP business_payable_name,
					DROP business_payable_address,
					DROP business_friendly_name,
					DROP business_friendly_name_possessive,
					DROP credit_card_payment_phone,
					DROP faults_phone
					;";
		
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
