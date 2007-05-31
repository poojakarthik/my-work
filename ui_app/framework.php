<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// framework.php
//----------------------------------------------------------------------------//
/**
 * framework
 *
 * Defines the framework classes for ui_app
 *
 * Defines the framework classes for ui_app
 *
 * @file		framework.php
 * @language	PHP
 * @package		framework
 * @author		Jared
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


// of Dbo()->Account->Id->RenderInput($bolRequired, $strContext)
// Dbo()->Object->Property->Render([$bolRequired], [$strContext]);
//DEPRECIATED
function dboRender($strTemplateType, $bolRequired)
{

/*
	// $templatetype = label;
	$strTag = $this->GetHTMLTag($templatetype);
	// $strTag = ""
	
	
	$myTarget = $this->Dbo->Account->Id->Value;
	$newTag = strReplace($strTag, "[location]", "localhost/intran...../account_view.php?Id=" . $myTarget);
	$newTag = strReplace($newTage, "[pagename]", "View Account");
	echo $newTag;*/
	
	
	
	
	// $templatetype = input;
	// lookup database definition to see what type to use
	//$GLOBALS['arrDatabaseTableDefine'][$arrDefine['Name']] = $arrDefine; 
	//$arrType = $GLOBALS['arrDatabaseTableDefine']['Account']['Column']['Id'];
	
	$arrType = Array();
	$arrType['Class'] 	= 'input-string-valid';
	
	$arrParams = Array();
	/*
	$arrParams['Object'] 		= $this->object;		// 'Account'
	$arrParams['Property'] 		= $this->property;		// 'Id'
	$arrParams['Context'] 		= $this->context;		// DEFAULT
	$arrParams['Definition'] 	= $;					// definition array
	$arrParams['Value'] 		= $this->Value;			// '1000123456'
	$arrParams['Valid']			= $;					// TRUE
	$arrParams['Required'] 		= $bolRequired;			// TRUE
	
	$arrDefinition['ValidationRule']	= $;			// VALID_EMAIL
	$arrDefinition['InputType']	= $;					// 
	$arrDefinition['OutputType']	= $;				//
	$arrDefinition['Label']	= $;						//
	$arrDefinition['InputOptions']	= $;				//
	$arrDefinition['OutputOptions']	= $;				// ['-1'] = "blah <value> blah"
														// ['0']  = "blah bleh blah"
	$arrDefinition['DefaultOutput']	= $;				// "Do not charge for <value> months"
	$arrDefinition['OutputMask']	= $;				// how data is output
	
	*/
		
	$arrParams['Definition'] 	= $arrType;
	$arrParams['Template'] 		= $strTemplateType;
	$arrParams['Value'] 		= '100012345';
	$arrParams['Name'] 			= 'account.id';
	$arrParams['Valid'] 		= TRUE;
	$arrParams['Required'] 		= $bolRequired;

	RenderHTMLTemplate($arrParams);
	
	// $strTag = "<input name=[name] class='input-wide-string'>[value]</input>"
	// what we want:
	// 	<input name='Account.Id' class='input-wide-string'>100012345</input>
	
	
}

//----------------------------------------------------------------------------//
// Page
//----------------------------------------------------------------------------//
/**
 * Page
 *
 * The Page class.  Logically represents a single webpage
 *
 * The Page class.  Logically represents a single webpage
 *
 * @package	ui_app
 * @class	Page
 */
class Page
{
	//------------------------------------------------------------------------//
	// _strPageName
	//------------------------------------------------------------------------//
	/**
	 * _strPageName
	 *
	 * Stores the title of the webpage
	 *
	 * Stores the title of the webpage
	 *
	 * @type		string
	 *
	 * @property
	 */
	private $_strPageName;
	
