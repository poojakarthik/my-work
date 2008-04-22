<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// customer_group
//----------------------------------------------------------------------------//
/**
 * customer_group
 *
 * contains all ApplicationTemplate extended classes relating to Customer Group functionality
 *
 * contains all ApplicationTemplate extended classes relating to Customer Group functionality
 *
 * @file		customer_group.php
 * @language	PHP
 * @package		framework
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateCustomerGroup
//----------------------------------------------------------------------------//
/**
 * AppTemplateCustomerGroup
 *
 * The AppTemplateCustomerGroup class
 *
 * The AppTemplateCustomerGroup class.  This incorporates all logic for all pages
 * relating to customer groups
 *
 *
 * @package	ui_app
 * @class	AppTemplateCustomerGroup
 * @extends	ApplicationTemplate
 */
class AppTemplateCustomerGroup extends ApplicationTemplate
{
	//------------------------------------------------------------------------//
	// ViewAll
	//------------------------------------------------------------------------//
	/**
	 * ViewAll()
	 *
	 * Displays all the CustomerGroups in a table, from which they can be managed
	 * 
	 * Displays all the CustomerGroups in a table, from which they can be managed
	 * It does assume anything to be passed to it via GET variables
	 *
	 * @return		void
	 * @method
	 *
	 */
	function ViewAll()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		// Breadcrumb menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->System_Settings_Menu();
		BreadCrumb()->SetCurrentPage("Customer Groups");
		
