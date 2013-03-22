<?php

/**
 * Version 188 of database update.
 * This version: -
 *	
 *	1:	Add FKs to the service_total_service Table
 *
 */

class Flex_Rollout_Version_000188 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add FKs to the service_total_service Table
		$strSQL = "	ALTER TABLE		service_total_service
					ADD CONSTRAINT	fk_service_total_service_service_total_id	FOREIGN KEY (service_total_id)	REFERENCES ServiceTotal(Id)	ON UPDATE CASCADE	ON DELETE CASCADE,
					ADD CONSTRAINT	fk_service_total_service_service_id			FOREIGN KEY (service_id)		REFERENCES Service(Id)		ON UPDATE CASCADE	ON DELETE CASCADE;";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add FKs to the service_total_service Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	service_total_service
									DROP CONSTRAINT	fk_service_total_service_service_id,
									DROP CONSTRAINT	fk_service_total_service_service_total_id;";
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