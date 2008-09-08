<?php

class HtmlTemplate_Customer_Status_View extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
	}

	public function Render()
	{
		$objStatus = $this->mxdDataToRender['CustomerStatus'];

		$strName					= htmlspecialchars($objStatus->name);
		$strDescription				= htmlspecialchars($objStatus->description);
		$strDefaultAction			= htmlspecialchars($objStatus->defaultActionDescription);
		$strDefaultOverdueAction	= htmlspecialchars($objStatus->defaultOverdueActionDescription);
		
		$strEditLink = Href()->EditCustomerStatus($objStatus->id);

		$arrUserRoles = User_Role::getAll();
		
		// Build the tables showing the actions to be taken, specific to the role of the user
		// Note that this iterates through $arrUserRoles (instead of the $arrActions), so that the tables will be in alphabetical order of user_role.name
		$arrActions = $objStatus->getAllActionDescriptions();
		$strRoleSpecificActions = "";
		foreach ($arrUserRoles as $intUserRoleId=>$objUserRole)
		{
			// Check if the CustomerStatus has actions descriptions specific to this role
			if (!array_key_exists($intUserRoleId, $arrActions))
			{
				continue;
			}
			$arrAction = $arrActions[$intUserRoleId];
			
			$strRole			= htmlspecialchars($objUserRole->name);
			$strNormalAction	= htmlspecialchars($arrAction['Normal']);
			$strOverdueAction	= htmlspecialchars($arrAction['Overdue']);

			$strRoleSpecificActions .= "
<div class='SmallSeparator'></div>
<table class='reflex'>
	<thead class='header'>
		<tr>
			<th colspan='2'>Actions specific to user role: $strRole</th>
		</tr>
	</thead>
	<tbody>
		<tr class='alt'>
			<td class='title'>Normal: </td>
			<td>$strNormalAction</td>
		</tr>
		<tr>
			<td class='title'>When Overdue: </td>
			<td>$strOverdueAction</td>
		</tr>
	</tbody>
</table>
";

		}


		echo "
<table class='reflex'>
	<caption>
		<div id='caption_bar' name='caption_bar'>
			<div id='caption_title' name='caption_title'>
				Customer Status: $strName
			</div>
			<div id='caption_options' name='caption_options'>
				<a href='$strEditLink'>Edit</a>
			</div>
		</div>
	</caption>
	<thead class='header'>
		<tr>
			<th colspan='2'>&nbsp;</th>
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
			<td>$strDefaultAction</td>
		</tr>
		<tr class='alt'>
			<td class='title'>Default Action when Customer Overdue: </td>
			<td>$strDefaultOverdueAction</td>
		</tr>
	</tbody>
</table>
$strRoleSpecificActions
";


	}
}

?>
