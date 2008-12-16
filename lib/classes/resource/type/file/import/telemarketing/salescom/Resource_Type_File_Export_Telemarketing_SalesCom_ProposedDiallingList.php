<?php
/**
 * Resource_Type
 *
 * Models a record of the resource_type table
 *
 * @class	Service
 */
class Resource_Type_File_Import_Telemarketing_SalesCom_ProposedDiallingList
{
	protected	$_objFileImport;
	
	/**
	 * __construct()
	 *
	 * constructor
	 * 
	 * @constructor
	 */
	public function __construct($objFileImport)
	{
		if (!($objFileImport instanceof File_Import))
		{
			throw new Exception("Invalid File_Import object passed");
		}
		
		$this->_objFileImport	= $objFileImport;
	}
	
	/**
	 * import()
	 *
	 * Imports the File into Flex
	 * 
	 * @method
	 */
	public function import()
	{
		// Open the file
		$this->_resFile	= $this->_objFileImport->fopen();
		
		// Parse each Line
		while ($strLine = fgets($this->_resFile))
		{
			$this->_parseLine($strLine);
		}
		
		// Close the file
		fclose($this->_resFile);
	}
	
	/**
	 * _parseLine()
	 *
	 * Imports the File into Flex
	 * 
	 * @method
	 */
	private function _parseLine()
	{
		
	}
}