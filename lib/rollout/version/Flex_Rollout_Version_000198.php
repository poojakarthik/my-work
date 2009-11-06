<?php

/**
 * Version 198 of database update.
 * This version: -
 *	
 *	1:	Add the RateGroupRate.effective_start_datetime and effective_end_datetime Fields
 *	2:	Add the discount Table
 *	3:	Add the rate_plan_discount Table
 *	4:	Add the discount_record_type Table
 *
 */

class Flex_Rollout_Version_000198 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the RateGroupRate.effective_start_datetime and effective_end_datetime Fields
		$strSQL = "	ALTER TABLE	RateGroupRate
					ADD			effective_start_datetime					DATETIME	NOT NULL	DEFAULT	'0000-00-00 00:00:00'	COMMENT 'Effective Start Datetime for this relationship',
					ADD			effective_end_datetime						DATETIME	NOT NULL	DEFAULT '9999-12-31 23:59:59'	COMMENT 'Effective End Datetime for this relationship',
					ADD	INDEX	in_rate_group_rate_effective_start_datetime	(effective_start_datetime),
					ADD	INDEX	in_rate_group_rate_effective_end_datetime	(effective_end_datetime);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the RateGroupRate.effective_start_datetime and effective_end_datetime Fields. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	RateGroupRate
									DROP INDEX	in_rate_group_rate_effective_start_datetime,
									DROP INDEX	in_rate_group_rate_effective_end_datetime,
									DROP		effective_start_datetime,
									DROP		effective_end_datetime;";
		
		//	2:	Add the discount Table
		$strSQL = "	CREATE TABLE	discount
					(
						id				BIGINT	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name			VARCHAR(255)		NOT NULL					COMMENT 'Name for the Discount which will appear on the Invoice',
						description		VARCHAR(512)		NULL						COMMENT 'Description for the Discount',
						charge_limit	DECIMAL(13,4)		NULL						COMMENT 'Dollar limit of usage to discount',
						unit_limit		INTEGER				NULL						COMMENT 'Unit limit of usage to discount (takes priority over charge_limit)',
						
						CONSTRAINT	pk_discount_id	PRIMARY KEY	(id)
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the discount Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	discount;";
		
		//	3:	Add the rate_plan_discount Table
		$strSQL = "	CREATE TABLE	rate_plan_discount
					(
						id				BIGINT	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						rate_plan_id	BIGINT	UNSIGNED	NOT NULL					COMMENT '(FK) Rate Plan',
						discount_id		BIGINT	UNSIGNED	NOT NULL					COMMENT '(FK) Discount',
						
						CONSTRAINT	pk_rate_plan_discount_id			PRIMARY KEY	(id),
						CONSTRAINT	fk_rate_plan_discount_rate_plan_id	FOREIGN KEY	(rate_plan_id)	REFERENCES RatePlan(Id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_rate_plan_discount_discount_id	FOREIGN KEY	(discount_id)	REFERENCES discount(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the rate_plan_discount Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	rate_plan_discount;";
		
		//	4:	Add the discount_record_type Table
		$strSQL = "	CREATE TABLE	discount_record_type
					(
						id				BIGINT	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						discount_id		BIGINT	UNSIGNED	NOT NULL					COMMENT '(FK) Discount',
						record_type_id	BIGINT	UNSIGNED	NOT NULL					COMMENT '(FK) Record Type',
						
						CONSTRAINT	pk_discount_record_type_id				PRIMARY KEY	(id),
						CONSTRAINT	fk_discount_record_type_discount_id		FOREIGN KEY	(discount_id)		REFERENCES discount(id)		ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_discount_record_type_record_type_id	FOREIGN KEY	(record_type_id)	REFERENCES RecordType(Id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the discount_record_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	discount_record_type;";
		
	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>