<?php

class JSON_Handler_Email_Text_Editor extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	protected $xml;
	protected $_aText = array();
	protected $_iOLCount;

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
		$sHTML = $this->_processHTML($sHTML);
		$this->_toText();

		return	array(
						'Success'		=> true,
						'text'		=> implode("\n",$this->_aText)
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
			$this->removeNode($node);
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
		 	//$this->xml->removeChild($node);
		 	$this->removeNode($node);
		 }

		return $this->xml->saveXML();
	}

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
						$sListChar = $tagName=='ul'?'* ':($tagName=='ol'?++$this->_iOLCount." ":null);

						$this->_aText[]=$sListChar.trim($node->wholeText);
					}

				}
				else
				{

					$oNode->tagName == 'ul'||$oNode->tagName=='ol'?$this->_toText($node,$oNode->tagName ):$this->_toText($node) ;

				}
			}
		}

		//$oNode->nextSibling == null?null:$this->_toText($oNode->nextSibling);

	}


}



?>