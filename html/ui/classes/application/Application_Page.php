<?php

class Application_Page extends Page
{
	public $mxdDataToRender = NULL;

	function __construct($mxdDataToRender=NULL)
	{
		$this->mxdDataToRender = $mxdDataToRender;
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
	function AddObject($strName, $intColumn=COLUMN_ONE, $intContext=HTML_CONTEXT_DEFAULT, $strId=NULL)
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
		$strClassName = "HtmlTemplate_$strName";

		// set up the object
		$arrObject = Array();
		$arrObject['Name']		= $strName;
		$arrObject['Id']		= $strId;
		$arrObject['Column']	= $intColumn;
		$arrObject['Object']	= new $strClassName($intContext, $strId, $this->mxdDataToRender);
		if ($this->IsModal())
		{
			$arrObject['Object']->SetModal(TRUE);
		}
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
		// Include the menu.css, reflex.css and style.css files
		$strRelativePath = Flex::applicationUrlBase();
		echo "\t\t<link rel='stylesheet' type='text/css' href='{$strRelativePath}css/menu.css' />\n";
		echo "\t\t<link rel='stylesheet' type='text/css' href='{$strRelativePath}css/reflex.css' />\n";
		echo "\t\t<link rel='stylesheet' type='text/css' href='{$strRelativePath}css/style.css' />\n";
		
		/* Include all css files in the css directory 
		$strRelativePath = Flex::applicationUrlBase();
		$cssFiles = glob("{$strRelativePath}css/*.css");
		foreach($cssFiles as $cssFile)
		{
			echo "\t\t<link rel='stylesheet' type='text/css' href='{$strRelativePath}css/" . basename($cssFile) . "' />\n";
		}
		*/
	}

	//------------------------------------------------------------------------//
	// RenderHeaderJS
	//------------------------------------------------------------------------//
	/**
	 * RenderHeaderJS()
	 *
	 * Renders the JS part of the page
	 *
	 * Renders the JS part of the page
	 * Any js files that are included in HtmlTemplate constructors, are loaded 
	 * here, as well as the standard ones used by every page
	 * 
	 * @return	void
	 * @method
	 */
	function RenderHeaderJS()
	{
		// Load the old vixen framework for backward compatibility.
		// Do this first to ensure that any new stuff doesn't get broken by it.
		$arrStandardJsFiles = array("vixen", "popup", "dhtml", "ajax", "event_handler", "login", "search", "customer_verification", "validation", "js_auto_loader", "flex_constant");
		// Build the get variables for the javascript.php script
		$strFiles = $this->_GetJsFilesQueryString($arrStandardJsFiles);
		// Echo the reference to the javascript.php script which retrieves all the javascript
		echo "\t\t<script type='text/javascript' src='javascript.php?$strFiles'></script>\n";

		// Add direct links to the following files as they are large and this will result in automatic caching of them
		$strFrameworkDir = Flex::frameworkUrlBase();
		
		echo "\t\t<script type='text/javascript' src='{$strFrameworkDir}javascript/prototype.js' ></script>\n";
		
		//echo "\t\t<script type='text/javascript' src='javascript/ext.js' ></script>\n";
		echo "\t\t<script type='text/javascript' src='{$strFrameworkDir}javascript/jquery.js' ></script>\n";
		echo "\t\t<script type='text/javascript' src='{$strFrameworkDir}javascript/json.js' ></script>\n";
		echo "\t\t<script type='text/javascript' src='{$strFrameworkDir}javascript/flex.js' ></script>\n";
		echo "\t\t<script type='text/javascript' src='{$strFrameworkDir}javascript/sha1.js' ></script>\n";
		echo "\t\t<script type='text/javascript' src='{$strFrameworkDir}javascript/reflex_popup.js' ></script>\n";
		// TODO: Add a non-vixen login handler to flex.js for when the session has timed out

		if (!array_key_exists('*arrJavaScript', $GLOBALS) || !is_array($GLOBALS['*arrJavaScript']))
		{
			$GLOBALS['*arrJavaScript'] = array();
		}

		// Remove any duplicates from the list, as well as files that have already been referenced
		$arrStandardJsFiles		= array_merge($arrStandardJsFiles, array("prototype", "jquery", "json", "flex"));
		$arrRemainingJsFiles	= array_unique($GLOBALS['*arrJavaScript']);
		
		foreach ($arrStandardJsFiles as $strFile)
		{
			if (($intKey = array_search($strFile, $arrRemainingJsFiles)) !== FALSE)
			{
				array_splice($arrRemainingJsFiles, $intKey, 1);
			}
		}
		
		foreach($arrRemainingJsFiles as $strJsFile)
		{
			// Find the relative path of the javascript file
			$strJsFileRelativePath = $this->_GetJsFileRelativePath($strJsFile .".js");
			if ($strJsFileRelativePath !== FALSE)
			{
				// The file was found
				echo "\t\t<script type='text/javascript' src='$strJsFileRelativePath' ></script>\n";
				//echo "\t\t<script type='text/javascript' src='javascript/$strJsFile.js' ></script>\n";
			}
		}
	}

