<?php

/**
 * Version 82 of database update.
 * This version: -
 *	1:	Add the compression_algorithm table
 *	2:	Populate the compression_algorithm table
 *	3:	Add the FileImport.compression_algorithm_id field
 */

class Flex_Rollout_Version_000082 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the compression_algorithm table
		$strSQL = "CREATE TABLE compression_algorithm " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Compression Algorithm', " .
						"name VARCHAR(255) NOT NULL COMMENT 'Name of the Compression Algorithm', " .
						"description VARCHAR(1024) NOT NULL COMMENT 'Description of the Compression Algorithm'," .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Compression Algorithm'," .
						"file_extension VARCHAR(10) NOT NULL COMMENT 'File Extension (including \'.\') for the Compressed File'," .
						"php_stream_wrapper VARCHAR(50) NULL COMMENT 'PHP fopen() stream wrapper prefix'" .
					") ENGINE = innodb;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the compression_algorithm Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS compression_algorithm;";
		
		// 2:	Populate the compression_algorithm table
		$strSQL = "INSERT INTO compression_algorithm (name, description, const_name, file_extension, php_stream_wrapper) VALUES " .
					"('None', 'No compression', 'COMPRESSION_ALGORITHM_NONE', '', NULL), " .
					"('bzip2', 'bzip2', 'COMPRESSION_ALGORITHM_BZIP2', '.bz2', 'compress.bzip2://')," .
					"('gzip', 'gzip', 'COMPRESSION_ALGORITHM_GZIP', '.gz', 'compress.zlib://');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the compression_algorithm Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE compression_algorithm;";
		
		// 3:	Add the FileImport.compression_algorithm_id field
		$strSQL = "ALTER TABLE FileImport ADD compression_algorithm_id BIGINT(20) NOT NULL DEFAULT (SELECT id FROM compression_algorithm WHERE name = 'None' LIMIT 1) COMMENT '(FK) Compression Algorithm applied at Collection' AFTER archive_location;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the FileImport.compression_algorithm_id field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE FileImport DROP compression_algorithm_id;";
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
