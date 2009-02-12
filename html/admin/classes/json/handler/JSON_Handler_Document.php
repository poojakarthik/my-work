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
	
	public function sendEmail($arrTo, $strFrom, $strSubject, $strContent, $arrDocuments)
	{
		set_include_path(get_include_path() . PATH_SEPARATOR . realpath(str_replace('/', DIRECTORY_SEPARATOR, FLEX_BASE_PATH."lib")));
		require_once('Zend/Mail.php');
		
		try
		{
			$qryQuery	= new Query();
			
			//throw new Exception(print_r($arrTo, true));
			
			// Build the Email
			$objEmail	= new Zend_Mail();
			$objEmail->setBodyText($strContent);
			$objEmail->setFrom($strFrom);
			$objEmail->setSubject($strSubject);
			
			if (is_array($arrTo) && count($arrTo))
			{
				foreach ($arrTo as $jobToAddress)
				{
					$objEmail->addTo($jobToAddress->address, $jobToAddress->name);
				}
			}
			else
			{
				throw new Exception("No Recipients specified");
			}
			
			// Add attachments
			if (is_array($arrDocuments) && count($arrDocuments))
			{
				foreach ($arrDocuments as $jobDocument)
				{
					throw new Exception(print_r($jobDocument, true));
					
					$objDocument		= new Document($jobDocument->id, true);
					$objDocumentContent	= $objDocument->getContent();
					$objFileType		= new File_Type(array('id'=>$objDocumentContent->file_type_id), true);
					$objMimeType		= new Mime_Type(array('id'=>$objFileType->mime_type_id), true);
					
					$attAttachment				= $objEmail->createAttachment($objDocumentContent->content);
					$attAttachment->type		= $objMimeType->mime_content_type;
					$attAttachment->filename	= $objDocumentContent->getFileName();
				}
			}
			else
			{
				throw new Exception("No Documents specified");
			}
			
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
}
?>