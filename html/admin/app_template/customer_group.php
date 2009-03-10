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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);
		
		// Breadcrumb menu
		BreadCrumb()->Admin_Console();
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN))
		{
			BreadCrumb()->System_Settings_Menu();
		}
		BreadCrumb()->SetCurrentPage("Customer Groups");
		
		// Retrieve the list of customer groups
		DBL()->CustomerGroup->OrderBy("internal_name");
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
			
			// Default Values
			DBO()->CustomerGroup->invoice_cdr_credits	= 0;
			
			// Check that the CustomerGroup's internal_name is not being used by another CustomerGroup
			$selCustomerGroup = new StatementSelect("CustomerGroup", "Id", "internal_name LIKE <Name>", "", "1");
			$intRecordFound = $selCustomerGroup->Execute(Array("Name"=> DBO()->CustomerGroup->internal_name->Value));
			if ($intRecordFound)
			{
				// The CustomerGroup's internal_name is already in use by another CustomerGroup
				DBO()->CustomerGroup->internal_name->SetToInvalid();
				Ajax()->AddCommand("Alert", "ERROR: This name is already in use by another Customer Group");
				Ajax()->RenderHtmlTemplate("CustomerGroupNew", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
				return TRUE;
			}
			// DBO()->CustomerGroup->SetColumns("Id,internal_name,external_name,outbound_email");
			// The CustomerGroup is valid.  Save it
			if (!DBO()->CustomerGroup->Save())
			{
				// The CustomerGroup could not be saved for some unforseen reason
				Ajax()->AddCommand("Alert", "ERROR: Saving the CustomerGroup failed, unexpectedly");
				return TRUE;
			}
			
			// Create default customer_group_delivery_method entries
			$arrDeliveryMethods	= Delivery_Method::getAll();
			foreach ($arrDeliveryMethods as $objDeliveryMethod)
			{
				$objCustomerGroupDeliveryMethod	= new Customer_Group_Delivery_Method();
				
				$objCustomerGroupDeliveryMethod->customer_group_id		= DBO()->CustomerGroup->Id->Value;
				$objCustomerGroupDeliveryMethod->delivery_method_id		= $objDeliveryMethod->id;
				$objCustomerGroupDeliveryMethod->minimum_invoice_value	= Customer_Group_Delivery_Method::DEFAULT_MINIMUM_INVOICE_VALUE;
				$objCustomerGroupDeliveryMethod->employee_id			= Flex::getUserId();
				
				try
				{
					$objCustomerGroupDeliveryMethod->save();
				}
				catch (Exception $eException)
				{
					Ajax()->AddCommand("Alert", "ERROR: Saving the Customer Group Delivery Method details failed, unexpectedly");
					return TRUE;
				}
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
	// ChangeLogo
	//------------------------------------------------------------------------//
	/**
	 * ChangeLogo()
	 *
	 * Handles the logic for adding a new customer group logo
	 * 
	 * Handles the logic for adding a new customer group logo
	 *
	 * @return		void
	 * @method
	 */
	function ChangeLogo()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		// Breadcrumb menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->System_Settings_Menu();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->SetCurrentPage("Modify Customer Group");

		if(array_key_exists('CustomerGroup_Id', $_POST))
		{
			if(!empty($_POST['CustomerGroup_Id']))
			{
			$strFileName = $_FILES['userfile']['name'];
			$strTmpName  = $_FILES['userfile']['tmp_name'];
			$strFileType = $_FILES['userfile']['type'];
			
			$resImage      = fopen($strTmpName, 'r');
			$mixContent = fread($resImage, filesize($strTmpName));
			fclose($resImage);

			DBO()->CustomerGroup->Id = $_POST['CustomerGroup_Id'];
			DBO()->CustomerGroup->Load();
			DBO()->CustomerGroup->customer_logo = $mixContent;
			DBO()->CustomerGroup->customer_logo_type = $strFileType;
			DBO()->CustomerGroup->SetColumns("customer_logo,customer_logo_type");
			DBO()->CustomerGroup->Save();
			}
			else
			{
				// Group ID entered was empty.
				$this->LoadPage('customer_group_view');
			}

		}

		// Declare which Page Template to use
		$this->LoadPage('customer_group_change_logo');

		return TRUE;
	}





	//------------------------------------------------------------------------//
	// ChangeAdvertisement
	//------------------------------------------------------------------------//
	/**
	 * ChangeAdvertisement()
	 *
	 * Handles the logic for adding a new customer group advertisement image
	 * 
	 * Handles the logic for adding a new customer group advertisement image
	 *
	 * @return		void
	 * @method
	 */
	function ChangeAdvertisement()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		// Breadcrumb menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->System_Settings_Menu();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->SetCurrentPage("Modify Customer Group");

		if(array_key_exists('CustomerGroup_Id', $_POST))
		{
			if(!empty($_POST['CustomerGroup_Id']))
			{
				if ((($_FILES["userfile"]["type"] == "image/gif") || ($_FILES["userfile"]["type"] == "image/jpeg") || ($_FILES["userfile"]["type"] == "image/png")) && ($_FILES["userfile"]["size"] < 9000000))
				{
					if ($_FILES["userfile"]["error"] > 0)
					{
						DBO()->ChangeAdvertisement->Error =  "Error: " . $_FILES["userfile"]["error"] . "<br />";
					}
					else
					{

						$strFileName = $_FILES['userfile']['name'];
						$strTmpName  = $_FILES['userfile']['tmp_name'];
						$strFileType = $_FILES['userfile']['type'];
						
						$resImage      = fopen($strTmpName, 'r');
						$mixContent = fread($resImage, filesize($strTmpName));
						fclose($resImage);

						$dbConnection = GetDBConnection($GLOBALS['**arrDatabase']["flex"]['Type']);
						$dbConnection->execute("UPDATE CustomerGroup SET customer_advert_image=\"" . base64_encode(serialize($mixContent)) . "\",customer_advert_image_type=\"$strFileType\" WHERE Id=\"$_POST[CustomerGroup_Id]\"");
					}
				}
				else
				{
					DBO()->ChangeAdvertisement->Error = "Invalid file";
				}
			}
			else
			{
				// Group ID entered was empty.
				$this->LoadPage('customer_group_view');
			}

		}

		// Declare which Page Template to use
		$this->LoadPage('customer_group_change_advertisement');

		return TRUE;
	}


	//------------------------------------------------------------------------//
	// CreditCardConfig
	//------------------------------------------------------------------------//
	/**
	 * CreditCardConfig()
	 *
	 * Handles the logic for CreditCardConfig Customer Group functionality
	 * 
	 * Handles the logic for CreditCardConfig Customer Group functionality
	 * Assumes DBO()->CustomerGroup->Id to be set
	 *
	 * @return		void
	 * @method
	 */
	function CreditCardConfig()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);

		// Load the CustomerGroupDetails
		if (!DBO()->CustomerGroup->Load())
		{
			DBO()->Error->Message = "The Customer Group with account id: ". DBO()->CustomerGroup->Id->value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}

		// Load the CustomerGroupDetails
		if (!defined('FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS') || !FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS)
		{
			DBO()->Error->Message = "The Credit Card Payment module has not been enabled in Flex.";
			$this->LoadPage('error');
			return FALSE;
		}

		// Breadcrumb menu
		BreadCrumb()->Admin_Console();
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN))
		{
			BreadCrumb()->System_Settings_Menu();
		}
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->internal_name->Value);
		BreadCrumb()->SetCurrentPage("Credit Card Config");

		// Declare which Page Template to use
		$this->LoadPage('customer_group_credit_card_config');

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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);
		
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
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN))
		{
			BreadCrumb()->System_Settings_Menu();
		}
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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);

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
		
		// Check that the CustomerGroup's internal_name is not being used by another CustomerGroup
		$selCustomerGroup	= new StatementSelect("CustomerGroup", "Id", "internal_name LIKE <Name> AND Id != <Id>", "", "1");
		$intRecordFound		= $selCustomerGroup->Execute(Array(	"Name"=> DBO()->CustomerGroup->internal_name->Value, 
																"Id"=> DBO()->CustomerGroup->Id->Value));
		if ($intRecordFound)
		{
			// The CustomerGroup's new internal_name is already in use by another CustomerGroup
			DBO()->CustomerGroup->internal_name->SetToInvalid();
			Ajax()->AddCommand("Alert", "ERROR: This name is already in use by another Customer Group");
			Ajax()->RenderHtmlTemplate("CustomerGroupDetails", HTML_CONTEXT_EDIT, $this->_objAjax->strContainerDivId, $this->_objAjax);
			return TRUE;
		}

		DBO()->CustomerGroup->cooling_off_period = (trim(DBO()->CustomerGroup->cooling_off_period->Value) == "")? NULL : intval(DBO()->CustomerGroup->cooling_off_period->Value);

		DBO()->CustomerGroup->customer_primary_color = ereg_replace("[^a-zA-Z0-9]", "", DBO()->CustomerGroup->customer_primary_color->Value);
		DBO()->CustomerGroup->customer_secondary_color = ereg_replace("[^a-zA-Z0-9]", "", DBO()->CustomerGroup->customer_secondary_color->Value);
		DBO()->CustomerGroup->SetColumns("Id,internal_name,external_name,outbound_email,flex_url,email_domain,customer_primary_color,customer_secondary_color,customer_exit_url,external_name_possessive,bill_pay_biller_code,abn,acn,business_phone,business_fax,business_web,business_contact_email,business_info_email,customer_service_phone,customer_service_email,customer_service_contact_name,business_payable_name,business_payable_address,credit_card_payment_phone,faults_phone,customer_advert_url,cooling_off_period");
		// The CustomerGroup is valid.  Save it
		if (!DBO()->CustomerGroup->Save())
		{
			// The CustomerGroup could not be saved for some unforseen reason
			Ajax()->AddCommand("Alert", "ERROR: Saving changes to the CustomerGroup failed, unexpectedly");
			return TRUE;
		}
		
		// If the sales module is active then update the sales database (the vendor table)
		if (Data_Source::dsnExists(FLEX_DATABASE_CONNECTION_SALES))
		{
			try
			{
				Cli_App_Sales::pushAll();
			}
			catch (Exception $e)
			{
				// Pushing the data failed
				$strWarning = "Pushing the data from Flex to the Sales database, failed. Contact your system administrators to have them manually trigger the data push.  (Error message: ". htmlspecialchars($e->getMessage()) .")";
				Ajax()->AddCommand("Alert", $strWarning);
			}
			
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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);
		$bolUserIsSuperAdmin = AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN);
		
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
			// Redirect the user to the AddNewTemplate page, if they have permission to add a new template
			if ($bolUserIsSuperAdmin)
			{
				return $this->BuildNewTemplate();
			}
			else
			{
				DBO()->Error->Message = "You do not have permission to build a new template";
				$this->LoadPage('error');
				return TRUE;
			}
		}
		
		// Build Context Menu
		//TODO! When we have stuff to put in it
		
		// Breadcrumb menu
		BreadCrumb()->Admin_Console();
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN))
		{
			BreadCrumb()->System_Settings_Menu();
		}
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->internal_name->Value);
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
				DBO()->Error->Message = "The DocumentTemplate of which to base the new template on, is not owned by the ". DBO()->CustomerGroup->internal_name->Value ." customer group";
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
		DBO()->DocumentTemplate->Description = "Version ". DBO()->DocumentTemplate->Version->Value ." for ". DBO()->CustomerGroup->internal_name->Value ." ". DBO()->DocumentTemplateType->Name->Value ." Template";		

		if (DBO()->BaseTemplate->Id->Value)
		{
			// Load the Template Source code from the BaseTemplate, into the new one
			DBO()->DocumentTemplate->Source	= DBO()->BaseTemplate->Source->Value;
		}
		
		// Load all the DocumentResourceTypes
		DBL()->DocumentResourceType->OrderBy("PlaceHolder ASC");
		DBL()->DocumentResourceType->Load();
		
		// Context Menu
		//TODO!
		
		//BreadCrumb Menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->SystemSettingsMenu();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->internal_name->Value);
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
		
		// Load all the DocumentResourceTypes
		DBL()->DocumentResourceType->OrderBy("PlaceHolder ASC");
		DBL()->DocumentResourceType->Load();
		
		// Context Menu
		//TODO!
		
		//BreadCrumb Menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->SystemSettingsMenu();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->internal_name->Value);
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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);
		
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
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN))
		{
			BreadCrumb()->System_Settings_Menu();
		}
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->internal_name->Value);
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
			DBO()->Template->Description = "Version ". DBO()->Template->Version->Value ." for ". DBO()->CustomerGroup->internal_name->Value ." ". DBO()->DocumentTemplateType->Name->Value;
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
		// Don't send back the template's source code
		unset($arrReply['Template']['Source']);
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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);
		
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
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN))
		{
			BreadCrumb()->System_Settings_Menu();
		}
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->internal_name->Value);
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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);
		
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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);
		
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
	// ViewDocumentResource
	//------------------------------------------------------------------------//
	/**
	 * ViewDocumentResource()
	 *
	 * Retrieves a Document Resource and declares its MIME type so that an apropriate application can display it
	 * 
	 * Retrieves a Document Resource and declares its MIME type so that an apropriate application can display it
	 * It expects the following objects to be defined
	 * 	DBO()->DocumentResource->Id					Id of the resource to view
	 *  DBO()->DocumentResource->DownloadFile		Set to TRUE, to download the file, instead of just viewing it
	 * 
	 * @return		void
	 * @method	ViewDocumentResource
	 */
	function ViewDocumentResource()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);
		
		$intResourceId			= DBO()->DocumentResource->Id->Value;
		$bolDownloadResource	= DBO()->DocumentResource->DownloadFile->Value;
		
		$arrColumns 	= Array(	"ResourceId"		=> "DR.Id",
									"CustomerGroup"		=> "DR.CustomerGroup",
									"OriginalFilename"	=> "DR.OriginalFilename",
									"FileContent"		=> "DR.FileContent",
									"MIMEType"			=> "FT.MIMEType"
								);
		$strFrom		= "DocumentResource AS DR INNER JOIN FileType AS FT ON DR.FileType = FT.Id";
		$selResource	= new StatementSelect($strFrom, $arrColumns, "DR.Id = <Id>");
		$mixResult		= $selResource->Execute(Array("Id" => $intResourceId));
		
		if ($mixResult === FALSE)
		{
			DBO()->Error->Message = "An unexpected error occurred when trying to retrieve the Resource from the database.  Please notify your system administrator";
			$this->LoadPage('error');
			return TRUE;
		}
		elseif ($mixResult == 0)
		{
			DBO()->Error->Message = "The resource with Id: $intResourceId, could not be found in the database";
			$this->LoadPage('error');
			return TRUE;
		}
		
		$arrResource = $selResource->Fetch();
		
		// Send the file to the user
		header("Content-Type: {$arrResource['MIMEType']}");
		if ($bolDownloadResource)
		{
			// This will prompt the user to save the file, with its original filename
			header("Content-Disposition: attachment; filename=\"{$arrResource['OriginalFilename']}\"");
		}
		echo $arrResource['FileContent'];
		exit;
	}


	//------------------------------------------------------------------------//
	// UploadDocumentResource
	//------------------------------------------------------------------------//
	/**
	 * UploadDocumentResource()
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
	 * @method	UploadDocumentResource
	 */
	function UploadDocumentResource()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);
		
		// Retrieve the FileTypes that this resource can be
		$intResourceType = DBO()->DocumentResource->Type->Value;
		
		$strWhere = "Id IN (SELECT file_type_id FROM document_resource_type_file_type WHERE document_resource_type_id = $intResourceType)";
		$selFileTypes = new StatementSelect("document_resource_type_file_type", "*", $strWhere);
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
			
			$mixResult = $this->_UploadDocumentResource($arrFileTypes, $intResourceType, $intCustomerGroup, $mixStart, $mixEnd);

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

	//------------------------------------------------------------------------//
	// ViewSamplePDF
	//------------------------------------------------------------------------//
	/**
	 * ViewSamplePDF()
	 *
	 * Produces the "Sample PDF" popup (THIS ISN'T USED YET)
	 * 
	 * Produces the "Sample PDF" popup
	 * It can have the following values declared, although they are both optional:
	 *	DBO()->CustomerGroup->Id			Id of the customer group
	 *	DBO()->DocumentTemplateType->Id		Id of the DocumentTemplateType to produce a pdf of
	 *
	 * @return	void
	 * @method	ViewSamplePDF
	 */
	function ViewSamplePDF()
	{
		//TODO! Implement this popup
		//(Currently Sample PDFs are built through a popup that is defined in the DocumentTemplate HtmlTemplate and document_template.js files)
		
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);
		
		// Load all Customer Groups
		DBL()->CustomerGroup->OrderBy("internal_name ASC");
		DBL()->CustomerGroup->Load();
		
		// Load all the Document Template Types
		DBL()->DocumentTemplateType->OrderBy("Name");
		DBL()->DocumentTemplateType->Load();
		
		
		// Load the page template
		$this->LoadPage('ViewSamplePDF');
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// BuildSamplePDFOldMethodUsingFormSubmittion DEPRICATED
	//------------------------------------------------------------------------//
	/**
	 * BuildSamplePDFOldMethodUsingFormSubmittion()
	 *
	 * Produces a sample pdf based on a Document Template
	 * 
	 * Produces a sample pdf based on a Document Template
	 * It must have the following values declared:
	 * 	DBO()->DocumentTemplateType->Id
	 * 	DBO()->CustomerGroup->Id			Id of the CustomerGroup
	 *  DBO()->Generation->Date				Hypothetical date on which the PDF will be generated (dd/mm/yyyy)
	 *	DBO()->Generation->Time				Hypothetical time on which the PDF will be generated (hh:mm:ss)
	 *	(The appropriate DocumentTemplateType will be found and used)
	 * OR
	 * 	DBO()->Template->Source				SourceCode of the DocumentTemplate
	 *	DBO()->Schema->Id					Id of the DocumentTemplateType to produce a pdf of
	 * 	DBO()->DocumentTemplateType->Id
	 *	DBO()->CustomerGroup->Id
	 *  DBO()->Generation->Date
	 *	DBO()->Generation->Time
	 *	(the pdf will be built using the supplied Source code and schema)
	 *
	 * If Generation->Date and Generation->Time are not declared then it will use NOW()
	 *
	 * @return	void
	 * @method	BuildSamplePDFOldMethodUsingFormSubmittion
	 */
	function BuildSamplePDFOldMethodUsingFormSubmittion()
	{
		if(is_array($_POST))
		{
			foreach($_POST AS $strName=>$strValue)
			{
				// parse variable
				echo "<br />POST variable '$strName' is ". strlen($strValue) ." chars long, assuming it's a string\n";
			}
		}

		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);
		
		if (!DBO()->CustomerGroup->Load())
		{
			echo "ERROR: Could not load the CustomerGroup record";
			exit;
		}
		
		if (!DBO()->DocumentTemplateType->Load())
		{
			echo "ERROR: Could not load the DocumentTemplateType record";
			exit;
		}
		
		// Validate the Date and Time parameters
		if (!Validate("ShortDate", DBO()->Generation->Date->Value))
		{
			echo "ERROR: The Generation Date is invalid.  It must be in the format DD/MM/YYYY";
			exit;
		}
		
		if (!Validate("Time", DBO()->Generation->Time->Value))
		{
			echo "ERROR: The Generation Time is invalid.  It must be in the format HH:MM:SS";
			exit;
		}
		$strGenerationDate = ConvertUserDateToMySqlDate(DBO()->Generation->Date->Value) ." ". DBO()->Generation->Time->Value;
		
		
		// Check if the template's source code hasn't been supplied
		if (!DBO()->Template->Source->IsSet)
		{
			// Find the right template to use
			$strWhere = "TemplateType = <TemplateType> AND CustomerGroup = <CustomerGroup> AND EffectiveOn <= <GenerationDate>";
			$arrWhere = Array	(	
									"TemplateType"	=> DBO()->DocumentTemplateType->Id->Value,
									"CustomerGroup"	=> DBO()->CustomerGroup->Id->Value,
									"EffectiveOn"	=> $strGenerationDate
								);
			$selTemplate	= new StatementSelect("DocumentTemplate", "Source, TemplateSchema", $strWhere, "CreatedOn DESC", "1");
			$mixResult		= $selTemplate->Execute($arrWhere);
			if ($mixResult === FALSE)
			{
				echo "ERROR: Retrieving the approriate DocumentTemplate from the database failed, unexpectedly.  Please notify your system administrator";
				exit;
			}
			if ($mixResult == 0)
			{
				echo "ERROR: Could not find an appropriate DocumentTemplate for CustomerGroup: ". DBO()->CustomerGroup->internal_name->Value .", TemplateType: ". DBO()->DocumentTemplateType->Name->Value .", for generation on $strGenerationDate";
				exit;
			}
			
			$arrTemplate			= $selTemplate->Fetch();
			DBO()->Template->Source	= $arrTemplate['Source'];
			DBO()->Schema->Id		= $arrTemplate['TemplateSchema'];
		}
		
		// Load the Schema
		DBO()->Schema->SetTable("DocumentTemplateSchema");
		if (!DBO()->Schema->Load())
		{
			echo "ERROR: Could not load DocumentTemplateSchema record";
			exit;
		}
		
		$strDate			= ConvertUserDateToMySqlDate(DBO()->Generation->Date->Value);
		$strEffectiveDate	= $strDate ." ". DBO()->Generation->Time->Value;
		$strTemplateXSLT	= DBO()->Template->Source->Value;
		$intCustomerGroup	= DBO()->CustomerGroup->Id->Value;
		$strSampleXML		= DBO()->Schema->Sample->Value;
		
		VixenRequire("lib/pdf/Flex_Pdf_Template.php");
		
		set_time_limit(120);
		
		try
		{
			$objPDFTemplate	= new Flex_Pdf_Template($intCustomerGroup, $strEffectiveDate, $strTemplateXSLT, $strSampleXML, Flex_Pdf_Style::MEDIA_ALL, TRUE);
			$objPDFDocument	= $objPDFTemplate->createDocument();

			ob_start();
			echo $objPDFDocument->render();
			$strPdf = ob_get_clean();
		}
		catch (Exception $objException) 
		{
			// Turn output buffering off, if it was on
			ob_get_clean();
			echo "ERROR: PDF generation failed<br /><br />". $objException->getMessage();
			exit;
		}
		
		// The pdf was successfully created
		$strFilename = "Sample_". str_replace(" ", "_", DBO()->CustomerGroup->internal_name->Value) ."_". str_replace(" ", "_", DBO()->DocumentTemplateType->Name->Value) ."_". str_replace("-", "_", $strDate) ."_". str_replace(":", "_", DBO()->Generation->Time->Value) .".pdf";
		header("Content-type: application/pdf;");
		header("Content-Disposition: attachment; filename=\"$strFilename\"");
		echo $strPdf;
		exit;
	}
	
	//------------------------------------------------------------------------//
	// BuildSamplePDF
	//------------------------------------------------------------------------//
	/**
	 * BuildSamplePDF()
	 *
	 * Produces a sample pdf based on a Document Template
	 * 
	 * Produces a sample pdf based on a Document Template
	 * It must have the following values declared:
	 * 	DBO()->DocumentTemplateType->Id
	 * 	DBO()->CustomerGroup->Id			Id of the CustomerGroup
	 *  DBO()->Generation->Date				Hypothetical date on which the PDF will be generated (dd/mm/yyyy)
	 *	DBO()->Generation->Time				Hypothetical time on which the PDF will be generated (hh:mm:ss)
	 *	DBO()->Generation->MediaType		DocumentTemplateMediaType with which the PDF will be generated as
	 *	(The appropriate DocumentTemplateType will be found and used)
	 * OR
	 * 	DBO()->Template->Source				SourceCode of the DocumentTemplate
	 *	DBO()->Schema->Id					Id of the DocumentTemplateType to produce a pdf of
	 * 	DBO()->DocumentTemplateType->Id
	 *	DBO()->CustomerGroup->Id
	 *  DBO()->Generation->Date
	 *	DBO()->Generation->Time
	 *	DBO()->Generation->MediaType
	 *	(the pdf will be built using the supplied Source code and schema)
	 *
	 * @return	void
	 * @method	BuildSamplePDF
	 */
	function BuildSamplePDF()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);
		
		$_SESSION['DocumentTemplateSamplePDF'] = "";
				
		if (!DBO()->CustomerGroup->Load())
		{
			Ajax()->AddCommand("Alert", "ERROR: Could not load the CustomerGroup record");
			return TRUE;
		}
		
		if (!DBO()->DocumentTemplateType->Load())
		{
			Ajax()->AddCommand("Alert", "ERROR: Could not load the DocumentTemplateType record");
			return TRUE;
		}
		
		// Validate the Date and Time parameters
		if (!Validate("ShortDate", DBO()->Generation->Date->Value))
		{
			Ajax()->AddCommand("Alert", "ERROR: The Generation Date is invalid.  It must be in the format DD/MM/YYYY");
			return TRUE;
		}
		
		if (!Validate("Time", DBO()->Generation->Time->Value))
		{
			Ajax()->AddCommand("Alert", "ERROR: The Generation Time is invalid.  It must be in the format HH:MM:SS");
			return TRUE;
		}
		$strGenerationDate = ConvertUserDateToMySqlDate(DBO()->Generation->Date->Value) ." ". DBO()->Generation->Time->Value;
		
		
		// Check if the template's source code hasn't been supplied
		if (!DBO()->Template->Source->IsSet)
		{
			// Find the right template to use
			$strWhere = "TemplateType = <TemplateType> AND CustomerGroup = <CustomerGroup> AND EffectiveOn <= <GenerationDate>";
			$arrWhere = Array	(	
									"TemplateType"	=> DBO()->DocumentTemplateType->Id->Value,
									"CustomerGroup"	=> DBO()->CustomerGroup->Id->Value,
									"EffectiveOn"	=> $strGenerationDate
								);
			$selTemplate	= new StatementSelect("DocumentTemplate", "Source, TemplateSchema", $strWhere, "CreatedOn DESC", "1");
			$mixResult		= $selTemplate->Execute($arrWhere);
			if ($mixResult === FALSE)
			{
				Ajax()->AddCommand("Alert", "ERROR: Retrieving the approriate DocumentTemplate from the database failed, unexpectedly.  Please notify your system administrator");
				return TRUE;
			}
			if ($mixResult == 0)
			{
				Ajax()->AddCommand("Alert", "ERROR: Could not find an appropriate DocumentTemplate for CustomerGroup: ". DBO()->CustomerGroup->internal_name->Value .", TemplateType: ". DBO()->DocumentTemplateType->Name->Value .", for generation on $strGenerationDate");
				return TRUE;
			}
			
			$arrTemplate			= $selTemplate->Fetch();
			DBO()->Template->Source	= $arrTemplate['Source'];
			DBO()->Schema->Id		= $arrTemplate['TemplateSchema'];
		}
		
		// Load the Schema
		DBO()->Schema->SetTable("DocumentTemplateSchema");
		if (!DBO()->Schema->Load())
		{
			Ajax()->AddCommand("Alert", "ERROR: Could not load DocumentTemplateSchema record");
			return TRUE;
		}
		
		$strDate			= ConvertUserDateToMySqlDate(DBO()->Generation->Date->Value);
		$intMediaType		= DBO()->Generation->MediaType->Value;
		$strEffectiveDate	= $strDate ." ". DBO()->Generation->Time->Value;
		$strTemplateXSLT	= DBO()->Template->Source->Value;
		$intCustomerGroup	= DBO()->CustomerGroup->Id->Value;
		$strSampleXML		= DBO()->Schema->Sample->Value;
		
		VixenRequire("lib/pdf/Flex_Pdf_Template.php");
		
		set_time_limit(120);

		try
		{
			$objPDFTemplate	= new Flex_Pdf_Template($intCustomerGroup, $strEffectiveDate, $strTemplateXSLT, $strSampleXML, $intMediaType, TRUE);
			$objPDFDocument	= $objPDFTemplate->createDocument();

			ob_start();
			echo $objPDFDocument->render();
			$strPdf = ob_get_clean();
		}
		catch (Exception $objException) 
		{
			// Turn output buffering off, if it was on
			ob_get_clean();
			Ajax()->AddCommand("Alert", "ERROR: PDF generation failed<br /><br />". $objException->getMessage());
			return TRUE;
		}

		// The pdf was successfully built
		// Save it to the session, so that we can both report on the process, and let the user retrieve it as a file
		$strFilename = "Sample_". str_replace(" ", "_", DBO()->CustomerGroup->internal_name->Value) ."_". str_replace(" ", "_", DBO()->DocumentTemplateType->Name->Value) ."_". str_replace("-", "_", $strDate) ."_". str_replace(":", "_", DBO()->Generation->Time->Value) .".pdf";
		$_SESSION['DocumentTemplateSamplePdf'] = $strPdf;
		$_SESSION['DocumentTemplateSamplePdfFilename'] = $strFilename;
		
		$arrReply = Array('Success' => TRUE);
		AjaxReply($arrReply);
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// GetSamplePDF
	//------------------------------------------------------------------------//
	/**
	 * GetSamplePDF()
	 *
	 * Retrieves the sample pdf which should be stored in the user's session data
	 * 
	 * Retrieves the sample pdf which should be stored in the user's session data
	 *
	 * @return	void
	 * @method	GetSamplePDF
	 */
	function GetSamplePDF()
	{
		$strPdf			= $_SESSION['DocumentTemplateSamplePdf'];
		$strFilename	= $_SESSION['DocumentTemplateSamplePdfFilename'];
		
		// Remove the pdf from the session array
		unset($_SESSION['DocumentTemplateSamplePdf']);
		unset($_SESSION['DocumentTemplateSamplePdfFilename']);
		
		if ($strPdf == "")
		{
			DBO()->Error->Message = "ERROR: Could not find the pdf requested";
			$this->LoadPage('error');
			return TRUE;
		}
		
		header("Content-type: application/pdf;");
		header("Content-Disposition: attachment; filename=\"$strFilename\"");
		echo $strPdf;
		exit;
	}
	
	// Returns TRUE on success, or an error msg on failure
	private function _UploadDocumentResource($arrFileTypes, $intResourceType, $intCustomerGroup, $mixStart, $mixEnd)
	{
		$strFilename		= $_FILES['ResourceFile']['name'];
		$strTempFilename	= $_FILES['ResourceFile']['tmp_name'];
		$intFileStatus		= $_FILES['ResourceFile']['error'];
		$strFileType		= $_FILES['ResourceFile']['type'];
		$intFileSize		= $_FILES['ResourceFile']['size'];
		
		$arrFilenameParts	= explode(".", $strFilename);
		$strExtension		= strtolower($arrFilenameParts[count($arrFilenameParts)-1]);
		
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
			if ($strExtension == strtolower($arrFileType['Extension']))
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
			if (!Validate("ShortDate", $mixEnd))
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
		
		$blobFileContent = file_get_contents($strTempFilename);
		if ($blobFileContent === FALSE)
		{
			return "ERROR: Could not read the file";
		}
		
		// Add the DocumentResource Record
		TransactionStart();
		$arrResource = Array(	"CustomerGroup"		=> $intCustomerGroup,
								"Type"				=> $intResourceType,
								"FileType"			=> $intFileTypeId,
								"StartDatetime"		=> $strStartDatetime,
								"EndDatetime"		=> $strEndDatetime,
								"CreatedOn"			=> $strNow,
								"OriginalFilename"	=> $strFilename,
								"FileContent"		=> $blobFileContent
							);
		 
		$insResource	= new StatementInsert("DocumentResource", $arrResource);
		$intResourceId	= $insResource->Execute($arrResource);
		
		if (!$intResourceId)
		{
			// Inserting the DocumentResource record failed
			TransactionRollback();
			return "ERROR: Adding the Resource to the database failed, unexpectedly.  Please notify your system administrator";
		}
/*		
		// Move the file to {SHARED_BASE_PATH}/template/resource/{CustomerGroupId}/{ResourceId}.Extension
		$strNewFilename	= "{$intResourceId}.{$strExtension}";
		$strPath		= SHARED_BASE_PATH . "/template/resource/$intCustomerGroup";
		
		// Make the directory if it doesn't already exist
		if (!RecursiveMkdir($strPath))
		{
			TransactionRollback();
			return "ERROR: Creating the directory structure, for resources belong to this customer group, failed unexpectedly.  Please notify your system administrator"; 
		}
		$strDestination = $strPath . "/". $strNewFilename;
		
		if (!move_uploaded_file($strTempFilename, $strDestination))
		{
			TransactionRollback();
			return "ERROR: Moving the file to its destination failed, unexpectedly.  Please notify your system administrator";
		}
*/
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
