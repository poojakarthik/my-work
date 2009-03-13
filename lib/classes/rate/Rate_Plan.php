<?php
//----------------------------------------------------------------------------//
// Rate_Plan
//----------------------------------------------------------------------------//
/**
 * Rate_Plan
 *
 * Models a record of the RatePlan table
 *
 * Models a record of the RatePlan table
 *
 * @class	Rate_Plan
 */
class Rate_Plan extends ORM
{
	protected	$_strTableName	= "RatePlan";
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * constructor
	 * 
	 * constructor
	 *
	 * @param	array	$arrProperties 		[optional]	Associative array defining the class with keys for each field of the table
	 * @param	boolean	$bolLoadById		[optional]	Automatically load the object with the passed Id
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public function __construct($arrProperties=Array(), $bolLoadById=FALSE)
	{
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
	}
	
	
	// Returns all RatePlan objects in an array where the id of the RatePlan is the key to the array
	// This array is sorted by RatePlan.Name
	public static function getAll() 
	{
		$selRatePlan = new StatementSelect("RatePlan", "*", "", "Name ASC");
		
		if ($selRatePlan->Execute() === FALSE)
		{
			throw new Exception("Failed to retrieve all RatePlans - ". $selRatePlan->Error());
		}
		
		$arrRatePlans = array();
		$arrRecordSet = $selRatePlan->FetchAll();
		foreach ($arrRecordSet as $arrRecord)
		{
			$objRatePlan = new self($arrRecord);
			$arrRatePlans[$objRatePlan->Id] = $objRatePlan;
		}
		return $arrRatePlans;
	}

	/**
	 * setBrochure()
	 *
	 * Sets the provided file as the Rate Plan's Brochure
	 * 
	 * @param	string		$strFilePath						Path to the file to use
	 * 
	 * @return	boolean
	 *
	 * @method
	 */
	public function setBrochure($strFilePath)
	{
		// Ensure the File is usable
		if (!is_file($strFilePath))
		{
			throw new Exception("Unable to open file '{$strFilePath}' to set as Plan Brochure");
		}
		
		$objBrochureDir			= Document::getByPath("/Plan Brochures/");
		$objCustomerGroupDir	= Document::getByPath("/Plan Brochures/{$this->customer_group}/");
		
		// Is there already an existing Brochure?
		if ($this->brochure_document_id)
		{
			// YES
			$objBrochureDocument	= new Document(array('id'=>$this->brochure_document_id), true);
		}
		else
		{
			// NO
			// Ensure that the Document Path /Plan Brochures/[CustomerGroup]/ exists
			if (!$objBrochureDir)
			{
				//throw new Exception("/Plan Brochures/ not found!");
				
				// Create the Plan Brochures node
				$objBrochureDir	= new Document();
				$objBrochureDir->document_nature_id	= DOCUMENT_NATURE_FOLDER;
				$objBrochureDir->employee_id		= Employee::SYSTEM_EMPLOYEE_ID;
				$objBrochureDir->is_system_document	= true;
				$objBrochureDir->save();
				
				$objBrochureDirContent	= new Document_Content();
				$objBrochureDirContent->document_id	= $objBrochureDir->id;
				$objBrochureDirContent->name		= "Plan Brochures";
				$objBrochureDirContent->employee_id	= Employee::SYSTEM_EMPLOYEE_ID;
				$objBrochureDirContent->status_id	= STATUS_ACTIVE;
				$objBrochureDirContent->save();
			}
			else
			{
				$objBrochureDirContent	= $objBrochureDir->getContent();
			}
			if (!$objCustomerGroupDir)
			{
				// Create the CustomerGroup node
				$objCustomerGroupDir	= new Document();
				$objCustomerGroupDir->document_nature_id	= DOCUMENT_NATURE_FOLDER;
				$objCustomerGroupDir->employee_id			= Employee::SYSTEM_EMPLOYEE_ID;
				$objCustomerGroupDir->is_system_document	= true;
				$objCustomerGroupDir->save();
				
				$objCustomerGroupDirContent	= new Document_Content();
				$objCustomerGroupDirContent->document_id		= $objCustomerGroupDir->id;
				$objCustomerGroupDirContent->name				= "{$this->customer_group}";
				$objCustomerGroupDirContent->constant_group		= "CustomerGroup:id,external_name";
				$objCustomerGroupDirContent->parent_document_id	= $objBrochureDir->id;
				$objCustomerGroupDirContent->employee_id		= Employee::SYSTEM_EMPLOYEE_ID;
				$objCustomerGroupDirContent->status_id			= STATUS_ACTIVE;
				$objCustomerGroupDirContent->save();
			}
			else
			{
				$objCustomerGroupDirContent	= $objCustomerGroupDir->getContent();
			}
			
			// Create a Document
			$objBrochureDocument	= new Document();
			$objBrochureDocument->document_nature_id	= DOCUMENT_NATURE_FILE;
			$objBrochureDocument->employee_id			= Flex::getUserId();
			$objBrochureDocument->is_system_document	= true;
			$objBrochureDocument->save();
			
			// Set this as the new Brochure
			$this->brochure_document_id	= $objBrochureDocument->id;
			$this->save();
		}
		
		$arrFileType	= File_Type::getForExtensionAndMimeType('pdf', 'application/pdf', true);
		
		// Create the new Content object
		$objBrochureDocumentContent	= new Document_Content();
		$objBrochureDocumentContent->document_id		= $objBrochureDocument->id;
		$objBrochureDocumentContent->name				= $this->name;
		$objBrochureDocumentContent->description		= $this->name . " Plan Brochure";
		$objBrochureDocumentContent->file_type_id		= $arrFileType['id'];
		$objBrochureDocumentContent->content			= file_get_contents($strFilePath);
		$objBrochureDocumentContent->parent_document_id	= $objCustomerGroupDir->id;
		$objBrochureDocumentContent->employee_id		= Flex::getUserId();
		$objBrochureDocumentContent->status_id			= STATUS_ACTIVE;
		$objBrochureDocumentContent->save();
		
		return true;
	}
	
