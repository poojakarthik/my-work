<?php

/**
 * Version 123 of database update.
 * This version: -
 *	1:	Change PCAD - Plan Charge in Advance to PCAD - Plan Charge and PCAR - Plan Charge in Arrears to PCAR - Plan Charge
 *	2:	Change PCR - Plan Credit in Arrears to PCR - Plan Usage
 *	3:	Change Voicemail Setup & Retrieval to Voicemail Retrieval
 */

class Flex_Rollout_Version_000123 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Change PCAD - Plan Charge in Advance to PCAD - Plan Charge and PCAR - Plan Charge in Arrears to PCAR - Plan Charge
		$strSQL = "UPDATE ChargeType SET Description = 'Plan Charge' WHERE ChargeType IN ('PCAD', 'PCAR');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to change PCAD - Plan Charge in Advance to PCAD - Plan Charge and PCAR - Plan Charge in Arrears to PCAR - Plan Charge. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "UPDATE ChargeType SET Description = 'Plan Charge in Advance' WHERE ChargeType = 'PCAD'";
		$this->rollbackSQL[] = "UPDATE ChargeType SET Description = 'Plan Charge in Arrears' WHERE ChargeType = 'PCAR'";
		
		// 2:	Change PCR - Plan Credit in Arrears to PCR - Plan Usage
		$strSQL = "UPDATE ChargeType SET Description = 'Plan Usage' WHERE ChargeType = 'PCR';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to change PCR - Plan Credit in Arrears to PCR - Plan Usage ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "UPDATE ChargeType SET Description = 'Plan Credit in Arrears' WHERE ChargeType = 'PCR'";
		
		// 3:	Change Voicemail Setup & Retrieval to Voicemail Retrieval
		$strSQL = "UPDATE RecordType SET Description = 'Mobile VoiceMail Retrieval' WHERE Code = 'VoiceMailRetrieval' AND ServiceType = 101;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to change Voicemail Setup & Retrieval to Voicemail Retrieval ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "UPDATE RecordType SET Description = 'Mobile VoiceMail Setup/Retrieval' WHERE Code = 'VoiceMailRetrieval' AND ServiceType = 101;";
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