	//------------------------------------------------------------------------//
	// _strPageLayout
	//------------------------------------------------------------------------//
	/**
	 * _strPageLayout
	 *
	 * Defines the page's layout type
	 *
	 * Defines the page's layout type.  For example, it could be "2COLUMN", "3COLUMN", etc
	 * This will directly reference a php script in the "layout_template" directory.
	 * For example "3COLUMN" will reference the file "layout_template/3column.php"
	 *
	 * @type		string
	 *
	 * @property
	 */
	private $_strPageLayout;

	//------------------------------------------------------------------------//
	// _arrObjects
	//------------------------------------------------------------------------//
	/**
	 * _arrObjects
	 *
	 * list of extended Html_Template objects that will be included in the page
	 *
	 * List of extended html_template objects that will be included in the page.
	 * Each object is stored in an associated array which also defines the type 
	 * of extended html_template object it is and which column it will belong to
	 * in the page layout.
	 *
	 * @type		array 
	 *
	 * @property
	 */
	private $_arrObjects = Array();
	
	//------------------------------------------------------------------------//
	// Page - Constructor
	//------------------------------------------------------------------------//
	/**
	 * Page()
	 *
	 * Constructor for the Page object
	 *
	 * Constructor for the Page object
	 *
	 * @method
	 */
	function __construct()
	{
		$this->_arrObjects = Array();
	}
	
	//------------------------------------------------------------------------//
	// SetName
	//------------------------------------------------------------------------//
	/**
	 * SetName()
	 *
	 * Sets the name of the page (the title of the webpage)
	 *
	 * Sets the name of the page (the title of the webpage)
	 * 
	 * @param	string	$strName		the value to set the page name to
	 *
	 * @method
	 */
	function SetName($strName)
	{
		//var_dump($this);
		//echo "<br />";
		$this->_strPageName = $strName;
	}

	//------------------------------------------------------------------------//
	// SetLayout
	//------------------------------------------------------------------------//
	/**
	 * SetLayout()
	 *
	 * Sets the layout of the page
	 *
	 * Sets the layout of the page.  See comments regarding the _strPageLayout property
	 * 
	 * @param	string	$strLayout		the value to set the page layout to
	 *
	 * @method
	 */
	function SetLayout($strLayout)
	{
		$this->_strPageLayout = $strLayout;
	}
	
	//------------------------------------------------------------------------//
	// AddObject
	//------------------------------------------------------------------------//
	/**
	 * AddObject()
	 *
	 * Adds an extended Html_Template object to the page 
	 *
	 * Adds an extended Html_Template object to the page.
	 * Extended Html_Template classes must be located in the html_template directory
	 * The order in which objects are added will be the order in which they will be
	 * displayed in their associated column
	 * 
	 * @param	string	$strName		The template name (does not include the 'HtmlTemplate' prefix)
	 * @param	integer	$intColumn		the column number which the object will be positioned in
	 * @param	string	$strId			uniquely identifies the object. Defaults to null
	 *
	 * @method
	 */
	function AddObject($strName, $intColumn, $strId=NULL)
	{
		// set UID for this object
		if ($strId)
		{
			// check if this object already exists and die (or something) if it does
		}
		else
		{
			$strId = uniqid();
		}
		
		// set the class name
		$strClassName = "HtmlTemplate$strName";
		
		// set up the object
		$arrObject = Array();
		$arrObject['Name'] = $strName;
		$arrObject['Column'] = $intColumn;
		$arrObject['Object'] = new $strClassName;
		$this->_arrObjects[$strId] = $arrObject;
		
		// return the object id
		return $strId;
	}
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Renders the page 
	 *
	 * Renders the page 
	 * 
	 * @method
	 */
	function Render()
	{
		// load required layout
		require_once(TEMPLATE_BASE_DIR."layout_template/" . strtolower($this->_strPageLayout) . ".php");
	}
	
	//------------------------------------------------------------------------//
	// RenderCSS
	//------------------------------------------------------------------------//
	/**
	 * RenderCSS()
	 *
	 * Renders the CSS part of the page
	 *
	 * Renders the CSS part of the page
	 * 
	 * @method
	 */
	function RenderCSS()
	{
		echo "<link rel='stylesheet' type='text/css' href='css.php' />\n";
	}
	
