<?php

//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// style
//----------------------------------------------------------------------------//
/**
 * style
 *
 * Controls the processing of XSLT with phpObLib
 *
 * Controls the processing of XSLT with phpObLib
 *
 * @file		classes/style/style.php
 * @language	PHP
 * @package		client_app
 * @author		Bashkim 'bash' Isai
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 */

 	class style 
 	{
 		
 		private $xslContent;
 		private $strApplicationDir;
 		
 		function __construct (&$strApplicationDir)
 		{
 			$this->strApplicationDir =& $strApplicationDir;
 			$this->xslContent = new dataArray ("Response");
 		}
 		
 		public function attachObject (&$dataObject)
		{
 			if (!is_subclass_of ($dataObject, 'data'))
 			{
 				throw new Exception
				(
					"Attaching Object on `Style` object failed because object is not inherit from `data`."
				);
 			}
 			
 			return $this->xslContent->Push ($dataObject);
 		}
		
		public function __toString ()
		{
			return '<pre>' . htmlentities ($this->xslContent->Output ()->SaveXML ()) . '</pre>';
		}
 		
 		public function Output ($strXSLFilename)
 		{
 			$xslDocument = new DOMDocument;
 			$xslDocument->load ($this->strApplicationDir . $strXSLFilename);
 			
 			$xslProcessor = new XSLTProcessor;
 			$xslProcessor->importStyleSheet ($xslDocument);
 			
 			echo $xslProcessor->transformToXML ($this->xslContent->Output ());
 		}
 	}
 	
?>
