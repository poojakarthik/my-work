<?php
abstract class Dummy
{
	// Overidden
	protected $_aProperties	= array();
	protected $_sTableName	= null;
	protected $_sIdField	= null;
	
	protected $_aTidyNames		= array();
	protected $_bSaved			= false;
	protected $_bAllowZeroId	= false;

	protected function __construct($aProperties=array(), $bLoadById=false)
	{
		foreach ($this->_aProperties as $sName => $mValue)
		{
			$this->_aTidyNames[self::tidyName($sName)] = $sName;
		}
		
		if ($aProperties instanceof Dummy)
		{
			throw new Exception_ORM("\$aProperties is a Dummy object!");
		}
		
		// Automatically load the Record using the passed Id
		$iId = ($aProperties[$this->_sIdField] ? $aProperties[$this->_sIdField] : NULL);
		if ($bLoadById && $iId)
		{
			// Load by id
			Log::getLog()->log($this->_getClassName().", {$iId}");
			$aProperties = self::getForId($this->_getClassName(), $iId)->toArray();
		}
		
		// Set Properties
		
		// First set the id field, if it has been specified
		if (array_key_exists($this->_sIdField, $aProperties))
		{
			$this->setId($aProperties[$this->_sIdField]);
			
			// Remove it from the properties
			unset($aProperties[$this->_sIdField]);
		}
		
		// Set all remaining fields
		foreach ($aProperties as $sName => $mValue)
		{
			// Load from the Database
			$this->___set($sName, $mValue);
		}
	}


	
	public function getId()
	{
		return $this->_aProperties[$this->_sIdField];
	}
	
	protected function setId($iId)
	{
		$this->_aProperties[$this->_sIdField] = $iId;
	}
	
	public function save()
	{
		if ($this->getId() === null)
		{
			// New record, get a new id
			$this->setId(self::getNewId($this->_getClassName(), $this->_bAllowZeroId));
		}
		
		self::setForId($this->_getClassName(), $this->getId(), $this);
		return $this->getId();
	}
	
	public function __get($sName)
	{
		$sName = $this->_getFieldName($sName);
		return (array_key_exists($sName, $this->_aProperties)) ? $this->_aProperties[$sName] : NULL;
	}

	public function __set($sName, $mValue)
	{
		$sName = $this->_getFieldName($sName);
		$this->___set($sName, $mValue);
	}
	
	final public function ___set($sName, $mValue)
	{
		$sName = $this->_getFieldName($sName);
		if (array_key_exists($sName, $this->_aProperties))
		{
			if ($sName == $this->_sIdField)
			{
				// Cannot explicitly mutate the id
				throw new Exception_Assertion("Cannot explicitly set the id property of an Dummy object", "Attempted to set the id to $mValue for the ". $this->_getClassName() ." Object with internal state: \n". print_r($this, true), "Dummy::__set() Violation");
			}
			
			$mOldValue					= $this->_aProperties[$sName];
			$this->_aProperties[$sName]	= $mValue;
			
			if ($mOldValue !== $mValue)
			{
				$this->_bSaved	= FALSE;
			}
		}
		else
		{
			$this->{$sName}	= $mValue;
		}
	}
	
	public function __isset($sName)
	{
		return isset($this->_aProperties[$this->_getFieldName($sName)]);
	}
	
	public function __unset($sName)
	{
		unset($this->_arrProperties[$this->_getFieldName($sName)]);
	}
	
	protected function _getFieldName($sName)
	{
		return array_key_exists($sName, $this->_aTidyNames) ? $this->_aTidyNames[$sName] : $sName;
	}
	
	protected function _getClassName()
	{
		return get_class($this);
	}
	
	protected static function tidyName($sName)
	{
		$sTidy		= str_replace(' ', '', ucwords(str_replace('_', ' ', $sName)));
		$sTidy[0]	= strtolower($sTidy[0]);
		return $sTidy;
	}
	
	public function toArray($bUseTidyNames=FALSE)
	{
		if ($bUseTidyNames)
		{
			$aProps = array();
			foreach ($this->_aTidyNames as $sTidyName=>$sPropName)
			{
				$aProps[$sTidyName] = $this->_aProperties[$sPropName];
			}
			return $aProps;
		}
		else
		{
			return $this->_aProperties;
		}
	}
	