	/**
	 * setAuthorisationScript()
	 *
	 * Sets the provided file as the Rate Plan's Authorisation Script
	 * 
	 * @param	string		$strFilePath						Path to the file to use
	 * 
	 * @return	boolean
	 *
	 * @method
	 */
	public function setAuthorisationScript($strFilePath)
	{
		// Ensure the File is usable
		if (!is_file($strFilePath))
		{
			throw new Exception("Unable to open file '{$strFilePath}' to set as Plan Authorisation Script");
		}
		
		$objAuthScriptDir		= Document::getByPath("/Authorisation Scripts/");
		$objCustomerGroupDir	= Document::getByPath("/Authorisation Scripts/{$this->customer_group}/");
		
		// Is there already an existing Auth Script?
		if ($this->auth_script_document_id)
		{
			// YES
			$objAuthScriptDocument	= new Document(array('id'=>$this->auth_script_document_id), true);
		}
		else
		{
			// NO
			// Ensure that the Document Path /Authorisation Scripts/[CustomerGroup]/ exists
			if (!$objAuthScriptDir)
			{
				//throw new Exception("/Authorisation Scripts/ not found!");
				
				// Create the Plan Brochures node
				$objAuthScriptDir	= new Document();
				$objAuthScriptDir->document_nature_id	= DOCUMENT_NATURE_FOLDER;
				$objAuthScriptDir->employee_id			= Employee::SYSTEM_EMPLOYEE_ID;
				$objAuthScriptDir->is_system_document	= true;
				$objAuthScriptDir->save();
				
				$objAuthScriptDirContent	= new Document_Content();
				$objAuthScriptDirContent->document_id	= $objAuthScriptDir->id;
				$objAuthScriptDirContent->name			= "Authorisation Scripts";
				$objAuthScriptDirContent->employee_id	= Employee::SYSTEM_EMPLOYEE_ID;
				$objAuthScriptDirContent->status_id		= STATUS_ACTIVE;
				$objAuthScriptDirContent->save();
			}
			else
			{
				$objAuthScriptDirContent	= $objAuthScriptDir->getContent();
			}
			if (!$objCustomerGroupDir)
			{
				//throw new Exception("/Authorisation Scripts/customer_group/ not found!");
				
				// Create the CustomerGroup node
				$objCustomerGroupDir	= new Document();
				$objCustomerGroupDir->document_nature_id	= DOCUMENT_NATURE_FOLDER;
				$objCustomerGroupDir->employee_id			= Employee::SYSTEM_EMPLOYEE_ID;
				$objCustomerGroupDir->is_system_document	= true;
				$objCustomerGroupDir->save();
				
				$objCustomerGroupDirContent	= new Document_Content();
				$objCustomerGroupDirContent->document_id		= $objCustomerGroupDir->id;
				$objCustomerGroupDirContent->name				= "{$this->customer_group}";
				$objCustomerGroupDirContent->constant_group		= "CustomerGroup:id,external_name";
				$objCustomerGroupDirContent->parent_document_id	= $objAuthScriptDir->id;
				$objCustomerGroupDirContent->employee_id		= Employee::SYSTEM_EMPLOYEE_ID;
				$objCustomerGroupDirContent->status_id			= STATUS_ACTIVE;
				$objCustomerGroupDirContent->save();
			}
			else
			{
				$objCustomerGroupDirContent	= $objCustomerGroupDir->getContent();
			}
			
			// Create a Document
			$objAuthScriptDocument	= new Document();
			$objAuthScriptDocument->document_nature_id	= DOCUMENT_NATURE_FILE;
			$objAuthScriptDocument->employee_id			= Flex::getUserId();
			$objAuthScriptDocument->is_system_document	= true;
			$objAuthScriptDocument->save();
			
			// Set this as the new Brochure
			$this->auth_script_document_id	= $objAuthScriptDocument->id;
			$this->save();
		}
		
		$arrFileType	= File_Type::getForExtensionAndMimeType('txt', 'text/plain', true);
		
		// Create the new Content object
		$objAuthScriptDocumentContent	= new Document_Content();
		$objAuthScriptDocumentContent->document_id			= $objAuthScriptDocument->id;
		$objAuthScriptDocumentContent->name					= $this->name;
		$objAuthScriptDocumentContent->description			= $this->name . " Authorisation Script";
		$objAuthScriptDocumentContent->file_type_id			= $arrFileType['id'];
		$objAuthScriptDocumentContent->content				= file_get_contents($strFilePath);
		$objAuthScriptDocumentContent->parent_document_id	= $objCustomerGroupDir->id;
		$objAuthScriptDocumentContent->employee_id			= Flex::getUserId();
		$objAuthScriptDocumentContent->status_id			= STATUS_ACTIVE;
		$objAuthScriptDocumentContent->save();
		
		return true;
	}
	
