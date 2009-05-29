<?php

/**
 * Version 176 of database update.
 * This version: -
 *	
 *	1:	Add the SFTP Resource Type
 *	2:	Add the iSeek Data File Resource Type
 *
 */

class Flex_Rollout_Version_000176 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the SFTP Resource Type
		$strSQL	= "	SELECT	id
					FROM	resource_type
					WHERE	const_name = 'RESOURCE_TYPE_FILE_RESOURCE_SFTP'
					LIMIT	1;";
		$result	= $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to check whether SFTP Resource Type already exists . ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		elseif (!$result->fetchRow())
		{
			// DEBUG
			throw new Exception("SFTP already exists!");
			// DEBUG
			
			$strSQL = "	INSERT INTO	resource_type
							(name, description, const_name, resource_type_nature)
						VALUES
							('SFTP File Server'			, 'SFTP File Server'		, 'RESOURCE_TYPE_FILE_RESOURCE_SFTP'			, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_FILE_REPOSITORY' LIMIT 1));";
			$result = $dbAdmin->query($strSQL);
			if (PEAR::isError($result))
			{
				throw new Exception(__CLASS__ . ' Failed to add the SFTP Resource Type. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
			}
			$this->rollbackSQL[] =	"	DELETE FROM	resource_type
									WHERE		const_name IN ('RESOURCE_TYPE_FILE_RESOURCE_SFTP');";
		}
		
		//	2:	Add the iSeek Data File Resource Type
		$strSQL	= "	SELECT	id
					FROM	resource_type
					WHERE	const_name = 'RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA'
					LIMIT	1;";
		$result	= $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to check whether iSeek Data File Resource Type already exists . ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		elseif (!$result->fetchRow())
		{
			// DEBUG
			throw new Exception("SFTP already exists!");
			// DEBUG
			
			$strSQL = "	INSERT INTO	resource_type
							(name, description, const_name, resource_type_nature)
						VALUES
							('iSeek Data Usage File'	, 'iSeek Data Usage File'	, 'RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA'	, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE' LIMIT 1));";
			$result = $dbAdmin->query($strSQL);
			if (PEAR::isError($result))
			{
				throw new Exception(__CLASS__ . ' Failed to add the iSeek Data File Resource Type. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
			}
			$this->rollbackSQL[] =	"	DELETE FROM	resource_type
									WHERE		const_name IN ('RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA');";
		}
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