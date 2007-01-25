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
	
	function FetchMobileDetails()
	{
		$strQuery 	= "SELECT CustomerId, DataOriginal FROM ScrapeServiceMobile ";
		$strName	= 'MobileDetails';
		$arrMobile = $this->FetchResult($strName, $strQuery);
		if ($arrMobile)
		{
			$arrMobile['DataArray'] = $this->ParseMobileDetails($arrMobile['DataOriginal'], $arrMobile['CustomerId']);
			unset($arrMobile['DataOriginal']);
		}
		return $arrMobile;
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
	
	// prase mobile details
	function ParseMobileDetails($strMobileHtml, $intCustomerId)
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
	function DecodeMobileDetails($arrDetails)
	{
		if (!is_array($arrDetails))
		{
			return FALSE;
		}
		
		return Array (
			"ServiceMobileDetail"	=> Array (
				"SimPUK"			=> $arrDetails ['SimPUK'],
				"SimESN"			=> $arrDetails ['SimESN'],
				"SimState"			=> $arrDetails ['SimState'],
				"DOB"				=> (($arrDetails['DOB_month'] && $arrDetails['DOB_day'] && $arrDetails['DOB_year']) ? date ("Y-m-d", $arrDetails ['DOB']) : "0000-00-00"),
				"Comments"			=> $arrDetails ['Comments'],
			),
			
			"Plan"					=> $arrDetails ['Plan'],
			"Parent"				=> $arrDetails ['Parent'],
		);
	}
	
	// decode a customer
	function DecodeCustomer($arrCustomer)
	{
		// clean the output array
		$arrOutput = Array();
		
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
		switch ($arrCustomer['customer_group'])
		{
			case 'VoiceTalk':
				$arrCustomer['customer_group'] = 2;
				break;
				
			case 'Imagine':
				$arrCustomer['customer_group'] = 3;
				break;
				
			case 'TelcoBlue':
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
		//TODO!!!! - $arrOutput['Account'][0]['CreditCard']
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
			$arrOutput['Name'] 				= "$strName : $strCarrier : $strDestination";
			$arrOutput['Description'] 		= "Calls to $strDestination via the $strCarrier network on the $strName plan";
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
			$arrOutput['StartTime'] 		= '00:00:00';
			$arrOutput['EndTime'] 			= '23:59:59';
			$arrOutput['Monday'] 			= 1;
			$arrOutput['Tuesday'] 			= 1;
			$arrOutput['Wednesday'] 		= 1;
			$arrOutput['Thursday'] 			= 1;
			$arrOutput['Friday'] 			= 1;
			$arrOutput['Saturday'] 			= 1;
			$arrOutput['Sunday'] 			= 1;
			
			// try to find the destination code
			$strQuery = "SELECT Code FROM DestinationCode WHERE CarrierDescription LIKE '$strDestination' AND Carrier = $intCarrier LIMIT 1";
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
