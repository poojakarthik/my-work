<?php
//----------------------------------------------------------------------------//
// HtmlTemplateEmployeeDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateEmployeeDetails
 *
 * A specific HTML Template object
 *
 * An Employee Details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateEmployeeDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplateEmployeeEdit extends HtmlTemplate
{
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
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strId = $strId;
		
		$this->LoadJavascript("permissions");
		$this->LoadJavascript("employee_edit");
		$this->LoadJavascript("date_time_picker_xy");

		if (DBO()->Employee->EditSelf->Value)
		{
			//$this->LoadJavascript("vixen_modal");
		}
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
		$this->_RenderFullDetail();
	}

	//------------------------------------------------------------------------//
	// _RenderFullDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderFullDetail()
	 *
	 * Render this HTML Template with full detail
	 *
	 * Render this HTML Template with full detail
	 *
	 * @method
	 */
	private function _RenderFullDetail()
	{
		$objUserRole = User_Role::getForId(DBO()->Employee->user_role_id->Value);
		$strUserRole = ($objUserRole != NULL)? $objUserRole->name : "[Not Specified]";
		
		echo "<!-- START HtmlTemplateEmployeeEdit -->\n";
		$bolProperAdminUser	= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);
		$bolAdding		= FALSE;
		$strEditDisplay	= " style='display: none;'";
		$strViewDisplay	= "";

		$bolUserIsSelf	= DBO()->Employee->Id->Value == AuthenticatedUser()->GetUserId();

		$currentUserTicketingPermission = Ticketing_User::getPermissionForEmployeeId(AuthenticatedUser()->GetUserId());
		if ($bolUserIsSelf)
		{
			$displayUserTicketingPermission = $currentUserTicketingPermission;
		}
		else
		{
			$displayUserTicketingPermission = Ticketing_User::getPermissionForEmployeeId(DBO()->Employee->Id->Value);
		}

		$bolEditSelf	= FALSE;

		if (DBO()->Employee->Id->Value == -1)
		{
			$bolAdding = TRUE;
			$strViewDisplay = $strEditDisplay;
			$strEditDisplay = "";
		}

		$this->FormStart('Employee', 'Employee', $bolAdding ? 'Create' : 'Edit');
		
		if (DBO()->Employee->EditSelf->Value)
		{
			echo "<input type='hidden' name='Employee.EditSelf' value='1'/>\n";
			$bolEditSelf = TRUE;
		}
		
		//$strMinWidth = $bolEditSelf ? " style='width: 400px;'" : "";
		
		echo "<div class='GroupedContent'$strMinWidth>";
		
		echo "<div id='Employee.Edit'$strEditDisplay>";
		
		DBO()->Employee->Id->RenderHidden();
		if ($bolAdding && !$bolEditSelf && $bolProperAdminUser)
		{
			DBO()->Employee->DOB->Value = "";
			DBO()->Employee->UserName->RenderInput(CONTEXT_DEFAULT, TRUE);
		}
		else
		{
			DBO()->Employee->UserName->RenderOutput(CONTEXT_DEFAULT, TRUE);
		}
		
		if (!$bolEditSelf && $bolProperAdminUser)
		{
			DBO()->Employee->FirstName->RenderInput(CONTEXT_DEFAULT, TRUE);
			DBO()->Employee->LastName->RenderInput(CONTEXT_DEFAULT, TRUE);
			$arrAdditionalArgs = array();
			$arrAdditionalArgs["FROM_YEAR"] = 1900;
			$arrAdditionalArgs["TO_YEAR"] = ((int)date("Y"));
			$arrAdditionalArgs["DEFAULT_YEAR"] = ((int)date("Y")) - 18;
			DBO()->Employee->DOB->RenderInput(CONTEXT_DEFAULT, TRUE, TRUE, $arrAdditionalArgs);
		}
		else
		{
			DBO()->Employee->FirstName->RenderOutput();
			DBO()->Employee->LastName->RenderOutput();
			DBO()->Employee->DOB->RenderOutput();
		}

		DBO()->Employee->Email->RenderInput();
		DBO()->Employee->Extension->RenderInput();
		DBO()->Employee->Phone->RenderInput();
		DBO()->Employee->Mobile->RenderInput();
		DBO()->Employee->Password->RenderInput(CONTEXT_DEFAULT, $bolAdding);
		
		if (!$bolAdding && !$bolEditSelf)
		{
			DBO()->Employee->Archived->RenderInput();
		}

		if (Flex_Module::isActive(FLEX_MODULE_TICKETING))
		{
			// If the current user is super admin OR (a ticketing admin and not editing self), allow modification
			if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN) || (!$bolUserIsSelf && $currentUserTicketingPermission == TICKETING_USER_PERMISSION_ADMIN))
			{
				echo "
<div class=\"DefaultElement\">
	<select id=\"ticketing_user.permission\" name=\"ticketing_user.permission\" class=\"DefaultInputText Default\">
		<option value='".TICKETING_USER_PERMISSION_NONE ."'".($displayUserTicketingPermission == TICKETING_USER_PERMISSION_NONE						? ' SELECTED' : '').">" . GetConstantDescription(TICKETING_USER_PERMISSION_NONE				, 'ticketing_user_permission') . "</option>
		<option value='".TICKETING_USER_PERMISSION_USER ."'".($displayUserTicketingPermission == TICKETING_USER_PERMISSION_USER						? ' SELECTED' : '').">" . GetConstantDescription(TICKETING_USER_PERMISSION_USER				, 'ticketing_user_permission') . "</option>
		<option value='".TICKETING_USER_PERMISSION_USER_EXTERNAL ."'".($displayUserTicketingPermission == TICKETING_USER_PERMISSION_USER_EXTERNAL	? ' SELECTED' : '').">" . GetConstantDescription(TICKETING_USER_PERMISSION_USER_EXTERNAL	, 'ticketing_user_permission') . "</option>
		<option value='".TICKETING_USER_PERMISSION_ADMIN."'".($displayUserTicketingPermission == TICKETING_USER_PERMISSION_ADMIN					? ' SELECTED' : '').">" . GetConstantDescription(TICKETING_USER_PERMISSION_ADMIN			, 'ticketing_user_permission') . "</option>
	</select>
   <div id=\"ticketing_user.permission.Label\" class=\"DefaultLabel\">
      <span> &nbsp;</span>
      <span id=\"ticketing_user.permission.Label.Text\">Ticketing System : </span>

   </div>
</div>
				";
			}
			else
			{
				// Else, just display an output
				$description = htmlspecialchars(GetConstantDescription($displayUserTicketingPermission, 'ticketing_user_permission'));
				echo "
<div class=\"DefaultElement\">
   <div id=\"ticketing_user.permission.Output\" name=\"ticketing_user.permission.id\" class=\"DefaultOutput Default\">$description</div>
   <div id=\"ticketing_user.permission.Label\" class=\"DefaultLabel\">
      <span> &nbsp;</span>
      <span id=\"ticketing_user.permission.Label.Text\">Ticketing System : </span>

   </div>
</div>
			";
			}
		}
		
		if (!$bolEditSelf && $bolProperAdminUser)
		{
			// The user can change the role of the employee
			$arrUserRoles = User_Role::getAll();
			$strRoleOptions = "";
			$strSelected = "";
			foreach ($arrUserRoles as $objRole)
			{
				if ($objUserRole !== NULL)
				{
					$strSelected = ($objUserRole->id === $objRole->id)? "selected='selected'": "";
				}
				$strRoleOptions .= "<option $strSelected value='{$objRole->id}'>{$objRole->name}</option>";
			}
			
			echo "
<div class='DefaultElement'>
	<select id='Employee.user_role_id' name='Employee.user_role_id' class='DefaultInputText Default' style='width:210px'>$strRoleOptions</select>
	<div id='Employee.user_role_id.Label' class='DefaultLabel'>
		<span> &nbsp;</span>
		<span id='Employee.user_role_id.Label.Text'>Role : </span>
	</div>
</div>
";
		}
		else
		{
			// User can not change their role
			echo "
<div class='DefaultElement'>
   <div class='DefaultOutput Default'>$strUserRole</div>
   <div class='DefaultLabel'>
      <span> &nbsp;</span>
      <span>Role : </span>
   </div>
</div>
";
		}
		

		echo "</div>";

		echo "<div id='Employee.View'$strViewDisplay>";
		
		DBO()->Employee->UserName->RenderOutput();
		DBO()->Employee->FirstName->RenderOutput();
		DBO()->Employee->LastName->RenderOutput();
		DBO()->Employee->DOB->RenderOutput();
		DBO()->Employee->Email->RenderOutput();
		DBO()->Employee->Extension->RenderOutput();
		DBO()->Employee->Phone->RenderOutput();
		DBO()->Employee->Mobile->RenderOutput();
		DBO()->Employee->Password = "[Hidden]";
		DBO()->Employee->Password->RenderOutput();
		if (!$bolEditSelf)
		{
			DBO()->Employee->Archived->RenderOutput();
		}

		if (Flex_Module::isActive(FLEX_MODULE_TICKETING))
		{
			$description = htmlspecialchars(GetConstantDescription($displayUserTicketingPermission, 'ticketing_user_permission'));
			echo "
	<div class=\"DefaultElement\">
	   <div id=\"ticketing_user.permission.Output\" name=\"ticketing_user.permission.id\" class=\"DefaultOutput Default\">$description</div>
	   <div id=\"ticketing_user.permission.Label\" class=\"DefaultLabel\">
	      <span> &nbsp;</span>
	      <span id=\"ticketing_user.permission.Label.Text\">Ticketing System : </span>
	   </div>
	</div>
			";
		}
		
		echo "
