<?php

/**
 * Version 193 of database update.
 * This version: -
 *	
 *	1:	Declares all Foreign Keys for the ticketing_attachment table
 *
 */

class Flex_Rollout_Version_000193 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		$arrCommands = array();
		
		/********************************************* START - Modifications to the ticketing_attachment table - START ****************************************************************/ 
		// Declaration of FOREIGN KEY for ticketing_attachment.correspondance_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_attachment.correspondance_id -> ticketing_correspondance.id (ON DELETE CASCADE ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_attachment ADD CONSTRAINT fk_ticketing_attachment_correspondance_id_t_correspondance_id FOREIGN KEY (correspondance_id) REFERENCES ticketing_correspondance(id) ON DELETE CASCADE ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_attachment DROP FOREIGN KEY fk_ticketing_attachment_correspondance_id_t_correspondance_id, DROP INDEX fk_ticketing_attachment_correspondance_id_t_correspondance_id;";
		$arrCommand['check_sql']	= "SELECT tt_c.id FROM ticketing_attachment AS tt_c LEFT JOIN ticketing_correspondance AS tt_p ON tt_c.correspondance_id = tt_p.id WHERE tt_c.correspondance_id IS NOT NULL AND tt_p.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_attachment.attachment_type_id
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_attachment.attachment_type_id -> ticketing_attachment_type.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_attachment ADD CONSTRAINT fk_ticketing_attachment_attachment_type_id_t_attachment_type_id FOREIGN KEY (attachment_type_id) REFERENCES ticketing_attachment_type(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_attachment DROP FOREIGN KEY fk_ticketing_attachment_attachment_type_id_t_attachment_type_id, DROP INDEX fk_ticketing_attachment_attachment_type_id_t_attachment_type_id;";
		$arrCommand['check_sql']	= "SELECT tt_c.id FROM ticketing_attachment AS tt_c LEFT JOIN ticketing_attachment_type AS tt_p ON tt_c.attachment_type_id = tt_p.id WHERE tt_c.attachment_type_id IS NOT NULL AND tt_p.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		// Declaration of FOREIGN KEY for ticketing_attachment.blacklist_override
		$arrCommand = array();
		$arrCommand['step_name']	= 'Declaration of Foreign Key for ticketing_attachment.blacklist_override -> active_status.id (ON DELETE RESTRICT ON UPDATE CASCADE)';
		$arrCommand['rollout_sql']	= "ALTER TABLE ticketing_attachment ADD CONSTRAINT fk_ticketing_attachment_blacklist_override_active_status_id FOREIGN KEY (blacklist_override) REFERENCES active_status(id) ON DELETE RESTRICT ON UPDATE CASCADE;";
		$arrCommand['rollback_sql']	= "ALTER TABLE ticketing_attachment DROP FOREIGN KEY fk_ticketing_attachment_blacklist_override_active_status_id, DROP INDEX fk_ticketing_attachment_blacklist_override_active_status_id;";
		$arrCommand['check_sql']	= "SELECT tt_c.id FROM ticketing_attachment AS tt_c LEFT JOIN active_status AS tt_p ON tt_c.blacklist_override = tt_p.id WHERE tt_c.blacklist_override IS NOT NULL AND tt_p.id IS NULL LIMIT 1;";
		$arrCommands[] = $arrCommand;

		/*********************************************** END - Modifications to the ticketing_attachment table - END ******************************************************************/


		// Run each Check SQL statement (these check that the step should be doable)
		$intStep = 0;

		foreach ($arrCommands as $i=>$arrCommand)
		{
			$intStep = $i + 1;
			$this->outputMessage("\nChecking Step {$intStep} - {$arrCommand['step_name']}... ");

			if (array_key_exists('check_sql', $arrCommand))
			{
				$fltStartTime	= microtime(true);
				$result			= $dbAdmin->query($arrCommand['check_sql']);
				$strTimeTaken	= number_format(microtime(true) - $fltStartTime, 3, '.', '');
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . " Failed Check for Step {$intStep} " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
				}
				if ($result->numRows() > 0)
				{
					// At least 1 row was returned, signifying there were problems
					$this->outputMessage("FAIL!!!\nThe following query returned records signifying that this step will fail if actually executed.  Please rectify the issue before running this rollout script again.\n\n{$arrCommand['check_sql']}\n\n");
					throw new Exception("Preliminary check failed");
				}
				else
				{
					// No Rows were returned signifying that there aren't any problems
					$this->outputMessage("PASS ({$strTimeTaken} sec)\n");
				}
			}
			else
			{
				// There is no check for this step
				$this->outputMessage("No Check Required\n");
			}
		}
		
		$this->outputMessage("\nStarted: ". date('H:i:s d-m-Y') ."\n");

		// Now run each command
		$intStep = 0;
		foreach ($arrCommands as $i=>$arrCommand)
		{
			$intStep = $i + 1;
			$this->outputMessage("\nStep {$intStep} - {$arrCommand['step_name']}\n");

			$result = $dbAdmin->query($arrCommand['rollout_sql']);
			if (PEAR::isError($result))
			{
				throw new Exception(__CLASS__ . " Failed Step {$intStep} " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
			}
			$this->rollbackSQL[] = $arrCommand;
		}

		$this->outputMessage("\nFinished: ". date('H:i:s d-m-Y') ."\n");

	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			$this->outputMessage("\nRollback required!!!\n");
			
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				// Display which step is being rolled back
				$intStep = $l + 1;
				$this->outputMessage("\nUndoing Step {$intStep}\t- ". $this->rollbackSQL[$l]['step_name'] ."\n");
				
				$result = $dbAdmin->query($this->rollbackSQL[$l]['rollback_sql']);
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l]['rollback_sql'] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>