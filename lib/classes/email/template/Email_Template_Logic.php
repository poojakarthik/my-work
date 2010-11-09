<?php
class Email_Template_Logic
{

	const READ = 1;
	const EDIT = 2;
	const CREATE = 3;


	private		$_oEmailTemplate	= null;
	//protected	$_aVariables		= array();


	protected static $_aReport = array(
										'javascript',
										'events',
										'form',
										'input',
										'link',
										'style',
										'head'
									);



	public function __construct($oEmailTemplate, $oEmailTemplateDetails = null )
	{
		$this->_oEmailTemplate	= $oEmailTemplate;
		$this->oDetails	= $oEmailTemplateDetails== null?Email_Template_Details::getCurrentDetailsForTemplateId($this->_oEmailTemplate->id):$oEmailTemplateDetails;
	}

	// getHTMLContent: Return the 'ready-to-send' HTML content for the given array of data

	public function getHTMLContent($mData)
	{
		$aData				= self::_getArrayFromData($mData);
		//$oDetails			= Email_Template_Details::getCurrentDetailsForTemplateId($this->_oEmailTemplate->id);
		$sHTML = $this->oDetails->email_html;
		$sHTML				= self::processHTML($sHTML);
		$oDOMDocument		= DomDocument::loadXML('<?xml version="1.0" encoding="utf-8"?>'.$sHTML);
		$oXPath 			= new DOMXPath($oDOMDocument);
		$oTags				=$oXPath->query("//*[@*] | //variable");
		foreach ($oTags as $node)
		{
			if ($node->tagName =='variable')
			{
				$sObject;
				$sField;

				if ($node->hasAttribute('object'))
				{
					$oAttributes 	= $node->attributes;
					$sObject 		= $oAttributes->getNamedItem('object')->value;
					$sField		= $oAttributes->getNamedItem('field')->value;
				}
				else
				{
					$aTokens = explode(".",trim($node->nodeValue, "{} "));
					$sObject = $aTokens[0];
					$sField = $aTokens[1];
				}

				if (array_key_exists($sObject, $this->_aVariables) && array_key_exists( $sField ,  $this->_aVariables[$sObject]))
				{
					$sValue = isset($aData[$sObject]) && isset($aData[$sObject][$sField])?$aData[$sObject][$sField]:'{'.$sObject.'.'.$sField.'}';
					$oNewNode = new DOMText($sValue);
					$node->parentNode->replaceChild($oNewNode, $node);
				}

			}
			else
			{
				foreach ($node->attributes as $attrName => $attrNode)
			  	{
			  		$aMatches = array();
			  		$iMatches = preg_match_all ( "/{\s*([A-Za-z0-9_]+).([A-Za-z_0-9]+)\s*}/" , $attrNode->value, $aMatches);
			  		$sValue = $attrNode->value;
			  		if($iMatches)
			  		{
			  			for($i=0;$i<count($aMatches[0]);$i++)
			  			{

			  				if (array_key_exists($aMatches[1][$i], $this->_aVariables) && array_key_exists( $aMatches[2][$i] , $this->_aVariables[$aMatches[1][$i]]) && (isset($aData[$aMatches[1][$i]]) && isset($aData[$aMatches[1][$i]][$aMatches[2][$i]])))
								$sValue = str_replace ($aMatches[0][$i] ,$aData[$aMatches[1][$i]][$aMatches[2][$i]] , $sValue);
			  			}
			  			$node->setAttribute ($attrName , $sValue);
			  		}
			  	}
			}
		}

		return str_replace ( '<?xml version="1.0" encoding="utf-8"?>' , "" ,$oDOMDocument->saveXML());
	}

	// getTextContent: Return the 'ready-to-send' Text content for the given array of data
	public function getTextContent($mData)
	{
		try
		{
			$aData		= self::_getArrayFromData($mData);
			//$oDetails	= Email_Template_Details::getCurrentDetailsForTemplateId($this->_oEmailTemplate->id);
			$sText		= $this->_replaceVariablesInText($this->oDetails->email_text, $aData);
			return $sText;
		}
		catch (Exception $oException)
		{
			throw new Exception("Failed to get text content. ".$oException->getMessage());
		}
	}