		// Retrieve the list of customer groups
		DBL()->CustomerGroup->OrderBy("InternalName");
		DBL()->CustomerGroup->Load();
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('customer_groups_list');

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Add
	//------------------------------------------------------------------------//
	/**
	 * Add()
	 *
	 * Handles the logic for adding a new customer group
	 * 
	 * Handles the logic for adding a new customer group
	 * It does not assume anything to be passed to it via GET variables, intially
	 *
	 * @return		void
	 * @method
	 */
	function Add()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		// Check if the form was submitted
		if (SubmittedForm('NewCustomerGroup', 'Ok'))
		{
			if (DBO()->CustomerGroup->IsInvalid())
			{
				// At least one Field is invalid
				Ajax()->AddCommand("Alert", "ERROR: Invalid fields are highlighted");
				Ajax()->RenderHtmlTemplate("CustomerGroupNew", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
				return TRUE;
			}
			
			// Check that the CustomerGroup's InternalName is not being used by another CustomerGroup
			$selCustomerGroup = new StatementSelect("CustomerGroup", "Id", "InternalName LIKE <Name>", "", "1");
			$intRecordFound = $selCustomerGroup->Execute(Array("Name"=> DBO()->CustomerGroup->InternalName->Value));
			if ($intRecordFound)
			{
				// The CustomerGroup's InternalName is already in use by another CustomerGroup
				DBO()->CustomerGroup->InternalName->SetToInvalid();
				Ajax()->AddCommand("Alert", "ERROR: This name is already in use by another Customer Group");
				Ajax()->RenderHtmlTemplate("CustomerGroupNew", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
				return TRUE;
			}
			
			// The CustomerGroup is valid.  Save it
			if (!DBO()->CustomerGroup->Save())
			{
				// The CustomerGroup could not be saved for some unforseen reason
				Ajax()->AddCommand("Alert", "ERROR: Saving the CustomerGroup failed, unexpectedly");
				return TRUE;
			}
			
			// The CustomerGroup has now been saved
			Ajax()->AddCommand("AlertAndRelocate", Array("Alert"=>"The new Customer Group has been successfully created", "Location"=>Href()->ViewAllCustomerGroups()));
			return TRUE;
		}
		
		// Breadcrumb menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->System_Settings_Menu();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->SetCurrentPage("New Customer Group");
		
		// Declare which Page Template to use
		$this->LoadPage('customer_group_add');

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// View
	//------------------------------------------------------------------------//
	/**
	 * View()
	 *
	 * Handles the logic for View Customer Group functionality
	 * 
	 * Handles the logic for View Customer Group functionality
	 * Assumes DBO()->CustomerGroup->Id to be set
	 *
	 * @return		void
	 * @method
	 */
	function View()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		// Load the CustomerGroupDetails
		if (!DBO()->CustomerGroup->Load())
		{
			DBO()->Error->Message = "The Customer Group with account id: ". DBO()->CustomerGroup->Id->value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		$intCustomerGroupId = DBO()->CustomerGroup->Id->Value;
		// Load the currently used DocumentTemplates
		$arrColumns = Array("TypeId"		=> "DTT.Id",
							"TypeName"		=> "DTT.Name",
							"TemplateId"	=> "DT.Id",
							"Version"		=> "DT.Version",
							"EffectiveOn"	=> "DT.EffectiveOn");
							
		$strTables	= "DocumentTemplateType AS DTT LEFT JOIN DocumentTemplate AS DT 
						ON DTT.Id = DT.TemplateType AND DT.CustomerGroup = $intCustomerGroupId AND DT.EffectiveOn <= NOW()
						AND DT.CreatedOn =	(	SELECT Max(CreatedOn)
												FROM DocumentTemplate AS DT2
												WHERE DT2.CustomerGroup = DT.CustomerGroup AND DT2.TemplateType = DTT.Id AND DT2.EffectiveOn <= NOW()
											)";
		DBL()->DocumentTemplate->SetTable($strTables);
		DBL()->DocumentTemplate->SetColumns($arrColumns);
		DBL()->DocumentTemplate->OrderBy("TypeId ASC");
		DBL()->DocumentTemplate->Load();
		
		// Breadcrumb menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->System_Settings_Menu();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->SetCurrentPage("Customer Group");
		
		// Declare which Page Template to use
		$this->LoadPage('customer_group_view');

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// RenderHtmlTemplateCustomerGroupDetails
	//------------------------------------------------------------------------//
	/**
	 * RenderHtmlTemplateCustomerGroupDetails()
	 *
	 * Renders the CustomerGroupDetails Html Template in the specified context (View or Edit)
	 * 
	 * Renders the CustomerGroupDetails Html Template in the specified context (View or Edit)
	 * It expects	DBO()->CustomerGroup->Id 	CustomerGroup Id 
	 *				DBO()->Container->Id		id of the container div in which to place the Rendered HtmlTemplate
	 *				DBO()->Context->View		true if rending in viewing context (defaults to this)
	 *				DBO()->Context->Edit		true if rending in edit context (View takes precedence)
	 *
	 * @return		void
	 * @method
	 *
	 */
	function RenderHtmlTemplateCustomerGroupDetails()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);

		// Load the CustomerGroup
		DBO()->CustomerGroup->Load();
		
		// Work out which context to render the HtmlTemplate in
		$intContext = HTML_CONTEXT_VIEW;
		if (DBO()->Context->View->Value)
		{
			$intContext = HTML_CONTEXT_VIEW;
		}
		elseif (DBO()->Context->Edit->Value)
		{
			$intContext = HTML_CONTEXT_EDIT;
		}
		
		// Render the CustomerGroupDetails HtmlTemplate for Viewing
		Ajax()->RenderHtmlTemplate("CustomerGroupDetails", $intContext, DBO()->Container->Id->Value);

		return TRUE;
	}

	//------------------------------------------------------------------------//
	// SaveDetails
	//------------------------------------------------------------------------//
	/**
	 * SaveDetails()
	 *
	 * Handles the logic of validating and saving the details of a CustomerGroup
	 * 
	 * Handles the logic of validating and saving the details of a CustomerGroup
	 * This works with the HtmlTemplateCustomerGroupDetails object, when rendered in Edit mode (HTML_CONTEXT_EDIT)
	 * It fires the OnCustomerGroupDetailsUpdate Event if the CustomerGroup is successfully modified
	 *
	 * @return		void
	 * @method
	 *
	 */
	function SaveDetails()
	{
		// Check permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);

		// Validate the CustomerGroup
		if (DBO()->CustomerGroup->IsInvalid())
		{
			// At least one Field is invalid
			Ajax()->AddCommand("Alert", "ERROR: Invalid fields are highlighted");
			Ajax()->RenderHtmlTemplate("CustomerGroupDetails", HTML_CONTEXT_EDIT, $this->_objAjax->strContainerDivId, $this->_objAjax);
			return TRUE;
		}
		
		// Check that the CustomerGroup's InternalName is not being used by another CustomerGroup
		$selCustomerGroup	= new StatementSelect("CustomerGroup", "Id", "InternalName LIKE <Name> AND Id != <Id>", "", "1");
		$intRecordFound		= $selCustomerGroup->Execute(Array(	"Name"=> DBO()->CustomerGroup->InternalName->Value, 
																"Id"=> DBO()->CustomerGroup->Id->Value));
		if ($intRecordFound)
		{
			// The CustomerGroup's new InternalName is already in use by another CustomerGroup
			DBO()->CustomerGroup->InternalName->SetToInvalid();
			Ajax()->AddCommand("Alert", "ERROR: This name is already in use by another Customer Group");
			Ajax()->RenderHtmlTemplate("CustomerGroupDetails", HTML_CONTEXT_EDIT, $this->_objAjax->strContainerDivId, $this->_objAjax);
			return TRUE;
		}
		
		// The CustomerGroup is valid.  Save it
		if (!DBO()->CustomerGroup->Save())
		{
			// The CustomerGroup could not be saved for some unforseen reason
			Ajax()->AddCommand("Alert", "ERROR: Saving changes to the CustomerGroup failed, unexpectedly");
			return TRUE;
		}
		
		// Fire the OnCustomerGroupDetailsUpdate Event
		$arrEvent['CustomerGroup']['Id'] = DBO()->CustomerGroup->Id->Value;
		Ajax()->FireEvent(EVENT_ON_CUSTOMER_GROUP_DETAILS_UPDATE, $arrEvent);
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// ViewDocumentTemplateHistory
	//------------------------------------------------------------------------//
	/**
	 * ViewDocumentTemplateHistory()
	 *
	 * Builds the "View Document Template History" webpage 
	 * 
	 * Builds the "View Document Template History" webpage
	 *
	 * @return		void
	 * @method		ViewDocumentTemplateHistory
	 */
	function ViewDocumentTemplateHistory()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);
		
		if (!DBO()->CustomerGroup->Load())
		{
			DBO()->Error->Message = "The CustomerGroup with id: ". DBO()->CustomerGroup->Id->Value ." could not be found";
			$this->LoadPage('error');
			return TRUE;
		}
		
		if (!DBO()->DocumentTemplateType->Load())
		{
			DBO()->Error->Message = "The DocumentTemplateType with id: ". DBO()->DocumentTemplateType->Id->Value ." could not be found";
			$this->LoadPage('error');
			return TRUE;
		}
		
		// Retrieve the Template history
		//TODO!
		
		
		// Build Context Menu
		//TODO! When we have stuff to put in it
		
		// Breadcrumb menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->SystemSettingsMenu();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value);
		BreadCrumb()->SetCurrentPage("Template History");
		
		$this->LoadPage('document_template_history');
		return TRUE;
	}


    //----- DO NOT REMOVE -----//
	
}
?>