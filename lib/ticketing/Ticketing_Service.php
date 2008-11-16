<?php

// Ensure that the Zend folder (lib) is in the incoude path
//set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR));

// Load the Zend mail library (used for retrieving and parsing emails)
//require_once 'Zend/Mail.php';

// Load other required classes
require_once 'ticketing/Ticketing_Ticket.php';


function glog($str)
{
	$f = fopen('php://stdout', 'w');
	fwrite($f, $str . "\n");
	fclose($f);
}


class Ticketing_Service
{
	public static function loadEmails()
	{
		// Load the ticketing configuration
		$config = Ticketing_Config::load();

		switch(strtoupper($config->protocol))
		{
			case 'XML':
				$outcome = self::loadXmlFiles();
				break;

			default:
				$outcome = self::loadFromMailServer();
				break;
		}

		return $outcome;
	}

	public static function loadXmlFiles()
	{
		// Load the ticketing configuration
		$config = Ticketing_Config::load();

		// Get the source directory
		$strSourceDirectory = $config->getSourceDirectory();

		// Get the backup directory
		$strBackupDirectory = $config->getBackupDirectory();

		// Get the junk mail directory
		$strJunkDirectory = $config->getJunkDirectory();

		// Assume the dir is in the host setting
		$xmlFiles = glob($strSourceDirectory . '*.xml');

		foreach($xmlFiles as $xmlFile)
		{
			$correspondence = NULL;

			try
			{
				// Each email should be processed in its own db transaction,
				// as each email will be deleted separately
				$dbAccess = DataAccess::getDataAccess();
				$dbAccess->TransactionStart();

				// Parse the file
				$details = self::parseXmlFile($xmlFile);

				if ($details === FALSE)
				{
					continue;
				}

				// Check that there is a sender
				$correspondence = FALSE;
				if (array_key_exists('from', $details))
				{
					// Set delivery status to received (this is inbound)
					$details['delivery_status'] = TICKETING_CORRESPONDANCE_DELIVERY_STATUS_RECEIVED; //
	
					// XML files originate from emails
					$details['source_id'] = TICKETING_CORRESPONDANCE_SOURCE_EMAIL;
	
					// System user id
					//$details['user_id'] = USER_ID;
	
					// Set delivery time (to system) same as creation time (now)
					$details['delivery_datetime'] = $details['creation_datetime'] = date('Y-m-d H-i-s');
	
					// Load the details into the ticketing system
					$correspondence = Ticketing_Correspondance::createForDetails($details);
					// If a correspondence was created...
					if ($correspondence)
					{
						// Acknowledge receipt of the correspondence
						$correspondence->acknowledgeReceipt();
					}
				}

				// Determine whether we will be backing up files
				$bolBackup = $correspondence ? ($strBackupDirectory ? TRUE : FALSE) : ($strJunkDirectory ? TRUE : FALSE);
				$strMoveToDir = $correspondence ? $strBackupDirectory : $strJunkDirectory;

				$dbAccess->TransactionCommit();
			}
			catch (Exception $exception)
			{
				$dbAccess->TransactionRollback();
				throw $exception;
			}

			// Backup or remove files as required
			for ($i = 1; $i <= 2; $i++)
			{
				foreach ($details['files_to_remove'] as $path)
				{
					if (file_exists($path))
					{
						$strRealPath = realpath($path);

						// First run through we move / remove the files
						if ($i == 1 && is_file($strRealPath))
						{
							if ($bolBackup)
							{
								// Work out the location for the backup
								$newPath = str_replace($strSourceDirectory, $strMoveToDir, $strRealPath);
								// Ensure the directory exists
								self::mkdir(dirname($newPath));
								// Move the file to the new location
								rename($path, $newPath);
							}
							else
							{
								// We don't care about the file. Just remove it
								@unlink($path);
							}
						}

						// On the second pass we can remove any directories
						// (can't do this on the first pass as they may contain the files we are backing up)
						else if ($i == 2 && is_dir($strRealPath))
						{
							$baseDir = realpath($strSourceDirectory);
							while ($baseDir != $strRealPath && strpos($strRealPath, $baseDir) === 0)
							{
								@rmdir($strRealPath);
								$strRealPath = realpath(dirname($strRealPath));
							}
						}

					}
				}
			}

		}
	}

	private static function mkdir($path)
	{
		$parentDir = dirname($path);
		if (!file_exists($parentDir))
		{
			self::mkdir($parentDir);
		}
		if (!file_exists($path))
		{
			@mkdir($path);
		}
	}
	
