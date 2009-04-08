<?php

require_once('../../lib/classes/Flex.php');
Flex::load();

$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);

if (PEAR::isError($resBegin = $dbAdmin->beginTransaction()))
{
	throw new Exception($resBegin->getMessage()."\n\n".$resBegin->getUserInfo());
}

try
{
	// Decompress all document_content.content data with BZIP2
	$resDocumentContent	= $dbAdmin->query("SELECT * FROM document_content WHERE content IS NOT NULL");
	if (PEAR::isError($resDocumentContent))
	{
		throw new Exception(__CLASS__ . ' Failed to retrieve the list of document_content Records. ' . $resDocumentContent->getMessage() . " (DB Error: " . $resDocumentContent->getUserInfo() . ")");
	}
	while ($arrDocumentContent = $resDocumentContent->fetchRow())
	{
		// Compress with BZIP2
		$mixUncompressed	= bzdecompress($arrDocumentContent['content']);
		if (is_int($mixUncompressed))
		{
			// Error
			throw new Exception("Unable to uncompress Content for Document {$arrDocumentContent['document_id']} (Revision: {$arrDocumentContent['id']}): Error #{$mixUncompressed}");
		}
		
		if (PEAR::isError($strNewContent = $dbAdmin->quote($mixUncompressed, 'text')))
		{
			throw new Exception(__CLASS__ . ' Failed to quote new document_content.content: '. $strNewContent->getMessage() . " (DB Error: " . $strNewContent->getUserInfo() . ")");
		}
		if (PEAR::isError($intDocumentContentId = $dbAdmin->quote($arrDocumentContent['id'], 'integer')))
		{
			throw new Exception(__CLASS__ . ' Failed to quote document_content.id: '. $intDocumentContentId->getMessage() . " (DB Error: " . $intDocumentContentId->getUserInfo() . ")");
		}
		
		$strUpdateSQL	=	"UPDATE	document_content " .
							"SET	content					= {$strNewContent}" .
							"WHERE	id		= {$intDocumentContentId};";
		
		$resDocumentContentUpdate	= $dbAdmin->exec($strUpdateSQL);
		if (PEAR::isError($resDocumentContentUpdate))
		{
			throw new Exception(__CLASS__ . ' Failed to decompress document_content '.$arrDocumentContent['id'].'. ' . $resDocumentContentUpdate->getMessage() . " (DB Error: " . $resDocumentContentUpdate->getUserInfo() . ")");
		}
	}
	
	if (PEAR::isError($resCommit = $dbAdmin->commit()))
	{
		throw new Exception($resCommit->getMessage()."\n\n".$resCommit->getUserInfo());
	}
}
catch (Exception $eException)
{
	if (PEAR::isError($resRollback = $dbAdmin->rollback()))
	{
		throw new Exception($eException->__toString()."\n\n".$resRollback->getMessage()."\n\n".$resRollback->getUserInfo());
	}
	
	throw $eException;
}

?>