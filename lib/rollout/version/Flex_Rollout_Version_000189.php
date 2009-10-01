<?php

/**
 * Version 189 of database update.
 * This version: -
 *	
 *	1:	Add the 'Cooling Off' Credit Control Status
 *
 */

class Flex_Rollout_Version_000189 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the 'Cooling Off' Credit Control Status
		$strSQL = "	INSERT INTO	credit_control_status
						(name, can_bar, send_late_notice, description, const_name)
					VALUES
						('Cooling Off', 0, 1, 'Do not bar.', 'CREDIT_CONTROL_STATUS_COOLING_OFF');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed to add the 'Cooling Off' Credit Control Status. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DELETE FROM	credit_control_status
									WHERE		const_name = 'CREDIT_CONTROL_STATUS_COOLING_OFF';";
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