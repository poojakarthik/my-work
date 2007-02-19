<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// decode_etech
//----------------------------------------------------------------------------//
/**
 * decode_etech
 *
 * Contains all classes for decoding etech scrape data
 *
 * Contains all classes for decoding etech scrape data
 *
 * @file		decode_etech.php
 * @language	PHP
 * @package		vixen_import
 * @author		Jared 'flame' Herbohn
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenDecode
//----------------------------------------------------------------------------//
/**
 * VixenDecode
 *
 * etech decode Module
 *
 * etech decode Module
 *
 *
 * @prefix		obj
 *
 * @package		vixen_import
 * @class		VixenDecode
 */
 class VixenDecode extends ApplicationBaseClass
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
	 * @return			Application
	 *
	 * @method
	 */
 	function __construct($arrConfig)
 	{
		parent::__construct();
		
		// local config
		$this->arrConfig = $arrConfig;
		
		// setup db object
		$this->sqlQuery = new Query();
		
		// sql result array
		$this->sqlResult = Array();
		
		
		// Init Statements
		$this->_insNoteType		= new StatementInsert('NoteType');

		$selNoteTypes	= new StatementSelect('NoteType', '*');		
		$selNoteTypes->Execute();
		$this->_arrNoteTypes = Array();
		
		while ($arrNoteType = $selNoteTypes->Fetch())
		{
			$this->_arrNoteTypes[$arrNoteType['TypeLabel']] = $arrNoteType['Id'];
		}
	}
	
	// ------------------------------------//
	// Decode Constants
	// ------------------------------------//
	
	// carrier code	
	function DecodeCarrier($intCarrierCode)
	{
		switch ($intCarrierCode)
		{
			case 19:
				$intCarrier = CARRIER_UNITEL;
				break;
			case 24:
				$intCarrier = CARRIER_OPTUS;
				break;
			case 26:
				$intCarrier = CARRIER_AAPT;
				break;
			default:
				$intCarrier = 0;
		}
		
		return $intCarrier;
	}
	
	// ------------------------------------//
	// FETCH RECORDS
	// ------------------------------------//
	
	function FetchIDDGroupRate()
	{
		$strQuery 	= "SELECT DataSerialised, AxisM FROM ScrapeRates";
		$strName	= 'IDDGroupRate';
		$arrRow = $this->FetchResult($strName, $strQuery);
		if ($arrRow)
		{
			$arrRow['DataArray'] = unserialize($arrRow['DataSerialised']);
			unset($arrRow['DataSerialised']);
		}
		return $arrRow;
	}
	
	function FetchCustomer()
	{
		$strQuery 	= "SELECT CustomerId, DataSerialized AS DataSerialised FROM ScrapeAccount ";
		$strName	= 'Account';
		$arrRow = $this->FetchResult($strName, $strQuery);
		if ($arrRow)
		{
			$arrRow['DataArray'] = unserialize($arrRow['DataSerialised']);
			unset($arrRow['DataSerialised']);
		}
		return $arrRow;
	}
	
	function FetchCustomerById($intCustomer)
	{
		$intCustomer = (int)$intCustomer;
		if (!$intCustomer)
		{
			return FALSE;
		}
		
		// get customer details
		$strQuery 	= "SELECT DataSerialized AS DataSerialised FROM ScrapeAccount WHERE CustomerId = $intCustomer LIMIT 1";
		$sqlResult = $this->sqlQuery->Execute($strQuery);
		if (!$sqlResult)
		{
			// return false if we can't get the results
			return FALSE;
		}
		$arrRow = $sqlResult->fetch_assoc();
		if ($arrRow)
		{
			$arrRow['DataArray'] = unserialize($arrRow['DataSerialised']);
			unset($arrRow['DataSerialised']);
			return($arrRow);
		}
		else
		{
			return FALSE;
		}
	}
	
	function FetchSystemNote()
	{
		$strQuery 	= "SELECT CustomerId, DataOriginal FROM ScrapeNoteSys ";
		$strName	= 'NoteSys';
		$arrNote = $this->FetchResult($strName, $strQuery);
		if ($arrNote)
		{
			$arrNote['DataArray'] = $this->ParseSystemNote($arrNote['DataOriginal'], $arrNote['CustomerId']);
			unset($arrNote['DataOriginal']);
		}
		return $arrNote;
	}
	
	function FetchUserNote()
	{
		$strQuery 	= "SELECT CustomerId, DataOriginal FROM ScrapeNoteUser ";
		$strName	= 'NoteUser';
		$arrNote = $this->FetchResult($strName, $strQuery);
		if ($arrNote)
		{
			$arrNote['DataArray'] = $this->ParseUserNote($arrNote['DataOriginal'], $arrNote['CustomerId']);
			unset($arrNote['DataOriginal']);
		}
		return $arrNote;
	}
	
	function FetchInvoiceDetail()
	{
		$strQuery 	= "SELECT CustomerId, DataOriginal FROM ScrapeInvoice ";
		$strName	= 'InvoiceDetails';
		$arrInvoice = $this->FetchResult($strName, $strQuery);
		if ($arrInvoice)
		{
			$arrInvoice['DataArray'] = $this->ParseInvoiceDetail($arrInvoice['DataOriginal'], $arrInvoice['CustomerId']);
			unset($arrInvoice['DataOriginal']);
		}
		return $arrInvoice;
	}
	
	function FetchCharges()
	{
		$strQuery 	= "SELECT CustomerId, DataOriginal FROM ScrapeInvoice ";
		$strName	= 'Charges';
		$arrCharges = $this->FetchResult($strName, $strQuery);
		if ($arrCharges)
		{
			$arrCharges['DataArray'] = $this->ParseCharges($arrCharges['DataOriginal'], $arrCharges['CustomerId']);
			unset($arrCharges['DataOriginal']);
		}
		return $arrCharges;
	}
	
	function FetchRecurringCharges()
	{
		$strQuery 	= "SELECT CustomerId, DataOriginal FROM ScrapeInvoice ";
		$strName	= 'RecurringCharges';
		$arrRecurringCharges = $this->FetchResult($strName, $strQuery);
		if ($arrRecurringCharges)
		{
			$arrRecurringCharges['DataArray'] = $this->ParseRecurringCharges($arrRecurringCharges['DataOriginal'], $arrRecurringCharges['CustomerId']);
			unset($arrRecurringCharges['DataOriginal']);
		}
		return $arrRecurringCharges;
	}
	
	function FetchAccountOptions()
	{
		$strQuery 	= "SELECT CustomerId, DataOriginal FROM ScrapeInvoice ";
		$strName	= 'AccountOptions';
		$arrOptions = $this->FetchResult($strName, $strQuery);
		
		if ($arrOptions)
		{
			$arrOptions ['DataArray'] = $this->ParseAccountOptions ($arrOptions ['DataOriginal'], $arrOptions ['CustomerId']);
			unset ($arrOptions ['DataOriginal']);
		}
		
		return $arrOptions;
	}
	
	function FetchMobileDetail()
	{
		$strQuery 	= "SELECT CustomerId, DataOriginal FROM ScrapeServiceMobile ";
		$strName	= 'MobileDetails';
		$arrRow = $this->FetchResult($strName, $strQuery);
		if ($arrRow)
		{
			$arrRow['DataArray'] = $this->ParseMobileDetail($arrRow['DataOriginal'], $arrRow['CustomerId']);
			unset($arrRow['DataOriginal']);
		}
		return $arrRow;
	}
	
	function FetchInboundDetail()
	{
		$strQuery 	= "SELECT CustomerId, FNN, DataOriginal FROM ScrapeServiceInbound ";
		$strName	= 'InboundDetails';
		$arrRow		= $this->FetchResult($strName, $strQuery);
		
		if ($arrRow)
		{
			$arrRow['DataArray'] = $this->ParseInboundDetail($arrRow['DataOriginal'], $arrRow['CustomerId'], $arrRow['FNN']);
			unset($arrRow['DataOriginal']);
		}
		
		return $arrRow;
	}
	
	function FetchMobileDetailsByAccount($intAccount)
	{
		$intAccount = (int)$intAccount;
		
		$strQuery 	= "SELECT CustomerId, DataOriginal FROM ScrapeServiceMobile WHERE CustomerId = $intAccount";
		$strName	= 'MobileDetailsByAccount';
		$arrMobile = $this->FetchResult($strName, $strQuery);
		if ($arrMobile)
		{
			$arrMobile['DataArray'] = $this->ParseMobileDetails($arrMobile['DataOriginal'], $arrMobile['CustomerId']);
			unset($arrMobile['DataOriginal']);
		}
		return $arrMobile;
	}
	
	function FetchPayment()
	{
		$strQuery 	= "SELECT Month, Year, DataOriginal FROM ScrapePayment ";
		$strName	= 'Payment';
		$arrRow = $this->FetchResult($strName, $strQuery);
		if ($arrRow)
		{
			$arrRow['DataArray'] = $this->ParsePayment($arrRow['DataOriginal']);
			unset($arrRow['DataOriginal']);
		}
		return $arrRow;
	}
	
	function FetchPassword()
	{
		$strQuery 	= "SELECT CustomerId, DataOriginal FROM ScrapeAccountViewDetail ";
		$strName	= 'FetchPassword';
		$arrRow = $this->FetchResult ($strName, $strQuery);
		
		if ($arrRow)
		{
			$arrRow['DataArray'] = $this->ParsePassword ($arrRow['DataOriginal'], $arrRow['CustomerId']);
			unset($arrRow['DataOriginal']);
		}
		
		return $arrRow;
	}
	
	function FetchCostCentre()
	{
		$strQuery 	= "SELECT CustomerId, DataOriginal FROM ScrapeAccountViewDetail ";
		$strName	= 'CostCentre';
		$arrRow = $this->FetchResult ($strName, $strQuery);
		
		if ($arrRow)
		{
			$arrRow['DataArray'] = $this->ParseCostCentre ($arrRow['DataOriginal'], $arrRow['CustomerId']);
			unset($arrRow['DataOriginal']);
		}
		
		return $arrRow;
	}
	
	// generic fetch
	function FetchResult($strName, $strQuery)
	{
		// check if we have the results yet
		if (!isset ($this->sqlResult[$strName]) || !$this->sqlResult[$strName])
		{
			// init the counter & total
			$this->sqlResult[$strName]['count'] = 0;
			$this->sqlResult[$strName]['total'] = 0;
			// if not, get them
			$strQuery .= " LIMIT 100";
			$this->sqlResult[$strName]['sql'] = $this->sqlQuery->Execute($strQuery);
			if (!$this->sqlResult[$strName]['sql'])
			{
				// return false if we can't get the results
				Return FALSE;
			}
		}
		
		// incrament the total
		$this->sqlResult[$strName]['total']++;
		
		// check if we need to get the next batch of results
		if ($this->sqlResult[$strName]['count'] > 99)
		{
			// reset the counter
			$this->sqlResult[$strName]['count'] = 0;
			
			// free the current result
			$this->sqlResult[$strName]['sql']->free();
			
			// get the next group of records
			$strQuery .= " LIMIT {$this->sqlResult[$strName]['total']}, 100";
			$this->sqlResult[$strName]['sql'] = $this->sqlQuery->Execute($strQuery);
			if (!$this->sqlResult[$strName]['sql'])
			{
				// return false if we can't get the results
				Return FALSE;
			}
		}
		
		// increment the counter
		$this->sqlResult[$strName]['count']++;
		
		// return next record
		return $this->sqlResult[$strName]['sql']->fetch_assoc();
	}
	
	// ------------------------------------//
	// PARSE
	// ------------------------------------//
	
	// parse user note
	function ParseUserNote($strNoteHtml, $intCustomerId)
	{
		// Read the DOM Document
		$domDocument	= new DOMDocument('1.0', 'utf-8');
		@$domDocument->LoadHTML($strNoteHtml);
		
		$dxpPath		= new DOMXPath($domDocument);
		
		//-----------------------------------------------
		//	Ok - Freeze Frame.
		//	We want to do the following
		//	
		//	1.	Get the second table in the page (because that's where the data resides)
		//	2.	Get the Third Row in the table and make sure it doesn't state that the
		//		table is empty
		//	3.	If the Third Row does not State that there are no rows
		//		1.	Get Each Row After the Third Row EXCEPT the last row
		//-----------------------------------------------
		
		//	1.	Get the second table in the page
		//	2.	Get the Third Row in the table and make sure it doesn't state that the
		//		table is empty
		
		$dncNotes = $dxpPath->Query("//table[2]/tr[position() >= 3 and position() mod 2 = 1]");
		
		// Check if we are told there are "No Results"
		if ($dncNotes->length == 1)
		{
			$domRow = new DOMDocument('1.0', 'utf-8');
			$domRow->formatOutput = true;
			@$domRow->appendChild (
				$domRow->importNode (
					$dncNotes->item (0),
					TRUE
				)
			);
			
			$xpaRow = new DOMXPath($domRow);
			
			if ($xpaRow->Evaluate("count(/tr/td[1][@colspan='3']) = 1"))
			{
				return FALSE;
			}
		}
		
		// Loop through each of the Rows
		$arrNotes = Array();
		$intCurrentRow = 0;
		foreach ($dncNotes as $dnoRow)
		{
			// Up the count
			$intCurrentRow++;
			
			$domRow = new DOMDocument('1.0', 'utf-8');
			$domRow->formatOutput = true;
			@$domRow->appendChild (
				$domRow->importNode (
					$dnoRow,
					TRUE
				)
			);
			
			$xpaRow = new DOMXPath($domRow);
			
			// Insert raw data 
			$arrNote = Array();
			$arrNote['Datetime']	= $xpaRow->Query("/tr/td[1]")->item(0)->nodeValue;
			$arrNote['NoteValue']	= $xpaRow->Query("/tr/td[2]")->item(0)->nodeValue;
			$arrNote['NoteType']	= $xpaRow->Query("/tr/td[3]")->item(0)->nodeValue;
			$arrNote['Employee']	= $xpaRow->Query("/tr/td[4]")->item(0)->nodeValue;
			$arrNote['CustomerId']	= $intCustomerId;
			$arrNotes[]	= $arrNote;
		}
		
		return $arrNotes;
	}
	
	// prase system note
	function ParseSystemNote($strNoteHtml, $intCustomerId)
	{
		// Read the DOM Document
		$domDocument	= new DOMDocument ('1.0', 'utf-8');
		@$domDocument->LoadHTML ($strNoteHtml);
		
		$dxpPath		= new DOMXPath ($domDocument);
		
		
		//-----------------------------------------------
		//	Ok - Freeze Frame.
		//-----------------------------------------------
		
		$dncNotes = $dxpPath->Query ("//table[1]/tr[position() >= 4 and position() mod 2 = 0]");
		
		// Check if we are told there are "No Results"
		if ($dncNotes->length == 1)
		{
			$domRow = new DOMDocument ('1.0', 'utf-8');
			$domRow->formatOutput = true;
			@$domRow->appendChild (
				$domRow->importNode (
					$dncNotes->item (0),
					TRUE
				)
			);
			
			$xpaRow = new DOMXPath ($domRow);
			
			if ($xpaRow->Evaluate ("count(/tr/td[1][@colspan='3']) = 1"))
			{
				return FALSE;
			}
		}
		
		// Loop through each of the Rows
		$arrNotes = Array ();
		$intCurrentRow = 0;
		foreach ($dncNotes as $dnoRow)
		{
			// Up the count
			$intCurrentRow++;
			
			$domRow = new DOMDocument ('1.0', 'utf-8');
			$domRow->formatOutput = true;
			@$domRow->appendChild (
				$domRow->importNode (
					$dnoRow,
					TRUE
				)
			);
			
			$xpaRow = new DOMXPath ($domRow);
			
			$arrNote['NoteValue']	= $xpaRow->Query ("/tr/td[2]")->item (0)->nodeValue;
			$arrNote['Employee']	= $xpaRow->Query ("/tr/td[3]")->item (0)->nodeValue;
			$arrNote['Datetime']	= $xpaRow->Query ("/tr/td[1]")->item (0)->nodeValue;
			$arrNote['NoteType']	= SYSTEM_NOTE_TYPE;
			$arrNote['CustomerId']	= $intCustomerId;
			$arrNotes[] = $arrNote;
		}

		// return array directly from data
		return $arrNotes;
	}
	
	// parse charges
	function ParseCharges($strHtml, $intCustomerId)
	{
		$arrCharges = Array ();
		
		$domDocument = new DOMDocument;
		@$domDocument->LoadHTML ($strHtml);
		$dxpDocument = new DOMXPath ($domDocument);
		
		$dnlTable = $dxpDocument->Query ("//table");
		
		$domTable = new DOMDocument;
		$domTable->appendChild (
			$domTable->importNode (
				$dnlTable->Item (7),
				TRUE
			)
		);
		
		$dxpTable = new DOMXPath ($domTable);
		
		$dnlRows = $dxpTable->Query ("//tr[position() >= 3 and position() < last()]");
		
		foreach ($dnlRows as $dnoRow)
		{
			$domRow = new DOMDocument;
			$domRow->appendChild (
				$domRow->importNode (
					$dnoRow,
					TRUE
				)
			);
			
			$dxpRow = new DOMXPath ($domRow);
			
			$strDetails = $dxpRow->Query ("/tr/td/img[1]")->item (0)->getAttribute ("alt");
			
			$arrDetails = explode ("\n", $strDetails);
			
			$arrChargeFields = Array (
				"Adjustment Message"	=> "Description"
			);
			
			$arrCharge = Array (
				"AccountGroup"	=> $intCustomerId,
				"Account"		=> $intCustomerId,
				"Service"		=> NULL,
				"InvoiceRun"	=> NULL,
				"CreatedByName"	=> NULL,
				"CreatedOn"		=> "",
				"ApprovedByName"=> "",
				"ChargeType"	=> "",
				"Description"	=> "",
				"ChargedOn"		=> "",
				"Nature"		=> "",
				"Amount"		=> "",
				"Status"		=> CHARGE_APPROVED
			);
			
			$strLastIndex = "";
			
			foreach ($arrDetails as $value)
			{
				if (strstr ($value, ": LP") || strstr ($value, "#:") || strstr ($value, "CSG Refund"))
				{
					$arrData = Array (
						"0"		=> $value
					);
				}
				else
				{
					$arrData = preg_split ("/\:/", $value, 2);
				}
				
				if (trim ($arrData [0]) == "")
				{
					continue;
				}
				
				if (!isset ($arrData [1]))
				{
					$arrData [1] = $arrData [0];
					$arrData [0] = $strLastIndex;
				}
				
				if (isset ($arrData [1]))
				{
					$arrData [1] = trim ($arrData [1]);
				}
				
				// AMOUNT
				// Removes the Dollar sign from the start of any amount
				if ($arrData [0] == "Amount")
				{
					$arrCharge ['Amount'] = str_replace ("$", "", $arrData [1]);
					continue;
				}
				
				// APPLIED BY
				// The person who Applied the Charge
				// In this case, this is also the person who Approved the Charge
				if ($arrData [0] == "Applied By")
				{
					$arrCharge ['CreatedByName'] = $arrData [1];
					$arrCharge ['ApprovedByName'] = $arrData [1];
					continue;
				}
				
				// APPLIED DATE/TIME
				// Stores the date in which this Charge was added to the System
				// and calculates the date that this charge was first created on.
				if ($arrData [0] == "Applied Date/Time")
				{
					$arrCharge ['CreatedOn'] = $arrData [1];
					continue;
				}
				
				// ADJUSTMENT TYPE
				// Works out if this is a Continuously Charge or a Count Controlled
				// Charge. If this is count controlled, it also gets the number of counts
				// until it ceases
				if ($arrData [0] == "Adjustment Type")
				{
					$arrCharge ['Nature'] = ($arrData [1] == "Debit") ? "DR" : "CR";
					continue;
				}
				
				// This is the Default for Storage
				if (!isset ($arrChargeFields [$arrData [0]]))
				{
					echo "Not Found: " . $arrData [0] . "\n";
					continue;
				}
				
				// Store the Last Field we were working with in case
				// This is a multi-line field
				$strLastIndex = $arrData [0];
				
				// Instantiate the storage array value if it doesn't exist
				if (!isset ($arrCharge [$arrData [0]]))
				{
					$arrCharge [$arrChargeFields [$arrData [0]]] = "";
				}
				
				// Add the Information
				$arrCharge [$arrChargeFields [$arrData [0]]] .= $arrData [1];
			}
			
			$arrCharges [] = $arrCharge;
		}
		
		return $arrCharges;
	}
	
	// parse recurring charges
	function ParseRecurringCharges($strHtml, $intCustomerId)
	{
		$arrRecurringCharges = Array ();
		
		// Load the entire HTML into a DOM Document
		$domDocument = new DOMDocument;
		@$domDocument->LoadHTML ($strHtml);
		
		$dxpDocument = new DOMXPath ($domDocument);
		
		// Find the Tables in the file and use Table 9 (Recurring Charges)
		$dnlTable = $dxpDocument->Query ("//table");
		
		$domTable = new DOMDocument;
		$domTable->appendChild (
			$domTable->importNode (
				$dnlTable->Item (9),
				TRUE
			)
		);
		
		$dxpTable = new DOMXPath ($domTable);
		
		// Get the Rows in that Table which are actual recurring charges
		$dnlRows = $dxpTable->Query ("/table/tr[position() >= 3 and position() != last()]");
		
		// Foreach Row (Recurring Charge)
		foreach ($dnlRows as $dnoRow)
		{
			$domRow = new DOMDocument;
			$domRow->appendChild (
				$domRow->importNode (
					$dnoRow,
					TRUE
				)
			);
			
			$dxpRow = new DOMXPath ($domRow);
			
			// There are 4 Columns in each Row. We only want the Image from Column 1
			$strDetails	= trim ($dxpRow->Query ("/tr/td/img[1]")->item (0)->getAttribute ("alt"));
			
			$arrDetails = explode ("\n", $strDetails);
			
			$arrRecurringChargeFields = Array (
				"Amount"				=> "RecursionCharge",
				"Frequency"				=> "Frequency",
				"Continuable"			=> "Continuable",
				"Payments Remaining"	=> "RemainingRecursions",
				"Invoice Message"		=> "Description"
			);
			
			$arrRecurringCharge = Array (
				"AccountGroup"			=> $intCustomerId,
				"Account"				=> $intCustomerId,
				"Service"				=> null,
				"ChargeType"			=> "",
				"Description"			=> "",
				"Nature"				=> "DR",
				"CreatedOn"				=> "",
				"StartedOn"				=> "",
				"LastChargedOn"			=> "",
				"RecurringFreqType"		=> "",
				"RecurringFreq"			=> "",
				"MinCharge"				=> "",
				"RecursionCharge"		=> "",
				"CancellationFee"		=> "0.0000",
				"Continuable"			=> 0,
				"PlanCharge"			=> FALSE,
				"UniqueCharge"			=> FALSE,
				"TotalCharged"			=> "0.0000",
				"TotalRecursions"		=> "0",
				"Archived"				=> 0
			);
			
			$strLastIndex = "";
			
			foreach ($arrDetails as $value)
			{
				if (strstr ($value, "IMEI"))
				{
					$arrData = Array (
						"0"		=> $value
					);
				}
				else
				{
					$arrData = preg_split ("/\:/", $value, 2);
				}
				
				if ($arrData [0] == "")
				{
					continue;
				}
				
				if (!isset ($arrData [1]))
				{
					$arrData [1] = $arrData [0];
					$arrData [0] = $strLastIndex;
				}
				
				if (isset ($arrData [1]))
				{
					$arrData [1] = trim ($arrData [1]);
				}
				
				// AMOUNT
				// Removes the Dollar sign from the start of any amount
				if ($arrData [0] == "Amount")
				{
					$arrRecurringCharge ['RecursionCharge'] = str_replace ("$", "", $arrData [1]);
					continue;
				}
				
				// APPLIED BY
				// The person who Applied the Recurring Charge
				// In this case, this is also the person who Approved the Recurring Charge
				if ($arrData [0] == "Added By")
				{
					$arrRecurringCharge ['CreatedByName'] = $arrData [1];
					$arrRecurringCharge ['ApprovedByName'] = $arrData [1];
					continue;
				}
				
				// FREQUENCY
				// The Frequency of the Recurring Charge (Monthly, Quarterly, ... etc)
				if ($arrData [0] == "Frequency")
				{
					switch (strtolower ($arrData [1]))
					{
						case "monthly":
							$arrRecurringCharge ['RecurringFreqType']	= BILLING_FREQ_MONTH;
							$arrRecurringCharge ['RecurringFreq']		= 1;
							break;
							
						case "quarterly":
							$arrRecurringCharge ['RecurringFreqType']	= BILLING_FREQ_MONTH;
							$arrRecurringCharge ['RecurringFreq']		= 3;
							break;
							
						case "half yearly":
							$arrRecurringCharge ['RecurringFreqType']	= BILLING_FREQ_MONTH;
							$arrRecurringCharge ['RecurringFreq']		= 6;
							break;
							
						case "annually":
							$arrRecurringCharge ['RecurringFreqType']	= BILLING_FREQ_MONTH;
							$arrRecurringCharge ['RecurringFreq']		= 12;
							break;
					}
					
					continue;
				}
				
				// APPLIED DATE/TIME
				// Stores the date in which this Recurring Charge was added to the System
				// and calculates the date that this recurring charge was first created on.
				if ($arrData [0] == "Applied Date/Time")
				{
					$arrRecurringCharge ['CreatedOn'] = $arrData [1];
					
					$intStartedOn = strtotime ($arrData [1]);
					$arrRecurringCharge ['StartedOn'] = date (
						"Y-m-d", 
						strtotime ("+1 month", mktime (0, 0, 0, date ("m", $intStartedOn), 1, date ("Y", $intStartedOn)))
					);
					
					continue;
				}
				
				// TYPE
				// Works out if this is a Continuously Recurring Charge or a Count Controlled
				// Recurring Charge. If this is count controlled, it also gets the number of counts
				// until it ceases
				if ($arrData [0] == "Type")
				{
					$arrRecurringCharge ['Continuable'] = ($arrData [1] == "Continuous Recurring");
					
					if (preg_match ("/^Set Number of Payments\[(\d+)\]$/", $arrData [1], $arrMatches))
					{
						$arrRecurringCharge ['TotalPaymentsRequired'] = $arrMatches [1];
					}
					
					continue;
				}
				
				// STATUS
				// Checks if the Recurring Charge is Archived or Available. If the Recurring Charge is Archived,
				// don't bother doing anything else with it
				if ($arrData [0] == "Status")
				{
					$arrRecurringCharge ['Archived'] = preg_match ("/^Cancelled - /", $arrData [1], $arrMatches);
					
					if ($arrRecurringCharge ['Archived'])
					{
						break;
					}
					
					continue;
				}
				
				// This is the Default for Storage
				if (!isset ($arrRecurringChargeFields [$arrData [0]]))
				{
					echo "Not Found: " . $arrData [0] . "\n";
					continue;
				}
				
				// Store the Last Field we were working with in case
				// This is a multi-line field
				$strLastIndex = $arrData [0];
				
				// Instantiate the storage array value if it doesn't exist
				if (!isset ($arrRecurringCharge [$arrData [0]]))
				{
					$arrRecurringCharge [$arrRecurringChargeFields [$arrData [0]]] = "";
				}
				
				// Add the Information
				$arrRecurringCharge [$arrRecurringChargeFields [$arrData [0]]] .= $arrData [1];
			}
			
			$intFirstThisMonth = mktime (0, 0, 0, 1, 1, date ("Y"));
			
			if (isset ($arrRecurringCharge ['TotalPaymentsRequired']))
			{
				// This calculates the Total Number of Payments that have been made and the total Amount that has been Charged
				// but only for count-controlled recurring charges
				
				$arrRecurringCharge ['TotalRecursions'] = $arrRecurringCharge ['TotalPaymentsRequired'] - $arrRecurringCharge ['RemainingRecursions'];
				$arrRecurringCharge ['TotalCharged'] = $arrRecurringCharge ['TotalRecursions'] * $arrRecurringCharge ['RecursionCharge'];
				
				$arrRecurringCharge ['MinCharge'] = $arrRecurringCharge ['TotalPaymentsRequired'] * $arrRecurringCharge ['RecursionCharge'];
				
				unset ($arrRecurringCharge ['TotalPaymentsRequired']);
				unset ($arrRecurringCharge ['RemainingRecursions']);
			}
			else
			{
				// This calculates the Total Number of Payments that have been made 
				// and the total Amount that has been Charged but only for continuous recurring charges
				
				$intCurrentMonth = strtotime ($arrRecurringCharge ['StartedOn']);
				
				$intMonths = 0;
				
				while (true)
				{
					if (
						date ("Y", $intFirstThisMonth) == date ("Y", $intCurrentMonth) &&
						date ("m", $intFirstThisMonth) == date ("m", $intCurrentMonth)
					)
					{
						break;
					}
					
					++$intMonths;
					$intCurrentMonth = strtotime ("+1 month", $intCurrentMonth);
				}
				
				$arrRecurringCharge ['TotalRecursions']		= $intMonths;
				$arrRecurringCharge ['TotalCharged']		= $intMonths * $arrRecurringCharge ['RecursionCharge'];
			}
			
			$arrRecurringCharge ['LastChargedOn']		= date ("Y-m-d", $intFirstThisMonth);
			
			// We only want to save this if it's not archived.
			if (!$arrRecurringCharge ['Archived'])
			{
				$arrRecurringCharges [] = $arrRecurringCharge;
			}
		}
		
		return $arrRecurringCharges;
	}
	
	// account options (namely: DisableDDR and LatePaymentFee)
	function ParseAccountOptions ($strHtml, $intCustomerId)
	{
		$arrAccountInformation = Array (
			"DisableDDR"			=> 0,
			"DisableLatePayment"	=> 0
		);
		
		// Load the entire HTML into a DOM Document
		$domDocument = new DOMDocument;
		@$domDocument->LoadHTML ($strHtml);
		
		$dxpDocument = new DOMXPath ($domDocument);
		
		// Get the three fields that we are interested in
		$bolNonDirectDebitFeeDisabled	= $dxpDocument->Query ("//input[@id='checkbox_nonddr'][@checked]");
		$bolLatePaymentDisabledAlways	= $dxpDocument->Query ("//input[@id='checkbox_lpdisable'][@checked]");
		$bolLatePaymentDisabledOnce		= $dxpDocument->Query ("//input[@id='checkbox_lpdisable_oneoff'][@checked]");
		
		$arrAccountInformation ['DisableDDR']			= $bolNonDirectDebitFeeDisabled->length == 1;
		$arrAccountInformation ['DisableLatePayment']	= (($bolLatePaymentDisabledAlways->length) ? 1 : (($bolLatePaymentDisabledOnce->length) ? -1 : 0));
		
		return $arrAccountInformation;
	}
	
	// parse invoice detail
	function ParseInvoiceDetail($strHtml, $intCustomerId)
	{
		$arrInvoices = Array ();
		
		$domDocument	= new DOMDocument;
		@$domDocument->LoadHTML ($strHtml);
		$dxpDocument	= new DOMXPath ($domDocument);	
		
		
		// Get the Table
		$dnlTable = $dxpDocument->Query ("//table");
		
		$domTable = new DOMDocument;
		$domTable->appendChild (
			$domTable->importNode (
				$dnlTable->Item (5),
				TRUE
			)
		);
		
		$dxpDocument	= new DOMXPath ($domTable);	
		
		
		// Get Each Possible Invoice Row
		$dnlRows		= $dxpDocument->Query ("/table/tr[position() >= 6 and position() < last()]");
		
		// Loop Through all the Invoice Rows
		foreach ($dnlRows as $dnoRow)
		{
			$domRow = new DOMDocument;
			$domRow->appendChild (
				$domRow->importNode (
					$dnoRow,
					TRUE
				)
			);
			
			$dxpRow		= new DOMXPath ($domRow);	
			
			// Pull the Invoice Information from the Rows
			$strMonthYear	= trim ($dxpRow->Query ("/tr/td[position() = 1]")->Item (0)->nodeValue);
			$strInvoiceId	= trim ($dxpRow->Query ("/tr/td[position() = 2]")->Item (0)->nodeValue);
			$strInvAmount	= trim ($dxpRow->Query ("/tr/td[position() = 3]")->Item (0)->nodeValue);
			$strInvApplied	= trim ($dxpRow->Query ("/tr/td[position() = 4]")->Item (0)->nodeValue);
			$strInvOwing	= trim ($dxpRow->Query ("/tr/td[position() = 5]")->Item (0)->nodeValue);
			$bolInvSent		= trim ($dxpRow->Query ("/tr/td[position() = 6]")->Item (0)->nodeValue) == "Yes";
			
			list ($strYear, $strMonth) = explode ("_", $strMonthYear);
			
			// get rid of $ and , in amounts
			$strInvAmount	= str_replace ("$", "", $strInvAmount);
			$strInvAmount	= str_replace (",", "", $strInvAmount);
			
			$strInvApplied	= str_replace ("$", "", $strInvApplied);
			$strInvApplied	= str_replace (",", "", $strInvApplied);
			
			$strInvOwing	= str_replace ("$", "", $strInvOwing);
			$strInvOwing	= str_replace (",", "", $strInvOwing);
			
			// To make sure we're dealing with an Invoice and not a pure PDF, 
			// check that the Invoice Number exists
			if ($strInvoiceId)
			{
				// Write up the Invoice add it to the Invoices array
				$arrInvoices [] = Array (
					"CustomerId"	=> $intCustomerId,
					"Month"			=> $strMonth,
					"Year"			=> $strYear,
					"InvoiceId"		=> $strInvoiceId,
					"Amount"		=> $strInvAmount,
					"Applied"		=> $strInvApplied,
					"Owing"			=> $strInvOwing,
					"Sent"			=> $bolInvSent
				);
			}
		}
		
		return $arrInvoices;
	}
	
	// prase mobile details
	function ParseMobileDetail($strMobileHtml, $intCustomerId)
	{
		// Read the DOM Document
		$domDocument	= new DOMDocument ('1.0', 'utf-8');
		@$domDocument->LoadHTML ($strMobileHtml);
		
		$dxpPath		= new DOMXPath ($domDocument);
		
		
		//-----------------------------------------------
		//	Ok - Freeze Frame.
		//-----------------------------------------------
		
		$delForm = $dxpPath->Query ("//form[@name='form1']")->Item (0);
		
		// Loop through each of the Rows
		$arrDetails = Array ();
		
		$domForm = new DOMDocument ('1.0', 'utf-8');
		@$domForm->appendChild (
			$domForm->importNode (
				$delForm,
				TRUE
			)
		);
			
		$xpaForm = new DOMXPath ($domForm);
		
		$dnlNumber		= $xpaForm->Query ("//input[@id='number']");
		$dnlSimPUK		= $xpaForm->Query ("//input[@id='puk']");
		$dnlSimESN		= $xpaForm->Query ("//input[@id='sim_esn_num']");
		$dnlSimState	= $xpaForm->Query ("//select[@id='sim_state']/option[@selected]");
		$dnlPlan		= $xpaForm->Query ("//select[@id='group_id']/option[@selected]");
		$dnlParent		= $xpaForm->Query ("//select[@name='shared_parent_number']/option[@selected]");
		$dnlComments	= $xpaForm->Query ("//textarea[@id='comments']");
		$dnlDOB_day		= $xpaForm->Query ("//select[@id='dob_day']/option[@selected]");
		$dnlDOB_month	= $xpaForm->Query ("//select[@id='dob_month']/option[@selected]");
		$dnlDOB_year	= $xpaForm->Query ("//select[@id='dob_year']/option[@selected]");
		
		$arrDetails['Number']		= (($dnlNumber->length		== 1) ? $dnlNumber->item (0)->getAttribute ("value") : "");
		$arrDetails['SimPUK']		= (($dnlSimPUK->length		== 1) ? $dnlSimPUK->item (0)->getAttribute ("value") : "");
		$arrDetails['SimESN']		= (($dnlSimESN->length		== 1) ? $dnlSimESN->item (0)->getAttribute ("value") : "");
		$arrDetails['SimState']		= (($dnlSimState->length	== 1) ? $dnlSimState->item (0)->getAttribute ("value") : "");
		$arrDetails['Plan']			= (($dnlPlan->length		== 1) ? $dnlPlan->item (0)->nodeValue : "");
		$arrDetails['Parent']		= (($dnlParent->length		== 1) ? $dnlParent->item (0)->getAttribute ("value") : "");
		$arrDetails['Comments']		= (($dnlComments->length	== 1) ? $dnlComments->item (0)->nodeValue : "");
		$arrDetails['DOB_month']	= (($dnlDOB_month->length	== 1) ? $dnlDOB_month->item (0)->nodeValue : 0);
		$arrDetails['DOB_day']		= (($dnlDOB_day->length		== 1) ? $dnlDOB_day->item (0)->nodeValue : 0);
		$arrDetails['DOB_year']		= (($dnlDOB_year->length	== 1) ? $dnlDOB_year->item (0)->nodeValue : 0);
		
		// return array directly from data
		return $arrDetails;
	}
	
	function ParseInboundDetail ($strHtml, $intCustomerId, $strFNN)
	{
		$domDocument	= new DOMDocument ('1.0', 'utf-8');
		@$domDocument->LoadHTML ($strHtml);
		
		$dxpPath		= new DOMXPath ($domDocument);
		
		$strAnswerPoint		= $dxpPath->Query ("//input[@name='ans_point']")->item (0)->getAttribute ("value");
		$bolComplexConfig	= ($dxpPath->Query ("//input[@name='complex_set'][@checked]")->length == 1);
		$strConfiguration	= $dxpPath->Query ("//textarea[@name='complex_desc']")->item (0)->nodeValue;
		
		return Array (
			"Service"			=> NULL,
			"AnswerPoint"		=> $strAnswerPoint,
			"Complex"			=> $bolComplexConfig,
			"Configuration"		=> $strConfiguration
		);
	}
	
	// god help us
	// parse payment history
	function ParsePayment($strHtml)
	{
		// Read the DOM Document
		$domDocument	= new DOMDocument ('1.0', 'utf-8');
		@$domDocument->LoadHTML ($strHtml);
		
		$dxpPath		= new DOMXPath ($domDocument);
		
		//-----------------------------------------------
		//	Ok - Freeze Frame.
		//-----------------------------------------------
		
		// This an example of what the Page looks like
		
		/*
		
			+--------------------------------------+----------------+--------------------------+
			| Name                                 | Customer ID    | Amount                   |
			+--------------------------------------+----------------+--------------------------+
			| AAA PTY LTD                          | 1000100000     | $100.00                  |		/tr/td[@bgcolor="#999900"]
			+--------------------------------------+----------------+--------------------------+
			|                                      |                | $100.00 [2006-06-15]     |		/tr/td[@bgcolor="#FFFFDD"]
			+--------------------------------------+----------------+--------------------------+
			| BBB PTY LTD                          | 1000100001     | $200.00                  |
			+--------------------------------------+----------------+--------------------------+
			|                                      |                | $99.50 [2006-06-14]      |
			+--------------------------------------+----------------+--------------------------+
			|                                      |                | $101.50 [2006-06-15]     |
			+--------------------------------------+----------------+--------------------------+
			
		*/
		
		//--------------------------------------------------------------------------------------
		//
		//	So this is how we're going to go about parsing this page
		//
		//	If the Background Color of the Row is #999900, then
		//	we can assume that we are gathering Customer Details
		//	In which case, we only want the "Customer ID" column
		//
		//	If the Background Color of the Row is #FFFFDD, then
		//	we can assume that we are gathering Payment Details
		//	In which case, we only want the "Amount" column
		//	which will have String functions performed
		//
		//	In the table we're working with, we want to loop through 
		//	from row #4 to the end
		//
		//--------------------------------------------------------------------------------------
		
		$delTable = $dxpPath->Query ("//table")->Item (4);
		
		$domTable = new DOMDocument ('1.0', 'utf-8');
		@$domTable->appendChild (
			$domTable->importNode (
				$delTable,
				TRUE
			)
		);
		
		$dxpTable	= new DOMXPath ($domTable);
		$delRows	= $dxpTable->Query ("/table/tr");
		
		$arrPayments = Array ();
		
		$strCurrentCustomer = "";
		
		// Loop through all the rows we found
		foreach ($delRows as $intIndex => $delRow)
		{
			if ($intIndex < 3 || $intIndex >= ($delRows->length - 3))
			{
				continue;
			}
			
			$domRow = new DOMDocument ('1.0', 'utf-8');
			@$domRow->appendChild (
				$domRow->importNode (
					$delRow,
					TRUE
				)
			);
				
			$xpaRow = new DOMXPath ($domRow);
			
			// Firstly, check if we're dealing with a Customer or with a Payment row
			// We can define this by checking the first TD of the row:
			//		If the Background Color is #999900, this is a Customer
			//		[otherwise] If the Background Color is #FFFFDD, this is a Payment 
			
			$bolCustomer = ($xpaRow->Query ("/tr/td")->Item (0)->getAttribute ("bgcolor") == "#999900");
			
			if ($bolCustomer)
			{
				// If we're dealing with a Customer, set $strCurrentCustomer to the Customer ID column's value
				$strCurrentCustomer = $xpaRow->Query ("/tr/td")->Item (1)->nodeValue;
				
				// Also - start the Array
				$arrPayments [$strCurrentCustomer] = Array ();
			}
			else
			{
				// Store the Amount + Date Combination in a Variable
				$strInformation = $xpaRow->Query ("/tr/td")->Item (2)->nodeValue;
				
				// Match the Information
				preg_match ("/([\S]+)\s\[([^\]]+)\]/misU", $strInformation, $arrMatches);
				
				// $arrMatches [1] = Amount
				// $arrMatches [2] = Date (YYYY-MM-DD)
				
				$arrPayment = Array (
					"Amount"		=> $arrMatches ['1'],
					"Date"			=> $arrMatches ['2'],
				);
				
				$arrPayments [$strCurrentCustomer][] = $arrPayment;
			}
		}
		
		// return array directly from data
		return $arrPayments;
	}
	
	// parse password out
	function ParsePassword ($strHtml, $intCustomerId)
	{
		// Put the HTML of the file into a DOM Document Object
		$domDocument = new DOMDocument;
		@$domDocument->LoadHTML ($strHtml);
		$domDocument->formatOutput = true;
		
		// Create an XPath object so we can search for Applicable Tables
		$dxpDocument = new DOMXPath ($domDocument);
		
		$dnlTDs = $dxpDocument->Query ("//td[@bgcolor='#FFF0D1']");
		
		foreach ($dnlTDs as $dnoTD)
		{
			$strTD = $dnoTD->nodeValue;
			
			if (preg_match ("/^Pass\: (.*)$/", $strTD, $arrMatches))
			{
				return Array (
					"password"	=> $arrMatches [1]
				);
			}
		}
	}
	
	// parse cost centre information
	function ParseCostCentre ($strHtml, $intCustomerId)
	{
		$arrServices = Array ();
		
		// Put the HTML of the file into a DOM Document Object
		$domDocument = new DOMDocument;
		@$domDocument->LoadHTML ($strHtml);
		$domDocument->formatOutput = true;
		
		// Create an XPath object so we can search for Applicable Tables
		$dxpDocument = new DOMXPath ($domDocument);
		
		// Get a list of Tables
		$dnlTables = $dxpDocument->Query ("//table");
		
		// Loop through each of the tables
		foreach ($dnlTables as $dnoTable)
		{
			// Put the Table into its own DOM Document Object, allowing us to perform
			// our own XPath Queries to find information
			$domTable = new DOMDocument;
			$domTable->appendChild (
				$domTable->importNode (
					$dnoTable,
					TRUE
				)
			);
			
			$dxpTable = new DOMXPath ($domTable);
			
			// At this point we want to check if we're dealing with the Applicable table.
			// We can identify whether or not we are dealing with the applicable table by testing 
			// to see if the value of the first row is equal to "Servicenumbers"
			
			$bolApplicable = $dxpTable->Evaluate ("/table/tr/td/strong = 'Servicenumbers'");
			
			
			// If we are dealing with an Applicable Table
			// Then we want to parse out the data
			
			if ($bolApplicable)
			{
				// With this table, we are ONLY USING THE FIRST COLUMN
				// Because of this, we wont bother to put each row in its own DOMDocument
				// Instead, we can pull the list of <a> tags (from the first column) directly
				$dnlCostCentres = $dxpTable->Query ("/table/tr/td[1]/a");
				
				foreach ($dnlCostCentres as $dnoCostCentre)
				{
					$strOnclick = $dnoCostCentre->getAttribute ("onclick");
					$strOnclick = substr ($strOnclick, 0, (0 - strlen ("','CostCentre','status=yes,scrollbars=yes,width=500,height=250')")));
					$strOnclick = substr ($strOnclick, strlen ("MM_openBrWindow('costcentre.php?"));
					
					parse_str ($strOnclick, $arrQuery);
					
					$arrServices [] = Array (
						"AccountGroup"	=> $intCustomerId,
						"Account"		=> $intCustomerId,
						"FNN"			=> CleanFNN ($arrQuery ['number']),
						"CostCentre"	=> ($dnoCostCentre->nodeValue == "Add Cost Centre") ? FALSE : $dnoCostCentre->nodeValue
					);
				}
			}
		}
		
		return $arrServices;
	}
	
	// ------------------------------------//
	// DECODE RECORDS
	// ------------------------------------//
	
	// decode user note
	function DecodeUserNote($arrNotes)
	{
		if (!is_array($arrNotes))
		{
			return FALSE;
		}
		
		// Loop through each of the Rows
		$intCurrentRow = 0;
		foreach ($arrNotes as $arrNote)
		{
			// Up the count
			$intCurrentRow++;
			
			// Datetime
			$arrDatetime = preg_split("/\s+/", $arrNote['Datetime']);
			
			$arrMonths = Array(
				"January"		=> 1,
				"February"		=> 2,
				"March"			=> 3,
				"April"			=> 4,
				"May"			=> 5,
				"June"			=> 6,
				"July"			=> 7,
				"August"		=> 8,
				"September"		=> 9,
				"October"		=> 10,
				"November"		=> 11,
				"December"		=> 12
			);
			
			// Time
			$arrTime = preg_split("/\:/", $arrDatetime [4]);
			
			$intDatetime = mktime(
				$arrTime [0],
				$arrTime [1],
				0,
				$arrMonths [$arrDatetime [0]],
				substr ($arrDatetime [1], 0, -1),
				$arrDatetime [2]
			);
			$arrNormalisedNote['Datetime']	= date("Y-m-d H:i:s", $intDatetime);


			// Note Type
			$strNoteType = preg_replace("/\W/", "", $arrNote['NoteType']);
			$intNoteType = 0;
			if (isset ($this->_arrNoteTypes[$strNoteType]))
			{
				$intNoteType = $this->_arrNoteTypes[$strNoteType];
			}
			else
			{
				$intNoteType = $this->_insNoteType->Execute (
					Array(
						'TypeLabel'			=> $strNoteType,
						'BorderColor'		=> '',
						'BackgroundColor'	=> '',
						'TextColor'			=> ''
					)
				);
				
				$this->_arrNoteTypes[$strNoteType] = $intNoteType;
			}
			$arrNormalisedNote['NoteType']	= $intNoteType;


			// Employee
			$arrNormalisedNote['EmployeeName']	= preg_replace("/[^A-Za-z ]+/", "", $arrNote['Employee']);
			$arrNormalisedNote['AccountGroup']	= $arrNote['CustomerId'];
			$arrNormalisedNote['Account']		= $arrNote['CustomerId'];
			$arrNormalisedNote['Note']			= $arrNote['NoteValue'];
			
			// Add to normalised array
			$arrNormalisedNotes[] = $arrNormalisedNote;
		}
		
		return $arrNormalisedNotes;
	}
	
	// decode system note
	function DecodeSystemNote($arrNotes)
	{
		if (!is_array($arrNotes))
		{
			return FALSE;
		}
			
		// Loop through each of the Rows
		$intCurrentRow = 0;
		foreach ($arrNotes as $arrNote)
		{
			// Up the count
			$intCurrentRow++;
			$arrNormalisedNote['Datetime']		= $arrNote['Datetime'];
			$arrNormalisedNote['NoteType']		= $arrNote['NoteType'];
			$arrNormalisedNote['AccountGroup']	= $arrNote['CustomerId'];
			$arrNormalisedNote['Account']		= $arrNote['CustomerId'];
			$arrNormalisedNote['Note']			= $arrNote['NoteValue'];
			$arrNormalisedNote['EmployeeName']	= preg_replace("/[^A-Za-z ]+/", "", $arrNote['Employee']);
			
			// Add to normalised array
			$arrNormalisedNotes[] = $arrNormalisedNote;
		}
		
		return $arrNormalisedNotes;
	}
	
	// decode mobile details
	function DecodeMobileDetail($arrDetails)
	{
		$intCustomer = (int)$arrDetails['CustomerId'];
		if (!is_array($arrDetails) || !$intCustomer)
		{
			return FALSE;
		}
		
		// get customer details
		$arrRow = $this->FetchCustomerById($intCustomer);
		$arrCustomer = $arrRow['DataArray'];
	
		// get RateGroups for this Customer
		$arrRateGroup = $this->DecodeRateGroup($arrCustomer);
		
		// get RatePlan Name
		$strRatePlanName = $this->arrConfig['RatePlanConvert'][SERVICE_TYPE_MOBILE][trim($arrDetails['Plan'])];
		
		return Array (
			// extra information
			"FNN"				=> CleanFNN($arrDetails['Number']),
			"RatePlanName"		=> $strRatePlanName,
			"PlanName"			=> $arrDetails['Plan'],
			"ParentFNN"			=> $arrDetails['Parent'],
			// table values
			"SimPUK"			=> $arrDetails['SimPUK'],
			"SimESN"			=> $arrDetails['SimESN'],
			"SimState"			=> $arrDetails['SimState'],
			"DOB"				=> (($arrDetails['DOB_month'] && $arrDetails['DOB_day'] && $arrDetails['DOB_year']) ? date ("Y-m-d", $arrDetails ['DOB']) : "0000-00-00"),
			"Comments"			=> $arrDetails['Comments'],
			"RateGroup"			=> $arrRateGroup
		);
	}
	
	// decode a customer
	function DecodeCustomer($arrCustomer)
	{
		// clean the output array
		$arrOutput = Array();
		
		$MonthAbbr = Array (
			"Jan"	=> "1",
			"Feb"	=> "2",
			"Mar"	=> "3",
			"Apr"	=> "4",
			"May"	=> "5",
			"Jun"	=> "6",
			"Jul"	=> "7",
			"Aug"	=> "8",
			"Sep"	=> "9",
			"Oct"	=> "10",
			"Nov"	=> "11",
			"Dec"	=> "12"
		);
	
		// is this customer archived
		$bolArchived = ($arrCustomer ['archived'] ? TRUE : FALSE);
		
		// ------------------------------------//
		// Account Group
		// ------------------------------------//
		$arrOutput['AccountGroup'][0] = Array('Id'=>$arrCustomer['CustomerId'], Archived=>$bolArchived);
		
		// ------------------------------------//
		// Credit Card
		// ------------------------------------//
		
		// credit card type
		switch ($arrCustomer['cc_type'])
		{
			case 'Visa':
				$arrOutput['CreditCard'][0]['CardType'] = CREDIT_CARD_VISA;
				break;
			
			case 'Mastercard':
				$arrOutput['CreditCard'][0]['CardType'] = CREDIT_CARD_MASTERCARD;
				break;
				
			case 'Bankcard':
				$arrOutput['CreditCard'][0]['CardType'] = CREDIT_CARD_BANKCARD;
				break;
			
			case 'Amex':
				$arrOutput['CreditCard'][0]['CardType'] = CREDIT_CARD_AMEX;
				break;
				
			case 'Diners':
				$arrOutput['CreditCard'][0]['CardType'] = CREDIT_CARD_DINERS;
				break;
		}
		
		// credit card details
		if ($arrOutput['CreditCard'][0])
		{
			$arrOutput['CreditCard'][0]['AccountGroup'] 	= $arrCustomer['CustomerId'];
			$arrOutput['CreditCard'][0]['Name'] 			= $arrCustomer['cc_name'];
			$arrOutput['CreditCard'][0]['CardNumber'] 		= $arrCustomer['cc_num'];
			$arrOutput['CreditCard'][0]['ExpMonth'] 		= $arrCustomer['cc_exp_m'];
			$arrOutput['CreditCard'][0]['ExpYear'] 			= $arrCustomer['cc_exp_y'];
			$arrOutput['CreditCard'][0]['CVV'] 				= $arrCustomer['cc_cvv'];
		}
		
		// ------------------------------------//
		// Account
		// ------------------------------------//
		
		// customer group
		switch (strtolower($arrCustomer['customer_group']))
		{
			case 'voicetalk':
				$arrCustomer['customer_group'] = 2;
				break;
				
			case 'imagine':
				$arrCustomer['customer_group'] = 3;
				break;
				
			case 'telcoblue':
			default:
				$arrCustomer['customer_group'] = 1;
		}
		
		// abn / acn
		$arrCustomer ['abn'] = (strlen (preg_replace ("/\D/", "", $arrCustomer ['abn_acn'])) == 11) ? $arrCustomer ['abn_acn'] : "";
		$arrCustomer ['acn'] = (strlen (preg_replace ("/\D/", "", $arrCustomer ['abn_acn'])) == 9) ? $arrCustomer ['abn_acn'] : "";
		
		// account
		$arrOutput['Account'][0]['Id'] 				= $arrCustomer['CustomerId'];
		$arrOutput['Account'][0]['BusinessName'] 	= $arrCustomer['businessname'];
		$arrOutput['Account'][0]['TradingName'] 		= $arrCustomer['tradingname'];
		$arrOutput['Account'][0]['ABN'] 				= $arrCustomer['abn'];
		$arrOutput['Account'][0]['ACN'] 				= $arrCustomer['acn'];
		$arrOutput['Account'][0]['Address1'] 		= $arrCustomer['address1'];
		$arrOutput['Account'][0]['Address2'] 		= $arrCustomer['address2'];
		$arrOutput['Account'][0]['Suburb'] 			= strtoupper($arrCustomer['suburb']);
		$arrOutput['Account'][0]['Postcode'] 		= $arrCustomer['postcode'];
		$arrOutput['Account'][0]['State'] 			= strtoupper($arrCustomer['state']);
		$arrOutput['Account'][0]['Country'] 			= 'AU';
		$arrOutput['Account'][0]['CustomerGroup'] 	= $arrCustomer['customer_group'];
		$arrOutput['Account'][0]['AccountGroup'] 	= $arrCustomer ['CustomerId'] . "', ";
		$arrOutput['Account'][0]['Archived'] 		= $bolArchived;
		
		// ------------------------------------//
		// Contacts
		// ------------------------------------//
		
		$arrContact = Array();
		$arrContact['AccountGroup'] 			= $arrCustomer['CustomerId'];
		$arrContact['Title'] 					= $arrCustomer['title'];
		$arrContact['FirstName'] 				= $arrCustomer['firstname'];
		$arrContact['LastName'] 				= $arrCustomer['lastname'];
		$arrContact['DOB'] 						= sprintf("%04d", intval ($arrCustomer['dob_year'])) . "-" . sprintf("%02d", ($arrCustomer['dob_month'] != "") ? intval($MonthAbbr[trim($arrCustomer['dob_month'])]) : "0") . "-" . sprintf("%02d", intval($arrCustomer['dob_day']));
		$arrContact['JobTitle'] 				= $arrCustomer['position'];
		$arrContact['Email'] 					= $arrCustomer['admin_email'];
		$arrContact['Account'] 					= $arrCustomer['CustomerId'];
		$arrContact['CustomerContact'] 			=  1;
		$arrContact['Phone'] 					= $arrCustomer['phone'];
		$arrContact['Mobile'] 					= $arrCustomer['mobile'];
		$arrContact['Fax'] 						= $arrCustomer['fax'];
		$arrContact['UserName'] 				= $arrCustomer['CustomerId'];
		$arrContact['PassWord'] 				= sha1("password"); //TODO!!!! - create a random password ????
		$arrContact['Archived'] 				= $bolArchived;
		
		//NOTE : First contact will be added with the name from the etech system
		//			and the first available email address. Any additional users
		//			will have their name changed to Billing Contact or 
		//			Secondary Billing Contact.
		
		// if we have an admin email address
		if ($arrCustomer ['admin_email'])
		{
			// add admin user
			$arrOutput['Contact'][] = $arrContact;
			
			// change the contact details for next user
			unset($arrContact['Title']);
			unset($arrCustomer['lastname']);
			$arrContact['FirstName'] 				= 'Billing Contact';
			$arrContact['UserName'] 				.= "-1";
			$arrContact['CustomerContact'] 			=  0;
		}

		// if we have a billing addrees that is different from the admin address
		if ($arrCustomer['billing_email'] && $arrCustomer['admin_email'] != $arrCustomer['billing_email'])
		{
			// add billing user
			$arrContact['Email'] 					= $arrCustomer['billing_email'];
			$arrOutput['Contact'][] = $arrContact;
			
			// change the contact details for next user
			unset($arrContact['Title']);
			unset($arrCustomer['lastname']);
			$arrContact['FirstName'] 				= 'Secondary Billing Contact';
			$arrContact['UserName'] 				.= "-2";
			$arrContact['CustomerContact'] 			=  0;
		}
		
		// if we have a second billing addrees that is different from the admin address & the first billing address
		if ($arrCustomer['billing_email_2'] && $arrCustomer['admin_email'] != $arrCustomer['billing_email_2'] && $arrCustomer['billing_email'] != $arrCustomer['billing_email_2'])
		{
			// add second billing user
			$arrContact['Email'] 					= $arrCustomer['billing_email_2'];
			$arrOutput['Contact'][] = $arrContact;
		}
		
		// if we did not add a contact yet
		if (!$arrOutput['Contact'])
		{
			// add a contact
			$arrOutput['Contact'][] = $arrContact;
		}
		
		// ------------------------------------//
		// Service Rate Groups
		// ------------------------------------//
		
		// put Rates into an array
		$arrRateGroup = Array();
		foreach($this->arrConfig['RecordType'] AS $strRecordType=>$intServiceType)
		{
			if ($arrCustomer[$strRecordType])
			{
				$arrRateGroup[$strRecordType] = $arrCustomer[$strRecordType];
			}
		}
		
		// decode Rate Groups
		$arrRateGroup = $this->DecodeRateGroup($arrRateGroup);
		
		// ------------------------------------//
		// Services
		// ------------------------------------//
		
		// clean the service arrays
		$arrServices = Array();
		$arrServiceType = Array();
		
		// for each service number
		foreach ($arrCustomer['sn'] as $sn_id => $_SN)
		{
			// clean the service number
			$strFNN = CleanFNN($_SN['Number'], $_SN['AreaCode']);
			
			// find the service type
			$arrServiceType[$strFNN] = ServiceType($strFNN);
			
			// check landlines for indial ranges
			if ($arrServiceType[$strFNN] == SERVICE_TYPE_LAND_LINE)
			{
				$strIndialPrefix = substr($strFNN, 0, 8); 
				$arrIndial[$strIndialPrefix]['Count']++;
			}
		}
		
		// set default service details
		$arrService = Array();
		$arrService['AccountGroup'] 			= $arrCustomer['CustomerId'];
		$arrService['Account'] 					= $arrCustomer['CustomerId'];
		$arrService['Archived'] 				= $bolArchived;
		
		// for each service number
		foreach ($arrCustomer['sn'] as $sn_id => $_SN)
		{
			// clean the service number
			$strFNN = CleanFNN($_SN['Number'], $_SN['AreaCode']);

			// raw services count
			$arrOutput['RawServiceCount']++;
			
			// default is not an indial 
			$arrService['Indial100'] 			= 0;
			
			// check Land Lines for indial ranges
			if ($arrServiceType[$strFNN] == SERVICE_TYPE_LAND_LINE)
			{
				// if this service is part of an indial range
				$strIndialPrefix = substr($strFNN, 0, 8); 
				if ($arrIndial[$strIndialPrefix]['Count'] > 90)
				{
					// check if we already have a GDN
					if($arrIndial[$strIndialPrefix]['GDN'])
					{
						// if so, don't add this service
						continue;
					}
					else
					{
						// if not, add this service as the GDN
						$arrIndial[$strIndialPrefix]['GDN'] = $strFNN;
						$arrService['Indial100'] 			= 1;
					}
				}
			}
			
			// add service
			$arrService['EtechId'] 				= $_SN['Id'];
			$arrService['FNN'] 					= $strFNN;
			$arrService['ServiceType'] 			= $arrServiceType[$strFNN];
			$arrOutput['Service'][$strFNN]		= $arrService;
			
			// real services count
			$arrOutput['ServiceCount']++;
			
			// get real service type
			$intServiceType								= $arrServiceType[$strFNN];
			
			// skip mobile services, we get their RateGroups someplace else
			if ($intServiceType == SERVICE_TYPE_MOBILE)
			{
				continue;
			}
			
			// get fake service type
			if ($intServiceType == SERVICE_TYPE_INBOUND)
			{
				// use default RateGroups for inbound services
				$strPrefix = substr($strFNN, 0, 2);
				if ($strPrefix == '13')
				{
					$intFakeServiceType = 1300;
				}
				elseif ($strPrefix == '18')
				{
					$intFakeServiceType = 1800;
				}
			}
			else
			{
				$intFakeServiceType = $intServiceType;
			}
			
			// add serviceRateGroup records
			if (is_array($arrRateGroup[$intFakeServiceType]))
			{
				$arrServiceRateGroup['FNN'] 				= $strFNN;
				$arrServiceRateGroup['ServiceType'] 		= $intServiceType;
				
				foreach($arrRateGroup[$intFakeServiceType] AS $strRecordType=>$strRateGroupName)
				{
					$arrServiceRateGroup['RecordTypeName'] 	= $strRecordType;
					$arrServiceRateGroup['RateGroupName'] 	= $strRateGroupName;
					$arrOutput['ServiceRateGroup'][]		= $arrServiceRateGroup;
				}
			}
		}
		
		// return the output array
		return $arrOutput;
	}
	
	// decode a customers rate groups
	// returns	: Array[intServiceType][strRecordType] = strRateGroupName
	function DecodeRateGroup($arrCustomer)
	{
		// clean the output array
		if (is_array($this->arrConfig['DefaultRateGroup']))
		{
			// or set it to the default array
			$arrOutput = $this->arrConfig['DefaultRateGroup'];
		}
		else
		{
			$arrOutput = Array();
		}
		
		// for each record type
		foreach($this->arrConfig['RecordType'] AS $strRecordType=>$intServiceType)
		{
			// if we have an etech rate for this record type
			if ($arrCustomer[$strRecordType])
			{
				// get the etech rate name
				$strRateName = $arrCustomer[$strRecordType];
				
				// try to match it to new rate groups		
				if (is_array($this->arrConfig['RateConvert'][$strRecordType][$strRateName]))
				{
					foreach($this->arrConfig['RateConvert'][$strRecordType][$strRateName] AS $strNewRecordType=>$strRateGroupName)
					{
						$arrOutput[$intServiceType][$strNewRecordType] = $strRateGroupName;
					}
				}
			}
		}
		return $arrOutput;
	}
	
	
	// decode Destinations from a group of IDD rate records
	function DecodeIDDDestination($arrScrapeRate)
	{
		// return on error
		if (!is_array($arrScrapeRate['Rates']))
		{
			return FALSE;
		}
		
		// clean output array
		$arrOutput = Array();
		
		// for each record
		foreach ($arrScrapeRate['Rates'] as $arrRate)
		{	
			// get the destination
			if ($arrRate['Destination'])
			{
				$arrOutput[] = $arrRate['Destination'];
			}
		}
		
		return $arrOutput;
	}
	
	// decode a group of IDD rate records
	function DecodeIDDGroupRate($arrScrapeRate)
	{
		// return on error
		if (!is_array($arrScrapeRate['Rates']))
		{
			return FALSE;
		}
		
		// clean output array
		$arrFullOutput = Array();
		
		// set RateGroup wide values
		$fltStdFlagfall		= $arrScrapeRate['StdFlag'];
		$fltExsFlagfall		= $arrScrapeRate['PostFlag'];
		$arrTitle 			= explode(': ',$arrScrapeRate['Title']);
		$strName 			= trim($arrTitle[1]);
		
		// get carrier code
		$intCarrier			= $this->DecodeCarrier($arrScrapeRate['Carrier']);	
		
		// get carrier name
		if ($intCarrier)
		{
			$strCarrier 	= GetCarrierName($intCarrier);
		}
		else
		{
			$strCarrier 	= 'Unknown';
		}
		
		// for each record
		foreach ($arrScrapeRate['Rates'] as $arrRate)
		{
			// clean output array
			$arrOutput = Array();
			
			// get rate specific values
			$strDestination 	= $arrRate['Destination'];
			$intCapSet 			= Min((int)$arrScrapeRate['SetCap'], (int)$arrRate['CapSet']);
			//$intCapSet 			= (int)$arrRate['CapSet'];
			
			// work out the record & service type
			// ############################################################################################################################ //
			// HACK ALERT : this will only work if mobile IDD rates start with 'mobile' : will work for telcoblue, may not work for others
			// ############################################################################################################################ //
			if (strpos(strtolower($strName),'mobile') === 0)
			{
				// Mobile
				$intServiceType 			= 101;
				$intRecordType		 		= 27;
			}
			else
			{
				// LL
				$intServiceType 			= 102;
				$intRecordType		 		= 28;
			}
			// ############################################################################################################################ //
			// ############################################################################################################################ //
			
			// set output array values
			$arrOutput['Name'] 				= "$strName : $strDestination";
			$arrOutput['Description'] 		= "$strDestination on the $strName plan";
			$arrOutput['StdRatePerUnit'] 	= number_format($arrRate['StdRate'] / 60, 8, '.', '');
			$arrOutput['ExsRatePerUnit'] 	= number_format($arrRate['PostCredit'] / 60, 8, '.', '');
			if ($intCapSet)
			{
				$arrOutput['CapUsage'] 		= Max($arrScrapeRate['CapTime'], $arrRate['CapSeconds']);
				$arrOutput['CapCost']		= Max($arrScrapeRate['MaxCost'], $arrRate['CapCost']);
			}
			$arrOutput['StdFlagfall'] 		= $fltStdFlagfall;
			$arrOutput['ExsFlagfall'] 		= $fltExsFlagfall;
			$arrOutput['ServiceType'] 		= $intServiceType;
			$arrOutput['RecordType'] 		= $intRecordType;
			$arrOutput['StdUnits'] 			= 1;
			$arrOutput['ExsUnits'] 			= 1;
			$arrOutput['StartTime'] 		= '00:00:00';
			$arrOutput['EndTime'] 			= '23:59:59';
			$arrOutput['Monday'] 			= 1;
			$arrOutput['Tuesday'] 			= 1;
			$arrOutput['Wednesday'] 		= 1;
			$arrOutput['Thursday'] 			= 1;
			$arrOutput['Friday'] 			= 1;
			$arrOutput['Saturday'] 			= 1;
			$arrOutput['Sunday'] 			= 1;
			
			// find the destination code
			$strQuery = "SELECT Code FROM Destination WHERE Description = '$strDestination' AND Context = 1 LIMIT 1";
			$sqlResult = $this->sqlQuery->Execute($strQuery);
			$row = $sqlResult->fetch_assoc();
			if ($row['Code'])
			{
				$arrOutput['Destination'] 			= $row['Code'];
			}
			
			$arrFullOutput[] = $arrOutput;
		}
		
		return $arrFullOutput;
	}


}

?>
