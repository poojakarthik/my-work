<?php

//----------------------------------------------------------------------------//
// Config
//----------------------------------------------------------------------------//
/**
 * Config
 *
 * The Config class
 *
 * The Config class - encapsulates all configuration settings
 *
 *
 * @package	ui_app
 * @class	Config
 */
class Config
{
	//------------------------------------------------------------------------//
	// _arrConfig
	//------------------------------------------------------------------------//
	/**
	 * _arrConfig
	 *
	 * Stores all configuration settings
	 *
	 * Stores all configuration settings
	 *
	 * @type		array
	 *
	 * @property
	 */
	private $_arrConfig = Array();
	
	//------------------------------------------------------------------------//
	// Set
	//------------------------------------------------------------------------//
	/**
	 * Set()
	 *
	 * Set configuration parameters
	 *
	 * Set configuration parameters
	 *
	 * @param	array	$arrConfig	the complete set of configuration settings
	 * @return	void
	 *
	 * @method
	 * 
	 */
	function Set($arrConfig)
	{
		$this->_arrConfig = $arrConfig;
	}

	//------------------------------------------------------------------------//
	// instance
	//------------------------------------------------------------------------//
	/**
	 * instance()
	 *
	 * Returns a singleton instance of this class
	 *
	 * Returns a singleton instance of this class
	 *
	 * @return	__CLASS__
	 *
	 * @method
	 */
	public static function instance()
	{
		static $instance;
		if (!isset($instance))
		{
			$instance = new self();
		}
		return $instance;
	}
	
	//------------------------------------------------------------------------//
	// Get
	//------------------------------------------------------------------------//
	/**
	 * Get()
	 *
	 * retrieves part of the configuration array
	 *
	 * retrieves part of the configuration array
	 *
	 * @param	string	$strType	the name of a first level parameter stored
	 *								in the configuration array
	 * @param	string	$strName	[optional] the name of a second level parameter
	 *								stored in the configuration array.
	 *	 
	 * @return	array
	 *
	 * @method
	 * 
	 */
	function Get($strType, $strName=NULL)
	{
		$strType = strtolower($strType);
		if ($strName === NULL)
		{
			return $this->_arrConfig[$strType];
		}
		else
		{
			if (!isset($this->_arrConfig[$strType][$strName]))
			{
				switch ($strType)
				{
					case "dbo":
						// Retrieve the documentation so that it can be cached
						$selDocumentation = new StatementSelect("UIAppDocumentation", "*", "Object = <Object>");
	 					$selDocumentation->Execute(Array('Object' => $strName));	
						$arrDocumentation = $selDocumentation->FetchAll();
					
						// Set up the object used to retrieve records from the UIAppDocumentationOptions table
						$selOptions = new StatementSelect("UIAppDocumentationOptions", "*", "Object = <Object>");
					
						if (is_array($arrDocumentation))
						{
							// Add each record into the $this->_arrConfig[$strType] array
							// This data can be accessed by: $this->_arrConfig['dbo'][object][property][context][field] = value
							foreach ($arrDocumentation as $arrRecord)
							{	
								$this->_arrConfig[$strType][$arrRecord['Object']][$arrRecord['Property']][$arrRecord['Context']] = $arrRecord;
								unset($this->_arrConfig[$strType][$arrRecord['Object']][$arrRecord['Property']][$arrRecord['Context']]['Id']);
								unset($this->_arrConfig[$strType][$arrRecord['Object']][$arrRecord['Property']][$arrRecord['Context']]['Object']);
								unset($this->_arrConfig[$strType][$arrRecord['Object']][$arrRecord['Property']][$arrRecord['Context']]['Property']);
								unset($this->_arrConfig[$strType][$arrRecord['Object']][$arrRecord['Property']][$arrRecord['Context']]['Context']);
							}
							
							// Retrieve further documentation options such as radio button values and labels
							$selOptions->Execute(Array('Object' => $strName));
							$arrOptions = $selOptions->FetchAll();
	
							if (is_array($arrOptions))
							{
								foreach ($arrOptions as $arrRecord)
								{
									// Add each record to an array called 'Options' inside its associated property array
									// This data can be accessed by: $this->_arrConfig['dbo'][object][property][context]['Options'][][field] = value
									$arrOption['Value']			= $arrRecord['Value'];
									$arrOption['OutputLabel']	= $arrRecord['OutputLabel'];
									$arrOption['InputLabel']	= $arrRecord['InputLabel'];
									$this->_arrConfig[$strType][$arrRecord['Object']][$arrRecord['Property']][$arrRecord['Context']]['Options'][] = $arrOption;
								}
							}
							
							// Retrieve conditional context information from the ConditionalContexts table
							$selCondContexts = new StatementSelect("ConditionalContexts", "*", "Object = <Object>", "Id");
							$selCondContexts->Execute(Array('Object' => $strName));
							$arrCondContexts = $selCondContexts->FetchAll();
	
							if (is_array($arrCondContexts))
							{
								foreach ($arrCondContexts as $arrRecord)
								{
									// Add each record to an array called 'ConditionalContexts' inside its associated property array
									// This data can be accessed by: $this->_arrConfig['dbo'][object][property]['ConditionalContexts'][][field] = value
									$arrCondition['Operator']	= $arrRecord['Operator'];
									$arrCondition['Value']		= $arrRecord['Value'];
									$arrCondition['Context']	= $arrRecord['Context'];
									$this->_arrConfig[$strType][$arrRecord['Object']][$arrRecord['Property']]['ConditionalContexts'][] = $arrCondition;
								}
							}
						}
						break;
						
					case "dbl":
						// TODO!Joel! Load and cache config for this object (from somewhere)
						// $this->_arrConfig[$strType][$strName] = 
						// What config data is necessary for DBList objects?
						// possibly information describing how the DBList would be displayed as a table
						break;
						
					default:
						break;
				}
			}
			if (array_key_exists($strType, $this->_arrConfig) && array_key_exists($strName, $this->_arrConfig[$strType]))
			{
				return $this->_arrConfig[$strType][$strName];
			}
		}
	}
}

?>
