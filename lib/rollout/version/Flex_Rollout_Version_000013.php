<?php

/**
 * Version 13 (thirteen) of database update.
 * This version: -
 *	1:	Alters FileImport.FileType to allow NULLs
 *	2:	Adds FileImport.file_download Foreign Key to the FileDownload table
 *	3:	Adds CarrierModule.description field
 *	4:	Adds 3G Usage RecordType
 *	5:	Adds Payment.carrier Foreign Key to the Carrier table
 *	6 :	Adds carrier_type table, which defines the different types of Carriers Flex Supports (eg. CDR, Payment, etc), Carrier.const_name, Carrier.description, and makes Carrier.Id autoincrement to make it compatible with the new constants framework
 *	7:	Populates carrier_type table
 *	8:	Adds Carrier.carrier_type Foreign Key to the carrier_type table
 *	9:	Adds M2, Westpac BPAY, BillExpress, SecurePay and Payment (temporary) Carriers
 */

class Flex_Rollout_Version_000013 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// Alters FileImport.FileType to allow NULLs
		$strSQL = "ALTER TABLE FileImport CHANGE FileType FileType INT(10) NULL";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to change FileImport.FileType to allow NULLs. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE FileImport CHANGE FileType FileType INT(10) NOT NULL";
		
		// Adds FileImport.file_download Foreign Key to the FileDownload table
		$strSQL = " ALTER TABLE FileImport
						ADD file_download BIGINT(20) NULL COMMENT '(FK) FileDownload record from which this File was Imported from'";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add FileImport.file_download. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE FileImport DROP file_download";
		
		// Adds CarrierModule.description field
		$strSQL = " ALTER TABLE CarrierModule
						ADD description VARCHAR(512) NULL COMMENT 'Description for this instance of the specific Module' AFTER FileType";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add CarrierModule.description. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE CarrierModule DROP description";
		
		// Adds 3G Usage RecordType
		$strSQL = " INSERT INTO RecordType
						(Code, Name, Description, ServiceType, Context, Required, Itemised, GroupId, DisplayType) " .
						"VALUES ('3G', '3G', '3G Data', 101, 0, 0, 1, 0, 3)";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add 3G RecordType. ' . $qryQuery->Error());
		}
		$strSQL 		= " UPDATE RecordType
							SET GroupId = Id WHERE Code = '3G' AND ServiceType = 101;";
		if (!($intInsertId = $qryQuery->Execute($strSQL)))
		{
			throw new Exception(__CLASS__ . ' Failed to Set 3G RecordType\'s GroupId. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM RecordType WHERE Code = '3G' AND ServiceType = 101;";
		
		// Adds Payment.carrier Foreign Key to the Carrier table
		$strSQL = " ALTER TABLE Payment
						ADD carrier BIGINT(20) NULL COMMENT '(FK) Carrier from which this payment came from' AFTER PaidOn";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add Payment.carrier. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE Payment DROP carrier";
		
 		// Adds carrier_type table, which defines the different types of Carriers Flex Supports (eg. CDR, Payment, etc)
		$strSQL	= " CREATE TABLE `carrier_type` (
						`id` BIGINT NOT NULL AUTO_INCREMENT ,
						`name` VARCHAR( 255 ) NOT NULL COMMENT 'Name of this Carrier Type',
						`description` VARCHAR( 512 ) NOT NULL COMMENT 'Description of this Carrier Type',
						`const_name` VARCHAR( 255 ) NOT NULL COMMENT 'Constant name for this Carrier Type',
						PRIMARY KEY ( `id` )
						) ENGINE = InnoDB COMMENT = 'Different Types of Carrier that Flex Supports'";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create carrier_type table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS carrier_type";
		
 		// Populates carrier_type table
		$strSQL = "
			INSERT INTO carrier_type 
			(id, name, description, const_name)
			VALUES
			(NULL, 'Telecom', 'Telecommunications Carrier', 'CARRIER_TYPE_TELECOM'),
			(NULL, 'Payment', 'Payments Carrier', 'CARRIER_TYPE_PAYMENT');
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate carrier_type table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE carrier_type;";
		
		// Adds Carrier.carrier_type Foreign Key to the Carrier table
		$strSQL = " ALTER TABLE Carrier
						ADD carrier_type BIGINT(20) NOT NULL COMMENT '(FK) The type of Carrier', 
						ADD description VARCHAR(255) NOT NULL COMMENT 'Description for this Carrier', 
						ADD const_name VARCHAR(255) NOT NULL COMMENT 'Constant representation of this Carrier', 
						MODIFY Id BIGINT(20) NOT NULL AUTO_INCREMENT";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add new Carrier fields. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE Carrier DROP carrier_type, DROP description, DROP const_name, MODIFY Id BIGINT(20) NOT NULL";
		$strSQL 		= " UPDATE Carrier
							SET carrier_type = (SELECT id FROM carrier_type WHERE name = 'Telecom'), description = Name, const_name = IF(Name = 'Unitel (VoiceTalk)', 'CARRIER_UNITEL_VOICETALK', UCASE(CONCAT('carrier_', Name)))";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to set new Carrier fields. ' . $qryQuery->Error());
		}
		
 		// Adds M2, Westpac BPAY, BillExpress and SecurePay Carriers
		$strSQL = "
			INSERT INTO Carrier 
			(Id, Name, carrier_type, description, const_name)
			VALUES
			(NULL, 'M2', (SELECT id FROM carrier_type WHERE name = 'Telecom' LIMIT 1), 'M2', 'CARRIER_M2'), 
			(NULL, 'BPAY Westpac', (SELECT id FROM carrier_type WHERE name = 'Payment' LIMIT 1), 'BPay Westpac', 'CARRIER_BPAY_WESTPAC'), 
			(NULL, 'BillExpress', (SELECT id FROM carrier_type WHERE name = 'Payment' LIMIT 1), 'BillExpress', 'CARRIER_BILLEXPRESS'), 
			(NULL, 'SecurePay', (SELECT id FROM carrier_type WHERE name = 'Payment' LIMIT 1), 'SecurePay', 'CARRIER_SECUREPAY');
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add new Carriers. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM Carrier WHERE Name IN ('M2', 'BPAY Westpac', 'BillExpress', 'SecurePay');";

		$strSQL = "DELETE FROM ConfigConstant WHERE ConstantGroup IN (SELECT id FROM ConfigConstantGroup WHERE Name = 'Carrier')";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to delete Carrier config constants. ' . $qryQuery->Error());
		}
		$strSQL = "DELETE FROM ConfigConstantGroup WHERE Name = 'Carrier'";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to delete Carrier config constant group. ' . $qryQuery->Error());
		}
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
