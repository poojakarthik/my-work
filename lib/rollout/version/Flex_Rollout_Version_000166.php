<?php

/**
 * Version 166 of database update.
 * This version: -
 *	
 *	1:	Add the document_content.uncompressed_file_size Field
 *	2:	Compress all document_content.content data with BZIP2
 *
 */

class Flex_Rollout_Version_000166 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the document_content.uncompressed_file_size Field
		$strSQL = "	ALTER TABLE document_content
					ADD		uncompressed_file_size	INT			UNSIGNED	NULL	COMMENT 'Unique Identifier',
					MODIFY	content					MEDIUMBLOB				NULL	COMMENT 'Binary content of the Document (compressed with BZIP2)';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the document_content.uncompressed_file_size Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE document_content
									CHANGE	content					MEDIUMBLOB				NULL	COMMENT 'Binary content of the Document', 
									DROP	uncompressed_file_size;";
		
		//	2:	Compress all document_content.content data with BZIP2
		$resDocumentContent	= $dbAdmin->query("SELECT * FROM document_content WHERE content IS NOT NULL");
		if (PEAR::isError($resDocumentContent))
		{
			throw new Exception(__CLASS__ . ' Failed to retrieve the list of document_content Records. ' . $resDocumentContent->getMessage() . " (DB Error: " . $resDocumentContent->getUserInfo() . ")");
		}
		while ($arrDocumentContent = $resDocumentContent->fetchRow())
		{
			// Compress with BZIP2
			$mixCompressed	= bzcompress($arrDocumentContent['content']);
			if (is_int($mixCompressed))
			{
				// Error
				throw new Exception("Unable to compress Content for Document {$arrDocumentContent['document_id']} (Revision: {$arrDocumentContent['id']}): Error #{$mixCompressed}");
			}
			
			if (PEAR::isError($strNewContent = $dbAdmin->quote($mixCompressed, 'text')))
			{
				throw new Exception(__CLASS__ . ' Failed to quote new document_content.content: '. $strNewContent->getMessage() . " (DB Error: " . $strNewContent->getUserInfo() . ")");
			}
			if (PEAR::isError($intUncompressedFileSize = $dbAdmin->quote(strlen($arrDocumentContent['content']), 'integer')))
			{
				throw new Exception(__CLASS__ . ' Failed to quote document_content.uncompressed_file_size: '. $strNewContent->getMessage() . " (DB Error: " . $strNewContent->getUserInfo() . ")");
			}
			if (PEAR::isError($intDocumentContentId = $dbAdmin->quote($arrDocumentContent['id'], 'integer')))
			{
				throw new Exception(__CLASS__ . ' Failed to quote document_content.id: '. $intDocumentContentId->getMessage() . " (DB Error: " . $intDocumentContentId->getUserInfo() . ")");
			}
			
			// Rollback values
			if (PEAR::isError($strOldContent = $dbAdmin->quote($arrDocumentContent['content'], 'text')))
			{
				throw new Exception(__CLASS__ . ' Failed to quote old document_content.content: '. $strOldContent->getMessage() . " (DB Error: " . $strOldContent->getUserInfo() . ")");
			}
			
			$strUpdateSQL	=	"UPDATE	document_content " .
								"SET	content					= {$strNewContent} ," .
								"		uncompressed_file_size	= {$intUncompressedFileSize} " .
								"WHERE	id		= {$intDocumentContentId};";
			
			$resDocumentContentUpdate	= $dbAdmin->exec($strUpdateSQL);
			if (PEAR::isError($resDocumentContentUpdate))
			{
				throw new Exception(__CLASS__ . ' Failed to compress document_content '.$arrDocumentContent['id'].'. ' . $resDocumentContentUpdate->getMessage() . " (DB Error: " . $resDocumentContentUpdate->getUserInfo() . ")");
			}
			
			$this->rollbackSQL[] =	"UPDATE	document_content " .
									"SET	content	= {$strOldContent} " .
									"WHERE	id		= {$intDocumentContentId};";
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