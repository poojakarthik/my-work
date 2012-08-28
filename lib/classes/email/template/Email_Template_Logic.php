<?php
// NOTE: This doesn't actually represent an Email Template.  Instead, it represents an Email Template-Customer Group relationship
class Email_Template_Logic {
	const READ = 1;
	const EDIT = 2;
	const CREATE = 3;

	protected $_oEmailTemplate = null;
	//protected $_aVariables = array();

	protected static $_aReport = array(
		'javascript',
		'events',
		'form',
		'input',
		'link',
		'style',
		'head'
	);

	public function __construct($oEmailTemplate, $oEmailTemplateDetails=null) {
		$this->_oEmailTemplate = $oEmailTemplate;
		
		if (!$oEmailTemplateDetails) {
			$oEmailTemplateDetails = Email_Template_Details::getCurrentDetailsForTemplateId($this->_oEmailTemplate->id);
			/* TODO: Damnit -- can't do this here, because the Logic is expected to be able to be created without details
			if (!$oEmailTemplateDetails) {
				throw new Exception("Email Template {$this->_oEmailTemplate->email_template_id} does not have a template for Customer Group {$this->_oEmailTemplate->customer_group_id}");
			}*/
		}
		
		$this->oDetails = $oEmailTemplateDetails;
	}

	// getHTMLContent: Return the 'ready-to-send' HTML content for the given array of data
	public function getHTMLContent($mData) {
		$aData = self::_getArrayFromData($mData);
		$sHTML = $this->oDetails->email_html;
		$sHTML = self::processHTML($sHTML);
		
		if ($sHTML === null || $sHTML === '') {
			return '';
		}
		
		$oDOMDocument = DomDocument::loadXML('<?xml version="1.0" encoding="utf-8"?>'.$sHTML);
		$oXPath = new DOMXPath($oDOMDocument);
		$oTags = $oXPath->query("//*[@*] | //variable");
		foreach ($oTags as $node) {
			if ($node->tagName =='variable') {
				$sObject = null;
				$sField = null;
				if ($node->hasAttribute('object')) {
					$oAttributes = $node->attributes;
					$sObject = $oAttributes->getNamedItem('object')->value;
					$sField = $oAttributes->getNamedItem('field')->value;
				} else {
					$aTokens = explode(".",trim($node->nodeValue, "{} "));
					$sObject = $aTokens[0];
					$sField = $aTokens[1];
				}

				if (array_key_exists($sObject, $this->_aVariables) && array_key_exists( $sField ,  $this->_aVariables[$sObject])) {
					$sValue = isset($aData[$sObject]) && isset($aData[$sObject][$sField])?$aData[$sObject][$sField]:'{'.$sObject.'.'.$sField.'}';
					$oNewNode = new DOMText($sValue);
					$node->parentNode->replaceChild($oNewNode, $node);
				}

			} else {
				foreach ($node->attributes as $attrName => $attrNode) {
					$aMatches = array();
					$iMatches = preg_match_all ( "/{\s*([A-Za-z0-9_]+).([A-Za-z_0-9]+)\s*}/" , $attrNode->value, $aMatches);
					$sValue = $attrNode->value;
					if($iMatches) {
						for($i=0;$i<count($aMatches[0]);$i++) {

							if (array_key_exists($aMatches[1][$i], $this->_aVariables) && array_key_exists( $aMatches[2][$i] , $this->_aVariables[$aMatches[1][$i]]) && (isset($aData[$aMatches[1][$i]]) && isset($aData[$aMatches[1][$i]][$aMatches[2][$i]])))
								$sValue = str_replace ($aMatches[0][$i] ,$aData[$aMatches[1][$i]][$aMatches[2][$i]] , $sValue);
						}
						$node->setAttribute ($attrName , $sValue);
					}
				}
			}
		}

		return str_replace('<?xml version="1.0" encoding="utf-8"?>' , "" ,$oDOMDocument->saveXML());
	}

