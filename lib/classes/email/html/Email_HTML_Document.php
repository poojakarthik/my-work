<?php
class Email_HTML_Document
{

	protected $_sHTML;
	protected $_oDOMDocument;
	protected $_aText = array();
	protected $_iOLCount;
	protected $_lastParent;

	protected static $_aVariables = array(
											'CustomerGroup'	=>array('external_name', 'customer_service_phone'),
											'Account'		=>array('BusinessName', 'ABN')
										);

	protected $_aReport = array(
								'javascript' 	=>array(),
								'events'		=>array(),
								'form'			=>array(),
								'input'			=>array()
							);


	public function __construct($sHTML)
	{

		$sHTML = str_replace ( 'xmlns="http://www.w3.org/1999/xhtml"' , "" , $sHTML);

		$this->_preProcessHTML($sHTML);
		$this->_toText();
	}

	public function getHTML($bProcess = false)
	{
		return $bProcess?$this->_processHTML():$this->_sHTML;
	}

	public function getText()
	{
		return $this->_aText;
	}

	protected function _preProcessHTML($sHTML)
	{

		$this->_oDOMDocument = @DOMDocument::loadHTML($sHTML);
		//$this->_oDOMDocument = @DOMDocument::loadXML(str_replace ( '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">' , "" , $x->saveHTML()));
		$xpath = new DOMXPath($this->_oDOMDocument);
		$oRootElement = $this->_oDOMDocument->documentElement;//->firstChild;
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


 		$this->_sHTML = str_replace ( '<?xml version="1.0"?>' , "" , $x->saveXML());

	}

	public function getProcessReport()
	{
		$this->_processHTML();
		return $this->_aReport;
	}

	protected function _processHTML()
	{

		$this->_oDOMDocument = DOMDocument::loadXML($this->_sHTML);
		$xpath = new DOMXPath($this->_oDOMDocument);

        $query = '//cssclass';
        $result = $xpath->query($query);

		$aStyles = array();
		 foreach ($result as $node)
		 {
		 	foreach ($node->attributes as $attrName => $attrNode)
		  	{
		  		if ($attrName == 'name')
		  		{
		  			$sName = $attrNode->value;
		  		}

		  		if ($attrName == 'style')
		  		{
		  			$sStyle = $attrNode->value;
		  		}
			}
			$aStyles[$sName] = $sStyle;
			$node->parentNode->removeChild($node);
		}

		 foreach ($aStyles as $sSelector=>$sStyle)
		 {
		 	$oElements = $xpath->query("//*[@class = '".$sSelector."']");
		 	foreach ($oElements as $oElement)
		 	{
		 		$oElement->setAttribute('style',$sStyle);
		 	}
		 }

		  $query = '//css';
        $result = $xpath->query($query);
		//require_once('C:\wamp\www\CssSelector\CSSParser.php');
        foreach($result as $node)
        {
        	$sSelector = $node->getAttributeNode('selector')->value;
        	$sXpath = CSS_Parser::cssToXpath($sSelector);
        	$sStyle = $node->textContent;
        	$nodesToStyle = $xpath->query($sXpath);
        	foreach ($nodesToStyle as $nodeToStyle)
        	{
        		$nodeToStyle->setAttribute('style', $sStyle);
        	}
			$node->parentNode->removeChild($node);
        }





		 $result = $xpath->query("//script");
		  foreach ($result as $node)
		 {
		 	$this->_aReport['javascript'][] =  $node->textContent;
		 	$node->parentNode->removeChild($node);
		 }

		//$oElements = $xpath->query("//*[@onclick]");
		//foreach ($oElements as $oElement)
	 	//{
	 	//	$oElement->removeAttribute('onclick');
	 	//}

	 	$oElements = $xpath->query("//*[@*]");
		foreach ($oElements as $oElement)
	 	{
	 		foreach ($oElement->attributes as $attrName => $attrNode)
		  	{
		  		if (strpos($attrName, 'on') === 0)
		  		{
		  			$this->_aReport['events'][]= $node->textContent;
		  			$oElement->removeAttribute($attrName);
		  		}

		  	}
	 	}

	 	/*remove form related things*/
		$result = $xpath->query("//input");
		  foreach ($result as $node)
		 {
		 	$this->_aReport['input'][]=$node->textContent;
		 	$node->parentNode->removeChild($node);
		 }

		$result = $xpath->query("//form");
		foreach ($result as $node)
		{
			foreach ($node->attributes as $attrName => $attrNode)
		  	{
		  		if ($attrName == 'action' || $attrName == 'method')
		  		{
		  			$this->_aReport['form'][]=$node->textContent;
		  			$node->removeAttribute($attrName);
		  		}
		  	}
		 }

		 return str_replace ( '<?xml version="1.0"?>' , "" , $this->_oDOMDocument->saveXML());

	}