	/**
	 * generateEmailButtonOnClick()
	 *
	 * Retrieves a Document based on a passed pseudo-path
	 * 
	 * @param	[mixed			$mixRevision]						Revision of the Content to retrieve
	 * 																TRUE	: Latest Revision (default)
	 * 																FALSE	: Earliest Revision
	 * 																integer	: X Revisions ago (0 = current)
	 * 
	 * @return	Document_Content									The requested Statement
	 *
	 * @method
	 */
	public static function generateEmailButtonOnClick($intCustomerGroup, $arrRatePlans, $intAccountId=null)
	{
		$strPlans		= '';
		
		// Documents
		$arrDocuments		= array();
		foreach ($arrRatePlans as $mixRatePlan)
		{
			if ($mixRatePlan instanceof Rate_Plan)
			{
				$arrRatePlan	= $mixRatePlan->toArray();
			}
			elseif (is_array($mixRatePlan))
			{
				$arrRatePlan	= $mixRatePlan;
			}
			elseif ((int)$mixRatePlan > 1)
			{
				$objRatePlan	= new Rate_Plan(array('Id'=>$mixRatePlan), true);
				$arrRatePlan	= $objRatePlan->toArray();
			}
			else
			{
				// We will just skip
				continue;
			}
			
			$objDocument		= new Document(array('id'=>$arrRatePlan['brochure_document_id']), true);
			$objDocumentContent	= $objDocument->getContent();
			
			$intFileSizeKB	= round(mb_strlen($objDocumentContent->content) / 1024);
			$arrDocuments[]	= "{id: {$objDocument->id}, strFileName: \"".$objDocumentContent->getFileName()."\", intFileSizeKB: {$intFileSizeKB}, file_type_id: {$objDocumentContent->file_type_id}}";
			
			$strPlans	.= " - {$arrRatePlan['Name']}\\n";
		}
		if (!count($arrDocuments))
		{
			throw new Exception("There are no Documents to attach!");
		}		
		$strDocuments	= "new Array(".implode(",\n", $arrDocuments).")";
		
		// Recipients
		if ($intAccountId)
		{
			$objAccount		= new Account(array('Id'=>$intAccountId), false, true);
			$arrContacts	= $objAccount->getContacts(true);
			
			$arrRecipients	= array();
			foreach ($arrContacts as $objContact)
			{
				$arrContact	= $objContact->toArray();
				if ($arrContact['Archived'] === 0 && trim($arrContact['Email']) && stripos($arrContact['Email'], 'noemail@') === false)
				{
					$strPrimaryContact	= ($objAccount->PrimaryContact == $objContact->Id) ? 'true' : 'false';
					$arrRecipients[]	= "{name: \"{$arrContact['FirstName']} {$arrContact['LastName']}\", address: \"{$arrContact['Email']}\", is_primary_contact: {$strPrimaryContact}}";
				}
			}
			$strRecipients	= "new Array(".implode(",\n", $arrRecipients).")";
		}
		else
		{
			$strRecipients	= 'null';
		}
		
		$objCustomerGroup	= Customer_Group::getForId($intCustomerGroup);
		
		// Senders
		$arrSenders		= array();
		$objEmployee	= Employee::getForId(Flex::getUserId());
		$arrSenders[]	= "{name: \"{$objCustomerGroup->externalName} Customer Care\", address: \"contact@{$objCustomerGroup->emailDomain}\"}";
		if (trim($objEmployee->Email))
		{
			$arrSenders[]	= "{name: \"{$objEmployee->FirstName} {$objEmployee->LastName}\", address: \"{$objEmployee->Email}\"}";
		}
		$strSenders		= "new Array(".implode(",\n", $arrSenders).")";
		
		if (count($arrDocuments) > 1)
		{
			$strBrochurePlural	= "Brochures";
		}
		else
		{
			$strBrochurePlural	= "Brochure";
		}
		
		$strAccount			= ((int)$intAccountId > 0) ? "{$intAccountId}" : "null";
		
		// Generate HTML
		$strSubject			= "Requested {$objCustomerGroup->externalName} Plan {$strBrochurePlural}";
		$strContent			= "Dear <Addressee>,\\n\\nPlease find attached the Plan {$strBrochurePlural}:\\n\\n{$strPlans}\\nAs per your request.\\n\\nRegards,\\n\\nThe Team at {$objCustomerGroup->externalName}";
		
		return "JsAutoLoader.loadScript(\"javascript/document.js\", function(){Flex.Document.emailDocument($strDocuments, \"Plan {$strBrochurePlural}\", {$strSenders}, \"{$strSubject}\", \"{$strContent}\", {$strRecipients}, {$strAccount})});";
	}
	
