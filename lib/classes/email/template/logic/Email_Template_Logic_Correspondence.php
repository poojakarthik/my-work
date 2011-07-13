<?php
class Email_Template_Logic_Correspondence extends Email_Template_Logic
{
	private $_oEmailTemplateCorrespondence	= null;
	private $_oCorrespondenceTemplate		= null;
	
	public static $_iEmailQueueIdentifier	= 0;

	public function __construct($oEmailTemplate, $oEmailTemplateDetails=null)
	{
		parent::__construct($oEmailTemplate, $oEmailTemplateDetails);
		
		$this->_oEmailTemplateCorrespondence	= Email_Template_Correspondence::getForEmailTemplateId($this->_oEmailTemplate->email_template_id);
		if (!$this->_oEmailTemplateCorrespondence)
		{
			throw new Exception("Could not load email_template_correspondence for template: {$this->_oEmailTemplate->email_template_id}");
		}
	}
	
	public function getVariables()
	{
		$oQuery		= new Query();
		$sQuery		= preg_replace('/<[a-zA-Z_0-9]+>/', 'NULL', $this->_oEmailTemplateCorrespondence->datasource_sql);
		$mResult	= $oQuery->Execute($sQuery);
		if ($mResult === false)
		{
			throw new Exception("Failed to get null dataset to determine correspondence email template variables. ".$oQuery->Error());
		}
		
		$aFields 	= $mResult->fetch_fields();
		$aDudRecord	= array();
		foreach ($aFields as $oFieldProperties)
		{
			$aDudRecord[$oFieldProperties->name]	= '';
		}
		
		return self::_createVariableDataFromRecord($aDudRecord);
	}

	public function getData($aDataParameters)
	{
		// Generate the query from the query template
		$sQuery	= self::_generateDataSourceQuery($this->_oEmailTemplateCorrespondence->datasource_sql, $aDataParameters);
		
		// Get the records returned by the query
		$oQuery		= new Query();
		$mResult	= $oQuery->Execute($sQuery);
		if ($mResult === false)
		{
			throw new Exception("Failed to get data for correspondence email template parameters. {{$sQuery}} ".$oQuery->Error());
		}
		
		// Merge all records into a single record
		$aSingleRecord	= null;
		while($aRow = $mResult->fetch_assoc())
		{
			if ($aSingleRecord === null)
			{
				// Must be the first, use it
				$aSingleRecord	= $aRow;
			}
			else
			{
				// Overwrite each field in the record (only if not null)
				foreach ($aRow as $sField => $mValue)
				{
					if ($mValue !== null)
					{
						$aSingleRecord[$sField]	= $mValue;
					}
				}
			}
		}
		
		// Build the variable data array & return
		return self::_createVariableDataFromRecord($aSingleRecord);
	}
	
	function generateEmail($aDataParameters, Email_Flex $mEmail=null)
	{
		$aData = $this->getData($aDataParameters);
		return parent::generateEmail($aData, $mEmail);
	}
	
