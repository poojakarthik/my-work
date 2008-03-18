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
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strId = $strId;
		//$this->LoadJavascript("dhtml");
		//$this->LoadJavascript("highlight");
		$this->LoadJavascript("permissions");
		$this->LoadJavascript("employee_edit");
		$this->LoadJavascript("date_time_picker_xy");

		if (DBO()->Employee->EditSelf->Value)
		{
			$this->LoadJavascript("vixen_modal");
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
		switch ($this->_intContext)
		{
			/*case HTML_CONTEXT_SEANS_DETAIL:
				$this->_RenderSeansDetail();
				break;
			case HTML_CONTEXT_LEDGER_DETAIL:
				$this->_RenderLedgerDetail();
				break;
			case HTML_CONTEXT_FULL_DETAIL:
		
				$this->_RenderFullDetail();
				break;*/
			default:
				$this->_RenderFullDetail();
				break;
		}
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
		echo "<!-- START HtmlTemplateEmployeeEdit -->\n";
		$bolAdminUser = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		$bolAdding = FALSE;
		$strEditDisplay = " style='display: none;'";
		$strViewDisplay = "";

		$bolUserIsSelf = DBO()->Employee->Id->Value == AuthenticatedUser()->GetUserId();

		$bolEditSelf = FALSE;

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
		
		$strMinWidth = $bolEditSelf ? " style='width: 400px;'" : "";
		
		echo "<div class='NarrowForm'$strMinWidth>";
		
		echo "<div id='Employee.Edit'$strEditDisplay>";
		
		DBO()->Employee->Id->RenderHidden();
		if ($bolAdding && !$bolEditSelf && $bolAdminUser)
		{
			DBO()->Employee->DOB->Value = "";
			DBO()->Employee->UserName->RenderInput(CONTEXT_DEFAULT, TRUE);
		}
		else
		{
			DBO()->Employee->UserName->RenderOutput(CONTEXT_DEFAULT, TRUE);
		}
		
		if (!$bolEditSelf && $bolAdminUser)
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
		
		echo "</div>";
		echo "</div>";
		
		
		// Control the display of the permissions lists
		$arrCurrentPerms = "";
		$intPermIndex = 1;
		foreach ($GLOBALS['Permissions'] as $intKey => $strValue)
		{
			// Don't allow super admin permissions to be set
			if (PermCheck(PERMISSION_SUPER_ADMIN, $intKey))
			{
				continue;
			}
			// Only allow admins to set credit card and rate management permissions
			if (PermCheck(PERMISSION_CREDIT_CARD | PERMISSION_RATE_MANAGEMENT, $intKey))
			{
				if (!AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN))
				{
					continue;
				}
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
		
		$strCurrentPerms = is_array($arrCurrentPerms) ? implode($arrCurrentPerms, "<br/>") : "[No permissions]";
		
		echo "<p>

			  <div class='NarrowContent'>
				  <div class='SmallSeperator'></div>";
		
		if ($bolAdminUser && !$bolEditSelf)
		{
			echo "
					<div id='Permissions.Edit'$strEditDisplay>
			  			<input type='hidden' name='Id' value='27' />
						<table border='0' cellpadding='3' cellspacing='0'>
							<tr>
								<th>Available Permissions :</th>
								<th></th>
								<th>Selected Permissions :</th>
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
						<table border='0' cellpadding='3' cellspacing='0'>
							<tr>
								<th>Permissions :</th>
							</tr>
							<tr>
								<td>
									 $strCurrentPerms
								</td>
							</tr>
						</table>
					</div>";
			
		echo "
				</div>
			</div>
			<div class='Seperator'></div>";

		echo "<script type='text/javascript'>EmployeeEdit.bolPerms = " . ($bolEditSelf ? "false" : "true") . "; EmployeePermissions.init();</script>";

		if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			echo "<div class='ButtonContainer' id='EmployeeButtons.Edit'$strEditDisplay><div class='right'>\n";
			$this->AjaxSubmit('Save', NULL, $bolAdding ? 'Create' : 'Edit');
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
				$this->Button("Edit", "EmployeeEdit.toggle();");
				$this->Button("Close", "Vixen.Popup.Close(" . ($this->IsModal() ? "'CloseFlexModalWindow'" : "this") . ");");
				echo "</div></div>";
			}
		}

		echo "</div>\n";

		$this->FormEnd();
		
		echo "<!-- END HtmlTemplateEmployeeEdit -->\n";
	}
}

?>
