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
			if (!($objBrochureDir = Document::getByPath("/Plan Brochures/")))
			{
				//throw new Exception("/Plan Brochures/ not found!");
				
				// Create the Plan Brochures node
				$objBrochureDir	= new Document();
				$objBrochureDir->document_nature_id	= DOCUMENT_NATURE_FOLDER;
				$objBrochureDir->employee_id		= Employee::SYSTEM_EMPLOYEE_ID;
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
			if (!($objCustomerGroupDir = Document::getByPath("/Plan Brochures/{$this->customer_group}/")))
			{
				//throw new Exception("/Plan Brochures/customer_group/ not found!");
				
				// Create the CustomerGroup node
				$objCustomerGroupDir	= new Document();
				$objCustomerGroupDir->document_nature_id	= DOCUMENT_NATURE_FOLDER;
				$objCustomerGroupDir->employee_id			= Employee::SYSTEM_EMPLOYEE_ID;
				$objCustomerGroupDir->save();
				
				$objCustomerGroupDirContent	= new Document_Content();
				$objCustomerGroupDirContent->document_id		= $objCustomerGroupDir->id;
				$objCustomerGroupDirContent->name				= "{$this->customer_group}";
				$objCustomerGroupDirContent->constant_group		= "CustomerGroup";
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
			if (!($objAuthScriptDir = Document::getByPath("/Authorisation Scripts/")))
			{
				//throw new Exception("/Authorisation Scripts/ not found!");
				
				// Create the Plan Brochures node
				$objAuthScriptDir	= new Document();
				$objAuthScriptDir->document_nature_id	= DOCUMENT_NATURE_FOLDER;
				$objAuthScriptDir->employee_id			= Employee::SYSTEM_EMPLOYEE_ID;
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
			if (!($objCustomerGroupDir = Document::getByPath("/Authorisation Scripts/{$this->customer_group}/")))
			{
				//throw new Exception("/Authorisation Scripts/customer_group/ not found!");
				
				// Create the CustomerGroup node
				$objCustomerGroupDir	= new Document();
				$objCustomerGroupDir->document_nature_id	= DOCUMENT_NATURE_FOLDER;
				$objCustomerGroupDir->employee_id			= Employee::SYSTEM_EMPLOYEE_ID;
				$objCustomerGroupDir->save();
				
				$objCustomerGroupDirContent	= new Document_Content();
				$objCustomerGroupDirContent->document_id		= $objCustomerGroupDir->id;
				$objCustomerGroupDirContent->name				= "{$this->customer_group}";
				$objCustomerGroupDirContent->constant_group		= "CustomerGroup";
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
		$objCustomerGroup	= Customer_Group::getForId($intCustomerGroup);
		
		$strPlans		= '';
		
		// Documents
		$arrDocuments	= array();
		foreach ($arrRatePlans as $mixRatePlan)
		{
			$arrRatePlan	= ($mixRatePlan instanceof Rate_Plan) ? $mixRatePlan->toArray() : $mixRatePlan;
			
			$objDocument		= new Document(array('id'=>$arrRatePlan['brochure_document_id']), true);
			$objDocumentContent	= $objDocument->getContent();
			
			$intFileSizeKB	= round(mb_strlen($objDocumentContent->content) / 1024);
			$arrDocuments[]	= "{id: {$objDocument->id}, strFileName: \"".$objDocumentContent->getFileName()."\", intFileSizeKB: {$intFileSizeKB}, file_type_id: {$objDocumentContent->file_type_id}}";
			
			$strPlans	.= " - {$arrRatePlan['Name']}\\n";
		}
		$strDocuments	= "new Array(".implode(",\n", $arrDocuments).")";
		
		// Recipients
		if ($intAccountId)
		{
			$objAccount	= new Account(array('Id'=>$intAccountId), false, true);
			$arrContacts	= $objAccount->getContacts(false);
			
			$arrRecipients	= array();
			foreach ($arrContacts as $arrContact)
			{
				if ($arrContact['Archived'] === 0 && trim($arrContact['Email']) && stripos($arrContact['Email'], 'noemail@') === false)
				{
					$arrRecipients[]	= "{name: \"{$arrContact['FirstName']} {$arrContact['LastName']}\", address: \"{$arrContact['Email']}\"}";
				}
			}
			$strRecipients	= "new Array(".implode(",\n", $arrRecipients).")";
		}
		else
		{
			$strRecipients	= 'null';
		}
		
		// Senders
		$arrSenders		= array();
		$objEmployee	= Employee::getForId(Flex::getUserId());
		if (trim($objEmployee->Email))
		{
			$arrSenders[]	= "{name: \"{$objEmployee->FirstName} {$objEmployee->LastName}\", address: \"{$objEmployee->Email}\"}";
		}
		$arrSenders[]	= "{name: \"{$objCustomerGroup->externalName} Customer Care\", address: \"{$objCustomerGroup->emailDomain}\"}";
		$strSenders		= "new Array(".implode(",\n", $arrSenders).")";
		
		if (count($arrDocuments) > 1)
		{
			$strBrochurePlural	= "Brochure";
		}
		else
		{
			$strBrochurePlural	= "Brochures";
		}
		
		// Generate HTML
		$strSubject			= "Requested {$strCustomerGroup} Plan Brochure";
		$strContent			= "Dear <Addressee>,\\n\\nPlease attached find the Plan {$strBrochurePlural}:\\n\\n{$strPlans}\\nAs per your request.\\n\\nRegards,\\n\\nThe Team at {$objCustomerGroup->externalName}";
		
		return "JsAutoLoader.loadScript(\"javascript/document.js\", function(){Flex.Document.emailDocument($strDocuments, \"Plan {$strBrochurePlural}\", {$strSenders}, \"{$strSubject}\", \"{$strContent}\", {$strRecipients})});";
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