	// getSubjectContent: Return the 'ready-to-send' Subject content for the given array of data
	public function getSubjectContent($mData)
	{
		try
		{
			$aData		= self::_getArrayFromData($mData);
			//$oDetails	= Email_Template_Details::getCurrentDetailsForTemplateId($this->_oEmailTemplate->id);
			$sText		= $this->_replaceVariablesInText($this->oDetails->email_subject, $aData);
			return $sText;
		}
		catch (Exception $oException)
		{
			throw new Exception("Failed to get subject content. ".$oException->getMessage());
		}
	}

	protected function _replaceVariablesInText($sText, $aData)
	{
		foreach ($this->_aVariables as $sObject => $aProperties)
		{
			if (isset($aData[$sObject]))
			{
				$aDataProperties	= $aData[$sObject];
				foreach ($aProperties as $sProperty => $mSampleValue)
				{
					$mValue	= $aDataProperties[$sProperty];
					if (isset($aDataProperties[$sProperty]))
					{
						// Replace all references with value
						$sText	= preg_replace("/\\{{$sObject}.{$sProperty}\\}/", "{$mValue}", $sText);
					}
				}
			}
		}
		return $sText;
	}

	public function generateEmail($aData, Email_Flex $mEmail=null)
	{
		$oEmail	= ($mEmail !== null ? $mEmail : new Email_Flex());
		$aData = $this->getData($aData['invoice_id'], $aData['contact_id']);
		$sSubject	= $this->getSubjectContent($aData);
		$sHTML		= $this->getHTMLContent($aData);
		$sText		= $this->getTextContent($aData);

		$oEmail->setBodyText($sText);
		if ($sHTML && $sHTML !== '')
		{
			$oEmail->setBodyHtml($sHTML);
		}
		$oEmail->setSubject($sSubject);

		return $oEmail;
	}

	// getInstance: Returns the appropriate sub class for the email template type given
	public static function getInstance($iEmailTemplateType, $iCustomerGroup)
	{
		try
		{
			$oEmailTemplateType	= Email_Template_Type::getForId($iEmailTemplateType);
			if (!$oEmailTemplateType)
			{
				// Couldn't find the template
				throw new Exception("Invalid email template type id supplied.");
			}

			if (!class_exists($oEmailTemplateType->class_name))
			{
				// Bad class name in database
				throw new Exception("Invalid class_name value in email_template_type {$iEmailTemplateType}, class_name='{$oEmailTemplateType->class_name}'");
			}

			// All good, return the instance
			$oEmailTemplate	= Email_Template::getForCustomerGroupAndType($iCustomerGroup, $iEmailTemplateType);
			return new $oEmailTemplateType->class_name($oEmailTemplate);
		}
		catch (Exception $oException)
		{
			throw new Exception("Failed to get Email_Template_Logic instance. ".$oException->getMessage());
		}
	}

	public static function getLineNo($aLines, $sLinePart)
	{
		for($i=0;$i<count($aLines);$i++)
		{
			if (strpos ( $aLines[$i] , $sLinePart ))
				return $i+1;
		}

		return false;

	}



