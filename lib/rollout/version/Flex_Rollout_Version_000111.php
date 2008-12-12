<?php

/**
 * Version 111 of database update.
 * This version: -
 *	1:	Add new Carrier Type for Sales Call Centres
 *	2:	Add Yellow Call Centre, Insel, and OTHER INDIAN CALL CENTRE Carriers
 *	3:	Add Resource Types for INDIAN CALL CENTRE and ACMA Do Not Call Register Import/Export files
 */

class Flex_Rollout_Version_000111 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add new Carrier Type for Sales Call Centres
		$strSQL = "INSERT INTO carrier_type (name, description, const_name) VALUES
					('Sales Call Centre', 'Sales Call Centre', 'CARRIER_TYPE_SALES_CALL_CENTRE')";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add new Carrier Type for Outbound Call Centres. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DELETE FROM carrier_type WHERE const_name = 'CARRIER_TYPE_SALES_CALL_CENTRE';";
		
		// 2:	Add Yellow Call Centre, Insel, and OTHER INDIAN CALL CENTRE Carriers
		$strSQL = "INSERT INTO Carrier (Name, carrier_type, description, const_name) VALUES
					('Yellow Call Centre', (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_SALES_CALL_CENTRE'), 'Yellow Call Centre', 'CARRIER_YELLOW_CALL_CENTRE'),
					('Insel', (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_SALES_CALL_CENTRE'), 'Insel', 'CARRIER_INSEL'),
					('Indian Call Centre', (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_SALES_CALL_CENTRE'), 'Indian Call Centre', 'CARRIER_INDIAL_CALL_CENTRE')";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add Yellow Call Centre, Insel, and OTHER INDIAN CALL CENTRE Carriers. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DELETE FROM Carrier WHERE const_name IN ('CARRIER_YELLOW_CALL_CENTRE', 'CARRIER_INSEL', 'CARRIER_INDIAL_CALL_CENTRE');";
		
		// 3:	Add Resource Types for INDIAN CALL CENTRE and ACMA Do Not Call Register Import/Export files
		$strSQL = "INSERT INTO resource_type (name, description, const_name, resource_type_nature) VALUES
					('INDIAN CC Proposed Dialling List'		, 'INDIAN CC Proposed Dialling List'	, 'RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_INDIAN_PROPOSED_DIALLING_LIST'	, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')),
					('ACMA DNCR Request'					, 'ACMA Do Not Call Register Request'	, 'RESOURCE_TYPE_FILE_EXPORT_TELEMARKETING_ACMA_DNCR_REQUEST'				, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE')),
					('ACMA DNCR Response'					, 'ACMA Do Not Call Register Response'	, 'RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_ACMA_DNCR_RESPONSE'				, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')),
					('INDIAN CC Permitted Dialling List'	, 'INDIAN CC Permitted Dialling List'	, 'RESOURCE_TYPE_FILE_EXPORT_TELEMARKETING_INDIAN_PERMITTED_DIALLING_LIST'	, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE')),
					('INDIAN CC Dialler Report'				, 'INDIAN CC Dialler Report'			, 'RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_INDIAN_DIALLER_REPORT'			, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE'))";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add new Telemarketing File Types. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DELETE FROM resource_type WHERE const_name IN (" .
								"'RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_INDIAN_PROPOSED_DIALLING_LIST', " .
								"'RESOURCE_TYPE_FILE_EXPORT_TELEMARKETING_ACMA_DNCR_REQUEST', " .
								"'RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_ACMA_DNCR_RESPONSE', " .
								"'RESOURCE_TYPE_FILE_EXPORT_TELEMARKETING_INDIAN_PERMITTED_DIALLING_LIST', " .
								"'RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_INDIAN_DIALLER_REPORT');";
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