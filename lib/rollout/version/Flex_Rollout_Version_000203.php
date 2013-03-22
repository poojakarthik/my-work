<?php

/**
 * Version 203 of database update.
 * This version: -
 *	
 *	1:	Add the "Australia's Telecom" and "Sparkz Infotech" Carriers
 *
 */

class Flex_Rollout_Version_000203 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the "Australia's Telecom" and "Sparkz Infotech" Carriers
		$strSQL = "	INSERT INTO	Carrier
						(name, description, const_name, carrier_type)
					VALUES
						('Australia\\'s Telecom'	, 'Australia\\'s Telecom'	, 'CARRIER_AUSTRALIAS_TELECOM'	, (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_SALES_CALL_CENTRE' LIMIT 1)),
						('Sparkz Infotech'			, 'Sparkz Infotech'			, 'CARRIER_SPARKZ_INFOTECH'		, (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_SALES_CALL_CENTRE' LIMIT 1));";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the "Australia\'s Telecom" and "Sparkz Infotech" Carriers. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DELETE FROM	Carrier
									WHERE		const_name IN ('CARRIER_AUSTRALIAS_TELECOM', 'CARRIER_SPARKZ_INFOTECH');";
		
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