	private static function cleanseXML($strXML)
	{
		$arrDodgyChars = array(
		//  Good char sequence => Bad char sequence
			'&#161;' => '&iexcl;',
			'&#224;' => '&agrave;',
			'&#170;' => '&ordf;',
			'&#225;' => '&aacute;',
			'&#186;' => '&ordm;',
			'&#226;' => '&acirc;',
			'&#191;' => '&iquest;',
			'&#232;' => '&egrave;',
			'&#199;' => '&Ccedil;',
			'&#233;' => '&eacute;',
			'&#209;' => '&Ntilde;',
			'&#234;' => '&ecirc;',
			'&#231;' => '&ccedil;',
			'&#238;' => '&icirc;',
			'&#237;' => '&iacute;',
			'&#242;' => '&ograve;',
			'&#241;' => '&ntilde;',
			'&#244;' => '&ocirc;',
			'&#243;' => '&oacute;',
			'&#251;' => '&ucirc;',
			'&#170;' => '&ordf;',
			'&#168;' => '&uml;',
			'&#186;' => '&ordm;',
			'&#196;' => '&Auml;',
			'&#192;' => '&Agrave;',
			'&#203;' => '&Euml;',
			'&#193;' => '&Aacute;',
			'&#214;' => '&Ouml;',
			'&#194;' => '&Acirc;',
			'&#220;' => '&Uuml;',
			'&#200;' => '&Egrave;',
			'&#228;' => '&auml;',
			'&#201;' => '&Eacute;',
			'&#235;' => '&euml;',
			'&#202;' => '&Ecirc;',
			'&#246;' => '&ouml;',
			'&#212;' => '&Ocirc;',
			'&#252;' => '&uuml;',		
			'&#160;' => '&nbsp;',	
			'&#163;' => '&pound;',	
			'&#169;' => '&copy;',	
		);
		
		$out = array();
		$in = array();
		foreach ($arrDodgyChars as $strIn => $strOut)
		{
			$in[] = $strIn;
			$out[] = '/' . preg_quote($strOut) . '/';
		}
		
		$strXML = preg_replace($out, $in, $strXML);
		
		return $strXML;
	}

	private static function parseXmlFile($xmlFilePath)
	{
		/* XML schema for email content
			<?xml version="1.0"?>
			<document>
				<timestamp>XXXX</timestamp>
				<subject>XXXX</subject>
				<from>
					<name>XXXX</name>
					<email>XXXX</email>
				</from>
				<tos>
					<to>
						<name>XXXX</name>
						<email>XXXX</email>
					</to>
				</tos>
				<ccs>
					<cc>
						<name>XXXX</name>
						<email>XXXX</email>
					</cc>
				</ccs>
				<body type="[text|html]">XXXX</body>
				<attachments>
					<file name="XXXX" type="XXXX">
						<data>
							XXXX
						</data>
					</file>
					<file name="XXXX" type="XXXX">
						<data>
							XXXX
						</data>
					</file>
				</attachments>
			</document>
		*/

		// Resolve to a real path (removing symbolics)
		$xmlFilePath = realpath($xmlFilePath);

		$xml = self::cleanseXML(file_get_contents($xmlFilePath));

		$dom = new DOMDocument();
		if (!$dom->loadXML($xml))
		{
			return false;
		}
		$details = array();

		$details['files_to_remove'] = array();
		$details['files_to_remove'][] = $xmlFilePath;

		$details['timestamp'] = $dom->getElementsByTagName('timestamp')->item(0)->textContent;

		$details['subject'] = $dom->getElementsByTagName('subject')->item(0)->textContent;

		$email = $dom->getElementsByTagName('from')->item(0);
		$defails['from'] = self::getEmailNameAndAddress($email);

		$details['to'] = array();
		$emails = $dom->getElementsByTagName('to');
		for ($x = 0; $x < $emails->length; $x++)
		{
			$email = $emails->item($x);
			$details['to'][] = self::getEmailNameAndAddress($email); 
		}

		$details['cc'] = array();
		$emails = $dom->getElementsByTagName('cc');
		for ($x = 0; $x < $emails->length; $x++)
		{
			$email = $emails->item($x);
			$details['cc'][] = self::getEmailNameAndAddress($email); 
		}

		$body = $dom->getElementsByTagName('body')->item(0);
		$details['message'] = $body->textContent;

		// Check to see if the message looks like it might be base64 encoded
		// If it contains no word spaces
		if (!preg_match("/[a-zA-Z0-9\+\/]+ +[a-zA-Z0-9\+\/]+/", trim($details['message'])))
		{
			// Get the message with all whitespace removed
			$sansWhiteSpace = preg_replace("/[\r\n\t ]*/", "", $details['message']);
			// If this has a multiple of 4 chars and only comprises base64 chars with either 0, 1 or 2 trailing '='
			if((strlen($sansWhiteSpace)%4 == 0) && preg_match("/^[a-zA-Z0-9\+\/]+[=]{0,2}$/", $sansWhiteSpace))
			{
				// Decode it
				$decoded = @base64_decode($sansWhiteSpace);
				if ($decoded)
				{
					$details['message'] = $decoded;
				}
			}
		}

		if (trim(strtolower($body->getAttribute('type'))) == 'html')
		{
			// De-html'ify the message
			$details['message'] = self::html2txt($details['message']);
		}

		$attachments = $dom->getElementsByTagName('file');
		$details['attachments'] = array();

		// Extract attachments that are included in the XML file
		for ($x = 0; $x < $attachments->length; $x++)
		{
			$attachment = $attachments->item($x);
			$data = $attachment->getElementsByTagName('data')->item(0);
			$details['attachments'][] = array(
				'name' => $attachment->getAttribute('name'),
				'type' => $attachment->getAttribute('type'),
				'data' => base64_decode(trim($data->textContent))
			);
		}

		// Check for attachments in an associated directory
		$attachmentDirPath = $xmlFilePath . '-attachments';
		if (file_exists($attachmentDirPath) && is_dir($attachmentDirPath))
		{
			$attachmentFiles = glob($attachmentDirPath . DIRECTORY_SEPARATOR . '*.*');
			foreach($attachmentFiles as $attachmentFile)
			{
				if (is_file($attachmentFile))
				{
					$details['attachments'][] = array(
						'name' => basename($attachmentFile),
						// TODO:: Replace mime_content_type (deprecated) with PECL FileInfo function
						'type' => mime_content_type($attachmentFile),
						'data' => file_get_contents($attachmentFile)
					);
					$details['files_to_remove'][] = $attachmentFile;
				}
			}
			$details['files_to_remove'][] = $attachmentDirPath;
		}

		return $details;
	}
	
