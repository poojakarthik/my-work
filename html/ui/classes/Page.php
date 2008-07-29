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
		$cssFiles = glob(GetVixenBase() . '/html/ui/css/*.css');
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
		// The Javascript autoloader is no longer used as it cannot guarantee 
		// the files are interpreted before they are required by explicit calls 
		// to the functions and objects they contain.  We can therefore no longer
		// guarantee that a js file is loaded only once, but so long as any objects
		// created in the js files, are only created if they don't already exist,
		// then this shouldn't be a problem.
		//echo "<script type='text/javascript' src='" . JAVASCRIPT_BASE_DIR . "javascript/autoloader.js' ></script>\n";
		//echo "<script type='text/javascript'>VixenSetJavascriptBaseDir('". JAVASCRIPT_BASE_DIR ."')</script>\n";
		
		/*
		echo "<script type='text/javascript' src='javascript.php?File=vixen.js' ></script>\n";
		echo "<script type='text/javascript' src='javascript.php?File=menu.js' ></script>\n";
		echo "<script type='text/javascript' src='javascript.php?File=popup.js' ></script>\n";
		echo "<script type='text/javascript' src='javascript.php?File=dhtml.js' ></script>\n";
		echo "<script type='text/javascript' src='javascript.php?File=ajax.js' ></script>\n";
		echo "<script type='text/javascript' src='javascript.php?File=event_handler.js' ></script>\n";
		*/
		
		// Prepend the js files that all pages require, to the list of js files to include
		if (!array_key_exists('*arrJavaScript', $GLOBALS) || !is_array($GLOBALS['*arrJavaScript']))
		{
			$GLOBALS['*arrJavaScript'] = Array();
		}
		array_unshift($GLOBALS['*arrJavaScript'], "vixen", "menu", "popup", "dhtml", "ajax", "event_handler", "login");
		
		// Remove any duplicates from the list
		$arrJsFiles = array_unique($GLOBALS['*arrJavaScript']);
		
		// Build the get variables for the javascript.php script
		$strFiles = $this->_GetJsFilesQueryString($arrJsFiles);

		// Echo the reference to the javascript.php script which retrieves all the javascript
		echo "<script type='text/javascript' src='javascript.php?$strFiles'></script>\n";
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
		if (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'])
		{
			$strBaseDir = "https://{$_SERVER['SERVER_NAME']}$strBaseDir";
		}
		else
		{
			$strBaseDir = "http://{$_SERVER['SERVER_NAME']}$strBaseDir";
		}
		
		// The following code is supposed to make the browser retrieve new js 
		// files every time, although I don't think it works.  I think it was more so
		// for testing purposes because the most recent js files weren't being used
		// but, for general operation, you want the user's browser to cache the js,
		// as it shouldn't be being changed that often
		
		/* 
		 * This was prohibitting the effective use of going back through the browser history. 
		 * (popups weren't being displayed; the page was always reloading)
		 * It should probably be updated so that the page expires within an hour of being loaded
		 *
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', FALSE);
		header('Pragma: no-cache');
		*/
		
		echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>\n";
		echo "<title>Flex - $this->_strPageName</title>\n";
		echo "<base href='$strBaseDir'/>\n";
		$this->RenderHeaderJS();
		$this->RenderCSS();
		echo "</head>\n";
		echo "<body onload='Vixen.Init();'>\n";
		
		// the following div holds any popup windows that are instantiated within the page
		echo "<div id='PopupHolder'></div>\n";
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
		 * every time a page was requested
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
		*/
	
		echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>\n";
		echo "<title>Flex - $this->_strPageName</title>\n";
		echo "<base href='$strBaseDir'/>\n";
		$this->RenderHeaderJS();
		$this->RenderCSS();
		echo "</head>\n";
		echo "<body onload='Vixen.Init()'>\n";
		
		// the following div holds any popup windows that are instantiated within the page
		echo "<div id='PopupHolder'></div>\n";
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
		
		// add to html and run js
		echo "<div id='VixenMenu' class='ContextMenu'></div>\n";
		echo "<script type='text/javascript'>Vixen.Menu.objMenu = $strContextMenu; Vixen.Menu.Render();</script>\n";
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
	 * @method
	 */
	function RenderFlexHeader($bolWithSearch=TRUE)
	{
		echo "
	<div id=\"header\" name=\"header\">
		<div id=\"logo\">
			<div id=\"blurb\" name=\"blurb\">Flex Customer Management System</div>
		</div>\n";
/*
		if ($bolWithSearch && Flex::loggedIn())
		{
			$this->RenderSearchField();
		}
*/		
	}

	function RenderSearchField()
	{
/*		
		$strUserName = Flex::getDisplayName();
*/		
		echo "
		<div id=\"person_search\" name=\"person_search\">
			<div id=\"person\" name=\"person\">
				Logged in as: $strUserName
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
			$strFiles .= "File[]=$strFilename&";
	
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
