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



	public function __construct($oEmailTemplate)
	{
		$this->_oEmailTemplate	= $oEmailTemplate;
	}

	// getHTMLContent: Return the 'ready-to-send' HTML content for the given array of data
	public function getHTMLContent($mData)
	{
		try
		{
			$aData		= self::_getArrayFromData($mData);//(is_array($mData) ? $mData : get_object_vars($mData));
			$oDetails	= Email_Template_Details::getCurrentDetailsForTemplateId($this->_oEmailTemplate->id);
			$sHTML		= self::processHTML($oDetails->email_html);
			foreach ($this->_aVariables as $sObject => $aProperties)
			{
				if (isset($aData[$sObject]))
				{
					$aDataProperties	= $aData[$sObject];
					foreach ($aProperties as $sProperty)
					{
						$mValue	= $aDataProperties[$sProperty];
						if (isset($aDataProperties[$sProperty]))
						{
							// Replace all references with value
							$sHTML	= preg_replace("/\<variable(\s+)object=['\"]{$sObject}['\"](\s+)field=['\"]{$sProperty}['\"](\s?)\/\>/", "{$mValue}", $sHTML);
						}
					}
				}
			}
			return $sHTML;
		}
		catch (Exception $oException)
		{
			throw new Exception("Failed to get HTML content. ".$oException->getMessage().". Data = ".print_r($mData, true));
		}
	}

	// getTextContent: Return the 'ready-to-send' Text content for the given array of data
	public function getTextContent($mData)
	{
		try
		{
			$aData		= self::_getArrayFromData($mData);
			$oDetails	= Email_Template_Details::getCurrentDetailsForTemplateId($this->_oEmailTemplate->id);
			$sText		= $this->_replaceVariablesInText($oDetails->email_text, $aData);
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
			$oDetails	= Email_Template_Details::getCurrentDetailsForTemplateId($this->_oEmailTemplate->id);
			$sText		= $this->_replaceVariablesInText($oDetails->email_subject, $aData);
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
				foreach ($aProperties as $sProperty)
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

	public static  function processHTML($sHTML, $bReport = false, $bForTestEmail=false)
	{

		$aReport = array();
		foreach(self::$_aReport as $sItem)
		{
			$aReport[$sItem] = array();
		}

		if ($sHTML !=null && trim($sHTML)!='')
		{

			$sEncoding = mb_detect_encoding(sHTML);
			$oDOMDocument = @DOMDocument::loadHTML(' <meta http-equiv="Content-Type" content="text/html; charset="'.$sEncoding.'">'.$sHTML);
			$xpath = @new DOMXPath($oDOMDocument);

			$query = '//css';
	        $result = $xpath->query($query);

	        foreach($result as $node)
	        {
	        	$sSelector = $node->getAttributeNode('selector')->value;
	        	if ($sSelector!=null)
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
				$node->parentNode->removeChild($node);
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
					$oAttributes 	= $node->attributes;
					$oObject 		= $oAttributes->getNamedItem('object');
					$oField 		= $oAttributes->getNamedItem('field');
					$sText = "{".$oObject->value.".".$oField->value."}";
					$oNewNode = new DOMElement('span', $sText);
					$node->parentNode->replaceChild($oNewNode, $node);
				}
			 }


			$oHeader = $oDOMDocument->getElementsByTagName ('head');
			foreach ($oHeader as $node)
			{
			 	$aReport['head'][] = $node->textContent;
				$node->parentNode->removeChild($node);

			}

			$oRootElement = $oDOMDocument->documentElement;//->firstChild;
			$oRootElement->firstChild->nextSibling == null&&$oRootElement->tagName =='html'?$oRootElement=$oRootElement->firstChild:null;
			$sRootName = $oRootElement->tagName =='body'||$oRootElement->tagName =='html'?'div':$oRootElement->tagName;

			$aError = error_get_last();
			$x = @DOMDocument::loadXML("<".$sRootName."> </".$sRootName.">");
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


		/*	//For query debug purpose
		  	$myFile = "html.txt";
			$fh = fopen($myFile, 'w') or die("can't open file");
			fwrite($fh, str_replace ( '<?xml version="1.0"?>' , "" , $oDOMDocument->saveXML()));
			fclose($fh);*/

			return $bReport?$aReport:str_replace ( '<?xml version="1.0"?>' , "" , $oDOMDocument->saveXML());
		}
		return $bReport?$aReport:"";
	}



	protected function _preProcessHTML($sHTML)
	{

		$oDOMDocument = @DOMDocument::loadHTML($sHTML);
		//$oDOMDocument = @DOMDocument::loadXML(str_replace ( '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">' , "" , $x->saveHTML()));
		$xpath = new DOMXPath($oDOMDocument);
		$oRootElement = $oDOMDocument->documentElement;//->firstChild;
		$oRootElement->firstChild->nextSibling == null&&$oRootElement->tagName =='html'?$oRootElement=$oRootElement->firstChild:null;
		$sRootName = $oRootElement->tagName =='body'||$oRootElement->tagName =='html'?'div':$oRootElement->tagName;
	 	$x = DOMDocument::loadXML("<".$sRootName."> </".$sRootName.">");

	 	$oChildren = $oRootElement->childNodes;
		if ($oChildren!=null)
		{
			foreach ($oChildren as $node)
			{
				$node = $x->importNode($node, true);
				$x->documentElement->appendChild($node);
			}

		}

 		return str_replace ( '<?xml version="1.0"?>' , "" , $x->saveXML());
	}



	public static function toText($sHTML)
	{

		return !empty($sHTML) && trim($sHTML)!='' && $sHTML!=null?self::_toText(DOMDocument::loadXML(self::processHTML($sHTML))->documentElement, array()):array();
	}

	protected static function _toText($oNode, $aTextArray, $sParentTagName = null, $iListCount = null)
	{
		//$oNode = $oNode ==null?DOMDocument::loadXML($this->getHTML(true))->documentElement:$oNode;
		$oNode->tagName !='ol' && $sParentTagName!='ol'?$iListCount=0:null;
		$x = $oNode->childNodes;

		if ($x!=null)
		{
			foreach ($x as $node)
			{

				if ($node->tagName == 'li')
				{
					$sListChar 	= $oNode->tagName =='ul'?"\t* ":($oNode->tagName=='ol'?"\t".++$iListCount.". ":null);
					$aTextArray[] = $sListChar;
				}


				if (get_class($node) == 'DOMText')
				{
					if (trim($node->wholeText)!=null)
					{
						$aTextArray[]=trim($node->wholeText);
					}
				}
				else if ($node->tagName == 'variable')
				{
					$oAttributes 	= $node->attributes;
					$oObject 		= $oAttributes->getNamedItem('object');
					$oField 		= $oAttributes->getNamedItem('field');

					$sPad = null;

					$sBreak;
					if ($node->nextSibling->parentNode === $node->parentNode )
					{
						$sBreak = " ";
					}


					$aTextArray[] = $sPad."{".$oObject->value.".".$oField->value."}$sBreak";
				}
				else
				{

					$aTextArray = self::_toText($node, $aTextArray, $oNode->tagName, $iListCount);
				}
			}


					if ($oNode->tagName == 'p' || $oNode->tagName == 'br' ||$oNode->tagName == 'div' ||$oNode->tagName == 'h1' ||$oNode->tagName == 'h2' ||$oNode->tagName == 'h3'||$oNode->tagName == 'h4'||$oNode->tagName == 'form' ||$oNode->tagName == 'table')
					{
						$aTextArray[] = "\n\n";
					}
					else if ($oNode->tagName == 'li' ||$oNode->tagName == 'ul' ||$oNode->tagName == 'ol' ||$oNode->tagName == 'tr')
					{

						$aTextArray[] = "\n";

					}
					else if ($oNode->tagName == 'td' || $oNode->tagName == 'th')
					{
						$aTextArray[] = "\t\t";
					}
					else if ($oNode->tagName == 'span' || $oNode->tagName == 'a')
					{
						$aTextArray[] = " ";
					}

		}

		return $aTextArray;
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

	public static function sendTestEmail($aData)
	{
		$oEmail	= new Email_Flex();

		$oEmail->setBodyText($aData['text']);

		if ($aData['html']!=null && trim($aData['html'])!='')
		{
			$oEmail->setBodyHtml(self::processHTML($aData['html'], false, true));
		}

		$oEmail->setSubject($aData['subject']);

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



	public function __get($sField)
	{
		return $sField == "_aVariables"?$this->getVariables():null;
	}

}