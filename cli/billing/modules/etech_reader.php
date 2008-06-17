<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// etech_reader
//----------------------------------------------------------------------------//
/**
 * etech_reader
 *
 * Read an etech billing file
 *
 * Read an etech billing file
 *
 * @file		etech_reader.php
 * @language	PHP
 * @package		Billing
 * @author		Jared 'flame' Herbohn, Rich "Waste" Davis
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 

//----------------------------------------------------------------------------//
// EtechReader
//----------------------------------------------------------------------------//
/**
 * EtechReader
 *
 * Read an etech billing file
 *
 * Read an etech billing file
 *
 *
 * @prefix		sux
 *
 * @package		billing_app
 * @class		EtechReader
 */
 class EtechReader extends ApplicationBaseClass
 {
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Application
	 *
	 * Constructor for the Application
	 * 
	 * @param	array	$arrConfig				Configuration array
	 *
	 * @return			ApplicationCollection
	 *
	 * @method
	 */
 	function __construct($arrConfig=NULL)
 	{
		parent::__construct();
		
		// cache recordtypes by Value for faster lookups
		foreach ($GLOBALS['FileFormatEtech'] as $arrRecordType)
		{
			$this->_arrEtechRecordType[(int)$arrRecordType['RecordType']['Value']] = $arrRecordType;
		}
			
	}
	
	//------------------------------------------------------------------------//
	// OpenFile
	//------------------------------------------------------------------------//
	/**
	 * OpenFile()
	 *
	 * Open an etech billing file
	 *
	 * Open an etech billing file
	 * 
	 * @param	string	$strFilePath	full path to file
	 * @param	int		$intLine		line no. to start reading from
	 *									first line of the file is line 1
	 *
	 * @return			bool
	 *
	 * @method
	 */
 	function OpenFile($strFilePath, $intLine=0, $arrStatus=NULL)
 	{
		$intLine = (int)$intLine;
		
		// set status
		if (is_array($arrStatus))
		{
			$this->_arrStatus = $arrStatus;
		}
		else
		{
			$this->_arrStatus = Array();
		}
		
		// close existing file
		if ($this->ptrFile)
		{
			@fclose($this->ptrFile);
		}
		
		// check if the file exists
		if (!file_exists($strFilePath))
		{
			return FALSE;
		}
		
		// open the file
		if ((@$this->ptrFile = fopen($strFilePath, "r")) === FALSE)
		{
			// if it failed, return false
			return FALSE;
		}
		
		// skip forward to line no.
		for ($i = 0; $i < $intLine; $i++)
		{
			fgets($this->ptrFile);
		}
		
		// set line no.
		$this->intLine = $intLine;
		
		// return TRUE if all went well
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// FetchNext
	//------------------------------------------------------------------------//
	/**
	 * FetchNext()
	 *
	 * Fetch the next record from the file
	 *
	 * Fetch the next record from the file
	 * 
	 *
	 * @return			mixed	array of record details
	 * 							bool	FALSE on EOF
	 *
	 * @method
	 */
 	function FetchNext()
 	{
		$arrOutput = Array();
		$this->arrData = NULL;
		$this->intDepth = 0;
		
		// Decode the next batch of data
		while ($this->arrData === NULL)
		{
			$this->DecodeData();
		}
		if (($arrOutput = $this->arrData) === FALSE)
		{
			// EOF
			fclose($this->ptrFile);
			return FALSE;
		}
		elseif ($arrOutput == '!ERROR!')
		{
			$arrOutput = Array();
			$arrOutput['_LineNo'] 	= $this->intLine;
			$arrOutput['_LineType'] = 'ERROR';
			return $arrOutput;
		}
		
		// Set common fields
		switch ($arrOutput['_Table'])
		{
			case "ServiceTotal":
			case "ServiceTypeTotal":
				$arrOutput['Invoice']		= $this->_arrStatus['Invoice'];
				$arrOutput['InvoiceDate']	= $this->_arrStatus['CreatedOn'];
				break;
			case "Invoice":
				$arrOutput['DueOn']			= $this->_arrStatus['DueOn'];
				$arrOutput['CreatedOn']		= $this->_arrStatus['CreatedOn'];
				break;
		}
		
		// set status
		$arrOutput['_Status'] 	= $this->_arrStatus;
		
		// set line type for output
		$arrOutput['_LineType'] 	= 'DATA';
		
		// set line no for output
		$arrOutput['_LineNo'] 		= $this->intLine;
		
		// return record
		return $arrOutput;
	}
	
	//------------------------------------------------------------------------//
	// ReadRawLine
	//------------------------------------------------------------------------//
	/**
	 * ReadRawLine()
	 *
	 * Read the next raw line from the file
	 *
	 * Read the next raw line from the file
	 * 
	 *
	 * @return			mixed	string	contents of line
	 * 							bool	FALSE on EOF
	 *
	 * @method
	 */
 	function ReadRawLine()
 	{
		// If EOF, then return FALSE
		if (feof($this->ptrFile))
		{
			return FALSE;
		}
		
		// increment counter
		$this->intLine++;

		// read next line from file
		if (($strLine = fgets($this->ptrFile)) === FALSE)
		{
			// If EOF, then return FALSE
			if (feof($this->ptrFile))
			{
				return FALSE;
			}
			// There was an error
			return "!ERROR!";
		}
		
		// return the raw line
		return $strLine;				
	}
	
	//------------------------------------------------------------------------//
	// DecodeData
	//------------------------------------------------------------------------//
	/**
	 * DecodeData()
	 *
	 * Decodes data from the etech file format
	 *
	 * Decodes data from the etech file format
	 *
	 * @return	array					associative array of data
	 *
	 * @method
	 */
 	function DecodeData()
 	{	
		// Fetch the next line and split it
		$strLine = $this->ReadRawLine();
		if ($strLine === FALSE)
		{
			$this->arrData = FALSE;
			return;
		}
		if ($strLine === "!ERROR!")
		{
			return $strLine;
		}
		$arrLine = $this->SplitLine($strLine);
		
		// debuging
		$this->arrData['_OriginalLine'] = $strLine;
		$this->arrData['_OriginalLineArray'] = $arrLine;
		
		// If its an itemised data row, assign general data here
		if ($arrLine['RecordType'] > 100 && $arrLine['RecordType'] < 240)
		{
			$arrDuration			= explode(":", $arrLine['Duration']);
			$intMinutesInSeconds	=  (int)$arrDuration[0] * 60;
			
			$this->arrData['_Table']			= "CDR";
			$this->arrData['FNN']				= $this->_arrStatus['FNN'];
			$this->arrData['Account']			= $this->_arrStatus['Account'];
			$this->arrData['AccountGroup']		= $this->_arrStatus['Account'];
			$this->arrData['Invoice']			= $this->_arrStatus['Invoice'];
			$this->arrData['StartDatetime']		= $arrLine['Datetime'];
			$this->arrData['Destination']		= $arrLine['CalledParty'];
			$this->arrData['Units']				= (int)$intMinutesInSeconds + (int)$arrDuration[1];
			$this->arrData['Charge']			= (float)$arrLine['Charge'];
			$this->arrData['NormalisedOn']		= date("Y-m-d H:i:s", time());
		}
		
		/*if ($arrLine['RecordType'] == 40 || $arrLine['RecordType'] == 41 || $arrLine['RecordType'] == 45)
		{
			$this->arrData['FNN']				= $this->_arrStatus['FNN'];
			$this->arrData['Account']			= $this->_arrStatus['Account'];
			$this->arrData['AccountGroup']		= $this->_arrStatus['Account'];
			$this->arrData['Invoice']			= $this->_arrStatus['Invoice'];
		}*/
		
		// Determine the Row Type
		switch ($arrLine['RecordType'])
		{
			//------------------------- INVOICE  HEADERS -------------------------//
			case 1:
				// FILE HEADER
				$this->_arrStatus['BillingPeriod']	= date("Y-m-d", strtotime($arrLine['BillingPeriod']));
				
				// Call DecodeData() again
				$this->DecodeData();
				break;				
			case 2:
				// SP DETAILS
				// Set Data
				$this->_arrStatus['CreatedOn']	= $arrLine['InvoiceDate'];
				$this->arrData['CreatedOn']		= $this->_arrStatus['CreatedOn'];
				$this->_arrStatus['DueOn']		= $arrLine['DueByDate'];
				$this->arrData['DueOn']			= $this->_arrStatus['DueOn'];
				
				// Call DecodeData() again
				$this->DecodeData();
				break;
			case 6:
				// INVOICE NUMBER
				// Set data
				$this->arrData['_Table']		= "Invoice";
				$this->arrData['Id']			= (int)$arrLine['InvoiceNo'];
				$this->_arrStatus['Invoice']	= $this->arrData['Id'];
				
				// Call DecodeData() again
				$this->DecodeData();
				break;
			
			//---------------------------- FRONT PAGE ----------------------------//
			case 10:
				// INVOICE CHARGES
				// Set data
				$this->arrData['Tax']				= (float)$arrLine['NewCharges'] / 11.0;
				$this->arrData['Total']				= (float)$arrLine['NewCharges'] - $this->arrData['Tax'];
				$this->arrData['Balance']			= (float)$arrLine['AmountOwing'];
				$this->arrData['TotalOwing']		= (float)$arrLine['AmountOwing'];
				$this->arrData['AccountBalance']	= (float)$arrLine['Overdue'];
				// FIXME: These Credit/Debit values could be incorrect - its just a guess
				$this->arrData['Credits']			= (float)$arrLine['Adjustments'];
				$this->arrData['Debits']			= $this->arrData['Total'] - (float)$arrLine['Adjustments'];
				
				// Call DecodeData() again
				$this->DecodeData();
				break;
			case 11:
				// CUSTOMER DETAIL
				// Set data
				$this->_arrStatus['Account']	= (int)$arrLine['AccountNo'];
				$this->arrData['Account']		= $this->_arrStatus['Account'];
				$this->arrData['AccountGroup']	= $this->_arrStatus['Account'];
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			
			//-------------------------- ITEMISED CALLS --------------------------//
			case 20:
				// ITEMISED CALLS HEADER
				$this->_arrStatus['FNN']		= $arrLine['FNN'];
				
				// Call DecodeData() again
				$this->DecodeData();
				break;
			case 102:
				// LANDLINE -> NATIONAL
				$this->arrData['ServiceType']	= SERVICE_TYPE_LAND_LINE;
				$this->arrData['RecordType']	= 19;
				$this->arrData['EndDatetime']	= date("Y-m-d H:i:s", strtotime("+ {$this->arrData['Units']} seconds", strtotime($this->arrData['StartDatetime'])));
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 103:
				// LANDLINE -> 13/1300
				$this->arrData['ServiceType']	= SERVICE_TYPE_LAND_LINE;
				$this->arrData['RecordType']	= 24;
				$this->arrData['EndDatetime']	= date("Y-m-d H:i:s", strtotime("+ {$this->arrData['Units']} seconds", strtotime($this->arrData['StartDatetime'])));
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 104:
				// LANDLINE -> MOBILE
				$this->arrData['ServiceType']	= SERVICE_TYPE_LAND_LINE;
				$this->arrData['RecordType']	= 20;
				$this->arrData['EndDatetime']	= date("Y-m-d H:i:s", strtotime("+ {$this->arrData['Units']} seconds", strtotime($this->arrData['StartDatetime'])));
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 105:
				// LANDLINE -> IDD
				$this->arrData['ServiceType']	= SERVICE_TYPE_LAND_LINE;
				$this->arrData['RecordType']	= 28;
				$this->arrData['EndDatetime']	= date("Y-m-d H:i:s", strtotime("+ {$this->arrData['Units']} seconds", strtotime($this->arrData['StartDatetime'])));
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 106:
				// MOBILE -> MOBILE
				$this->arrData['ServiceType']	= SERVICE_TYPE_MOBILE;
				$this->arrData['RecordType']	= 2;
				$this->arrData['EndDatetime']	= date("Y-m-d H:i:s", strtotime("+ {$this->arrData['Units']} seconds", strtotime($this->arrData['StartDatetime'])));
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 107:
				// MOBILE -> NATIONAL
				$this->arrData['ServiceType']	= SERVICE_TYPE_MOBILE;
				$this->arrData['RecordType']	= 6;
				$this->arrData['EndDatetime']	= date("Y-m-d H:i:s", strtotime("+ {$this->arrData['Units']} seconds", strtotime($this->arrData['StartDatetime'])));
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 110:
				// MOBILE OTHER
				$this->arrData['ServiceType']	= SERVICE_TYPE_MOBILE;
				$this->arrData['RecordType']	= 16;
				$this->arrData['EndDatetime']	= date("Y-m-d H:i:s", strtotime("+ {$this->arrData['Units']} seconds", strtotime($this->arrData['StartDatetime'])));
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 111:
				// MOBILE ROAMING
				$this->arrData['ServiceType']	= SERVICE_TYPE_MOBILE;
				$this->arrData['RecordType']	= 11;
				$this->arrData['EndDatetime']	= date("Y-m-d H:i:s", strtotime("+ {$this->arrData['Units']} seconds", strtotime($this->arrData['StartDatetime'])));
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 112:
				// MOBILE -> IDD
				$this->arrData['ServiceType']	= SERVICE_TYPE_MOBILE;
				$this->arrData['RecordType']	= 27;
				$this->arrData['EndDatetime']	= date("Y-m-d H:i:s", strtotime("+ {$this->arrData['Units']} seconds", strtotime($this->arrData['StartDatetime'])));
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 113:
				// MOBILE -> 1800
				$this->arrData['ServiceType']	= SERVICE_TYPE_MOBILE;
				$this->arrData['RecordType']	= 7;
				$this->arrData['EndDatetime']	= date("Y-m-d H:i:s", strtotime("+ {$this->arrData['Units']} seconds", strtotime($this->arrData['StartDatetime'])));
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 114:
				// 13/1300 <- IDD
				$this->arrData['ServiceType']	= SERVICE_TYPE_INBOUND;
				$this->arrData['RecordType']	= 29;
				$this->arrData['EndDatetime']	= date("Y-m-d H:i:s", strtotime("+ {$this->arrData['Units']} seconds", strtotime($this->arrData['StartDatetime'])));
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 116:
				// LANDLINE OTHER
				$this->arrData['ServiceType']	= SERVICE_TYPE_LAND_LINE;
				$this->arrData['RecordType']	= 26;
				$this->arrData['EndDatetime']	= date("Y-m-d H:i:s", strtotime("+ {$this->arrData['Units']} seconds", strtotime($this->arrData['StartDatetime'])));
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 117:
				// 1800 <- ALL
				$this->arrData['ServiceType']	= SERVICE_TYPE_INBOUND;
				$this->arrData['RecordType']	= 35;
				$this->arrData['EndDatetime']	= date("Y-m-d H:i:s", strtotime("+ {$this->arrData['Units']} seconds", strtotime($this->arrData['StartDatetime'])));
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 118:
				// 13/1300 <- ALL
				$this->arrData['ServiceType']	= SERVICE_TYPE_INBOUND;
				$this->arrData['RecordType']	= 35;
				$this->arrData['EndDatetime']	= date("Y-m-d H:i:s", strtotime("+ {$this->arrData['Units']} seconds", strtotime($this->arrData['StartDatetime'])));
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 119:
				// MOBILE SMS
				$this->arrData['ServiceType']	= SERVICE_TYPE_MOBILE;
				$this->arrData['RecordType']	= 10;
				$this->arrData['EndDatetime']	= $this->arrData['StartDatetime'];
				$this->arrData['Units']			= 1;
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 120:
				// MOBILE MMS
				$this->arrData['ServiceType']	= SERVICE_TYPE_MOBILE;
				$this->arrData['RecordType']	= 15;
				$this->arrData['EndDatetime']	= $this->arrData['StartDatetime'];
				$this->arrData['Units']			= 1;
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 135:
				// UNKNOWN
				$this->arrData['ServiceType']	= ServiceType($this->arrData['FNN']);
				$this->arrData['RecordType']	= 0;
				$this->arrData['EndDatetime']	= date("Y-m-d H:i:s", strtotime("+{$this->arrData['Units']} seconds", strtotime($this->arrData['StartDatetime'])));
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 208:
				// OC&C
				$this->arrData['_Table']		= "Other";
				$this->arrData['FNN']			= trim(substr($arrLine['Description'], 0, 11));	// 11 to account for ADSL FNNs with i's
				$this->arrData['Description']	= trim(substr($arrLine['Description'], 13));
				$this->arrData['ServiceType']	= ServiceType($this->arrData['FNN']);
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			case 237:
				// S&E
				$arrDateRange = Array();				
				preg_match("/\d{1,2} [A-Za-z]{3} \d{4} to \d{1,2} [A-Za-z]{3} \d{4}$/", trim($arrLine['Description']), $arrDateRange);
				$arrDates = explode(" to ", $arrDateRange[1]);
				
				$this->arrData['FNN']			= trim(substr($arrLine['Description'], 0, 11));	// 11 to account for ADSL FNNs with i's
				$this->arrData['ServiceType']	= ServiceType($this->arrData['FNN']);
				$this->arrData['StartDatetime']	= $arrDates[0] . " 00:00:00";
				$this->arrData['EndDatetime']	= $arrDates[1] . " 00:00:00";
				$this->arrData['Description']	= trim(substr($arrLine['Description'], 13, 0-strlen($arrDateRange[1])));
				$this->arrData['Units']			= 1;
				
				// we have all of the data we need, so return back to FetchNext()
				return;
			
			//------------------------ SERVICE  SUMMARIES ------------------------//
			case 40:
				// SERVICE SUMMARY HEADER
				$this->arrData['Account']		= $this->_arrStatus['Account'];
				$this->arrData['AccountGroup']	= $this->_arrStatus['Account'];
				$this->arrData['FNN']			= $arrLine['FNN'];
				$this->intCurrentFNN			= $this->arrData['FNN'];
				
				// Call DecodeData() again
				$this->DecodeData();
				break;
			case 41:
				// SUMMARY DETAILS
				// Set data
				$this->arrData['_Table']		= "ServiceTypeTotal";
				$this->arrData['RecordType']	= $this->DecodeRecordType($arrLine['ChargeType']);
				$this->arrData['Records']		= (int)$arrLine['CallCount'];
				$this->arrData['Charge']		= (float)$arrLine['Charge'];
				return;
			case 45:
				// SUMMARY TOTALS
				// Set data
				$this->arrData['_Table']		= "ServiceTotal";
				$this->arrData['FNN']			= $arrLine['FNN'];
				$this->arrData['Account']		= $this->_arrStatus['Account'];
				$this->arrData['AccountGroup']	= $this->_arrStatus['Account'];
				$this->arrData['TotalCharge']	= (float)$arrLine['Total'];
				return;
			
			//-------------------------- CREDIT DETAILS --------------------------//
			/* TODO: Will we use these?
			case 60:
				// Start Credit Details
				
				break;
			case 61:
				// Credit Balance Total
				
				break;
			case 62:
				// Credit Amount Total
				
				break;
			case 63:
				// Credit Used
				
				break;
			case 64:
				// Credit Remaining
				
				break;
			case 69:
				// End Credit Detail
				
				break;
			*/
			case 99:
				// file footer
				$this->arrData = FALSE;
				return;
			
			default:
				// Recursively call DecodeData()
				$this->DecodeData();
				break;
		}
		
		return;
	}
	
	
	//------------------------------------------------------------------------//
	// SplitLine
	//------------------------------------------------------------------------//
	/**
	 * SplitLine()
	 *
	 * Splits up a line of data
	 *
	 * Splits up a line of data
	 *
	 * @param	string	$strLine		contents of current line 
	 *
	 * @return	array					associative array of data
	 *
	 * @method
	 */
	 function SplitLine($strLine)
	 {
	 	// clean the array
		$arrLine = Array();
		
		// Make sure this is a recognised type
		$intRecordType = (int)substr($strLine, 0, 3);
		$arrRecordDefine = NULL;
		$arrRecordDefine = $this->_arrEtechRecordType[$intRecordType];
		if (!$arrRecordDefine)
		{
			// Unknown Record Type (ie. invalid file)
			return "Unknown Record Type for line";
		}
		
		// Explode the line on '|'
		$arrRawLine = explode("|", $strLine);
		
		// Set the fields
		$i = 0;
		if (count($arrRecordDefine) > 1)
		{
			foreach($arrRecordDefine as $strKey=>$arrValue)
			{
				// Is the field optional (must be last field on line)?
				if (($arrValue['Optional'] === TRUE) && ($arrRawLine[$i] == NULL))
				{
					continue;
				}
				
				$mixData = trim($arrRawLine[$i]);
				
				// If it's a dollar value, then remove commas
				if ($arrValue['Type'] == ETECH_SHORT_CURRENCY || $arrValue['Type'] == ETECH_LONG_CURRENCY)
				{
					$mixData = (float)str_replace(",", "", $mixData);
				}
				
				$arrLine[$strKey] = $mixData;
				$i++;
			}
		}
		else
		{
			// not enough rows in define
			if ($intRecordType >= 200)
			{
				// S&E and OC&C
				$arrRow = $GLOBALS['FileFormatEtech']['ItemisedS&E'];
			}
			else
			{
				// Everything else
				$arrRow = $GLOBALS['FileFormatEtech']['ItemisedCall'];
			}
			foreach($arrRow as $strKey=>$arrValue)
			{
				// Is the field optional (must be last field on line)?
				if (($arrValue['Optional'] === TRUE) && ($arrRawLine[$i] == NULL))
				{
					continue;
				}
				
				$mixData = trim($arrRawLine[$i]);
				
				// If it's a dollar value, then remove commas
				if ($arrValue['Type'] == ETECH_SHORT_CURRENCY || $arrValue['Type'] == ETECH_LONG_CURRENCY)
				{
					$mixData = (float)str_replace(",", "", $mixData);
				}
				
				$arrLine[$strKey] = $mixData;
				$i++;
			}
		}
		
		return $arrLine;
	 }
	
	
	
	
	//------------------------------------------------------------------------//
	// DecodeRecordType
	//------------------------------------------------------------------------//
	/**
	 * DecodeRecordType()
	 *
	 * Converts and Etech Charge Type to Vixen Record Type
	 *
	 * Converts and Etech Charge Type to Vixen Record Type
	 *
	 * @param	string	$strChargeType	charge type to convert 
	 *
	 * @return	int						vixen record type
	 *
	 * @method
	 */
	 function DecodeRecordType($strChargeType)
	 {		
		$intServiceType = ServiceType($this->_arrStatus['FNN']);
		
		// ServiceType specific record types
		switch ($intServiceType)
		{
			case SERVICE_TYPE_LAND_LINE:
				switch ($strChargeType)
				{
					case "Local Calls":
						return 17;
					case "National Calls":
						return 19;
					case "Calls to 13/1300 Numbers":
						return 24;
					case "Calls to Mobiles":
						return 20;
					case "International Calls":
						return 28;
					case "Service & Equipment":
						return 21;
					case "Other Call Types":
					default:
						return 26;
				}
				break;
			
			case SERVICE_TYPE_MOBILE:
				switch ($strChargeType)
				{
					case "Mobile to International":
						return 27;
					case "Mobile to Mobile":
						return 2;
					case "Mobile to National":
						return 6;
					case "Mobile International Roaming":
						return 11;
					case "Mobile to 1800 Numbers":
						return 7;
					case "Mobile - SMS":
						return 10;
					case "Mobile - MMS":
						return 15;
					case "Mobile - Other Charges":
					default:
						return 16;
				}
				break;
			
			case SERVICE_TYPE_INBOUND:
				return 35;
		}
	 }
	
 }


?>
