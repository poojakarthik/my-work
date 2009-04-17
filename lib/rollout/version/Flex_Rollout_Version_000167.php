<?php

/**
 * Version 167 of database update.
 * This version: -
 *	
 *	1:	Add new Line Connection Destinations
 *	2:	Add Unitel Destination Translation data for new Line Connection Destinations
 *
 */

class Flex_Rollout_Version_000167 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add new Line Connection Destinations
		$strSQL = "	INSERT INTO	Destination
						(Code	, Description										, Context)
					VALUES
						(80005	, 'Additional Line Connection'						, (SELECT id FROM destination_context WHERE const_name = 'DESTINATION_CONTEXT_SERVICE_AND_EQUIPMENT')),
						(80006	, 'Telephone Line Connection'						, (SELECT id FROM destination_context WHERE const_name = 'DESTINATION_CONTEXT_SERVICE_AND_EQUIPMENT'))),
						(80007	, 'Telephone Line Connection Addtl with Tech visit'	, (SELECT id FROM destination_context WHERE const_name = 'DESTINATION_CONTEXT_SERVICE_AND_EQUIPMENT'))),
						(80008	, 'Telephone Line Connection with Tech Visit'		, (SELECT id FROM destination_context WHERE const_name = 'DESTINATION_CONTEXT_SERVICE_AND_EQUIPMENT'))),
						(82001	, 'ISDN HOME New Connection'						, (SELECT id FROM destination_context WHERE const_name = 'DESTINATION_CONTEXT_SERVICE_AND_EQUIPMENT'))),
						(82002	, 'ISDN 2 Connection Charges'						, (SELECT id FROM destination_context WHERE const_name = 'DESTINATION_CONTEXT_SERVICE_AND_EQUIPMENT'))),
						(83000	, 'ISDN New Service Connection'						, (SELECT id FROM destination_context WHERE const_name = 'DESTINATION_CONTEXT_SERVICE_AND_EQUIPMENT'))),
						(83001	, 'ISDN In-Place service connection'				, (SELECT id FROM destination_context WHERE const_name = 'DESTINATION_CONTEXT_SERVICE_AND_EQUIPMENT')));";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the new Line Connection Destinations. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DELETE FROM	Destination
									WHERE		Code IN (80005, 80006, 80007, 80008, 82001, 82002, 83000, 83001);";
		
		//	2:	Add Unitel Destination Translation data for new Line Connection Destinations
		$strSQL = "	INSERT INTO	cdr_call_type_translation
						(code	, carrier_id										, carrier_code										, description)
					VALUES
						(80005	, (SELECT Id FROM Carrier WHERE name = 'Unitel')	, 'Additional Line Connection'						, 'Additional Line Connection'),
						(80006	, (SELECT Id FROM Carrier WHERE name = 'Unitel')	, 'Telephone Line Connection'						, 'Telephone Line Connection'),
						(80007	, (SELECT Id FROM Carrier WHERE name = 'Unitel')	, 'Telephone Line Connection Addtl with Tech visit'	, 'Telephone Line Connection Addtl with Tech visit'),
						(80008	, (SELECT Id FROM Carrier WHERE name = 'Unitel')	, 'Telephone Line Connection with Tech Visit'		, 'Telephone Line Connection with Tech Visit'),
						(82001	, (SELECT Id FROM Carrier WHERE name = 'Unitel')	, 'ISDN HOME New Connection'						, 'ISDN HOME New Connection'),
						(82002	, (SELECT Id FROM Carrier WHERE name = 'Unitel')	, 'ISDN 2 Connection Charges'						, 'ISDN 2 Connection Charges'),
						(83000	, (SELECT Id FROM Carrier WHERE name = 'Unitel')	, 'ISDN New Service Connection'						, 'ISDN New Service Connection'),
						(83001	, (SELECT Id FROM Carrier WHERE name = 'Unitel')	, 'ISDN In-Place service connection'				, 'ISDN In-Place service connection');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the  Unitel Destination Translation data for new Line Connection Destinations. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DELETE FROM	cdr_call_type_translation
									WHERE		Carrier = (SELECT Id FROM Carrier WHERE name = 'Unitel')
												AND carrier_code IN	(
																		'Additional Line Connection',
																		'Telephone Line Connection',
																		'Telephone Line Connection Addtl with Tech visit',
																		'Telephone Line Connection with Tech Visit',
																		'ISDN HOME New Connection',
																		'ISDN 2 Connection Charges',
																		'ISDN New Service Connection',
																		'ISDN In-Place service connection',
																	);";
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