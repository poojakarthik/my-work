<?php

class JSON_Handler_Email_Text_Editor extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	protected $xml;
	protected $_aText = array();
	protected $_iOLCount;
	protected $_lastParent;

	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}

	public function save($aEmailText)
	{
		return	array(
						'Success'		=> true,
						'html'		=> $aEmailText
					);
	}

	public function toText($sHTML)
	{
		$sHTML = $this->_processHTML($sHTML, true);
		$this->_toText();

		return	array(
						'Success'		=> true,
						'text'		=> implode("",$this->_aText)
					);

	}

	public function processHTML($sHTML)
	{
		$this->_processHTML($sHTML);


		return	array(
						'Success'		=> true,
						'html'		=> $this->xml->saveXML()
					);
	}

	protected function _processHTML($sHTML)
	{
		$sHTML = str_replace ( 'xmlns="http://www.w3.org/1999/xhtml"' , "" , $sHTML);

		$this->xml = DOMDocument::loadXML($sHTML);
		$xpath = new DOMXPath($this->xml);

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

		return $this->xml->saveXML();
	}

	public function _toText($oNode = null, $tagName = null)
	{
		$oNode = $oNode ==null?$this->xml->documentElement:$oNode;
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

						$sListChar 	= $tagName=='ul'?"\t* ":($tagName=='ol'?"\t".++$this->_iOLCount." ":null);

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
					if ($node->parentNode == $this->_lastParent)
						$this->_aText[count($this->_aText)-1]= rtrim($this->_aText[count($this->_aText)-1])." ";

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

					$this->_aText[] = "{".$oObject->value.".".$oField->value."}$sBreak";
				}
				else
				{
					//$oNode->tagName == 'ul'||$oNode->tagName=='ol'?$this->_toText($node,$oNode->tagName ):$this->_toText($node, $oNode->tagName) ;
					$this->_toText($node,$oNode->tagName );
				}
			}
		}
	}

	//this does the job, but it looks like using $node->parentNode->removeChild($node) works just as well, and much simpler!
	public function removeNode($oNodeToRemove, $parentNode = null)
	{
		$parentNode = $parentNode ==null?$this->xml->documentElement:$parentNode;
		try
		{

			$x = $parentNode->tagName;
			if ($parentNode->removeChild($oNodeToRemove))
			{
				return true;
			}
			else
			{
				throw new Exception();
			}
		}
		catch (Exception $e)
		{
			$x = $parentNode->childNodes;
			if ($x!=null)
			{
				foreach ($x as $node)
				{
					if ($this->removeNode($oNodeToRemove,$node ))
					{
						return true;
					}
				}
			}
		}

		return false;

	}


}



?>