<?php
	
	//----------------------------------------------------------------------------//
	// ACN.php
	//----------------------------------------------------------------------------//
	/**
	 * ACN.php
	 *
	 * File containing ACN Class
	 *
	 * File containing ACN Class
	 *
	 * @file		ACN.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// ACN
	//----------------------------------------------------------------------------//
	/**
	 * ACN
	 *
	 * ACN Number Validation/Parsing
	 *
	 * Controls validation of ACN Numbers
	 *
	 *
	 * @prefix	acn
	 *
	 * @package		intranet_app
	 * @class		ACN
	 * @extends		dataPrimitive
	 */
	
	class ACN extends dataPrimitive
	{
		
		public static $arrWeights = Array (
			"0"	=> 8,
			"1"	=> 7,
			"2"	=> 6,
			"3"	=> 5,
			"4"	=> 4,
			"5"	=> 3,
			"6"	=> 2,
			"7"	=> 1
		);
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new ACN
		 *
		 * Constructor for a new ACN
		 *
		 * @param	String		$strACN			The initial value of the ACN being set
		 *
		 * @method
		 */
		
		function __construct ($strName, $strACN)
		{
			// Construct the object
			parent::__construct ($strName);
			$this->setValue ($strACN);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Value of the ACN
		 *
		 * Change the Value of the ACN
		 *
		 * @param	String		$strACN			The value of the ACN being set
		 * @return	Boolean						Whether the Number is Valid or not. Invalid numbers are not subscribed.
		 *
		 * @method
		 */
		
		public function setValue ($strACN)
		{
			// 1. If the length is 0, it is valid because we might not have an ACN
			
			if (strlen ($strACN) == 0)
			{
				return true;
			}
			
			// 2. Check that the item has only Numbers and Spaces
			if (preg_match ("/[^\d\s]/", $strACN))
			{
				return false;
			}
			
			$strACN = preg_replace ("/[^\d]/", "", $strACN);
			
			// 3. Check there are 9 integers
			if (strlen ($strACN) != 9)
			{
				return false;
			}
			
			// 1. Apply weighting to digits 1 to 8
			// 2. Sum the products
			// 3. Divide by 10 to obtain remainder 84 / 10 = 8 remainder 4
			// 4. Complement the remainder to 10 10 - 4 = 6 (if complement = 10, set to 0)
			// 5. Check the calculated check digit equals actual check digit
			
			// 1. Apply weighting to digits 1 to 8
			// 2. Sum the products
			$intNumberSum = 0;
			
			for ($i=0; $i < 8; ++$i)
			{
				$intNumberSum += substr ($strACN, $i, 1) * ACN::$arrWeights [$i];
			}
			
			// 3. Divide by 10 to obtain remainder 84 / 10 = 8 remainder 4
			$intRemainder = $intNumberSum % 10;
			
			// 4. Complement the remainder to 10 10 - 4 = 6 (if complement = 10, set to 0)
			$intComplement = 10 - $intRemainder;
			
			if ($intComplement == 10)
			{
				$intComplement = 0;
			}
			
			// 5. Check the calculated check digit equals actual check digit
			if (substr ($strACN, 8, 1) != $intComplement)
			{
				return false;
			}
			
			parent::setValue (
				substr ($strACN, 0, 3) . " " . substr ($strACN, 3, 3) . " " . substr ($strACN, 6, 3)
			);
			
			return true;
		}
	}
	
?>
