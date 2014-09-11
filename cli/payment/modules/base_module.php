<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// base_module
//----------------------------------------------------------------------------//
/**
 * base_module
 *
 * Base Module for Payment record Normalisation
 *
 * Base Module for Payment record Normalisation
 *
 * @file		base_module.php
 * @language	PHP
 * @package		Payment_application
 * @author		Rich Davis
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
 
//----------------------------------------------------------------------------//
// PaymentModule
//----------------------------------------------------------------------------//
/**
 * PaymentModule
 *
 * Base Module for Payment record Normalisation
 *
 * Base Module for Payment record Normalisation
 *
 *
 * @prefix		pay
 *
 * @package		Payment_application
 * @class		PaymentModule
 */
 class PaymentModule extends CarrierModule
 {
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier, MODULE_TYPE_NORMALISATION_PAYMENT);
 		
 		// Init member variables
 		$this->_strDelimiter	= NULL;
 		$this->_strEnclosedBy	= NULL;
 		
	 	$this->_selGetAccountGroup	= new StatementSelect(	"Account",
	 														"AccountGroup",
	 														"Account.Id = <Account>");
		
		$this->_arrDefine = NULL;
 	}

	//------------------------------------------------------------------------//
	// Validate
	//------------------------------------------------------------------------//
	/**
	 * Validate()
	 *
	 * Validate Normalised Data
	 *
	 * Validate Normalised Data
	 *
	 * @return	boolean				true	: Data matches
	 * 								false	: Data doesn't match
	 *
	 * @method
	 */
	function Validate()
	{
		// Validate our normalised data
		$arrValid = Array();
		
		if (!$arrValid[] = preg_match("/^\d{4}-[01]\d-[0-3]\d$/", $this->_arrNormalisedData['PaidOn']))		// 1
		{
			Debug($this->_arrNormalisedData['PaidOn']);
		}
		
		//$arrValid[] = (bool)$this->_arrNormalisedData['CarrierRef'];
		
		if (!$arrValid[] = is_float($this->_arrNormalisedData['Amount']))									// 2
		{
			Debug($this->_arrNormalisedData['Amount']);
		}
		
		if (!$arrValid[] = (bool)$this->_arrNormalisedData['TXNReference'])									// 3
		{
			Debug($this->_arrNormalisedData['TXNReference']);
		}
		
		if (!$arrValid[] = ($this->_arrNormalisedData['EnteredBy'] >= 0))									// 4
		{
			Debug($this->_arrNormalisedData['EnteredBy']);
		}
		
		if (!$arrValid[] = ($this->_arrNormalisedData['Amount'] == $this->_arrNormalisedData['Balance']))	// 5
		{
			Debug($this->_arrNormalisedData['Balance']);
		}

		$i = 0;
		foreach ($arrValid as $bolValid)
		{
			$i++;
			if(!$bolValid)
			{
				$this->_arrNormalisedData['Status']	= PAYMENT_CANT_NORMALISE_INVALID;
				Debug("Erroneous: ".$i);
				return false;
			}
		}
		
		return true;
	}

	//------------------------------------------------------------------------//
	// Normalise
	//------------------------------------------------------------------------//
	/**
	 * Normalise()
	 *
	 * Normalises a Payment record
	 *
	 * Normalises a Payment record.  NEVER CALL THIS METHOD FROM ANYTHING BUT AN INHERITED Normalise()
	 *
	 * @param		string	$strPaymentRecord	String containing raw record
	 * 
	 * @return		array
	 *
	 * @method
	 */
	protected function Normalise($arrPaymentRecord)
 	{
 		// Set Balance to Amount
 		$this->_Append('Balance', $this->_arrNormalisedData['Amount']);
 		
 		// Set EnteredBy to Automated Employee
 		$this->_Append('EnteredBy', USER_ID);
 		
 		// If there is no PaidOn, then set it to today's date
 		if (!isset($this->_arrNormalisedData['PaidOn']))
 		{
 			$this->_Append('PaidOn', date("Y-m-d"));
 		}
 	}
 	
	//------------------------------------------------------------------------//
	// _SplitRaw
	//------------------------------------------------------------------------//
	/**
	 * _SplitRaw()
	 *
	 * Split a Raw Payment record into an array
	 *
	 * Split a Raw Payment record into an array
	 * 
	 * @param	string		strPayment		Payment record
	 *
	 * @return	VOID					
	 *
	 * @method
	 */
	 protected function _SplitRaw($strPayment)
	 {
	 	// clean the arrays
		$this->_arrRawData = array();
		$this->_arrNormalisedData = array();
		
		// build the array
	 	if ($this->_strDelimiter)
		{
			// delimited record
			$arrRawData = explode($this->_strDelimiter, rtrim($strPayment, "\n"));
			foreach($this->_arrDefine as $strKey=>$strValue)
			{
				$this->_arrRawData[$strKey] = $arrRawData[$strValue['Index']];
				// delimited fields may have fixed width contents
				if (isset($strValue['Start']) && $strValue['Length'])
				{
					$this->_arrRawData[$strKey] = substr($this->_arrRawData[$strKey], $strValue['Start'], $strValue['Length']);
				}
				
				if (isset($this->_strEnclosedBy))
				{
					// Remove enclosure characters
					$this->_arrRawData[$strKey] = trim($this->_arrRawData[$strKey], $this->_strEnclosedBy);
				}
			}
		}
		else
		{
			// fixed width record
			foreach($this->_arrDefine as $strKey=>$strValue)
			{
				$this->_arrRawData[$strKey] = trim(substr($strPayment, $strValue['Start'], $strValue['Length']));
			}
			
			if (isset($this->_strEnclosedBy))
			{
				// Remove enclosure characters
				$this->_arrRawData[$strKey] = trim($this->_arrRawData[$strKey], $this->_strEnclosedBy);
			}
		}

	 }
	
	//------------------------------------------------------------------------//
	// _ValidateRaw
	//------------------------------------------------------------------------//
	/**
	 * _ValidateRaw()
	 *
	 * Validate contents of Raw Payment record
	 *
	 * Validate contents of Raw Payment record
	 * 
	 *
	 * @return	bool	TRUE if record is valid, FALSE otherwise				
	 *
	 * @method
	 */
	 protected function _ValidateRaw()
	 {
	 	if (is_array($this->_arrDefine))
		{
			foreach($this->_arrDefine as $strKey=>$strValue)
			{
				if ($strValue['Validate'])
				{
					if (!preg_match($strValue['Validate'], $this->_arrRawData[$strKey]))
					{
						Debug("$strKey: '".$this->_arrRawData[$strKey]."' != '".$strValue['Validate']."'");
						return FALSE;
					}
				}
			}
			return TRUE;
		}
		// retfrn false if there is no define array for the carrier (should never happen)
		return FALSE;
	 }
	
	//------------------------------------------------------------------------//
	// _FetchRaw
	//------------------------------------------------------------------------//
	/**
	 * _FetchRaw()
	 *
	 * Fetch a field from the raw Payment
	 *
	 * Fetch a field from the raw Payment
	 * 
	 * @param	string		strKey		field key
	 *
	 * @return	string					field value					
	 *
	 * @method
	 */
	 protected function _FetchRaw($strKey)
	 {
	 	return $this->_arrRawData[$strKey];
	 }
	
	//------------------------------------------------------------------------//
	// FetchRawComplete
	//------------------------------------------------------------------------//
	/**
	 * FetchRawComplete()
	 *
	 * Fetches raw Payment array
	 *
	 * Fetches raw Payment array
	 *
	 * @return	array					raw array					
	 *
	 * @method
	 */
	 public function FetchRawComplete()
	 {
	 	return $this->_arrRawData;
	 }
	 
	//------------------------------------------------------------------------//
	// _NewPayment
	//------------------------------------------------------------------------//
	/**
	 * _NewPayment()
	 *
	 * Create a new default Payment record
	 *
	 * Create a new default Payment record
	 * 
	 *
	  * @param	array		arrPayment	Payment array
	 * @return	VOID					
	 *
	 * @method
	 */
	 protected function _NewPayment($arrPayment)
	 {
	 	// set Payment Record
	 	$this->_arrNormalisedData = $arrPayment;
		
		// set Default Context
		$this->_intContext = 0;
	 }
	 
	//------------------------------------------------------------------------//
	// _Append
	//------------------------------------------------------------------------//
	/**
	 * _Append()
	 *
	 * Add a field to the output Payment record
	 *
	 * Add a field to the output Payment record
	 * 
	 * @param	string		strKey		field key
	 * @param	mixed		mixValue	field value
	 *
	 * @return	VOID					
	 *
	 * @method
	 */
	 protected function _Append($strKey, $mixValue)
	 {
	 	$this->_arrNormalisedData[$strKey] = $mixValue;
	 }
	 
	//------------------------------------------------------------------------//
	// _Output
	//------------------------------------------------------------------------//
	/**
	 * _Output()
	 *
	 * Output Payment Record
	 *
	 * Output Payment Record
	 * 
	 * @return	array					
	 *
	 * @method
	 */
	 protected function _Output()
	 {
	 	return $this->_arrNormalisedData;
	 }
	 
	//------------------------------------------------------------------------//
	// _FindAccountGroup
	//------------------------------------------------------------------------//
	/**
	 * _FindAccountGroup()
	 *
	 * Finds the AccountGroup for the given Account
	 *
	 * Finds the AccountGroup for the given Account
	 *
	 * @return	mixed					integer: Account Number
	 * 									FALSE: There was no match				
	 *
	 * @method
	 */
	 protected function _FindAccountGroup($intAccount)
	 {
		$arrParams['Account'] = $intAccount;
		if (!$this->_selGetAccountGroup->Execute($arrParams))
		{
			// There was no match or an error
			return FALSE;
		}
		$arrData = $this->_selGetAccountGroup->Fetch();
		return $arrData['AccountGroup'];
	 }
 }
?>
