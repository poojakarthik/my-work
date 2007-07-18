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
	private $_objAjax;
	private $_intTemplateMode;

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
	 * Adds an extended HtmlTemplate object to the page 
	 *
	 * Adds an extended HtmlTemplate object to the page.
	 * Extended HtmlTemplate classes must be located in the html_template directory
	 * The order in which objects are added will be the order in which they will be
	 * displayed in their associated column
	 * 
	 * @param	string	$strName		template name (does not include the 'HtmlTemplate' prefix)
	 *									A file must exist in the html_template directory.
	 *									For example if the class to load is called HtmlTemplateKnowledgeBaseDocView
	 *									then $strName must be "KnowledgeBaseDocView" and the class must be defined
	 *									in the file "html_template/knowledge_base_doc_view.php"
	 *
	 * @param	integer	$intColumn		column number which the object will be positioned in
	 * @param	integer	$intContext		context in which the HTML template will be used
	 * @param	string	$strId			uniquely identifies the object. Defaults to null
	 *
	 * @return	string					unique id for the object. ($strId if specified as a parameter)
	 * @method
	 */
	function AddObject($strName, $intColumn, $intContext=HTML_CONTEXT_DEFAULT, $strId=NULL)
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
		$arrObject['Name']		= $strName;
		$arrObject['Id']		= $strId;
		$arrObject['Column']	= $intColumn;
		$arrObject['Object']	= new $strClassName($intContext, $strId);
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
	function RenderHeaderJS()
	{
		echo "<script type='text/javascript' src='" . JAVASCRIPT_BASE_DIR . "javascript/autoloader.js' ></script>\n";
		echo "<script type='text/javascript'>VixenSetJavascriptBaseDir('". JAVASCRIPT_BASE_DIR ."')</script>\n";
		echo "<script type='text/javascript'>VixenIncludeJSOnce('vixen')</script>\n";
		echo "<script type='text/javascript'>VixenIncludeJSOnce('menu')</script>\n";
		echo "<script type='text/javascript'>VixenIncludeJSOnce('popup')</script>\n";
		echo "<script type='text/javascript'>VixenIncludeJSOnce('dhtml')</script>\n";
		echo "<script type='text/javascript'>VixenIncludeJSOnce('ajax')</script>\n";

		if (is_array($GLOBALS['*arrJavaScript']))
		{
			foreach ($GLOBALS['*arrJavaScript'] as $strValue)
			{
				echo "<script type='text/javascript'>VixenIncludeJSOnce('". $strValue ."')</script>\n";
			}
		}
	}
	
	
	function RenderJS()
	{
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );	
		if (is_array($GLOBALS['*arrJavaScript']))
		{
			foreach ($GLOBALS['*arrJavaScript'] as $strValue)
			{
				//TODO!!!!!!!!!!!!!
				//echo "<script type='text/javascript'>VixenIncludeJSOnce('". $strValue ."')</script>\n";
				echo "<script type='text/javascript' src='".JAVASCRIPT_BASE_DIR."javascript/$strValue.js'></script>\n";
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
				echo "<div id='{$arrObject['Id']}'>\n";
				$arrObject['Object']->SetMode($this->_intTemplateMode, $this->_objAjax);
				$arrObject['Object']->Render();
				echo "</div>\n";
			}
		}
	}
	
	//------------------------------------------------------------------------//
	// RenderFooter
	//------------------------------------------------------------------------//
	/**
	 * RenderFooter()
	 *
	 * Renders the footer of a page
	 *
	 * Renders the footer of a page
	 * 
	 * @method
	 */
	function RenderFooter()
	{	
		echo "</body>\n</html>\n";
	}
		
	//------------------------------------------------------------------------//
	// RenderHeader
	//------------------------------------------------------------------------//
	/**
	 * RenderHeader()
	 *
	 * Renders the header of a page
	 *
	 * Renders the header of a page
	 * 
	 * @method
	 */
	function RenderHeader()
	{	
		$arrScript = explode('.php', $_SERVER['REQUEST_URI'], 2);
		$intLastSlash = strrpos($arrScript[0], "/");
		$strBaseDir = substr($arrScript[0], 0, $intLastSlash + 1);
		if ($_SERVER['HTTPS'])
		{
			$strBaseDir = "https://{$_SERVER['SERVER_NAME']}$strBaseDir";
		}
		else
		{
			$strBaseDir = "http://{$_SERVER['SERVER_NAME']}$strBaseDir";
		}
//echo $_SERVER['SERVER_NAME'] ."<br>";
//echo $strBaseDir;
//die;

header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );
		
	
		echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>\n";
		echo "<title>viXen : Employee Intranet System - $this->_strPageName</title>\n";
		echo "<base href='$strBaseDir'/>\n";
		$this->RenderHeaderJS();
		$this->RenderCSS();
		echo "</head>\n";
		echo "<body onload='Vixen.Init()'>\n";
	}
	
	//------------------------------------------------------------------------//
	// RenderContextMenu
	//------------------------------------------------------------------------//
	/**
	 * RenderContextMenu()
	 *
	 * Renders the context menu
	 *
	 * Renders the context menu
	 * 
	 * @method
	 */
	function RenderContextMenu()
	{
		// build array
		$arrContextMenu = ContextMenu()->BuildArray();
		
		// convert to json
		$strContextMenu = Json()->Encode($arrContextMenu);
		
		// add to html
		echo "<div id='VixenMenu' class='ContextMenu'></div>\n";
		echo "<script type='text/javascript'>Vixen.Menu.objMenu = $strContextMenu; </script>\n";
		
		// run js
		echo "<script type='text/javascript'>Vixen.Menu.Render()</script>\n";
	}
	
	//------------------------------------------------------------------------//
	// RenderBreadCrumbMenu
	//------------------------------------------------------------------------//
	/**
	 * RenderBreadCrumbMenu()
	 *
	 * Renders the breadcrumb menu
	 *
	 * Renders the breadcrumb menu
	 * 
	 * @method
	 */
	function RenderBreadCrumbMenu()
	{
		$objBreadCrumb = new HtmlTemplateBreadCrumb(HTML_CONTEXT_DEFAULT);
		$objBreadCrumb->Render();
	}
	
	//------------------------------------------------------------------------//
	// RenderVixenHeader
	//------------------------------------------------------------------------//
	/**
	 * RenderVixenHeader()
	 *
	 * Renders the Vixen header
	 *
	 * Renders the Vixen header
	 * 
	 * @method
	 */
	function RenderVixenHeader()
	{
		$objHeader = new HtmlTemplateVixenHeader(HTML_CONTEXT_DEFAULT);
		$objHeader->Render();
	}
	
	//------------------------------------------------------------------------//
	// SetMode
	//------------------------------------------------------------------------//
	/**
	 * SetMode()
	 *
	 * Sets the mode of the template
	 * 
	 * Sets the mode of the template
	 *
	 * @param		int	$intMode	The mode number to set
	 *								ie AJAX_MODE, HTML_MODE
	 * @param		obj	$objAjax	optional Ajax object
	 *
	 * @return		void
	 * @method
	 *
	 */
	function SetMode($intMode, $objAjax=NULL)
	{
		$this->_intTemplateMode = $intMode;
		$this->_objAjax = $objAjax;
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
 * @package	ui_app
 * @class	DBOFramework
 */
class DBOFramework
{
	// this member variable is not currently used for anything
	public	$_arrOptions	= Array();
	
	//------------------------------------------------------------------------//
	// _arrProperty
	//------------------------------------------------------------------------//
	/**
	 * _arrProperty
	 *
	 * Stores all DBObject objects in the DBOFramework
	 *
	 * Stores all DBObject objects in the DBOFramework
	 *
	 * @type		array
	 *
	 * @property
	 */
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
	 * If the database object requested doesn't exist, it is created and returned.
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
	 * @return	bool		TRUE if all database objects are valid; else FALSE
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
	 * returns info about each DBO object contained in the framework
	 *
	 * returns info about each DBO object contained in the framework
	 * 
	 * @return	array		[DBObjectName=>DBObjectInfo]
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

	//------------------------------------------------------------------------//
	// ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * ShowInfo()
	 *
	 * Formats a list containing information regarding each DBObject object, so that it can be displayed
	 *
	 * Formats a list containing information regarding each DBObject object, so that it can be displayed
	 * 
	 * @param	string		$strTabs	[optional]	a string containing tab chars '\t'
	 *												used to define how far the list should be tabbed.
	 * @return	string								returns the list as a formatted string.
	 *												If strTabs is not given then this string is
	 *												also output using Debug()
	 *
	 * @method
	 */
	function ShowInfo($strTabs='')
	{
		foreach ($this->_arrProperty AS $strObject=>$objObject)
		{
			$strOutput .= $strTabs."$strObject\n";
			$strOutput .= $objObject->ShowInfo($strTabs."\t");
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
 * @package	ui_app
 * @class	DBLFramework
 */
class DBLFramework
{
	// this member variable is not currently used for anything
	public	$_arrOptions	= Array();

	//------------------------------------------------------------------------//
	// _arrProperty
	//------------------------------------------------------------------------//
	/**
	 * _arrProperty
	 *
	 * Stores all DBList objects in the DBLFramework
	 *
	 * Stores all DBList objects in the DBLFramework
	 *
	 * @type		array
	 *
	 * @property
	 */
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
	 * return info about all DBL objects
	 *
	 * return info about all DBL objects
	 * 
	 * @return	array		[DBListName=>DBListInfo]
	 *
	 * @method
	 */
	function Info()
	{
		$arrReturn = Array();
		foreach ($this->_arrProperty AS $strObject=>$objObject)
		{
			$arrReturn[$strObject] = $objObject->Info();
		}
		return $arrReturn;
	}
	
	//------------------------------------------------------------------------//
	// ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * ShowInfo()
	 *
	 * Formats a list containing information regarding each DBList object, so that it can be displayed
	 *
	 * Formats a list containing information regarding each DBList object, so that it can be displayed
	 * 
	 * @param	string		$strTabs	[optional]	a string containing tab chars '\t'
	 *												used to define how far the list should be tabbed.
	 * @return	string								returns the list as a formatted string.
	 *												If strTabs is not given then this string is
	 *												also output using Debug()
	 *
	 * @method
	 */
	function ShowInfo($strTabs='')
	{
		foreach ($this->_arrProperty AS $strObject=>$objObject)
		{
			$strOutput .= $strTabs."$strObject\n";
			$strOutput .= $objObject->ShowInfo($strTabs."\t");
		}
		if (!$strTabs)
		{
			Debug($strOutput);
		}
		return $strOutput;
	}	
}

//----------------------------------------------------------------------------//
// VixenTableFramework
//----------------------------------------------------------------------------//
/**
 * VixenTableFramework
 *
 * VixenTable Object Framework container
 *
 * VixenTable Object Framework container
 *
 * @prefix	tblfwk
 *
 * @package	ui_app
 * @class	DBOFramework
 */
class VixenTableFramework
{
	//------------------------------------------------------------------------//
	// _arrTable
	//------------------------------------------------------------------------//
	/**
	 * _arrTable
	 *
	 * Stores all VixenTable objects in the DBOFramework
	 *
	 * Stores all VixenTable objects in the DBOFramework
	 *
	 * @type	array
	 *
	 * @property
	 */
	private	$_arrTable = Array();
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * Generic GET function for returning VixenTable objects
	 *
	 * Generic GET function for returning VixenTable objects
	 * If the VixenTable object requested doesn't exist, it is created and returned.
	 *
	 * @param	string	$strName	Name of the Table Object
	 * 
	 * @return	VixenTable
	 *
	 * @method
	 */
	function __get($strName)
	{
		// Instanciate the VixenTable if we can't find an instance
		if (!$this->_arrTable[$strName])
		{
			$this->_arrTable[$strName] = new VixenTable($strName);
		}
		
		// Return the Table
		return $this->_arrTable[$strName];
	}
	
	
	//------------------------------------------------------------------------//
	// Info
	//------------------------------------------------------------------------//
	/**
	 * Info()
	 *
	 * returns info about each VixenTable object contained in the framework
	 *
	 * returns info about each VixenTable object contained in the framework
	 * 
	 * @return	array		
	 *
	 * @method
	 */
	function Info()
	{
		foreach ($this->_arrTable as $objTable)
		{
			$arrReturn[] = $objTable->Info();
		}
		
		return $arrReturn;
	}

	//------------------------------------------------------------------------//
	// ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * ShowInfo()
	 *
	 * Formats a list containing information regarding each VixenTable object, so that it can be displayed
	 *
	 * Formats a list containing information regarding each VixenTable object, so that it can be displayed
	 * 
	 * @param	string		$strTabs	[optional]	a string containing tab chars '\t'
	 *												used to define how far the list should be tabbed.
	 * @return	string								returns the list as a formatted string.
	 *												If strTabs is not given then this string is
	 *												also output using Debug()
	 *
	 * @method
	 */
	function ShowInfo($strTabs='')
	{
		$strOutput = "Vixen Tables:\n";
		foreach ($this->_arrTable as $objTable)
		{
			$strOutput .= $objTable->ShowInfo("\t");
		}
		
		if (!$strTabs)
		{
			Debug($strOutput);
		}
		return $strOutput;
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
									// This data can be accessed by: $this->_arrConfig['dbo'][object][property][context]['Options'][][field] = value
									$arrOption['Value'] = $arrRecord['Value'];
									$arrOption['OutputLabel'] = $arrRecord['OutputLabel'];
									$arrOption['InputLabel'] = $arrRecord['InputLabel'];
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
									$arrCondition['Operator'] = $arrRecord['Operator'];
									$arrCondition['Value'] = $arrRecord['Value'];
									$arrCondition['Context'] = $arrRecord['Context'];
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
		// return false if not a valid regex
		if (substr($strValidationRule, 0, 1) != '/' || !strrpos($strValidationRule, '/') > 0)
		{
			return FALSE;
		}

		// try to match with a regex
		if (preg_match($strValidationRule, $mixValue))
		{
			return TRUE;
		}
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// Integer
	//------------------------------------------------------------------------//
	/**
	 * Integer()
	 *
	 * Checks if a value is a valid integer
	 *
	 * Checks if a value is a valid integer
	 *
	 * @param	mix			$mixValue			the value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function Integer($mixValue)
	{
		if ((string)(int)$mixValue == (string)$mixValue)
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// UnsignedInteger
	//------------------------------------------------------------------------//
	/**
	 * UnsignedInteger()
	 *
	 * Checks if a value is a valid unsigned integer
	 *
	 * Checks if a value is a valid unsigned integer
	 *
	 * @param	mix			$mixValue			the value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function UnsignedInteger($mixValue)
	{
		if ((int)$mixValue > -1 && (string)(int)$mixValue == (string)$mixValue)
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// NonZeroInteger
	//------------------------------------------------------------------------//
	/**
	 * UnsignedInteger()
	 *
	 * Checks if a value is a valid non-zero integer
	 *
	 * Checks if a value is a valid non-zero integer
	 *
	 * @param	mix			$mixValue			the value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function NonZeroInteger($mixValue)
	{
		if ((int)$mixValue != 0 && (string)(int)$mixValue == (string)$mixValue)
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// UnsignedNonZeroInteger
	//------------------------------------------------------------------------//
	/**
	 * UnsignedNonZeroInteger()
	 *
	 * Checks if a value is a valid unsigned non-zero integer
	 *
	 * Checks if a value is a valid unsigned non-zero integer
	 *
	 * @param	mix			$mixValue			the value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function UnsignedNonZeroInteger($mixValue)
	{
		if ((int)$mixValue > 0 && (string)(int)$mixValue == (string)$mixValue)
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// DateAndTime
	//------------------------------------------------------------------------//
	/**
	 * DateAndTime()
	 *
	 * Checks if a value is in a valid date and time format
	 *
	 * Checks if a value is in a valid date and time format
	 *
	 * @param	mix			$mixDateAndTime		the value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function DateAndTime($mixDateAndTime)
	{
		// TODO! Joel  Test against all variations of the MySql datetime data type
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// IsMoneyValue
	//------------------------------------------------------------------------//
	/**
	 * IsMoneyValue()
	 *
	 * Checks if a value is in a valid monetary format and is not NULL
	 *
	 * Checks if a value is in a valid monetary format and is not NULL
	 * The valid format is a float that can start with a '$' char
	 *
	 * @param	mix			$mixValue		value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function IsMoneyValue($mixValue)
	{
		// remove whitespace and the $ if they are present
		$mixValue = trim($mixValue);
		$mixValue = ltrim($mixValue, "$");
		
		//check that the value is a float
		list($fltValue, $strAppendedText) = sscanf($mixValue, "%f%s");
		
		if ($strAppendedText)
		{
			// there was some text after the float
			return FALSE;
		}
		
		return (is_numeric($fltValue));
	}
	
	//------------------------------------------------------------------------//
	// IsNotNull
	//------------------------------------------------------------------------//
	/**
	 * IsNotNull()
	 *
	 * Returns TRUE if the value is not NULL
	 *
	 * Returns TRUE if the value is not NULL
	 * This will return TRUE if $mixValue == 0
	 * 
	 *
	 * @param	mix			$mixValue		value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function IsNotNull($mixValue)
	{
		// take care of the special case where $mixValue == 0
		if (is_numeric($mixValue))
		{
			// if the value is a number then it can't be NULL
			return TRUE;
		}
		
		return (bool)($mixValue != NULL);
	}

	//------------------------------------------------------------------------//
	// IsNotEmptyString
	//------------------------------------------------------------------------//
	/**
	 * IsNotEmptyString()
	 *
	 * Returns TRUE if the value is not an empty string and is not just whitespace
	 *
	 * Returns TRUE if the value is not an empty string and is not just whitespace
	 * 
	 *
	 * @param	mix			$mixValue		value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function IsNotEmptyString($mixValue)
	{
		$mixValue = trim($mixValue);
		
		return (bool)(strlen($mixValue) > 0);
	}
}


//----------------------------------------------------------------------------//
// OutputMasks
//----------------------------------------------------------------------------//
/**
 * OutputMasks
 *
 * The OutputMasks class
 *
 * The OutputMasks class - encapsulates all output masks
 *
 * @package	ui_app
 * @class	OutputMasks
 */
class OutputMasks
{
	//------------------------------------------------------------------------//
	// MoneyValue
	//------------------------------------------------------------------------//
	/**
	 * MoneyValue()
	 *
	 * Formats a float as a money value
	 *
	 * Formats a float as a money value
	 *
	 * @param	float	$fltValue					value to format as a money value
	 * @param	int		$intDecPlaces				optional; number of decimal places to show
	 * @param	bool	$bolIncludeDollarSign		optional; should a dollar sign be included
	 * @param	bool	$bolUseBracketsForNegative	optional; should brackets be used to denote a negative value
	 * @return	string								$fltValue formatted as a money value
	 *
	 * @method
	 */
	function MoneyValue($fltValue, $intDecPlaces=2, $bolIncludeDollarSign=FALSE, $bolUseBracketsForNegative=FALSE)
	{
		if (fltValue < 0)
		{
			$bolIsNegative = TRUE;
			// Change it to a positive
			$fltValue = fltValue * (-1.0);
		}
		else
		{
			$bolIsNegative = FALSE;
		}
		
		$strValue = number_format($fltValue, $intDecPlaces, ".", "");
		
		if ($bolIsNegative)
		{
			if ($bolUseBracketsForNegative)
			{
				$strValue = '($' . $strValue . ')';
			}
			else
			{
				$strValue = '$-' . $strValue;
			}
		}
		else
		{
			$strValue = '$' . $strValue;
		}
		
		if (!$bolIncludeDollarSign)
		{
			$strValue = str_replace('$', '', $strValue);
		}
		
		return $strValue;	
	}

	//------------------------------------------------------------------------//
	// ShortDate
	//------------------------------------------------------------------------//
	/**
	 * ShortDate()
	 *
	 * Converts a Date from YYYY-MM-DD (MySql Date) to DD/MM/YYYY
	 *
	 * Converts a Date from YYYY-MM-DD (MySql Date) to DD/MM/YYYY
	 *
	 * @param	string	$strMySqlDate				in the format YYYY-MM-DD (standard MySql Date data type)
	 * @return	string								date in format DD/MM/YYYY
	 *
	 * @method
	 */
	function ShortDate($strMySqlDate)
	{
		$arrDate = explode("-", $strMySqlDate);
		$strDate = $arrDate[2] ."/". $arrDate[1] ."/". $arrDate[0];
		return $strDate;
	}

	//------------------------------------------------------------------------//
	// LongDateAndTime
	//------------------------------------------------------------------------//
	/**
	 * LongDateAndTime()
	 *
	 * Converts date and time from YYYY-MM-DD HH:MM:SS (MySql Datetime) to "Wednesday, Jun 21, 2007 11:36:54 AM" format
	 *
	 * Converts date and time from YYYY-MM-DD HH:MM:SS (MySql Datetime) to "Wednesday, Jun 21, 2007 11:36:54 AM" format
	 *
	 * @param	string	$strMySqlDatetime			in the format YYYY-MM-DD HH:MM:SS (MySql Datetime data type)
	 * @return	string								date in format "Wednesday, Jun 21, 2007 11:36:54 AM"
	 *
	 * @method
	 */
	function LongDateAndTime($strMySqlDatetime)
	{
		$arrDateAndTime = explode(" ", $strMySqlDatetime);
		$arrTime = explode(":", $arrDateAndTime[1]);
		$arrDate = explode("-", $arrDateAndTime[0]);
		$intUnixTime = mktime($arrTime[0], $arrTime[1], $arrTime[2], $arrDate[1], $arrDate[2], $arrDate[0]);
		$strDateAndTime = date("l, M j, Y g:i:s A", $intUnixTime);
	
		return $strDateAndTime;
	}

	//------------------------------------------------------------------------//
	// BooleanYesNo
	//------------------------------------------------------------------------//
	/**
	 * BooleanYesNo()
	 *
	 * Converts a boolean into a string of either "Yes" or "No"
	 *
	 * Converts a boolean into a string of either "Yes" or "No"
	 *
	 * @param	bool	$bolYes				boolean value
	 * @return	string						"Yes" or "No"
	 *
	 * @method
	 */
	function BooleanYesNo($bolYes)
	{
		if ($bolYes)
		{
			return "Yes";
		}
		
		return "No";
	}
	
}

//----------------------------------------------------------------------------//
// ContextMenuFramework
//----------------------------------------------------------------------------//
/**
 * ContextMenuFramework
 *
 * Context Menu container
 *
 * Context Menu container.  Manages a context menu.
 *
 * @prefix	cmf
 *
 * @package	ui_app
 * @class	ContextMenuFramework
 */
class ContextMenuFramework
{
	//------------------------------------------------------------------------//
	// _arrProperties
	//------------------------------------------------------------------------//
	/**
	 * _arrProperties
	 *
	 * Multi-dimensional array storing all submenus and menu items
	 *
	 * Multi-dimensional array storing all submenus and menu items
	 *
	 * @type		array
	 *
	 * @property
	 */
	public	$arrProperties	= Array();
	
	//------------------------------------------------------------------------//
	// _objMenuToken
	//------------------------------------------------------------------------//
	/**
	 * _objMenuToken
	 *
	 * Token object used to represent a single menu item that is stored in $arrProperties
	 *
	 * Token object used to represent a single menu item that is stored in $arrProperties
	 *
	 * @type		MenuToken
	 *
	 * @property
	 */
	private	$_objMenuToken	= NULL;
	
	//------------------------------------------------------------------------//
	// _objMenuItems
	//------------------------------------------------------------------------//
	/**
	 * _objMenuItems
	 *
	 * MenuItems object, used to compile Hrefs for the menu items
	 *
	 * MenuItems object, used to compile Hrefs for the menu items
	 *
	 * @type		MenuItems
	 *
	 * @property
	 */
	private $_objMenuItems;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for a ContextMenuFramework object
	 *
	 * Constructor for a ContextMenuFramework object
	 *
	 * @return	void
	 *
	 * @method
	 */
	function __construct()
	{
		$this->_objMenuToken = new MenuToken();
		
	}
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * Creates a new context menu path and returns a reference to it
	 *
	 * Creates a new context menu path and returns a reference to it 
	 *
	 * @param	string	$strName	Name of the new menu path to create
	 * 
	 * @return	MenuToken
	 *
	 * @method
	 */
	function __get($strName)
	{
		$this->_objMenuToken->NewPath($this, $strName);

		// Return the MenuToken
		return $this->_objMenuToken;
	}
	
	//------------------------------------------------------------------------//
	// Reset
	//------------------------------------------------------------------------//
	/**
	 * Reset()
	 *
	 * Resets the context menu (empties it)
	 *
	 * Resets the context menu (empties it)
	 * 
	 * @return	void
	 * @method
	 */
	function Reset()
	{
		$this->arrProperties = Array();
	}
	
	//------------------------------------------------------------------------//
	// _BuildArray
	//------------------------------------------------------------------------//
	/**
	 * _BuildArray()
	 *
	 * Used recursively by the method BuildArray() to build the Context Menu array
	 *
	 * Used recursively by the method BuildArray() to build the Context Menu array
	 *
	 * @param	array	$arrMenu	the menu to build the Context Menu array from
	 * 
	 * @return	array				the built Context Menu array
	 * @method
	 */
	function _BuildArray($arrMenu)
	{
		$arrReturn = Array();

		foreach ($arrMenu as $strMenu=>$arrSubMenu)
		{
			// add menu item
			$strMenu = str_replace("_", " ", $strMenu);  //replace _'s with spaces
			
			if (!is_array(current($arrSubMenu)))
			{
				$strMethod = str_replace(" ", "", $strMenu);
				// add menu link
				$arrReturn[$strMenu] = call_user_func_array(Array($this->_objMenuItems, $strMethod), $arrSubMenu);
			}
			else
			{
				$arrReturn[$strMenu] = $this->_BuildArray($arrSubMenu);
			}
		}
		
		return $arrReturn;
	}
	
	//------------------------------------------------------------------------//
	// BuildArray
	//------------------------------------------------------------------------//
	/**
	 * BuildArray()
	 *
	 * Builds the Context Menu Array
	 *
	 * Builds the Context Menu Array
	 * 
	 * @return	array
	 * @method
	 */
	function BuildArray()
	{
		$this->_objMenuItems = new MenuItems();
		
		$arrOutput = $this->_BuildArray($this->arrProperties);
		
		return $arrOutput;

	}
	
	//------------------------------------------------------------------------//
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * Creates a new root Menu item with this name
	 *
	 * Creates a new root Menu item with this name
	 *
	 * @param	string	$strItem		Item to create
	 * @param	array	$arrArguments	Passed Arguments where first and only member should be the value
	 * 
	 * @return	mixed
	 *
	 * @method
	 */
	function __call($strItem, $arrArguments)
	{
		// Set item value
		$this->arrProperties[$strItem]	= $arrArguments;
		return TRUE;
	}
	
	
	//------------------------------------------------------------------------//
	// Info
	//------------------------------------------------------------------------//
	/**
	 * Info()
	 *
	 * returns a multi-dimensional array representing the contents of the ContextMenu
	 *
	 * returns a multi-dimensional array representing the contents of the ContextMenu
	 * 
	 * @return	array
	 *
	 * @method
	 */
	function Info()
	{
		$this->_objMenuItems = new MenuItems();
		
		return $this->_BuildArray($this->arrProperties);
	}

	//------------------------------------------------------------------------//
	// _ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * _ShowInfo()
	 *
	 * Formats a string representing the layout of the Context Menu (used recursively)
	 *
	 * Formats a string representing the layout of the Context Menu (used recursively)
	 * 
	 * @param	array		$arrMenu				multi-dimensional menu structure to process
	 * @param	string		$strTabs	[optional]	a string containing tab chars '\t'
	 *												used to define how far the menu structure should be tabbed.
	 * @return	string								returns the menu as a formatted string.
	 *												If strTabs is not given then this string is
	 *												also output using Debug()
	 * @method
	 */
	private function _ShowInfo($arrMenu, $strTabs='')
	{
		// Output each element of the array $arrMenu
		if (!is_array($arrMenu))
		{
			// This should never actually happen
			return "";
		}
		foreach ($arrMenu as $strMenu=>$mixSubMenu)
		{
			if (!is_array($mixSubMenu))
			{
				// this is a command
				$strOutput .= $strTabs . $strMenu . " => " . $mixSubMenu . "\n";
			}
			else
			{
				// this is a menu
				$strOutput .= $strTabs . $strMenu . "\n";
				$strOutput .= $this->_ShowInfo($mixSubMenu, $strTabs . "\t");
			}
		}
	
		return $strOutput;
	}

	//------------------------------------------------------------------------//
	// ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * ShowInfo()
	 *
	 * Formats a string representing the layout of the Context Menu
	 *
	 * Formats a string representing the layout of the Context Menu
	 * 
	 * @param	string		$strTabs	[optional]	string containing tab chars '\t'
	 *												used to define how far the menu structure should be tabbed.
	 * @return	string								returns the menu as a formatted string.
	 *												If strTabs is not given then this string is
	 *												also output using Debug()
	 *
	 * @method
	 */
	function ShowInfo($strTabs='')
	{
		$arrMenu = $this->Info();
		
		$strOutput = $this->_ShowInfo($arrMenu, $strTabs);
		
		if (!$strTabs)
		{
			Debug($strOutput);
		}
		return $strOutput;
	}	

}

//----------------------------------------------------------------------------//
// AjaxFramework
//----------------------------------------------------------------------------//
/**
 * AjaxFramework
 *
 * Ajax container
 *
 * Ajax container. Manages the construction of a JSON object to send as a reply for an AJAX request
 *
 * @prefix	ajax
 *
 * @package	ui_app
 * @class	AjaxFramework
 */
class AjaxFramework
{
	//------------------------------------------------------------------------//
	// _arrCommands
	//------------------------------------------------------------------------//
	/**
	 * _arrCommands
	 *
	 * List of commands which will be handled by the Vixen.Ajax.HandleReply
	 *
	 * List of commands which will be handled by the Vixen.Ajax.HandleReply
	 *
	 * @type		array
	 *
	 * @property
	 */
	private $_arrCommands = Array();
	
	//------------------------------------------------------------------------//
	// Reply
	//------------------------------------------------------------------------//
	/**
	 * Reply()
	 *
	 * Sends the list of commands as an AjaxReply
	 *
	 * Sends the list of commands as an AjaxReply
	 * 
	 * @return	void		
	 *
	 * @method
	 */
	function Reply()
	{
		$strReply = Json()->encode($this->_arrCommands);
		$strReply = "//JSON" . $strReply;
		//return AjaxReply($this->_arrCommands);
		echo $strReply;
	}
	
	//------------------------------------------------------------------------//
	// AddCommand
	//------------------------------------------------------------------------//
	/**
	 * AddCommand()
	 *
	 * Adds a javascript command to the list of commands that will be returned to Vixen.Ajax.HandleReply
	 *
	 * Adds a javascript command to the list of commands that will be returned to Vixen.Ajax.HandleReply
	 * 
	 * @param	string		$strType	command type
	 * @param	mixed		$mixData	command data
	 *
	 * @method
	 */
	function AddCommand($strType, $mixData=NULL)
	{
		$arrCommand['Type'] = $strType;
		$arrCommand['Data'] = $mixData;
		$this->_arrCommands[] = $arrCommand;
	}
	
	function HasCommands()
	{
		return count($this->_arrCommands);
	}
}

//----------------------------------------------------------------------------//
// HrefFramework
//----------------------------------------------------------------------------//
/**
 * HrefFramework
 *
 * Wrapper for the MenuItems class.  Used to return the resultant Href for a given menu item.
 *
 * Wrapper for the MenuItems class.  Used to return the resultant Href for a given menu item.
 *
 * @prefix	hrf
 *
 * @package	ui_app
 * @class	HrefFramework
 */
class HrefFramework
{
	//------------------------------------------------------------------------//
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * Should only call the names of methods belonging to the MenuItems class
	 *
	 * Should only call the names of methods belonging to the MenuItems class
	 *
	 * @param	string	$strMethod		name of MenuItem method to use to produce a Href
	 * @param	array	$arrArguments	arguments required of $strMethod
	 * @return	string					resultant href
	 *
	 * @method
	 */
	function __call($strMethod, $arrArguments)
	{
		$objMenuItems = new MenuItems();
		
		$strHref = call_user_func_array(Array($objMenuItems, $strMethod), $arrArguments);

		return $strHref;
	}
}



//----------------------------------------------------------------------------//
// MenuItems
//----------------------------------------------------------------------------//
/**
 * MenuItems
 *
 * Defines the resultant Href for each paricular item that can be included in a menu
 *
 * Defines the resultant Href for each paricular item that can be included in a menu.
 * Each type of menu item (a command in the context menu) should have a method
 * defined here which returns the Href that should be used when the menu item is 
 * clicked.  Alternatively the menu item can be handled by the __call function.
 * You will notice that the menu item "ViewAccount" has been handled both ways as
 * an example of how they work.
 * These menu items can also be expressed as BreadCrumbMenu items, so long as they 
 * set $strLabel to the label that will be displayed for the BreadCrumb.
 *
 * @prefix	mit
 *
 * @package	ui_app
 * @class	MenuItems
 */
class MenuItems
{
	//------------------------------------------------------------------------//
	// strLabel
	//------------------------------------------------------------------------//
	/**
	 * strLabel
	 *
	 * Stores the accompanying label if the last menu item processed can be used as a breadcrumb
	 *
	 * Stores the accompanying label if the last menu item processed can be used as a breadcrumb
	 *
	 * @type		string
	 *
	 * @property
	 */
	public $strLabel;
	
	//------------------------------------------------------------------------//
	// ViewAccount
	//------------------------------------------------------------------------//
	/**
	 * ViewAccount()
	 *
	 * Compiles the Href to be executed when the ViewAccount menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewAccount menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account to view
	 *
	 * @return	string				Href to be executed when the ViewAccount menu item is clicked
	 *
	 * @method
	 */
	function ViewAccount($intId)
	{
		$this->strLabel	= "acc: $intId";
		return "account_view.php?Id=$intId";
	}
	
	//------------------------------------------------------------------------//
	// ViewService
	//------------------------------------------------------------------------//
	/**
	 * ViewService()
	 *
	 * Compiles the Href to be executed when the ViewService menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewService menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int		$intId		id of the service to view
	 * @param	int		$strFNN		[optional] FNN of the service to view
	 *
	 * @return	string				Href to be executed when the ViewService menu item is clicked
	 *
	 * @method
	 */
	function ViewService($intId, $strFNN=NULL)
	{
		$this->strLabel	= "service : $strFNN";
		return "service_view.php?Service.Id=$intId";
	}
	
	//------------------------------------------------------------------------//
	// InvoicesAndPayments
	//------------------------------------------------------------------------//
	/**
	 * InvoicesAndPayments()
	 *
	 * Compiles the Href to be executed when the InvoicesAndPayments menu item is clicked
	 *
	 * Compiles the Href to be executed when the InvoicesAndPayments menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account to view
	 *
	 * @return	string				Href to be executed when the InvoicesAndPayments menu item is clicked
	 *
	 * @method
	 */
	function InvoicesAndPayments($intId)
	{
		$this->strLabel	= "acc: $intId";
		return "vixen.php/Account/InvoicesAndPayments/?Account.Id=$intId";
	}
	
	//------------------------------------------------------------------------//
	// EditEmployee
	//------------------------------------------------------------------------//
	/**
	 * EditEmployee()
	 *
	 * Compiles the Href to be executed when the EditEmployee menu item is clicked
	 *
	 * Compiles the Href to be executed when the EditEmployee menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the Employee to view
	 *
	 * @return	string				Href to be executed when the EditEmployee menu item is clicked
	 *
	 * @method
	 */
	function EditEmployee($intId)
	{
		
		$this->strLabel	= "edit emp: $intId";
		
		// Setup data to send

		//$arrData['HtmlMode'] = TRUE;
		//$arrData['Application'] = "Employee.Edit";
		$arrData['Objects']['Employee']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		//return "javascript:ShowAjaxPopup('ViewNotes', medium, Note.View, $strJsonCode)";
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"Employee{$intId}EditPopup\", \"medium\", \"Employee\", \"Edit\", $strJsonCode)";

	}
	
	//------------------------------------------------------------------------//
	// ViewInvoicePdf
	//------------------------------------------------------------------------//
	/**
	 * ViewInvoicePdf()
	 *
	 * Compiles the Href to be executed when the View Invoice Pdf menu item is clicked
	 *
	 * Compiles the Href to be executed when the View Invoice Pdf menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intInvoice		invoice number of the invoice to view
	 *
	 * @return	string					Href to be executed when the View Invoice Pdf menu item is clicked
	 *
	 * @method
	 */
	function ViewInvoicePdf($intAccount, $intMonth, $intYear)
	{
		$this->strLabel = "pdf acct: $intAccount, $intMonth/$intYear";
		return "invoice_pdf.php?Account=$intAccount&Year=$intYear&Month=$intMonth";
	}
	
	//------------------------------------------------------------------------//
	// ViewInvoice
	//------------------------------------------------------------------------//
	/**
	 * ViewInvoice()
	 *
	 * Compiles the Href to be executed when the View Invoice menu item is clicked
	 *
	 * Compiles the Href to be executed when the View Invoice menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intInvoice		invoice number of the invoice to view
	 *
	 * @return	string					Href to be executed when the View Invoice menu item is clicked
	 *
	 * @method
	 */
	function ViewInvoice($intInvoice)
	{
		$this->strLabel = "inv: $intInvoice";
		return "invoice_view.php?Invoice=$intInvoice";
		
	}
	
	//------------------------------------------------------------------------//
	// ViewNotes
	//------------------------------------------------------------------------//
	/**
	 * ViewNotes()
	 *
	 * Compiles the javascript to be executed when the ViewNotes menu item is clicked
	 *
	 * Compiles the javascript to be executed when the ViewNotes menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account associated with the notes to view
	 *
	 * @return	string				action to be executed when the ViewNotes menu item is clicked
	 *
	 * @method
	 */
	function ViewNotes($intId)
	{
		$this->strLabel	= "view notes";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		//return "javascript:ShowAjaxPopup('ViewNotes', medium, Note.View, $strJsonCode)";
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ViewNotesPopupId\", \"medium\", \"Note\", \"View\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// AddNote
	//------------------------------------------------------------------------//
	/**
	 * AddNote()
	 *
	 * Compiles the javascript to be executed when the AddNote menu item is clicked
	 *
	 * Compiles the javascript to be executed when the AddNote menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account associated with the note to add
	 *
	 * @return	string				action to be executed when the AddNotes menu item is clicked
	 *
	 * @method
	 */
	function AddNote($intId)
	{
		$this->strLabel	= "add note";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddNotePopupId\", \"medium\", \"Note\", \"Add\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// EmailPDFInvoice
	//------------------------------------------------------------------------//
	/**
	 * EmailPDFInvoice()
	 *
	 * Compiles the javascript to be executed when the EmailPDFInvoice menu item is clicked
	 *
	 * Compiles the javascript to be executed when the EmailPDFInvoice menu item is clicked
	 * 
	 * @param	int		$intId		id of the account associated with the invoice to email
	 * @param	int		$intYear	year part of the date of the invoice to email
	 * @param	int		$intMonth	month part of the date of the invoice to email
	 *
	 * @return	string				action to be executed when the AddNotes menu item is clicked
	 *
	 * @method
	 */
	function EmailPDFInvoice($intId, $intYear, $intMonth)
	{
		$this->strLabel	= "email pdf invoice";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		$arrData['Objects']['Invoice']['Year'] = $intYear;
		$arrData['Objects']['Invoice']['Month'] = $intMonth;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"EmailPDFInvoicePopupId\", \"medium\", \"Invoice\", \"EmailPDFInvoice\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// RatesList
	//------------------------------------------------------------------------//
	/**
	 * RatesList()
	 *
	 * Compiles the javascript to be executed when the RatesList menu item is clicked
	 *
	 * Compiles the javascript to be executed when the RatesList menu item is clicked
	 * 
	 * @param	int		$intId		id of the account associated with the invoice to email
	 * @param	int		$intYear	year part of the date of the invoice to email
	 * @param	int		$intMonth	month part of the date of the invoice to email
	 *
	 * @return	string				action to be executed when the AddNotes menu item is clicked
	 *
	 * @method
	 */
	function RatesList($intId)
	{
		$this->strLabel	= "rates list";
		
		// Setup data to send
		$arrData['Objects']['RatePlan']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"RatesListPopupId\", \"large\", \"Plan\", \"RateList\", $strJsonCode)";
	}
	

	//------------------------------------------------------------------------//
	// AddAdjustment
	//------------------------------------------------------------------------//
	/**
	 * AddAdjustment()
	 *
	 * Compiles the javascript to be executed when the AddAdjustment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the AddAdjustment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account that the Adjustment will be added to
	 *
	 * @return	string				action to be executed when the AddAdjustment menu item is clicked
	 *
	 * @method
	 */
	function AddAdjustment($intId)
	{
		$this->strLabel	= "add adjustment";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddAdjustmentPopupId\", \"medium\", \"Adjustment\", \"Add\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// AddRecurringAdjustment
	//------------------------------------------------------------------------//
	/**
	 * AddRecurringAdjustment()
	 *
	 * Compiles the javascript to be executed when the AddRecurringAdjustment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the AddRecurringAdjustment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account that the Adjustment will be added to
	 *
	 * @return	string				action to be executed when the AddRecurringAdjustment menu item is clicked
	 *
	 * @method
	 */
	function AddRecurringAdjustment($intId)
	{
		$this->strLabel	= "add recurring adjustment";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddRecurringAdjustmentPopupId\", \"large\", \"Adjustment\", \"AddRecurring\", $strJsonCode)";
	}
	
	
	//------------------------------------------------------------------------//
	// MakePayment
	//------------------------------------------------------------------------//
	/**
	 * MakePayment()
	 *
	 * Compiles the javascript to be executed when the MakePayment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the MakePayment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account that is currently being viewed
	 *
	 * @return	string				action to be executed when the MakePayment menu item is clicked
	 *
	 * @method
	 */
	function MakePayment($intId)
	{
		$this->strLabel	= "make payment";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"MakePaymentPopupId\", \"large\", \"Payment\", \"Add\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// DeletePayment
	//------------------------------------------------------------------------//
	/**
	 * DeletePayment()
	 *
	 * Compiles the javascript to be executed when the DeletePayment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the DeletePayment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intPaymentId		id of the payment to delete
	 *
	 * @return	string						action to be executed when the DeletePayment menu item is clicked
	 *
	 * @method
	 */
	function DeletePayment($intPaymentId)
	{
		$this->strLabel	= "delete payment: $intPaymentId";
		
		// Setup data to send
		$arrData['Objects']['DeleteRecord']['RecordType'] = "Payment";
		$arrData['Objects']['Payment']['Id'] = $intPaymentId;
		
				
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"DeletePaymentPopupId\", \"medium\", \"Account\", \"DeleteRecord\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// DeleteAdjustment
	//------------------------------------------------------------------------//
	/**
	 * DeleteAdjustment()
	 *
	 * Compiles the javascript to be executed when the DeleteAdjustment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the DeleteAdjustment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAdjustmentId		id of the adjustment to delete
	 *
	 * @return	string							action to be executed when the DeleteAdjustment menu item is clicked
	 *
	 * @method
	 */
	function DeleteAdjustment($intAdjustmentId)
	{
		$this->strLabel	= "delete adjustment: $intAdjustmentId";
				
		// Setup data to send
		$arrData['Objects']['DeleteRecord']['RecordType'] = "Adjustment";
		$arrData['Objects']['Charge']['Id'] = $intAdjustmentId;
				
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"DeleteAdjustmentPopupId\", \"medium\", \"Account\", \"DeleteRecord\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// DeleteRecurringAdjustment
	//------------------------------------------------------------------------//
	/**
	 * DeleteRecurringAdjustment()
	 *
	 * Compiles the javascript to be executed when the DeleteRecurringAdjustment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the DeleteRecurringAdjustment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intRecurringAdjustmentId		id of the recurring adjustment to delete
	 *
	 * @return	string									action to be executed when the DeleteRecurringAdjustment menu item is clicked
	 *
	 * @method
	 */
	function DeleteRecurringAdjustment($intRecurringAdjustmentId)
	{
		$this->strLabel	= "delete recurring adjustment: $intRecurringAdjustmentId";
		
		// Setup data to send
		$arrData['Objects']['DeleteRecord']['RecordType'] = "RecurringAdjustment";
		$arrData['Objects']['RecurringCharge']['Id'] = $intRecurringAdjustmentId;
				
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"DeleteRecurringAdjustmentPopupId\", \"medium\", \"Account\", \"DeleteRecord\", $strJsonCode)";
	}
	

	//------------------------------------------------------------------------//
	// BreadCrumb
	//------------------------------------------------------------------------//
	/**
	 * BreadCrumb()
	 *
	 * Compiles the passed menu item as a breadcrumb to be used in the breadcrumb menu
	 *
	 * Compiles the passed menu item as a breadcrumb to be used in the breadcrumb menu
	 * Any menu item can be used as a breadcrumb so long as it defines a value for 
	 * the public data attribute $strLabel
	 *
	 * @param	string	$strName	Name of the menu item to be used as a breadcrumb
	 *								ie "ViewAccount" or "View_Account"
	 * @param	array	$arrParams	Parameters to be passed to the MenuItem method associated
	 *								with $strName
	 *
	 * @return	array				['Href'] 	= Href to be executed when the breadcrumb is clicked
	 *								['Label'] 	= breadcrumb's label
	 *
	 * @method
	 */
	function BreadCrumb($strName, $arrParams)
	{
		$this->strLabel = NULL;
		$arrReturn = Array();
		$strName = str_replace('_', '', $strName);
		
		// call the menu item method specific to $strName
		$arrReturn['Href'] = call_user_func_array(array($this, $strName), $arrParams);
		
		if (!$this->strLabel)
		{
			// the menu item cannot be used as a breadcrumb
			return FALSE;
		}
		$arrReturn['Label'] = $this->strLabel;
		
		return $arrReturn;
	}
	
	//------------------------------------------------------------------------//
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * Handles all menu items that have not had a specific method defined in this class
	 *
	 * Handles all menu items that have not had a specific method defined in this class
	 * 
	 * @param	string		$strName		name of the menu item
	 * @param	array		$arrParams		any parameters defined for the menu item
	 *
	 * @return	string						the Href to be executed when menu item is clicked
	 *
	 * @method
	 */
	function __call($strName, $arrParams)
	{
		switch ($strName)
		{
			case "Logout":
				return "logout.php";
				break;
			case "AdminConsole":
				return "console.php";
				break;
			default;
				return "[insert generic HREF here]";
				
				break;
		}
	}
}

//----------------------------------------------------------------------------//
// BreadCrumbFramework
//----------------------------------------------------------------------------//
/**
 * BreadCrumbFramework
 *
 * Manages the bread crumb menu
 *
 * Manages the bread crumb menu
 *
 * @prefix	bcf
 *
 * @package	ui_app
 * @class	BreadCrumbFramework
 */
class BreadCrumbFramework
{
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * constructor
	 *
	 * constructor
	 * 
	 *
	 * @return	void
	 *
	 * @method
	 */
	function __construct()
	{
		$this->_mitMenuItems = new MenuItems();
	}
	
	//------------------------------------------------------------------------//
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * Adds a breadcrumb to the menu so long as $strName is a valid menu item that can be expressed as a breadcrumb
	 *
	 * Adds a breadcrumb to the menu so long as $strName is a valid menu item that can be expressed as a breadcrumb
	 * Menu items are defined in the MenuItems class
	 * 
	 * @param	string		$strName		Name of the menu item to be used as a bread crumb
	 * @param	array		$arrParams		Any parameters required by the menu item
	 *
	 * @return	array				['Href'] 	= Href to be executed when the breadcrumb is clicked
	 *								['Label'] 	= breadcrumb's label
	 *
	 * @method
	 */
	function __call($strName, $arrParams)
	{
		$arrBreadCrumb = $this->_mitMenuItems->BreadCrumb($strName, $arrParams);
		if (is_array($arrBreadCrumb))
		{
			DBO()->BreadCrumb->$strName 		= $arrBreadCrumb['Href'];
			DBO()->BreadCrumb->$strName->Label 	= $arrBreadCrumb['Label'];
		}
		return $arrBreadCrumb;
	}
	
	//------------------------------------------------------------------------//
	// Info
	//------------------------------------------------------------------------//
	/**
	 * Info()
	 *
	 * returns an array representing the contents of the Bread Crumb Menu
	 *
	 * returns an array representing the contents of the Bread Crumb Menu
	 * 
	 * @return	array
	 *
	 * @method
	 */
	function Info()
	{
		return DBO()->BreadCrumb->Info();
	}

	//------------------------------------------------------------------------//
	// ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * ShowInfo()
	 *
	 * Formats a string representing the layout of the Bread Crumb Menu
	 *
	 * Formats a string representing the layout of the Bread Crumb Menu
	 * 
	 * @param	string		$strTabs	[optional]	a string containing tab chars '\t'
	 *												used to define how far the menu structure should be tabbed.
	 * @return	string								returns the menu as a formatted string.
	 *												If strTabs is not given then this string is
	 *												also output using Debug()
	 *
	 * @method
	 */
	function ShowInfo($strTabs='')
	{
		return DBO()->BreadCrumb->ShowInfo($strTabs);
	}
}

?>