	public static  function processHTML($sHTML, $bReport = false, $bForTestEmail=false)
	{

		$aReport = array();

		foreach(self::$_aReport as $sItem)
		{
			$aReport[$sItem] = array();
		}

		if ($sHTML !=null && trim($sHTML)!='')
		{
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

	        foreach($result as $node)
	        {
	        	$sSelector = $node->getAttributeNode('selector')->value;
	        	if ($sSelector!=null)
	        	{
		        	try
		        	{

		        		$sXpath = CSS_Parser::cssToXpath($sSelector);
			        	$sStyle = $node->textContent;
			        	$nodesToStyle = $xpath->query($sXpath);
			        	foreach ($nodesToStyle as $nodeToStyle)
			        	{
			        		$sInlineStyle = $nodeToStyle->getAttributeNode('style')?$nodeToStyle->getAttributeNode ('style')->value:'';
			        		$nodeToStyle->setAttribute('style', $sStyle." ".$sInlineStyle);
			        	}
		        	}
		        	catch(Exception $e)
		        	{
		        		$iLineNo = self::getLineNo($aLines, $sSelector);
		        		//for now we won't actually throw the exception, to make our system consistent with browser response to faulty css, which is to ignore it altogether
		        		//throw new EmailTemplateEditException('There was an error parsing CSS with selector: '.$sSelector.'.',$iLineNo, $e->__toString());
		        	}

	        	}
				$node = $node->parentNode->removeChild($node);
	        }



			 $result = $xpath->query("//script");
			 foreach ($result as $node)
			 {
				 $aReport['javascript'][] =  $node->textContent;
				 $node->parentNode->removeChild($node);
			 }

			$result = $xpath->query("//link");
			foreach ($result as $node)
			{
				$aReport['link'][] =  $node->textContent;
				$node->parentNode->removeChild($node);
			}

			$result = $xpath->query("//style");
			foreach ($result as $node)
			 {
				 $aReport['style'][] =  $node->textContent;
			 	$node->parentNode->removeChild($node);
			 }


		 	$oElements = $xpath->query("//*[@*]");
			foreach ($oElements as $oElement)
		 	{
		 		foreach ($oElement->attributes as $attrName => $attrNode)
			  	{
			  		if (strpos($attrName, 'on') === 0)
			  		{
			  			$aReport['events'][]= $node->textContent;
			  			$oElement->removeAttribute($attrName);
			  		}
			  	}
		 	}

		 	/*remove form related things*/
			$result = $xpath->query("//input");
			foreach ($result as $node)
			{
				$aReport['input'][]=$node->textContent;
			 	$node->parentNode->removeChild($node);
			 }

			$result = $xpath->query("//select");
			 foreach ($result as $node)
			 {
			 	$aReport['input'][]=$node->textContent;
			 	$node->parentNode->removeChild($node);
			 }

			$result = $xpath->query("//form");
			foreach ($result as $node)
			{
				$aReport['form'][]=$node->textContent;
				$oNewNode = new DOMElement('div');
				$node->parentNode->replaceChild($oNewNode, $node);

				foreach ($node->attributes as $attrName => $attrNode)
			  	{
			  		if ($attrName != 'action' && $attrName != 'method')
			  		{
			  			$oNewNode->setAttributeNode ($attrNode );

			  		}
			  	}

			  	$x = $node->childNodes;

				if ($x!=null)
				{
					foreach ($x as $childNode)
					{
						$oNewChild = $childNode->cloneNode(true);
						$oNewNode->appendChild($oNewChild);
					}
				 }
			}

			 if ($bForTestEmail)
			 {
			 	$result = $xpath->query("//variable");
				foreach ($result as $node)
				{
					if ($node->hasAttribute('object'))
					{
						$oAttributes 	= $node->attributes;
						$oObject 		= $oAttributes->getNamedItem('object');
						$oField 		= $oAttributes->getNamedItem('field');
						$sText = "{".$oObject->value.".".$oField->value."}";
						$oNewNode = new DOMElement('span', $sText);
						$node->parentNode->replaceChild($oNewNode, $node);
					}
					else
					{

						$oNewNode = new DOMText("{".$node->nodeValue."}");
						$node->parentNode->replaceChild($oNewNode, $node);

					}
				}
			 }


			$oHeader = $oDOMDocument->getElementsByTagName ('head');
			foreach ($oHeader as $node)
			{
			 	$bHeader?$aReport['head'][] = $node->textContent:null;
				$node->parentNode->removeChild($node);

			}

			$oElements = $xpath->query("//*[@id='__stripme']");
			$oRootElement = $oElements->item(0);
			$oRootElement->firstChild->nextSibling == null&&$oRootElement->firstChild->tagName!=null?$oRootElement=$oRootElement->firstChild:null;
			$sRootName = $oRootElement==null?'div':$oRootElement->tagName;

			/*$oRootElement = $oDOMDocument->documentElement;//$oDOMDocument->getElementById ('__stripme');//
			//$oRootElement->firstChild->nextSibling == null?$oRootElement=$oRootElement->firstChild:null;
			$oRootElement->firstChild->nextSibling == null&&$oRootElement->tagName =='html'?$oRootElement=$oRootElement->firstChild:null;
			$sRootName = $oRootElement->tagName =='body'||$oRootElement->tagName =='html'||$oRootElement==null?'div':$oRootElement->tagName;
*/
			$aError = error_get_last();
			$x = @DOMDocument::loadXML('<?xml version="1.0" encoding="utf-8"?>'."<".$sRootName."> </".$sRootName.">");
			$aNewError = error_get_last();
			if ($aNewError!=$aError && $aNewError['message'] != "Non-static method DOMDocument::loadXML() should not be called statically")
			{
				throw new Exception ("DOM Document XML Error whilst processing HTML: ".$aNewError['message']);
			}

		 	$oChildren = $oRootElement->childNodes;
			if ($oChildren!=null)
			{
				foreach ($oChildren as $node)
				{
					$node = @$x->importNode($node, true);
					$x->documentElement->appendChild($node);
				}

			}

			$oDOMDocument = $x;//*DOMDocument::loadXML(str_replace ( '<?xml version="1.0">' , "" , $x->saveXML()));


	/*		//For query debug purpose
		  	$myFile = "html.txt";
			$fh = fopen($myFile, 'w') or die("can't open file");
			fwrite($fh, str_replace ( '<?xml version="1.0" encoding="utf-8"?>' , "" , $oDOMDocument->saveXML()));
			fclose($fh);*/

			return $bReport?$aReport:str_replace ( '<?xml version="1.0" encoding="utf-8"?>' , "" , $oDOMDocument->saveXML());
		}
		return $bReport?$aReport:"";
	}

