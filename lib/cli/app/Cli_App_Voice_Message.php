<?php

// Note: Suppress errors whilst loading application as there may well be some if the 
// database model files have not yet been generated.
$_SESSION = array();
// Load Flex.php
require_once(dirname(__FILE__) . "/../../../lib/classes/Flex.php");
Flex::load();

class Cli_App_Voice_Message extends Cli
{
	const SWITCH_OUTPUT_FILE_PATH_AND_NAME = "f";
	const SWITCH_ERROR_FILE_PATH_AND_NAME = "b";
	const SWITCH_ACCOUNT_ID_FILE = "a";
	const SWITCH_MESSAGE_FILE = "m";
	const SWITCH_ACCOUNT_ID_INDEX = "i";
	const SWITCH_SEND_EMAIL = "e";

	public function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			$arrBadNumbers = array();
			
			$message = $this->readMessageFromFile($arrArgs[self::SWITCH_MESSAGE_FILE]);

			$this->writeMessagesToFile(
										$this->filterPhoneNumbers(
											$this->getPhoneNumbersForAccountIds(
												$this->extractAccountIdsFromFile($arrArgs[self::SWITCH_ACCOUNT_ID_FILE], $arrArgs[self::SWITCH_ACCOUNT_ID_INDEX])
											), 
											$arrBadNumbers
										),
										$message,
										$arrArgs[self::SWITCH_OUTPUT_FILE_PATH_AND_NAME]
									);
									
			if ($arrArgs[self::SWITCH_ERROR_FILE_PATH_AND_NAME] !== null)
			{
				$file = $this->openFile($arrArgs[self::SWITCH_ERROR_FILE_PATH_AND_NAME]);
				fwrite($file, "AccountId,Phone\n");
				foreach($arrBadNumbers as $accountId => $phone)
				{
					fwrite($file, "$accountId,$phone\n");
				}
				fclose($file);
			}
			
