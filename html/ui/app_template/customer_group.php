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
	 * @return	void
	 * @method	SaveDetails
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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
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
		$arrColumns = Array(	"Id"				=> "DT.Id",
								"Version"			=> "DT.Version",
								"Description"		=> "DT.Description",
								"EffectiveOn"		=> "DT.EffectiveOn",
								"CreatedOn"			=> "DT.CreatedOn",
								"LastModifiedOn"	=> "LastModifiedOn",
								"LastUsedOn"		=> "LastUsedOn",
								"SchemaVersion"		=> "DS.Version",
								"Overridden"		=> "CASE WHEN (	SELECT Count(DT2.Id)
																	FROM DocumentTemplate AS DT2
																	WHERE DT2.CustomerGroup = DT.CustomerGroup 
																	AND DT2.TemplateType = DT.TemplateType 
																	AND DT2.CreatedOn > DT.CreatedOn 
																	AND DT2.EffectiveOn <= DT.EffectiveOn
														) > 0 THEN 1 ELSE 0 END"
							);
		$strTable	= "DocumentTemplate AS DT INNER JOIN DocumentSchema AS DS ON DT.TemplateSchema = DS.Id";
		$strWhere	= "DT.CustomerGroup = <CustomerGroup> AND DT.TemplateType = <TemplateType>";
		$arrWhere	= Array("CustomerGroup"	=> DBO()->CustomerGroup->Id->Value,
							"TemplateType"	=> DBO()->DocumentTemplateType->Id->Value
							);
		DBL()->Templates->SetTable($strTable);
		DBL()->Templates->SetColumns($arrColumns);
		DBL()->Templates->Where->Set($strWhere, $arrWhere);
		DBL()->Templates->OrderBy("DT.Version DESC");
		DBL()->Templates->Load();
		
		if (DBL()->Templates->RecordCount() == 0)
		{
			// There aren't any templates for this CustomerGroup/TemplateType combination
			// Redirect the user to the AddNewTemplate page
			return $this->BuildNewTemplate();
		}
		
		// Build Context Menu
		//TODO! When we have stuff to put in it
		
		// Breadcrumb menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->SystemSettingsMenu();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->InternalName->Value);
		BreadCrumb()->SetCurrentPage("Template History");
		
		$this->LoadPage('document_template_history');
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// BuildNewTemplate
	//------------------------------------------------------------------------//
	/**
	 * BuildNewTemplate()
	 *
	 * Builds the "Edit Template page" webpage, but configures it for building a brand new Template, which could possibly be based on an existing one 
	 * 
	 * Builds the "Edit Template page" webpage, but configures it for building a brand new Template, which could possibly be based on an existing one
	 * It expects the following values to be defined:
	 * 	DBO()->CustomerGroup->Id			Id of the customer group
	 * 	DBO()->DocumentTemplateType->Id		Template type. Only required if the new template is not based on an existing one
	 * 	DBO()->BaseTemplate->Id				The Template to base the new one on.  Only required if the new template is based on an existing one
	 *
	 * @return		void
	 * @method		BuildNewTemplate
	 */
	function BuildNewTemplate()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		if (!DBO()->CustomerGroup->Load())
		{
			DBO()->Error->Message = "The CustomerGroup with id: ". DBO()->CustomerGroup->Id->Value ." could not be found";
			$this->LoadPage('error');
			return TRUE;
		}
		
		if (DBO()->BaseTemplate->Id->IsSet)
		{
			// The new template will be based on an existing one
			DBO()->BaseTemplate->SetTable("DocumentTemplate");
			if (!DBO()->BaseTemplate->Load())
			{
				// Could not load the template to base the new one on
				DBO()->Error->Message = "The DocumentTemplate with id: ". DBO()->BaseTemplate->Id->Value ." could not be found";
				$this->LoadPage('error');
				return TRUE;
			}
			
			if (DBO()->BaseTemplate->CustomerGroup->Value != DBO()->CustomerGroup->Id->Value)
			{
				// The base template does not belong to the CustomerGroup
				DBO()->Error->Message = "The DocumentTemplate of which to base the new template on, is not owned by the ". DBO()->CustomerGroup->InternalName->Value ." customer group";
				$this->LoadPage('error');
				return TRUE;
			}
			
			DBO()->DocumentTemplateType->Id = DBO()->BaseTemplate->TemplateType->Value;
		}
		
		if (!DBO()->DocumentTemplateType->Load())
		{
			DBO()->Error->Message = "The DocumentTemplateType with id: ". DBO()->DocumentTemplateType->Id->Value ." could not be found";
			$this->LoadPage('error');
			return TRUE;
		}
		
		// Load the most recent schema for this DocumentTemplateType
		$strWhere = "Id = (SELECT Max(Id) FROM DocumentSchema WHERE TemplateType = <TemplateType>)";
		$arrWhere = Array("TemplateType" => DBO()->DocumentTemplateType->Id->Value);
		DBO()->DocumentSchema->Where->Set($strWhere, $arrWhere);
		if (!DBO()->DocumentSchema->Load())
		{
			DBO()->Error->Message = "Could not find the document schema to use for this document template type (". DBO()->DocumentTemplateType->Id->Value .").  Please notify your system administrator";
			$this->LoadPage('error');
			return TRUE;
		}
		
		// If there is a draft template, then load it, and copy the contents of the BaseTemplate into it
		$arrDraft = $this->_GetDraftTemplate(DBO()->CustomerGroup->Id->Value, DBO()->DocumentTemplateType->Id->Value);
		DBO()->DocumentTemplate->CustomerGroup	= DBO()->CustomerGroup->Id->Value;
		DBO()->DocumentTemplate->TemplateType	= DBO()->DocumentTemplateType->Id->Value;
		DBO()->DocumentTemplate->Version		= $this->_GetNextVersionForTemplate(DBO()->CustomerGroup->Id->Value, DBO()->DocumentTemplateType->Id->Value);
		DBO()->DocumentTemplate->TemplateSchema	= DBO()->DocumentSchema->Id->Value;
		if (is_array($arrDraft))
		{
			// There is a draft
			DBO()->DocumentTemplate->Id				= $arrDraft['Id'];
			DBO()->DocumentTemplate->Source			= $arrDraft['Source'];
			DBO()->DocumentTemplate->CreatedOn		= $arrDraft['CreatedOn'];
			DBO()->DocumentTemplate->LastModifiedOn	= $arrDraft['LastModifiedOn'];
		}
		

		// Set a default description
		DBO()->DocumentTemplate->Description = "Version ". DBO()->DocumentTemplate->Version->Value ." for ". DBO()->CustomerGroup->InternalName->Value ." ". DBO()->DocumentTemplateType->Name->Value ." Template";		

		if (DBO()->BaseTemplate->Id->Value)
		{
			// Load the Template Source code from the BaseTemplate, into the new one
			DBO()->DocumentTemplate->Source	= DBO()->DocumentTemplate->Source->Value;
		}
		
		// Context Menu
		//TODO!
		
		//BreadCrumb Menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->SystemSettingsMenu();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->InternalName->Value);
		BreadCrumb()->ViewDocumentTemplateHistory(DBO()->CustomerGroup->Id->Value, DBO()->DocumentTemplateType->Id->Value);
		BreadCrumb()->SetCurrentPage("Template");
		
		DBO()->Render->Context = HTML_CONTEXT_NEW;
		
		$this->LoadPage('document_template');
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// SaveTemplate
	//------------------------------------------------------------------------//
	/**
	 * SaveTemplate()
	 *
	 * Handles the ajax request to save a template 
	 * 
	 * Handles the ajax request to save a template
	 * It expects the following values to be defined:
	 *
	 * @return		void
	 * @method		SaveTemplate
	 */
	function SaveTemplate()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		$strNow = GetCurrentDateAndTimeForMySQL();
		
		if (DBO()->Template->Id->Value != NULL)
		{
			// The user is saving changes made to an existing template
			$selCurrentTemplate = new StatementSelect("DocumentTemplate", "*", "Id = <Id>");
			if (!$selCurrentTemplate->Execute(Array("Id" => DBO()->Template->Id->Value)))
			{
				Ajax()->AddCommand("Alert", "ERROR: Could not retrieve the DocumentTemplate with id: ". DBO()->Template->Id->Value ." and Version: ". DBO()->Template->Version->Value .". Please notify your system administrator.");
				return TRUE;
			}
			$arrCurrentTemplate = $selCurrentTemplate->Fetch();
			
			// If the Template's EffectiveOn date is set and in the past then they cannot update it
			if ($arrCurrentTemplate['EffectiveOn'] != NULL && $arrCurrentTemplate['EffectiveOn'] <= $strNow)
			{
				Ajax()->AddCommand("Alert", "ERROR: This template has already come into effect, and can therefore not be modified");
				return TRUE;
			}
			 
			// Copy over values that should not have changed
			DBO()->Template->CreatedOn = $arrCurrentTemplate["CreatedOn"];
		}
		else
		{
			// The template is completely new
			// If there is a draft template, then copy over this one
			$arrDraft = $this->_GetDraftTemplate(DBO()->Template->CustomerGroup->Value, DBO()->Template->TemplateType->Id->Value);
			if (is_array($arrDraft))
			{
				DBO()->Template->Id = $arrDraft['Id'];
			}
			
			// Set the Version to the next available version
			DBO()->Template->Version = $this->_GetNextVersionForTemplate(DBO()->Template->CustomerGroup->Value, DBO()->Template->TemplateType->Value);
			
			DBO()->Template->CreatedOn = $strNow;
		}
		
		DBO()->Template->EffectiveOn	= NULL;
		DBO()->Template->LastModifiedOn = $strNow;
		DBO()->Template->LastUsedOn		= NULL; 
		
		// Save the record
		DBO()->Template->SetTable("DocumentTemplate");
		if (!DBO()->Template->Save())
		{
			// Saving the template failed
			Ajax()->AddCommand("Alert", "ERROR: Saving the template failed, unexpectedly.  Please notify your system administrator");
			return TRUE;
		}
		
		// The Template was successfully saved
		$arrReply["Template"]	= DBO()->Template->_arrProperties;
		$arrReply["Success"]	= TRUE;
		
		AjaxReply($arrReply);
		
		return TRUE;
	}
	

	// Returns the record (associative array) of the draft template or returns NULL if there is no draft template
	private function _GetDraftTemplate($intCustomerGroup, $intTemplateType)
	{
		$selTemplate	= new StatementSelect("DocumentTemplate", "*", "CustomerGroup = <CustomerGroup> AND TemplateType = <TemplateType> AND EffectiveOn IS NULL", "CreatedOn DESC", "1");
		$mixResult		= $selTemplate->Execute(Array("CustomerGroup" => $intCustomerGroup, "TemplateType" => $intTemplateType));
		
		if ($mixResult === FALSE)
		{
			return FALSE;
		}
		elseif ($mixResult == 1)
		{
			return $selTemplate->Fetch();
		}
		return NULL;
	}
	
	// Returns the next version number to use.
	// If there is a draft DocumentTemplate for this particular CustomerGroup/TemplateType, then it will
	// return the draft's assigned version
	private function _GetNextVersionForTemplate($intCustomerGroup, $intTemplateType)
	{
		$arrColumns	= Array("NextVersion" => "	CASE
													WHEN	(	SELECT MAX(Version)
																FROM DocumentTemplate
																WHERE CustomerGroup = $intCustomerGroup AND TemplateType = $intTemplateType
															) IS NULL THEN 1
													WHEN 	(	SELECT MAX(Version)
																FROM DocumentTemplate
																WHERE CustomerGroup = $intCustomerGroup AND TemplateType = $intTemplateType AND EffectiveOn IS NULL
															) IS NULL THEN MAX(Version) + 1
													ELSE MAX(Version) END");
		$strWhere	= "CustomerGroup = $intCustomerGroup AND TemplateType = $intTemplateType";
		$selVersion	= new StatementSelect("DocumentTemplate", $arrColumns, $strWhere);
		$selVersion->Execute();
		$arrRecord = $selVersion->Fetch();
		return $arrRecord['NextVersion'];
	}

    //----- DO NOT REMOVE -----//
	
}
?>