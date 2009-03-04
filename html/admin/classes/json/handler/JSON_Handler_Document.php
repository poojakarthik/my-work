<?php

class JSON_Handler_Document extends JSON_Handler
{
	protected	$_JSONDebug	= '';
		
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function sendEmail($arrTo, $jobFrom, $strSubject, $strContent, $arrDocuments, $intAccount=null)
	{
		set_include_path(get_include_path() . PATH_SEPARATOR . realpath(str_replace('/', DIRECTORY_SEPARATOR, FLEX_BASE_PATH."lib")));
		require_once('Zend/Mail.php');
		
		try
		{
			$qryQuery	= new Query();
			
			// Build the Email
			$objEmail	= new Zend_Mail();
			$objEmail->setBodyText($strContent);
			$objEmail->setFrom($jobFrom->address, $jobFrom->name);
			$objEmail->setSubject($strSubject);
			
			$arrToContent	= array();
			if (is_array($arrTo) && count($arrTo))
			{
				foreach ($arrTo as $jobToAddress)
				{
					$objEmail->addTo($jobToAddress->address, $jobToAddress->name);
					$arrToContent[]	= ($jobToAddress->name) ? "{$jobToAddress->name} ($jobToAddress->address)" : $jobToAddress->address;
				}
			}
			else
			{
				throw new Exception("No Recipients specified");
			}
			
			// Add attachments
			$arrAttachmentContent	= array();
			if (is_array($arrDocuments) && count($arrDocuments))
			{
				foreach ($arrDocuments as $jobDocument)
				{
					$objDocument		= new Document(array('id'=>$jobDocument->id), true);
					$objDocumentContent	= $objDocument->getContent();
					$objFileType		= new File_Type(array('id'=>$objDocumentContent->file_type_id), true);
					$objMimeType		= new Mime_Type(array('id'=>$objFileType->mime_type_id), true);
					
					$attAttachment				= $objEmail->createAttachment($objDocumentContent->content);
					$attAttachment->type		= $objMimeType->mime_content_type;
					$attAttachment->filename	= $objDocumentContent->getFileName();
					
					$arrAttachmentContent[]	= " - ".$objDocumentContent->description;
				}
			}
			else
			{
				throw new Exception("No Documents specified");
			}
			
			// Add in a System Note if this is sent to a particular Account
			if ($intAccount > 0)
			{
				try
				{
					$objAccount	= new Account(array('id'=>$intAccount), false, true);
					
					$strNoteContent	=	"Flex Documents have been emailed with the following details:\n" .
										"\n" .
										"To: ".implode('; ', $arrToContent)."\n".
										"Subject: {$strSubject}\n" .
										"Documents:\n" .
										implode("\n", $arrAttachmentContent);
					Note::createSystemNote($strNoteContent, Flex::getUserId(), $objAccount->AccountGroup, $objAccount->Id);
				}
				catch (Exception_ORM_LoadById $eException)
				{
					throw new Exception("Unable to create a System Note for Account #{$intAccount}.  The email has not been sent.");
				}
				
			}
			
			// The System Note has been added -- Send off the email!
			$objEmail->send();
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"		=> true,
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"	=> false,
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
	
	public function getDirectoryListing($intDocumentId=null)
	{
		try
		{
			$qryQuery	= new Query();
			$arrRootDir	= array('name'=>Document::ROOT_DIRECTORY_NAME, 'document_id'=>null, 'friendly_name'=>Document::ROOT_DIRECTORY_NAME);
			
			$bolSuperAdmin	= AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN);
			$bolGOD			= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
			$bolProperAdmin	= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
			
			// If we have a document, load its children
			$objDocumentOutput	= new stdClass();
			if ($intDocumentId)
			{
				// Load the Document
				$objDocument		= new Document(array('id'=>$intDocumentId));
				$objDocumentContent	= $objDocument->getContentDetails();
				
				$objDocumentOutput->strName			= $objDocumentContent->name;
				$objDocumentOutput->strDescription	= $objDocumentContent->description;
				$objDocumentOutput->strFriendlyName	= $objDocumentContent->getFriendlyName();
				$objDocumentOutput->editable		= ((!$objDocument->is_system_document && $bolProperAdmin) || $bolGOD) ? true : false;
				
				$objDocumentOutput->arrPath			= $objDocument->getPath(true);
			}
			else
			{
				// Assume we are viewing the root directory
				$objDocumentOutput->strName			= Document::ROOT_DIRECTORY_NAME;
				$objDocumentOutput->strDescription	= Document::ROOT_DIRECTORY_NAME;
				$objDocumentOutput->strFriendlyName	= Document::ROOT_DIRECTORY_NAME;
				$objDocumentOutput->editable		= $bolProperAdmin;
				$objDocumentOutput->arrPath			= array($arrRootDir);
			}
			
			// Load the children
			$arrChildren					= Document::getChildrenForId($intDocumentId, true);
			$objDocumentOutput->arrChildren	= array();
			foreach ($arrChildren as $arrChild)
			{
				$objChild			= new Document($arrChild);
				$objChildContent	= $objChild->getContentDetails();
				
				// Hide system documents from general users
				if (!(bool)$objChild->is_system_document || $bolSuperAdmin)
				{
					$objChildOutput	= new stdClass();
					
					$objFileType	= ($objChildContent->file_type_id) ? new File_Type(array('id'=>$objChildContent->file_type_id), true) : null;
					$objMimeType	= ($objChildContent->file_type_id) ? new Mime_Type(array('id'=>$objFileType->mime_type_id), true) : null;
					
					$objModifiedBy	= Employee::getForId($objChildContent->employee_id);
					
					$objChildOutput->id				= $objChild->id;
					$objChildOutput->name			= $objChildContent->name;
					$objChildOutput->friendly_name	= $objChildContent->getFriendlyName();
					$objChildOutput->description	= $objChildContent->description;
					$objChildOutput->nature			= GetConstantName($objChild->document_nature_id, 'document_nature');
					$objChildOutput->file_type_id	= $objChildContent->file_type_id;
					$objChildOutput->extension		= ($objChildContent->file_type_id) ? $objFileType->extension : '';
					$objChildOutput->has_icon		= ($objChildContent->file_type_id) ? File_Type::hasIcon($objChildContent->file_type_id, 16) : false;
					$objChildOutput->file_size		= $objChildContent->intContentSize;
					$objChildOutput->date_modified	= date("j/n/Y g:i A", strtotime($objChildContent->changed_on));
					$objChildOutput->modified_by	= $objModifiedBy->firstName.' '.$objModifiedBy->lastName;
					$objChildOutput->editable		= ((!$objChild->is_system_document && $bolProperAdmin) || $bolGOD) ? true : false;
					$objChildOutput->system			= (bool)$objChild->is_system_document;
					$objChildOutput->mime			= ($objChildContent->file_type_id) ? $objMimeType->mime_content_type : '';
					$objChildOutput->file_type		= ($objChildContent->file_type_id) ? $objFileType->description : '';
					
					$objDocumentOutput->arrChildren[]	= $objChildOutput;
				}
			}
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"		=> true,
							"objDocument"	=> $objDocumentOutput,
							"strDebug"		=> ($bolGOD) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"	=> false,
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> ($bolGOD) ? $this->_JSONDebug : ''
						);
		}
	}
	
	public function buildEditPopup($intDocumentId=null)
	{
		try
		{
			$qryQuery	= new Query();
			
			
			
			// If no exceptions were thrown, then everything worked
			return array(
							"Success"		=> true,
							"strDebug"		=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
		catch (Exception $e)
		{
			// Send an Email to Devs
			//SendEmail("rdavis@yellowbilling.com.au", "Exception in ".__CLASS__, $e->__toString(), CUSTOMER_URL_NAME.'.errors@yellowbilling.com.au');
			
			return array(
							"Success"	=> false,
							"Message"	=> 'ERROR: '.$e->getMessage(),
							"strDebug"	=> (AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_GOD)) ? $this->_JSONDebug : ''
						);
		}
	}
}
?>