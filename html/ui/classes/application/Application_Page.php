<?php

class Application_Page extends Page
{
	protected $mxdDataToRender = NULL;

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
		$cssFiles = glob(Flex::getBase() . '/html/ui/css/*.css');
		foreach($cssFiles as $cssFile)
		{
			echo "\t\t<link rel='stylesheet' type='text/css' href='./css/" . basename($cssFile) . "' />\n";
		}
		
		
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
		$arrJsFiles = array("vixen", "popup", "dhtml", "ajax", "event_handler", "login");
		// Build the get variables for the javascript.php script
		$strFiles = $this->_GetJsFilesQueryString($arrJsFiles);
		// Echo the reference to the javascript.php script which retrieves all the javascript
		echo "\t\t<script type='text/javascript' src='javascript.php?$strFiles'></script>\n";

		// Add direct links to the following files as they are large and this will result in automatic caching of them
		echo "\t\t<script type='text/javascript' src='javascript/prototype.js' ></script>\n";
		
		//echo "\t\t<script type='text/javascript' src='javascript/ext.js' ></script>\n";
		echo "\t\t<script type='text/javascript' src='javascript/jquery.js' ></script>\n";
		echo "\t\t<script type='text/javascript' src='javascript/json.js' ></script>\n";
		echo "\t\t<script type='text/javascript' src='javascript/flex.js' ></script>\n";
		// TODO: Add a non-vixen login handler to flex.js for when the session has timed out

		// Prepend the js files that all pages require, to the list of js files to include
		if (!array_key_exists('*arrJavaScript', $GLOBALS) || !is_array($GLOBALS['*arrJavaScript']))
		{
			$GLOBALS['*arrJavaScript'] = Array();
		}

		// Remove any duplicates from the list
		$arrJsFiles = array_unique($GLOBALS['*arrJavaScript']);

		foreach($arrJsFiles as $strJsFile)
		{
			echo "\t\t<script type='text/javascript' src='javascript/$strJsFile.js' ></script>\n";
		}
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
			echo "\t\t<script type='text/javascript' src='javascript/$strJsFile.js' ></script>\n";
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

	function RenderSearchField_REDUNDANT()
	{
		$usename = Flex::getDisplayName();
		echo "
			<div id=\"person_search\" name=\"person_search\">
				<div id=\"person\" name=\"person\">
					Logged in as: $usename
				<!--	| <a href=\"#\">Preferences</a> -->
					| <a href=\"logout.php\">Logout</a>
				</div>
				<div id=\"search_bar\" name=\"search_bar\">
					Search: 
					<input type=\"text\" id=\"search_string\" name=\"search_string\" />
				</div>
			</div>\n";
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
		
		echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>\n";
		echo "<title>Flex - $this->_strPageName</title>\n";
		echo "<base href='$strBaseDir'/>\n";
		$this->RenderHeaderJS();
		$this->RenderCSS();
		echo "</head>\n";
		echo "<body class='flexModalWindow'>\n";
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
	function RenderContextMenu_REDUNDANT()
	{
		// build array
		$arrContextMenu = ContextMenu()->BuildArray();
		echo "
			<div id=\"nav\" name=\"nav\">\n";
		self::renderMenuLevel($arrContextMenu);
		echo "
			</div>
		</div>\n"; // Close the header
		return;
	}

	private function renderMenuLevel2_REDUNDANT($items, $level=0)
	{
		$indent = str_repeat("\t", 4 + (2 * $level));
		if (!is_array($items) || empty($items))
		{
			return;
		}
		echo "$indent<ul>\n";
		foreach ($items as $label => $value)
		{
			if (is_string($value))
			{
				echo "$indent\t<li><a href=\"" . addslashes($value) . "\">" . htmlspecialchars($label) . "</a></li>\n";
			}
			else
			{
				echo "$indent\t<li><span>" . htmlspecialchars($label) . "</span>\n";
				self::renderMenuLevel($value, $level + 1);
				echo "$indent\t</li>\n";
			}
		}
		echo "$indent</ul>\n";
	}


}

?>