	// getTextContent: Return the 'ready-to-send' Text content for the given array of data
	public function getTextContent($mData) {
		try {
			$aData = self::_getArrayFromData($mData);
			//$oDetails = Email_Template_Details::getCurrentDetailsForTemplateId($this->_oEmailTemplate->id);
			$sText = $this->_replaceVariablesInText($this->oDetails->email_text, $aData);
			return $sText;
		} catch (Exception $oException) {
			throw new Exception("Failed to get text content. ".$oException->getMessage());
		}
	}

	// getSubjectContent: Return the 'ready-to-send' Subject content for the given array of data
	public function getSubjectContent($mData) {
		try {
			$aData = self::_getArrayFromData($mData);
			//$oDetails = Email_Template_Details::getCurrentDetailsForTemplateId($this->_oEmailTemplate->id);
			$sText = $this->_replaceVariablesInText($this->oDetails->email_subject, $aData);
			return $sText;
		} catch (Exception $oException) {
			throw new Exception("Failed to get subject content. ".$oException->getMessage());
		}
	}

	public function generateEmail($aData, Email_Flex $mEmail=null) {
		// By default, just pass the raw data through as variable data
		return $this->generateEmailFromVariableData($aData, $mEmail);
	}

	public function generateEmailFromVariableData($aData, Email_Flex $mEmail=null) {
		$oEmail = ($mEmail !== null ? $mEmail : new Email_Flex());
		
		// Text
		$sText = $this->getTextContent($aData);
		$oEmail->setBodyText($sText);
		
		// HTML
		$sHTML = $this->getHTMLContent($aData);
		if ($sHTML && $sHTML !== '') {
			$oEmail->setBodyHtml($sHTML);
		}
		
		// Subject
		$sSubject = $this->getSubjectContent($aData);
		$oEmail->setSubject($sSubject);
		
		// From (Sender)
		$sFrom = $this->oDetails->email_from;
		if ($sFrom) {
			if (EmailAddressValid($sFrom)) {
				$oEmail->setFrom($sFrom);
			} else {
				Flex::assert(false, "Email Template {$this->_oEmailTemplate->email_template_id}/Customer Group {$this->_oEmailTemplate->customer_group_id} has an invalid From address: '{$sFrom}'", array('Email_Template_Logic'=>$this), "Email Template {$this->_oEmailTemplate->email_template_id}/Customer Group {$this->_oEmailTemplate->customer_group_id} has an invalid From address: '{$sFrom}'");
			}
		} else {
			Flex::assert(false, "Email Template {$this->_oEmailTemplate->email_template_id}/Customer Group {$this->_oEmailTemplate->customer_group_id} doesn't have a From address", array('Email_Template_Logic'=>$this), "Email Template {$this->_oEmailTemplate->email_template_id}/Customer Group {$this->_oEmailTemplate->customer_group_id} doesn't have a From address");
		}
		
		return $oEmail;
	}

	protected function _replaceVariablesInText($sText, $aData) {
		foreach ($this->_aVariables as $sObject => $aProperties) {
			if (isset($aData[$sObject])) {
				$aDataProperties = $aData[$sObject];
				foreach ($aProperties as $sProperty => $mSampleValue) {
					$mValue = $aDataProperties[$sProperty];
					if (isset($aDataProperties[$sProperty])) {
						// Replace all references with value
						$sText = preg_replace("/\\{{$sObject}.{$sProperty}\\}/", "{$mValue}", $sText);
					}
				}
			}
		}
		return $sText;
	}

	// getInstance: Returns the appropriate sub class for the email template type given
	public static function getInstance($iEmailTemplateType, $iCustomerGroup) {
		try {
			$oEmailTemplateType = Email_Template::getForId($iEmailTemplateType);
			if (!$oEmailTemplateType) {
				// Couldn't find the template
				throw new Exception("Invalid email template type id supplied.");
			}

			$oEmailTemplateCustomerGroup = Email_Template_Customer_Group::getForCustomerGroupAndType($iCustomerGroup, $iEmailTemplateType);

			// All good, return the instance
			$oEmailTemplate = null;
			if (($oEmailTemplateType->class_name !== null) && ($oEmailTemplateType->class_name !== '')) {
				if (!class_exists($oEmailTemplateType->class_name)) {
					// Bad class name in database
					throw new Exception("Invalid class_name value in email_template {$iEmailTemplateType}, class_name='{$oEmailTemplateType->class_name}'");
				}
				
				// A system template
				$oEmailTemplate = new $oEmailTemplateType->class_name($oEmailTemplateCustomerGroup);
			} else if (Email_Template_Correspondence::getForEmailTemplateId($iEmailTemplateType)) {
				// A correspondence template
				$oEmailTemplate = new Email_Template_Logic_Correspondence($oEmailTemplateCustomerGroup);    
			}
			return $oEmailTemplate;
		} catch (Exception $oException) {
			throw new Exception("Failed to get Email_Template_Logic instance. ".$oException->getMessage());
		}
	}

