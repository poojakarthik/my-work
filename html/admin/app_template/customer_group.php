<?php
class AppTemplateCustomerGroup extends ApplicationTemplate {
	function ViewAll() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);

		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Customer Groups");

		// Retrieve the list of customer groups
		DBL()->CustomerGroup->OrderBy("internal_name");
		DBL()->CustomerGroup->Load();

		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('customer_groups_list');

		return true;
	}

	function Add() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);

		// Check if the form was submitted
		if (SubmittedForm('NewCustomerGroup', 'Ok')) {
			if (DBO()->CustomerGroup->IsInvalid()) {
				// At least one Field is invalid
				Ajax()->AddCommand("Alert", "ERROR: Invalid fields are highlighted");
				Ajax()->RenderHtmlTemplate("CustomerGroupNew", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
				return true;
			}

			// Default Values
			DBO()->CustomerGroup->invoice_cdr_credits = 0;

			// Check that the CustomerGroup's internal_name is not being used by another CustomerGroup
			$selCustomerGroup = new StatementSelect("CustomerGroup", "Id", "internal_name LIKE <Name>", "", "1");
			$intRecordFound = $selCustomerGroup->Execute(array("Name"=> DBO()->CustomerGroup->internal_name->Value));
			if ($intRecordFound) {
				// The CustomerGroup's internal_name is already in use by another CustomerGroup
				DBO()->CustomerGroup->internal_name->SetToInvalid();
				Ajax()->AddCommand("Alert", "ERROR: This name is already in use by another Customer Group");
				Ajax()->RenderHtmlTemplate("CustomerGroupNew", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
				return true;
			}
			// DBO()->CustomerGroup->SetColumns("Id,internal_name,external_name,outbound_email");
			// The CustomerGroup is valid.  Save it
			if (!DBO()->CustomerGroup->Save()) {
				// The CustomerGroup could not be saved for some unforseen reason
				Ajax()->AddCommand("Alert", "ERROR: Saving the CustomerGroup failed, unexpectedly");
				return true;
			}

			// Create default customer_group_delivery_method entries
			$arrDeliveryMethods = Delivery_Method::getAll();
			foreach ($arrDeliveryMethods as $objDeliveryMethod) {
				$objCustomerGroupDeliveryMethod = new Customer_Group_Delivery_Method();

				$objCustomerGroupDeliveryMethod->customer_group_id = DBO()->CustomerGroup->Id->Value;
				$objCustomerGroupDeliveryMethod->delivery_method_id = $objDeliveryMethod->id;
				$objCustomerGroupDeliveryMethod->minimum_invoice_value = Customer_Group_Delivery_Method::DEFAULT_MINIMUM_INVOICE_VALUE;
				$objCustomerGroupDeliveryMethod->employee_id = Flex::getUserId();

				try {
					$objCustomerGroupDeliveryMethod->save();
				} catch (Exception $eException) {
					Ajax()->AddCommand("Alert", "ERROR: Saving the Customer Group Delivery Method details failed, unexpectedly");
					return true;
				}
			}

			// The CustomerGroup has now been saved
			Ajax()->AddCommand("AlertAndRelocate", array("Alert"=>"The new Customer Group has been successfully created", "Location"=>Href()->ViewAllCustomerGroups()));
			return true;
		}

		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->SetCurrentPage("New Customer Group");

		// Declare which Page Template to use
		$this->LoadPage('customer_group_add');

		return true;
	}

	function ChangeLogo() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);

		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->SetCurrentPage("Modify Customer Group");

		if(array_key_exists('CustomerGroup_Id', $_POST)) {
			if(!empty($_POST['CustomerGroup_Id'])) {
				$strFileName = $_FILES['userfile']['name'];
				$strTmpName = $_FILES['userfile']['tmp_name'];
				$strFileType = $_FILES['userfile']['type'];

				$resImage = fopen($strTmpName, 'r');
				$mixContent = fread($resImage, filesize($strTmpName));
				fclose($resImage);

				DBO()->CustomerGroup->Id = $_POST['CustomerGroup_Id'];
				DBO()->CustomerGroup->Load();
				DBO()->CustomerGroup->customer_logo = $mixContent;
				DBO()->CustomerGroup->customer_logo_type = $strFileType;
				DBO()->CustomerGroup->SetColumns("customer_logo,customer_logo_type");
				DBO()->CustomerGroup->Save();
			} else {
				// Group ID entered was empty.
				$this->LoadPage('customer_group_view');
			}
		}

		// Declare which Page Template to use
		$this->LoadPage('customer_group_change_logo');

		return true;
	}

	function ChangeAdvertisement() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);

		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->SetCurrentPage("Modify Customer Group");

		if(array_key_exists('CustomerGroup_Id', $_POST)) {
			if(!empty($_POST['CustomerGroup_Id'])) {
				if ((($_FILES["userfile"]["type"] == "image/gif") || ($_FILES["userfile"]["type"] == "image/jpeg") || ($_FILES["userfile"]["type"] == "image/png")) && ($_FILES["userfile"]["size"] < 9000000)) {
					if ($_FILES["userfile"]["error"] > 0) {
						DBO()->ChangeAdvertisement->Error =  "Error: " . $_FILES["userfile"]["error"] . "<br />";
					} else {
						$strFileName = $_FILES['userfile']['name'];
						$strTmpName = $_FILES['userfile']['tmp_name'];
						$strFileType = $_FILES['userfile']['type'];

						$resImage = fopen($strTmpName, 'r');
						$mixContent = fread($resImage, filesize($strTmpName));
						fclose($resImage);

						$dbConnection = GetDBConnection($GLOBALS['**arrDatabase']["flex"]['Type']);
						$dbConnection->execute("UPDATE CustomerGroup SET customer_advert_image=\"" . base64_encode(serialize($mixContent)) . "\",customer_advert_image_type=\"$strFileType\" WHERE Id=\"$_POST[CustomerGroup_Id]\"");
					}
				} else {
					DBO()->ChangeAdvertisement->Error = "Invalid file";
				}
			} else {
				// Group ID entered was empty.
				$this->LoadPage('customer_group_view');
			}
		}

		// Declare which Page Template to use
		$this->LoadPage('customer_group_change_advertisement');

		return true;
	}

	function CreditCardConfig() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);

		// Load the CustomerGroupDetails
		if (!DBO()->CustomerGroup->Load()) {
			DBO()->Error->Message = "The Customer Group with account id: ". DBO()->CustomerGroup->Id->value ." could not be found";
			$this->LoadPage('error');
			return false;
		}

		// Load the CustomerGroupDetails
		if (!Flex_Module::isActive(FLEX_MODULE_ONLINE_CREDIT_CARD_PAYMENTS)) {
			DBO()->Error->Message = "The Credit Card Payment module has not been enabled in Flex.";
			$this->LoadPage('error');
			return false;
		}

		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->internal_name->Value);
		BreadCrumb()->SetCurrentPage("Credit Card Config");

		// Declare which Page Template to use
		$this->LoadPage('customer_group_credit_card_config');

		return true;
	}

	function View() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);

		// Load the CustomerGroupDetails
		if (!DBO()->CustomerGroup->Load()) {
			DBO()->Error->Message = "The Customer Group with account id: ". DBO()->CustomerGroup->Id->value ." could not be found";
			$this->LoadPage('error');
			return false;
		}

		$intCustomerGroupId = DBO()->CustomerGroup->Id->Value;
		// Load the currently used DocumentTemplates
		$arrColumns = array(
			"TypeId" => "DTT.Id",
			"TypeName" => "DTT.Name",
			"TemplateId" => "DT.Id",
			"Version" => "DT.Version",
			"EffectiveOn" => "DT.EffectiveOn"
		);

		$strTables = "
			DocumentTemplateType AS DTT
			LEFT JOIN DocumentTemplate AS DT ON (
				DTT.Id = DT.TemplateType AND DT.CustomerGroup = {$intCustomerGroupId} AND DT.EffectiveOn <= NOW()
				AND DT.CreatedOn = (
					SELECT	MAX(CreatedOn)
					FROM	DocumentTemplate AS DT2
					WHERE	DT2.CustomerGroup = DT.CustomerGroup
							AND DT2.TemplateType = DTT.Id AND DT2.EffectiveOn <= NOW()
				)
			)
		";
		DBL()->DocumentTemplate->SetTable($strTables);
		DBL()->DocumentTemplate->SetColumns($arrColumns);
		DBL()->DocumentTemplate->OrderBy("TypeId ASC");
		DBL()->DocumentTemplate->Load();

		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->SetCurrentPage("Customer Group");

		// Declare which Page Template to use
		$this->LoadPage('customer_group_view');

		return true;
	}

	function RenderHtmlTemplateCustomerGroupDetails() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);

		// Load the CustomerGroup
		DBO()->CustomerGroup->Load();

		// Work out which context to render the HtmlTemplate in
		$intContext = HTML_CONTEXT_VIEW;
		if (DBO()->Context->View->Value) {
			$intContext = HTML_CONTEXT_VIEW;
		} elseif (DBO()->Context->Edit->Value) {
			$intContext = HTML_CONTEXT_EDIT;
		}

		// Render the CustomerGroupDetails HtmlTemplate for Viewing
		Ajax()->RenderHtmlTemplate("CustomerGroupDetails", $intContext, DBO()->Container->Id->Value);

		return true;
	}

	function SaveDetails() {
		// Check permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);

		// Validate the CustomerGroup
		if (DBO()->CustomerGroup->IsInvalid()) {
			// At least one Field is invalid
			Ajax()->AddCommand("Alert", "ERROR: Invalid fields are highlighted");
			Ajax()->RenderHtmlTemplate("CustomerGroupDetails", HTML_CONTEXT_EDIT, $this->_objAjax->strContainerDivId, $this->_objAjax);
			return true;
		}

		// Check that the CustomerGroup's internal_name is not being used by another CustomerGroup
		$selCustomerGroup = new StatementSelect("CustomerGroup", "Id", "internal_name LIKE <Name> AND Id != <Id>", "", "1");
		$intRecordFound = $selCustomerGroup->Execute(array(
			"Name" => DBO()->CustomerGroup->internal_name->Value,
			"Id" => DBO()->CustomerGroup->Id->Value
		));
		if ($intRecordFound) {
			// The CustomerGroup's new internal_name is already in use by another CustomerGroup
			DBO()->CustomerGroup->internal_name->SetToInvalid();
			Ajax()->AddCommand("Alert", "ERROR: This name is already in use by another Customer Group");
			Ajax()->RenderHtmlTemplate("CustomerGroupDetails", HTML_CONTEXT_EDIT, $this->_objAjax->strContainerDivId, $this->_objAjax);
			return true;
		}

		DBO()->CustomerGroup->cooling_off_period = (trim(DBO()->CustomerGroup->cooling_off_period->Value) == "") ? null : intval(DBO()->CustomerGroup->cooling_off_period->Value);

		// The ereg function has been DEPRECATED as of PHP 5.3.0 and REMOVED as of PHP 6.0.0.
		// DBO()->CustomerGroup->customer_primary_color = ereg_replace("[^a-zA-Z0-9]", "", DBO()->CustomerGroup->customer_primary_color->Value);
		// DBO()->CustomerGroup->customer_secondary_color = ereg_replace("[^a-zA-Z0-9]", "", DBO()->CustomerGroup->customer_secondary_color->Value);

		DBO()->CustomerGroup->customer_primary_color = preg_replace("/[^a-zA-Z0-9]/", "", DBO()->CustomerGroup->customer_primary_color->Value);
		DBO()->CustomerGroup->customer_secondary_color = preg_replace("/[^a-zA-Z0-9]/", "", DBO()->CustomerGroup->customer_secondary_color->Value);

		DBO()->CustomerGroup->SetColumns("Id,internal_name,external_name,outbound_email,flex_url,email_domain,customer_primary_color,customer_secondary_color,customer_exit_url,external_name_possessive,bill_pay_biller_code,abn,acn,business_phone,business_fax,business_web,business_contact_email,business_info_email,customer_service_phone,customer_service_email,customer_service_contact_name,business_payable_name,business_payable_address,credit_card_payment_phone,faults_phone,customer_advert_url,cooling_off_period");
		// The CustomerGroup is valid.  Save it
		if (!DBO()->CustomerGroup->Save()) {
			// The CustomerGroup could not be saved for some unforseen reason
			Ajax()->AddCommand("Alert", "ERROR: Saving changes to the CustomerGroup failed, unexpectedly");
			return true;
		}

		// If the sales module is active then update the sales database (the vendor table)
		if (Data_Source::dsnExists(FLEX_DATABASE_CONNECTION_SALES)) {
			try {
				Cli_App_Sales::pushAll();
			} catch (Exception $e) {
				// Pushing the data failed
				$strWarning = "Pushing the data from Flex to the Sales database, failed. Contact your system administrators to have them manually trigger the data push.  (Error message: ". htmlspecialchars($e->getMessage()) .")";
				Ajax()->AddCommand("Alert", $strWarning);
			}
		}

		// Fire the OnCustomerGroupDetailsUpdate Event
		$arrEvent['CustomerGroup']['Id'] = DBO()->CustomerGroup->Id->Value;
		Ajax()->FireEvent(EVENT_ON_CUSTOMER_GROUP_DETAILS_UPDATE, $arrEvent);
		return true;
	}

	function ViewDocumentTemplateHistory() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);
		$bolUserIsSuperAdmin = AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN);

		if (!DBO()->CustomerGroup->Load()) {
			DBO()->Error->Message = "The CustomerGroup with id: ". DBO()->CustomerGroup->Id->Value ." could not be found";
			$this->LoadPage('error');
			return true;
		}

		if (!DBO()->DocumentTemplateType->Load()) {
			DBO()->Error->Message = "The DocumentTemplateType with id: ". DBO()->DocumentTemplateType->Id->Value ." could not be found";
			$this->LoadPage('error');
			return true;
		}

		// Retrieve the Template history
		$arrColumns = array(
			"Id" => "DT.Id",
			"Version" => "DT.Version",
			"Description" => "DT.Description",
			"EffectiveOn" => "DT.EffectiveOn",
			"CreatedOn" => "DT.CreatedOn",
			"ModifiedOn" => "ModifiedOn",
			"LastUsedOn" => "LastUsedOn",
			"SchemaVersion" => "DS.Version",
			"Overridden" => "
				CASE WHEN (
					SELECT	COUNT(DT2.Id)
					FROM	DocumentTemplate AS DT2
					WHERE	DT2.CustomerGroup = DT.CustomerGroup
							AND DT2.TemplateType = DT.TemplateType
							AND DT2.CreatedOn > DT.CreatedOn
							AND DT2.EffectiveOn <= DT.EffectiveOn
				) > 0 THEN 1 ELSE 0 END
			"
		);
		$strTable = "DocumentTemplate AS DT INNER JOIN DocumentTemplateSchema AS DS ON DT.TemplateSchema = DS.Id";
		$strWhere = "DT.CustomerGroup = <CustomerGroup> AND DT.TemplateType = <TemplateType>";
		$arrWhere = array(
			"CustomerGroup" => DBO()->CustomerGroup->Id->Value,
			"TemplateType" => DBO()->DocumentTemplateType->Id->Value
		);
		DBL()->Templates->SetTable($strTable);
		DBL()->Templates->SetColumns($arrColumns);
		DBL()->Templates->Where->Set($strWhere, $arrWhere);
		DBL()->Templates->OrderBy("DT.Version DESC");
		DBL()->Templates->Load();

		if (DBL()->Templates->RecordCount() == 0) {
			// There aren't any templates for this CustomerGroup/TemplateType combination
			// Redirect the user to the AddNewTemplate page, if they have permission to add a new template
			if ($bolUserIsSuperAdmin) {
				return $this->BuildNewTemplate();
			} else {
				DBO()->Error->Message = "You do not have permission to build a new template";
				$this->LoadPage('error');
				return true;
			}
		}

		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->internal_name->Value);
		BreadCrumb()->SetCurrentPage("Template History");

		$this->LoadPage('document_template_history');
		return true;
	}

	function ViewEmailTemplateHistory() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);
		$bolUserIsSuperAdmin = AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN);

		if (!DBO()->CustomerGroup->Load()) {
			DBO()->Error->Message = "The CustomerGroup with id: ". DBO()->CustomerGroup->Id->Value ." could not be found";
			$this->LoadPage('error');
			return true;
		}

		/*
		if (!DBO()->DocumentTemplateType->Load()) {
			DBO()->Error->Message = "The DocumentTemplateType with id: ". DBO()->DocumentTemplateType->Id->Value ." could not be found";
			$this->LoadPage('error');
			return true;
		}
		*/

		// Retrieve the Template history
		/*
		$arrColumns = array(
			"Id" => "DT.Id",
			"Version" => "DT.Version",
			"Description" => "DT.Description",
			"EffectiveOn" => "DT.EffectiveOn",
			"CreatedOn" => "DT.CreatedOn",
			"ModifiedOn" => "ModifiedOn",
			"LastUsedOn" => "LastUsedOn",
			"SchemaVersion" => "DS.Version",
			"Overridden" => "
				CASE WHEN (
					SELECT	COUNT(DT2.Id)
					FROM	DocumentTemplate AS DT2
					WHERE	DT2.CustomerGroup = DT.CustomerGroup
							AND DT2.TemplateType = DT.TemplateType
							AND DT2.CreatedOn > DT.CreatedOn
							AND DT2.EffectiveOn <= DT.EffectiveOn
				) > 0 THEN 1 ELSE 0 END
			"
		);
		$strTable = "DocumentTemplate AS DT INNER JOIN DocumentTemplateSchema AS DS ON DT.TemplateSchema = DS.Id";
		$strWhere = "DT.CustomerGroup = <CustomerGroup> AND DT.TemplateType = <TemplateType>";
		$arrWhere = array(
			"CustomerGroup" => DBO()->CustomerGroup->Id->Value,
			"TemplateType" => DBO()->DocumentTemplateType->Id->Value
		);
		DBL()->Templates->SetTable($strTable);
		DBL()->Templates->SetColumns($arrColumns);
		DBL()->Templates->Where->Set($strWhere, $arrWhere);
		DBL()->Templates->OrderBy("DT.Version DESC");
		DBL()->Templates->Load();

		if (DBL()->Templates->RecordCount() == 0) {
			// There aren't any templates for this CustomerGroup/TemplateType combination
			// Redirect the user to the AddNewTemplate page, if they have permission to add a new template
			if ($bolUserIsSuperAdmin) {
				return $this->BuildNewTemplate();
			} else {
				DBO()->Error->Message = "You do not have permission to build a new template";
				$this->LoadPage('error');
				return true;
			}
		}*/

		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->internal_name->Value);
		BreadCrumb()->SetCurrentPage("Email Template History");

		$this->LoadPage('email_template_history');
		return true;
	}

	function BuildNewTemplate() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);

		if (!DBO()->CustomerGroup->Load()) {
			DBO()->Error->Message = "The CustomerGroup with id: ". DBO()->CustomerGroup->Id->Value ." could not be found";
			$this->LoadPage('error');
			return true;
		}

		if (DBO()->BaseTemplate->Id->IsSet) {
			// The new template will be based on an existing one
			DBO()->BaseTemplate->SetTable("DocumentTemplate");
			if (!DBO()->BaseTemplate->Load()) {
				// Could not load the template to base the new one on
				DBO()->Error->Message = "The DocumentTemplate with id: ". DBO()->BaseTemplate->Id->Value ." could not be found";
				$this->LoadPage('error');
				return true;
			}

			if (DBO()->BaseTemplate->CustomerGroup->Value != DBO()->CustomerGroup->Id->Value) {
				// The base template does not belong to the CustomerGroup
				DBO()->Error->Message = "The DocumentTemplate of which to base the new template on, is not owned by the ". DBO()->CustomerGroup->internal_name->Value ." customer group";
				$this->LoadPage('error');
				return true;
			}

			DBO()->DocumentTemplateType->Id = DBO()->BaseTemplate->TemplateType->Value;
		}

		if (!DBO()->DocumentTemplateType->Load()) {
			DBO()->Error->Message = "The DocumentTemplateType with id: ". DBO()->DocumentTemplateType->Id->Value ." could not be found";
			$this->LoadPage('error');
			return true;
		}

		// Load the most recent schema for this DocumentTemplateType
		if (false === ($arrSchema = $this->_GetCurrentSchema(DBO()->DocumentTemplateType->Id->Value))) {
			DBO()->Error->Message = "There is no Schema defined for Document Template ".DBO()->DocumentTemplateType->Name->Value." (#". DBO()->DocumentTemplateType->Id->Value .")";
			$this->LoadPage('error');
			return true;
		}
		DBO()->DocumentTemplateSchema->_arrProperties = $arrSchema;

		// If there is a draft template, then load it, and copy the contents of the BaseTemplate into it
		$arrDraft = $this->_GetDraftTemplate(DBO()->CustomerGroup->Id->Value, DBO()->DocumentTemplateType->Id->Value);
		DBO()->DocumentTemplate->CustomerGroup = DBO()->CustomerGroup->Id->Value;
		DBO()->DocumentTemplate->TemplateType = DBO()->DocumentTemplateType->Id->Value;
		DBO()->DocumentTemplate->Version = $this->_GetNextVersionForTemplate(DBO()->CustomerGroup->Id->Value, DBO()->DocumentTemplateType->Id->Value);
		DBO()->DocumentTemplate->TemplateSchema = DBO()->DocumentTemplateSchema->Id->Value;
		DBO()->DocumentTemplate->EffectiveOn = null;
		if (is_array($arrDraft)) {
			// There is a draft
			DBO()->DocumentTemplate->Id = $arrDraft['Id'];
			DBO()->DocumentTemplate->Source = $arrDraft['Source'];
			DBO()->DocumentTemplate->CreatedOn = $arrDraft['CreatedOn'];
			DBO()->DocumentTemplate->ModifiedOn = $arrDraft['ModifiedOn'];
		}

		// Set a default description
		DBO()->DocumentTemplate->Description = "Version ". DBO()->DocumentTemplate->Version->Value ." for ". DBO()->CustomerGroup->internal_name->Value ." ". DBO()->DocumentTemplateType->Name->Value ." Template";

		if (DBO()->BaseTemplate->Id->Value) {
			// Load the Template Source code from the BaseTemplate, into the new one
			DBO()->DocumentTemplate->Source = DBO()->BaseTemplate->Source->Value;
		}

		// Load all the DocumentResourceTypes
		DBL()->DocumentResourceType->OrderBy("PlaceHolder ASC");
		DBL()->DocumentResourceType->Load();

		//BreadCrumb Menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->internal_name->Value);
		BreadCrumb()->ViewDocumentTemplateHistory(DBO()->CustomerGroup->Id->Value, DBO()->DocumentTemplateType->Id->Value);
		BreadCrumb()->SetCurrentPage("Template");

		DBO()->Render->Context = HTML_CONTEXT_NEW;

		$this->LoadPage('document_template');
		return true;
	}

	function EditTemplate() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);

		if (!$this->_LoadTemplate(DBO()->DocumentTemplate->Id->Value, true)) {
			// The template could not be loaded
			DBO()->Error->Message = "The DocumentTemplate with id: ". DBO()->DocumentTemplate->Id->Value ." could not be found";
			$this->LoadPage('error');
			return true;
		}

		// Load all the DocumentResourceTypes
		DBL()->DocumentResourceType->OrderBy("PlaceHolder ASC");
		DBL()->DocumentResourceType->Load();

		//BreadCrumb Menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->internal_name->Value);
		BreadCrumb()->ViewDocumentTemplateHistory(DBO()->CustomerGroup->Id->Value, DBO()->DocumentTemplate->TemplateType->Value);
		BreadCrumb()->SetCurrentPage("Template");

		DBO()->Render->Context = HTML_CONTEXT_EDIT;

		$this->LoadPage('document_template');
		return true;
	}

	function ViewTemplate() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);

		if (!$this->_LoadTemplate(DBO()->DocumentTemplate->Id->Value, false)) {
			// The template could not be loaded
			DBO()->Error->Message = "The DocumentTemplate with id: ". DBO()->DocumentTemplate->Id->Value ." could not be found";
			$this->LoadPage('error');
			return true;
		}

		//BreadCrumb Menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->internal_name->Value);
		BreadCrumb()->ViewDocumentTemplateHistory(DBO()->CustomerGroup->Id->Value, DBO()->DocumentTemplate->TemplateType->Value);
		BreadCrumb()->SetCurrentPage("Template");

		DBO()->Render->Context = HTML_CONTEXT_VIEW;

		$this->LoadPage('document_template');
		return true;
	}

	function SaveTemplate() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);

		$strNow = GetCurrentDateAndTimeForMySQL();

		if (DBO()->Template->Id->Value != null) {
			// The user is saving changes made to an existing template
			$selCurrentTemplate = new StatementSelect("DocumentTemplate", "*", "Id = <Id>");
			if (!$selCurrentTemplate->Execute(array("Id" => DBO()->Template->Id->Value))) {
				Ajax()->AddCommand("Alert", "ERROR: Could not retrieve the DocumentTemplate with id: ". DBO()->Template->Id->Value ." and Version: ". DBO()->Template->Version->Value .". Please notify your system administrator.");
				return true;
			}
			$arrCurrentTemplate = $selCurrentTemplate->Fetch();

			// If the Template's EffectiveOn date is set and in the past then they cannot update it
			if ($arrCurrentTemplate['EffectiveOn'] != null && $arrCurrentTemplate['EffectiveOn'] <= $strNow) {
				Ajax()->AddCommand("Alert", "ERROR: This template has already come into effect, and can therefore not be modified");
				return true;
			}

			// Copy over values that should not have changed
			DBO()->Template->CreatedOn = $arrCurrentTemplate["CreatedOn"];
		} else {
			// The template is completely new
			// If there is a draft template, then copy over this one
			$arrDraft = $this->_GetDraftTemplate(DBO()->Template->CustomerGroup->Value, DBO()->Template->TemplateType->Value);
			if (is_array($arrDraft)) {
				DBO()->Template->Id = $arrDraft['Id'];
			}

			// Set the Version to the next available version
			DBO()->Template->Version = $this->_GetNextVersionForTemplate(DBO()->Template->CustomerGroup->Value, DBO()->Template->TemplateType->Value);

			DBO()->Template->CreatedOn = $strNow;
		}

		// Build a default description if one has not been supplied
		if (!Validate("IsNotEmptyString", DBO()->Template->Description->Value)) {
			DBO()->CustomerGroup->Id = DBO()->Template->CustomerGroup->Value;
			DBO()->CustomerGroup->Load();
			DBO()->DocumentTemplateType->Id = DBO()->Template->TemplateType->Value;
			DBO()->DocumentTemplateType->Load();
			DBO()->Template->Description = "Version ". DBO()->Template->Version->Value ." for ". DBO()->CustomerGroup->internal_name->Value ." ". DBO()->DocumentTemplateType->Name->Value;
		}

		switch (DBO()->Template->EffectiveOnType->Value) {
			case "immediately":
				DBO()->Template->EffectiveOn = $strNow;
				break;

			case "date":
				// Validate the date and check that it is in the future
				if (!Validate("ShortDate", DBO()->Template->EffectiveOn->Value)) {
					// The EffectiveOn date is invalid
					Ajax()->AddCommand("Alert", "ERROR: Invalid 'Effective On' date.<br />It must be in the format dd/mm/yyyy and in the future");
					return true;
				}

				// Convert the date into the YYYY-MM-DD format
				DBO()->Template->EffectiveOn = ConvertUserDateToMySqlDate(DBO()->Template->EffectiveOn->Value);

				// Check that the Date is in the future
				if ($strNow > DBO()->Template->EffectiveOn->Value) {
					// The EffectiveOn date is in the past (or today, which is considered in the past)
					Ajax()->AddCommand("Alert", "ERROR: Invalid 'Effective On' date.  It must be in the future");
					return true;
				}
				break;

			case "undeclared":
			default:
				// Check that there isn't a current value for EffectiveOn
				if (isset($arrCurrentTemplate) && $arrCurrentTemplate['EffectiveOn'] != null) {
					Ajax()->AddCommand("Alert", "ERROR: 'Effective On' date has already been set and can not be set back to 'undeclared'");
					return true;
				}

				DBO()->Template->EffectiveOn = null;
		}

		DBO()->Template->ModifiedOn = $strNow;

		// Save the record
		DBO()->Template->SetTable("DocumentTemplate");
		if (!DBO()->Template->Save()) {
			// Saving the template failed
			Ajax()->AddCommand("Alert", "ERROR: Saving the template failed, unexpectedly.  Please notify your system administrator");
			return true;
		}

		// The Template was successfully saved

		// If the EffectiveOn Date is set.  Check if the Template will be totally overridden by a newer one
		if (DBO()->Template->EffectiveOn->Value != null) {
			$strWhere = "CustomerGroup = <CustomerGroup> AND TemplateType = <TemplateType> AND Id != <TemplateId> AND CreatedOn > <CreatedOn> AND EffectiveOn IS NOT NULL AND EffectiveOn <= <EffectiveOn>";
			$arrWhere = array(
				"CustomerGroup" => DBO()->Template->CustomerGroup->Value,
				"TemplateType" => DBO()->Template->TemplateType->Value,
				"TemplateId" => DBO()->Template->Id->Value,
				"CreatedOn" => DBO()->Template->CreatedOn->Value,
				"EffectiveOn" => DBO()->Template->EffectiveOn->Value
			);
			$selOverridingTemplates = new StatementSelect("DocumentTemplate", "Id", $strWhere);
			$intRecCount = $selOverridingTemplates->Execute($arrWhere);
			if ($intRecCount > 0) {
				// The template is completely overridden and will never be used unless the EffectiveOn date is changed
				$strHref = Href()->ViewDocumentTemplateHistory(DBO()->Template->CustomerGroup->Value, DBO()->Template->TemplateType->Value);
				$arrData = array(
					"Alert" => "The Template has been successfully saved however, with its current EffectiveOn date, it will never be used as it is currently overridden by another template which was created more recently, and has an ealier EffectiveOn date",
					"Location" => $strHref
				);
				Ajax()->AddCommand("AlertAndRelocate", $arrData);
				return true;
			}
		}
		if (DBO()->Template->EffectiveOn->Value == $strNow) {
			// The template can no longer be editted.  Relocate the user to the history page
			$strHref = Href()->ViewDocumentTemplateHistory(DBO()->Template->CustomerGroup->Value, DBO()->Template->TemplateType->Value);
			$arrData = array(
				"Alert" => "The Template has been successfully saved and comes into effect immediately",
				"Location" => $strHref
			);
			Ajax()->AddCommand("AlertAndRelocate", $arrData);
			return true;
		}

		$arrReply["Template"] = DBO()->Template->_arrProperties;
		// Don't send back the template's source code
		unset($arrReply['Template']['Source']);
		$arrReply["Success"] = true;

		AjaxReply($arrReply);
		return true;
	}

	function ViewDocumentResources() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);

		if (!DBO()->CustomerGroup->Load()) {
			DBO()->Error->Message = "The CustomerGroup with id: ". DBO()->CustomerGroup->Id->Value ." could not be found";
			$this->LoadPage('error');
			return true;
		}

		// Define all the objects required to retrieve the DocumentResourceType information from the database
		$selResourceType = new StatementSelect("DocumentResourceType", "*", "", "PlaceHolder");
		if ($selResourceType->Execute() === false) {
			DBO()->Error->Message = "Could not load all data required to describe the Document Resource Types.  Please notify your system administrator";
			$this->LoadPage('error');
			return true;
		}

		// This array has to be wrapped in the DBO() so that they are accessable within the HtmlTemplates
		DBO()->DocumentResourceTypes->Asarray = $selResourceType->FetchAll();

		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->ViewAllCustomerGroups();
		BreadCrumb()->ViewCustomerGroup(DBO()->CustomerGroup->Id->Value, DBO()->CustomerGroup->internal_name->Value);
		BreadCrumb()->SetCurrentPage("Document Resources");

		// Load the page template
		$this->LoadPage('document_resource_management');
		return true;
	}

	function GetDocumentResourceHistory() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);

		$intResourceType = DBO()->History->ResourceType->Value;
		$intCustomerGroup = DBO()->History->CustomerGroup->Value;

		DBO()->DocumentResourceType->Id = $intResourceType;
		DBO()->DocumentResourceType->Load();

		DBO()->CustomerGroup->Id = $intCustomerGroup;
		DBO()->CustomerGroup->Load();

		// Retrieve the DocumentResources
		$selResources = new StatementSelect("DocumentResource", "*", "CustomerGroup = <CustomerGroup> AND Type = <ResourceType>", "CreatedOn DESC, StartDatetime DESC");
		if ($selResources->Execute(array("CustomerGroup" => $intCustomerGroup, "ResourceType" => $intResourceType)) === false) {
			Ajax()->AddCommand("Alert", "ERROR: Retrieving the Document Resource History failed, unexpectedly.  Please notify your system administrator");
			return true;
		}
		$arrHistory = $selResources->FetchAll();

		$objHistoryTableGenerator = new HtmlTemplateDocumentResourceManagement(null, null);
		//AjaxReply($objHistoryTableGenerator->GetHistory(DBO()->DocumentResourceType->PlaceHolder->Value, $arrHistory));
		echo $objHistoryTableGenerator->GetHistory($arrHistory);
		return true;
	}

	// Displays the popup for adding a new resource
	function AddDocumentResource() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);

		if (!DBO()->CustomerGroup->Load()) {
			Ajax()->AddCommand("Alert", "ERROR: Could not load the customer group.  Please notify your system administrator");
			return true;
		}

		if (!DBO()->DocumentResourceType->Load()) {
			Ajax()->AddCommand("Alert", "ERROR: Could not load the DocumentResourceType.  Please notify your system administrator");
			return true;
		}

		if (!AuthenticatedUser()->UserHasPerm(DBO()->DocumentResourceType->PermissionRequired->Value)) {
			Ajax()->AddCommand("Alert", "ERROR: The user does not have permission to upload resources of this type");
			return true;
		}

		// Load the page template
		$this->LoadPage('document_resource_add');
		return true;
	}

	function ViewDocumentResource() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);

		$intResourceId = DBO()->DocumentResource->Id->Value;
		$bolDownloadResource = DBO()->DocumentResource->DownloadFile->Value;

		$arrColumns = array(
			"ResourceId" => "DR.Id",
			"CustomerGroup" => "DR.CustomerGroup",
			"OriginalFilename" => "DR.OriginalFilename",
			"FileContent" => "DR.FileContent",
			"MIMEType" => "FT.MIMEType"
		);
		$strFrom = "DocumentResource AS DR INNER JOIN FileType AS FT ON DR.FileType = FT.Id";
		$selResource = new StatementSelect($strFrom, $arrColumns, "DR.Id = <Id>");
		$mixResult = $selResource->Execute(array("Id" => $intResourceId));

		if ($mixResult === false) {
			DBO()->Error->Message = "An unexpected error occurred when trying to retrieve the Resource from the database.  Please notify your system administrator";
			$this->LoadPage('error');
			return true;
		} elseif ($mixResult == 0) {
			DBO()->Error->Message = "The resource with Id: {$intResourceId}, could not be found in the database";
			$this->LoadPage('error');
			return true;
		}

		$arrResource = $selResource->Fetch();

		// Send the file to the user
		header("Content-Type: {$arrResource['MIMEType']}");
		if ($bolDownloadResource) {
			// This will prompt the user to save the file, with its original filename
			header("Content-Disposition: attachment; filename=\"{$arrResource['OriginalFilename']}\"");
		}
		echo $arrResource['FileContent'];
		exit;
	}

	function UploadDocumentResource() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);

		// Retrieve the FileTypes that this resource can be
		$intResourceType = DBO()->DocumentResource->Type->Value;

		$strWhere = "Id IN (SELECT file_type_id FROM document_resource_type_file_type WHERE document_resource_type_id = {$intResourceType})";
		$selFileTypes = new StatementSelect("FileType", "*", $strWhere);
		$selFileTypes->Execute();
		$arrFileTypes = $selFileTypes->FetchAll();
		DBO()->FileTypes->Asarray = $arrFileTypes;

		// Check if the form has been submitted
		if (SubmittedForm("ImportResource")) {
			$intCustomerGroup = DBO()->DocumentResource->CustomerGroup->Value;
			$intResourceType = DBO()->DocumentResource->Type->Value;
			$mixStart = DBO()->DocumentResource->Start->Value;
			$mixEnd = DBO()->DocumentResource->End->Value;

			$mixResult = $this->_UploadDocumentResource($arrFileTypes, $intResourceType, $intCustomerGroup, $mixStart, $mixEnd);

			if ($mixResult === true) {
				// The file was successfully uploaded
				DBO()->Import->Success = true;
			} else {
				// The import was unsuccessful
				DBO()->Import->Success = false;
				DBO()->Import->ErrorMsg = $mixResult;
			}
		}

		// Load the page template
		$this->LoadPage('document_resource_upload_component');
		return true;
	}

	function ViewSamplePDF() {
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
		return true;
	}

	function BuildSamplePDFOldMethodUsingFormSubmittion()
	{
		if(is_array($_POST)) {
			foreach($_POST AS $strName=>$strValue) {
				// parse variable
				echo "<br />POST variable '{$strName}' is ". strlen($strValue) ." chars long, assuming it's a string\n";
			}
		}

		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);

		if (!DBO()->CustomerGroup->Load()) {
			echo "ERROR: Could not load the CustomerGroup record";
			exit;
		}

		if (!DBO()->DocumentTemplateType->Load()) {
			echo "ERROR: Could not load the DocumentTemplateType record";
			exit;
		}

		// Validate the Date and Time parameters
		if (!Validate("ShortDate", DBO()->Generation->Date->Value)) {
			echo "ERROR: The Generation Date is invalid.  It must be in the format DD/MM/YYYY";
			exit;
		}

		if (!Validate("Time", DBO()->Generation->Time->Value)) {
			echo "ERROR: The Generation Time is invalid.  It must be in the format HH:MM:SS";
			exit;
		}
		$strGenerationDate = ConvertUserDateToMySqlDate(DBO()->Generation->Date->Value) ." ". DBO()->Generation->Time->Value;

		// Check if the template's source code hasn't been supplied
		if (!DBO()->Template->Source->IsSet) {
			// Find the right template to use
			$strWhere = "TemplateType = <TemplateType> AND CustomerGroup = <CustomerGroup> AND EffectiveOn <= <GenerationDate>";
			$arrWhere = array(
				"TemplateType" => DBO()->DocumentTemplateType->Id->Value,
				"CustomerGroup" => DBO()->CustomerGroup->Id->Value,
				"EffectiveOn" => $strGenerationDate
			);
			$selTemplate = new StatementSelect("DocumentTemplate", "Source, TemplateSchema", $strWhere, "CreatedOn DESC", "1");
			$mixResult = $selTemplate->Execute($arrWhere);
			if ($mixResult === false) {
				echo "ERROR: Retrieving the approriate DocumentTemplate from the database failed, unexpectedly.  Please notify your system administrator";
				exit;
			}
			if ($mixResult == 0) {
				echo "ERROR: Could not find an appropriate DocumentTemplate for CustomerGroup: ". DBO()->CustomerGroup->internal_name->Value .", TemplateType: ". DBO()->DocumentTemplateType->Name->Value .", for generation on {$strGenerationDate}";
				exit;
			}

			$arrTemplate = $selTemplate->Fetch();
			DBO()->Template->Source = $arrTemplate['Source'];
			DBO()->Schema->Id = $arrTemplate['TemplateSchema'];
		}

		// Load the Schema
		DBO()->Schema->SetTable("DocumentTemplateSchema");
		if (!DBO()->Schema->Load()) {
			echo "ERROR: Could not load DocumentTemplateSchema record";
			exit;
		}

		$strDate = ConvertUserDateToMySqlDate(DBO()->Generation->Date->Value);
		$strEffectiveDate = $strDate ." ". DBO()->Generation->Time->Value;
		$strTemplateXSLT = DBO()->Template->Source->Value;
		$intCustomerGroup = DBO()->CustomerGroup->Id->Value;
		$strSampleXML = DBO()->Schema->Sample->Value;

		VixenRequire("lib/pdf/Flex_Pdf_Template.php");

		//set_time_limit(120);

		try {
			$objPDFTemplate = new Flex_Pdf_Template($intCustomerGroup, $strEffectiveDate, $strTemplateXSLT, $strSampleXML, Flex_Pdf_Style::MEDIA_ALL, true);
			$objPDFDocument = $objPDFTemplate->createDocument();

			ob_start();
			echo $objPDFDocument->render();
			$strPdf = ob_get_clean();
		} catch (Exception $objException) {
			// Turn output buffering off, if it was on
			ob_get_clean();
			echo "ERROR: PDF generation failed<br /><br />". $objException->getMessage();
			exit;
		}

		// The pdf was successfully created
		$strFilename = "Sample_". str_replace(" ", "_", DBO()->CustomerGroup->internal_name->Value) ."_". str_replace(" ", "_", DBO()->DocumentTemplateType->Name->Value) ."_". str_replace("-", "_", $strDate) ."_". str_replace(":", "_", DBO()->Generation->Time->Value) .".pdf";
		header("Content-type: application/pdf;");
		header("Content-Disposition: attachment; filename=\"{$strFilename}\"");
		echo $strPdf;
		exit;
	}

	function BuildSamplePDF()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_CUSTOMER_GROUP_ADMIN);

		$_SESSION['DocumentTemplateSamplePDF'] = "";

		if (!DBO()->CustomerGroup->Load()) {
			Ajax()->AddCommand("Alert", "ERROR: Could not load the CustomerGroup record");
			return true;
		}

		if (!DBO()->DocumentTemplateType->Load()) {
			Ajax()->AddCommand("Alert", "ERROR: Could not load the DocumentTemplateType record");
			return true;
		}

		// Validate the Date and Time parameters
		if (!Validate("ShortDate", DBO()->Generation->Date->Value)) {
			Ajax()->AddCommand("Alert", "ERROR: The Generation Date is invalid.  It must be in the format DD/MM/YYYY");
			return true;
		}

		if (!Validate("Time", DBO()->Generation->Time->Value)) {
			Ajax()->AddCommand("Alert", "ERROR: The Generation Time is invalid.  It must be in the format HH:MM:SS");
			return true;
		}
		$strGenerationDate = ConvertUserDateToMySqlDate(DBO()->Generation->Date->Value) ." ". DBO()->Generation->Time->Value;

		// Check if the template's source code hasn't been supplied
		if (!DBO()->Template->Source->IsSet) {
			// Find the right template to use
			$strWhere = "TemplateType = <TemplateType> AND CustomerGroup = <CustomerGroup> AND EffectiveOn <= <GenerationDate>";
			$arrWhere = array(
				"TemplateType" => DBO()->DocumentTemplateType->Id->Value,
				"CustomerGroup" => DBO()->CustomerGroup->Id->Value,
				"EffectiveOn" => $strGenerationDate
			);
			$selTemplate = new StatementSelect("DocumentTemplate", "Source, TemplateSchema", $strWhere, "CreatedOn DESC", "1");
			$mixResult = $selTemplate->Execute($arrWhere);
			if ($mixResult === false) {
				Ajax()->AddCommand("Alert", "ERROR: Retrieving the approriate DocumentTemplate from the database failed, unexpectedly.  Please notify your system administrator");
				return true;
			}
			if ($mixResult == 0) {
				Ajax()->AddCommand("Alert", "ERROR: Could not find an appropriate DocumentTemplate for CustomerGroup: ". DBO()->CustomerGroup->internal_name->Value .", TemplateType: ". DBO()->DocumentTemplateType->Name->Value .", for generation on {$strGenerationDate}");
				return true;
			}

			$arrTemplate = $selTemplate->Fetch();
			DBO()->Template->Source = $arrTemplate['Source'];
			DBO()->Schema->Id = $arrTemplate['TemplateSchema'];
		}

		// Load the Schema
		DBO()->Schema->SetTable("DocumentTemplateSchema");
		if (!DBO()->Schema->Load()) {
			Ajax()->AddCommand("Alert", "ERROR: Could not load DocumentTemplateSchema record");
			return true;
		}

		$strDate = ConvertUserDateToMySqlDate(DBO()->Generation->Date->Value);
		$intMediaType = DBO()->Generation->MediaType->Value;
		$strEffectiveDate = $strDate ." ". DBO()->Generation->Time->Value;
		$strTemplateXSLT = DBO()->Template->Source->Value;
		$intCustomerGroup = DBO()->CustomerGroup->Id->Value;
		$strSampleXML = DBO()->Schema->Sample->Value;

		VixenRequire("lib/pdf/Flex_Pdf_Template.php");

		//set_time_limit(120);

		try {
			$objPDFTemplate = new Flex_Pdf_Template($intCustomerGroup, $strEffectiveDate, $strTemplateXSLT, $strSampleXML, $intMediaType, true);
			$objPDFDocument = $objPDFTemplate->createDocument();

			ob_start();
			echo $objPDFDocument->render();
			$strPdf = ob_get_clean();
		} catch (Exception $objException) {
			// Turn output buffering off, if it was on
			ob_get_clean();
			Ajax()->AddCommand("Alert", "ERROR: PDF generation failed<br /><br />". $objException->getMessage());
			return true;
		}

		// The pdf was successfully built
		// Save it to the session, so that we can both report on the process, and let the user retrieve it as a file
		$strFilename = "Sample_". str_replace(" ", "_", DBO()->CustomerGroup->internal_name->Value) ."_". str_replace(" ", "_", DBO()->DocumentTemplateType->Name->Value) ."_". str_replace("-", "_", $strDate) ."_". str_replace(":", "_", DBO()->Generation->Time->Value) .".pdf";
		$_SESSION['DocumentTemplateSamplePdf'] = $strPdf;
		$_SESSION['DocumentTemplateSamplePdfFilename'] = $strFilename;

		$arrReply = array('Success' => true);
		AjaxReply($arrReply);
		return true;
	}

	function GetSamplePDF() {
		$strPdf = $_SESSION['DocumentTemplateSamplePdf'];
		$strFilename = $_SESSION['DocumentTemplateSamplePdfFilename'];

		// Remove the pdf from the session array
		unset($_SESSION['DocumentTemplateSamplePdf']);
		unset($_SESSION['DocumentTemplateSamplePdfFilename']);

		if ($strPdf == "") {
			DBO()->Error->Message = "ERROR: Could not find the pdf requested";
			$this->LoadPage('error');
			return true;
		}

		header("Content-type: application/pdf;");
		header("Content-Disposition: attachment; filename=\"{$strFilename}\"");
		echo $strPdf;
		exit;
	}

	// Returns true on success, or an error msg on failure
	private function _UploadDocumentResource($arrFileTypes, $intResourceType, $intCustomerGroup, $mixStart, $mixEnd) {
		$strFilename = $_FILES['ResourceFile']['name'];
		$strTempFilename = $_FILES['ResourceFile']['tmp_name'];
		$intFileStatus = $_FILES['ResourceFile']['error'];
		$strFileType = $_FILES['ResourceFile']['type'];
		$intFileSize = $_FILES['ResourceFile']['size'];

		$arrFilenameParts = explode(".", $strFilename);
		$strExtension = strtolower($arrFilenameParts[count($arrFilenameParts)-1]);

		// Load the DocumentResourceType record
		$selResourceType = new StatementSelect("DocumentResourceType", "*", "Id = <Id>");
		if (!$selResourceType->Execute(array("Id" => $intResourceType))) {
			return "ERROR: Could not find the DocumentResourceType with Id: {$intResourceType}";
		}
		$arrResourceType = $selResourceType->Fetch();

		// Load the CustomerGroup record
		$selCustomerGroup = new StatementSelect("CustomerGroup", "*", "Id = <Id>");
		if (!$selCustomerGroup->Execute(array("Id" => $intCustomerGroup))) {
			return "ERROR: Could not find the CustomerGroup with Id: {$intCustomerGroup}";
		}
		$arrCustomerGroup = $selCustomerGroup->Fetch();

		// Check that the user has the required permissions to added resources of this type
		if (!AuthenticatedUser()->UserHasPerm($arrResourceType['PermissionRequired'])) {
			return "ERROR: You do not have the required permissions to add '{$arrResourceType['PlaceHolder']}' resources";
		}

		// Check that the file was successfully uploaded
		if ($intFileStatus != UPLOAD_ERR_OK) {
			// The file was not uploaded properly
			$strErrorMsg = GetConstantDescription($intFileStatus, "HTTPUploadStatus");
			if ($strErrorMsg === false) {
				// The error code is unknown
				$strErrorMsg = "The file failed to upload, for an undetermined reason";
			}
			$strErrorMsg = "ERROR: $strErrorMsg";
			return $strErrorMsg;
		}
		// Check that something was actually uploaded
		if ($intFileSize == 0) {
			return "ERROR: File is empty";
		}


		// Check that the file is of an appropriate type
		$bolFoundFileType = false;
		foreach ($arrFileTypes as $arrFileType) {
			if ($strExtension == strtolower($arrFileType['Extension'])) {
				// Check that their MIME Type matches
				if ($strFileType == $arrFileType['MIMEType']) {
					$intFileTypeId = $arrFileType['Id'];
					$bolFoundFileType = true;
					break;
				}
			}
		}

		if (!$bolFoundFileType) {
			// The file is not of an appropriate type
			return "ERROR: The file is not of an appropriate type, for the '{$arrResourceType['PlaceHolder']}' Resource"
					. (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD) ? "(Extension: '{$strExtension}'; MIME: '{$strFileType}')" : '');
		}

		// Validate the Start time and End time
		$strNow = GetCurrentDateAndTimeForMySQL();
		if ($mixStart == 0) {
			// The resource will start immediately
			$strStartDatetime = $strNow;
		} else {
			if (!Validate("ShortDate", $mixStart)) {
				return "ERROR: The Starting date is invalid.  It must be in the format dd/mm/yyyy";
			}

			// Check that the StartDate is greater than today
			$strStartDatetime = ConvertUserDateToMySqlDate($mixStart) . " 00:00:00";
			if ($strNow >= $strStartDatetime) {
				return "ERROR: The Starting date is invalid.  It must be in the future";
			}
		}

		if ($mixEnd == 0) {
			// The resource will be used indefinitely
			$strEndDatetime = END_OF_TIME;
		} else {
			if (!Validate("ShortDate", $mixEnd)) {
				return "ERROR: The Ending date is invalid.  It must be in the format dd/mm/yyyy";
			}

			// Check that the EndDate is greater than the StartDate
			$strEndDatetime = ConvertUserDateToMySqlDate($mixEnd) . " 23:59:59";
			if ($strStartDatetime > $strEndDatetime) {
				return "ERROR: The Ending date is invalid.  It must be greater than the Starting date";
			}
		}

		$blobFileContent = @file_get_contents($strTempFilename);
		if ($blobFileContent === false) {
			return "ERROR: Could not read the file ({$php_errormsg})";
		}
		
		// Add the DocumentResource Record
		TransactionStart();
		$arrResource = array(
			"CustomerGroup" => $intCustomerGroup,
			"Type" => $intResourceType,
			"FileType" => $intFileTypeId,
			"StartDatetime" => $strStartDatetime,
			"EndDatetime" => $strEndDatetime,
			"CreatedOn" => $strNow,
			"OriginalFilename" => $strFilename,
			"FileContent" => $blobFileContent
		);
		$insResource = new StatementInsert("DocumentResource", $arrResource);
		$intResourceId = $insResource->Execute($arrResource);

		if (!$intResourceId) {
			// Inserting the DocumentResource record failed
			TransactionRollback();
			return "ERROR: Adding the Resource to the database failed, unexpectedly.  Please notify your system administrator";
		}
