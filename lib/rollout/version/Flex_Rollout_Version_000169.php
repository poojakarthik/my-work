<?php

/**
 * Version 169 of database update.
 * This version: -
 *	
 *	1:	Remove country.description, change code to code_3_char, and add code_2_char Fields
 *	2:	Populate country.code_2_char Field
 *	3:	Remove state.description Field
 *	4:	Add address_locality Table
 *
 */

class Flex_Rollout_Version_000169 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Remove country.description, change code to code_3_char, and add code_2_char Fields
		$strSQL = "	ALTER TABLE	country
						DROP	description,
						CHANGE	code		code_3_char	CHAR(3)			NULL	COMMENT '3-character Country Code (ISO 3166-1 alpha-3)',
						ADD		code_2_char				CHAR(2)			NULL	COMMENT '2-character Country Code (ISO 3166-1 alpha-2)';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to remove country.description, change code to code_3_char, and add code_2_char Fields. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	country
										ADD		description				VARCHAR(255)	NOT NULL	COMMENT 'Description of the Country',
										CHANGE	code_3_char	code		VARCHAR(255)	NOT NULL	COMMENT 'Abbreviation of the Country\'s name',
										DROP	code_2_char;";
		$this->rollbackSQL[] =	"	UPDATE	country
									SET		description = name
									WHERE	1;";
		
		//	2:	Populate country.code_2_char Field
		$strSQL = "	UPDATE	country
					SET		code_2_char	= 'AU'
					WHERE	code_3_char	= 'AUS';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to set country.code_2_char for AUS. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$strSQL = "	UPDATE	country
					SET		code_2_char	= 'IN'
					WHERE	code_3_char	= 'IND';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to set country.code_2_char for IND. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		
		//	3:	Remove state.description Field
		$strSQL = "	ALTER TABLE	`state`
						DROP	description;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to remove state.description Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	UPDATE	state
									SET		description = name
									WHERE	1;";
		$this->rollbackSQL[] =	"	ALTER TABLE	`state`
										ADD		description				VARCHAR(255)	NOT NULL	COMMENT 'Description of the State';";
		
		//	4:	Add address_locality Table
		$strSQL = "	CREATE TABLE address_locality
					(
						id			BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name		VARCHAR(256)				NOT NULL					COMMENT 'Name of the Locality',
						postcode	INT				UNSIGNED	NULL						COMMENT 'Postcode of the Locality',
						state_id	BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) State (Top-level Subdivision) of the Locality',
						
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