<div class='DefaultElement'>
   <div class='DefaultOutput Default'>$strUserRole</div>
   <div class='DefaultLabel'>
      <span> &nbsp;</span>
      <span>Role : </span>
   </div>
</div>
";

		echo "</div>";
		echo "</div>";
		
		
		// Control the display of the permissions lists
		$arrCurrentPerms = array();
		$strSelectedPerms = '';
		$strAvailPerms = '';
		$intPermIndex = 1;
		asort($GLOBALS['Permissions']);
		foreach ($GLOBALS['Permissions'] as $intKey => $strValue)
		{
			// Only Super Admins can assign Super Admin
			if (PERMISSION_SUPER_ADMIN == $intKey && !AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN))
			{
				continue;
			}
			// Only allow admins to set credit card and rate management permissions
			// This is a redundant check as, at present, only admins can change any permissions!
			if (PermCheck(PERMISSION_CREDIT_MANAGEMENT | PERMISSION_RATE_MANAGEMENT, $intKey))
			{
				if (!AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN))
				{
					continue;
				}
			}
			// Only allow SuperAdmins to set the CustomerGroupAdmin permission
			if (PermCheck(PERMISSION_CUSTOMER_GROUP_ADMIN, $intKey) && !AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN))
			{
				continue;
			}
			
			if (PermCheck(DBO()->Employee->Privileges->Value, $intKey))
			{
				$strDisableAdmin = ($bolUserIsSelf && PermCheck(PERMISSION_ADMIN, $intKey)) ? " disabled" : "";
				$strSelectedPerms .= "<option value='$intKey'$strDisableAdmin>$strValue</option>";
				$arrCurrentPerms[] = $strValue;
			}
			else
			{
				$strAvailPerms .= "<option value='$intKey'>$strValue</option>";
			}
		}
		
		$strCurrentPerms = !empty($arrCurrentPerms) ? implode($arrCurrentPerms, "<br/>") : "[No permissions]";
		
		echo "<p>

			  <div class='GroupedContent'>
				  <div class='SmallSeperator'></div>";
		
		if ($bolProperAdminUser && !$bolEditSelf)
		{
			echo "
					<div id='Permissions.Edit'$strEditDisplay>
			  			<input type='hidden' name='Id' value='27' />
						<table border='0' cellpadding='3' cellspacing='0'>
							<tr>
								<th>Available Permissions</th>
								<th></th>
								<th>Selected Permissions</th>
							</tr>
							<tr>
								<td>
									<select id='AvailablePermissions' name='AvailablePermissions[]' size='8' class='SmallSelection' style='width: 180px;' multiple='multiple'>$strAvailPerms</select>
								</td>
								<td>
									<div>
										<input type='button' value='&#xBB;' onclick='EmployeePermissions.addSelections()' />
									</div>
									<div class='Seperator'></div>
									<div>
										<input type='button' value='&#xAB;' onclick='EmployeePermissions.removeSelections()' />
									</div>
								</td>
								<td>
									<select id='SelectedPermissions' name='Employee.Privileges' size='8' class='SmallSelection' style='width: 180px;' multiple='multiple' valueIsList>$strSelectedPerms
									</select>
								</td>
							</tr>
						</table>
					</div>";
		}

		echo "
					<div id='Permissions.View'$strViewDisplay>
						<table border='0' cellpadding='3' cellspacing='0' style='width:100%'>
							<tr>
								<th>Permissions</th>
							</tr>
							<tr>
								<td>
									<div style='overflow:auto;max-height:10em;width:100%;'>
										$strCurrentPerms
									</div>
								</td>
							</tr>
						</table>
					</div>";
			
		echo "
				</div>
			</div>
			<div class='SmallSeperator'></div>";

		echo "<script type='text/javascript'>EmployeeEdit.bolPerms = " . ($bolEditSelf ? "false" : "true") . "; EmployeePermissions.init();</script>";

		if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			echo "<div class='ButtonContainer' id='EmployeeButtons.Edit'$strEditDisplay><div class='right'>\n";
			
			if (!$bolAdding && $bolProperAdminUser)
			{
				$this->Button("Reassign Tickets & Follow-Ups", "EmployeeEdit.showReassignPopup(".DBO()->Employee->Id->Value.", Vixen.Popup.arrOverlayZIndexHistory.pop());");
			}
			
			$this->_renderSaveButton(DBO()->Employee->Id->Value, 'Save', NULL, $bolAdding ? 'Create' : 'Edit');
			
			if (!$bolAdding)
			{
				$this->Button("Cancel", "EmployeeEdit.toggle();");
			}
			else
			{
				$this->Button("Cancel", "Vixen.Popup.Close(this);");
			}
			
			echo "</div></div>";
	
			if (!$bolAdding)
			{
				echo "<div class='ButtonContainer' id='EmployeeButtons.View'$strViewDisplay><div class='right'>\n";
				
				if ($bolProperAdminUser)
				{
					$this->Button("Reassign Tickets & Follow-Ups", "EmployeeEdit.showReassignPopup(".DBO()->Employee->Id->Value.", Vixen.Popup.arrOverlayZIndexHistory.pop());");
				}
				
				if ($bolProperAdminUser || $bolEditSelf)
				{
					$this->Button("Edit", "EmployeeEdit.toggle();");
				}
				
				$this->Button("Close", "Vixen.Popup.Close(" . ($this->IsModal() ? "'CloseFlexModalWindow'" : "this") . ");");
				echo "</div></div>";
			}
		}

		echo "</div>\n";

		$this->FormEnd();
		
		echo "<!-- END HtmlTemplateEmployeeEdit -->\n";
	}
	
	// This is a copy of HtmlTemplate::AjaxSubmit, customised so that the save button can have an intermediate click event
	private function _renderSaveButton($iEmployeeId, $strLabel, $strTemplate=NULL, $strMethod=NULL, $strTargetType=NULL, $strStyleClass="InputSubmit", $strButtonId='VixenButtonId')
	{
		$strTarget = '';
		$strId = '';
		$strSize = '';
		
		if (!$strTemplate)
		{
			$strTemplate = $this->_strTemplate;
		}
		if (!$strMethod)
		{
			$strMethod = $this->_strMethod;
		}
		if (is_object($this->_objAjax))
		{
			//echo $this->_objAjax->TargetType;
			$strTarget = $this->_objAjax->TargetType;
			$strId = $this->_objAjax->strId;
			$strSize = $this->_objAjax->strSize;
		}
		
		if ($strTargetType !== NULL)
		{
			$strTarget = $strTargetType;
		}
		
		echo "<input type='button' value='$strLabel' class='$strStyleClass' id='$strButtonId' name='VixenButtonId' onclick=\"EmployeeEdit.checkForAssignedTasks({$iEmployeeId}, function() {Vixen.Ajax.SendForm('{$this->_strForm}', '$strLabel','$strTemplate', '$strMethod', '$strTarget', '$strId', '$strSize', '{$this->_strContainerDivId}');});\"></input>\n";
	}
}

?>
