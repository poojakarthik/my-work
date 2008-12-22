<?php

//----------------------------------------------------------------------------//
// StatementUpdateById
//----------------------------------------------------------------------------//
/**
 * StatementUpdateById
 *
 * UPDATE by Id Query
 *
 * Implements an UPDATE by Id query using mysqli statements
 *
 *
 * @prefix		ubi
 *
 * @package		framework
 * @class		UpdateById
 */
 class StatementUpdateById extends StatementUpdate
 {
 	//------------------------------------------------------------------------//
	// StatementUpdateById() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * StatementUpdateById()
	 *
	 * Constructor for StatementUpdateById object
	 *
	 * Constructor for StatementUpdateById object
	 *
	 * @param		string	strTable		Name of the table to update
	 * @param		array	arrColumns		optional Associative array of the columns 
	 * 										you want to update, where the keys are the column names.
	 * 										If you want to update everything, ignore
	 * 										this parameter
	 *
	 * @return		void
	 *
	 * @method
	 */ 
 	function __construct($strTable, $arrColumns = null, $strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
	{
		// make global database object available
		$this->db = DataAccess::getDataAccess($strConnectionType);
		$strId = $this->db->arrTableDefine->{$strTable}['Id'];
		if (!$strId)
		{
			throw new Exception("Missing Table Id for : $strTable");
		}
		// This is a hack to fix a problem introduced when another problem was fixed that had already been hacked around, the hack not working after the original problem was resolved.
		if ($strId == 'id')
		{
			if ($arrColumns && array_key_exists('Id', $arrColumns))
			{
				$arrColumns['id'] = $arrColumns['Id'];
				unset($arrColumns['Id']);
			}
		}
		$strWhere = "$strId = <$strId>";
		parent::__construct($strTable, $strWhere, $arrColumns);
	}
	
	//------------------------------------------------------------------------//
	// Execute()
	//------------------------------------------------------------------------//
	/**
	 * Execute()
	 *
	 * Executes the StatementUpdateById, with a new set of values
	 *
	 * Executes the StatementUpdateById, with a new set of values
	 *
	 * @param		array	arrData			Associative array of data to be entered.  If this is
	 * 										for a partial update, make sure that it is the exact same
	 * 										array passed to the constructor (the elements must be in the same order)
	 * 
	 * @return		mixed					int			: number of Affected Rows
	 * 										bool FALSE	: Update failed
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	 function Execute($arrData)
	 {
	 	$strId = $this->db->arrTableDefine->{$this->_strTable}['Id'];
		$intId = $arrData[$strId];
		$arrWhere = Array($strId => $intId);
	 	return parent::Execute($arrData, $arrWhere);
	 }
}

?>