	//------------------------------------------------------------------------//
	// _GetJsFileRelativePath
	//------------------------------------------------------------------------//
	/**
	 * _GetJsFileRelativePath()
	 *
	 * Returns the relative path of the javascript file in question (including the filename)
	 *
	 * Returns the relative path of the javascript file in question (including the filename)
	 * It first looks in the application's javascript directory and if it is not found there
	 * then it will look in the app framework's javascript directory
	 *
	 * @param	string $strJsFile	javascript file to find.  Include the .js extension
	 *
	 * @return	string				relative path of the javascript file (ie ../management/javascript/hello.js)
	 * @method
	 */
	private function _GetJsFileRelativePath($strJsFile)
	{
		// Look for the file in the application's javascript dir
		$strFile = $strFile = Flex::getRelativeBase() . Flex::relativeApplicationBase() . "javascript". DIRECTORY_SEPARATOR . $strJsFile;
		
		$arrFiles = glob($strFile);
		if (is_array($arrFiles) && count($arrFiles) == 1)
		{
			// The file was found
			return Flex::applicationUrlBase() . "javascript". DIRECTORY_SEPARATOR . $strJsFile;
		}
		
		// Look for the file in the application's javascript dir
		$strFile = $strFile = Flex::getRelativeBase() . Flex::relativeFrameworkBase() . "javascript". DIRECTORY_SEPARATOR . $strJsFile;
		$arrFiles = glob($strFile);
		if (is_array($arrFiles) && count($arrFiles) == 1)
		{
			// The file was found
			return Flex::frameworkUrlBase() . "javascript". DIRECTORY_SEPARATOR . $strJsFile;
		}
		
		// The file could not be found
		return FALSE;
		
	}

	//------------------------------------------------------------------------//
	// RenderJS
	//------------------------------------------------------------------------//
	/**
	 * RenderJS()
	 *
	 * Includes any javascript files required of a popup page
	 *
	 * Includes any javascript files required of a popup page
	 * Any js files that are included in HtmlTemplate constructors, are loaded 
	 * here.  This is used when a popup is loaded, assuming it is called from
	 * within the layout template of the popup (popup_layout)
	 * 
	 * @return	void
	 * @method
	 */
	function RenderJS()
	{
		if (!array_key_exists('*arrJavaScript', $GLOBALS) || !is_array($GLOBALS['*arrJavaScript']))
		{
			// There is no javascript to include
			return;
		}

		// Remove any duplicates from the list
		$arrJsFiles = array_unique($GLOBALS['*arrJavaScript']);

		foreach($arrJsFiles as $strJsFile)
		{
			$strJsFileRelativePath = $this->_GetJsFileRelativePath($strJsFile .".js");
			echo "\t\t<script type='text/javascript' src='$strJsFileRelativePath' ></script>\n";
			//echo "\t\t<script type='text/javascript' src='javascript/$strJsFile.js' ></script>\n";
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
				$fltStartTime = microtime(TRUE);
				echo "<div id='{$arrObject['Id']}'>\n";
				$arrObject['Object']->SetMode($this->_intTemplateMode, $this->_objAjax);
				$arrObject['Object']->Render();
				
				// Check for Debug mode
				if ($GLOBALS['bolDebugMode'])
				{
					// Display how long it took to render the HtmlTemplate
					$fltTimeTaken = number_format(microtime(TRUE) - $fltStartTime, 4, ".", "");
					echo "<div>Time taken to render {$arrObject['Name']}: $fltTimeTaken sec</div>";
				}
				
				echo "</div>\n";
			}
		}
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
	function RenderHeader($bolWithSearch=TRUE)
	{	
		$strBaseDir = Flex::getUrlBase();

		echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
	<head>
		<link rel=\"shortcut icon\" HREF=\"{$strBaseDir}img/favicon.ico\">
		<link rel=\"icon\" href=\"{$strBaseDir}img/favicon.ico\">
		<meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\" />
		<meta name=\"generator\" content=\"Flex\" />
		<title>Flex - $this->_strPageName</title>
		<base href='$strBaseDir'/>\n";
		$this->RenderHeaderJS();
		$this->RenderCSS();
		echo "
	</head>
	<body onload='Vixen.Init();'>
		<div id='PopupHolder'></div>
		<div id='VixenTooltip' style='display: none;' class='VixenTooltip'></div>\n";
	}

	//------------------------------------------------------------------------//
	// RenderHeaderFlexModal
	//------------------------------------------------------------------------//
	/**
	 * RenderHeaderFlexModal()
	 *
	 * Renders the header of a 'flex modal page''
	 *
	 * Renders the header of a 'flex modal page''
	 * 
	 * @method
	 */
	function RenderHeaderFlexModal()
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
		
		echo "<html><head><link rel=\"shortcut icon\" HREF=\"./img/favicon.ico\"><link rel=\"icon\" href=\"./img/favicon.ico\"><meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>\n";
		echo "<title>Flex - $this->_strPageName</title>\n";
		echo "<base href='$strBaseDir'/>\n";
		$this->RenderHeaderJS();
		$this->RenderCSS();
		echo "</head>\n";
		echo "<body class='flexModalWindow'>\n";
	}
}

?>
