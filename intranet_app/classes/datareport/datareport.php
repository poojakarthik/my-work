<?php
	
	//----------------------------------------------------------------------------//
	// datareport.php
	//----------------------------------------------------------------------------//
	/**
	 * datareport.php
	 *
	 * File containing DataReport Class
	 *
	 * File containing DataReport Class
	 *
	 * @file		datareport.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// DataReport
	//----------------------------------------------------------------------------//
	/**
	 * DataReport
	 *
	 * A DataReport in the Database
	 *
	 * A DataReport in the Database
	 *
	 *
	 * @prefix	rpt
	 *
	 * @package		intranet_app
	 * @class		DataReport
	 * @extends		dataObject
	 */
	
	class DataReport extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new DataReport
		 *
		 * Constructor for a new DataReport
		 *
		 * @param	Integer		$intId		The Id of the DataReport being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the DataReport information and Store it ...
			$selDataReport = new StatementSelect ('DataReport', '*', 'Id = <Id>', null, 1);
			$selDataReport->useObLib (TRUE);
			$selDataReport->Execute (Array ('Id' => $intId));
			
			if ($selDataReport->Count () <> 1)
			{
				throw new Exception ('DataReport does not exist.');
			}
			
			$selDataReport->Fetch ($this);
			
			// Construct the object
			parent::__construct ('DataReport', $this->Pull ('Id')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// Execute
		//------------------------------------------------------------------------//
		/**
		 * Execute()
		 *
		 * Execute the DataReport
		 *
		 * Execute the DataReport
		 *
		 * @param	Array		$arrFields		A filled Associative Array (from the Serialized DataReportFields field)
		 * @param	String		$strOrder		The field in which to Order By
		 *
		 * @return	Array
		 *
		 * @method
		 */
		
		public function Execute ($arrFields, $strOrder=null)
		{
			// This deals with turning the SQLSelect Serialized Array 
			// into a String: Field1, Field2, Field3 [, ... ]
			$arrSelect = unserialize ($this->Pull ('SQLSelect')->getValue ());
			
			$i = 0;
			foreach ($arrSelect as $strField)
			{
				if ($i != 0)
				{
					$strSelect .= ", ";
				}
				
				$strSelect .= $strField;
				
				++$i;
			}
			
			$selResult = new StatementSelect (
				$this->Pull ('SQLTable')->getValue (), 
				$strSelect, 
				$this->Pull ('SQLWhere')->getValue (), 
				$strOrder,
				null
			);
			
			$selResult->Execute ($arrFields);
			
			return $selResult;
		}
		
		//------------------------------------------------------------------------//
		// Documentation
		//------------------------------------------------------------------------//
		/**
		 * Documentation()
		 *
		 * Get a list of Documentation objects required
		 *
		 * Get a list of Documentation objects required
		 *
		 * @return	Array
		 *
		 * @method
		 */
		
		public function Documentation ()
		{
			return unserialize ($this->Pull ('Documentation')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// Inputs
		//------------------------------------------------------------------------//
		/**
		 * Inputs()
		 *
		 * Turn the Serialised Input Array into ObLib
		 *
		 * Turn the Serialised Input Array into ObLib
		 *
		 * @return	dataArray
		 *
		 * @method
		 */
		
		public function Inputs ()
		{
			$arrInputs = unserialize ($this->Pull ('SQLFields')->getValue ());
			
			$oblarrInputs = new dataArray ('Inputs');
			
			foreach ($arrInputs as $strName => $arrInput)
			{
				$oblarrField = $oblarrInputs->Push (new dataArray ('Input'));
				$strName = $oblarrField->Push (new dataString ('Name', $strName));
				
				foreach ($arrInput as $strKey => $mixValue)
				{
					$oblarrField->Push (new dataString ($strKey, $mixValue));
				}
			}
			
			return $oblarrInputs;
		}
	}
	
?>