	//------------------------------------------------------------------------//
	// RenderJS
	//------------------------------------------------------------------------//
	/**
	 * RenderJS()
	 *
	 * Renders the JS part of the page
	 *
	 * Renders the JS part of the page
	 * 
	 * @method
	 */
	function RenderJS()
	{
		// for each on global array
		if (is_array($GLOBALS['*arrJavaScript']))
		{
			foreach ($GLOBALS['*arrJavaScript'] as $strValue)
			{
				echo "<script type='text/javascript' src='" . JAVASCRIPT_BASE_DIR . "javascript/$strValue.js' />\n";
			}	
		}
	}
	
	//------------------------------------------------------------------------//
	// RenderColumn
	//------------------------------------------------------------------------//
	/**
	 * RenderColumn()
	 *
	 * Renders a single column of the page
	 *
	 * Renders a single column of the page
	 * 
	 * @method
	 */
	function RenderColumn($intColumn)
	{
		foreach ($this->_arrObjects as $arrObject)
		{
			if ($arrObject['Column'] == $intColumn)
			{
				$arrObject['Object']->Render();
			}
		}
	}
}



//----------------------------------------------------------------------------//
// DBOFramework
//----------------------------------------------------------------------------//
/**
 * DBOFramework
 *
 * Database Object Framework container
 *
 * Database Object Framework container
 *
 * @prefix	dbo
 *
 * @package	framework_ui
 * @class	DBOFramework
 */
class DBOFramework
{
	public	$_arrOptions	= Array();
	private	$_arrProperty	= Array();
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * Generic GET function for returning Database Objects
	 *
	 * Generic GET function for returning Database Objects
	 *
	 * @param	string	$strName	Name of the Database Object
	 * 
	 * @return	DBObject
	 *
	 * @method
	 */
	function __get($strName)
	{
		// Instanciate the DBObject if we can't find an instance
		if (!$this->_arrProperty[$strName])
		{
			$this->_arrProperty[$strName] = new DBObject($strName);
		}
		
		// Return the DBObject
		return $this->_arrProperty[$strName];
	}
	
	//------------------------------------------------------------------------//
	// Validate
	//------------------------------------------------------------------------//
	/**
	 * Validate()
	 *
	 * Validate all Database Objects
	 *
	 * Validate all Database Objects
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function Validate()
	{
		$bolReturn = TRUE;
		
		foreach($this->_arrProperty AS $dboObject)
		{
			if (!$dboObject->SetValid())
			{
				$bolReturn = FALSE;
			}
		}
		
		return $bolReturn;
	}
	
	//------------------------------------------------------------------------//
	// Info
	//------------------------------------------------------------------------//
	/**
	 * Info()
	 *
	 * return info about all DBO object
	 *
	 * return info about all DBO object
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function Info()
	{
		foreach ($this->_arrProperty AS $strObject=>$objObject)
		{
			$arrReturn[$strObject] = $objObject->Info();
		}
		return $arrReturn;
	}
	
	function ShowInfo($strTabs='')
	{
		foreach ($this->_arrProperty AS $strObject=>$objObject)
		{
			$strOutput .= $strTabs."$strObject\n";
			$strOutput .= $strTabs.$objObject->ShowInfo("\t");
		}
		if (!$strTabs)
		{
			Debug($strOutput);
		}
		return $strOutput;
	}	
}

//----------------------------------------------------------------------------//
// DBLFramework
//----------------------------------------------------------------------------//
/**
 * DBLFramework
 *
 * Database Object List Framework container
 *
 * Database Object List Framework container
 *
 * @prefix	dbl
 *
 * @package	framework_ui
 * @class	DBLFramework
 */
class DBLFramework
{
	public	$_arrOptions	= Array();
	private	$_arrProperty	= Array();
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * Generic GET function for returning Database Object Lists
	 *
	 * Generic GET function for returning Database Object Lists
	 *
	 * @param	string	$strName	Name of the Database Object List
	 * 
	 * @return	DBList
	 *
	 * @method
	 */
	function __get($strName)
	{
		// Instanciate the DBList if we can't find an instance
		if (!$this->_arrProperty[$strName])
		{
			$this->_arrProperty[$strName] = new DBList($strName);
		}
		
		// Return the DBList
		return $this->_arrProperty[$strName];
	}
	
