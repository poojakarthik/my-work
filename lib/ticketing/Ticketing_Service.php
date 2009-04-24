<?php

// Ensure that the Zend folder (lib) is in the incoude path
//set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR));

// Load the Zend mail library (used for retrieving and parsing emails)
//require_once 'Zend/Mail.php';

// Load other required classes
require_once dirname(__FILE__).'/Ticketing_Ticket.php';


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
			'&#193;' => '&Aacute;', // latin capital letter A with acute
			'&#225;' => '&aacute;', // latin small letter a with acute
			'&#226;' => '&acirc;', // latin small letter a with circumflex
			'&#194;' => '&Acirc;', // latin capital letter A with circumflex
			'&#180;' => '&acute;', // acute accent
			'&#230;' => '&aelig;', // latin small letter ae
			'&#198;' => '&AElig;', // latin capital letter AE
			'&#192;' => '&Agrave;', // latin capital letter A with grave
			'&#224;' => '&agrave;', // latin small letter a with grave
			'&#8501;' => '&alefsym;', // alef symbol
			'&#913;' => '&Alpha;', // greek capital letter alpha
			'&#945;' => '&alpha;', // greek small letter alpha
			'&#38;' => '&amp;', // ampersand
			'&#8743;' => '&and;', // logical and
			'&#8736;' => '&ang;', // angle
			'&#229;' => '&aring;', // latin small letter a with ring above
			'&#197;' => '&Aring;', // latin capital letter A with ring above
			'&#8776;' => '&asymp;', // almost equal to
			'&#195;' => '&Atilde;', // latin capital letter A with tilde
			'&#227;' => '&atilde;', // latin small letter a with tilde
			'&#196;' => '&Auml;', // latin capital letter A with diaeresis
			'&#228;' => '&auml;', // latin small letter a with diaeresis
			'&#8222;' => '&bdquo;', // double low-9 quotation mark
			'&#914;' => '&Beta;', // greek capital letter beta
			'&#946;' => '&beta;', // greek small letter beta
			'&#166;' => '&brvbar;', // broken bar
			'&#8226;' => '&bull;', // bullet
			'&#8745;' => '&cap;', // intersection
			'&#199;' => '&Ccedil;', // latin capital letter C with cedilla
			'&#231;' => '&ccedil;', // latin small letter c with cedilla
			'&#184;' => '&cedil;', // cedilla
			'&#162;' => '&cent;', // cent sign
			'&#967;' => '&chi;', // greek small letter chi
			'&#935;' => '&Chi;', // greek capital letter chi
			'&#710;' => '&circ;', // modifier letter circumflex accent
			'&#9827;' => '&clubs;', // black club suit
			'&#8773;' => '&cong;', // approximately equal to
			'&#169;' => '&copy;', // copyright sign
			'&#8629;' => '&crarr;', // downwards arrow with corner leftwards
			'&#8746;' => '&cup;', // union
			'&#164;' => '&curren;', // currency sign
			'&#8224;' => '&dagger;', // dagger
			'&#8225;' => '&Dagger;', // double dagger
			'&#8659;' => '&dArr;', // downwards double arrow
			'&#8595;' => '&darr;', // downwards arrow
			'&#176;' => '&deg;', // degree sign
			'&#916;' => '&Delta;', // greek capital letter delta
			'&#948;' => '&delta;', // greek small letter delta
			'&#9830;' => '&diams;', // black diamond suit
			'&#247;' => '&divide;', // division sign
			'&#233;' => '&eacute;', // latin small letter e with acute
			'&#201;' => '&Eacute;', // latin capital letter E with acute
			'&#202;' => '&Ecirc;', // latin capital letter E with circumflex
			'&#234;' => '&ecirc;', // latin small letter e with circumflex
			'&#232;' => '&egrave;', // latin small letter e with grave
			'&#200;' => '&Egrave;', // latin capital letter E with grave
			'&#8709;' => '&empty;', // empty set
			'&#8195;' => '&emsp;', // em space
			'&#8194;' => '&ensp;', // en space
			'&#949;' => '&epsilon;', // greek small letter epsilon
			'&#917;' => '&Epsilon;', // greek capital letter epsilon
			'&#8801;' => '&equiv;', // identical to
			'&#919;' => '&Eta;', // greek capital letter eta
			'&#951;' => '&eta;', // greek small letter eta
			'&#240;' => '&eth;', // latin small letter eth
			'&#208;' => '&ETH;', // latin capital letter ETH
			'&#235;' => '&euml;', // latin small letter e with diaeresis
			'&#203;' => '&Euml;', // latin capital letter E with diaeresis
			'&#8364;' => '&euro;', // euro sign
			'&#8707;' => '&exist;', // there exists
			'&#402;' => '&fnof;', // latin small f with hook
			'&#8704;' => '&forall;', // for all
			'&#189;' => '&frac12;', // vulgar fraction one half
			'&#188;' => '&frac14;', // vulgar fraction one quarter
			'&#190;' => '&frac34;', // vulgar fraction three quarters
			'&#8260;' => '&frasl;', // fraction slash
			'&#915;' => '&Gamma;', // greek capital letter gamma
			'&#947;' => '&gamma;', // greek small letter gamma
			'&#8805;' => '&ge;', // greater-than or equal to
			'&#62;' => '&gt;', // greater-than sign
			'&#8660;' => '&hArr;', // left right double arrow
			'&#8596;' => '&harr;', // left right arrow
			'&#9829;' => '&hearts;', // black heart suit
			'&#8230;' => '&hellip;', // horizontal ellipsis
			'&#237;' => '&iacute;', // latin small letter i with acute
			'&#205;' => '&Iacute;', // latin capital letter I with acute
			'&#238;' => '&icirc;', // latin small letter i with circumflex
			'&#206;' => '&Icirc;', // latin capital letter I with circumflex
			'&#161;' => '&iexcl;', // inverted exclamation mark
			'&#204;' => '&Igrave;', // latin capital letter I with grave
			'&#236;' => '&igrave;', // latin small letter i with grave
			'&#8465;' => '&image;', // blackletter capital I
			'&#8734;' => '&infin;', // infinity
			'&#8747;' => '&int;', // integral
			'&#921;' => '&Iota;', // greek capital letter iota
			'&#953;' => '&iota;', // greek small letter iota
			'&#191;' => '&iquest;', // inverted question mark
			'&#8712;' => '&isin;', // element of
			'&#207;' => '&Iuml;', // latin capital letter I with diaeresis
			'&#239;' => '&iuml;', // latin small letter i with diaeresis
			'&#922;' => '&Kappa;', // greek capital letter kappa
			'&#954;' => '&kappa;', // greek small letter kappa
			'&#955;' => '&lambda;', // greek small letter lambda
			'&#923;' => '&Lambda;', // greek capital letter lambda
			'&#9001;' => '&lang;', // left-pointing angle bracket
			'&#171;' => '&laquo;', // left-pointing double angle quotation mark
			'&#8592;' => '&larr;', // leftwards arrow
			'&#8656;' => '&lArr;', // leftwards double arrow
			'&#8968;' => '&lceil;', // left ceiling
			'&#8220;' => '&ldquo;', // left double quotation mark
			'&#8804;' => '&le;', // less-than or equal to
			'&#8970;' => '&lfloor;', // left floor
			'&#8727;' => '&lowast;', // asterisk operator
			'&#9674;' => '&loz;', // lozenge
			'&#8206;' => '&lrm;', // left-to-right mark
			'&#8249;' => '&lsaquo;', // single left-pointing angle quotation mark
			'&#8216;' => '&lsquo;', // left single quotation mark
			'&#60;' => '&lt;', // less-than sign
			'&#175;' => '&macr;', // macron
			'&#8212;' => '&mdash;', // em dash
			'&#181;' => '&micro;', // micro sign
			'&#183;' => '&middot;', // middle dot
			'&#8722;' => '&minus;', // minus sign
			'&#924;' => '&Mu;', // greek capital letter mu
			'&#956;' => '&mu;', // greek small letter mu
			'&#8711;' => '&nabla;', // nabla
			'&#160;' => '&nbsp;', // no-break space
			'&#8211;' => '&ndash;', // en dash
			'&#8800;' => '&ne;', // not equal to
			'&#8715;' => '&ni;', // contains as member
			'&#172;' => '&not;', // not sign
			'&#8713;' => '&notin;', // not an element of
			'&#8836;' => '&nsub;', // not a subset of
			'&#241;' => '&ntilde;', // latin small letter n with tilde
			'&#209;' => '&Ntilde;', // latin capital letter N with tilde
			'&#925;' => '&Nu;', // greek capital letter nu
			'&#957;' => '&nu;', // greek small letter nu
			'&#243;' => '&oacute;', // latin small letter o with acute
			'&#211;' => '&Oacute;', // latin capital letter O with acute
			'&#212;' => '&Ocirc;', // latin capital letter O with circumflex
			'&#244;' => '&ocirc;', // latin small letter o with circumflex
			'&#338;' => '&OElig;', // latin capital ligature OE
			'&#339;' => '&oelig;', // latin small ligature oe
			'&#242;' => '&ograve;', // latin small letter o with grave
			'&#210;' => '&Ograve;', // latin capital letter O with grave
			'&#8254;' => '&oline;', // overline
			'&#969;' => '&omega;', // greek small letter omega
			'&#937;' => '&Omega;', // greek capital letter omega
			'&#927;' => '&Omicron;', // greek capital letter omicron
			'&#959;' => '&omicron;', // greek small letter omicron
			'&#8853;' => '&oplus;', // circled plus
			'&#8744;' => '&or;', // logical or
			'&#170;' => '&ordf;', // feminine ordinal indicator
			'&#186;' => '&ordm;', // masculine ordinal indicator
			'&#216;' => '&Oslash;', // latin capital letter O with stroke
			'&#248;' => '&oslash;', // latin small letter o with stroke
			'&#213;' => '&Otilde;', // latin capital letter O with tilde
			'&#245;' => '&otilde;', // latin small letter o with tilde
			'&#8855;' => '&otimes;', // circled times
			'&#214;' => '&Ouml;', // latin capital letter O with diaeresis
			'&#246;' => '&ouml;', // latin small letter o with diaeresis
			'&#182;' => '&para;', // pilcrow sign
			'&#8706;' => '&part;', // partial differential
			'&#8240;' => '&permil;', // per mille sign
			'&#8869;' => '&perp;', // up tack
			'&#966;' => '&phi;', // greek small letter phi
			'&#934;' => '&Phi;', // greek capital letter phi
			'&#928;' => '&Pi;', // greek capital letter pi
			'&#960;' => '&pi;', // greek small letter pi
			'&#982;' => '&piv;', // greek pi symbol
			'&#177;' => '&plusmn;', // plus-minus sign
			'&#163;' => '&pound;', // pound sign
			'&#8243;' => '&Prime;', // double prime
			'&#8242;' => '&prime;', // prime
			'&#8719;' => '&prod;', // n-ary product
			'&#8733;' => '&prop;', // proportional to
			'&#968;' => '&psi;', // greek small letter psi
			'&#936;' => '&Psi;', // greek capital letter psi
			'&#936;' => '&Psi;', // greek capital letter psi
			'&#8730;' => '&radic;', // square root
			'&#9002;' => '&rang;', // right-pointing angle bracket
			'&#187;' => '&raquo;', // right-pointing double angle quotation mark
			'&#8658;' => '&rArr;', // rightwards double arrow
			'&#8594;' => '&rarr;', // rightwards arrow
			'&#8969;' => '&rceil;', // right ceiling
			'&#8221;' => '&rdquo;', // right double quotation mark
			'&#8476;' => '&real;', // blackletter capital R
			'&#174;' => '&reg;', // registered sign
			'&#8971;' => '&rfloor;', // right floor
			'&#929;' => '&Rho;', // greek capital letter rho
			'&#961;' => '&rho;', // greek small letter rho
			'&#8207;' => '&rlm;', // right-to-left mark
			'&#8250;' => '&rsaquo;', // single right-pointing angle quotation mark
			'&#8217;' => '&rsquo;', // right single quotation mark
			'&#8218;' => '&sbquo;', // single low-9 quotation mark
			'&#352;' => '&Scaron;', // latin capital letter S with caron
			'&#353;' => '&scaron;', // latin small letter s with caron
			'&#8901;' => '&sdot;', // dot operator
			'&#167;' => '&sect;', // section sign
			'&#173;' => '&shy;', // soft hyphen
			'&#931;' => '&Sigma;', // greek capital letter sigma
			'&#963;' => '&sigma;', // greek small letter sigma
			'&#962;' => '&sigmaf;', // greek small letter final sigma
			'&#8764;' => '&sim;', // tilde operator
			'&#9824;' => '&spades;', // black spade suit
			'&#8834;' => '&sub;', // subset of
			'&#8838;' => '&sube;', // subset of or equal to
			'&#8721;' => '&sum;', // n-ary sumation
			'&#8835;' => '&sup;', // superset of
			'&#185;' => '&sup1;', // superscript one
			'&#178;' => '&sup2;', // superscript two
			'&#179;' => '&sup3;', // superscript three
			'&#8839;' => '&supe;', // superset of or equal to
			'&#223;' => '&szlig;', // latin small letter sharp s
			'&#932;' => '&Tau;', // greek capital letter tau
			'&#964;' => '&tau;', // greek small letter tau
			'&#8756;' => '&there4;', // therefore
			'&#920;' => '&Theta;', // greek capital letter theta
			'&#952;' => '&theta;', // greek small letter theta
			'&#977;' => '&thetasym;', // greek small letter theta symbol
			'&#8201;' => '&thinsp;', // thin space
			'&#222;' => '&THORN;', // latin capital letter THORN
			'&#254;' => '&thorn;', // latin small letter thorn with
			'&#732;' => '&tilde;', // small tilde
			'&#215;' => '&times;', // multiplication sign
			'&#8482;' => '&trade;', // trade mark sign
			'&#250;' => '&uacute;', // latin small letter u with acute
			'&#218;' => '&Uacute;', // latin capital letter U with acute
			'&#8657;' => '&uArr;', // upwards double arrow
			'&#8593;' => '&uarr;', // upwards arrow
			'&#251;' => '&ucirc;', // latin small letter u with circumflex
			'&#219;' => '&Ucirc;', // latin capital letter U with circumflex
			'&#217;' => '&Ugrave;', // latin capital letter U with grave
			'&#249;' => '&ugrave;', // latin small letter u with grave
			'&#168;' => '&uml;', // diaeresis
			'&#978;' => '&upsih;', // greek upsilon with hook symbol
			'&#965;' => '&upsilon;', // greek small letter upsilon
			'&#933;' => '&Upsilon;', // greek capital letter upsilon
			'&#252;' => '&uuml;', // latin small letter u with diaeresis
			'&#220;' => '&Uuml;', // latin capital letter U with diaeresis
			'&#8472;' => '&weierp;', // script capital P
			'&#958;' => '&xi;', // greek small letter xi
			'&#926;' => '&Xi;', // greek capital letter xi
			'&#253;' => '&yacute;', // latin small letter y with acute
			'&#221;' => '&Yacute;', // latin capital letter Y with acute
			'&#165;' => '&yen;', // yen sign
			'&#255;' => '&yuml;', // latin small letter y with diaeresis
			'&#376;' => '&Yuml;', // latin capital letter Y with diaeresis
			'&#918;' => '&Zeta;', // greek capital letter zeta
			'&#950;' => '&zeta;', // greek small letter zeta
			'&#8205;' => '&zwj;', // zero width joiner
			'&#8204;' => '&zwnj;', // zero width non-joiner
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
		$details['from'] = self::getEmailNameAndAddress($email);

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
