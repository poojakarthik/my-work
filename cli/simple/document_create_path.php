<?php

// Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

$objDocument		= new Document();
$objDocumentContent	= new Document_Content();

//----------------------------------------------------------------------------//
// PROMPTS
//----------------------------------------------------------------------------//

// Path
do
{
	$strPath		= trim(getUserInput("Please enter the Document path for the Document you want to create: "));
	$strParentPath	= dirname($strPath);
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
}
while (!$strPath);

// Document Nature (File/Folder)
do
{
	$strNature		= trim(getUserInput("What is the nature of the document [FILE|FOLDER] ? "));
	switch (strtoupper($strNature))
	{
		case 'FILE':
			$objDocument->document_nature_id	= DOCUMENT_NATURE_FILE;
			break;
			
		case 'FOLDER':
			$objDocument->document_nature_id	= DOCUMENT_NATURE_FILE;
			break;
		
		default:
			$strNature	= null;
			break;
	}
}
while (!$strNature);

// Name
// TODO

// Description (optional)
// TODO

// File Type (files only)
// TODO

// Content File Path (files only)(optional)
// TODO



//----------------------------------------------------------------------------//

exit(0);



function getUserInput($strPrompt)
{
	CliEcho($strPrompt, false);
	$strResponse	= fgets(STDIN);	
	return $strResponse;
}
?>