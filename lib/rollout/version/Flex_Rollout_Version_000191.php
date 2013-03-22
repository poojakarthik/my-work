<?php

/**
 * Version 191 of database update.
 * This version: -
 *	
 *	1:	Add CARRIER_MATE_AUSTRALIA and CARRIER_CONTACT_CENTRE_AUSTRALIA to the Carrier table
 *
 */

class Flex_Rollout_Version_000191 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);

		// This will reset the id sequence for the Carrier table to the next available integer, if a rollback is required
		$this->rollbackSQL[] =	"ALTER TABLE Carrier AUTO_INCREMENT=1;";
		
		//	1:	Add the CARRIER_MATE_AUSTRALIA record to the Carrier table
		$strSQL = " INSERT INTO Carrier (Name, carrier_type, description, const_name)
					VALUES
					('Mate Australia', 3, 'Mate Australia', 'CARRIER_MATE_AUSTRALIA'),
					('Contact Centre Australia', 3, 'Contact Centre Australia', 'CARRIER_CONTACT_CENTRE_AUSTRALIA');";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed to add records to the Carrier table for sale call centers 'Mate Australia' and 'Contact Centre Australia'. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DELETE FROM Carrier WHERE const_name IN ('CARRIER_MATE_AUSTRALIA', 'CARRIER_CONTACT_CENTRE_AUSTRALIA')";
	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (MDB2::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>