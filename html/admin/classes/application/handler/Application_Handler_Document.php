<?php

class Application_Handler_Document extends Application_Handler
{
	// Saves a New or Existing Document
	public function Save($subPath)
	{
		$bolVerboseErrors	= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
		$arrDetailsToRender	= array();
		try
		{
			//throw new Exception(var_export($_POST, true));
			
			if (!DataAccess::getDataAccess()->TransactionStart())
			{
				throw new Exception("Flex was unable to start a Transaction.  The Upload has been aborted.  Please try again shortly.");
			}
			
			$qryQuery	= new Query();
			
			// Check user permissions
			if (!AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN))
			{
				throw new Exception("You do not have sufficient privileges to create a "+GetConstantDescription($objDocument->document_nature_id, 'document_nature')+"!");
			}
			
			$intParentDocumentId	= ($_POST['Document_Edit_Parent'] === '') ? null : (int)$_POST['Document_Edit_Parent'];
			$strDocumentName		= trim($_POST['Document_Edit_Name']);
			$intDocumentId			= (int)$_POST['Document_Edit_Id'];
			
			// Does a Document exist at this path?
			if ($intParentDocumentId)
			{
				$objParentDocument		= new Document(array('id'=>$intParentDocumentId), true);
				$strPath				= $objParentDocument->getPath().'/';
			}
			else
			{
				$strPath				= '/';
			}
			$objExistingDocument = Document::getByPath($strPath.$strDocumentName);
			if ($objExistingDocument && (!$intDocumentId || ($intDocumentId && $objExistingDocument->id != $intDocumentId)))
			{
				throw new Exception("There is already an item with the Name '{$strDocumentName}' in this Folder".($bolVerboseErrors ? " ({$strPath}{$strDocumentName})" : ''));
			}
			
			if (!$intDocumentId)
			{
				// Create the Document
				$objDocument						= new Document();
				$objDocument->employee_id			= Flex::getUserId();
				$objDocument->is_system_document	= ($_POST['Document_Edit_System'] === 'true') ? true : false;
				
				if (GetConstantName(constant($_POST['Document_Edit_Nature']), 'document_nature') !== $_POST['Document_Edit_Nature'])
				{
					throw new Exception("Document Nature '{$_POST['Document_Edit_Nature']}' is invalid!");
				}
				$objDocument->document_nature_id	= constant($_POST['Document_Edit_Nature']);
				
				// Check special permissions for System Documents
				if ($objDocument->is_system_document && !AuthenticatedUser()->UserHasPerm(PERMISSION_GOD))
				{
					throw new Exception("You do not have sufficient privileges to create a System "+GetConstantDescription($objDocument->document_nature_id, 'document_nature')+"!");
				}
				
				$objDocument->save();
				
				// Create the Document Content
				$objDocumentContent					= new Document_Content();
				$objDocumentContent->document_id	= $objDocument->id;
			}
			else
			{
				// Load the Document & its Content
				$objDocument						= new Document(array('id'=>$intDocumentId), true);
				$objOldDocumentContent				= $objDocument->getContent();
				
				$objDocumentContent					= clone $objOldDocumentContent;
				$objDocumentContent->changed_on		= null;
			}
			
			// Populate new Content Version
			$objDocumentContent->employee_id		= Flex::getUserId();
			$objDocumentContent->status_id			= STATUS_ACTIVE;
			$objDocumentContent->parent_document_id	= $intParentDocumentId;
			$objDocumentContent->name				= $strDocumentName;
			$objDocumentContent->description		= trim($_POST['Document_Edit_Description']);
			
			if ($objDocument->document_nature_id === DOCUMENT_NATURE_FILE && $_FILES['Document_Edit_File'])
			{
				// Determine File Type
				$strExtension	= array_pop(explode('.', $_FILES['Document_Edit_File']['name']));
				$arrFileType	= File_Type::getForExtensionAndMimeType($strExtension, $_FILES['Document_Edit_File']['type'], true);
				if (!$arrFileType)
				{
					throw new Exception("The File you have uploaded is not permitted by Flex".($bolVerboseErrors ? " ({$strExtension}|{$_FILES['Document_Edit_File']['type']})" : ''));
				}
				$objDocumentContent->file_type_id	= $arrFileType['id'];
				
				$objDocumentContent->content		= file_get_contents($_FILES['Document_Edit_File']['tmp_name']);
			}
			
			$objDocumentContent->save();
			
			// Commit the transaction
			DataAccess::getDataAccess()->TransactionCommit();
			
			// Generate Response
			$arrDetailsToRender['Success']	= true;
		}
		catch (Exception $e)
		{
			DataAccess::getDataAccess()->TransactionRollback();
			
			$arrDetailsToRender['Success']	= false;
			$arrDetailsToRender['Message']	= $e->getMessage().($bolVerboseErrors ? " ('".$e->getFile()."' @ Line ".$e->getLine().") ".$e->getTraceAsString() : '');
		}
		
		// Render the JSON'd Array
		flush();
		echo JSON_Services::instance()->encode($arrDetailsToRender);
		die;
	}
}
?>
