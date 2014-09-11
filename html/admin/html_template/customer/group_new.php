<?php
class HtmlTemplateCustomerGroupNew extends HtmlTemplate {
	public $_intContext;

	function __construct($intContext, $strId) {
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
	}

	function Render() {
		$this->FormStart("NewCustomerGroup", "CustomerGroup", "Add");

		echo "<div class='GroupedContent'>\n";

		DBO()->CustomerGroup->internal_name->RenderInput(CONTEXT_DEFAULT, true);
		DBO()->CustomerGroup->external_name->RenderInput(CONTEXT_DEFAULT, true);
		DBO()->CustomerGroup->outbound_email->RenderInput(CONTEXT_DEFAULT, true);
		DBO()->CustomerGroup->flex_url->RenderInput(CONTEXT_DEFAULT, true);
		DBO()->CustomerGroup->email_domain->RenderInput(CONTEXT_DEFAULT, true);


		// Default Account Class
		$aAccountClassOptions = array();
		foreach (Account_Class::getForStatus(STATUS_ACTIVE) as $oAccountClass) {
			$aAccountClassOptions []= array(
				'Value' => $oAccountClass->id,
				'InputLabel' => $oAccountClass->name
			);
		}
		DBO()->CustomerGroup->default_account_class_id = null;
		DBO()->CustomerGroup->default_account_class_id->RenderInputAs(array(
			'Label' => 'Default Account Class',
			'InputType' => 'ComboBox',
			'Options' => $aAccountClassOptions,
			'DisableAutoValue' => true
		), CONTEXT_DEFAULT, true);

		echo "</div>"; // GroupedContent

		// Create the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "window.location = '". Href()->ViewAllCustomerGroups() ."'");
		$this->AjaxSubmit("Ok");
		echo "</div></div>\n";

		$this->FormEnd();
	}
}