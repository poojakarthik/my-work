<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// group_change_advertisement.php
//----------------------------------------------------------------------------//
/**
 * group_change_advertisement
 *
 * HTML Template for the CustomerGroup Change Advertisement object
 *
 * HTML Template for the CustomerGroup Change Advertisement object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to add a Advertisement.
 *
 * @file		group_change_advertisement.php
 * @language	PHP
 * @package		ui_app
 * @author		Ryan
 * @version		7.12
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateCustomerGroupChangeAdvertisement
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateCustomerGroupChangeAdvertisement
 *
 * A specific HTML Template object
 *
 * A specific HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateCustomerGroupChangeAdvertisement
 * @extends	HtmlTemplate
 */
class HtmlTemplateCustomerGroupChangeAdvertisement extends HtmlTemplate
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
		if(DBO()->ChangeAdvertisement->Error->Value)
		{

			echo "<h2 class='CustomerGroup'>There was an error with the upload.</h2>\n";
			echo "<div class='GroupedContent'>\n";
			echo "
			<TABLE>
			<TR>
				<TD width=\"200\">Error Message: </TD>
				<TD>" . DBO()->ChangeAdvertisement->Error->Value . "</TD>
			</TR>
			<TR>
				<TD></TD>
				<TD></TD>
			</TR>
			</TABLE>
			</div>";
		}
		else if(!DBO()->ChangeAdvertisement->Error->Value && array_key_exists('CustomerGroup_Id', $_POST))
		{
			$mixRedirectLink = Href()->ViewCustomerGroup($_POST['CustomerGroup_Id']);
			if($_POST['CustomerGroup_Id'] == "")
			{
				$mixRedirectLink = "./flex.php/CustomerGroup/ViewAll/";
			}
			$strFileName = $_FILES['userfile']['name'];
			$strFileType = $_FILES['userfile']['type'];

			echo "<h2 class='CustomerGroup'>The advertisement has now been updated.</h2>\n";
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
			</TABLE>
			</div><br/><br/>
			<A HREF=$mixRedirectLink>Continue Editing Customer Group</A>";
		
		
		}

	}
	
}

?>