	public static function getLineNo($aLines, $sLinePart) {
		for($i = 0; $i < count($aLines); $i++) {
			if (strpos($aLines[$i], $sLinePart)) {
				return $i+1;
			}
		}

		return false;
	}

	public static  function processHTML($sHTML, $bReport = false, $bForTestEmail=false) {
		$aReport = array();

		foreach(self::$_aReport as $sItem) {
			$aReport[$sItem] = array();
		}

		if ($sHTML !=null && trim($sHTML)!='') {
			$sHTML = preg_replace ( '/(<variable\s*object\s*=\s*"[a-z _ 0-9 A-Z]+"\s*field\s*=\s*"[a-z _ 0-9 A-Z]+"\s*\/\s*>)\s+(<)/' ,"$1&nbsp;$2" , $sHTML );
			$aLines = explode("\n",$sHTML);
			//the loadHTML function will create a header tag when the meta tag is supplied, as below, so we have to first test if there is a user supplied header, for change repoerting purposes
			$bHeader = self::hasHeader($sHTML);
			$sEncoding = mb_detect_encoding($sHTML);
			//supply charset in order to preserve multi byte chars
			//the "__stripme" div is a container for the user supplied html, and will later be used to extract the user supplied html again
			$oDOMDocument = @DOMDocument::loadHTML('<meta http-equiv="Content-Type" content="text/xml;charset=utf-8" /><div id = "__stripme">'.$sHTML."</div>");//
			$xpath = @new DOMXPath($oDOMDocument);

			$query = '//css';
			$result = $xpath->query($query);

			foreach($result as $node) {
				$sSelector = $node->getAttributeNode('selector')->value;
				if ($sSelector!=null) {
					try {
						$sXpath = CSS_Parser::cssToXpath($sSelector);
						$sStyle = $node->textContent;
						$nodesToStyle = $xpath->query($sXpath);
						foreach ($nodesToStyle as $nodeToStyle) {
							$sInlineStyle = $nodeToStyle->getAttributeNode('style')?$nodeToStyle->getAttributeNode ('style')->value:'';
							$nodeToStyle->setAttribute('style', $sStyle." ".$sInlineStyle);
						}
					} catch(Exception $e) {
						$iLineNo = self::getLineNo($aLines, $sSelector);
						//for now we won't actually throw the exception, to make our system consistent with browser response to faulty css, which is to ignore it altogether
						//throw new EmailTemplateEditException('There was an error parsing CSS with selector: '.$sSelector.'.',$iLineNo, $e->__toString());
					}

				}
				$node = $node->parentNode->removeChild($node);
			}
			
			$result = $xpath->query("//script");
			foreach ($result as $node) {
				$aReport['javascript'][] =  $node->textContent;
				$node->parentNode->removeChild($node);
			}

			$result = $xpath->query("//link");
			foreach ($result as $node) {
				$aReport['link'][] =  $node->textContent;
				$node->parentNode->removeChild($node);
			}

			$result = $xpath->query("//style");
			foreach ($result as $node) {
				$aReport['style'][] =  $node->textContent;
				$node->parentNode->removeChild($node);
			}


			$oElements = $xpath->query("//*[@*]");
			foreach ($oElements as $oElement) {
				foreach ($oElement->attributes as $attrName => $attrNode) {
					if (strpos($attrName, 'on') === 0) {
						$aReport['events'][]= $node->textContent;
						$oElement->removeAttribute($attrName);
					}
				}
			}

			/*remove form related things*/
			$result = $xpath->query("//input");
			foreach ($result as $node) {
				$aReport['input'][]=$node->textContent;
				$node->parentNode->removeChild($node);
			}

			$result = $xpath->query("//select");
			foreach ($result as $node) {
				$aReport['input'][]=$node->textContent;
				$node->parentNode->removeChild($node);
			}

			$result = $xpath->query("//form");
			foreach ($result as $node) {
				$aReport['form'][]=$node->textContent;
				$oNewNode = new DOMElement('div');
				$node->parentNode->replaceChild($oNewNode, $node);

				foreach ($node->attributes as $attrName => $attrNode) {
					if ($attrName != 'action' && $attrName != 'method') {
						$oNewNode->setAttributeNode($attrNode);
					}
				}

				$x = $node->childNodes;

				if ($x!=null) {
					foreach ($x as $childNode) {
						$oNewChild = $childNode->cloneNode(true);
						$oNewNode->appendChild($oNewChild);
					}
				}
			}

			if ($bForTestEmail) {
				$result = $xpath->query("//variable");
				foreach ($result as $node) {
					if ($node->hasAttribute('object')) {
						$oAttributes = $node->attributes;
						$oObject = $oAttributes->getNamedItem('object');
						$oField = $oAttributes->getNamedItem('field');
						$sText = "{".$oObject->value.".".$oField->value."}";
						$oNewNode = new DOMElement('span', $sText);
						$node->parentNode->replaceChild($oNewNode, $node);
					} else {

						$oNewNode = new DOMText("{".$node->nodeValue."}");
						$node->parentNode->replaceChild($oNewNode, $node);

					}
				}
			}

			$oHeader = $oDOMDocument->getElementsByTagName ('head');
			foreach ($oHeader as $node) {
				$bHeader?$aReport['head'][] = $node->textContent:null;
				$node->parentNode->removeChild($node);

			}

			$oElements = $xpath->query("//*[@id='__stripme']");
			$oRootElement = $oElements->item(0);
			
			$imp = new DOMImplementation();
			$y = $imp->createDocument(  null, 'div',
										$imp->createDocumentType("xml",
																'xml version="1.0" encoding="utf-8"',
																NULL)
										);
			

			$oChildren = $oRootElement->childNodes;
			if ($oChildren!=null) {
				foreach ($oChildren as $node) {
					$node = @$y->importNode($node, true);
					$y->documentElement->appendChild($node);
					//$x->appendChild($node);
				}

			}
			$sString = str_replace ( "<?xml version=\"1.0\"?>" , "" , $y->saveXML());
			$sString = str_replace("<!DOCTYPE xml PUBLIC 'xml version=\"1.0\" encoding=\"utf-8\"' \"\">", "", $sString);    


			/*  //For query debug purpose
				$oDOMDocument = $y;*/
				/*$myFile = FILES_BASE_PATH.'temp/'."html_y.txt";
				$fh = fopen($myFile, 'w') or die("can't open file");
				$sString = str_replace ( "<?xml version=\"1.0\"?>" , "" , $y->saveXML());
				$sString = str_replace("<!DOCTYPE xml PUBLIC 'xml version=\"1.0\" encoding=\"utf-8\"' \"\">", "", $sString);
				fwrite($fh,trim($sString) );
				fclose($fh);*/

			return $bReport?$aReport:str_replace ( '<?xml version="1.0" encoding="utf-8"?>' , "" , trim($sString));
		}
		return $bReport?$aReport:"";
	}

