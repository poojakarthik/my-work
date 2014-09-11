<?php
	
	//----------------------------------------------------------------------------//
	// ABN.php
	//----------------------------------------------------------------------------//
	/**
	 * ABN.php
	 *
	 * File containing ABN Class
	 *
	 * File containing ABN Class
	 *
	 * @file		ABN.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ABN
	//----------------------------------------------------------------------------//
	/**
	 * ABN
	 *
	 * ABN Number Validation/Parsing
	 *
	 * Controls validation of ABN Numbers
	 *
	 *
	 * @prefix	abn
	 *
	 * @package		intranet_app
	 * @class		ABN
	 * @extends		dataPrimitive
	 */
	
	class ABN extends dataPrimitive
	{
		
		public static $arrWeights = Array (
			0	=> "10",
			1	=> "1",
			2	=> "3",
			3	=> "5",
			4	=> "7",
			5	=> "9",
			6	=> "11",
			7	=> "13",
			8	=> "15",
			9	=> "17",
			10	=> "19"
		);
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new ABN
		 *
		 * Constructor for a new ABN
		 *
		 * @param	String		$strName		The name of the ABN String
		 * @param	String		$strABN			The initial value of the ABN being set
		 *
		 * @method
		 */
		
		function __construct ($strName, $strABN)
		{
			// Construct the object
			parent::__construct ($strName);
			
			$this->setValue ($strABN);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Value of the ABN
		 *
		 * Change the Value of the ABN
		 *
		 * @param	String		$strABN			The value of the ABN being set
		 * @return	Boolean						Whether the Number is Valid or not. Invalid numbers are not subscribed.
		 *
		 * @method
		 */
		
		public function setValue ($strABN)
		{
			// 1. If the length is 0, it is valid because we might not have an ABN
			
			if (strlen ($strABN) == 0)
			{
				return true;
			}
			
			// 2. Check that the item has only Numbers and Spaces
			if (preg_match ('/[^\d\s]/', $strABN))
			{
				return false;
			}
			
			$strABN = preg_replace ('/\s/', '', $strABN);
			
			// 3. Check there are 11 integers
			if (strlen ($strABN) != 11)
			{
				return false;
			}
			
			
			// 4. ABN Calculation
			// http://www.ato.gov.au/businesses/content.asp?doc=/content/13187.htm&pc=001/003/021/002/001&mnu=610&mfp=001/003&st=&cy=1
			
			//   1. Subtract 1 from the first (left) digit to give a new eleven digit number
			//   2. Multiply each of the digits in this new number by its weighting factor
			//   3. Sum the resulting 11 products
			//   4. Divide the total by 89, noting the remainder
			//   5. If the remainder is zero the number is valid
			

			
			//   1. Subtract 1 from the first (left) digit to give a new eleven digit number
			$strNewABN = (intval (substr ($strABN, 0, 1)) - 1) . substr ($strABN, 1);
			
			
			//   2. Multiply each of the digits in this new number by its weighting factor
			//   3. Sum the resulting 11 products
			$intNumberSum = 0;
			
			for ($i=0; $i < 11; ++$i)
			{
				$intNumberSum += substr ($strNewABN, $i, 1) * ABN::$arrWeights [$i];
			}
			
			//   4. Divide the total by 89, noting the remainder
			//   5. If the remainder is zero the number is valid
			if ($intNumberSum % 89 != 0)
			{
				return false;
			}
			
			parent::setValue (
				substr ($strABN, 0, 2) . " " . substr ($strABN, 2, 3) . " " . substr ($strABN, 5, 3) . " " . substr ($strABN, 8, 3)
			);
			
			return true;
		}
	}
	
?>
