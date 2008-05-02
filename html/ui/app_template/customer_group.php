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
		$arrColumns = Array(	"Id"			=> "DT.Id",
								"Version"		=> "DT.Version",
								"Description"	=> "DT.Description",
								"EffectiveOn"	=> "DT.EffectiveOn",
								"CreatedOn"		=> "DT.CreatedOn",
								"ModifiedOn"	=> "ModifiedOn",
								"LastUsedOn"	=> "LastUsedOn",
								"SchemaVersion"	=> "DS.Version",
								"Overridden"	=> "CASE WHEN (	SELECT Count(DT2.Id)
																FROM DocumentTemplate AS DT2
																WHERE DT2.CustomerGroup = DT.CustomerGroup 
																AND DT2.TemplateType = DT.TemplateType 
																AND DT2.CreatedOn > DT.CreatedOn 
																AND DT2.EffectiveOn <= DT.EffectiveOn
													) > 0 THEN 1 ELSE 0 END"
							);
		$strTable	= "DocumentTemplate AS DT INNER JOIN DocumentTemplateSchema AS DS ON DT.TemplateSchema = DS.Id";
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
		$arrSchema = $this->_GetCurrentSchema(DBO()->DocumentTemplateType->Id->Value);
		DBO()->DocumentTemplateSchema->_arrProperties = $arrSchema;
		
		// If there is a draft template, then load it, and copy the contents of the BaseTemplate into it
		$arrDraft = $this->_GetDraftTemplate(DBO()->CustomerGroup->Id->Value, DBO()->DocumentTemplateType->Id->Value);
		DBO()->DocumentTemplate->CustomerGroup	= DBO()->CustomerGroup->Id->Value;
		DBO()->DocumentTemplate->TemplateType	= DBO()->DocumentTemplateType->Id->Value;
		DBO()->DocumentTemplate->Version		= $this->_GetNextVersionForTemplate(DBO()->CustomerGroup->Id->Value, DBO()->DocumentTemplateType->Id->Value);
		DBO()->DocumentTemplate->TemplateSchema	= DBO()->DocumentTemplateSchema->Id->Value;
		DBO()->DocumentTemplate->EffectiveOn	= NULL;
		if (is_array($arrDraft))
		{
			// There is a draft
			DBO()->DocumentTemplate->Id			= $arrDraft['Id'];
			DBO()->DocumentTemplate->Source		= $arrDraft['Source'];
			DBO()->DocumentTemplate->CreatedOn	= $arrDraft['CreatedOn'];
			DBO()->DocumentTemplate->ModifiedOn	= $arrDraft['ModifiedOn'];
		}
		

		// Set a default description
		DBO()->DocumentTemplate->Description = "Version ". DBO()->DocumentTemplate->Version->Value ." for ". DBO()->CustomerGroup->InternalName->Value ." ". DBO()->DocumentTemplateType->Name->Value ." Template";		

		if (DBO()->BaseTemplate->Id->Value)
		{
			// Load the Template Source code from the BaseTemplate, into the new one
			DBO()->DocumentTemplate->Source	= DBO()->BaseTemplate->Source->Value;
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
	// EditTemplate
	//------------------------------------------------------------------------//
	/**
	 * EditTemplate()
	 *
	 * Builds the "Edit Template page" webpage 
	 * 
	 * Builds the "Edit Template page" webpage
	 * It expects the following values to be defined:
	 * 	DBO()->DocumentTemplate->Id			The Template to edit
	 *
	 * @return	void
	 * @method	EditTemplate
	 */
	function EditTemplate()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		if (!$this->_LoadTemplate(DBO()->DocumentTemplate->Id->Value, TRUE))
		{
			// The template could not be loaded
			DBO()->Error->Message = "The DocumentTemplate with id: ". DBO()->DocumentTemplate->Id->Value ." could not be found";
			$this->LoadPage('error');
			return TRUE;
		}
		
		// Context Menu
		//TODO!
		
		//BreadCrumb Menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->SystemSettingsMenu();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->InternalName->Value);
		BreadCrumb()->ViewDocumentTemplateHistory(DBO()->CustomerGroup->Id->Value, DBO()->DocumentTemplate->TemplateType->Value);
		BreadCrumb()->SetCurrentPage("Template");
		
		DBO()->Render->Context = HTML_CONTEXT_EDIT;
		
		$this->LoadPage('document_template');
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// ViewTemplate
	//------------------------------------------------------------------------//
	/**
	 * ViewTemplate()
	 *
	 * Builds the "View Template page" webpage 
	 * 
	 * Builds the "View Template page" webpage
	 * It expects the following values to be defined:
	 * 	DBO()->DocumentTemplate->Id			The Template to view
	 *
	 * @return	void
	 * @method	ViewTemplate
	 */
	function ViewTemplate()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		if (!$this->_LoadTemplate(DBO()->DocumentTemplate->Id->Value, FALSE))
		{
			// The template could not be loaded
			DBO()->Error->Message = "The DocumentTemplate with id: ". DBO()->DocumentTemplate->Id->Value ." could not be found";
			$this->LoadPage('error');
			return TRUE;
		}
		
		// Context Menu
		//TODO!
		
		//BreadCrumb Menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->SystemSettingsMenu();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->InternalName->Value);
		BreadCrumb()->ViewDocumentTemplateHistory(DBO()->CustomerGroup->Id->Value, DBO()->DocumentTemplate->TemplateType->Value);
		BreadCrumb()->SetCurrentPage("Template");
		
		DBO()->Render->Context = HTML_CONTEXT_VIEW;
		
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
			$arrDraft = $this->_GetDraftTemplate(DBO()->Template->CustomerGroup->Value, DBO()->Template->TemplateType->Value);
			if (is_array($arrDraft))
			{
				DBO()->Template->Id = $arrDraft['Id'];
			}
			
			// Set the Version to the next available version
			DBO()->Template->Version = $this->_GetNextVersionForTemplate(DBO()->Template->CustomerGroup->Value, DBO()->Template->TemplateType->Value);
			
			DBO()->Template->CreatedOn = $strNow;
		}
		
		// Build a default description if one has not been supplied
		if (!Validate("IsNotEmptyString", DBO()->Template->Description->Value))
		{
			DBO()->CustomerGroup->Id = DBO()->Template->CustomerGroup->Value;
			DBO()->CustomerGroup->Load();
			DBO()->DocumentTemplateType->Id = DBO()->Template->TemplateType->Value;
			DBO()->DocumentTemplateType->Load();
			DBO()->Template->Description = "Version ". DBO()->Template->Version->Value ." for ". DBO()->CustomerGroup->InternalName->Value ." ". DBO()->DocumentTemplateType->Name->Value;
		}
		
		switch (DBO()->Template->EffectiveOnType->Value)
		{
			case "immediately":
				DBO()->Template->EffectiveOn = $strNow;
				break;
			
			case "date":
				// Validate the date and check that it is in the future
				if (!Validate("ShortDate", DBO()->Template->EffectiveOn->Value))
				{
					// The EffectiveOn date is invalid
					Ajax()->AddCommand("Alert", "ERROR: Invalid 'Effective On' date.<br />It must be in the format dd/mm/yyyy and in the future");
					return TRUE;
				}
				
				// Convert the date into the YYYY-MM-DD format
				DBO()->Template->EffectiveOn = ConvertUserDateToMySqlDate(DBO()->Template->EffectiveOn->Value);
				
				// Check that the Date is in the future
				if ($strNow > DBO()->Template->EffectiveOn->Value)
				{
					// The EffectiveOn date is in the past (or today, which is considered in the past)
					Ajax()->AddCommand("Alert", "ERROR: Invalid 'Effective On' date.  It must be in the future");
					return TRUE;
				}
				break;
				
			case "undeclared":
			default:
				// Check that there isn't a current value for EffectiveOn
				if (isset($arrCurrentTemplate) && $arrCurrentTemplate['EffectiveOn'] != NULL)
				{
					Ajax()->AddCommand("Alert", "ERROR: 'Effective On' date has already been set and can not be set back to 'undeclared'");
					return TRUE;
				}
				
				DBO()->Template->EffectiveOn = NULL;
		}
		
		DBO()->Template->ModifiedOn = $strNow;
		
		// Save the record
		DBO()->Template->SetTable("DocumentTemplate");
		if (!DBO()->Template->Save())
		{
			// Saving the template failed
			Ajax()->AddCommand("Alert", "ERROR: Saving the template failed, unexpectedly.  Please notify your system administrator");
			return TRUE;
		}
		
		// The Template was successfully saved
		
		// If the EffectiveOn Date is set.  Check if the Template will be totally overridden by a newer one
		if (DBO()->Template->EffectiveOn->Value != NULL) 
		{
			$strWhere = "CustomerGroup = <CustomerGroup> AND TemplateType = <TemplateType> AND Id != <TemplateId> AND CreatedOn > <CreatedOn> AND EffectiveOn IS NOT NULL AND EffectiveOn <= <EffectiveOn>";
			$arrWhere = Array(	"CustomerGroup"	=> DBO()->Template->CustomerGroup->Value,
								"TemplateType"	=> DBO()->Template->TemplateType->Value,
								"TemplateId"	=> DBO()->Template->Id->Value,
								"CreatedOn"		=> DBO()->Template->CreatedOn->Value,
								"EffectiveOn"	=> DBO()->Template->EffectiveOn->Value);
			$selOverridingTemplates = new StatementSelect("DocumentTemplate", "Id", $strWhere);
			$intRecCount = $selOverridingTemplates->Execute($arrWhere);
			if ($intRecCount > 0)
			{
				// The template is completely overridden and will never be used unless the EffectiveOn date is changed
				$strHref = Href()->ViewDocumentTemplateHistory(DBO()->Template->CustomerGroup->Value, DBO()->Template->TemplateType->Value);
				$arrData = Array(	"Alert"		=> "The Template has been successfully saved however, with its current EffectiveOn date, it will never be used as it is currently overridden by another template which was created more recently, and has an ealier EffectiveOn date",
									"Location"	=> $strHref);
				Ajax()->AddCommand("AlertAndRelocate", $arrData);
				return TRUE;
			}
		}
		if (DBO()->Template->EffectiveOn->Value == $strNow)
		{
			// The template can no longer be editted.  Relocate the user to the history page
			$strHref = Href()->ViewDocumentTemplateHistory(DBO()->Template->CustomerGroup->Value, DBO()->Template->TemplateType->Value);
			$arrData = Array(	"Alert"		=> "The Template has been successfully saved and comes into effect immediately",
								"Location"	=> $strHref);
			Ajax()->AddCommand("AlertAndRelocate", $arrData);
			return TRUE;
		}
		
		$arrReply["Template"]	= DBO()->Template->_arrProperties;
		$arrReply["Success"]	= TRUE;
		
		AjaxReply($arrReply);
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// ViewDocumentResources
	//------------------------------------------------------------------------//
	/**
	 * ViewDocumentResources()
	 *
	 * Displays the page which allows the user to view DocumentResources and upload new ones
	 * 
	 * Displays the page which allows the user to view DocumentResources and upload new ones
	 * It assumes the following data objects have been set:
	 * 	DBO()->CustomerGroup->Id		Id of the customer group to view the DocumentResources of
	 *
	 * @return		void
	 * @method	ViewDocumentResources
	 */
	function ViewDocumentResources()
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
		
		// Define all the objects required to retrieve the DocumentResourceType information from the database
		$selResourceType = new StatementSelect("DocumentResourceType", "*", "", "PlaceHolder");
		if ($selResourceType->Execute() === FALSE)
		{
			DBO()->Error->Message = "Could not load all data required to describe the Document Resource Types.  Please notify your system administrator";
			$this->LoadPage('error');
			return TRUE;
		}
		
		// This array has to be wrapped in the DBO() so that they are accessable within the HtmlTemplates
		DBO()->DocumentResourceTypes->AsArray = $selResourceType->FetchAll();
		
		// Breadcrumb menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->SystemSettingsMenu();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->InternalName->Value);
		BreadCrumb()->SetCurrentPage("Document Resources");

		// Load the page template
		$this->LoadPage('document_resource_management');
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// GetDocumentResourceHistory
	//------------------------------------------------------------------------//
	/**
	 * GetDocumentResourceHistory()
	 *
	 * Draws a table representing the history of resources associated with a DocumentResourceType and Customer Group
	 * 
	 * Draws a table representing the history of resources associated with a DocumentResourceType and Customer Group
	 * It assumes the following data objects have been set:
	 * 	DBO()->History->ResourceType		Id of the DocumentResourceType, to view the history of resources, of.
	 *  DBO()->History->CustomerGroup		Id of the CustomerGroup, to view the history of resources, of.
	 *
	 * @return		void
	 * @method	ViewDocumentResourceHistory
	 */
	function GetDocumentResourceHistory()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);
		
		$intResourceType	= DBO()->History->ResourceType->Value;
		$intCustomerGroup	= DBO()->History->CustomerGroup->Value;
		
		DBO()->DocumentResourceType->Id = $intResourceType;
		DBO()->DocumentResourceType->Load();
		
		DBO()->CustomerGroup->Id = $intCustomerGroup;
		DBO()->CustomerGroup->Load();
		
		// Retrieve the DocumentResources
		$selResources = new StatementSelect("DocumentResource", "*", "CustomerGroup = <CustomerGroup> AND Type = <ResourceType>", "CreatedOn DESC, StartDatetime DESC");
		if ($selResources->Execute(Array("CustomerGroup" => $intCustomerGroup, "ResourceType" => $intResourceType)) === FALSE)
		{
			Ajax()->AddCommand("Alert", "ERROR: Retrieving the Document Resource History failed, unexpectedly.  Please notify your system administrator");
			return TRUE;
		}
		$arrHistory = $selResources->FetchAll();

		$objHistoryTableGenerator = new HtmlTemplateDocumentResourceManagement(NULL, NULL);
		//AjaxReply($objHistoryTableGenerator->GetHistory(DBO()->DocumentResourceType->PlaceHolder->Value, $arrHistory));
		echo $objHistoryTableGenerator->GetHistory($arrHistory);
		return TRUE;
	}
	
	// Displays the popup for adding a new resource
	function AddDocumentResource()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);
		
		if (!DBO()->CustomerGroup->Load())
		{
			Ajax()->AddCommand("Alert", "ERROR: Could not load the customer group.  Please notify your system administrator");
			return TRUE;
		}
		
		if (!DBO()->DocumentResourceType->Load())
		{
			Ajax()->AddCommand("Alert", "ERROR: Could not load the DocumentResourceType.  Please notify your system administrator");
			return TRUE;
		}
		
		if (!AuthenticatedUser()->UserHasPerm(DBO()->DocumentResourceType->PermissionRequired->Value))
		{
			Ajax()->AddCommand("Alert", "ERROR: The user does not have permission to upload resources of this type");
			return TRUE;
		} 

		// Load the page template
		$this->LoadPage('document_resource_add');
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// UploadResource
	//------------------------------------------------------------------------//
	/**
	 * UploadResource()
	 *
	 * Handles the uploading of a file to be used as a DocumentResource
	 * 
	 * Handles the uploading of a file to be used as a DocumentResource
	 * The HTML generated by this function will be displayed in an iframe which 
	 * will be embedded in the "View Document Resources" webpage
	 * When initialy called, it expects the following objects to be defined 
	 *		(I don't think it needs anything defined)
	 *
	 * When the form is submitted, it expects the following values to be defined
	 *		$_FILES['Resource']			References the Resource file uploaded
	 *		TODO! define the other Posted values
	 *
	 * @return		void
	 * @method	ViewDocumentResourceHistory
	 */
	function UploadResource()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);
		
		// Retrieve the FileTypes that this resource can be
		$intResourceType = DBO()->DocumentResource->Type->Value;
		
		$strWhere = "Id IN (SELECT FileType FROM DocumentResourceTypeFileType WHERE ResourceType = $intResourceType)";
		$selFileTypes = new StatementSelect("FileType", "*", $strWhere);
		$selFileTypes->Execute();
		$arrFileTypes = $selFileTypes->FetchAll();
		DBO()->FileTypes->AsArray = $arrFileTypes;
		
		// Check if the form has been submitted
		if (SubmittedForm("ImportResource"))
		{
			$intCustomerGroup	= DBO()->DocumentResource->CustomerGroup->Value;
			$intResourceType	= DBO()->DocumentResource->Type->Value;
			$mixStart			= DBO()->DocumentResource->Start->Value;
			$mixEnd				= DBO()->DocumentResource->End->Value;
			
			$mixResult = $this->_UploadResource($arrFileTypes, $intResourceType, $intCustomerGroup, $mixStart, $mixEnd);

			if ($mixResult === TRUE)
			{
				// The file was successfully uploaded
				DBO()->Import->Success = TRUE;
			}
			else
			{
				// The import was unsuccessful
				DBO()->Import->Success	= FALSE;
				DBO()->Import->ErrorMsg	= $mixResult;
			}
		}
		
		// Load the page template
		$this->LoadPage('document_resource_upload_component');
		return TRUE;
	}

	// Returns TRUE on success, or an error msg on failure
	private function _UploadResource($arrFileTypes, $intResourceType, $intCustomerGroup, $mixStart, $mixEnd)
	{
		$strFilename		= $_FILES['ResourceFile']['name'];
		$strTempFilename	= $_FILES['ResourceFile']['tmp_name'];
		$intFileStatus		= $_FILES['ResourceFile']['error'];
		$strFileType		= $_FILES['ResourceFile']['type'];
		$intFileSize		= $_FILES['ResourceFile']['size'];
		
		$arrFilenameParts	= explode(".", $strFilename);
		$strExtension		= $arrFilenameParts[count($arrFilenameParts)-1];
		
		// Load the DocumentResourceType record
		$selResourceType = new StatementSelect("DocumentResourceType", "*", "Id = <Id>");
		if (!$selResourceType->Execute(Array("Id" => $intResourceType)))
		{
			return "ERROR: Could not find the DocumentResourceType with Id: $intResourceType";
		}
		$arrResourceType = $selResourceType->Fetch();
		
		// Load the CustomerGroup record
		$selCustomerGroup = new StatementSelect("CustomerGroup", "*", "Id = <Id>");
		if (!$selCustomerGroup->Execute(Array("Id" => $intCustomerGroup)))
		{
			return "ERROR: Could not find the CustomerGroup with Id: $intCustomerGroup";
		}
		$arrCustomerGroup = $selCustomerGroup->Fetch();
		
		// Check that the user has the required permissions to added resources of this type
		if (!AuthenticatedUser()->UserHasPerm($arrResourceType['PermissionRequired']))
		{
			return "ERROR: You do not have the required permissions to add '{$arrResourceType['PlaceHolder']}' resources";
		}

		// Check that the file was successfully uploaded
		if ($intFileStatus != UPLOAD_ERR_OK)
		{
			// The file was not uploaded properly
			$strErrorMsg = GetConstantDescription($intFileStatus, "HTTPUploadStatus");
			if ($strErrorMsg === FALSE)
			{
				// The error code is unknown
				$strErrorMsg = "The file failed to upload, for an undetermined reason";
			}
			$strErrorMsg = "ERROR: $strErrorMsg";
			return $strErrorMsg;
		}
		// Check that something was actually uploaded
		if ($intFileSize == 0)
		{
			return "ERROR: File is empty";
		}
		

		// Check that the file is of an appropriate type
		$bolFoundFileType = FALSE;
		foreach ($arrFileTypes as $arrFileType)
		{
			if ($strExtension == $arrFileType['Extension'])
			{
				// Check that their MIME Type matches
				if ($strFileType == $arrFileType['MIMEType'])
				{
					$intFileTypeId		= $arrFileType['Id']; 
					$bolFoundFileType	= TRUE;
					break;
				}
			}
		}
		
		if (!$bolFoundFileType)
		{
			// The file is not of an appropriate type
			return "ERROR: The file is not of an appropriate type, for the '{$arrResourceType['PlaceHolder']}' Resource";
		}
		
		// Validate the Start time and End time
		$strNow = GetCurrentDateAndTimeForMySQL();
		if ($mixStart == 0)
		{
			// The resource will start immediately
			$strStartDatetime = $strNow;
		}
		else
		{
			if (!Validate("ShortDate", $mixStart))
			{
				return "ERROR: The Starting date is invalid.  It must be in the format dd/mm/yyyy";
			}
			
			// Check that the StartDate is greater than today
			$strStartDatetime = ConvertUserDateToMySqlDate($mixStart) . " 00:00:00";
			if ($strNow >= $strStartDatetime)
			{
				return "ERROR: The Starting date is invalid.  It must be in the future";
			}
		}
		
		if ($mixEnd == 0)
		{
			// The resource will be used indefinitely
			$strEndDatetime = END_OF_TIME;
		}
		else
		{
			if (!Validate("ShortDate", $mixStart))
			{
				return "ERROR: The Ending date is invalid.  It must be in the format dd/mm/yyyy";
			}
			
			// Check that the EndDate is greater than the StartDate
			$strEndDatetime = ConvertUserDateToMySqlDate($mixEnd) . " 23:59:59";
			if ($strStartDatetime > $strEndDatetime)
			{
				return "ERROR: The Ending date is invalid.  It must be greater than the Starting date";
			}
		}
		
		// Add the DocumentResource Record
		TransactionStart();
		$arrResource = Array(	"CustomerGroup"		=> $intCustomerGroup,
								"Type"				=> $intResourceType,
								"FileType"			=> $intFileTypeId,
								"StartDatetime"		=> $strStartDatetime,
								"EndDatetime"		=> $strEndDatetime,
								"CreatedOn"			=> $strNow,
								"OriginalFilename"	=> $strFilename
							);
		$insResource	= new StatementInsert("DocumentResource", $arrResource);
		$intResourceId	= $insResource->Execute($arrResource);
		
		if (!$intResourceId)
		{
			// Inserting the DocumentResource record failed
			TransactionRollback();
			return "ERROR: Adding the Resource to the database failed, unexpectedly.  Please notify your system administrator";
		}
		
		// Move the file to {SHARED_BASE_PATH}/template/resource/{CustomerGroupId}/{ResourceId}.Extension
		$strNewFilename	= "{$intResourceId}.{$strExtension}";
		$strPath		= SHARED_BASE_PATH . "/template/resource/$intCustomerGroup";
		
		// Make the directory if it doesn't already exist
		if (!RecursiveMkdir($strPath))
		{
			TransactionRollback();
			return "ERROR: Creating the directory failed, unexpectedly.  Please notify your system administrator"; 
		}
		$strDestination = $strPath . "/". $strNewFilename;
		
		if (move_uploaded_file($strTempFilename, $strDestination))
		{
			TransactionRollback();
			return "ERROR: Moving the file to it's destination failed, unexpectedly.  Please notify your system administrator";
		}

		// Everything worked
		TransactionCommit();
		return TRUE;
	}

	// Returns the record (associative array) of the current document template schema for the specified TemplateType
	// Returns FALSE on error
	private function _GetCurrentSchema($intTemplateType)
	{
		$selSchema = new StatementSelect("DocumentTemplateSchema", "*", "Id = (SELECT MAX(Id) FROM DocumentTemplateSchema WHERE TemplateType = <TemplateType>)");
		if (!$selSchema->Execute(Array("TemplateType" => $intTemplateType)))
		{
			return FALSE;
		}
		
		return $selSchema->Fetch();
	}

	// Returns the record (associative array) of the draft template or returns NULL if there is no draft template
	// Returns FALSE on error
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

	//------------------------------------------------------------------------//
	// LoadTemplate
	//------------------------------------------------------------------------//
	/**
	 * LoadTemplate()
	 *
	 * Loads the template and associated objects into the DBO() collection for use by the "Edit Template" and "View Template" pages 
	 * 
	 * Loads the template and associated objects into the DBO() collection for use by the "Edit Template" and "View Template" pages
	 * It expects the following values to be defined:
	 *
	 * @param	int		$intId					id of template to load
	 * @param	bool	$bolUseCurrentSchema	optional, defaults to FALSE.  If set to TRUE then the most recent
	 * 											TemplateSchema (for this TemplateType) will be used, instead of the
	 * 											one specified by the DocumentTemplate
	 *
	 * @return	bool							TRUE on success. FALSE on failure
	 * @method	LoadTemplate
	 */
	private function _LoadTemplate($intId, $bolUseCurrentSchema=FALSE)
	{
		DBO()->DocumentTemplate->Id = $intId;
		if (!DBO()->DocumentTemplate->Load())
		{
			// The template could not be loaded
			return FALSE;
		}
		
		// Load the CustomerGroup record as it is needed for the breadcrumb menu
		DBO()->CustomerGroup->Id = DBO()->DocumentTemplate->CustomerGroup->Value;
		DBO()->CustomerGroup->Load();
		
		// Load the details of the DocumentTemplateType as this is also needed
		DBO()->DocumentTemplateType->Id = DBO()->DocumentTemplate->TemplateType->Value;
		DBO()->DocumentTemplateType->Load();
		
		// Load the schema
		if ($bolUseCurrentSchema)
		{
			// Load the most recent schema for this DocumentTemplateType
			$arrSchema = $this->_GetCurrentSchema(DBO()->DocumentTemplate->TemplateType->Value);
			DBO()->DocumentTemplateSchema->_arrProperties = $arrSchema;
		}
		else
		{
			// Load the schema that is actually used by this Template regardless of whether or not it is the most recent
			DBO()->DocumentTemplateSchema->Id = DBO()->DocumentTemplate->TemplateSchema->Value;
			DBO()->DocumentTemplateSchema->Load();
		}
		
		return TRUE;
	}


    //----- DO NOT REMOVE -----//
	
}
?>