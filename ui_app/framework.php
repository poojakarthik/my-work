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
 * @author		Jared, Sean and Joel
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// PropertyToken
//----------------------------------------------------------------------------//
/**
 * PropertyToken
 *
 * The PropertyToken logically represents a single property of a single object
 *
 * The PropertyToken logically represents a single property of a single object
 *
 *
 * @package	ui_app
 * @class	PropertyToken
 */
// Add this to the 'real' PropertyToken class
class PropertyToken
{
	//------------------------------------------------------------------------//
	// RenderInput
	//------------------------------------------------------------------------//
	/**
	 * RenderInput
	 *
	 * Renders the property as an input element
	 *
	 * Organises the data required to render the property as an input element
	 * and then renders it.
	 *
	 *
	 * @param		bool	$bolRequired
	 * @param		string	$strContext		the context in which the property will be rendered as an input element
	 *
	 * @return		void
	 *
	 */
	function RenderInput($bolRequired=NULL, $strContext=NULL)
	{
		// Build up parameters for RenderHTMLTemplate()
		//TODO!Interface-kids!Actually do this
		
		RenderHTMLTemplate($arrParams);
	}

	//------------------------------------------------------------------------//
	// RenderOutput
	//------------------------------------------------------------------------//
	/**
	 * RenderOutput
	 *
	 * Renders the property as an output element
	 *
	 * Organises the data required to render the property as an output element
	 * and then renders it.
	 *
	 *
	 * @param		bool	$bolRequired
	 * @param		string	$strContext		the context in which the property will be rendered as an output element
	 *
	 * @return		void
	 *
	 */
	function RenderOutput($bolRequired=NULL, $strContext=NULL)
	{
		// Build up parameters for RenderHTMLTemplate()
		//TODO!Interface-kids!Actually do this
		
		RenderHTMLTemplate($arrParams);
	}

}


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
	$arrDefinition['OutputMask']	= $;				// 
	
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
	// SetName()
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
	// SetLayout()
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
	// AddObject()
	//------------------------------------------------------------------------//
	/**
	 * AddObject()
	 *
	 * Adds an extended Html_Template object to the page 
	 *
	 * Adds an extended Html_Template object to the page
	 * Extended Html_Template classes can be found in the 
	 * 
	 * @param	string	$str		the value to set the page layout to
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
	
	function Render()
	{
		// load required layout
		require_once(TEMPLATE_BASE_DIR."layout_template/" . strtolower($this->_strPageLayout) . ".php");
	}
	
	function RenderCSS()
	{
		echo "<link rel='stylesheet' type='text/css' href='css.php' />\n";
	}
	
	function RenderJS()
	{
		// for each on global array
		if (is_array($GLOBALS['*arrJavaScript']))
		{
			foreach ($GLOBALS['*arrJavaScript'] as $strValue)
			{
				echo "<script type='text/javascript' src='javascript/$strValue' />\n";
			}	
		}
	}
	
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


?>