	public static function normalizeNewLines($aText)
	{
		$sPreviousLine = "";
		$aResult = array();
		foreach ($aText as $sLine)
		{
			($sPreviousLine == "\n\n"&&($sLine=="\n\n" || $sLine == "\n"))||($sPreviousLine=="\n" && $sLine=="\n\n")?null:$aResult[]=$sLine;
			$sPreviousLine = $sLine;
		}
		return $aResult;
	}


	public static function toText($sHTML)
	{

		$sHTML = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $sHTML);

		$sText =  !empty($sHTML) && trim($sHTML)!='' && $sHTML!=null?implode("",self::normalizeNewLines(self::_toText(DOMDocument::loadXML(self::processHTML($sHTML))->documentElement, array()))):"";
		//$sText = preg_replace('/\s\s+/s', 'bbbbbbbb', $sText);

		$sText = self::trimLines(self::normalizeWhiteSpaces($sText));
		return trim($sText);
	}

	public static function normalizeWhiteSpaces($sString)
	{

		$sString = str_replace(array("  "), ' ', $sString);
		if (strpos( $sString ,"  " ))
		{
			$sString = self::normalizeWhiteSpaces($sString);

		}

		return $sString;


	}

	public static function trimLines($sString)
	{
		$aLines = explode("\n", $sString);
		$aResult = array();

		foreach ($aLines as $sLine)
		{
			$aResult[]= ltrim($sLine, " ");

		}
		return implode("\n", $aResult);


	}

