<?php

/**
 * Version 162 of database update.
 * This version: -
 *
 *	1:	Add the address_locality Table
 *	2:	Populate the address_locality Table
 */

class Flex_Rollout_Version_000162 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Add the address_locality Table
		$strSQL = "	CREATE TABLE address_locality
					(
						id			BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name		VARCHAR(256)				NOT NULL					COMMENT 'Name of the Locality',
						postcode	INT				UNSIGNED	NOT NULL					COMMENT 'Postcode of the Locality',
						state_id	BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) State of the Locality',
						
						CONSTRAINT	pk_address_locality_id			PRIMARY KEY	(id),
						CONSTRAINT	fk_address_locality_state_id	FOREIGN KEY	(state_id)	REFERENCES	state(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the address_locality Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE address_locality;";

		// 2:	Populate the address_locality Table
		$strSQL = "	INSERT INTO address_locality
					(
						id			BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name		VARCHAR(256)				NOT NULL					COMMENT 'Name of the Locality',
						postcode	INT				UNSIGNED	NOT NULL					COMMENT 'Postcode of the Locality',
						state_id	BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) State of the Locality',
						
						CONSTRAINT	pk_address_locality_id			PRIMARY KEY	(id),
						CONSTRAINT	fk_address_locality_state_id	FOREIGN KEY	(state_id)	REFERENCES	state(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the address_locality Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE address_locality;";
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