	public function toStdClass($bUseTidyNames=FALSE)
	{
		$aData = array();
		if ($bUseTidyNames)
		{
			foreach ($this->_aTidyNames as $sTidyName => $sPropName)
			{
				$aData[$sTidyName] = $this->_aProperties[$sPropName];
			}
		}
		else
		{
			$aData = $this->_aProperties;
		}
		
		$oStdClass = new stdClass();
		foreach ($aData as $sField => $mValue)
		{
			$oStdClass->{$sField} = $mValue;
		}
		return $oStdClass;
	}
	
	public function getPropertyNames()
	{
		return array_keys($this->_aProperties);
	}
	
	public static function getAll($sTableName)
	{
		if (!isset($_SESSION['Dummy_Data'][$sTableName]))
		{
			$_SESSION['Dummy_Data'][$sTableName] = array();
		}
		
		$aData	= $_SESSION['Dummy_Data'][$sTableName];
		$aAll	= array();
		foreach ($aData as $iId => $aRecord)
		{
			if (!$aRecord)
			{
				continue;
			}
			
			$aAll[$iId] = self::getInstance($sTableName, $aRecord);
		}
		return $aAll;
	}
	
	public static function getForId($sTableName, $iId)
	{
		if (!isset($_SESSION['Dummy_Data'][$sTableName]))
		{
			$_SESSION['Dummy_Data'][$sTableName] = array();
		}
		
		$aData	= $_SESSION['Dummy_Data'][$sTableName][$iId];
		return ($aData ? self::getInstance($sTableName, $aData) : null);
	}
	
	public static function getFor($sTableName, $aCriteria)
	{
		if (!isset($_SESSION['Dummy_Data'][$sTableName]))
		{
			$_SESSION['Dummy_Data'][$sTableName] = array();
		}
		
		$aData		= $_SESSION['Dummy_Data'][$sTableName];
		$aResults	= array();
		if ($aData !== null)
		{
			foreach ($aData as $iId => $aRecord)
			{
				if (!$aRecord)
				{
					continue;
				}
				
				foreach ($aCriteria as $sField => $mValue)
				{
					if ($aRecord[$sField] == $mValue)
					{	
						// Criteria match, add it to the results
						$aResults[] = self::getInstance($sTableName, $aRecord);
						break;
					}
				}
			}
		}
		return $aResults;
	}
	
	public static function debugData($sTableName=null, $bHTMLOutput=true, $bIncludeConfig = false)
	{
		$aData = $_SESSION['Dummy_Data'];
		if ($sTableName !== null)
		{
			$aData = array($sTableName => $_SESSION['Dummy_Data'][$sTableName]);
		}
		
		foreach ($aData as $sTableName => $aRecords)
		{
                    if (substr($sTableName,0, 5)=='Dummy')
                         $object = substr($sTableName,0, 5)=='Dummy' ? new $sTableName() : new stdClass();
                    if($bIncludeConfig || (substr($sTableName,0, 5)=='Dummy' && !isset($object->bConfig)))
                    {
                        echo ($bHTMLOutput ? "<p>" : "\n\n");
			echo "TABLE: {$sTableName}".($bHTMLOutput ? '<br/>' : "\n\n");
			foreach ($aRecords as $iId => $aData)
			{
				if (!$aData)
				{
					continue;
				}
				
				$oRecord	= self::getInstance($sTableName, $aData);
				$aRecord	= $oRecord->toArray();
				
				echo "ID: {$iId}".($bHTMLOutput ? '<br/>' : "\n");
				if ($bHTMLOutput)
				{
					echo "<pre>".print_r($aRecord, true)."</pre>";
				}
				else
				{
					//echo print_r($aRecord, true);
                                        foreach($aRecord as $field=>$value)
                                        {
                                            echo "[$field]: $value";
                                        }
				}
				echo ($bHTMLOutput ? "<p>" : "\n");
			}
                    }
                }
	}
	
	private static function getInstance($sClassName, $aData)
	{
		if (class_exists($sClassName))
		{
			// Dummy class exists
			return new $sClassName($aData);
		}
		else
		{
			// No dummy class, must be a constant table
			$sTableName = strtolower($sClassName);
			return new Dummy_Constant($sTableName, $aData);
		}
	}
	
	protected static function setForId($sTableName, $iId, $oData)
	{
		$_SESSION['Dummy_Data'][$sTableName][$iId] = $oData->toArray();
	}
	
	protected static function getNewId($sTableName, $bAllowZeroId=false)
	{
		if (!isset($_SESSION['Dummy_Data'][$sTableName]))
		{
			$_SESSION['Dummy_Data'][$sTableName] = ($bAllowZeroId ? array() : array(null));
		}
		return count($_SESSION['Dummy_Data'][$sTableName]);
	}
}
?>