protected static function _toText($oNode, $aTextArray, $sParentTagName = null, $iListCount = null)
	{
	if ( $oNode->tagName == 'p'  ||$oNode->tagName == 'h1' ||$oNode->tagName == 'h2' ||$oNode->tagName == 'h3'||$oNode->tagName == 'h4'||$oNode->tagName == 'form' ||$oNode->tagName == 'table' ||$oNode->tagName == 'ul' ||$oNode->tagName == '0l')
					{
						$aTextArray[] = "\n\n";
					}
					else if ($oNode->tagName == 'tr' ||$oNode->tagName == 'div' || $oNode->tagName == 'br' )
					{

						$aTextArray[] = "\n";

					}
					else if ($oNode->tagName == 'td' || $oNode->tagName == 'th')
					{
						$aTextArray[] = "\t\t\t";
					}
					/*else if ($oNode->tagName == 'span' || $oNode->tagName == 'a' || $oNode->tagName == 'variable' || $oNode->tagName == 'b')
					{
						$aTextArray[] = " ";
					}*/

		//$oNode = $oNode ==null?DOMDocument::loadXML($this->getHTML(true))->documentElement:$oNode;
		$oNode->tagName !='ol' && $sParentTagName!='ol'?$iListCount=0:null;
		$x = $oNode->childNodes;

		if ($x!=null)
		{
			foreach ($x as $node)
			{

				if ($node->tagName == 'li')
				{
					$aTextArray[] ="\n";
					$sListChar 	= $oNode->tagName =='ul'?"\t* ":($oNode->tagName=='ol'?"\t".++$iListCount.". ":null);
					$aTextArray[] = $sListChar;
				}


				if (get_class($node) == 'DOMText')
				{
					if ($node->wholeText!=null)
					{
						//$node->previousSibling->tagName == 'p'?$aTextArray[]="\n\n":$node->previousSibling->tagName == 'div'?$aTextArray[]="\n":null;
						(end($aTextArray)=="\n\n"||end($aTextArray)=="\n")&&self::normalizeWhiteSpaces($node->wholeText)==" "?null:$aTextArray[]=$node->wholeText;
						//$aTextArray[]=$node->wholeText;//$node->wholeText;

					}
				}
				else if ($node->tagName == 'variable')
				{

					if($node->hasAttribute('object'))
					{

						$oAttributes 	= $node->attributes;
						$oObject 		= $oAttributes->getNamedItem('object');
						$oField 		= $oAttributes->getNamedItem('field');


						//$sBreak;
						//if ($node->nextSibling->parentNode === $node->parentNode || $node->parentNode->tagName =='b')
						//{
						//	$sBreak = "";
						//}


						$aTextArray[] ="{".$oObject->value.".".$oField->value."}";
					}
					else
					{
						$aTextArray[] = "{".$node->nodeValue."}";

					}
				}
				else
				{

					$aTextArray = self::_toText($node, $aTextArray, $oNode->tagName, $iListCount);
				}
			}


			if ( $oNode->tagName == 'p'  ||$oNode->tagName == 'h1' ||$oNode->tagName == 'h2' ||$oNode->tagName == 'h3'||$oNode->tagName == 'h4'||$oNode->tagName == 'form' ||$oNode->tagName == 'table' ||$oNode->tagName == 'ul' ||$oNode->tagName == '0l')
					{
						$aTextArray[] = "\n\n";
					}
					else if ($oNode->tagName == 'div')
					{

						$aTextArray[] = "\n";

					}

		}

		return $aTextArray;
	}

	private static function hasHeader($sHTML)
	{
		$oDOMDocument = @DOMDocument::loadHTML($sHTML);
		$oHeaders = $oDOMDocument->getElementsByTagName('head');

		foreach ($oHeaders as $node)
		{
			return true;
		}
		return false;
	}

	// _getArrayFromData: Return an array representation of the given data (object or array)
	protected static function _getArrayFromData($mData)
	{
		if (is_array($mData))
		{
			$aData	= $mData;
		}
		else
		{
			$aData	= get_object_vars($mData);
		}

		foreach ($aData as $sKey => $mVal)
		{
			if (!is_array($mVal))
			{
				$aData[$sKey]	= get_object_vars($mVal);
			}
		}

		return $aData;
	}

	public static function validateTemplateDetails($aTemplateDetails)
	{
		$aErrors = array();
		trim($aTemplateDetails['email_text']) == ''?$aErrors[]= "Your template must have a text version.":null;
		trim($aTemplateDetails['email_subject']) == ''?$aErrors[]= "Your template must have a subject.":null;
		trim($aTemplateDetails['description']) == ''?$aErrors[]= "Your template must have a description.":null;
		return $aErrors;
	}

	public static function sendTestEmail($aData, $iTemplateId)
	{
		$oTemplate = Email_Template::getForId($iTemplateId);
		$oEmailTemplateType	= Email_Template_Type::getForId($oTemplate->email_template_type_id);
		$oTemplateDetails = new Email_Template_Details(array('email_text'=>$aData['text'], 'email_html'=>$aData['html'], 'email_subject'=>$aData['subject']));
		$oTemplateLogicObject = new $oEmailTemplateType->class_name($oTemplate, $oTemplateDetails);

		$aSampleData = $oTemplateLogicObject->getSampleData($iTemplateId);
		$sSubject	= $oTemplateLogicObject->getSubjectContent($aSampleData);
		$sHTML		= $aData['html']!=null&&trim($aData['html'])!=''?$oTemplateLogicObject->getHTMLContent($aSampleData):null;
		$sText		= $oTemplateLogicObject->getTextContent($aSampleData);//($aSampleData);


		$oEmail	= new Email_Flex();
		$oEmail->setBodyText($sText);

		if ($aData['html']!=null && trim($aData['html'])!='')
		{
			$oEmail->setBodyHtml($sHTML);
		}

		$oEmail->setSubject($sSubject);

		foreach ($aData['to'] as $sAddress)
		{
			$oEmail->addTo($sAddress);
		}
		$oEmail->setFrom('ybs-admin@ybs.net.au', $name = 'Yellow Billing Services');
		$aError = error_get_last();
		@$oEmail->send();
		$aNewError = error_get_last();
			if ($aNewError!=$aError && $aNewError['type']!=2048)
			{
				throw new Exception ("Email Error: ".$aNewError['message']);
			}
		return $oEmail;
	}

	public function getSampleData()
	{



		$oCustomerGroup = Customer_Group::getForId($this->_oEmailTemplate->customer_group_id);

		//$oEmployee = Employee::getForId(Flex::getUserId());

		$intInvoiceDatetime				= strtotime(Data_Source_Time::currentDate());
		$strInvoiceDatetime				= date("d/m/Y", $intInvoiceDatetime);

		$iBillingDate			= strtotime(Data_Source_Time::currentDate());
		$sInvoiceDate			= date('dmY', $iBillingDate);

		// Build billing period string for the email subject lines
		$sBillingPeriodEndMonth		= date("F", strtotime("-1 day", $iBillingDate));
		$sBillingPeriodEndYear		= date("Y", strtotime("-1 day", $iBillingDate));
		$sBillingPeriodStartMonth	= date("F", strtotime("-1 month", $iBillingDate));
		$sBillingPeriodStartYear	= date("Y", strtotime("-1 month", $iBillingDate));
		$sBillingPeriod				= $sBillingPeriodStartMonth;
		if ($sBillingPeriodStartYear !== $sBillingPeriodEndYear)
		{
			$sBillingPeriod	.= " {$sBillingPeriodStartYear} / {$sBillingPeriodEndMonth} {$sBillingPeriodEndYear}";
		}
		else if ($sBillingPeriodStartMonth !== $sBillingPeriodEndMonth)
		{
			$sBillingPeriod	.= " / {$sBillingPeriodEndMonth} {$sBillingPeriodEndYear}";
		}
		else
		{
			$sBillingPeriod	.= " {$sBillingPeriodStartYear}";
		}

		$aSampleInvoiceData = Invoice::getSampleDataForCustomerGroupId($this->_oEmailTemplate->customer_group_id);
		switch ($this->_oEmailTemplate->email_template_type_id)
		{
			case(EMAIL_TEMPLATE_TYPE_INVOICE):
													return $this->getData($aSampleInvoiceData['invoice_id'], $aSampleInvoiceData['contact_id']);
													break;
			case (EMAIL_TEMPLATE_TYPE_LATE_NOTICE):
													$aSampleData =  $this->_aVariables;
													$aSampleData['CustomerGroup']['external_name']= 	$oCustomerGroup->external_name;
													$aSampleData['Letter']['type'] = GetConstantDescription(DOCUMENT_TEMPLATE_TYPE_OVERDUE_NOTICE, "DocumentTemplateType");//DocumentTemplateType::getForId(DOCUMENT_TEMPLATE_TYPE_OVERDUE_NOTICE)->description;
													$aSampleData['Contact']['first_name'] = $aSampleAccountData['contact_first_name'];
													$aSampleData['Account']['id'] = $aSampleAccountData['account_id'];
													$aSampleData['CustomerGroup']['email_domain'] = $oCustomerGroup->email_domain;
													return $aSampleData;
													break;
			default:
													return $this->_aVariables;

		}

	}



	public function __get($sField)
	{
		return $sField == "_aVariables"?$this->getVariables():null;
	}



}



class EmailTemplateEditException extends Exception
{
	public $sSummaryMessage;
	public $iLineNumber;

	public function __construct($sSummary, $iLineNumber, $sDetails)
	{
		parent::__construct($sDetails);
		$this->sSummaryMessage = $sSummary;
		$this->iLineNumber = $iLineNumber;
	}
}