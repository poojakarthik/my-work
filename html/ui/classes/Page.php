<?php

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
	protected $_objAjax;
	protected $_intTemplateMode;
	protected $_bolModal = FALSE;

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
	protected $_strPageName;
	
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
	protected $_strPageLayout;

	//------------------------------------------------------------------------//
	// _strStyleOverride
	//------------------------------------------------------------------------//
	/**
	 * _strStyleOverride
	 *
	 * Stores css styling used to override the standard styling of the page, which is defined in the LayoutTemplate
	 *
	 * Stores css styling used to override the standard styling of the page, which is defined in the LayoutTemplate
	 *
	 * @type		string
	 *
	 * @property
	 */
	protected $_strStyleOverride;

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
	protected $_arrObjects = Array();
	
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
	// GetName
	//------------------------------------------------------------------------//
	/**
	 * GetName()
	 *
	 * Gets the name of the page (the title of the webpage)
	 *
	 * Gets the name of the page (the title of the webpage)
	 * 
	 * @return	string	$strName		the value the page name is set to
	 *
	 * @method
	 */
	function GetName()
	{
		return $this->_strPageName;
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
		$strMd5 = md5_file(MODULE_DEFAULT_CSS);
		echo "\t\t<link rel='stylesheet' type='text/css' href='css.php?v=$strMd5' />\n";
		
		//$arrCssFiles = array("menu.css", "reflex.css");
		$arrCssFiles = array("menu.css", "style.css");
		foreach ($arrCssFiles as $strCssFile)
		{
			echo "\t\t<link rel='stylesheet' type='text/css' href='./css/{$strCssFile}' />\n";
		}
		
		
		/*  This code loads all files that are in the html/ui/css/ dir
		$cssFiles = glob(Flex::getBase() . '/html/ui/css/*.css');
		foreach($cssFiles as $cssFile)
		{
			echo "\t\t<link rel='stylesheet' type='text/css' href='./css/" . basename($cssFile) . "' />\n";
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
		// Include reference to all the standard javascript files, which should be included on every page
		$arrStandardJsFiles = array("vixen", "popup", "dhtml", "ajax", "event_handler", "login", "search", "customer_verification", "validation", "js_auto_loader");
		$strFiles = $this->_GetJsFilesQueryString($arrStandardJsFiles);
		echo "\t\t<script type='text/javascript' src='javascript.php?$strFiles'></script>\n";

		// Add direct links to the following files as they are large and this will result in automatic caching of them
		$strFrameworkDir = Flex::frameworkUrlBase();
		echo "\t\t<script type='text/javascript' src='{$strFrameworkDir}javascript/prototype.js' ></script>\n";
		echo "\t\t<script type='text/javascript' src='{$strFrameworkDir}javascript/jquery.js' ></script>\n";
		echo "\t\t<script type='text/javascript' src='{$strFrameworkDir}javascript/json.js' ></script>\n";
		echo "\t\t<script type='text/javascript' src='{$strFrameworkDir}javascript/flex.js' ></script>\n";
		echo "\t\t<script type='text/javascript' src='{$strFrameworkDir}javascript/sha1.js' ></script>\n";
		echo "\t\t<script type='text/javascript' src='{$strFrameworkDir}javascript/reflex_popup.js' ></script>\n";
		echo "\t\t<script type='text/javascript' src='{$strFrameworkDir}javascript/flex_constant.js' ></script>\n";
		
		// Include reference to all other javascript files required of the page
		if (!array_key_exists('*arrJavaScript', $GLOBALS) || !is_array($GLOBALS['*arrJavaScript']))
		{
			$GLOBALS['*arrJavaScript'] = Array();
		}

		$arrRemainingJsFiles	= array_unique($GLOBALS['*arrJavaScript']);
		$arrStandardJsFiles		= array_merge($arrStandardJsFiles, array("prototype", "jquery", "json", "flex", "flex_constant", "sha1", "reflex_popup"));

		$arrRemainingJsFilesToInclude = array();
		foreach ($arrRemainingJsFiles as $strFile)
		{
			if (!in_array($strFile, $arrStandardJsFiles))
			{
				// The file is not in the standard list, so include it
				$arrRemainingJsFilesToInclude[] = $strFile;
			}
		}

		// Build the get variables for the javascript.php script
		if (count($arrRemainingJsFilesToInclude))
		{
			$strFiles = $this->_GetJsFilesQueryString($arrRemainingJsFilesToInclude);
			echo "\t\t<script type='text/javascript' src='javascript.php?$strFiles'></script>\n";
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
		if (!is_array($GLOBALS['*arrJavaScript']))
		{
			// There is no javascript to include
			return;
		}
		
		// Remove any duplicates from the list
		$arrJsFiles = array_unique($GLOBALS['*arrJavaScript']);
		
		// Build the get variables for the javascript.php script
		$strFiles = $this->_GetJsFilesQueryString($arrJsFiles);
		
		// Echo the reference to the javascript.php script which retrieves all the javascript
		echo "<script type='text/javascript' src='javascript.php?$strFiles'></script>\n";
		
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
		echo "\n\t</body>\n</html>\n";
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
		
		if (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'])
		{
			$strBaseDir = "https://{$_SERVER['SERVER_NAME']}$strBaseDir";
		}
		else
		{
			$strBaseDir = "http://{$_SERVER['SERVER_NAME']}$strBaseDir";
		}
// DTD has not been defined in framework2.  It is defined in framework3 (ApplicationPage::RenderHeader)		
//		echo "
//<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
//<html xmlns=\"http://www.w3.org/1999/xhtml\">
	echo "
<html>
	<head>
		<link rel=\"shortcut icon\" href=\"{$strBaseDir}img/favicon.ico\" />
		<link rel=\"icon\" href=\"{$strBaseDir}img/favicon.ico\" />
		<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1' />
		<title>Flex - {$this->_strPageName}</title>
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
		echo "<body onload='Vixen.Init();' class='flexModalWindow'>\n";
	}
	
	//------------------------------------------------------------------------//
	// RenderClientHeader
	//------------------------------------------------------------------------//
	/**
	 * RenderClientHeader()
	 *
	 * Renders the header of a page, for the client app (web_app)
	 *
	 * Renders the header of a page, for the client app (web_app)
	 * 
	 * @method
	 */
	function RenderClientHeader()
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

		/* This was used to guarantee the most recent javascript files were retrieved
		  every time a page was requested */
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
		
		echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\"><html><head>
		<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">
		<META HTTP-EQUIV=\"Expires\" CONTENT=\"-1\">
		<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>\n";
		echo "<title>Flex - $this->_strPageName</title>\n";
		echo "<base href='$strBaseDir'/>\n";
		$this->RenderHeaderJS();
		$this->RenderCSS();
		echo "</head>\n";
		//echo "<body onload='Vixen.Init()'>\n";
		echo "<body>\n";
		
		// the following div holds any popup windows that are instantiated within the page
		echo "<div id='PopupHolder'></div>\n";
	}
	
	
	//------------------------------------------------------------------------//
	// RenderClientPOPHeader
	//------------------------------------------------------------------------//
	/**
	 * RenderClientPOPHeader()
	 *
	 * Renders the header of a page, for the client app (web_app)
	 *
	 * Renders the header of a page, for the client app (web_app)
	 * 
	 * @method
	 */
	function RenderClientPOPHeader()
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

		/* This was used to guarantee the most recent javascript files were retrieved
		  every time a page was requested */
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
		
		echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\"><html><head>
		<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">
		<META HTTP-EQUIV=\"Expires\" CONTENT=\"-1\">
		<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>\n";
		echo "<title>Flex - $this->_strPageName</title>\n";
		echo "<base href='$strBaseDir'/>\n";
		echo "\t\t<link rel='stylesheet' type='text/css' href='./css/popup.css' />\n";
		echo "</head>\n";
		echo "<body>\n";
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
		if (!BreadCrumb()->HasBreadCrumbs())
		{
			return;
		}
		
		$strHtmlCode = "<div id='BreadCrumbMenu'>\n";
		foreach (DBO()->BreadCrumb as $objProperty)
		{
			$strHtmlCode .= "<a href='{$objProperty->Value}'>{$objProperty->Label}</a> &gt; ";
		}
		
		// Add the current page as a breadcrumb
		$mixCurrentPage = BreadCrumb()->GetCurrentPage();
		if ($mixCurrentPage !== FALSE)
		{
			// the current page has been defined.  Attach it to the bread crumb trail
			$strHtmlCode .= $mixCurrentPage;
		}
		
		// Remove the last 6 chars from html code, if it is equal to " &gt; "
		if (substr($strHtmlCode, -6) == " &gt; ")
		{
			$strHtmlCode = substr($strHtmlCode, 0, -6);
		}
		
		$strHtmlCode .= "\n</div>\n";
		
		echo $strHtmlCode;
	}
	
	//------------------------------------------------------------------------//
	// RenderVixenHeader DEPRECIATED
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
	// RenderFlexHeader
	//------------------------------------------------------------------------//
	/**
	 * RenderFlexHeader()
	 *
	 * Renders the Flex header including the context menu
	 *
	 * Renders the Flex header including the context menu
	 * (This is the new Flex header with the horizontal context menu and search functionality)
	 * 
	 * @param	bool	$bolWithSearch		optional, defaults to TRUE.  Set to FALSE to suppress rendering of search controls and user details
	 * @param	bool	$bolWithMenu		optional, defaults to TRUE.  Set to FALSE to suppress rendering of the context menu
	 * @param	bool	$bolWithBreadCrumbs	optional, defaults to TRUE.  Set to FALSE to suppress rendering of the bread crumb menu
	 * 
	 * @method
	 */
	function RenderFlexHeader($bolWithSearch=TRUE, $bolWithMenu=TRUE, $bolWithBreadCrumbs=TRUE)
	{
		echo "
		<div id='header' name='header'>
			<div id='logo' onclick='window.location.href=\"../admin/reflex.php/Console/View/\"'>
				<div id='blurb' name='blurb'>Flex Customer Management System</div>
			</div>\n";

		if ($bolWithSearch && Flex::loggedIn())
		{
			$this->RenderSearchField();
		}
		
		if ($bolWithMenu)
		{
			$this->RenderContextMenu();
		}
		
		if ($bolWithBreadCrumbs)
		{
			$this->RenderBreadCrumbMenu();
		}
		
		// Close the header div
		echo "\t\t</div> <!-- header -->\n";
		
	}

	//------------------------------------------------------------------------//
	// RenderSearchField
	//------------------------------------------------------------------------//
	/**
	 * RenderSearchField()
	 *
	 * Renders the user details and search controls in the page header
	 *
	 * Renders the user details and search controls in the page header
	 * 
	 * @method
	 */
	function RenderSearchField()
	{
		$strUserName = Flex::getDisplayName();
		
		$strUserPreferencesLink = Href()->ViewUserDetails();
		
		$strLastConstraint	= (array_key_exists("QuickSearch_Constraint", $_COOKIE))? htmlspecialchars($_COOKIE['QuickSearch_Constraint'], ENT_QUOTES) : "";
		$mixLastSearchType	= (array_key_exists("QuickSearch_SearchType", $_COOKIE))? $_COOKIE['QuickSearch_SearchType'] : "";

		$strCategoryOptions = "";
		$arrSearchTypes = Customer_Search::getSearchTypes();
		foreach ($arrSearchTypes as $intSearchType=>$arrSearchType)
		{
			$strSelected = ($intSearchType == $mixLastSearchType) ? "selected='selected'" : "";
			$strCategoryOptions .= "\n\t\t\t\t\t\t\t<option value='$intSearchType' $strSelected>{$arrSearchType['Name']}</option>";
		}
		$strSelected = ($mixLastSearchType == "tickets")? "selected='selected'" : "";
		$strCategoryOptions .= "\n\t\t\t\t\t\t\t<option value='tickets' $strSelected>Tickets</option>";
		
		
		$mixKbAdmin = NULL;
		// The default menu links.
		$mixMenuLinks = "
		Logged in as: $strUserName
		| <a onclick='$strUserPreferencesLink' >Preferences</a>
		| <a onclick='Vixen.Logout();'>Logout</a>";

		// Check kb permissions and check that the Knowledge Base module is active
		if(AuthenticatedUser()->UserHasPerm(PERMISSION_KB_USER) && Flex_Module::isActive(FLEX_MODULE_KNOWLEDGE_BASE))
		{
			// If the user is a kb_admin an extra flag is added.
			if(AuthenticatedUser()->UserHasPerm(PERMISSION_KB_ADMIN_USER))
			{
				$mixKbAdmin = "<input type=\"hidden\" name=\"strAdmin\" value=\"1\" />\n";
			}
			// If the user is allowed to access the kb system the menu links change to below:
			$mixMenuLinks = "
			<script type=\"text/javascript\">
			function redirectOutput(kbform)
			{
				var w = window.open('about:blank','Knowledg_Base_Popup','width=680,height=600,resizable=1,menubar=0,toolbar=0,location=0,directories=0,scrollbars=1,status=1');
				kbform.target = 'Knowledg_Base_Popup';
			}
			</script>
			<form method=\"post\" name=\"kbform\" target=\"Knowledg_Base_Popup\" id=\"kbform\" action=\"" . $GLOBALS['**arrCustomerConfig']['KnowledgeBase']['URI'] . "\">
			Logged in as: $strUserName
			$mixKbAdmin<input type=\"hidden\" name=\"strUsername\" value=\"$strUserName\" />
			<input type=\"hidden\" name=\"mixUsername\" value=\"" . $GLOBALS['**arrCustomerConfig']['KnowledgeBase']['User'] . "\" />
			<input type=\"hidden\" name=\"mixPassword\" value=\"" . $GLOBALS['**arrCustomerConfig']['KnowledgeBase']['Password'] . "\" />
			| <a onclick=\"redirectOutput(this); var elemform = getElementById('kbform'); elemform.submit();\">Knowledge Base</a>	
			| <a onclick='$strUserPreferencesLink' >Preferences</a>
			| <a onclick='Vixen.Logout();'>Logout</a>
			</form>";
		}
		echo "
			<div id='person_search' name='person_search'>
				<div id='person' name='person'>
					$mixMenuLinks
				</div>
				<div id='search_bar' name='search_bar'>
					<form action='#' onsubmit='FlexSearch.quickSearch();return false;'>
						Search: 
						<input type='text' id='search_string' name='search_string' value='$strLastConstraint' onkeypress='FlexSearch.quickSearchOnEnter(event)' />
						<select name='category' id='quick_search_category'>$strCategoryOptions
						</select>
						<input type='submit' id='Search' name='Search' value='Search'/>
					</form>
				</div>
			</div> <!-- person_search-->\n";
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
		// Build array
		$arrContextMenu = ContextMenu()->BuildArray();
		
		echo "
			<div id='nav' name='nav'>\n";
		self::renderMenuLevel($arrContextMenu);
		echo "
			</div> <!-- nav -->\n";
		return;
	}

	private function renderMenuLevel($arrItems, $intLevel=0)
	{
		$strIndent = str_repeat("\t", 4 + (2 * $intLevel));
		if (!is_array($arrItems) || empty($arrItems))
		{
			return;
		}
		echo "$strIndent<ul>\n";
		foreach ($arrItems as $strLabel => $mixValue)
		{
			if (is_string($mixValue))
			{
				// $mixValue is an action/link
				if (strtolower(substr($mixValue, 0, 11)) == "javascript:")
				{
					// The action is javascript
					$mixValue = substr($mixValue, 11);
					echo "$strIndent\t<li><a onclick=\"" . htmlspecialchars($mixValue, ENT_QUOTES) . "\">" . htmlspecialchars($strLabel) . "</a></li>\n";
				}
				else
				{
					// The action is a href
					echo "$strIndent\t<li><a href=\"" . htmlspecialchars($mixValue, ENT_QUOTES) . "\">" . htmlspecialchars($strLabel) . "</a></li>\n";
				}
			}
			else
			{
				// $mixValue is a submenu
				$strClass	= ($intLevel === 0) ? '' : "class='dropright'";
				echo "$strIndent\t<li class='dropdown'><a {$strClass}>" . htmlspecialchars($strLabel, ENT_QUOTES) . "</a>\n";
				self::renderMenuLevel($mixValue, $intLevel + 1);
				echo "$strIndent\t</li>\n";
			}
		}
		echo "$strIndent</ul>\n";
	}

	
	//------------------------------------------------------------------------//
	// RenderClientAppHeader DEPRECIATED
	//------------------------------------------------------------------------//
	/**
	 * RenderClientAppHeader()
	 *
	 * Renders the Client App header
	 *
	 * Renders the Client App header
	 * 
	 * @method
	 */
	function RenderClientAppHeader()
	{
		$objHeader = new HtmlTemplateClientAppHeader(HTML_CONTEXT_DEFAULT);
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
	
	//------------------------------------------------------------------------//
	// SetModal
	//------------------------------------------------------------------------//
	/**
	 * SetModal()
	 *
	 * Sets the modality of the template
	 * 
	 * Sets the modality of the template
	 *
	 * @param		int	$bolModal	Whether the page is to be rendered as a modal (complete) page
	 *
	 * @return		void
	 * @method
	 *
	 */
	function SetModal($bolModal)
	{
		$this->_bolModal = $bolModal;
	}
	
	//------------------------------------------------------------------------//
	// IsModal
	//------------------------------------------------------------------------//
	/**
	 * IsModal()
	 *
	 * Sets the modality of the template
	 * 
	 * Sets the modality of the template
	 *
	 * @param		void
	 *
	 * @return		boolean	whether the page is to be rendered as modal (complete) or not
	 * @method
	 *
	 */
	function IsModal()
	{
		return $this->_bolModal;
	}

	//------------------------------------------------------------------------//
	// SetStyleOverride
	//------------------------------------------------------------------------//
	/**
	 * SetStyleOverride()
	 *
	 * Allows the programmer to override the styling of the page, at the page level
	 * 
	 * Allows the programmer to override the styling of the page, at the page level
	 * This will probably only be used with the popup_layout LayoutTemplate
	 *
	 * @param		string	$strStyleOverride	css styling
	 *											What you would put in the "style" attribute of any HTML element tag
	 *											ie $strStyleOverride = "border: 1px solid #FFFFFF; padding: 3px;" etc
	 *
	 * @return		void
	 * @method
	 *
	 */
	function SetStyleOverride($strStyleOverride)
	{
		$this->_strStyleOverride = $strStyleOverride;
	}
	
	//------------------------------------------------------------------------//
	// _GetJsFilesQueryString
	//------------------------------------------------------------------------//
	/**
	 * _GetJsFilesQueryString()
	 *
	 * Builds the query string for the javascript.php script which retrieves the javascript files, when requested, so long as there haven't been any changes
	 *
	 * Builds the query string for the javascript.php script which retrieves the javascript files, when requested, so long as there haven't been any changes
	 * It appends an md5 sum to the end of it which is generated by concatinating the md5 sum of each javascript file requested,
	 * then doing an md5 sum on that.
	 *
	 * @param	array		$arrFilenames	indexed array of javascript files that are required of the page.
	 * 										These will filenames should not include the ".js" extension 
	 *
	 * @return	string		query string in the form "File[]=<jsFile1>.js&File[]=<jsFile2>.js&v=<md5Sum>"
	 * @method
	 */
	protected function _GetJsFilesQueryString($arrFilenames)
	{
		$strFiles	= '';
		$strMd5		= '';
		
		VixenRequire('html/ui/javascript_builder.php');
		
		foreach ($arrFilenames as $strFilename)
		{
			$strFilename .= ".js";
			$strFiles .= "File[]=$strFilename&amp;";
	
			// If nothing has been requested, return FALSE;
			if (trim($strFilename) == "")
			{
				continue;
			}
	
			// Try and find the javascript file
			if (HasJavascriptFile($strFilename, LOCAL_BASE_DIR . "javascript"))
			{
				// A local js file has been found.  Include it
				$strMd5 .= md5_file(LOCAL_BASE_DIR. "javascript/$strFilename");
			}
			else if (HasJavascriptFile($strFilename, FRAMEWORK_BASE_DIR . "javascript"))
			{
				// The file has been found in the framework.  Include it
				$strMd5 .= md5_file(FRAMEWORK_BASE_DIR. "javascript/$strFilename");
			}
		}
		return $strFiles . 'v=' . md5($strMd5);
	}
	
	
}

?>