	protected function _toText($oNode = null, $tagName = null)
	{
		$oNode = $oNode ==null?DOMDocument::loadXML($this->getHTML(true))->documentElement:$oNode;
		$tagName!='ol'?$this->_iOLCount = 0:null;
		$x = $oNode->childNodes;
		if ($oNode->tagName == 'li')
		{
			$sListChar 	= $tagName=='ul'?"\t* ":($tagName=='ol'?"\t".++$this->_iOLCount." ":null);
			$this->_aText[] = $sListChar;
		}
		if ($x!=null)
		{
			foreach ($x as $node)
			{

				if (get_class($node) == 'DOMText')
				{
					if (trim($node->wholeText)!=null)
					{
						//if ($this->_lastParent->tagName == 'li' && ($node->parentNode->tagName!='li' || !($node->parentNode->parentNode===$this->_lastParent->parentNode)))
						//	$this->_aText[count($this->_aText)-1]= $this->_aText[count($this->_aText)-1]."\n";

						$sListChar = "";
						//if (!($node->parentNode === $this->_lastParent))
						//{
						//	$sListChar 	= $tagName=='ul'?"\t* ":($tagName=='ol'?"\t".++$this->_iOLCount." ":null);
					//	}


						//$sBreak		= "\n\n";
						//if ($node->parentNode->tagName == 'li')
						//{
						//	$sBreak		= "\n";
						//}
						$this->_aText[]=$sListChar.ltrim($node->wholeText);//.$sBreak;
						$this->_lastParent = $node->parentNode;
					}
					else
					{
						$this->_aText[count($this->_aText)-1]= rtrim($this->_aText[count($this->_aText)-1]);
					}
				}
				else if ($node->tagName == 'variable')
				{
					$oAttributes 	= $node->attributes;
					$oObject 		= $oAttributes->getNamedItem('object');
					$oField 		= $oAttributes->getNamedItem('field');
					//if ($node->parentNode === $this->_lastParent)
					//	$this->_aText[count($this->_aText)-1]= rtrim($this->_aText[count($this->_aText)-1])." ";


					$sPad = null;
					if (($node->previousSibling == null || $node->previousSibling->wholeText == " ") && $node->parentNode->tagName == 'li')
					{
						//$sPad = $tagName=='ul'?"\t* ":($tagName=='ol'?"\t".++$this->_iOLCount." ":null);
						$this->_lastParent = $node->parentNode;
					}

					if ($node->nextSibling->parentNode === $node->parentNode )
					{
						$sBreak = " ";
					}
					else if ($node->parentNode->tagName == 'li' && !($node->parentNode->parentNode->lastChild === $node->parentNode))
					{
						$sBreak = "";//\n";
					}
					else
					{
						$sBreak		= ""; //\n\n";
					}

					$this->_aText[] = $sPad."{".$oObject->value.".".$oField->value."}$sBreak";
				}
				else
				{
					//$oNode->tagName == 'ul'||$oNode->tagName=='ol'?$this->_toText($node,$oNode->tagName ):$this->_toText($node, $oNode->tagName) ;



					$this->_toText($node,$oNode->tagName );
				}
			}

					if ($oNode->tagName == 'p' || $oNode->tagName == 'br' ||$oNode->tagName == 'div' ||$oNode->tagName == 'h1' ||$oNode->tagName == 'h2' ||$oNode->tagName == 'form' ||$oNode->tagName == 'table')
					{
						$this->_aText[] = "\n\n";
					}
					else if ($oNode->tagName == 'li' ||$oNode->tagName == 'ul' ||$oNode->tagName == 'ol' ||$oNode->tagName == 'tr')
					{

						$this->_aText[] = "\n";

					}
					else if ($oNode->tagName == 'td' || $oNode->tagName == 'th')
					{
						$this->_aText[] = "\t\t";
					}
					else if ($oNode->tagName == 'span' || $oNode->tagName == 'a')
					{
						$this->_aText[] = " ";
					}
		}
	}

	public static function getVariables()
	{
		return self::$_aVariables;
	}

	public static function domNodeList_to_string($DomNodeList) {
	    $output = '';
	    $doc = new DOMDocument;
	    while ( $node = $DomNodeList->item($i) ) {
	        // import node
	        $domNode = $doc->importNode($node, true);
	        // append node
	        $doc->appendChild($domNode);
	        $i++;
	    }
	    $output = $doc->saveXML();
	    $output = print_r($output, 1);
	    // I added this because xml output and ajax do not like each others
	    $output = htmlspecialchars($output);
	    return $output;
	}




}