			if ($arrArgs[self::SWITCH_SEND_EMAIL])
			{
				$emailNotification = new Email_Notification(EMAIL_NOTIFICATION_VOICE_MAIL);
				$emailNotification->setSubject("[SUCCESS] Voice message files for accounts in file: " . basename($arrArgs[self::SWITCH_ACCOUNT_ID_FILE]));
				$emailNotification->setBodyText("The attached file, '" . basename($arrArgs[self::SWITCH_OUTPUT_FILE_PATH_AND_NAME]) . "', contains voice messages." 
					. "\n\nAccount numbers were extracted from column " . $arrArgs[self::SWITCH_ACCOUNT_ID_INDEX] . " of file '" . basename($arrArgs[self::SWITCH_ACCOUNT_ID_FILE]) . "'." 
					. "\n\nThe voice message contained in this file reads:\n\n$message\n\n" 
					. (
						($arrArgs[self::SWITCH_ERROR_FILE_PATH_AND_NAME] !== null) 
							? ("NOTE: The other attached file, '" . basename($arrArgs[self::SWITCH_ERROR_FILE_PATH_AND_NAME]) . "', contains a list of phone numbers and accounts to which voice messages could not be sent.\n\n") 
							: ""
					  )
				);

				$emailNotification->addAttachment(file_get_contents($arrArgs[self::SWITCH_OUTPUT_FILE_PATH_AND_NAME]), basename($arrArgs[self::SWITCH_OUTPUT_FILE_PATH_AND_NAME]), "application/octet-stream; " . basename($arrArgs[self::SWITCH_OUTPUT_FILE_PATH_AND_NAME]));

				if ($arrArgs[self::SWITCH_ERROR_FILE_PATH_AND_NAME] !== null)
				{
					$emailNotification->addAttachment(file_get_contents($arrArgs[self::SWITCH_ERROR_FILE_PATH_AND_NAME]), basename($arrArgs[self::SWITCH_ERROR_FILE_PATH_AND_NAME]), "application/octet-stream; " . basename($arrArgs[self::SWITCH_ERROR_FILE_PATH_AND_NAME]));
				}

				try
				{
					$emailNotification->send();
					return TRUE;
				}
				catch(Exception $exception)
				{
					throw new Exception("Failed to send files via email: " . $exception->getMessage());
				}
			}
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());
		}
	}
	
	public function readMessageFromFile($messageFilePath)
	{
		$contents = file_get_contents($messageFilePath);
		$contents = str_replace("\n", "", $contents);
		$contents = str_replace("\r", "", $contents);
		return $contents;
	}
	
	public function filterPhoneNumbers($arrPhoneNumbers, &$arrBadNumbers)
	{
		$arrGoodNumbers = array();
		foreach ($arrPhoneNumbers as $accountId => $phone)
		{
			//$this->log("Chacking validity of phone number '$phone' for account id '$accountId'");
			$phoneNumber = $this->validifyPhoneNumber($phone);
			
			if ($phoneNumber === false)
			{
				$arrBadNumbers[$accountId] = $phone;
			}
			else
			{
				$arrGoodNumbers[] = $phoneNumber;
			}
		}
		asort($arrGoodNumbers);
		$this->log("Good numbers: " . count($arrGoodNumbers));
		$this->log("Bad numbers: " . count($arrBadNumbers));
		return $arrGoodNumbers;
	}
	
	public function writeMessagesToFile($arrPhoneNumbers, $strMessage, $strOutputFilePath)
	{
		$file = $this->openFile($strOutputFilePath);
		
		$contents = implode($arrPhoneNumbers, ',' . $strMessage . "\n") . ',' . $strMessage;
		
		fwrite($file, $contents);
		
		fclose($file);
	}
	
	public function validifyPhoneNumber($strPhoneNumber)
	{
		$nr = preg_replace("/[^0-9]+/", '', $strPhoneNumber);
		
		$l = strlen($nr);
		
		$crap = false;
		
		if ($l > 10 || $l < 9)
		{
			$crap = true;
		}
		if ($l == 9)
		{
			$nr = '0' . $nr;
			$l = 10;
		}
		if ($nr[0] != '0')
		{
			$crap = true;
		}
		if (!$crap && $nr[1] == '4')
		{
			$crap = true;
		}
		
		return $crap ? false : $nr;
	}
	
	public function openFile($path)
	{
		$arrDirsToCreate = array();
		$directory = dirname($path);
		while (!file_exists($directory))
		{
			array_shift($arrDirsToCreate, $directory);
			$directory = dirname($directory);
		}
		foreach ($arrDirsToCreate as $directory)
		{
			mkdir($directory, 0777, TRUE);
		}
		return fopen($path, 'w');
	}
	
	public function extractAccountIdsFromFile($accountFilePath, $intIndex=0)
	{
		$contents = trim(file_get_contents($accountFilePath));
		$val = "([0-9]+)";
		$cols = $intIndex ? str_repeat("(?:[^,]*,)", $intIndex) : '';
		$reg = "/^$cols$val/m";
		$matches = array();
		preg_match_all($reg, $contents, $matches);
		$accountIds = $matches[1];
		$accountIds = array_unique($accountIds);
		$this->log("Nr account ids found: " . count($accountIds));
		return $accountIds;
	}

	public function getPhoneNumbersForAccountIds($arrAccountIds)
	{
		$strSQL = "
SELECT Contact.Phone AS Phone, Account.Id AS AccountId
FROM Account, Contact
WHERE Account.PrimaryContact = Contact.Id
  AND
Account.Id IN (" . implode(',', $arrAccountIds) . ")
";
		
		$this->log("SQL Query:\n\n" . $strSQL . "\n\n");
		
		$db = Data_Source::get();
		$res = $db->query($strSQL);
		if (PEAR::isError($res))
		{
			$this->log("\n\n$strSQL\n\n");
			throw new Exception("Failed to load contact details for barring: " . $res->getMessage());
		}
		
		$results = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
		
		$accountPhoneNumbers = array();
		
		foreach ($results as $result)
		{
			$accountPhoneNumbers[$result['AccountId']] = $result['Phone'];
		}
		$this->log("Nr account id / phone numbers found: " . count($accountPhoneNumbers));
		
		return $accountPhoneNumbers;
	}



	function getCommandLineArguments()
	{
		$commandLineArguments = array(

			self::SWITCH_ACCOUNT_ID_FILE => array(
				self::ARG_LABEL 		=> "ACCOUNT_ID_FILE", 
				self::ARG_REQUIRED 	=> TRUE,
				self::ARG_DESCRIPTION => "is the full path of a readable csv file containing account ids (default column index of account id values is 0)",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validFile("%1$s", TRUE)'
			),

			self::SWITCH_ACCOUNT_ID_INDEX => array(
				self::ARG_LABEL 		=> "ACCOUNT_ID_INDEX", 
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "is the column index of the account id value in the csv file (default is at the start of the line, i.e. 0)",
				self::ARG_DEFAULT 	=> 0,
				self::ARG_VALIDATION 	=> 'Cli::_validInteger("%1$s")'
			),

			self::SWITCH_MESSAGE_FILE => array(
				self::ARG_LABEL 		=> "MESSAGE_FILE", 
				self::ARG_REQUIRED 	=> TRUE,
				self::ARG_DESCRIPTION => "is the full path of a readable file containing a single, comma-less line of text",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validFile("%1$s", TRUE)'
			),

			self::SWITCH_OUTPUT_FILE_PATH_AND_NAME => array(
				self::ARG_LABEL 		=> "OUTPUT_FILE", 
				self::ARG_REQUIRED 	=> TRUE,
				self::ARG_DESCRIPTION => "is the full path of a writable file (if the path and/or file do not exist they will be created, otherwise overwritten)",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validWritableFileOrDirectory("%1$s", TRUE)'
			),

			self::SWITCH_ERROR_FILE_PATH_AND_NAME => array(
				self::ARG_LABEL 		=> "BAD_NUMBERS_FILE", 
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "is the full path of a writable file (if the path and/or file do not exist they will be created, otherwise overwritten)",
				self::ARG_DEFAULT 	=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validWritableFileOrDirectory("%1$s", TRUE)'
			),

			self::SWITCH_SEND_EMAIL => array(
				self::ARG_REQUIRED 	=> FALSE,
				self::ARG_DESCRIPTION => "whether or not to email the generated files",
				self::ARG_DEFAULT 	=> FALSE,
				self::ARG_VALIDATION 	=> 'Cli::_validIsSet()'
			),
			
		);
		return $commandLineArguments;
	}
}



?>
