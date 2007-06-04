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
				echo "<script type='text/javascript' src='" . JAVASCRIPT_BASE_DIR . "javascript/$strValue.js' ></script>\n";
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
	
	//------------------------------------------------------------------------//
	// RenderHeader
	//------------------------------------------------------------------------//
	/**
	 * RenderHeader()
	 *
	 * Renders a single column of the page
	 *
	 * Renders a single column of the page
	 * 
	 * @method
	 */
	function RenderHeader()
	{
		// build referring string from _GET
		$strTarget = DBO()->Account->Id->Value;
		
		echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>";
		echo "<title>viXen : Employee Intranet System - $this->_strPageName</title>";
		$this->RenderJS();
		$this->RenderCSS();
		echo "
		
		<script type='text/javascript'>
var iState = 0;
function toggleBox(szDivID) // 1 visible, 0 hidden
{
    var obj = document.getElementById(szDivID);
    obj.style.visibility = iState ? 'visible' : 'hidden'; 
	iState ^= 1;
	
	dragObj = document.getElementById('TopBar');
	dragObj.addEventListener('mousedown', LoginHandler, false);
	
}

function LoginHandler(event)
{
	aphplix.dhtml.drag(event, 'LoginBox');
}
//dragObject = document.getElementById('TopBar');

</script>
";
	
		echo "</head> ";
		
		echo "
<body onload='toggleBox(\"LoginBox\");'>

	<div id='LoginBox' class='LoginBox' style='left: 400px; top:300px;'>
		<div id='TopBar' class='TopBar'>
		TelcoBlue Internal System
		</div>
			
			
			<form method='POST' action='account_view.php?Account.Id=$strTarget'>
				<table>
					<tr>
						<td>
							<label for='UserName'>Username:</label>
						</td>
						<td>
							<input type='text' name='UserName' id='UserName' maxlength='21'/>
						</td>
					</tr>
					<tr>
						<td>
							<label for='PassWord'>Password:</label>
						</td>
						<td>
							<input type='password' name='PassWord' />
						</td>
					</tr>
					<tr>
						<td/>
						<td>
							<input type='submit' value='Continue &#xBB;' class='Right'/>
						</td>
					</tr>
				</table>
			</form>
	</div>
    <div class='Logo'>
      <img src='img/template/vixen_logo.png' border='0'>
    </div>
    <div id='Header' class='sectionContainer'>
      <span class='LogoSpacer'></span>
      <div class='sectionContent'>
        <div class='Left'>
			TelcoBlue Internal Management System
		</div>
        <div class='Right'>
			Version 7.03
									
			<div class='Menu_Button'>
				<a href='#' onclick=\"return ModalDisplay ('#modalContent-ReportBug')\">
					<img src='img/template/bug.png' alt='Report Bug' title='Report Bug' border='0'>
				</a>
			</div>
		</div>


        <div class='Clear'></div>
      </div>
      <div class='Clear'></div>
    </div>
    <div class='Clear'></div>
    <div class='Seperator'></div>";
	
	echo "<table class='Listing' border='0' cellpadding='3' cellspacing='0' width='auto'>
              <tbody><tr class='First'>
                <th width='30'>#</th>
                <th>Invoice #</th>

                <th>Date</th>
                <th class='thRight'>Invoice Amount</th>
                <th class='thRight'>Applied Amount</th>
                <th class='thRight'>Amount Owing</th>
                <th class='thRight'>Sent</th>
                <th class='thRight'>Blank</th>

              </tr>";
	$strTableName = "Invoices";
	$intMaxRows = 10;
	for ($i = 0; $i <= $intMaxRows; $i++)
	{
		?>
        <tr id='<?php echo $strTableName . $i; ?>' class='Odd' onmouseover='highlight(this.id, "<?php echo $strTableName; echo '", "'; echo $intMaxRows;  ?>");' onclick='toggleSelection(this.id, "<?php echo $strTableName; echo '", "'; echo $intMaxRows;  ?>")'>
		<?php
		echo "
                <td>$i.</td>
                <td>
                  <a href='javascript:ToggleSlide('mydiv');'>3000306989</a>
                </td>
                <td>1/02/2007</td>

                <td class='Currency'>" . rand() . "</td>
                <td class='Currency'>$0.00</td>
                <td class='Currency'>
                  <strong>
                    <span class='Red'>$2,405.15</span>
                  </strong>
                </td>

                <td align='right'>
                  <img src='console_admin_files/btn_unlocked.png' height=20px></img>
                </td>
				<td align='right'> 
					<img src='console_admin_files/pdf.png' height=20px></img>
					<img src='console_admin_files/invoice.png' height=20px></img>
				</td>
              </tr>
			  <tr><td colspan=7><div id='mydiv' style='display:none; overflow:hidden; height:180px;'>

			  <table cellspacing=2 align='right' >
			  <th>
			  	<td>Date</td>
				<td style='padding-left:30px'>Total Applied</td>
				<td style='padding-left:30px'>Type</td>
			</th>  	
				<tr align='right' onmouseover='return escape(getHTML(1))'>
					<td></td><td>16/03/2007</td><td class='Currency' >$2,522.20</td><td>BPay</td>

				</tr>
				<tr align='right'>
					<td></td><td>03/02/2007</td><td class='Currency'>$678.20</td><td>Eftpos</td>
				</tr>
				<tr align='right'>
					<td></td><td>19/08/2006</td><td class='Currency'>$1,034.20</td><td>DD</td>

				</tr>
				<tr align='right'>
					<td></td><td>23/02/2006</td><td class='Currency'>$25.20</td><td>BPay</td>
				</tr>
				<tr align='right'>
					<td></td><td>06/12/2005</td><td class='Currency'>$822.20</td><td>Eftpos</td>

				</tr>
				</table>
			  </div></td></tr>";
		}
		
	}
	
	//------------------------------------------------------------------------//
	// RenderContextMenu
	//------------------------------------------------------------------------//
	/**
	 * RenderContextMenu()
	 *
	 * Renders a single column of the page
	 *
	 * Renders a single column of the page
	 * 
	 * @method
	 */
	function RenderContextMenu()
	{
	echo "
	<div id='Content'>
      <table border='0' cellpadding='0' cellspacing='0' width='100%'>
        <tbody><tr>
          <td nowrap='nowrap' valign='top' width='75'>
            <div id='Navigation' class='Left sectionContent Navigation'>
              <table border='0' cellpadding='0' cellspacing='0'>
                <tbody><tr>
                  <td>
                    <a href='http://localhost/sean/vixen/intranet_app/console.php'>
                      <img src='img/template/home.png' title='Employee Console' class='MenuIcon'>
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>
                    <a href='http://localhost/sean/vixen/intranet_app/account_add.php'>
                      <img src='img/template/contact_add.png' title='Add Customer' class='MenuIcon'>
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>
                    <a href='http://localhost/sean/vixen/intranet_app/contact_verify.php'>
                      <img src='img/template/contact_retrieve.png' title='Find Customer' class='MenuIcon'>
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>
                    <a href='#' onclick='return ModalDisplay (\"#modalContent-recentCustomers\")'>
                      <img src='img/template/history.png' title='Recent Customers' class='MenuIcon'>
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>
                    <a href='http://localhost/sean/vixen/intranet_app/rates_plan_list.php'>
                      <img src='img/template/plans.png' title='View Available Plans' class='MenuIcon'>
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>
                    <a href='#' onclick='toggleBox(\"LoginBox\");'>
                      <img src='img/template/admin_console.png' title='Test Button' class='MenuIcon'>
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>
                    <a href='logout.php'>
                      <img src='img/template/logout.png' title='Logout' class='MenuIcon'>
                    </a>
                  </td>
                </tr>
              </tbody></table>
            </div>
          </td>
		  <td valign='top'>";
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
	public	$arrProperties	= Array();
	private	$_objMenuToken	= NULL;
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
	// _Render
	//------------------------------------------------------------------------//
	/**
	 * _Render()
	 *
	 * Used recursively by the method Render() to prepare the Context Menu for rendering
	 *
	 * Used recursively by the method Render() to prepare the Context Menu for rendering
	 * 
	 * @return	array	
	 * @method
	 */
	function _Render($arrMenu)
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
				$arrReturn[$strMenu] = call_user_func_array(array($this->_objMenuItems, $strMethod), $arrSubMenu);
			}
			else
			{
				$arrReturn[$strMenu] = $this->_Render($arrSubMenu);
			}
		}
		
		return $arrReturn;
	}
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Renders the Context Menu
	 *
	 * Renders the Context Menu
	 * 
	 * @return	void
	 * @method
	 */
	function Render()
	{
		$this->_objMenuItems = new MenuItems();
		
		$arrOutput = $this->_Render($this->arrProperties);
		
		// convert $arrOutput to JSON
		//TODO!
		
		// output JSON
		//TODO!
		
		Debug($arrOutput);
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
		
		return $this->_Render($this->arrProperties);
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
	 * @param	array		$arrMenu				the multi-dimensional menu structure to process
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
// MenuItems
//----------------------------------------------------------------------------//
/**
 * MenuItems
 *
 * Defines the resultant HREF for each paricular item that can be included in a menu
 *
 * Defines the resultant HREF for each paricular item that can be included in a menu.
 * Each type of menu item (a command in the context menu) should have a method
 * defined here which returns the HREF that should be used when the menu item is 
 * clicked.  Alternatively the menu item can be handled by the __call function.
 * You will notice that the menu item "ViewAccount" has been handled both ways as
 * an example of how they work.
 *
 * @prefix	mit
 *
 * @package	ui_app
 * @class	MenuItems
 */
class MenuItems
{
	
	//------------------------------------------------------------------------//
	// ViewAccount
	//------------------------------------------------------------------------//
	/**
	 * ViewAccount()
	 *
	 * Compiles the HREF to be executed when the ViewAccount menu item is clicked
	 *
	 * Compiles the HREF to be executed when the ViewAccount menu item is clicked
	 * 
	 * @param	int		$intId		the id of the account to view
	 *
	 * @return	string				the HREF to be executed when the ViewAccount menu item is clicked
	 *
	 * @method
	 */
	function ViewAccount($intId)
	{
		return "Account_view.php?Account.Id=$intId";
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
	 * @param	string		$strName		The name of the menu item
	 * @param	array		$arrParams		any parameters defined for the menu item
	 *
	 * @return	string						the HREF to be executed when menu item is clicked
	 *
	 * @method
	 */
	function __call($strName, $arrParams)
	{
		switch ($strName)
		{
			case "ViewAccount":
				return "Account_view.php?Account.Id={$arrParams[0]}";
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
	private $_arrCrumbs = NULL;

	//------------------------------------------------------------------------//
	// AddCrumb
	//------------------------------------------------------------------------//
	/**
	 * AddCrumb()
	 *
	 * Appends a bread crumb to the end of the bread crumb menu
	 *
	 * Appends a bread crumb to the end of the bread crumb menu
	 * 
	 * @param	string		$strLabel		The label to be displayed in the bread crumb menu (include placeholders for variables)
	 *										ie $strLabel = "Acc:<id>"
	 * @param	string		$strHREF		The HREF to execute when the crumb is clicked (include placeholders for variables)
	 *										ie $strHREF = "view_account.php?Account.Id=<id>"
	 * @param	array		$arrAttributes	An associated array storing all variables used in $strLabel and $strHREF
	 *
	 * @return	void
	 *
	 * @method
	 */
	function AddCrumb($strLabel, $strHREF, $arrAttributes=NULL)
	{
		$this->_arrCrumbs[] = new BreadCrumb($strLabel, $strHREF, $arrAttributes);
	}

	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Renders the Bread Crumb Menu
	 *
	 * Renders the Bread Crumb Menu
	 * 
	 * @return	void
	 *
	 * @method
	 */
	function Render()
	{
		foreach ($this->_arrCrumbs as $objCrumb)
		{
			$arrCrumb['Label'] = $objCrumb->_strLabel;
			$arrCrumb['HREF'] = $objCrumb->_strHREF;
			
			// stick the values of the attributes into the HREF and label
			if (is_array($objCrumb->_arrAttributes))
			{
				foreach ($objCrumb->_arrAttributes as $strKey=>$mixValue)
				{
					$arrCrumb['Label'] = str_replace("<".strtolower($strKey).">", $mixValue, $arrCrumb['Label']);
					$arrCrumb['HREF'] = str_replace("<".strtolower($strKey).">", $mixValue, $arrCrumb['HREF']);
				}
			}
			$arrOutput[] = $arrCrumb;
		}
		// convert $arrOutput to JSON
		//TODO!
		
		// output JSON
		//TODO!
		
		Debug($arrOutput);
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
		return $this->_arrCrumbs;
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
		foreach ($this->_arrCrumbs as $objCrumb)
		{
			$arrCrumb['LabelFormat'] = $objCrumb->_strLabel;
			$arrCrumb['Label'] = $objCrumb->_strLabel;
			$arrCrumb['HREFFormat'] = $objCrumb->_strHREF;
			$arrCrumb['HREF'] = $objCrumb->_strHREF;
			
			// prepare the place holders in the format strings for displaying in html code
			$arrCrumb['LabelFormat'] = str_replace("<", "&lt;", $arrCrumb['LabelFormat']);
			$arrCrumb['LabelFormat'] = str_replace(">", "&gt;", $arrCrumb['LabelFormat']);
			$arrCrumb['HREFFormat'] = str_replace("<", "&lt;", $arrCrumb['HREFFormat']);
			$arrCrumb['HREFFormat'] = str_replace(">", "&gt;", $arrCrumb['HREFFormat']);
			
			// stick the values of the attributes into the HREF and label
			if (is_array($objCrumb->_arrAttributes))
			{
				foreach ($objCrumb->_arrAttributes as $strKey=>$mixValue)
				{
					$arrCrumb['Label'] = str_replace("<".strtolower($strKey).">", $mixValue, $arrCrumb['Label']);
					$arrCrumb['HREF'] = str_replace("<".strtolower($strKey).">", $mixValue, $arrCrumb['HREF']);
				}
			}
			$arrOutput[] = $arrCrumb;
		}
		
		//  Prepare the output string
		for ($i=0; $i<count($arrOutput); $i++)
		{
			$strOutput .= $strTabs . "BreadCrumb Menu Item $i:\n";
			$strOutput .= $strTabs . "\tLabel format:\t" . $arrOutput[$i]['LabelFormat'] . "\n";
			$strOutput .= $strTabs . "\tActual label:\t" . $arrOutput[$i]['Label'] . "\n";
			$strOutput .= $strTabs . "\tHREF format:\t" . $arrOutput[$i]['HREFFormat'] . "\n";
			$strOutput .= $strTabs . "\tActual HREF:\t" . $arrOutput[$i]['HREF'] . "\n";
		}
		
		if (!$strTabs)
		{
			Debug($strOutput);
		}
		return $strOutput;
	}
}

//----------------------------------------------------------------------------//
// BreadCrumb
//----------------------------------------------------------------------------//
/**
 * BreadCrumb
 *
 * Represents a single bread crumb in the bread crumb menu
 *
 * Represents a single bread crumb in the bread crumb menu
 *
 * @prefix	cmb
 *
 * @package	ui_app
 * @class	BreadCrumb
 */
class BreadCrumb
{
	public $_arrAttributes = NULL;
	public $_strLabel = "";	//defines how the crumb is displayed
	public $_strHREF = "";
	
	//----------------------------------------------------------------------------//
	// __construct
	//----------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * BreadCrumb constructor
	 *
	 * BreadCrumb constructor - sets BreadCrumb data attributes
	 *
	 * @return void
	 * @method
	 */
	function __construct($strLabel, $strHREF, $arrAttributes=NULL)
	{
		$this->_strLabel = $strLabel;
		$this->_strHREF = $strHREF;
		$this->_arrAttributes = $arrAttributes;
	}
	
}

?>