	private function getEmailNameAndAddress($email)
	{
		$emailAddress = $email ? $email->getElementsByTagName('email')->item(0)->textContent : '';
		$emailAddress = trim($emailAddress);
		// "Margaret Munro "<magneticfx@iinet.net.au>;
		// "lenrhonda"<lenrhonda@westnet.com.au>;
		$name = array();
		if (preg_match("/^\"([^\"]*)\" *\</", $emailAddress, $name))
		{
			$name = $name[1];
			$emailAddress = trim(substr($emailAddress, strlen($name) + 2));
		}
		else
		{
			$name = false;
		}
		if (substr($emailAddress, 0, 1) == '<') $emailAddress = substr($emailAddress, 1);
		if (substr($emailAddress, -1) == '>') $emailAddress = substr($emailAddress, 0, -1);
		$details = array('name' => '', 'address' => '');
		if ($emailAddress && EmailAddressValid($emailAddress))
		{
			$details = array(
				'name' => trim($email->getElementsByTagName('name')->item(0)->textContent),
				'address' => $emailAddress,
			);
			if ($name && !$details['name'])
			{
				$details['name'] = $name;
			}
		}
		return $details;
	}

	private function html2txt($document)
	{
		$search = array("/\<script[^\>]*?\>.*?\<\/script\>/si",	// Strip out javascript
						"/\<style[^>]*?\>.*?\<\/style\>/siU",	// Strip style tags properly
						"/\<[\/\!]*?[^\<\>]*?>/si",			// Strip out HTML tags
						"/\<![\s\S]*?--[ \t\n\r]*\>/"			// Strip multi-line comments including CDATA
		);
		$text = preg_replace($search, '', $document);
		return $text;
	}

	public static function loadFromMailServer()
	{
		// This function has not been fully implemented!!
		// Requires email parsing!
		return FALSE;

		// Connect to the email storage
		$storage = self::getEmailStorage();

		// Find out how many emails there are
		$arrUniqueIds = $storage->getUniqueId();
		$nrMessages = count($arrUniqueIds);

//echo 'Nr. messages: ' . $nrMessages . "\n\n";

		if (!$nrMessages)
		{
			return 0;
		}

		foreach($arrUniqueIds as $idx => $strUniqueId)
		{
			$objMessage = $storage->getMessage($storage->getNumberByUniqueId($strUniqueId));
//echo 'Id: ' . $strUniqueId . '(' . $idx . ')' . "\n";

			// Process the email
			// Get the details of the email
			$strSubject = $objMessage->subject;
//echo 'Subject: ' . $strSubject . "\n";

			$arrSections = array();
			self::extractEmailParts($arrSections, $objMessage);

			$strMessage = '';
			//$strMessage = $objMessage->getBodyText(TRUE);
			if (!$strMessage)
			{
				//$strMessage = $objMessage->getBodyHtml(TRUE);
			}
//echo 'Message: ' . $strMessage . "\n";

			$arrAttachments = array();

			// we should create a Ticketing_Ticket_Message,
			$ticketingMessage = new Ticketing_Correspondance($strSubject, $strMessage, $arrAttachments);

//echo 'Ticket number: ' . $ticketingMessage->getTicketNumber() . "\n\n";

			// which we should save to the db 
			$ticketingMessage->save();

			// and send am acknowledging email for.
			$ticketingMessage->acknowledgeReceipt();

			// Remove it from the server
			//$storage->removeMessage($storage->getNumberByUniqueId($strUniqueId));
		}
	}

