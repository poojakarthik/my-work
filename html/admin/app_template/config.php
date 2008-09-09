<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// config
//----------------------------------------------------------------------------//
/**
 * config
 *
 * contains all ApplicationTemplate extended classes relating to vixen config functionality
 *
 * contains all ApplicationTemplate extended classes relating to vixen config functionality
 *
 * @file		config.php
 * @language	PHP
 * @package		framework
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateConfig
//----------------------------------------------------------------------------//
/**
 * AppTemplateConfig
 *
 * The AppTemplateConfig class
 *
 * The AppTemplateConfig class.  This incorporates all logic for all pages
 * relating to vixen config (dynamic customer constants, and stuff)
 *
 *
 * @package	ui_app
 * @class	AppTemplateConfig
 * @extends	ApplicationTemplate
 */
class AppTemplateConfig extends ApplicationTemplate
{
	//------------------------------------------------------------------------//
	// ManageConstants
	//------------------------------------------------------------------------//
	/**
	 * ManageConstants()
	 *
	 * Displays all the Constants and ConstantGroups, from which they can be managed
	 * 
	 * Displays all the Constants and ConstantGroups, from which they can be managed
	 * It does assume anything to be passed to it via GET variables
	 *
	 * @return		void
	 * @method
	 *
	 */
	function ManageConstants()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_GOD);
		
		// Breadcrumb menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->SystemSettingsMenu();
		BreadCrumb()->SetCurrentPage("Constants");
		
		// Retrieve the list of ConstantGroups
		DBL()->ConfigConstantGroup->OrderBy("Name ASC");
		DBL()->ConfigConstantGroup->Load();
		
		// The actual Constants will be retrieved from within the HtmlTemplate
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('config_constants_management');

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// SystemSettingsMenu
	//------------------------------------------------------------------------//
	/**
	 * SystemSettingsMenu()
	 *
	 * Displays the System Settings Menu
	 * 
	 * Displays the System Settings Menu
	 * It does assume anything to be passed to it via GET variables
	 *
	 * @return		void
	 * @method
	 *
	 */
	function SystemSettingsMenu()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		// Breadcrumb menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->SetCurrentPage("System Settings");
		
		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('system_settings_menu');

		return TRUE;
	}


	//------------------------------------------------------------------------//
	// EditConstant
	//------------------------------------------------------------------------//
	/**
	 * EditConstant()
	 *
	 * Handles the logic for adding a new Constant, or editing an existing one
	 * 
	 * Handles the logic for adding a new Constant, or editing an existing one
	 * If DBO()->ConfigConstant->Id is set, then the popup will be set up for editing this Constant
	 * If it is not set then the popup will be set up for adding a new constant.
	 * If adding a new constant DBO()->ConfigConstantGroup->Id must be set to the constant group to
	 * add the constant to 
	 *
	 * @return		void
	 * @method
	 */
	function EditConstant()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		// Check if the form was submitted
		if (SubmittedForm('Constant', 'Ok'))
		{
			// Load the constantGroup
			DBO()->ConfigConstantGroup->Load();
			
			// Validate the Constant
			$mixResult = $this->_ValidateConstant(DBO()->ConfigConstant, DBO()->ConfigConstantGroup);
			if ($mixResult !== TRUE)
			{
				// The constant was invalid and the validation method returned an error message
				Ajax()->AddCommand("Alert", $mixResult);
				Ajax()->RenderHtmlTemplate("ConfigConstantEdit", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
				return TRUE;
			}
			
			// Set the ConstantGroup associated with the Constant
			// If it does not belong to a constant group, then this will be set to NULL
			DBO()->ConfigConstant->ConstantGroup = DBO()->ConfigConstantGroup->Id->Value;
			
			// The constant is valid; Save it
			if (!DBO()->ConfigConstant->Save())
			{
				// Saving the constant failed unexpectedly
				Ajax()->AddCommand("Alert", "ERROR: Saving the Constant to the database failed, unexpectedly");
				return TRUE;
			}
			
			// Close the popup
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("Alert", "The constant ". DBO()->ConfigConstant->Name->Value ." was successfully saved");
			
			// Fire the OnConfigConstantUpdate Event
			$arrEvent['ConfigConstantGroup']['Id']	= DBO()->ConfigConstantGroup->Id->Value;
			$arrEvent['ConfigConstant']['Id']		= DBO()->ConfigConstant->Id->Value;
			Ajax()->FireEvent(EVENT_ON_CONFIG_CONSTANT_UPDATE, $arrEvent);
			
			return TRUE;
		}
		
		if (DBO()->ConfigConstant->Id->Value)
		{
			// The user wants to edit an existing constant.  Load its details
			DBO()->ConfigConstant->Load();
			DBO()->ConfigConstantGroup->Id = DBO()->ConfigConstant->ConstantGroup->Value;
			
			// Flag whether the constant is set to NULL
			DBO()->ConfigConstant->ValueIsNull = (bool)(DBO()->ConfigConstant->Value->Value === NULL);
		}
		
		// Load the ConstantGroup
		DBO()->ConfigConstantGroup->Load();
		
		// Declare which Page Template to use
		$this->LoadPage('constant_edit');

		return TRUE;
	}

	//------------------------------------------------------------------------//
	// DeleteConstant
	//------------------------------------------------------------------------//
	/**
	 * DeleteConstant()
	 *
	 * Handles the logic for deleting a Constant
	 * 
	 * Handles the logic for deleting a Constant
	 * It assumes:
	 * 		DBO()->ConfigConstant->Id		Id of the constant to delete 
	 *
	 * @return		void
	 * @method
	 */
	function DeleteConstant()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		if (!DBO()->ConfigConstant->Load())
		{
			// The constant could not be found
			Ajax()->AddCommand("Alert", "ERROR: The constant could not be found in the database");
			return TRUE;
		}

		if (!(DBO()->ConfigConstant->Deletable->Value || AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD)))
		{
			// The user cannot delete this constant
			Ajax()->AddCommand("Alert", "ERROR: You do not have permission to delete this constant");
			return TRUE;
		}
		
		$strDeleteQuery = "DELETE FROM ConfigConstant WHERE Id = ". DBO()->ConfigConstant->Id->Value;
		
		$qryDelete = new Query();
		$mixResult = $qryDelete->Execute($strDeleteQuery);
		
		if ($mixResult === FALSE)
		{
			// The constant could not be deleted for some strange reason
			Ajax()->AddCommand("Alert", "ERROR: Deleting the constant failed, unexpectedly");
			return TRUE;
		}
		
		// Fire the OnConfigConstantUpdate Event 
		$arrEvent['ConfigConstantGroup']['Id']	= DBO()->ConfigConstant->ConstantGroup->Value;
		$arrEvent['ConfigConstant']['Id']		= DBO()->ConfigConstant->Id->Value;
		Ajax()->FireEvent(EVENT_ON_CONFIG_CONSTANT_UPDATE, $arrEvent);
		
		return TRUE;
	}


	// Returns an error message as a string if the constant is invalid.  Returns TRUE
	// if the constant is valid.  It will also set the properties of $dboConfigConstant to 
	// invalid, if they are
	// It is assumed that if $dboConfigConstantGroup is not NULL then it is a loaded ConfigConstantGroup record
	private function _ValidateConstant(&$dboConstant, $dboConstantGroup)
	{
		// If the constant is currently stored in the database, then load its current details
		if ($dboConstant->Id->Value)
		{
			// The constant is already defined in the database. Retrieve its details in a separate object
			$dboConstantCurrentDetails = new DBObject("ConfigConstant");
			$dboConstantCurrentDetails->Id = $dboConstant->Id->Value;
			$dboConstantCurrentDetails->Load();
			
			// Merge the data from the database, with the editted details
			$dboConstant->LoadMerge();
		}
		
		// Remove whitespace
		$dboConstant->Value = trim($dboConstant->Value->Value);
		
		if ($dboConstant->ValueIsNull->Value)
		{
			// The user wants to set the constant's value to NULL
			$dboConstant->Value = NULL;
		}
		
		// Convert the name to upper case and convert spaces into underscores
		$dboConstant->Name = strtoupper($dboConstant->Name->Value);
		$dboConstant->Name = str_replace(" ", "_", $dboConstant->Name->Value);
		
		// Check that a name has been specified
		if (!Validate("REGEX:/^[A-Z]([A-Z0-9_]*)[A-Z0-9]$/", $dboConstant->Name->Value))
		{
			$dboConstant->Name->SetToInvalid();
			return "ERROR: Name must be of the form: CONST_NAME";
		}

		
		// Check that the name is not currently in use
		if	(	// (The constant is already in the database AND its name has been changed AND the new name is currently a defined constant)
				((isset($dboConstantCurrentDetails)) && ($dboConstantCurrentDetails->Name->Value != $dboConstant->Name->Value) && (defined($dboConstant->Name->Value)))
				|| // OR
				// (The constant is not in the database AND its name is currently a defined constant)
				((!isset($dboConstantCurrentDetails)) && (defined($dboConstant->Name->Value)))
			)
		{
			$dboConstant->Name->SetToInvalid();
			return "ERROR: This name is already in use by another constant.  Please choose a unique name";
		}

		if ($dboConstantGroup->Special->Value)
		{
			// The constant belongs to a special constant group
			// A description must be specified
			if (!Validate("IsNotEmptyString", $dboConstant->Description->Value))
			{
				$dboConstant->Description->SetToInvalid();
				return "ERROR: Description must be specified";
			}
			
			// A value must be specified
			if (!Validate("IsNotEmptyString", $dboConstant->Value->Value))
			{
				$dboConstant->Value->SetToInvalid();
				return "ERROR: A value must be specified";
			}

			if ($dboConstantGroup->Type->Value == DATA_TYPE_INTEGER)
			{
				// Check that the value is an integer
				if (!Validate("Integer", $dboConstant->Value->Value))
				{
					$dboConstant->Value->SetToInvalid();
					return "ERROR: The value must be an integer";
				}
			}
			
			// Check that the value is not already in use by another constant of this group
			// It is assumed that all the constants stored in the database are currently loaded into memory
			$strGroupName	= $dboConstantGroup->Name->Value;
			$mixValue		= $dboConstant->Value->Value;
			
			if (isset($GLOBALS['*arrConstant'][$strGroupName][$mixValue]))
			{
				// The value is currently in use within this ConstantGroup
				// Check if it is refering to the current constant, or a different one
				if (isset($dboConstantCurrentDetails) && ($GLOBALS['*arrConstant'][$strGroupName][$mixValue]['Constant'] != $dboConstantCurrentDetails->Name->Value))
				{
					// The value is not refering to the current constant, therefor the value cannot be used by the current constant
					$dboConstant->Value->SetToInvalid();
					return "ERROR: This value is currently being used by another constant belonging to this constant group";
				}
				elseif (!isset($dboConstantCurrentDetails))
				{
					// The value is currently being used by another constant belonging to this constant group
					$dboConstant->Value->SetToInvalid();
					return "ERROR: This value is currently being used by another constant belonging to this constant group";
				}
			}
		}
		else
		{
			// The constant is not part of a special constant group and can be a string, integer, float or bool
			if ($dboConstant->Value->Value !== NULL)
			{
				$strErrorMessage = NULL; 
				switch ($dboConstant->Type->Value)
				{
					case DATA_TYPE_INTEGER:
						if (!Validate("Integer", $dboConstant->Value->Value))
						{
							$strErrorMessage = "ERROR: Value must be a valid integer";
						}
						else
						{
							// Type cast the string to an integer
							$dboConstant->Value = (int)($dboConstant->Value->Value);
						}
						break;
					case DATA_TYPE_FLOAT:
						if (!is_numeric($dboConstant->Value->Value))
						{
							$strErrorMessage = "ERROR: Value must be a valid floating point number";
						}
						else
						{
							// Type cast the string to a float
							$dboConstant->Value = (float)($dboConstant->Value->Value);
						}
						break;
					case DATA_TYPE_BOOLEAN:
						$strValue = $dboConstant->Value->Value;
						if (is_numeric($strValue))
						{
							$dboConstant->Value = ((int)($strValue)) ? 1 : 0;
						}
						else
						{
							switch (strtolower($strValue))
							{
								case "true":
									$dboConstant->Value = 1;
									break;
								case "false":
									$dboConstant->Value = 0;
									break;
								default:
									$strErrorMessage = "ERROR: Value must be a valid boolean.  ie TRUE, FALSE, or an integer";
									break;
							}
						}
						break;
					case DATA_TYPE_STRING:
						// No need to do anything if the data type is a string.  Even empty strings are allowed
						break;
					default:
						$strErrorMessage = "ERROR: Unknown DataType: ". $dboConstant->Type->Value;
						break;
				}
				if ($strErrorMessage)
				{
					// The value was invalid
					$dboConstant->Value->SetToInvalid();
					return $strErrorMessage;
				}
			}
		}
		
		if ($dboConstant->Id->Value)
		{
			// The user is editing an existing Constant
			if (!$dboConstant->Editable->IsSet)
			{
				// The user does not have permision to modify the 'Editable' and 'Deletable' properties
				// Set them to the old properties
				$dboConstant->Editable = $dboConstantCurrentDetails->Editable->Value;
				$dboConstant->Deletable = $dboConstantCurrentDetails->Deletable->Value;
			}
		}
		else
		{
			// The user is adding a new constant
			if (!$dboConstant->Editable->IsSet)
			{
				// The user does not have permision to modify the 'Editable' and 'Deletable' properties
				// Set them to both be true
				$dboConstant->Editable = TRUE;
				$dboConstant->Deletable = TRUE;
			}
		}
		
		return TRUE;
	}


	//------------------------------------------------------------------------//
	// RenderSingleConstantGroup
	//------------------------------------------------------------------------//
	/**
	 * RenderSingleConstantGroup()
	 *
	 * Renders a single ConstantGroup using the ConstantList HtmlTemplate
	 * 
	 * Renders a single ConstantGroup using the ConstantList HtmlTemplate
	 * This should only ever be requested by the "ManageConstants" page, via an ajax call
	 * It assumes:
	 * 			DBO()->ConfigConstantGroup->Id	To be set to the ConstantGroup to render.
	 * 											If this value is set to NULL, then the 
	 * 											miscellaneous group will be rendered
	 * 			DBO()->Container->Id			Id of the container div for the HTmlTemplate
	 *
	 * @return		void
	 * @method
	 */
	function RenderSingleConstantGroup()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);

		if (DBO()->ConfigConstantGroup->Id->Value)
		{
			// Load the ConstantGroup details
			DBO()->ConfigConstantGroup->Load();
		}
		
		// Render the ConfigConstantList HtmlTemplate 
		Ajax()->RenderHtmlTemplate("ConfigConstantList", HTML_CONTEXT_SINGLE, DBO()->Container->Id->Value);

		return TRUE;
	}


    //----- DO NOT REMOVE -----//
}
?>