	/**
	 * parseAuthenticationScript()
	 *
	 * Returns a rendered Authentication Script for changing a plan for a service
	 * 
	 * @param	Account				$objAccount				Account that the service belongs to
	 * @param	Contact				$objContact				Contact authorising the plan change
	 * @param	Service				$objService				Service having the plan change made to it
	 * @param	Service_Rate_Plan	$objRatePlanPrevious	(Optional, defaults to NULL) Current RatePlan associated with the service, if there is one
	 * 
	 * @return	string								Rendered Authentication script
	 *
	 * @method
	 */
	public function parseAuthenticationScript(Account $objAccount, Contact $objContact, Service $objService, Service_Rate_Plan $objRatePlanPrevious=NULL)
	{
		$strOriginalTimezone	= date_default_timezone_get();
		date_default_timezone_set("Australia/Brisbane");
		$strDate				= date("Y-m-d H:i:s");
		date_default_timezone_set($strOriginalTimezone);
		
		// Get the current Template
		$objTemplate		= Document::getByPath("/Authorisation Scripts/Templates/Authorisation Script Template");
		if (!$objTemplate)
		{
			throw new Exception("Could not find the document /Authorisation Scripts/Templates/Authorisation Script Template");
		}
		$objTemplateContent	= $objTemplate->getContent();
		
		// Get the Current Auth Script Blurb
		$strBlurb	= "[ There are no additional details specified in Flex for this Plan ]";
		if ($this->auth_script_document_id)
		{
			$objBlurb 			= new Document(array('id'=>$this->auth_script_document_id));
			$objBlurbContent	= $objBlurb->getContent();
			$strBlurb			= trim($objBlurbContent->content);
		}
		
		$objOldRatePlan		= ($objRatePlanPrevious !== NULL)? new Rate_Plan(array('Id'=>$objRatePlanPrevious->RatePlan), true) : NULL;
		
		$objEmployee		= Employee::getForId(Flex::getUserId());
		$objCustomerGroup	= Customer_Group::getForId($this->customer_group);
		$objServiceAddress	= $objService->getServiceAddress();
		
		$intTime			= strtotime($strDate);
		$strDateFormat		= "jS F, Y";
		$strTimeFormat		= "h:i a";
		
		$objVariables	= new Flex_Dom_Document();
		
		// Employee
		$objVariables->employee->name->setValue($objEmployee->firstName.' '.$objEmployee->lastName);
		
		// Customer Group
		$objVariables->customer_group->name->setValue($objCustomerGroup->externalName);
		
		// Dates
		$objVariables->datetime->today->date->setValue(date($strDateFormat, $intTime));
		$objVariables->datetime->today->time->setValue(date($strTimeFormat, $intTime));
		
		// Account
		$strABN	= (trim($objAccount->ABN)) ? $objAccount->ABN : '[ No ABN Specified ]';
		$objVariables->account->id->setValue($objAccount->Id);
		$objVariables->account->business_name->setValue($objAccount->BusinessName);
		$objVariables->account->abn->setValue($strABN);
		
		// Contact
		$dtDOB	= new DateTime($objContact->DOB);
		$objVariables->account->contact->name->setValue($objContact->firstName . ' ' . $objContact->lastName);
		$objVariables->account->contact->date_of_birth->setValue(($objContact->DOB && $objContact->DOB != '0000-00-00') ? $dtDOB->format("d/m/Y") : '[ No DOB Specified ]');
		$objVariables->account->contact->email->setValue((trim($objContact->email)) ? $objContact->email : '[ No Email Specified ]');
		
		// Previous Plan
		if ($objOldRatePlan)
		{
			// The Service has a current plan
			$bolIsContracted	= $objRatePlanPrevious->contract_scheduled_end_datetime !== null && $objRatePlanPrevious->contract_effective_end_datetime === null;
			$objVariables->plan->previous->name->setValue($objOldRatePlan->Name);
			$objVariables->plan->previous->is_contracted->setValue($bolIsContracted);
			if ($bolIsContracted)
			{
				$intMonthsRemaining	= Flex_Date::difference(date("Y-m-d", $intTime), $objRatePlanPrevious->contract_scheduled_end_datetime, 'm', 'ceil') + 1;
				$objVariables->plan->previous->contract->months_remaining->setValue($intMonthsRemaining . " month" . (($intMonthsRemaining == 1) ? '' : 's'));
				$objVariables->plan->previous->contract->start_date->setValue(date($strDateFormat, strtotime($objRatePlanPrevious->StartDatetime)));
			}
		}
		
		// New Plan
		$objVariables->plan->new->name->setValue($this->Name);
		$objVariables->plan->new->blurb->setValue(str_replace("\n", "<br />\n", $strBlurb));
		
		// Service
		$strFullAddress	= "[ No Address Specified in Flex ]";
		if ($objServiceAddress)
		{
			$strAddressType	= GetConstantDescription($objServiceAddress->ServiceAddressType, 'ServiceAddrType')." {$objServiceAddress->ServiceAddressTypeNumber}{$objServiceAddress->ServiceAddressTypeSuffix}\n";
			$strProperty	= "{$objServiceAddress->ServicePropertyName}\n";
			$strAddress		=	"{$objServiceAddress->ServiceStreetNumberStart} ".
								($objServiceAddress->ServiceStreetNumberEnd ? "-{$objServiceAddress->ServiceStreetNumberEnd}" : '').
								($objServiceAddress->ServiceStreetNumberSuffix ? "-{$objServiceAddress->ServiceStreetNumberSuffix}" : '').
								"{$objServiceAddress->ServiceStreetName} " .
								GetConstantDescription($objServiceAddress->ServiceStreetType, 'ServiceStreetType'). " " .
								"{$objServiceAddress->ServiceStreetTypeSuffix}\n";
			
			$strFullAddress	=	(trim($strProperty)		? $strProperty				: '') . 
								(trim($strAddressType)	? $strAddressType			: '') . 
								(trim($strAddress)		? $strAddress				: '') . 
								strtoupper("{$objServiceAddress->ServiceLocality}   ") .
								"{$objServiceAddress->ServiceState}   " .
								"{$objServiceAddress->ServicePostcode}";
		}
		$objVariables->service->has_address->setValue(in_array($objService->ServiceType, array(SERVICE_TYPE_LAND_LINE)));
		$objVariables->service->full_address->setValue($strFullAddress);
		$objVariables->service->fnn->setValue($objService->FNN);
		
		// Parse the Template, replacing the placeholders with valid data
		return Document_Template::render($objTemplateContent->content, $objVariables);
	}
	
