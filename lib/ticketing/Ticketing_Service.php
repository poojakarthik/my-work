<?php

// Ensure that the Zend folder (lib) is in the incoude path
//set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR));

// Load the Zend mail library (used for retrieving and parsing emails)
//require_once 'Zend/Mail.php';

// Load other required classes
require_once 'ticketing/Ticketing_Ticket.php';


class Ticketing_Service
{
	public static function loadXmlFiles()
	{
		// Load the ticketing configuration
		$config = Ticketing_Config::load();

		// Assume the dir is in the host setting
		$xmlFiles = scandir($config['host'] . DIRECTORY_SEPARATOR . '/*.xml');

		foreach($xmlFiles as $xmlFile)
		{
			// Parse the file
			$details = self::parseXmlFile($xmlFile);

			// Load the details into the ticketing system
			$correspondance = new Ticketing_Correspondance($details);

			// Remove the XML file so that we don't pick it up again
			unlink($xmlFile);
		}
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

		$dom = new DOMDocument();
		$dom->load($xmlFilePath);
		$details = array();

		$details['timestamp'] = $dom->getElementsByTagName('timestamp')->item(0)->textContent;

		$details['subject'] = $dom->getElementsByTagName('subject')->item(0)->textContent;

		$email = $dom->getElementsByTagName('from')->item(0);
		$details['from'] = array(
			'name' => $email->getElementsByTagName('name')->item(0)->textContent,
			'address' => $email->getElementsByTagName('address')->item(0)->textContent,
		);
		

		$details['to'] = array();
		$emails = $dom->getElementsByTagName('to');
		for ($x = 0; $x < $emails->length; $x++)
		{
			$email = $emails->item($x);
			$details['to'][] = array(
				'name' => $email->getElementsByTagName('name')->item(0)->textContent,
				'address' => $email->getElementsByTagName('address')->item(0)->textContent,
			);
		}

		$details['cc'] = array();
		$emails = $dom->getElementsByTagName('from');
		for ($x = 0; $x < $emails->length; $x++)
		{
			$email = $emails->item($x);
			$details['cc'][] = array(
				'name' => $email->getElementsByTagName('name')->item(0)->textContent,
				'address' => $email->getElementsByTagName('address')->item(0)->textContent,
			);
		}

		$body = $dom->getElementsByTagName('body')->item(0);
		$details['message'] = $body->textContent;
		if (trim(strtolower($body->getAttribute('type'))) == 'html')
		{
			// De-html'ify the message
			$details['message'] = self::html2txt($details['message']);
		}

		$attachments = $dom->getElementsByTagName('file');
		$details['attachments'] = array();
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
		
		return $details;
	}

	private function html2txt($document)
	{
		$search = array("/<script[^>]*?>.*?</script>/si",	// Strip out javascript
						"/<style[^>]*?>.*?</style>/siU",	// Strip style tags properly
						"/<[\/\!]*?[^<>]*?>/si",			// Strip out HTML tags
						"/<![\s\S]*?--[ \t\n\r]*>/"			// Strip multi-line comments including CDATA
		);
		$text = preg_replace($search, '', $document);
		return $text;
	}

	public static function loadEmails()
	{
		// This function has not been fully implemented!!
		// Requires email parsing!
		return NULL;

		// Connect to the email storage
		$storage = self::getEmailStorage();

		// Find out how many emails there are
		$arrUniqueIds = $storage->getUniqueId();
		$nrMessages = count($arrUniqueIds);

echo 'Nr. messages: ' . $nrMessages . "\n\n";

		if (!$nrMessages)
		{
			return 0;
		}

		foreach($arrUniqueIds as $idx => $strUniqueId)
		{
			$objMessage = $storage->getMessage($storage->getNumberByUniqueId($strUniqueId));
echo 'Id: ' . $strUniqueId . '(' . $idx . ')' . "\n";

			// Process the email
			// Get the details of the email
			$strSubject = $objMessage->subject;
echo 'Subject: ' . $strSubject . "\n";

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

echo 'Ticket number: ' . $ticketingMessage->getTicketNumber() . "\n\n";

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
			echo "\n=========START========= MULTI  PART =========START========\n";
			var_dump($objMessagePart->getHeaders());
			//echo $objMessagePart->getContent();
			// For each part we need to extract the sub-parts
			foreach ($objMessagePart as $objChildMessagePart)
			{
				try 
				{
					echo "\nxxLooking for content disposition\n";
					$strDisposition = $objMessagePart->ContentDisposition;
					echo "\$strDisposition = $strDisposition\n\n";
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
			echo "\n==========END========== MULTI  PART ==========END=========\n";
		}
		else
		{
			// We have a single part.
			echo "\n=========START========= SINGLE PART =========START========\n";

			// Check out whether or not this is 'inline' (part of the message body)
			// We need to look at the content type and the disposition to decide what to do with it
			try 
			{
				echo "\nLooking for content disposition\n";
				$strDisposition = $objMessagePart->ContentDisposition;
				echo "\$strDisposition = $strDisposition\n\n";
			}
			catch(Exception $exception)
			{
				$strDisposition = "inline";
			}

			try 
			{
				$strType = $objMessagePart->ContentType;
				echo "\n\nsingle part type: $strType\n\n";
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
			echo "\n==========END========== SINGLE PART ==========END=========\n";
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
