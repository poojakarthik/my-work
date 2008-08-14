<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Payment_DirectDebit_File
//----------------------------------------------------------------------------//
/**
 * Payment_DirectDebit_File
 *
 * Base Class for Charging Direct Debit Payments
 *
 * Base Class for Charging Direct Debit Payments
 *
 * @file		Payment_DirectDebit.php
 * @language	PHP
 * @package		cli.payment.directdebit
 * @author		Rich "Waste" Davis
 * @version		8.08
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// Payment_DirectDebit
//----------------------------------------------------------------------------//
/**
 * Payment_DirectDebit
 *
 * Base Class for Charging Direct Debit Payments
 *
 * Base Class for Charging Direct Debit Payments
 *
 * @prefix		exp
 *
 * @package		cli.payment
 * @class		Payment_DirectDebit_File
 */
 class Payment_DirectDebit_File extends CarrierModule
 {
 	//------------------------------------------------------------------------//
	// Properties
	//------------------------------------------------------------------------//
	protected	$_arrFileContent;
	protected	$_arrDefine;
	protected	$_arrFilename;
	protected	$_arrHeader;
	protected	$_arrFooter;
	protected	$_ptrFile;
	
	
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor
	 *
	 * Constructor
	 * 
	 * @param	integer	$intCarrier				The Carrier using this Module
	 * 
	 * @return	ExportBase
	 *
	 * @method
	 */
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier, MODULE_TYPE_PAYMENT_DIRECT_DEBIT);
 		
 		// Defaults
 		$this->intCarrier		= $intCarrier;
 		$this->_strDelimiter	= ",";
 		$this->_arrDefine		= Array();
 		$this->_arrFileContent	= Array();
 		$this->bolExported		= FALSE;
 		$this->_intMinRequests	= 0;
 		$this->_strFileContents	= '';
 		
 		// Statements 		
 		$this->_selCarrierModule		= new StatementSelect("CarrierModule", "*", "Carrier = <Carrier> AND Module = <Module> AND Type = ".MODULE_TYPE_PROVISIONING_OUTPUT);
 	}
 	
 	
 	//------------------------------------------------------------------------//
	// _Render
	//------------------------------------------------------------------------//
	/**
	 * _Render()
	 *
	 * Renders this file to its final output format
	 *
	 * Renders this file to its final output format
	 * 
	 * @param	boolean	$bolRenderToFile	optional	Whether to write to the output file (default: TRUE)
	 * 
	 * @return	mixed									['Pass']		: boolean
	 * 													['Description']	: string
	 *
	 * @method
	 */
 	protected function _Render($bolRenderToFile = TRUE)
 	{
 		$strDirectory		= FILES_BASE_PATH."export/payment/directdebit/".strtolower(GetConstantDescription($this->_intModuleCarrier, 'Carrier'))."/".get_class($this)."/";
 		$arrResult			= $this->_RenderLineTXT($this->_arrFilename, FALSE, '');
 		$this->_strFilePath	= $strDirectory . $arrResult['Line'];
 		
 		// Init file
 		if ($bolRenderToFile)
 		{
	 		if (!file_exists($strDirectory))
	 		{
	 			mkdir($strDirectory, 0777, TRUE);
	 		}
	 		
	 		switch ($this->_strFileFormat)
	 		{
	 			case 'XLS':
	 				// Create new XLS file
					$this->_ptrFile			= new Spreadsheet_Excel_Writer($this->_strFilePath);
					$this->_wksWorksheet	=& $this->_ptrFile->addWorksheet();
					$this->_arrFormat		= $this->_InitExcelFormats($this->_ptrFile);
					$this->_intRow			= 0;
	 				break;
	 				
	 			default:
	 				// Create new TXT file
	 				if (!$this->_ptrFile = fopen($this->_strFilePath, 'w'))
	 				{
	 					return Array('Pass' => FALSE, 'Description' => "Could not open file '{$this->_strFilePath}'");
	 				}
	 				break;
	 		}
 		}
 		
 		// Render Header
 		if ($this->_arrDefine['Header'])
 		{
			$arrResult	= $this->_RenderLine($this->_arrHeader, 'Title', $bolRenderToFile);
			if (!$arrResult['Pass'] && PROVISIONING_DEBUG)
			{
				CliEcho($arrResult['Line']);
			}
			else
			{
				$this->_strFileContents	.= $arrResult['Line'];
			}
 		}
 		
 		// Render each line
 		foreach ($this->_arrFileContent as $arrLine)
 		{
 			$arrResult	= $this->_RenderLine($arrLine, NULL, $bolRenderToFile);
 			
 			if (!$arrResult['Pass'] && PROVISIONING_DEBUG)
 			{
 				CliEcho($arrResult['Line']);
 			}
			else
			{
				$this->_strFileContents	.= $arrResult['Line'];
			}
 		}
 		
 		// Render Footer
 		if ($this->_arrDefine['Footer'])
 		{
			$arrResult	= $this->_RenderLine($this->_arrFooter, NULL, $bolRenderToFile);
			if (!$arrResult['Pass'] && PROVISIONING_DEBUG)
			{
				CliEcho($arrResult['Line']);
			}
			else
			{
				$this->_strFileContents	.= $arrResult['Line'];
			}
 		}
 		
 		// Replace \\n with \n
 		$this->_strFileContents	= str_replace('\n', "\n", $this->_strFileContents);
 		
 		// Close file
 		if ($bolRenderToFile)
 		{
	 		switch ($this->_strFileFormat)
	 		{
	 			case 'XLS':
	 				// Close XLS file
					$this->_ptrFile->close();
	 				break;
	 				
	 			default:
	 				// Close TXT file
	 				if (!fclose($this->_ptrFile))
	 				{
	 					return Array('Pass' => FALSE, 'Description' => "Could not close file '{$this->_strFilePath}'");
	 				}
	 				break;
	 		}
	 		
	 		// Change permissions
	 		chmod($this->_strFilePath, 0777);
 		}
 		
 		return Array('Pass' => TRUE);
 	}
 	
 	
 	
 	//------------------------------------------------------------------------//
	// _RenderLine
	//------------------------------------------------------------------------//
	/**
	 * _RenderLine()
	 *
	 * Renders a line into final output format
	 *
	 * Renders a line into final output format
	 * 
	 * @param	array	$arrLine						Line to Render
	 * @param	string	$strStyle			optional	Styling to apply to the line (XLS only)
	 * @param	boolean	$bolRenderToFile	optional	Whether to write to the output file (default: TRUE)
	 * 
	 * @return	mixed									['Pass'] : boolean
	 * 													['Line'] : string
	 *
	 * @method
	 */
 	protected function _RenderLine($arrLine, $strStyle = NULL, $bolRenderToFile = TRUE)
 	{
 		switch ($this->_strFileFormat)
 		{
 			case 'XLS':
 				$arrResult	= $this->_RenderLineXLS($arrLine, $strStyle, $bolRenderToFile);
 				//CliEcho($arrResult['Line']);
 				break;
 				
 			default:
 				$arrResult	= $this->_RenderLineTXT($arrLine, $bolRenderToFile);
 				//CliEcho($arrResult['Line']);
 				break;
 		}
 		
 		return $arrResult;
 	}
 	
 	
 	//------------------------------------------------------------------------//
	// _RenderLineTXT
	//------------------------------------------------------------------------//
	/**
	 * _RenderLineTXT()
	 *
	 * Renders a line into final output format (Plaintext)
	 *
	 * Renders a line into final output format (Plaintext)
	 * 
	 * @param	array	$arrLine						Line to Render
	 * @param	boolean	$bolRenderToFile	optional	Whether to write to the output file (default: TRUE)
	 * 
	 * @return	mixed									['Pass'] : boolean
	 * 													['Line'] : string
	 *
	 * @method
	 */
 	private function _RenderLineTXT($arrLine, $bolRenderToFile = TRUE, $strDelimiterOverride = NULL)
 	{
 		//Debug($arrLine);
 		
 		$arrOutput = Array();
 		foreach ($this->_arrDefine[$arrLine['**Type']] as $strField=>$arrField)
 		{
 			
 			//CliEcho("{$arrField['PadType']}; {$arrField['PadChar']}\t", FALSE);
 			// Put in default values
 			$arrField['Value']		= ($arrLine[$strField] !== NULL)	? $arrLine[$strField]							: $arrField['Value'];
 			$arrField['PadChar']	= ($arrField['PadChar'] !== NULL)	? $arrField['PadChar']							: ' ';
 			$arrField['PadType']	= ($arrField['PadType'] !== NULL)	? $arrField['PadType']							: STR_PAD_RIGHT;
 			$arrField['Value']		= ($arrField['Config'])				? $this->GetConfigField($arrField['Config'])	: $arrField['Value'];
 			
 			//Debug($arrField['Value']);
 			
 			// Prepare field
 			if (!trim($arrField['Value']) && isset($arrField['Optional']))
 			{
				// Optional field is empty
				$mixValue	= $arrField['Optional'];
 			}
 			else
 			{
	 			$arrType = explode('::', $arrField['Type']);
	 			switch ($arrType[0])
	 			{
	 				case 'Integer':
	 					$mixValue	= (int)$arrField['Value'];
	 					//Debug($mixValue);
	 					break;
	 				
	 				case 'Date':
	 					$strDate	= $arrField['Value'];
	 					switch ($arrType[1])
	 					{
	 						case 'YYYYMMDD':
	 							$strParse	= $strDate;
	 							break;
	 							
	 						case 'YYYY-MM-DD':
	 							$strParse	= $strDate;
	 							break;
	 						
	 						case 'DD-MM-YYYY':
	 							$strParse	= substr($strDate, -4, 4);
	 							$strParse	.= substr($strDate, 4, 2);
	 							$strParse	.= substr($strDate, 0, 2);
	 					}
	 					
	 					// Is it a valid date?
	 					if (!strtotime($strParse))
	 					{
							$strMessage	= "Request #{$arrLine['**Request']}; Field '$strField' with value '$strDate' is not a valid {$arrType[1]} date";
							return Array('Pass' => FALSE, 'Line' => $strMessage);
	 					}
	 					$mixValue	= $strDate; 					
	 					break;
	 				
	 				default:
	 					// String
	 					$mixValue	= $arrField['Value'];
	 					break;
	 			}
 			}
 			
			// Is this fixed-width?
			if ($arrField['Length'] > 0)
			{
				if (($intLength = strlen($mixValue)) > $arrField['Length'])
				{
					// Field is too long, fail out
					$strMessage	= "Request #{$arrLine['**Request']}; Field '$strField' is $intLength chars, expected max {$arrField['Length']} chars";
					return Array('Pass' => FALSE, 'Line' => $strMessage);
				}
				else
				{
					// Pad the field
					$mixValue	= str_pad($mixValue, $arrField['Length'], $arrField['PadChar'], $arrField['PadType']);
				}
			}
			
			$arrOutput[]	= $mixValue;
 		}
 		
 		// Implode Plaintext line
 		$strDelimiter	= ($strDelimiterOverride !== NULL) ? $strDelimiterOverride : $this->_strDelimiter;
 		$strLine		= implode($strDelimiter, $arrOutput);
 		
 		//DebugBackTrace();
 		
 		// Write to file
 		if ($bolRenderToFile)
 		{
 			fwrite($this->_ptrFile, $strLine.$this->_strNewLine);
 		}
 		
 		//Debug("Line: $strLine");
 		
 		return Array('Pass' => TRUE, 'Line' => $strLine);
 	}
 	
 	//------------------------------------------------------------------------//
	// _RenderLineXLS
	//------------------------------------------------------------------------//
	/**
	 * _RenderLineXLS()
	 *
	 * Renders a line into final output format (Excel 5)
	 *
	 * Renders a line into final output format (Excel 5)
	 * 
	 * @param	array	$arrLine						Line to Render
	 * @param	string	$strStyle			optional	Styling to apply to the line
	 * @param	boolean	$bolRenderToFile	optional	Whether to write to the output file (default: TRUE)
	 * 
	 * @return	mixed									['Pass'] : boolean
	 * 													['Line'] : string
	 *
	 * @method
	 */
 	private function _RenderLineXLS($arrLine, $strStyle = NULL, $bolRenderToFile = TRUE)
 	{
 		// Set first column
 		$intCol	= ($this->_intColOffset) ? $this->_intColOffset : 0;
 		
 		$arrOutput = Array();
 		foreach ($this->_arrDefine[$arrLine['**Type']] as $strField=>$arrField)
 		{
 			// Put in default values
 			$arrField['Value']		= ($arrLine[$strField] !== NULL)	? $arrLine[$strField]							: $arrField['Value'];
 			$arrField['PadChar']	= ($arrField['PadChar'] !== NULL)	? $arrField['PadChar']							: ' ';
 			$arrField['PadType']	= ($arrField['PadType'] !== NULL)	? $arrField['PadType']							: STR_PAD_RIGHT;
 			$arrField['Value']		= ($arrField['Config'])				? $this->GetConfigField($arrField['Config'])	: $arrField['Value'];
 			
 			// Prepare field
 			$arrType = explode('::', $arrField['Type']);
 			switch ($arrType[0])
 			{
 				case 'Integer':
 					$mixValue	= (int)$arrField['Value'];
 					break;
 				
 				case 'Date':
 				case 'Time':
 					$strDate	= $arrField['Value'];
 					switch ($arrType[1])
 					{
 						case 'YYYYMMDD':
 							$strParse	= $strDate;
 							break;
 							
 						case 'YYYY-MM-DD':
 							$strParse	= $strDate;
 							break;
 						
 						case 'DD-MM-YYYY':
 						case 'DD/MM/YYYY':
 							$strParse	= substr($strDate, -4, 4);
 							$strParse	.= substr($strDate, 4, 2);
 							$strParse	.= substr($strDate, 0, 2);
 							break; 							
 						
 						case 'HHII':
 							$strParse	= date("Y-m-d");
 							$strParse	.= substr($strDate, 0, 2) . ":";
 							$strParse	.= substr($strDate, 2, 4) . ":";
 							$strParse	.= "00";
 							break;
 					}
 					
 					// Is it a valid date?
 					if (!strtotime($strParse))
 					{
						$strMessage	= "Request #{$arrLine['**Request']}; Field '$strField' with value '$strDate' is not a valid {$arrType[1]} date";
						return Array('Pass' => FALSE, 'Line' => $strMessage);
 					}
 					$mixValue	= $strDate;
 					break;
 				
 				default:
 					// String
 					$mixValue	= $arrField['Value'];
 					break;
 			}
 			
			// Is this fixed-width?
			if ($arrField['Length'])
			{
				if (($intLength = strlen($mixValue)) > $arrField['Length'])
				{
					// Field is too long, fail out
					$strMessage	= "Request #{$arrLine['**Request']}; Field '$strField' is $intLength chars, expected max {$arrField['Length']} chars";
					return Array('Pass' => FALSE, 'Line' => $strMessage);
				}
				else
				{
					// Pad the field
					$strValue	= str_pad($mixValue, $arrField['Length'], $arrField['PadChar'], $arrField['PadType']);
				}
			}
			
			// Render XLS line
			if ($bolRenderToFile)
			{
				if ($strStyle)
				{
					$this->_wksWorksheet->writeString($this->_intRow, $intCol, $mixValue, $this->_arrFormat[$strStyle]);
				}
				else
				{
					switch ($arrType[0])
					{
						case 'Integer':
							$this->_wksWorksheet->writeNumber($this->_intRow, $intCol, $mixValue, $this->_arrFormat['Integer']);
							break;
							
						case 'FNN':
							$this->_wksWorksheet->writeNumber($this->_intRow, $intCol, $mixValue, $this->_arrFormat['FNN']);
						
						default:
							$this->_wksWorksheet->writeString($this->_intRow, $intCol, $mixValue);
							break;
					}
				}
			}
			$intCol++;
 		}
 		$this->_intRow++;
 		
 		// Implode Plaintext line
 		$strLine		= implode(';', $arrOutput);
 		
 		return Array('Pass' => FALSE, 'Line' => $strLine);
 	}
	 
	
	//------------------------------------------------------------------------//
	// _Deliver
	//------------------------------------------------------------------------//
	/**
	 * _Deliver()
	 *
	 * Delivers this Request File to its Destination
	 *
	 * Delivers this Request File to its Destination
	 *
	 * @return	array					['Pass']	: TRUE/FALSE
	 * 									['Message']	: Optional Error Message					
	 *
	 * @method
	 */
	 protected function _Deliver()
	 {
	 	// Debug
	 	//return Array('Pass' => TRUE, 'Message' => "Delivery Bypassed");
	 	
	 	switch ($this->_strDeliveryType)
	 	{
	 		case 'FTP':
	 			$mixResult	= $this->_DeliverFTP();
	 			break;
	 		
	 		case 'EmailAttach':
	 			$mixResult	= $this->_DeliverEmailAttachment();
	 			break;
	 		
	 		case 'Email':
	 		case 'EmailText':
	 			$mixResult	= $this->_DeliverEmail();
	 			break;
	 	}
	 	
	 	// Update the FileExport and ProvisioningRequest tables
	 	$arrCols					= Array();
 		$arrCols['Id']				= $this->_intFileExport;
 		$arrCols['Status']			= ($mixResult['Pass']) ? FILE_DELIVERED : FILE_DELIVERY_FAILED;
 		$this->_ubiFileDelivered	= new StatementUpdateById("FileExport", $arrCols);
 		if ($this->_ubiFileDelivered->Execute($arrCols) === FALSE)
 		{
 			return Array('Pass' => FALSE, 'Description' => "Unable to update FileExport to Delivered Status");
 		}
 		else
 		{
	 		// Update ProvisioningRequest records
	 		$arrCols	= Array();
	 		$arrCols['Status']		= REQUEST_STATUS_DELIVERED;
	 		$arrCols['SentOn']		= new MySQLFunction("NOW()");
	 		$arrCols['FileExport']	= $this->_intFileExport;
	 		$ubiRequest				= new StatementUpdateById("ProvisioningRequest", $arrCols);
	 		foreach ($this->_arrFileContent as $arrRequest)
	 		{
	 			$arrCols['Id']	= $arrRequest['**Request'];
	 			$ubiRequest->Execute($arrCols);
	 		}
 		}
 		 		
		return Array('Pass' => TRUE, 'Description' => "File Successfully Delivered");
	 }
	
	
	//------------------------------------------------------------------------//
	// _DeliverFTP
	//------------------------------------------------------------------------//
	/**
	* _DeliverFTP()
	*
	* Delivers this Request File to its Destination FTP Server
	*
	* Delivers this Request File to its Destination FTP Server
	*
	* @return	array					['Pass']	: TRUE/FALSE
	* 									['Message']	: Optional Error Message					
	*
	* @method
	*/
	protected function _DeliverFTP()
	{
		if (PROVISIONING_DEBUG_MODE === TRUE)
		{
	 		return Array('Pass' => TRUE, 'Description' => "FTP Delivery Bypassed");
		}
		
		// Get Configuration
		$strServer	= $this->GetConfigField('Server');
		$strUser	= $this->GetConfigField('User');
		$strPass	= $this->GetConfigField('Password');
		$strPath	= $this->GetConfigField('Path');
		
		// Copy File
		if ($ptrConnection = ftp_connect($strServer))
		{
			if ($mixResult = ftp_login($ptrConnection, $strUser, $strPass))
			{
				if ($mixResult = ftp_chdir($ptrConnection, $strPath))
				{
					if ($mixResult = ftp_put($ptrConnection, basename($this->_strFilePath), $this->_strFilePath, FTP_BINARY))
					{
						$mixResult = ftp_close($ptrConnection);
					}
				}
			}
		}
		else
		{
			$mixResult	= FALSE;
		}
		
		// Return extended error messaging
		if ($mixResult === TRUE)
		{
			return Array('Pass' => TRUE,	'Description' => "Deliver() Successful");
		}
		else
		{
			return Array('Pass' => FALSE,	'Description' => "Remote Copy to FTP Server Failed ($mixResult)");
		}
	}
	
	
	//------------------------------------------------------------------------//
	// _DeliverEmail
	//------------------------------------------------------------------------//
	/**
	* _DeliverEmail()
	*
	* Delivers this Request Data to its Destination Email Address
	*
	* Delivers this Request Data to its Destination Email Address
	*
	* @return	array					['Pass']	: TRUE/FALSE
	* 									['Message']	: Optional Error Message					
	*
	* @method
	*/
	protected function _DeliverEmail()
	{	
		// Get Configuration
		$strEmailAddress	= $this->GetConfigField('Destination');
		$strSubject			= $this->GetConfigField('Subject');
		$strReplyTo			= $this->GetConfigField('ReplyTo');
		$strCC				= $this->GetConfigField('CarbonCopy');
		
		if ($strCC)
		{
			$strEmailAddress .= ', ' . $strCC;
		}
		
		if (PROVISIONING_DEBUG_MODE === TRUE)
		{
	 		$strEmailAddress	= "rich@voiptelsystems.com.au";
		}
		
		// Send Email
		$mixResult			= SendEmail($strEmailAddress, $strSubject, $this->_strFileContents, $strReplyTo);
		
		// Return extended error messaging
		if ($mixResult === TRUE)
		{
			return Array('Pass' => TRUE,	'Description' => "Deliver() Successful");
		}
		else
		{
			return Array('Pass' => FALSE,	'Description' => "Email could not be sent");
		}
	}
	
	
	//------------------------------------------------------------------------//
	// _DeliverEmailAttachment
	//------------------------------------------------------------------------//
	/**
	* _DeliverEmail()
	*
	* Delivers this Request File to its Destination Email Address
	*
	* Delivers this Request File to its Destination Email Address
	*
	* @return	array					['Pass']	: TRUE/FALSE
	* 									['Message']	: Optional Error Message					
	*
	* @method
	*/
	protected function _DeliverEmailAttachment()
	{
		// Get Configuration
		$strEmailAddress	= $this->GetConfigField('Destination');
		$strSubject			= $this->GetConfigField('Subject');
		$strReplyTo			= $this->GetConfigField('ReplyTo');
		$strCC				= $this->GetConfigField('CarbonCopy');
		
		if ($strCC)
		{
			$strEmailAddress .= ', ' . $strCC;
		}
		
		if (PROVISIONING_DEBUG_MODE === TRUE)
		{
	 		$strEmailAddress	= "rich@voiptelsystems.com.au";
		}
		
		// Set content
		$strEmailContent	= $this->GetConfigField('EmailContent');
		
		// Determine MIME Type
		switch ($this->_strFileFormat)
		{
			case 'CSV':
				$strMIME	= "text/csv";
				
			case 'XLS':
				$strMIME	= "application/excel";
		}
		
		// Send Email
		$arrHeaders = Array	(
								'From'		=> $strReplyTo,
								'Reply-To'	=> $strReplyTo,
								'Subject'	=> $strSubject
							);
		$mimMime = new Mail_mime("\n");
		$mimMime->setTXTBody($strEmailContent);
		$mimMime->addAttachment($this->_strFilePath, $strMIME);
		$strBody = $mimMime->get();
		$strHeaders = $mimMime->headers($arrHeaders);
		$emlMail =& Mail::factory('mail');
		$mixResult	= $emlMail->send($strEmailAddress, $strHeaders, $strBody);
		
		// Return extended error messaging
		if ($mixResult === TRUE)
		{
			return Array('Pass' => TRUE,	'Description' => "Deliver() Successful");
		}
		else
		{
			return Array('Pass' => FALSE,	'Description' => "Email could not be sent");
		}
	}
	
	//------------------------------------------------------------------------//
	// _InitExcelFormats
	//------------------------------------------------------------------------//
	/**
	 * _InitExcelFormats()
	 *
	 * Initialises Number Formats for Excel Export
	 *
	 * Initialises Number Formats for Excel Export
	 *
	 * @param	Spreadsheet_Excel_Writer	$wkbWorkbook	Workbook to create formats for
	 *
	 * @return	array										Associative Array of Formats
	 *
	 * @method
	 */
 	private function _InitExcelFormats($wkbWorkbook)
 	{		
 		$arrFormat = Array();
 		
 		// Integer format (make sure it doesn't show exponentials for large ints)
		$fmtInteger =& $wkbWorkbook->addFormat();
		$fmtInteger->setNumFormat('0');
		$arrFormat['Integer']		= $fmtInteger;
		
 		// Bold Integer format (make sure it doesn't show exponentials for large ints)
		$fmtIntegerBold =& $wkbWorkbook->addFormat();
		$fmtIntegerBold->setNumFormat('0');
		$fmtIntegerBold->SetBold();
		$arrFormat['IntegerBold']		= $fmtIntegerBold;
		
 		// Total Integer format (make sure it doesn't show exponentials for large ints)
		$fmtIntegerTotal =& $wkbWorkbook->addFormat();
		$fmtIntegerTotal->setNumFormat('0');
		$fmtIntegerTotal->setBold();
		$fmtIntegerTotal->setTopColor('black');
		$fmtIntegerTotal->setTop(1);
		$arrFormat['IntegerTotal']		= $fmtIntegerTotal;
		
		
		
		// Bold Text
		$fmtBold		= $wkbWorkbook->addFormat();
		$fmtBold->setBold();
		$arrFormat['TextBold']		= $fmtBold;
		
		// Title Row
		$fmtTitle =& $wkbWorkbook->addFormat();
		$fmtTitle->setBold();
		$fmtTitle->setFgColor(22);
		$fmtTitle->setBorder(1);
		$arrFormat['Title']			= $fmtTitle;
		
		// Total Text Cell
		$fmtTotalText	= $wkbWorkbook->addFormat();
		$fmtTotalText->setTopColor('black');
		$fmtTotalText->setTop(1);
		$fmtTotalText->setBold();
		$arrFormat['TotalText']		= $fmtTotalText;
		
		
		
		// Currency
		$fmtCurrency	= $wkbWorkbook->addFormat();
		$fmtCurrency->setNumFormat('$#,##0.00;$#,##0.00 CR');
		$arrFormat['Currency']		= $fmtCurrency;
		
		// Bold Currency
		$fmtCurrencyBold	= $wkbWorkbook->addFormat();
		$fmtCurrencyBold->setNumFormat('$#,##0.00;$#,##0.00 CR');
		$fmtCurrencyBold->setBold();
		$arrFormat['CurrencyBold']	= $fmtCurrencyBold;
		
		// Total Currency
		$fmtTotal		= $wkbWorkbook->addFormat();
		$fmtTotal->setNumFormat('$#,##0.00;$#,##0.00 CR');
		$fmtTotal->setBold();
		$fmtTotal->setTopColor('black');
		$fmtTotal->setTop(1);
		$arrFormat['CurrencyTotal']	= $fmtTotal;
		
		
		
		// Percentage
		$fmtPercentage	= $wkbWorkbook->addFormat();
		$fmtPercentage->setNumFormat('0.00%;[red]-0.00%');
		$arrFormat['Percentage']	= $fmtPercentage;
		
		// Bold Percentage
		$fmtPCBold		= $wkbWorkbook->addFormat();
		$fmtPCBold->setNumFormat('0.00%;-0.00%');
		$fmtPCBold->setBold();
		$arrFormat['PercentageBold']	= $fmtPCBold;
		
		// Total Percentage
		$fmtPCTotal		= $wkbWorkbook->addFormat();
		$fmtPCTotal->setNumFormat('0.00%;-0.00%');
		$fmtPCTotal->setBold();
		$fmtPCTotal->setTopColor('black');
		$fmtPCTotal->setTop(1);
		$arrFormat['PercentageTotal']	= $fmtPCTotal;
		
		
		
		// FNN
		$fmtFNN			= $wkbWorkbook->addFormat();
		$fmtFNN->setNumFormat('0000000000');
		$arrFormat['FNN']				= $fmtFNN;
		
		return $arrFormat; 		
 	}
 	
 	
 	//------------------------------------------------------------------------//
	// Export
	//------------------------------------------------------------------------//
	/**
	 * Export()
	 *
	 * Builds the output file/email for delivery to Carrier
	 *
	 * Builds the output file/email for delivery to Carrier
	 * 
	 * @return	array					'Pass'			: TRUE/FALSE/NULL (Skipped)
	 * 									'Description'	: Error message
	 *
	 * @method
	 */
 	function Export()
 	{
 		// Check to see if we have enough requests
	 	$bolReturn	= TRUE;
 		$mixResult	= NULL;
 		if (count($this->_arrFileContent) >= $this->_intMinRequests)
 		{
	 		// Render File
	 		$mixResult	= $this->_Render();
	 		if ($mixResult['Pass'])
	 		{
		 		// Update Requests & FileExport 
		 		$mixResult	= $this->_UpdateDB();
		 		if ($mixResult['Pass'])
		 		{
		 			// Deliver to FTP Server
			 		$mixResult	= $this->_Deliver();
			 		if ($mixResult['Pass'])
			 		{
			 			// Update the Configuration
				 		$mixResult	= $this->SaveModule();
			 		}
		 		}
	 		}
 		}
 		else
 		{
 			// Not enough Requests, SKIP
 			$mixResult	= Array('Pass' => NULL, 'Description' => "No Requests to Export");
 		}
 		
 		// Make sure this file doesn't get exported again
 		$this->bolExported	= TRUE;
 		
 		return $mixResult;
 	}
 	
 	
 	//------------------------------------------------------------------------//
	// _UpdateDB
	//------------------------------------------------------------------------//
	/**
	 * _UpdateDB()
	 *
	 * Updates the Request records and adds an entry to FileExport
	 *
	 * Updates the Request records and adds an entry to FileExport
	 * 
	 * @return	array					'Pass'			: TRUE/FALSE
	 * 									'Description'	: Error message
	 *
	 * @method
	 */
 	protected function _UpdateDB()
 	{
 		// Insert FileExport record
 		$arrFileExport	= Array();
 		$arrFileExport['FileName']		= basename($this->_strFilePath);
 		$arrFileExport['Location']		= $this->_strFilePath;
 		$arrFileExport['Carrier']		= $this->_intModuleCarrier;
 		$arrFileExport['ExportedOn']	= new MySQLFunction("NOW()");
 		$arrFileExport['Status']		= FILE_RENDERED;
 		$arrFileExport['FileType']		= $this->intBaseFileType;
 		$arrFileExport['SHA1']			= sha1_file($this->_strFilePath);
 		$insFileExport	= new StatementInsert("FileExport", $arrFileExport);
 		if (($intFileExport	= $insFileExport->Execute($arrFileExport)) === FALSE)
 		{
 			return Array('Pass' => FALSE, 'Description' => "Unable to create FileExport DB entry!");
 		}
 		
 		$this->_intFileExport	= $intFileExport;
 		
 		return Array('Pass' => TRUE, 'Description' => "UpdateDB() Successful");
 	}
 	
 	
 	//------------------------------------------------------------------------//
	// GetTypes
	//------------------------------------------------------------------------//
	/**
	 * GetTypes()
	 *
	 * Gets a list of the Provisioning Types this Output Module supports
	 *
	 * Gets a list of the Provisioning Types this Output Module supports
	 * 
	 * @return	array						Indexed array of Provisioning Types
	 * @method
	 */
 	function GetTypes()
 	{
 		$arrTypes	= Array();
 		
 		foreach ($this->_arrDefine as $mixType=>$arrProperties)
 		{
 			if (is_int($mixType))
 			{
 				$arrTypes[]	= $mixType;
 			}
 		}
 		
 		return $arrTypes;
 	}
}
?>