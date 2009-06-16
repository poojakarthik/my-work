<?php

/**
 * Version 178 of database update.
 * This version: -
 *	
 *	1:	Add CARRIER_SOUVIK and CARRIER_THE_PHONE_BROKER records to the Carrier table
 *
 */

class Flex_Rollout_Version_000178 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);

		// This will reset the id sequence for the Carrier table to the next available integer, if a rollback is required
		$this->rollbackSQL[] =	"ALTER TABLE Carrier AUTO_INCREMENT=1;";
		
		//	1:	Add CARRIER_SOUVIK and CARRIER_THE_PHONE_BROKER records to the Carrier table
		$strSQL = " INSERT INTO Carrier (Name, carrier_type, description, const_name)
					VALUES
					('Souvik', 3, 'Souvik', 'CARRIER_SOUVIK'),
					('The Phone Broker', 3, 'The Phone Broker', 'CARRIER_THE_PHONE_BROKER');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed to add records to the Carrier table for sale call centers 'Souvik' and 'The Phone Broker'. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DELETE FROM Carrier WHERE const_name IN ('CARRIER_SOUVIK', 'CARRIER_THE_PHONE_BROKER')";
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