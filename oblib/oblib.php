<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006-7 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// oblib
//----------------------------------------------------------------------------//
/**
 * oblib
 *
 * Handles loading of applications
 *
 * Loads the base classes and sets up the application framework
 *
 * @file		application_loader.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis, Bashkim Isai
 * @version		7.01
 * @copyright	2006-7 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 

//----------------------------------------------------------------------------//
// data.abstract
//----------------------------------------------------------------------------//
	abstract class data
	{
		
		protected $_DOMDocument;
		protected $_DOMElement;
		
		function __construct ($nodeTag)
		{
			$this->_DOMDocument = new DOMDocument ('1.0', 'utf-8');
			$this->_DOMElement = new DOMElement ($nodeTag);
			$this->_DOMDocument->formatOutput = true;
			
			$this->_DOMDocument->appendChild
			(
				$this->_DOMElement
			);
		}
		
		public function tagName ()
		{
			return $this->_DOMElement->tagName;
		}
		
		public function setAttribute ($strAttributeName, $strAttributeValue)
		{
			$this->_DOMElement->setAttribute ($strAttributeName, $strAttributeValue);
		}
		
		public function getAttribute ($strAttributeName)
		{
			return $this->_DOMElement->getAttribute ($strAttributeName);
		}
		
		public function removeAttribute ($strAttributeName)
		{
			return $this->_DOMElement->removeAttribute ($strAttributeName);
		}
		
		public function __toString ()
		{
			return '<pre>' . htmlentities ($this->Output ()->SaveXML ()) . '</pre>';
		}
		
		abstract public function Output ();
	}
	
//----------------------------------------------------------------------------//
// dataPrimitive.abstract
//----------------------------------------------------------------------------//
	abstract class dataPrimitive extends data
	{
		
		protected $_DOMNode;
		
		public $_sleepTagName;
		public $_sleepTagValue;
		
		function __construct ($tagName)
		{
			parent::__construct ($tagName);
			
			$this->_DOMNode = $this->_DOMDocument->createTextNode ("");
			$this->_DOMNode = $this->_DOMElement->appendChild ($this->_DOMNode);
		}
		
		public function getValue ()
		{
			return $this->_DOMNode->data;
		}
		
		public function setValue ($nodeValue)
		{
			$this->_DOMNode->replaceData
			(
				0, 
				$this->_DOMNode->length, $nodeValue
			);
			
			return true;
		}
		
		public function Output ()
		{
			return $this->_DOMDocument;
		}
		
		public function __sleep ()
		{
			$this->_sleepTagName = $this->tagName ();
			$this->_sleepTagValue = $this->_DOMNode->data;
			
			return Array (
				"_sleepTagName",
				"_sleepTagValue"
			);
		}
		
		public function __wakeup ()
		{
			$this->__construct (
				$this->_sleepTagName,
				$this->_sleepTagValue
			);
			
			$this->_sleepTagName = null;
			$this->_sleepTagValue = null;
		}
	}
	
//----------------------------------------------------------------------------//
// dataBoolean.class
//----------------------------------------------------------------------------//
	class dataBoolean extends dataPrimitive
	{
		
		function __construct ($nodeName, $nodeValue=false)
		{
			parent::__construct ($nodeName);
			$this->setValue ($nodeValue);
		}
		
		public function setValue ($nodeValue)
		{
			parent::setValue (($nodeValue == true) ? "1" : "0");
		}
		
		public function setTrue ()
		{
			$this->setValue (true);
		}
		
		public function setFalse ()
		{
			$this->setValue (false);
		}
		
		public function isTrue ()
		{
			return $this->getValue () == 1;
		}
		
		public function isFalse ()
		{
			return $this->getValue () == 0;
		}
	}
	
//----------------------------------------------------------------------------//
// dataFloat.class
//----------------------------------------------------------------------------//
	class dataFloat extends dataPrimitive
	{
		
		function __construct ($nodeName, $nodeValue=0)
		{
			parent::__construct ($nodeName);
			$this->setValue ($nodeValue);
		}
		
		public function setValue ($nodeValue)
		{
			$nodeValue = preg_replace ("/^\$/misU", "", $nodeValue);
			
			if (!is_numeric ($nodeValue))
			{
				return false;
			}
			
			return parent::setValue	("$" . sprintf ("%0.2f", floatval ($nodeValue)));
		}
	}
	
//----------------------------------------------------------------------------//
// dataInteger.class
//----------------------------------------------------------------------------//
	class dataInteger extends dataPrimitive
	{
		
		function __construct ($nodeName, $nodeValue=0)
		{
			parent::__construct ($nodeName);
			$this->setValue ($nodeValue);
		}
		
		public function setValue ($nodeValue)
		{
			if (!is_numeric ($nodeValue))
			{
				return;
			}
			
			return parent::setValue
			(
				intval
				(
					$nodeValue
				)
			);
		}
	}
	
//----------------------------------------------------------------------------//
// dataString.class
//----------------------------------------------------------------------------//
	class dataString extends dataPrimitive
	{
		
		function __construct ($nodeName, $nodeValue="")
		{
			parent::__construct ($nodeName);
			
			$this->_DOMNode = $this->_DOMDocument->createCDATASection ($nodeValue);
			$this->_DOMNode = $this->_DOMElement->appendChild ($this->_DOMNode);
		}
		
		public function setValue ($nodeValue)
		{
			if (!is_string ($nodeValue))
			{
				return false;
			}
			
			return parent::setValue
			(
				$nodeValue
			);
		}
	}
	
//----------------------------------------------------------------------------//
// dataDuration.class
//----------------------------------------------------------------------------//
	class dataDuration extends dataPrimitive
	{
		
		private $Hours;
		private $Minutes;
		private $Seconds;
		
		function __construct ($nodeName, $nodeValue)
		{
			parent::__construct ($nodeName, $nodeName);
			
			$this->setValue ($nodeValue);
		}
		
		public function setValue ($nodeValue)
		{
			$nodeValue = intval ($nodeValue);
			
			$Hours =	intval ($nodeValue / (60 * 60));
			$Minutes =	intval ($nodeValue / 60) - ($Hours * 60);
			$Seconds =	intval ($nodeValue) - ($Minutes * 60) - ($Hours * 60);
			
			parent::setValue (
				sprintf ("%02d", $Hours) . ":" . 
				sprintf ("%02d", $Minutes) . ":" . 
				sprintf ("%02d", $Seconds)
			);
		}
	}

//----------------------------------------------------------------------------//
// dataObject.abstract
//----------------------------------------------------------------------------//
	abstract class dataObject extends data
	{
		
		private $_DATA = Array ();
		
		public $_sleepTagName;
		public $_sleepObjectData;
		
		function __construct ($nodeName)
		{
			parent::__construct ($nodeName);
		}
		
		public function Push (data &$nodeItem)
		{
			if (!is_object ($nodeItem))
			{
				throw new Exception ("Passed item is not an object: " . $nodeItem);
			}
			
			if (!($nodeItem instanceOf data))
			{
				throw new Exception ("Passed item is not an inheritance of the data class: " . $nodeItem);
			}
			
			if (isset ($this->_DATA [$nodeItem->tagName ()]))
			{
				throw new Exception ("An object with the tag name you are passing already exists: " . $nodeItem);
			}
			
			$this->_DATA [$nodeItem->tagName ()] =& $nodeItem;
			
			return $this->_DATA [$nodeItem->tagName ()];
		}
		
		public function Pop ($nodeName)
		{
			$nodeItem = $this->_DATA [$nodeName];
			
			if ($nodeItem === null)
			{
				return null;
			}
			
			unset ($this->_DATA [$nodeName]);
			
			return $nodeItem;
		}
		
		public function Pull ($indexID)
		{
			return (isset ($this->_DATA [$indexID]) ? $this->_DATA [$indexID] : null);
		}
		
		public function Output ()
		{
			foreach ($this->_DATA AS $nodeItem)
			{
				$this->_DOMElement->appendChild
				(
					$this->_DOMDocument->importNode
					(
						$nodeItem->Output ()->documentElement, 
						true
					)
				);
			}
			
			return $this->_DOMDocument;
		}
		
		public function __sleep ()
		{
			$this->_sleepTagName = $this->tagName ();
			$this->_sleepObjectData = $_DATA;
			
			return Array (
				"_sleepTagName",
				"_sleepObjectData"
			);
		}
		
		public function __wakeup ()
		{
			$this->__construct (
				$this->_sleepTagName
			);
			
			$this->_DATA = Array ();
			
			if ($this->_sleepObjectData)
			{
				$this->_DATA = $this->_sleepObjectData;
			}
			
			unset ($this->_sleepTagName);
			unset ($this->_sleepObjectData);
		}
	}
	
//----------------------------------------------------------------------------//
// dataDate.class
//----------------------------------------------------------------------------//
	class dataDate extends dataObject
	{
		
		private $Year;
		private $Month;
		private $Day;
		
		function __construct ($nodeName, $nodeValue)
		{
			parent::__construct ($nodeName, $nodeName);
			
			$this->setValue ($nodeValue);
		}
		
		public function getValue ()
		{
			if ($this->Month && $this->Day && $this->Year)
			{
				return date ("Y-m-d", 
					mktime (
						0,
						0,
						0,
						$this->Month->getValue (),
						$this->Day->getValue (),
						$this->Year->getValue ()
					)
				);
			}
			
			return null;
		}
		
		public function setValue ($nodeValue)
		{
			if ($nodeValue == null || $nodeValue == "0000-00-00")
			{
				if ($this->Year) {
					$this->Pop ($this->Year);
				}
				
				if ($this->Month) {
					$this->Pop ($this->Month);
				}
				
				if ($this->Day) {
					$this->Pop ($this->Day);
				}
				
				return;
			}
			
			if (!is_string ($nodeValue))
			{
				return false;
			}
			
			if (!strtotime ($nodeValue))
			{
				return;
			}
			
			$nodeValue = strtotime ($nodeValue);
			
			$this->Year		= $this->Push (new dataString ("year",	date ("Y", $nodeValue)));
			$this->Month	= $this->Push (new dataString ("month",	date ("m", $nodeValue)));
			$this->Day		= $this->Push (new dataString ("day",	date ("d", $nodeValue)));
		}
	}

//----------------------------------------------------------------------------//
// dataTime.class
//----------------------------------------------------------------------------//
	class dataTime extends dataObject
	{
		
		private $Hour;
		private $Minute;
		private $Second;
		
		private $Timestamp;
		
		function __construct ($nodeName, $nodeValue)
		{
			parent::__construct ($nodeName, $nodeName);
			
			$this->Hour 		= $this->Push (new dataString ("hour", "00"));
			$this->Minute 		= $this->Push (new dataString ("minute", "00"));
			$this->Second 		= $this->Push (new dataString ("second", "00"));
			
			$this->Timestamp	= $this->Push (new dataString ("timestamp", ""));
			
			$this->setValue ($nodeValue);
		}
		
		public function getValue ()
		{
			return mktime (
				$this->Hour->getValue (),
				$this->Minute->getValue (),
				$this->Second->getValue ()
			);
		}
		
		public function setValue ($nodeValue)
		{
			if (!is_string ($nodeValue))
			{
				return false;
			}
			
			if (!strtotime ($nodeValue))
			{
				return;
			}
			
			$nodeValue = strtotime ($nodeValue);
			
			$this->Hour->setValue		(date ("H", $nodeValue));
			$this->Minute->setValue		(date ("i", $nodeValue));
			$this->Second->setValue		(date ("s", $nodeValue));
			
			$this->Timestamp->setValue	(date ("H:i:s", $nodeValue));
		}
	}

//----------------------------------------------------------------------------//
// dataDatetime.class
//----------------------------------------------------------------------------//
	class dataDatetime extends dataObject
	{
		
		private $Year;
		private $Month;
		private $Day;
		
		private $Hour;
		private $Minute;
		private $Second;
		
		private $Timestamp;
		
		function __construct ($nodeName, $nodeValue)
		{
			parent::__construct ($nodeName, $nodeName);
			
			$this->Year 		= $this->Push (new dataString ("year", "00"));
			$this->Month 		= $this->Push (new dataString ("month", "00"));
			$this->Day			= $this->Push (new dataString ("day", "00"));
			
			$this->Hour 		= $this->Push (new dataString ("hour", "00"));
			$this->Minute 		= $this->Push (new dataString ("minute", "00"));
			$this->Second 		= $this->Push (new dataString ("second", "00"));
			
			$this->Timestamp	= $this->Push (new dataString ("timestamp", ""));
			
			$this->setValue ($nodeValue);
		}
		
		public function getValue ()
		{
			return mktime (
				$this->Hour->getValue (),
				$this->Minute->getValue (),
				$this->Second->getValue (),
				$this->Month->getValue (),
				$this->Day->getValue (),
				$this->Year->getValue ()
			);
		}
		
		public function setValue ($nodeValue)
		{
			if (!is_string ($nodeValue))
			{
				return false;
			}
			
			if (!strtotime ($nodeValue))
			{
				return;
			}
			
			$nodeValue = strtotime ($nodeValue);
			
			$this->Year->setValue		(date ("Y", $nodeValue));
			$this->Month->setValue		(date ("m", $nodeValue));
			$this->Day->setValue		(date ("d", $nodeValue));
			
			$this->Hour->setValue		(date ("H", $nodeValue));
			$this->Minute->setValue		(date ("i", $nodeValue));
			$this->Second->setValue		(date ("s", $nodeValue));
			
			$this->Timestamp->setValue	(date ("Y-m-d", $nodeValue) . "T" . date ("H:i:s", $nodeValue));
		}
	}

//----------------------------------------------------------------------------//
// dataArray.class
//----------------------------------------------------------------------------//
	class dataArray extends data implements Iterator
	{
	
		private $nodeType;
		
		private $_DATA = Array ();
		
		
		public $_sleepTagName;
		public $_sleepArrayType;
		public $_sleepArrayData;
		
		function __construct ($nodeName, $nodeType=null)
		{
			parent::__construct ($nodeName);
			
			if ($nodeType !== null && !class_exists ($nodeType))
			{
				throw new Exception ('Class does not exist: ' . $nodeType);
			}
			
			if ($nodeType !== null && !(is_subclass_of ($nodeType, 'data')))
			{
				throw new Exception ('Class is not inheritance of data: ' . $nodeType);
			}
	
			$this->nodeType = ($nodeType === null) ? "data" : $nodeType;
		}
		
		public function Push (&$arrayItem)
		{
			if (!is_object ($arrayItem))
			{
				throw new Exception ('Variable is not an object: ' . $arrayItem);
			}
			
			if (!($arrayItem instanceOf $this->nodeType))
			{
				throw new Exception ('Variable is not an instance of ' . $this->nodeType . ': ' . $arrayItem);
			}
			
			return $this->_DATA [] =& $arrayItem;
		}
		
		public function Pop (&$arrayItem)
		{
			foreach ($this->_DATA AS $index => &$_DATA)
			{
				if ($_DATA === $arrayItem)
				{
					unset ($this->_DATA [$index]);
					return;
				}
			}
		}
			
		public function Pull ($indexID)
		{
		}
			
		public function Output ()
		{
			foreach ($this->_DATA AS $arrItem)
			{
				$this->_DOMElement->appendChild
				(
					$this->_DOMDocument->importNode
					(
						$arrItem->Output ()->documentElement, 
						true
					)
				);
			}
		
			return $this->_DOMDocument;
		}
		
		private $Valid = false;
		
		public function current ()
		{
			return current ($this->_DATA);
		}
		
		public function key ()
		{
			return key ($this->_DATA);
		}
		
		public function next ()
		{
			$this->Valid = (next ($this->_DATA) !== false);
		}
		
		public function rewind ()
		{
			$this->Valid = (reset ($this->_DATA) !== false);
		}
		
		public function valid ()
		{
			return $this->Valid;
		}
		

		
		public function __sleep ()
		{
			$this->_sleepTagName = $this->tagName ();
			$this->_sleepArrayType = ($this->nodeType === 'data') ? null : $this->nodeType;
			$this->_sleepArrayData = $this->_DATA;
			
			return Array (
				"_sleepTagName",
				"_sleepArrayType",
				"_sleepArrayData"
			);
		}
		
		public function __wakeup ()
		{
			$this->__construct (
				$this->_sleepTagName,
				$this->_sleepArrayType
			);
			
			$this->_DATA = $this->_sleepArrayData;
			
			unset ($this->_sleepTagName);
			unset ($this->_sleepArrayType);
			unset ($this->_sleepArrayData);
		}
	}

//----------------------------------------------------------------------------//
// dataCollation.abstract
//----------------------------------------------------------------------------//
	abstract class dataCollation extends data
	{
		
		private $nodeType;
		private $collationLength;
		
		private $arrSample;
		
		private $_DATA = Array ();
		
		function __construct ($nodeName, $nodeType=null, $collationLength)
		{
			parent::__construct ($nodeName);
			
			if (!is_numeric ($collationLength))
			{
				throw new Exception ('Collation Length is not a Numerical Value:' . $collationLength);
			}
			
			$this->collationLength = intval ($collationLength);
			
			if ($nodeType !== null)
			{
				if (!class_exists ($nodeType))
				{
					throw new Exception ('Class does not exist: ' . $nodeType);
				}
				
				if (!($nodeType instanceOf data) && !(is_subclass_of ($nodeType, 'data')))
				{
					throw new Exception ('Class is not inheritance of data: ' . $nodeType);
				}
				
				$this->nodeType = $nodeType;
			}
		}
		
		abstract public function ItemIndex ($indexID);
		
		public function Sample ($rangePage=1, $rangeLength=null)
		{
			$this->arrSample = new dataSample (
				$this, 
				$this->tagName (),
				$this->nodeType,
				$this->collationLength,
				$rangePage,
				$rangeLength
			);
			
			return $this->arrSample;
		}
		
		protected function &Push (&$itemObj)
		{
			$this->_DATA [] =& $itemObj;
			return $itemObj;
		}
		
		protected function Pop ($itemID)
		{
			unset ($this->_DATA [$itemID]);
		}
		
		protected function Pull ($itemID)
		{
			return isset ($this->_DATA [$itemID]) ? $this->_DATA [$itemID] : null;
		}
		
		public function Output ()
		{
			return $this->Sample ()->Output ();
		}
	}

//----------------------------------------------------------------------------//
// dataCollection.abstract
//----------------------------------------------------------------------------//
	abstract class dataCollection extends data
	{
		
		protected $_DATA = Array ();
		
		private $nodeType;
		
		function __construct ($nodeName, $nodeType=null)
		{
			parent::__construct ($nodeName);
			
			if ($nodeType !== null)
			{
				if (!is_subclass_of ($nodeType, 'data'))
				{
					throw new Exception ('could not load datacollection');
				}
				
				$this->nodeType = $nodeType;
			}
		}
		
		protected function Push (&$itemObj)
		{
			if (($this->nodeType !== null && (is_subclass_of ($this->nodeType, $itemObj) || $itemObj instanceOf $this->nodeType)) || $this->nodeType === null)
			{
				$this->_DATA [] =& $itemObj;
				return $itemObj;
			}
			
			return null;
		}
		
		protected function Pop (&$itemObj)
		{
			foreach ($this->_DATA as $id => &$_DATA)
			{
				if ($_DATA === $itemObj)
				{
					unset ($this->_DATA [$id]);
					return true;
				}
			}
			
			return false;
		}
		
		protected function Pull ($itemObj)
		{
			foreach ($this->_DATA AS &$_DATA)
			{
				if ($_DATA === $itemObj)
				{
					return $_DATA;
				}
			}
			
			return null;
		}
		
		public function Output ()
		{
			foreach ($this->_DATA AS $arrayItem)
			{
				$this->_DOMElement->appendChild
				(
					$this->_DOMDocument->importNode
					(
						$arrayItem->Output ()->documentElement, true
					)
				);
			}
			
			return $this->_DOMDocument;
		}
	}

//----------------------------------------------------------------------------//
// dataEnumerative.abstract
//----------------------------------------------------------------------------//
	abstract class dataEnumerative extends dataCollection
	{
		
		public function __construct ($nodeName)
		{
			parent::__construct ($nodeName);
		}
		
		protected function Select (&$selectedItem)
		{
			if (!$this->Pull ($selectedItem))
			{
				return null;
			}
			
			foreach ($this->_DATA AS &$_DATA)
			{
				$_DATA->removeAttribute ('selected');
			}
			
			$selectedItem->setAttribute ('selected', 'selected');
		}
	}

//----------------------------------------------------------------------------//
// dataSample.class
//----------------------------------------------------------------------------//
	class dataSample extends dataObject implements iterator
	{
		
		private $_COLLATION;
		
		private $nodeName;
		private $nodeType;
		
		private $collationLength;
		
		private $rangePage;
		private $rangePages;
		
		private $rangeStart;
		private $rangeLength;
		
		private $_DATA;
		
		function __construct (&$_COLLATION, $nodeName, $nodeType, $collationLength, $rangePage=1, $rangeLength=null)
		{
			parent::__construct ($nodeName, $rangePage);
			
			if ($nodeType !== null)
			{
				if (!is_subclass_of ($nodeType, 'data'))
				{
					throw new Exception ('Collation Node Type not instance of Data: ' . $nodeType);
				}
			}
			
			if ($rangeLength === null)
			{
				$rangeLength = $collationLength;
			}
			
			$this->_COLLATION =& $_COLLATION;
			
			$this->collationLength = $this->Push (new dataInteger ('collationLength', $collationLength));
			
			$this->rangePage = $this->Push (new dataInteger ('rangePage', $rangePage));
			$this->rangePages = $this->Push (new dataInteger ('rangePages', ($rangeLength <> 0 && $collationLength <> 0) ? ceil ($collationLength / $rangeLength) : 0));
			
			$this->rangeStart = $this->Push (new dataInteger ('rangeStart', ($rangePage * $rangeLength) - $rangeLength));
			$this->rangeLength = $this->Push (new dataInteger ('rangeLength', $rangeLength));
			
			$this->_DATA = new dataArray ('rangeSample', $nodeType);
			$this->Push ($this->_DATA);
			
			if (method_exists ($this->_COLLATION, 'ItemList'))
			{
				$ItemList = $this->_COLLATION->ItemList (
					$this->rangeStart->getValue (),
					$this->rangeLength->getValue ()
				);
				
				foreach ($ItemList as &$Item)
				{
					$this->_DATA->Push ($Item);
				}
			}
			else
			{
				$_ITEM = Array ();
				
				for ($i=0; $i < $this->rangeLength->getValue (); ++$i)
				{
					$_ITEM [$i] = $this->_COLLATION->ItemIndex ($i + $this->rangeStart->getValue ());
					
					if ($_ITEM [$i] !== null)
					{
						$this->_DATA->Push ($_ITEM [$i]);
					}
				}
			}
		}
		
		public function Count ()
		{
			return $this->collationLength->getValue ();
		}
		
		private $Valid = false;
		
		public function current ()
		{
			return $this->_DATA->current ();
		}
		
		public function key ()
		{
			return $this->_DATA->key ();
		}
		
		public function next ()
		{
			$this->_DATA->next ();
		}
		
		public function rewind ()
		{
			$this->_DATA->rewind ();
		}
		
		public function valid ()
		{
			return $this->_DATA->Valid ();
		}
	}
	
//----------------------------------------------------------------------------//
// style
//----------------------------------------------------------------------------//
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
 			$xslDocument = new DOMDocument ('1.0', 'utf-8');
 			$xslDocument->load ($this->strApplicationDir . $strXSLFilename);
 			
 			$xslProcessor = new XSLTProcessor;
 			$xslProcessor->importStyleSheet ($xslDocument);
 			
 			echo $xslProcessor->transformToXML ($this->xslContent->Output ());
 		}
 	}

	//----------------------------------------------------------------------------//
	// ABN
	//----------------------------------------------------------------------------//
	/**
	 * ABN
	 *
	 * ABN Number Validation/Parsing
	 *
	 * Controls validation of ABN Numbers
	 *
	 *
	 * @prefix	abn
	 *
	 * @package		intranet_app
	 * @class		ABN
	 * @extends		dataPrimitive
	 */
	
	class ABN extends dataPrimitive
	{
		
		public static $arrWeights = Array (
			0	=> "10",
			1	=> "1",
			2	=> "3",
			3	=> "5",
			4	=> "7",
			5	=> "9",
			6	=> "11",
			7	=> "13",
			8	=> "15",
			9	=> "17",
			10	=> "19"
		);
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new ABN
		 *
		 * Constructor for a new ABN
		 *
		 * @param	String		$strName		The name of the ABN String
		 * @param	String		$strABN			The initial value of the ABN being set
		 *
		 * @method
		 */
		
		function __construct ($strName, $strABN)
		{
			// Construct the object
			parent::__construct ($strName);
			
			$this->setValue ($strABN);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Value of the ABN
		 *
		 * Change the Value of the ABN
		 *
		 * @param	String		$strABN			The value of the ABN being set
		 * @return	Boolean						Whether the Number is Valid or not. Invalid numbers are not subscribed.
		 *
		 * @method
		 */
		
		public function setValue ($strABN)
		{
			// 1. If the length is 0, it is valid because we might not have an ABN
			
			if (strlen ($strABN) == 0)
			{
				return true;
			}
			
			// 2. Check that the item has only Numbers and Spaces
			if (preg_match ('/[^\d\s]/', $strABN))
			{
				return false;
			}
			
			$strABN = preg_replace ('/\s/', '', $strABN);
			
			// 3. Check there are 11 integers
			if (strlen ($strABN) != 11)
			{
				return false;
			}
			
			
			// 4. ABN Calculation
			// http://www.ato.gov.au/businesses/content.asp?doc=/content/13187.htm&pc=001/003/021/002/001&mnu=610&mfp=001/003&st=&cy=1
			
			//   1. Subtract 1 from the first (left) digit to give a new eleven digit number
			//   2. Multiply each of the digits in this new number by its weighting factor
			//   3. Sum the resulting 11 products
			//   4. Divide the total by 89, noting the remainder
			//   5. If the remainder is zero the number is valid
			

			
			//   1. Subtract 1 from the first (left) digit to give a new eleven digit number
			$strNewABN = (intval (substr ($strABN, 0, 1)) - 1) . substr ($strABN, 1);
			
			
			//   2. Multiply each of the digits in this new number by its weighting factor
			//   3. Sum the resulting 11 products
			$intNumberSum = 0;
			
			for ($i=0; $i < 11; ++$i)
			{
				$intNumberSum += substr ($strNewABN, $i, 1) * ABN::$arrWeights [$i];
			}
			
			//   4. Divide the total by 89, noting the remainder
			//   5. If the remainder is zero the number is valid
			if ($intNumberSum % 89 != 0)
			{
				return false;
			}
			
			parent::setValue (
				substr ($strABN, 0, 2) . " " . substr ($strABN, 2, 3) . " " . substr ($strABN, 5, 3) . " " . substr ($strABN, 8, 3)
			);
			
			return true;
		}
	}

	//----------------------------------------------------------------------------//
	// ACN
	//----------------------------------------------------------------------------//
	/**
	 * ACN
	 *
	 * ACN Number Validation/Parsing
	 *
	 * Controls validation of ACN Numbers
	 *
	 *
	 * @prefix	acn
	 *
	 * @package		intranet_app
	 * @class		ACN
	 * @extends		dataPrimitive
	 */
	
	class ACN extends dataPrimitive
	{
		
		public static $arrWeights = Array (
			"0"	=> 8,
			"1"	=> 7,
			"2"	=> 6,
			"3"	=> 5,
			"4"	=> 4,
			"5"	=> 3,
			"6"	=> 2,
			"7"	=> 1
		);
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new ACN
		 *
		 * Constructor for a new ACN
		 *
		 * @param	String		$strACN			The initial value of the ACN being set
		 *
		 * @method
		 */
		
		function __construct ($strName, $strACN)
		{
			// Construct the object
			parent::__construct ($strName);
			$this->setValue ($strACN);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Value of the ACN
		 *
		 * Change the Value of the ACN
		 *
		 * @param	String		$strACN			The value of the ACN being set
		 * @return	Boolean						Whether the Number is Valid or not. Invalid numbers are not subscribed.
		 *
		 * @method
		 */
		
		public function setValue ($strACN)
		{
			// 1. If the length is 0, it is valid because we might not have an ACN
			
			if (strlen ($strACN) == 0)
			{
				return true;
			}
			
			// 2. Check that the item has only Numbers and Spaces
			if (preg_match ("/[^\d\s]/", $strACN))
			{
				return false;
			}
			
			$strACN = preg_replace ("/[^\d]/", "", $strACN);
			
			// 3. Check there are 9 integers
			if (strlen ($strACN) != 9)
			{
				return false;
			}
			
			// 1. Apply weighting to digits 1 to 8
			// 2. Sum the products
			// 3. Divide by 10 to obtain remainder 84 / 10 = 8 remainder 4
			// 4. Complement the remainder to 10 10 - 4 = 6 (if complement = 10, set to 0)
			// 5. Check the calculated check digit equals actual check digit
			
			// 1. Apply weighting to digits 1 to 8
			// 2. Sum the products
			$intNumberSum = 0;
			
			for ($i=0; $i < 8; ++$i)
			{
				$intNumberSum += substr ($strACN, $i, 1) * ACN::$arrWeights [$i];
			}
			
			// 3. Divide by 10 to obtain remainder 84 / 10 = 8 remainder 4
			$intRemainder = $intNumberSum % 10;
			
			// 4. Complement the remainder to 10 10 - 4 = 6 (if complement = 10, set to 0)
			$intComplement = 10 - $intRemainder;
			
			if ($intComplement == 10)
			{
				$intComplement = 0;
			}
			
			// 5. Check the calculated check digit equals actual check digit
			if (substr ($strACN, 8, 1) != $intComplement)
			{
				return false;
			}
			
			parent::setValue (
				substr ($strACN, 0, 3) . " " . substr ($strACN, 3, 3) . " " . substr ($strACN, 6, 3)
			);
			
			return true;
		}
	}

?>
