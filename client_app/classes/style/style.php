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
 			$this->xslContent = new dataArray ("response");
 		}
 		
 		public function attachObject (&$dataObject)
		{
 			if (!is_subclass_of ($dataObject, 'data'))
 			{
 				throw new Exception
				(
					"The parameter passed to attach an object to transform was not inherited from `data`." .
					"<pre>" . print_r ($dataObject, TRUE)
				);
 			}
 			
 			return $this->xslContent->Push ($dataObject);
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
