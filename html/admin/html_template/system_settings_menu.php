<?php
//----------------------------------------------------------------------------//
// HtmlTemplateSystemSettingsMenu
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateSystemSettingsMenu
 *
 * Displays the System Settings Menu
 *
 * Displays the System Settings Menu
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateSystemSettingsMenu
 * @extends	HtmlTemplate
 */
class HtmlTemplateSystemSettingsMenu extends HtmlTemplate
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
	 * @param	string	$_strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
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
	 * @method
	 */
	function Render()
	{
		// I'm going to try an use CSS properly for this menu
		
		// Render the link the the "Constants Management" page
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD))
		{
			$strImage = "img/template/constants_management_menu_item.png";
			$strItemName = "Manage Constants";
			$strItemDescription = "Manage various dynamic constants";
			$strHref = Href()->ViewAllConstants();
			$this->_RenderMenuItem($strImage, $strItemName, $strItemDescription, $strHref);
		}
		
		// Render the link the the "CustomerGroups Management" page
		$strImage = "img/template/customer_groups_menu_item.png";
		$strItemName = "Manage Customer Groups";
		$strItemDescription = "Edit and Add Customer Groups";
		$strHref = Href()->ViewAllCustomerGroups();
		$this->_RenderMenuItem($strImage, $strItemName, $strItemDescription, $strHref);

		// Render the link the the "Constants Management" page
		$strImage = "img/template/payment_terms.png";
		$strItemName = "Manage Payment Process";
		$strItemDescription = "Manage system payment process";
		$strHref = Href()->ManagePaymentTerms();
		$this->_RenderMenuItem($strImage, $strItemName, $strItemDescription, $strHref);
		
		// Render the link the the "Manage Customer Statuses" page
		$strImage = "img/template/contact.png";
		$strItemName = "Manage Customer Statuses";
		$strItemDescription = "Manage Customer Statuses";
		$strHref = Href()->ManageCustomerStatuses();
		$this->_RenderMenuItem($strImage, $strItemName, $strItemDescription, $strHref);
		
		
	}	
	
	private function _RenderMenuItem($strImage, $strItemName, $strItemDescription, $strHref)
	{
		echo "<div id='MenuItem'>";
		
		echo "<div class='MenuItemIcon'>";
		echo "<a href='$strHref'>";
		echo "<img src='$strImage'></img>";
		echo "</a>";
		echo "</div>";  // MenuItemIcon
		
		echo "<div class='MenuItemDetails'>";
		echo "<div class='MenuItemName'>$strItemName</div>";
		echo "<div class='MenuITemDescription'>$strItemDescription</div>";
		echo "</div>";  // MenuItemDetails
		
		echo "</div>";  // MenuItem
	}
}

?>