	public static function normalizeNewLines($aText) {
		$sPreviousLine = "";
		$aResult = array();
		foreach ($aText as $sLine) {
			if (!(($sPreviousLine == "\n\n") && (($sLine == "\n\n") || ($sLine == "\n"))) && !(($sPreviousLine == "\n") && ($sLine == "\n\n"))) {
				$aResult[] = $sLine;
			}
			$sPreviousLine = $sLine;
		}
		return $aResult;
	}


	public static function toText($sHTML) {
		//Log::get()->log("[*] Email_Template_Logic::toText - HTML - {$sHTML}");
		$sHTML = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $sHTML);
		//Log::get()->log("[*] Email_Template_Logic::toText - Stage 1 - {$sHTML}");

		$sText = '';
		if (trim((string)$sHTML)) {
			$sDocumentXML = DOMDocument::loadXML(self::processHTML($sHTML))->documentElement;
			$sText = implode("", self::normalizeNewLines(self::_toText($sDocumentXML, array())));
		}
		
		//Log::get()->log("[*] Email_Template_Logic::toText - Stage 2 - {$sText}");
		$sText = self::trimLines(self::normalizeWhiteSpaces($sText));
		//Log::get()->log("[*] Email_Template_Logic::toText - TEXT - ".trim($sText));
		return trim($sText);
	}

	public static function normalizeWhiteSpaces($sString) {

		$sString = str_replace(array("  "), ' ', $sString);
		if (strpos( $sString ,"  " )) {
			$sString = self::normalizeWhiteSpaces($sString);

		}

		return $sString;


	}

	public static function trimLines($sString) {
		$aLines = explode("\n", $sString);
		$aResult = array();

		foreach ($aLines as $sLine) {
			$aResult[]= ltrim($sLine, " ");

		}
		return implode("\n", $aResult);


	}

	protected static function _toText($oNode, $aTextArray, $sParentTagName = null, $iListCount = null) {
		if ( $oNode->tagName == 'p'  ||$oNode->tagName == 'h1' ||$oNode->tagName == 'h2' ||$oNode->tagName == 'h3'||$oNode->tagName == 'h4'||$oNode->tagName == 'form' ||$oNode->tagName == 'table' ||$oNode->tagName == 'ul' ||$oNode->tagName == '0l') {
			$aTextArray[] = "\n\n";
		} else if ($oNode->tagName == 'tr' ||$oNode->tagName == 'div' || $oNode->tagName == 'br' ) {
			$aTextArray[] = "\n";
		} else if ($oNode->tagName == 'td' || $oNode->tagName == 'th') {
			$aTextArray[] = "\t\t\t";
		}
		/*else if ($oNode->tagName == 'span' || $oNode->tagName == 'a' || $oNode->tagName == 'variable' || $oNode->tagName == 'b') {
			$aTextArray[] = " ";
		}*/

		//$oNode = $oNode ==null?DOMDocument::loadXML($this->getHTML(true))->documentElement:$oNode;
		$oNode->tagName !='ol' && $sParentTagName!='ol'?$iListCount=0:null;
		$x = $oNode->childNodes;

		if ($x != null) {
			foreach ($x as $node) {
				if (property_exists($node, 'tagName') && ($node->tagName == 'li')) {
					$aTextArray[] ="\n";
					$sListChar = $oNode->tagName =='ul'?"\t* ":($oNode->tagName=='ol'?"\t".++$iListCount.". ":null);
					$aTextArray[] = $sListChar;
				}


				if (get_class($node) == 'DOMText') {
					if ($node->wholeText!=null) {
						//$node->previousSibling->tagName == 'p'?$aTextArray[]="\n\n":$node->previousSibling->tagName == 'div'?$aTextArray[]="\n":null;
						(end($aTextArray)=="\n\n"||end($aTextArray)=="\n")&&self::normalizeWhiteSpaces($node->wholeText)==" "?null:$aTextArray[]=$node->wholeText;
						//$aTextArray[]=$node->wholeText;//$node->wholeText;

					}
				} else if ($node->tagName == 'variable') {

					if($node->hasAttribute('object')) {

						$oAttributes = $node->attributes;
						$oObject = $oAttributes->getNamedItem('object');
						$oField = $oAttributes->getNamedItem('field');

						//$sBreak;
						//if ($node->nextSibling->parentNode === $node->parentNode || $node->parentNode->tagName =='b')
						//{
						//  $sBreak = "";
						//}

						$aTextArray[] ="{".$oObject->value.".".$oField->value."}";
					} else {
						$aTextArray[] = "{".$node->nodeValue."}";
					}
				} else {

					$aTextArray = self::_toText($node, $aTextArray, $oNode->tagName, $iListCount);
				}
			}

			if ( $oNode->tagName == 'p'  ||$oNode->tagName == 'h1' ||$oNode->tagName == 'h2' ||$oNode->tagName == 'h3'||$oNode->tagName == 'h4'||$oNode->tagName == 'form' ||$oNode->tagName == 'table' ||$oNode->tagName == 'ul' ||$oNode->tagName == '0l') {
				$aTextArray[] = "\n\n";
			} else if ($oNode->tagName == 'div') {
				$aTextArray[] = "\n";
			}
		}

		return $aTextArray;
	}

	private static function hasHeader($sHTML) {
		$oDOMDocument = @DOMDocument::loadHTML($sHTML);
		$oHeaders = $oDOMDocument->getElementsByTagName('head');

		foreach ($oHeaders as $node) {
			return true;
		}
		return false;
	}

	// _getArrayFromData: Return an array representation of the given data (object or array)
	protected static function _getArrayFromData($mData) {
		if (is_array($mData)) {
			$aData = $mData;
		} else {
			$aData = get_object_vars($mData);
		}

		foreach ($aData as $sKey => $mVal) {
			if (!is_array($mVal)) {
				$aData[$sKey] = get_object_vars($mVal);
			}
		}

		return $aData;
	}

	public static function validateTemplateDetails($aTemplateDetails) {
		$aErrors = array();
		
		trim($aTemplateDetails['email_text']) == ''         ?   $aErrors[] = "Your template must have a text version."  : null;
		trim($aTemplateDetails['email_subject']) == ''      ?   $aErrors[] = "Your template must have a subject."           : null;
		trim($aTemplateDetails['description']) == ''        ?   $aErrors[] = "Your template must have a description."       : null;
		!EmailAddressValid($aTemplateDetails['email_from']) ?   $aErrors[] = "Invalid email address supplied for sender."   : null;
		
		return $aErrors;
	}

	public static function sendTestEmail($aData, $iTemplateId) {
		$oTemplate = Email_Template_Customer_Group::getForId($iTemplateId);
		$oEmailTemplateType = Email_Template::getForId($oTemplate->email_template_id);
		$oTemplateDetails = new Email_Template_Details(array(
			'email_text' => $aData['text'] ? $aData['text'] : '', 
			'email_html' => $aData['html'] ? $aData['html'] : '', 
			'email_subject' => $aData['subject'] ? $aData['subject'] : '',
			'email_from' => $aData['from'] ? $aData['from'] : 'ybs-admin@ybs.net.au'
		));
		
		// Create Email_Template_Logic instance
		if (($oEmailTemplateType->class_name !== null) && ($oEmailTemplateType->class_name !== '')) {
			$oTemplateLogicObject = new $oEmailTemplateType->class_name($oTemplate, $oTemplateDetails);
		} else if (Email_Template_Correspondence::getForEmailTemplateId($oEmailTemplateType->id)) {
			$oTemplateLogicObject = new Email_Template_Logic_Correspondence($oTemplate, $oTemplateDetails); 
		}
		
		$oEmail = $oTemplateLogicObject->generateEmailFromVariableData($oTemplateLogicObject->getSampleData());
		foreach ($aData['to'] as $sAddress) {
			$oEmail->addTo($sAddress);
		}
		
		$aError = error_get_last();
		@$oEmail->send();
		$aNewError = error_get_last();
		if ($aNewError != $aError && $aNewError['type'] != 2048) {
			throw new Exception ("Email Error: ".$aNewError['message']);
		}
		return $oEmail;
	}

	public function getSampleData() {
		return $this->_aVariables;
	}

	public function __get($sField) {
		return $sField == "_aVariables"?$this->getVariables():null;
	}
}

// Custom Exception classes
class EmailTemplateEditException extends Exception {
	public $sSummaryMessage;
	public $iLineNumber;

	public function __construct($sSummary, $iLineNumber, $sDetails) {
		parent::__construct($sDetails);
		$this->sSummaryMessage = $sSummary;
		$this->iLineNumber = $iLineNumber;
	}
}
