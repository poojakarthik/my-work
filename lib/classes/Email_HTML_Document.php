<?php
class Email_HTML_Document
{

	protected $_oDOMDocument;
	protected $_aText = array();
	protected $_iOLCount;
	protected $_lastParent;



	public function __construct($sHTML)
	{

		$sHTML = str_replace ( 'xmlns="http://www.w3.org/1999/xhtml"' , "" , $sHTML);
		$this->_oDOMDocument = DOMDocument::loadXML($sHTML);
		$this->_sHTML = $this->_processHTML();
		$this->_toText();
	}

	public function getHTML()
	{
		return $this->_oDOMDocument->saveXML();
	}

	public function getText()
	{
		return $this->_aText;
	}

	protected function _processHTML()
	{

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

		 $result = $xpath->query("//script");
		  foreach ($result as $node)
		 {
		 	$node->parentNode->removeChild($node);
		 }


	}

	protected function _toText($oNode = null, $tagName = null)
	{
		$oNode = $oNode ==null?$this->_oDOMDocument->documentElement:$oNode;
		$tagName==null?$this->_iOLCount = 0:null;
		$x = $oNode->childNodes;
		if ($x!=null)
		{
			foreach ($x as $node)
			{
				if (get_class($node) == 'DOMText')
				{
					if (trim($node->wholeText)!=null)
					{
						if ($this->_lastParent->tagName == 'li' && ($node->parentNode->tagName!='li' || !($node->parentNode->parentNode===$this->_lastParent->parentNode)))
							$this->_aText[count($this->_aText)-1]= $this->_aText[count($this->_aText)-1]."\n";

						$sListChar = "";
						if (!($node->parentNode === $this->_lastParent))
						{
							$sListChar 	= $tagName=='ul'?"\t* ":($tagName=='ol'?"\t".++$this->_iOLCount." ":null);
						}


						$sBreak		= "\n\n";
						if ($node->parentNode->tagName == 'li')
						{
							$sBreak		= "\n";
						}
						$this->_aText[]=$sListChar.trim($node->wholeText).$sBreak;
						$this->_lastParent = $node->parentNode;
					}
				}
				else if ($node->tagName == 'variable')
				{
					$oAttributes 	= $node->attributes;
					$oObject 		= $oAttributes->getNamedItem('object');
					$oField 		= $oAttributes->getNamedItem('field');
					if ($node->parentNode === $this->_lastParent)
						$this->_aText[count($this->_aText)-1]= rtrim($this->_aText[count($this->_aText)-1])." ";


					$sPad = null;
					if (($node->previousSibling == null || $node->previousSibling->wholeText == " ") && $node->parentNode->tagName == 'li')
					{
						$sPad = $tagName=='ul'?"\t* ":($tagName=='ol'?"\t".++$this->_iOLCount." ":null);
						$this->_lastParent = $node->parentNode;
					}

					if ($node->nextSibling->parentNode === $node->parentNode )
					{
						$sBreak = " ";
					}
					else if ($node->parentNode->tagName == 'li' && !($node->parentNode->parentNode->lastChild === $node->parentNode))
					{
						$sBreak = "\n";
					}
					else
					{
						$sBreak		= "\n\n";
					}

					$this->_aText[] = $sPad."{".$oObject->value.".".$oField->value."}$sBreak";
				}
				else
				{
					//$oNode->tagName == 'ul'||$oNode->tagName=='ol'?$this->_toText($node,$oNode->tagName ):$this->_toText($node, $oNode->tagName) ;
					$this->_toText($node,$oNode->tagName );
				}
			}
		}
	}




}