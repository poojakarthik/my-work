<?php

class JSON_Handler_Email_Text_Editor extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	protected $xml;

	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}

	public function processHTML($sHTML)
	{
$sHTML = str_replace ( 'xmlns="http://www.w3.org/1999/xhtml"' , "" , $sHTML);

	/*$sHTML = " <div>
					<cssclass name = 'div' style = 'background: yellow; color: #00ff00; margin-left: 2cm'></cssclass>

					<div>
						<cssclass name = 'h1' style = 'background: yellow; color: #00ff00; margin-left: 2cm'></cssclass>
					  <h1>text</h1>
					  <h2>stuff</h2>
					 </div>
					  <p>code</p>
					 <script>
					 alert('hello');
					 </script>
					</div>";*/

//$root = $xml->documentElement;
//
//foreach ($root->attributes as $attrName => $attrNode)
//{
//	$root->removeAttribute ( $attrName );
//}

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


		return	array(
						'Success'		=> true,
						'html'		=> $this->xml->saveXML()
					);
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


}



?>