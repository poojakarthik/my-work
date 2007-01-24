<?php
	
	//----------------------------------------------------------------------------//
	// report.php
	//----------------------------------------------------------------------------//
	/**
	 * report.php
	 *
	 * File containing Report Class
	 *
	 * File containing Report Class
	 *
	 * @file		report.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Report
	//----------------------------------------------------------------------------//
	/**
	 * Report
	 *
	 * A Report in the Database
	 *
	 * A Report in the Database
	 *
	 *
	 * @prefix	rpt
	 *
	 * @package		intranet_app
	 * @class		Report
	 * @extends		dataObject
	 */
	
	class Report extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Report
		 *
		 * Constructor for a new Report
		 *
		 * @param	Integer		$intId		The Id of the Report being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Report information and Store it ...
			$selReport = new StatementSelect ('Report', '*', 'Id = <Id>', null, 1);
			$selReport->useObLib (TRUE);
			$selReport->Execute (Array ('Id' => $intId));
			
			if ($selReport->Count () <> 1)
			{
				throw new Exception ('Report does not exist.');
			}
			
			$selReport->Fetch ($this);
			
			// Construct the object
			parent::__construct ('Report', $this->Pull ('Id')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// Execute
		//------------------------------------------------------------------------//
		/**
		 * Execute()
		 *
		 * Execute the Report
		 *
		 * Execute the Report
		 *
		 * @param	Array		$arrFields		A filled Associative Array (from the Serialized ReportFields field)
		 * @param	String		$strOrder		The field in which to Order By
		 *
		 * @return	Array
		 *
		 * @method
		 */
		 
		public function Execute ($arrFields, $strOrder)
		{
			// This deals with turning the SQLSelect Serialized Array 
			// into a String: Field1, Field2, Field3 [, ... ]
			$arrSelect = unserialize ($this->Pull ('SQLSelect')->getValue ());
			
			$i = 0;
			foreach ($arrSelect as $strField => $strType)
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
	}
	
?>