	private function extractEmailParts(&$arrEmailSections, $objMessagePart, $bolAlternative=FALSE)
	{
		// Find out if the message part is multipart
		if ($objMessagePart->isMultipart())
		{
			//echo "\n=========START========= MULTI  PART =========START========\n";
			var_dump($objMessagePart->getHeaders());
			//echo $objMessagePart->getContent();
			// For each part we need to extract the sub-parts
			foreach ($objMessagePart as $objChildMessagePart)
			{
				try 
				{
					//echo "\nxxLooking for content disposition\n";
					$strDisposition = $objMessagePart->ContentDisposition;
					//echo "\$strDisposition = $strDisposition\n\n";
				}
				catch(Exception $exception)
				{
					$strDisposition = "";
				}
				// Check out whether this is an 'alternative' section (i.e. We only need one section)
				$bolAlternative = FALSE;
				try 
				{
					$strType = $objMessagePart->ContentType;
					if (stripos('multipart/alternative', trim(strtolower($strType))) === 0)
					{
						$bolAlternative = TRUE;
					}
				}
				catch(Exception $exception)
				{
					$strType = "";
				}
				self::extractEmailParts($arrEmailSections, $objChildMessagePart, $bolAlternative);
			}
			//echo "\n==========END========== MULTI  PART ==========END=========\n";
		}
		else
		{
			// We have a single part.
			//echo "\n=========START========= SINGLE PART =========START========\n";

			// Check out whether or not this is 'inline' (part of the message body)
			// We need to look at the content type and the disposition to decide what to do with it
			try 
			{
				//echo "\nLooking for content disposition\n";
				$strDisposition = $objMessagePart->ContentDisposition;
				//echo "\$strDisposition = $strDisposition\n\n";
			}
			catch(Exception $exception)
			{
				$strDisposition = "inline";
			}

			try 
			{
				$strType = $objMessagePart->ContentType;
				//echo "\n\nsingle part type: $strType\n\n";
			}
			catch(Exception $exception)
			{
				$strType = "";
			}

			$content = $objMessagePart->getContent();

			$isText = stripos('text/', strtolower(trim($strType))) === 0;

			if ($isText)
			{
				if (stripos('text/html', strtolower(trim($strType))) === 0)
				{
					$content = strip_tags(preg_replace(array("/\<style.*style *\>/i", "/\<script.*script *\>/i"), '', $content));
				}
			}

			switch(strtolower(trim($strType)))
			{
				case "text/html";
			}

			//echo $objMessagePart->getContent();
			//echo "\n==========END========== SINGLE PART ==========END=========\n";
		}
	}

	private static function getEmailStorage()
	{
		// Load the ticketing configuration
		$config = Ticketing_Config::load();

		$storage = NULL;

		try
		{
			switch(strtoupper($config->protocol))
			{
				case 'POP3':
					// Connect to the POP3 mail server
					require_once 'Zend/Mail/Storage/Pop3.php';
					$storage = new Zend_Mail_Storage_Pop3(array('host'		=> $config->host,
																'user'		=> $config->username,
																'password'	=> $config->password,
																'port'		=> $config->port));
					break;

				case 'IMAP':
					// Connect to the IMAP mail server
					require_once 'Zend/Mail/Storage/Imap.php';
					$storage = new Zend_Mail_Storage_Imap(array('host'		=> $config->host,
																'user'		=> $config->username,
																'password'	=> $config->password,
																'port'		=> $config->port));
					break;

				case 'MBOX':
					// Connect to the MBox mail directory
					require_once 'Zend/Mail/Storage/Mbox.php';
					$storage = new Zend_Mail_Storage_Mbox(array('dirname'	=> $config->host));
					break;

				case 'MAILDIR':
					// Connect to the Maildir mail directory
					require_once 'Zend/Mail/Storage/Maildir.php';
					$storage = new Zend_Mail_Storage_Maildir(array('dirname'=> $config->host));
					break;

				default:
					throw new Exception('An unsupported mail protocol specified in the ticketing configuration: ' . $config->protocol);
			}
	
			if ($storage == NULL)
			{
				throw new Exception('Error unknown.');
			}
		}
		catch (Exception $exception)
		{
			throw new Exception('Failed to connect to mail storage: ' . $exception->getMessage());
		}

		return $storage;
	}
}

?>
