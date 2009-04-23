<?php

/**
 * Version 172 of database update.
 * This version: -
 *	
 *	1:	Set the NoteType to the General Notice NoteType, for all notes that have a NoteType of "", "0", "1", "7", "default", "none"
 *	2:	Remove the NoteTypes "", "0", "1", "7", "default", "none"
 */

class Flex_Rollout_Version_000172 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);

		// I'm assuming the NoteType ids are constant and controlled, as they are part of the template database, and there is no way to edit them through Flex
		$intGeneralNoticeNoteTypeId	= 1;
		$arrDeprecatedNoteTypeIds	= array(5, 6, 8, 9, 10, 11); // 'none', 'default', '', '0', '1', '7'
		
		// 1: Set the NoteType to the General Notice NoteType, for all notes that have a NoteType of "", "0", "1", "7", "default", "none"
		$strSQL = "UPDATE Note SET NoteType = $intGeneralNoticeNoteTypeId WHERE NoteType IN (". implode(", ", $arrDeprecatedNoteTypeIds) .")";
		
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to change the NoteType of all Notes referencing the deprecated NoteTypes. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No rollout necessary so long as this rollout script doesn't contain any structural changes to the database
		
		// 2: Remove the NoteTypes "", "0", "1", "7", "default", "none"
		$strSQL = "DELETE FROM NoteType WHERE Id IN (". implode(", ", $arrDeprecatedNoteTypeIds) .")";
		
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to delete the deprecated NoteTypes. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No rollout necessary so long as this rollout script doesn't contain any structural changes to the database
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