	public function getSampleData()
	{
		// Grab the latest piece of correspondence
		$aCorrespondence	= 	self::_getLatestCorrespndenceForTemplateAndCustomerGroup(
									$this->_oEmailTemplateCorrespondence->correspondence_template_id,
									$this->_oEmailTemplate->customer_group_id
								);
		if ($aCorrespondence === null)
		{
			// NO Correspondence in the system for the template yet.
			// Get the latest account & fill in the standard columns required for the template, leave additional 
			// columns blank. The default values should be handled by the email_template_correspondence.datasource_sql field.
			
			// Get the account
			$oQuery		= new Query();
			$mResult	= $oQuery->Execute("SELECT 	MAX(a.Id) AS account_id
											FROM 	Account a
											JOIN	Invoice i ON (i.Account = a.Id)
											WHERE	a.CustomerGroup = {$this->_oEmailTemplate->customer_group_id};");
			if ($mResult === false)
			{
				throw new Exception("Failed to get latest account id. ".$oQuery->Error());
			}
			
			$aRow	= $mResult->fetch_assoc();
			if (!$aRow || ($aRow['account_id'] === null))
			{
				// NOTE: This shouldn't happen unless there are no accounts in flex
				throw new Exception("No 'latest account id' found. Cannot test the email template without any accounts.");
			}
			
			$oAccount			= Account::getForId($aRow['account_id']);
			$oPrimaryContact	= Contact::getForId($oAccount->PrimaryContact);
			$aCorrespondence	= 	array(
										'account_id'						=> $oAccount->Id,
										'customer_group_id'					=> $oAccount->CustomerGroup,
										'correspondence_delivery_method_id'	=> Correspondence_Delivery_Method::getForId(CORRESPONDENCE_DELIVERY_METHOD_POST)->system_name,
										'account_name'						=> $oAccount->BusinessName,
										'title'								=> $oPrimaryContact->Title,
										'first_name'						=> $oPrimaryContact->FirstName,
										'last_name'							=> $oPrimaryContact->LastName,
										'address_line_1'					=> $oAccount->Address1,
										'address_line_2'					=> $oAccount->Address2,
										'suburb'							=> $oAccount->Suburb,
										'postcode'							=> $oAccount->Postcode,
										'state'								=> $oAccount->State,
										'email'								=> $oPrimaryContact->Email,
										'mobile'							=> $oPrimaryContact->Mobile,
										'landline'							=> $oPrimaryContact->Phone
									);
			
			$oTemplate			= $this->getCorrespondenceTemplate();
			$iStandardColumns	= count($aCorrespondence);
			$aAdditionalColumns	= $oTemplate->getAdditionalColumnSet($iStandardColumns);
			foreach ($aAdditionalColumns as $sColumn)
			{
				$aCorrespondence[$sColumn]	= null;
			}
		}
		
		return $this->getData($aCorrespondence);
	}
	
	private function getCorrespondenceTemplate()
	{
		if ($this->_oCorrespondenceTemplate === null)
		{
			$this->_oCorrespondenceTemplate = Correspondence_Logic_Template::getForId($this->_oEmailTemplateCorrespondence->correspondence_template_id);
		}
		return $this->_oCorrespondenceTemplate;
	}
	
	public static function sendCorrespondenceEmails($aCorrespondenceRecords, $aPDFFilePaths=array())
	{
		try
		{
			// Verify the pdf file paths is array
			if (!is_array($aPDFFilePaths))
			{
				$aPDFFilePaths	= array();
			}
			
			// Create an Email_Flex_Queue and push each email
			self::$_iEmailQueueIdentifier++;
			$oEmailFlexQueue	= Email_Flex_Queue::get("send_correspondence_".self::$_iEmailQueueIdentifier);
			foreach ($aCorrespondenceRecords as $aCorrespondence)
			{
				$oDeliveryMethod	= Correspondence_Delivery_Method::getForSystemName($aCorrespondence['correspondence_delivery_method']);
				if ($oDeliveryMethod->id !== CORRESPONDENCE_DELIVERY_METHOD_EMAIL)
				{
					// Ignore this correspondence, not an email correspondence
					continue;
				}
				
				$iCorrespondenceId	= $aCorrespondence['id'];
				$sPDFFilePath		= (isset($aPDFFilePaths[$iCorrespondenceId]) ? $aPDFFilePaths[$iCorrespondenceId] : null);
				$oEmailFlexQueue->push(self::generateEmailFromCorrespondenceRecord($aCorrespondence, $sPDFFilePath), $iCorrespondenceId);
			}
			
			// Schedule the queue
			$oEmailFlexQueue->scheduleForDelivery(null, "Correspondence Emails");
		}
		catch (Exception $oException)
		{
			Log::getLog()->log("Failed to send correspondence emails. ".$oException->getMessage());
			throw new Correspondence_Dispatch_Exception(Correspondence_Dispatch_Exception::MAILHOUSE_PROCESSING, $oException);
		}
	}
	
	public static function generateEmailFromCorrespondenceRecord($aCorrespondence, $sPDFFilePath=null)
	{
		$iCorrespondenceId	= $aCorrespondence['id'];
		
		// Get the email_template.id
		$oQuery		= new Query();
		$sQuery		= "	SELECT	etc.email_template_id AS email_template_id
						FROM	email_template_correspondence etc
						JOIN	correspondence_template ct ON (ct.id = etc.correspondence_template_id)
						JOIN	correspondence_run cr ON (cr.correspondence_template_id = ct.id)
						JOIN	correspondence c ON (c.correspondence_run_id = cr.id)
						WHERE	c.id = {$iCorrespondenceId}";
		$mResult	= $oQuery->Execute($sQuery);
		if ($mResult === false)
		{
			throw new Exception("Failed to get email_template.id for correspondence.id. ".$oQuery->Error());
		}
		
		$aRow	= $mResult->fetch_assoc();
		if (!$aRow)
		{
			throw new Exception("Failed to get email_template.id for correspondence.id. Could not find an associated email_template.");
		}
		
		// Get the template logic instance
		$oTemplateLogic	= Email_Template_Logic::getInstance($aRow['email_template_id'], $aCorrespondence['customer_group_id']);
		
		// Generate the Email_Flex object
		$oEmail	= $oTemplateLogic->generateEmail($aCorrespondence);
		
		// Add the recipient
		$sEmail	= $aCorrespondence['email'];
		if (($sEmail !== null) && ($sEmail !== ''))
		{
			$oEmail->addTo($sEmail);
		}
		
		// Add the pdf (if given) as an attachment
		if ($sPDFFilePath !== null)
		{
			$sPDFContents	= file_get_contents($sPDFFilePath);
			if ($sPDFContents === '')
			{
				throw new Exception("Empty PDF File for correspondence id {$iCorrespondenceId}.");
			}

			$sFileName = basename ($sPDFFilePath);
			$oEmail->createAttachment($sPDFContents, 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $sFileName);
		}
		
		return $oEmail;
	}
	
	private static function _generateDataSourceQuery($sQueryTemplate, $aParameters)
	{
		$sQuery	= $sQueryTemplate;
		foreach ($aParameters as $sParameter => $mValue)
		{
			$sValue	= ($mValue === null ? 'NULL' : "'{$mValue}'");
			$sQuery	= preg_replace('/<'.$sParameter.'>/', $sValue, $sQuery);
		}
		return $sQuery;
	}
	
	private static function _createVariableDataFromRecord($aRecord)
	{
		$aVariableData	= array();
		foreach ($aRecord as $sField => $mValue)
		{
			preg_match('/([A-Za-z0-9_]+)\.([A-Za-z0-9_]+)/', $sField, $aMatches);
			if ($aMatches)
			{
				$sObject	= $aMatches[1];
				$sProperty	= $aMatches[2];
				if (!isset($aVariableData[$sObject]))
				{
					$aVariableData[$sObject]	= array();
				}
				$aVariableData[$sObject][$sProperty]	= $mValue; 
			}
		}
		
		return $aVariableData;
	}
	
	private static function _getLatestCorrespndenceForTemplateAndCustomerGroup($iTemplateId, $iCustomerGroup)
	{
		$oQuery		= new Query();
		$sQuery		= "	SELECT	MAX(c.id) AS correspondence_id
						FROM	correspondence c
						JOIN	correspondence_run cr ON cr.id = c.correspondence_run_id
						WHERE	cr.correspondence_template_id = {$iTemplateId}
						AND		c.customer_group_id = {$iCustomerGroup}";
		$mResult	= $oQuery->Execute($sQuery);
		if ($mResult === false)
		{
			throw new Exception("Failed to get latest piece of correspondence for template {$iTemplateId} and customer group {$iCustomerGroup}. ".$oQuery->Error());
		}
		
		$aRow	= $mResult->fetch_assoc();
		if ($aRow && ($aRow['correspondence_id'] !== null))
		{
			$oCorrespondenceLogic	= new Correspondence_Logic(Correspondence::getForId($aRow['correspondence_id']));
			return $oCorrespondenceLogic->toArray();
		}
		
		return null;
	}
}
?>