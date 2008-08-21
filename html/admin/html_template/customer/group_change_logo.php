<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_import.php
//----------------------------------------------------------------------------//
/**
 * rate_group_import
 *
 * HTML Template for the Import Rate Group HTML object
 *
 * HTML Template for the Import Rate Group HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to add a rate group.
 *
 * @file		rate_group_import.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.12
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateRateGroupImport
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateRateGroupImport
 *
 * A specific HTML Template object
 *
 * A specific HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateRateGroupImport
 * @extends	HtmlTemplate
 */
class HtmlTemplateCustomerGroupChangeLogo extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContext
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	public $_intContext;

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * Constructor - java script required by the HTML object is loaded here
	 *
	 * @param	int		$intContext		context in which the html object will be rendered
	 *
	 * @method
	 */
	function __construct($intContext)
	{
		$this->_intContext = $intContext;
	}
	
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 * 
	 *
	 * @method
	 */
	function Render()
	{
		if(array_key_exists('CustomerGroup_Id', $_POST))
		{

			$strFileName = $_FILES['userfile']['name'];
			$strFileType = $_FILES['userfile']['type'];
			echo "<h2 class='CustomerGroup'>The logo has now been updated.</h2>\n";
			echo "<div class='GroupedContent'>\n";
			echo "
			<TABLE>
			<TR>
				<TD width=\"200\">File Name: </TD>
				<TD>$strFileName</TD>
			</TR>
			<TR>
				<TD>File Type: </TD>
				<TD>$strFileType</TD>
			</TR>
			<TR>
				<TD></TD>
				<TD></TD>
			</TR>
			</TABLE>";
			echo "</div><br/><br/>";
			echo "<META HTTP-EQUIV=Refresh CONTENT=\"5; URL=/flex/trunk/html/" . Href()->ViewCustomerGroup($_POST['CustomerGroup_Id']) . "\">";
		}

	}
	
}

?>
