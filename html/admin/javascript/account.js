// Class: Account
// Handles Accounts in Flex
var Account	= Class.create
({	
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		
	},
	
	displayReferAccountsPopup	: function(intAccount, strBusinessName, strAccountStatus)
	{
		var strReferees	= "";
		
		var strHTML	= "\n" + 
		"<div class='GroupedContent'>\n" + 
		"	<div>\n" + 
		"		<p>The Account '"+strBusinessName+"' ("+intAccount+") is currently set to the "+strAccountStatus+" Status, which you do not have permissions to view or edit.</p>\n" +
		"		<p>Please refer this Customer to one of the following Credit Control referees:</p>\n" + 
		"	</div>\n" + 
		"	<table class='reflex' style='margin-top: 8px; margin-bottom: 8px;' width='100%'>\n" + 
		"		<thead>\n" + 
		"			<tr>\n" + 
		"				<th style='font-size:10pt;vertical-align:top;text-align:left;'>Employee</th>\n" + 
		"				<th style='font-size:10pt;vertical-align:top;text-align:right;'>Internal Extenstion</th>\n" + 
		"				<th style='font-size:10pt;vertical-align:top;text-align:right;'>Email</th>\n" + 
		"			</tr>\n" + 
		"		</thead>\n" +
		"		<tbody id='Account_ReferAccounts_Table_Body'>\n" +
		"			<tr>\n" +
		"				<td colspan='3' style='text-align:center;'><img width='16px' height='16px' src='../admin/img/template/loading.gif' /></td>\n" +
		"			</tr>\n" +
		"		</tbody>\n" + 
		"	</table>\n" + 
		"</div>\n" + 
		"<div style='margin: 0pt auto; margin-top: 4px; margin-bottom: 4px; width: 100%; text-align: center;'>\n" + 
		"	<input id='Account_ReferAccounts_OK' value='Return to Console' onclick='window.location.href=\"../admin/reflex.php/Console/View/\"' style='margin-left: 3px;' type='button' /> \n" + 
		"</div>\n";
		
		/*Vixen.Popup.Create(
				'Account_ReferAccounts', 
				strHTML, 
				'medium', 
				'centre', 
				'modal', 
				'Debt Collection Account - Please Refer',
				null,
				false
			);*/
		
		var pupPopup	= new Reflex_Popup(40);
		pupPopup.setTitle('Debt Collection Account - Please Refer');
		pupPopup.setContent(strHTML);
		pupPopup.display();
		
		// Perform AJAX query
		var fncJsonFunc		= jQuery.json.jsonFunction(Flex.Account._displayReferAccountsPopupResponse.bind(this), null, 'Account', 'getAccountsReferees');
		fncJsonFunc();
		
		return;
	},
	
	_displayReferAccountsPopupResponse	: function(objResponse)
	{
		var elmTableBody	= $ID('Account_ReferAccounts_Table_Body');
		
		// Did we succeed?
		if (objResponse.Success === false)
		{
			elmTableBody.innerHTML	=	"			<tr>\n" +
										"				<td colspan='3' style='text-align:center;'><strong>There was an error loading the referee list.</strong></td>\n" +
										"			</tr>\n";
			$Alert(objResponse.ErrorMessage);
			return;
		}
		
		// Render Invoice Summry Popup
		var strInnerHTML	=	'';
		
		for (var i = 0; i < objResponse.arrReferees.length; i++)
		{
			strInnerHTML	+=	"			<tr>\n" +
								"				<td style='text-align:left;'>"+objResponse.arrReferees[i].FirstName+" "+objResponse.arrReferees[i].LastName+"</td>\n" +
								"				<td style='text-align:center;'>"+objResponse.arrReferees[i].Extension+"</td>\n" +
								"				<td style='text-align:center;'><a href='mailto:"+objResponse.arrReferees[i].Email+"'><img src='../admin/img/template/email.png' /></a></td>\n" +
								"			</tr>\n";
		}
		
		elmTableBody.innerHTML	= strInnerHTML;
		return;
	}
});

Flex.Account = (Flex.Account == undefined) ? new Account() : Flex.Account;