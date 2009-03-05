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
			throw new Exception(var_export($_POST, true));
			
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
			
			if ($intDocumentId = (int)$_POST['Document_Edit_Id'])
			{
				// Create the Document
				$objDocument						= new Document();
				$objDocument->employee_id			= Flex::getUserId();
				$objDocument->is_system_document	= ($_POST['Document_Edit_System'] === true) ? true : false;
				
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
				$objDocument			= new Document(array('id'=>$intDocumentId), true);
				$objDocumentContent		= $objDocument->getContent();
				$objDocumentContent->id	= null;
			}
			
			// Populate new Content Version
			$objDocumentContent->employee_id	= Flex::getUserId();
			$objDocumentContent->status_id		= STATUS_ACTIVE;
			
			switch ($objDocument->document_nature_id)
			{
				case DOCUMENT_NATURE_FILE:
					
					break;
					
				case DOCUMENT_NATURE_FOLDER:
					
					break;
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
			$arrDetailsToRender['Message']	= $e->getMessage();
		}
		
		// Render the JSON'd Array
		flush();
		echo JSON_Services::instance()->encode($arrDetailsToRender);
		die;
	}
}
?>