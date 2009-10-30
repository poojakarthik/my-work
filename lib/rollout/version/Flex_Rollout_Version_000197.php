<?php

/**
 * Version 197 of database update.
 * This version: -
 *	
 *	1:	Add the Other Charges & 3G Destination Contexts
 *	2:	Add the 3G default/fallback Destination
 *	3:	Map the 3G fallback Destination to the 3G Context
 *	4:	Map the 3G Record Type to the 3G Context
 *
 */

class Flex_Rollout_Version_000197 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		$sDatetime	= date("Y-m-d H:i:s");
		
		//	1:	Add the Other Charges & 3G Destination Contexts
		$strSQL = "	INSERT INTO	destination_context
						(name	, description				, const_name)
					VALUES
						('OC'	, 'Other Charges'			, 'DESTINATION_CONTEXT_OC'),
						('3G'	, '3G Data'					, 'DESTINTAION_CONTEXT_3G');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the Other Charges & 3G Destination Contexts. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DELETE FROM	destination_context
									WHERE		const_name IN ('DESTINATION_CONTEXT_OC', 'DESTINTAION_CONTEXT_3G');";
		
		//	2:	Add the 3G default/fallback Destination
		$strSQL = "	INSERT INTO	Destination
						(Code	, Description	, Context)
					VALUES
						(40000	, '3G Usage'	, (SELECT id FROM destination_context WHERE const_name = 'DESTINTAION_CONTEXT_3G' LIMIT 1));";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the 3G default/fallback Destination. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DELETE FROM	Destination
									WHERE		Code = 40000;";
		
		//	3:	Map the 3G fallback Destination to the 3G Context
		$strSQL = "	UPDATE	destination_context
					SET		fallback_destination_id = 40000
					WHERE	const_name = 'DESTINTAION_CONTEXT_3G';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to map the 3G fallback Destination to the 3G Context. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		
		//	4:	Map the 3G Record Type to the 3G Context
		$strSQL = "	UPDATE	RecordType
					SET		Context = (SELECT id FROM destination_context WHERE const_name = 'DESTINTAION_CONTEXT_3G' LIMIT 1)
					WHERE	Code = '3G'
							AND ServiceType = 101;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to map the 3G Record Type to the 3G Context. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	UPDATE	RecordType
									SET		Context = 0
									WHERE	Code = '3G'
											AND ServiceType = 101;";
		
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