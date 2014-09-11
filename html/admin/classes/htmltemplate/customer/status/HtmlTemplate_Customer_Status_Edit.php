<?php

class HtmlTemplate_Customer_Status_Edit extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript("customer_status_edit");
	}

	public function Render()
	{
		$objStatus = $this->mxdDataToRender['CustomerStatus'];
		
		$intActionDescriptionMaxLength = Customer_Status::ACTION_DESCRIPTION_MAX_LENGTH;

		$strName					= htmlspecialchars($objStatus->name);
		$strDescription				= htmlspecialchars($objStatus->description);
		$strDefaultAction			= htmlspecialchars($objStatus->defaultActionDescription);
		$strDefaultOverdueAction	= htmlspecialchars($objStatus->defaultOverdueActionDescription);
		
		$strViewLink = Href()->ViewCustomerStatus($objStatus->id);

		
		// Build the tables showing the actions to be taken, specific to the role of the user
		$arrActions = $objStatus->getAllActionDescriptions();
		$arrUserRoles = User_Role::getAll();
		
		$strRoleSpecificActions = "";
		$arrRoleIds = array();
		foreach ($arrUserRoles as $objRole)
		{
			// Check if specific actions are defined for this user role
			$strRoleName		= htmlspecialchars($objRole->name);
			$intRoleId			= $objRole->id;
			$arrRoleIds[]		= $objRole->id;
			$strNormalAction	= "";
			$strOverdueAction	= "";
			if (array_key_exists($objRole->id, $arrActions))
			{
				// It does
				$strNormalAction	= htmlspecialchars($arrActions[$objRole->id]['Normal'], ENT_QUOTES);
				$strOverdueAction	= htmlspecialchars($arrActions[$objRole->id]['Overdue'], ENT_QUOTES);
			}
			
			$strRoleSpecificActions .= "
<div class='SmallSeparator'></div>
<table class='reflex'>
	<thead class='header'>
		<tr>
			<th colspan='2'>Actions specific to user role: $strRoleName</th>
		</tr>
	</thead>
	<tbody>
		<tr class='alt'>
			<td class='title'>Normal: </td>
			<td><input type='text' style='width:100%' UserRoleId='$intRoleId' id='Action_Normal_$intRoleId' name='Action' value='$strNormalAction' maxlength='$intActionDescriptionMaxLength'></input></td>
		</tr>
		<tr>
			<td class='title'>When Overdue: </td>
			<td><input type='text' style='width:100%' UserRoleId='$intRoleId' Overdue='Overdue' id='Action_Overdue_$intRoleId' name='Action' value='$strOverdueAction' maxlength='$intActionDescriptionMaxLength'></input></td>
		</tr>
	</tbody>
</table>
";

		}


		echo "
<form id='FormCustomerStatus'>
	<table class='reflex'>
		<caption>
			<div id='caption_bar' class='caption_bar'>
				<div id='caption_title' class='caption_title'>
					Customer Status: $strName
				</div>
				<div id='caption_options' class='caption_options'>
					<a href='$strViewLink'>View</a>
				</div>
			</div>
		</caption>
		<thead class='header'>
			<tr>
				<th colspan='2' align='right'>
					<input type='button' class='reflex-button' value='Save' onclick='Flex.CustomerStatusEdit.Save()'/>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class='alt'>
				<td class='title'>Name: </td>
				<td>$strName</td>
			</tr>
			<tr class='alt'>
				<td class='title'>Criteria: </td>
				<td>$strDescription</td>
			</tr>
			<tr class='alt'>
				<td class='title'>Precedence: </td>
				<td>{$objStatus->precedence}</td>
			</tr>
			<tr class='alt'>
				<td class='title'>Default Action: </td>
				<td><input type='text' style='width:100%' class='required' id='Action_Normal_Default' name='Action' value='$strDefaultAction' maxlength='$intActionDescriptionMaxLength'></input></td>
			</tr>
			<tr class='alt'>
				<td class='title'>Default Action when Customer Overdue: </td>
				<td><input type='text' style='width:100%' class='required' Overdue='Overdue' id='Action_Overdue_Default' name='Action' value='$strDefaultOverdueAction' maxlength='$intActionDescriptionMaxLength'></td>
			</tr>
		</tbody>
	</table>
	$strRoleSpecificActions
</form>
<script type='text/javascript'>Flex.CustomerStatusEdit.Initialise({$objStatus->id})</script>
";


	}
}

?>