	/**
	 * parseRejectionScript()
	 *
	 * Retrieves a Document based on a passed pseudo-path
	 * 
	 * @param	[Document	$objDocument	]				Document to merge with (will pull from DB if none is passed)
	 * 
	 * @return	string										Merged template
	 *
	 * @method
	 */
	public function parseRejectionScript(Account $objAccount, Contact $objContact, Service $objService, Service_Rate_Plan $objRatePlanPrevious)
	{
		$strOriginalTimezone	= date_default_timezone_get();
		date_default_timezone_set("Australia/Brisbane");
		$strDate				= date("Y-m-d H:i:s");
		date_default_timezone_set($strOriginalTimezone);
		
		// Get the current Template
		$objTemplate		= Document::getByPath("/Authorisation Scripts/Templates/Rejection Script Template");
		if (!$objTemplate)
		{
			throw new Exception("Could not find the document /Authorisation Scripts/Templates/Rejection Script Template");
		}
		$objTemplateContent	= $objTemplate->getContent();
		
		$intTime			= strtotime($strDate);
		$strDateFormat		= "jS F, Y";
		$strTimeFormat		= "h:i a";
		
		$qryQuery	= new Query();
		
		$objVariables	= new Flex_Dom_Document();
		
		// Admin Managers
		$objVariables->admin_managers	= '';
		$resAdminManagers				= $qryQuery->Execute("SELECT * FROM Employee WHERE user_role_id = ".USER_ROLE_ADMIN_MANAGER);
		if ($resAdminManagers === false)
		{
			throw new Exception($qryQuery->Error());
		}
		elseif ($resAdminManagers->num_rows)
		{
			$intAdminManagerCount	= 0;
			while ($arrAdminManager = $resAdminManagers->fetch_assoc())
			{
				$intAdminManagerCount++;
				$objVariables->admin_managers	.= $arrAdminManager['FirstName'] . (($arrAdminManager['LastName']) ? ' '.$arrAdminManager['LastName'] : "");
				if ($intAdminManagerCount == ($resAdminManagers->num_rows - 1))
				{
					$objVariables->admin_managers	.= ', or ';
				}
				elseif ($intAdminManagerCount < ($resAdminManagers->num_rows - 1))
				{
					$objVariables->admin_managers	.= ', ';
				}
			}
		}
		else
		{
			$objVariables->admin_managers	= "your Admin Manager";
		}
		
		// Early Exit Fee
		$objVariables->early_exit_fee_inc_gst		= round($objRatePlanPrevious->contract_exit_fee * 1.1, 2);
		$objVariables->half_early_exit_fee_inc_gst	= round($objVariables->early_exit_fee_inc_gst / 2, 2);
		
		// Parse the Template, replacing the placeholders with valid data
		return Document_Template::render($objTemplateContent->content, $objVariables);
	}
	
	//------------------------------------------------------------------------//
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 * 
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"RatePlan", "*", "Id = <Id>", NULL, 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("RatePlan");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("RatePlan");
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>