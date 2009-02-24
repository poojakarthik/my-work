<?php

// Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

/*$resFileInfoDB		= new finfo(FILEINFO_MIME);
if (!$resFileInfoDB)
{
	throw new Exception("Unable to load MIME Database");
}*/

$objDocument		= new Document();
$objDocumentContent	= new Document_Content();

//----------------------------------------------------------------------------//
// PROMPTS
//----------------------------------------------------------------------------//

// Path
do
{
	$strPath		= trim(getUserInput("Please enter the Document path for the Document you want to create: "));
	
	// Check the directory
	$strParentPath	= dirname($strPath);
	$strParentPath	= ($strParentPath === '.') ? $strParentPath = '' : $strParentPath;
	if ($strParentPath)
	{
		$objParentDocument = Document::getByPath($strParentPath);
		if (!$objParentDocument)
		{
			throw new Exception("The parent path '{$strParentPath}' does not exist");
		}
		else
		{
			$objDocumentContent->parent_brochure_id	= $objParentDocument->id;
		}
	}
	
	// Check the file Name (can't contain /'s, new lines, or tabs)
	$strName	= basename($strPath);
	if (preg_match("/^[^\r\n\t\/]+$/i", $strName))
	{
		$objDocumentContent->name	= $strName;
	}
	
	// Does this Document already exist?
	$objDocumentExists = Document::getByPath($strPath);
	if (!$objDocumentExists)
	{
		// Yes -- add a new version
		$objDocument	= $objDocumentExists;
	}
}
while (!$objDocumentContent->name);

// Document Nature (File/Folder) (only for new Documents)
if (!$objDocument->id)
{
	do
	{
		$strNature		= trim(getUserInput("What is the nature of the document [FILE|FOLDER] ? "));
		switch (strtoupper($strNature))
		{
			case 'FILE':
				$objDocument->document_nature_id	= DOCUMENT_NATURE_FILE;
				break;
				
			case 'FOLDER':
				$objDocument->document_nature_id	= DOCUMENT_NATURE_FOLDER;
				break;
		}
	}
	while (!$objDocument->document_nature_id);
}

// Description (optional)
$strDescription		= trim(getUserInput("Please enter the Description for the Documents (optional): "));
if ($strDescription)
{
	$objDocumentContent->description	= $strDescription;
}

// Files Only
if ($objDocument->document_nature_id === DOCUMENT_NATURE_FILE)
{
	// Content File Path (optional)
	do
	{
		$strContentPath		= trim(getUserInput("Please enter the path to the file to import: "));
		if (is_file($strContentPath))
		{
			// File Extension
			$strBaseName	= basename($strContentPath);
			$strExtension	= substr($strBaseName, strripos($strBaseName, '.') + 1);
			
			// Mime Type
			//$strMimeContentType	= $resFileInfoDB->file($strContentPath);
			$strMimeContentType	= mime_content_type($strContentPath);
			
			// Find File/Mime Type Combination in Flex
			$objFileType	= File_Type::getForExtensionAndMimeType($strExtension, $strMimeContentType);
			if ($objFileType)
			{
				$objDocumentContent->file_type_id	= $objFileType->id;
				$objDocumentContent->mime_type_id	= $objFileType->mime_type_id;
			}
			else
			{
				throw new Exception("'{$strBaseName}' has an extension of '{$strExtension}' and MIME Content type of '{$strMimeContentType}', which are not supported in Flex");
			}
			
			// Set Document Content
			$objDocumentContent->content	= file_get_contents($strContentPath);
		}
	}
	while (!$objDocumentContent->file_type_id || !$objDocumentContent->mime_type_id);
}

//----------------------------------------------------------------------------//

// Save to the DB
DataAccess::getDataAccess()->TransactionStart();
try
{
	// Only save the Document if it's new
	if (!$objDocument->id)
	{
		$objDocument->employee_id	= Employee::SYSTEM_EMPLOYEE_ID;
		$objDocument->save();
	}
	
	$objDocumentContent->document_id	= $objDocument->id;
	$objDocumentContent->employee_id	= Employee::SYSTEM_EMPLOYEE_ID;
	$objDocumentContent->status_id		= STATUS_ACTIVE;
	$objDocumentContent->save();
	
	throw new Exception("TEST MODE");
	
	DataAccess::getDataAccess()->TransactionCommit();
}
catch (Exception $eException)
{
	DataAccess::getDataAccess()->TransactionRollback();
	throw $eException;
}

exit(0);



function getUserInput($strPrompt)
{
	CliEcho($strPrompt, false);
	$strResponse	= fgets(STDIN);	
	return $strResponse;
}
?>