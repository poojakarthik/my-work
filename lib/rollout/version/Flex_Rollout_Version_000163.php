<?php

/**
 * Version 163 of database update.
 * This version: -
 *
 *	1:	Add the file_type_mime_type Table
 *	2:	Populate the file_type_mime_type Table
 *
 *	3:	Remove the file_type.mime_type_id Field
 */

class Flex_Rollout_Version_000163 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);

		// 1:	Add the file_type_mime_type Table
		$strSQL = "	CREATE TABLE file_type_mime_type
					(
						id						BIGINT(20)	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						file_type_id			BIGINT		UNSIGNED	NOT NULL					COMMENT '(FK) File Type',
						mime_type_id			BIGINT(20)	UNSIGNED	NOT NULL					COMMENT '(FK) MIME Type',
						is_preferred_mime_type	TINYINT					NOT NULL	DEFAULT 0		COMMENT '1: Preferred Export MIME Type; 0: Alternate MIME Type',
						
						CONSTRAINT	pk_file_type_mime_type_id			PRIMARY KEY (id),
						CONSTRAINT	fk_file_type_mime_type_file_type_id	FOREIGN KEY (file_type_id)	REFERENCES file_type(id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_file_type_mime_type_mime_type_id	FOREIGN KEY (mime_type_id)	REFERENCES mime_type(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the file_type_mime_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE file_type_mime_type;";

		// 2:	Populate the file_type_mime_type Table
		$resFileType = $dbAdmin->query("SELECT * FROM Employee WHERE 1");
		if (PEAR::isError($resFileType))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve the list of file_type Records. ' . $resFileType->getMessage() . " (DB Error: " . $resFileType->getUserInfo() . ")");
		}
		$arrFileTypeRollbackSQL	= array();
		while ($arrFileType = $resFileType->fetchRow())
		{
			$strSQL = "	INSERT INTO file_type_mime_type (file_type_id, mime_type_id, is_preferred_mime_type) VALUES 
						({$arrFileType['id']}		, {$arrFileType['mime_type_id']}	, 1);";
			$result = $dbAdmin->query($strSQL);
			if (PEAR::isError($result))
			{
				throw new Exception(__CLASS__ . ' Failed to populate the file_type_mime_type Table for file_type '.$arrFileType['id'].'. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
			}
			
			$arrFileTypeRollbackSQL[]	= "UPDATE file_type SET mime_type_id = {$arrFileType['mime_type_id']} WHERE id = {$arrFileType['id']};";
		}

		// 3:	Remove the file_type.mime_type_id Field
		$strSQL =	"ALTER TABLE file_type " .
					"DROP FOREIGN KEY fk_file_type_mime_type_id, " .
					"DROP mime_type_id;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to remove the file_type.mime_type_id Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE file_type " .
								"ADD mime_type_id	BIGINT	UNSIGNED	NOT NULL	COMMENT '(FK) MIME Type', " .
								"ADD CONSTRAINT	fk_file_type_mime_type_id	FOREIGN KEY (mime_type_id)	REFERENCES mime_type(id)	ON UPDATE CASCADE	ON DELETE RESTRICT;";
		$this->rollbackSQL	= array_merge($this->rollbackSQL, $arrFileTypeRollbackSQL);
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