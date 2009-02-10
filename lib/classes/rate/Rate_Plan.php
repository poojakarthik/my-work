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
		$objBrochureDocumentContent->parent_document_id	= $objCustomerGroupDirContent->id;
		$objBrochureDocumentContent->employee_id		= Flex::getUserId();
		$objBrochureDocumentContent->status_id			= STATUS_ACTIVE;
		$objBrochureDocumentContent->save();
		
		return true;
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