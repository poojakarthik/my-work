<?php

class HtmlTemplate_Contact_View extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript("contact_edit");
		$this->LoadJavascript("actions_and_notes");
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("View Contact Details");
	}

	public function Render()
	{
		echo "<table class='contact-view'>"
		. "	<tbody>"
		. "		<tr>"
		. "			<td class='contact-view-details'>"
		. $this->renderDetails($this->mxdDataToRender['oContact'])
		. "			</td>"
		. "			<td class='contact-view-optionsnotes'>"
		. $this->renderOptions($this->mxdDataToRender['oContact']->Account)
		. $this->renderRecentNotes($this->mxdDataToRender['oContact'])
		. "			</td>"
		. "		</tr>"
		. "		<tr>"
		. "			<td colspan='2'>"
		. $this->renderAccounts($this->mxdDataToRender['aAccounts'])
		. '			</td>'
		. '		</tr>'
		. '	</tbody>'
		. '</table>';
	}
	
	public static function renderDetails($oContact)
	{
		$sHtml 	= "	<h2 class='contact-view-details-title'>Contact Details</h2>";
		$sHtml 	.= "<table class='reflex'>
						<tbody>
							<tr>
								<th class='label'>Full Name :</td>
								<td>{$oContact->FirstName} {$oContact->LastName}</td>
							</tr>
							<tr>
								<th class='label'>Date of Birth :</td>
								<td>{$oContact->DOB}</td>
							</tr>
							<tr>
								<th class='label'>Email Address :</td>
								<td><a href='mailto:{$oContact->Email}'>{$oContact->Email}</a></td>
							</tr>
							<tr>
								<th class='label'>Mobile Number :</td>
								<td>{$oContact->Mobile}</td>
							</tr>
							<tr>
								<th class='label'>Account Access :</td>
								<td>".($oContact->CustomerContact ? 'All Associated Accounts' : 'Primary Account Only')."</td>
							</tr>
							<tr>
								<th class='label'>Archived :</td>
								<td>".($oContact->Archived ? 'Archived Contact' : 'Active Contact')."</td>
							</tr>
						</tbody>
					</table>
					<button>
						Edit Contact Details
					</button>";
		
		return $sHtml;
	}
	
	public static function renderOptions($iAccountId)
	{
		$sHtml 	= "	<h2 class='contact-view-options-title'>Contact Options</h2>" .
				"	<ul>" .
				"		<li><a onclick='javascript: alert(\"popup goes here\");'>Edit Contact Details</a></li>" .
				"		<li><a href='/admin/flex.php/Account/InvoicesAndPayments/?Account.Id={$iAccountId}'>Make Payment</a></li>" .
				"	</ul>";
		return $sHtml;
	}
		
	public static function renderRecentNotes($oContact)
	{
		$sHtml	= HtmlTemplate_ActionsAndNotesList::renderActionsAndNotesList($oContact->Account, null, $oContact->Id, true, 5, ACTION_ASSOCIATION_TYPE_CONTACT, $oContact->Id);
		return $sHtml;
	}
	
	public static function renderAccounts($aAccounts)
	{
		$sHtml 	= "	<h2 class='contact-view-accounts-title'>Accounts</h2>";
		$sHtml 	= "	<table class='reflex contact-view-account'>" .
				"		<caption>" .
				"			<div class='caption_bar'>" .
				"				<div class='caption_title'>Accounts</div>" .
				"			</div>" .
				"		</caption>" .
				" 		<thead>" .
				" 			<th>Account</th>" .
				"			<th>Business Name</th>" .
				"			<th>Trading Name</th>" .
				"			<th>Overdue Charges</th>" .
				"		</thead>" .
				"		<tbody class='alternating'>";
		
		foreach ($aAccounts as $oAccount)
		{
			$sHtml	.= "<tr>" .
					"		<td>{$oAccount->Id}</td>" .
					"		<td>{$oAccount->BusinessName}</td>" .
					"		<td>{$oAccount->TradingName}</td>" .
					"		<td class='overdue'>\${$oAccount->fOverdueAmount}</td>" .
					"	</tr>";
		}
		
		$sHtml 	.= "	</tbody>
				</table>
				<div class='contact-view-account-add'>
					<button>
						Add Associated Account
					</button>
				</div>";
		
		return $sHtml;
	}
}

?>