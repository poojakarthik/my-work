<?php

/**
 * Version 133 of database update.
 * This version: -
 *	1:	Add the status Table
 *	2:	Add the mime_type Table
 *	3:	Add the file_type Table
 *	4:	Add the document_nature Table
 *	5:	Add the document Table
 *	6:	Add the document_content Table
 *
 *	7:	Populate the status Table
 *	8:	Populate the document_nature Table
 */

class Flex_Rollout_Version_000133 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the status Table
		$strSQL =	"CREATE TABLE status 
					(
						id			BIGINT(20)		UNSIGNED	NOT NULL AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name		VARCHAR(255)				NOT NULL				COMMENT 'Name of the Status',
						description	VARCHAR(1024)				NOT NULL				COMMENT 'Description of the Status',
						const_name	VARCHAR(512)				NOT NULL				COMMENT 'Constant Name of the Status',
						
						CONSTRAINT pk_status_id PRIMARY KEY (id)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add status Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DROP TABLE status;";
		
		// 2:	Add the mime_type Table
		$strSQL =	"CREATE TABLE mime_type 
					(
						id					BIGINT(20)		UNSIGNED	NOT NULL AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name				VARCHAR(255)				NOT NULL				COMMENT 'Name of the Mime Type',
						description			VARCHAR(1024)				NOT NULL				COMMENT 'Description of the Mime Type',
						mime_content_type	VARCHAR(255)				NOT NULL				COMMENT 'Mime Content Type',
						
						CONSTRAINT pk_mime_type_id PRIMARY KEY (id)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add mime_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DROP TABLE mime_type;";
		
		// 3:	Add the file_type Table
		$strSQL =	"CREATE TABLE file_type 
					(
						id				BIGINT(20)		UNSIGNED	NOT NULL AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name			VARCHAR(255)				NOT NULL				COMMENT 'Name of the File Type',
						description		VARCHAR(1024)				NOT NULL				COMMENT 'Description of the File Type',
						extension		VARCHAR(32)					NOT NULL				COMMENT 'File Type Extension',
						mime_type_id	BIGINT(20)		UNSIGNED	NOT NULL				COMMENT '(FK) Reference to the mime_type_id Table',
						icon_16x16		MEDIUMBLOB					NULL					COMMENT 'Small Icon for the File Type',
						icon_64x64		MEDIUMBLOB					NULL					COMMENT 'Large Icon for the File Type',
						
						CONSTRAINT pk_file_type_id PRIMARY KEY (id),
						CONSTRAINT fk_file_type_mime_type_id FOREIGN KEY (mime_type_id) REFERENCES mime_type(id) ON UPDATE CASCADE ON DELETE RESTRICT
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add file_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DROP TABLE file_type;";
		
		// 4:	Add the document_nature Table
		$strSQL =	"CREATE TABLE document_nature 
					(
						id			BIGINT(20)		UNSIGNED	NOT NULL AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name		VARCHAR(255)				NOT NULL				COMMENT 'Name of the Document Nature',
						description	VARCHAR(1024)				NOT NULL				COMMENT 'Description of the Document Nature',
						const_name	VARCHAR(512)				NOT NULL				COMMENT 'Constant Name of the Document Nature',
						
						CONSTRAINT pk_document_nature_id PRIMARY KEY (id)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add document_nature Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DROP TABLE document_nature;";
		
		// 5:	Add the document Table
		$strSQL =	"CREATE TABLE document 
					(
						id					BIGINT(20)		UNSIGNED	NOT NULL AUTO_INCREMENT	COMMENT 'Unique Identifier',
						document_nature_id	BIGINT(20)		UNSIGNED	NOT NULL				COMMENT '(FK) The Document Nature',
						created_on			TIMESTAMP					NOT NULL				COMMENT 'Date the Document was created',
						employee_id			BIGINT(20)		UNSIGNED	NOT NULL				COMMENT '(FK) Employee who created the Document',
						
						CONSTRAINT pk_document_id PRIMARY KEY (id),
						CONSTRAINT fk_document_document_nature_id FOREIGN KEY (document_nature_id) REFERENCES document_nature(id) ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT fk_document_employee_id FOREIGN KEY (employee_id) REFERENCES Employee(Id) ON UPDATE CASCADE ON DELETE RESTRICT
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add document Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DROP TABLE document;";
		
		// 6:	Add the document_content Table
		$strSQL =	"CREATE TABLE document_content 
					(
						id					BIGINT(20)		UNSIGNED	NOT NULL AUTO_INCREMENT	COMMENT 'Unique Identifier',
						document_id			BIGINT(20)		UNSIGNED	NOT NULL				COMMENT '(FK) The Document this belongs to',
						name				VARCHAR(255)				NOT NULL				COMMENT 'Name of the Document',
						description			VARCHAR(1024)				NULL					COMMENT 'Description of the Document',
						file_type_id		BIGINT(20)		UNSIGNED	NOT NULL				COMMENT '(FK) The Document\'s File Type',
						content				MEDIUMBLOB					NOT NULL				COMMENT 'Binary content of the Document',
						parent_document_id	BIGINT(20)		UNSIGNED	NULL					COMMENT '(FK) The Document this is a child of',
						changed_on			TIMESTAMP					NOT NULL				COMMENT 'Date the Document was changed',
						employee_id			BIGINT(20)		UNSIGNED	NOT NULL				COMMENT '(FK) Employee who modified the Document',
						status_id			BIGINT(20)		UNSIGNED	NOT NULL				COMMENT '(FK) Active Status of the Document',
						
						CONSTRAINT pk_document_content_id PRIMARY KEY (id),
						CONSTRAINT fk_document_content_document_id FOREIGN KEY (document_id) REFERENCES document(id) ON UPDATE CASCADE ON DELETE CASCADE,
						CONSTRAINT fk_document_content_file_type_id FOREIGN KEY (file_type_id) REFERENCES file_type(id) ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT fk_document_content_employee_id FOREIGN KEY (employee_id) REFERENCES Employee(Id) ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT fk_document_content_status_id FOREIGN KEY (status_id) REFERENCES status(id) ON UPDATE CASCADE ON DELETE RESTRICT
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add document_content Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DROP TABLE document_content;";
		
		// 7:	Populate the status Table
		$strSQL =	"INSERT INTO status (name, description, const_name) VALUES 
					('Active'	, 'Active'		, 'STATUS_ACTIVE'),
					('Inactive'	, 'Inactive'	, 'STATUS_INACTIVE');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the status Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE status;";
		
		// 7:	Populate the document_nature Table
		$strSQL =	"INSERT INTO document_nature (name, description, const_name) VALUES 
					('Folder'	, 'Folder'	, 'DOCUMENT_NATURE_FOLDER'),
					('File'		, 'File'	, 'DOCUMENT_NATURE_FILE');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the document_nature Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE document_nature;";
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