	//------------------------------------------------------------------------//
	// Info
	//------------------------------------------------------------------------//
	/**
	 * Info()
	 *
	 * return info about all DBO object
	 *
	 * return info about all DBO object
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function Info()
	{
		foreach ($this->_arrProperty AS $strObject=>$objObject)
		{
			$arrReturn[$strObject] = $objObject->Info();
		}
		return $arrReturn;
	}
	
	function ShowInfo()
	{
		$arrInfo = $this->Info();
		foreach ($arrInfo AS $strList=>$arrList)
		{
			$strOutput .= "$strList\n";
			foreach ($arrList AS $intId=>$arrObject)
			{
				$strOutput .= "	$intId\n";
				foreach ($arrObject AS $strKey=>$arrValue)
				{
					$strOutput .= "		$strKey\n";
					foreach ($arrValue AS $strProperty=>$mixValue)
					{
						$strOutput .= "			$strProperty : $mixValue\n";
					}
				}
			}
		}
		Debug($strOutput);
	}
}


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
		if ($strName === NULL)
		{
			return $this->_arrConfig[$strType];
		}
		else
		{
			if (!isset($this->_arrConfig[$strType][$strName]))
			{
				switch (strtolower($strType))
				{
					case "dbo":
						// Retrieve the documentation so that it can be cached
						$selDocumentation = new StatementSelect("UIAppDocumentation",
															"*", 
															"Object = <Object>");
	 					$selDocumentation->Execute(Array('Object' => $strName));	
						$arrDocumentation = $selDocumentation->FetchAll();
					
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
							$selOptions = new StatementSelect("UIAppDocumentationOptions", "*", "Object = <Object>");
							$selOptions->Execute(Array('Object' => $strName));
							$arrOptions = $selOptions->FetchAll();						
	
							if (is_array($arrOptions))
							{
								foreach ($arrOptions as $arrRecord)
								{
									// Add each record to an array called 'Options' inside its associated property array
									// This data can be accessed by: $this->_arrConfig['dbo'][object][property][context]['Options'][Group][][field] = value
									$arrOption['Value'] = $arrRecord['Value'];
									$arrOption['Label'] = $arrRecord['Label'];
									$this->_arrConfig[$strType][$arrRecord['Object']][$arrRecord['Property']][$arrRecord['Context']]['Options'][$arrRecord['Group']][] = $arrOption;
								}
							}
						}
						break;
						
					case "dbl":
						// TODO!Joel! Load and cache config for this object (from somewhere)
						// $this->_arrConfig[$strType][$strName] = 
						break;
						
					default:
						break;
				}
			}
			return $this->_arrConfig[$strType][$strName];
		}
	}
}

//----------------------------------------------------------------------------//
// Validation
//----------------------------------------------------------------------------//
/**
 * Validation
 *
 * The Validation class
 *
 * The Validation class - encapsulates all validation rules
 * It can also handle validation against a regex
 * Each validation rule that isn't a regex will have a method defined in this class.
 *
 * @package	ui_app
 * @class	Validation
 */
class Validation
{
	//------------------------------------------------------------------------//
	// RegexValidate
	//------------------------------------------------------------------------//
	/**
	 * RegexValidate()
	 *
	 * Validates a value using a regular expression as the validation rule
	 *
	 * Validates a value using a regular expression as the validation rule
	 *
	 * @param	string		$strValidationRule	the validation rule as a regex
	 * @param	mix			$mixValue			the value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function RegexValidate($strValidationRule, $mixValue)
	{
		return TRUE;
	}
}




?>
