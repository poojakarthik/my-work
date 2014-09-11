<?php

class HtmlTemplate_Contact_View extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('popup_contact_edit');
		$this->LoadJavascript('actions_and_notes');
		$this->LoadJavascript('reflex_validation');
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->AccountOverview($mxdDataToRender['oContact']->Account, TRUE);
		BreadCrumb()->SetCurrentPage("View Contact Details");
	}

	public function Render()
	{
		$oContact	= $this->mxdDataToRender['oContact'];
		echo "<h1>Contact &ndash; {$oContact->FirstName} {$oContact->LastName}</h1>" .
			"<table class='contact-view'>"
			. "	<tbody>"
			. "		<tr>"
			. "			<td>"
			. $this->renderDetails($this->mxdDataToRender['oContact'], $this->mxdDataToRender['aContactTitles'])
			. $this->renderAccounts($this->mxdDataToRender['aAccounts'], $this->mxdDataToRender['oContact'])
			. "			</td>"
			. "			<td class='contact-view-notes'>"
			. $this->renderRecentNotes($this->mxdDataToRender['oContact'])
			. "			</td>"
			. "		</tr>"
			. "		</tr>"
			. "	</tbody>"
			. "</table>"; 
	}
	
	public static function renderDetails($oContact, $aContactTitles)
	{
		// Work out title text
		$sTitle	= '';
		
		foreach ($aContactTitles as $iId => $oTitle)
		{
			if ($oTitle->name == $oContact->Title)
			{
				$sTitle	= "{$oTitle->name} ";
				break;
			}
		}
		
		// Reorganise date string
		$aDate			= explode('-', $oContact->DOB);
		$sDate			= "{$aDate[2]}-{$aDate[1]}-{$aDate[0]}";		
		$sNotSupplied	= "<span class='contact-field-null'>[ None ]</span>";
		$sHtml			= "	<div class='section'>" .
				"		<div class='section-header'>" .
				"			<div class='section-header-title'>" .
				"				<img src='../admin/img/template/contact_small.png'/>" .
				"				<h2>Contact Details</h2>" .
				"			</div>" .
				"		</div>" .
				"		<div class='section-content'>" .
				"			<table class='contact-view-details'>
								<tbody>
									<tr>
										<th>Full Name :</th>
										<td>{$sTitle}{$oContact->FirstName} {$oContact->LastName}</td>
									</tr>
									<tr>
										<th>Job Title :</th>
										<td>".($oContact->JobTitle != '' ? $oContact->JobTitle : $sNotSupplied )."</td>
									</tr>
									<tr>
										<th>Date of Birth :</th>
										<td>$sDate</td>
									</tr>
									<tr>
										<th>Email Address :</th>
										<td><a href='mailto:{$oContact->Email}'>{$oContact->Email}</a></td>
									</tr>
									<tr>
										<th>Phone Number :</th>
										<td>".($oContact->Phone != '' ? $oContact->Phone : $sNotSupplied )."</td>
									</tr>
									<tr>
										<th>Mobile Number :</th>
										<td>".($oContact->Mobile != '' ? $oContact->Mobile : $sNotSupplied )."</td>
									</tr>
									<tr>
										<th>Fax Number :</th>
										<td>".($oContact->Fax != '' ? $oContact->Fax : $sNotSupplied )."</td>
									</tr>
									<tr>
										<th>Account Access :</th>
										<td>".($oContact->CustomerContact ? 'All Associated Accounts' : 'Primary Account Only')."</td>
									</tr>
									<tr>
										<th>Status :</td>
										".($oContact->Archived ? "<td class='contact-archived'>Archived" : "<td class='contact-available'>Active")."</td>
									</tr>
								</tbody>
							</table>".
				"		</div>" .
				"		<div class='section-footer'>" .
				"			<button class='icon-button' onclick='javascript: new Popup_Contact_Edit({$oContact->Id}, null, Popup_Contact_Edit._goToPage);'>" .
				"				<img src='../admin/img/template/user_edit.png'/>" .
				"				<span>Edit Contact Details</span>" .
				"			</button>" .				
				"		</div>" .
				"	</div>";
		
		return $sHtml;
	}
	
	public static function renderRecentNotes($oContact)
	{
		$sHtml	= HtmlTemplate_ActionsAndNotesList::renderActionsAndNotesList($oContact->Account, null, $oContact->Id, true, 5, ACTION_ASSOCIATION_TYPE_CONTACT, $oContact->Id);
		return $sHtml;
	}
	
	public static function renderAccounts($aAccounts, $oContact)
	{
		$sHtml	= "	<div class='section'>" .
				"		<div class='section-header'>" .
				"			<div class='section-header-title'>" .
				"				<img src='../admin/img/template/accounts_small.png'/>" .
				"				<h2>Accounts</h2>" .
				"			</div>" .			
				"		</div>" .
				"		<div class='section-content section-content-fitted'>" .
				"			<table class='reflex contact-view-account'>" .
				" 				<thead>" .
				" 					<th>Account</th>" .
				"					<th>Business Name</th>" .
				"					<th>Trading Name</th>" .
				"					<th>Overdue Charges</th>" .
				"					<th></th>" .
				"				</thead>" .
				"				<tbody class='alternating'>";
		
		// Add a row for each account
		foreach ($aAccounts as $oAccount)
		{
			$sHtml	.= "<tr>" .
					"		<td><a href='flex.php/Account/Overview/?Account.Id={$oAccount->Id}'>{$oAccount->Id}</a></td>" .
					"		<td>{$oAccount->BusinessName}</td>" .
					"		<td>{$oAccount->TradingName}</td>" .
					"		<td class='overdue'>\${$oAccount->fOverdueAmount}</td>" .
					" 		<td><a href='flex.php/Account/InvoicesAndPayments/?Account.Id={$oAccount->Id}'>Make Payment</a>" .
					"	</tr>";
		}
		
		$sHtml 	.= "			</tbody>" .
			"				</table>".
			"			</div>" .
			"			<div class='section-footer'>" .
			"				<button class='icon-button' onclick='javascript: window.location=\"reflex.php/Account/Create/?Associated={$oAccount->Id}&Contact={$oContact->Id}\";'>" .
			"					<img src='../admin/img/template/accounts_small_add.png'/>" .
			"					<span>Add Associated Account</span>" .
			"				</button>" .				
			"			</div>" .
			"		</div>";
		
		return $sHtml;
	}
}

?>