/*
		// Move the file to {SHARED_BASE_PATH}/template/resource/{CustomerGroupId}/{ResourceId}.Extension
		$strNewFilename = "{$intResourceId}.{$strExtension}";
		$strPath = SHARED_BASE_PATH . "/template/resource/$intCustomerGroup";

		// Make the directory if it doesn't already exist
		if (!RecursiveMkdir($strPath)) {
			TransactionRollback();
			return "ERROR: Creating the directory structure, for resources belong to this customer group, failed unexpectedly.  Please notify your system administrator";
		}
		$strDestination = $strPath . "/". $strNewFilename;

		if (!move_uploaded_file($strTempFilename, $strDestination)) {
			TransactionRollback();
			return "ERROR: Moving the file to its destination failed, unexpectedly.  Please notify your system administrator";
		}
*/
		// Everything worked
		TransactionCommit();
		return true;
	}

	// Returns the record (associative array) of the current document template schema for the specified TemplateType
	// Returns false on error
	private function _GetCurrentSchema($intTemplateType) {
		$selSchema = new StatementSelect(
			"DocumentTemplateSchema",
			"*",
			"Id = (SELECT MAX(Id) FROM DocumentTemplateSchema WHERE TemplateType = <TemplateType>)"
		);
		if (!$selSchema->Execute(array("TemplateType" => $intTemplateType))) {
			return false;
		}

		return $selSchema->Fetch();
	}

	// Returns the record (associative array) of the draft template or returns NULL if there is no draft template
	// Returns false on error
	private function _GetDraftTemplate($intCustomerGroup, $intTemplateType) {
		$selTemplate = new StatementSelect("DocumentTemplate", "*", "CustomerGroup = <CustomerGroup> AND TemplateType = <TemplateType> AND EffectiveOn IS NULL", "CreatedOn DESC", "1");
		$mixResult = $selTemplate->Execute(array("CustomerGroup" => $intCustomerGroup, "TemplateType" => $intTemplateType));

		if ($mixResult === false) {
			return false;
		} elseif ($mixResult == 1) {
			return $selTemplate->Fetch();
		}
		return null;
	}

	// Returns the next version number to use.
	// If there is a draft DocumentTemplate for this particular CustomerGroup/TemplateType, then it will
	// return the draft's assigned version
	private function _GetNextVersionForTemplate($intCustomerGroup, $intTemplateType) {
		$arrColumns = array(
			"NextVersion" => "
				CASE
					WHEN (
						SELECT	MAX(Version)
						FROM	DocumentTemplate
						WHERE	CustomerGroup = {$intCustomerGroup}
								AND TemplateType = {$intTemplateType}
					) IS NULL THEN 1
					WHEN (
						SELECT	MAX(Version)
						FROM	DocumentTemplate
						WHERE	CustomerGroup = {$intCustomerGroup}
								AND TemplateType = {$intTemplateType} AND EffectiveOn IS NULL
					) IS NULL THEN MAX(Version) + 1
					ELSE MAX(Version)
				END
			"
		);
		$strWhere = "CustomerGroup = {$intCustomerGroup} AND TemplateType = {$intTemplateType}";
		$selVersion = new StatementSelect("DocumentTemplate", $arrColumns, $strWhere);
		$selVersion->Execute();
		$arrRecord = $selVersion->Fetch();
		return $arrRecord['NextVersion'];
	}

	private function _LoadTemplate($intId, $bolUseCurrentSchema=false) {
		DBO()->DocumentTemplate->Id = $intId;
		if (!DBO()->DocumentTemplate->Load()) {
			// The template could not be loaded
			return false;
		}

		// Load the CustomerGroup record as it is needed for the breadcrumb menu
		DBO()->CustomerGroup->Id = DBO()->DocumentTemplate->CustomerGroup->Value;
		DBO()->CustomerGroup->Load();

		// Load the details of the DocumentTemplateType as this is also needed
		DBO()->DocumentTemplateType->Id = DBO()->DocumentTemplate->TemplateType->Value;
		DBO()->DocumentTemplateType->Load();

		// Load the schema
		if ($bolUseCurrentSchema) {
			// Load the most recent schema for this DocumentTemplateType
			$arrSchema = $this->_GetCurrentSchema(DBO()->DocumentTemplate->TemplateType->Value);
			DBO()->DocumentTemplateSchema->_arrProperties = $arrSchema;
		} else {
			// Load the schema that is actually used by this Template regardless of whether or not it is the most recent
			DBO()->DocumentTemplateSchema->Id = DBO()->DocumentTemplate->TemplateSchema->Value;
			DBO()->DocumentTemplateSchema->Load();
		}

		return true;
	}


    //----- DO NOT REMOVE -----//
}