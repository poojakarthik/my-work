// Ensure that telemarketing.js Parent Class has been loaded
JsAutoLoader.loadScript('javascript/telemarketing.js', function(){Flex.Telemarketing.ProposedUpload = (Flex.Telemarketing.ProposedUpload == undefined) ? new Telemarketing_ProposedUpload() : Flex.Telemarketing.ProposedUpload});

// Class: Telemarketing
// Handles the Telemarketing Proposed FNN Upload page
var Telemarketing_ProposedUpload	= Class.create
({
	objPopupUpload	: {
						strId		: 'Telemarketing_ProposedUpload',
						strSize		: 'medium',
						strAlign	: 'centre',
						strNature	: 'modal',
						strTitle	: 'Upload Proposed Dialling List'
					},
	
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		
	},
	
	updatePermittedVendors	: function()
	{
		intCallCentre	= $('Telemarketing_ProposedUpload_Dealer').value;
		if (this._arrCallCentrePermissions[intCallCentre])
		{
			for (var i = 0; i < this._arrCallCentrePermissions[intCallCentre].length; i++)
			{
				
			}
		}
	},
	
	_renderPopup	: function(objPopup, strHTML, objResponse)
	{
		
		// Hide the 'Loading' Splash, and display the popup
		Vixen.Popup.ClosePageLoadingSplash();
		if (objResponse.Success)
		{
			// Render the popup
			Vixen.Popup.Create(
								objPopup.strId, 
								strHTML, 
								objPopup.strSize, 
								objPopup.strAlign, 
								objPopup.strNature, 
								objPopup.strTitle
							);
		}
		else
		{
			$Alert("Failed to open the '" + objPopup.strTitle + "' popup" + ((objResponse.ErrorMessage != undefined)? "<br />" + objResponse.ErrorMessage : ""));
		}
	},
	
	displayPopupUpload	: function()
	{
		alert("Displaying the Popup");
		
		var remoteClass		= 'Telemarketing_Wash';
		var remoteMethod	= 'getCallCentrePermissions';
		var jsonFunc		= jQuery.json.jsonFunction(this._renderPopup.bind(this), null, remoteClass, remoteMethod);
		Vixen.Popup.ShowPageLoadingSplash("Please Wait", null, null, null, 100);
		jsonFunc();
	},
	
	_renderPopupUpload	: function(strPopupName, objResponse)
	{
		var hashCallCentrePermissions	= objResponse.arrCallCentrePermissions;
		for (i in hashCallCentrePermissions)
		{
			alert(hashCallCentrePermissions[i]);
		}
		
		// Generate 
		
		// Generate Popup HTML
		strHTML	= "\n" + 
		"<form method='post' action='' enctype='multipart/form-data'>\n" + 
		"	<div class='GroupedContent'>\n" + 
		"		<table class='form-data' style='width:100%'>\n" + 
		"			<tbody>\n" + 
		"				<tr>\n" + 
		"					<td>Dealer:</td>\n" + 
		"					<td>\n" + 
		"						<select id='Telemarketing_ProposedUpload_Dealer' name='dealer_id'>\n" + 
		"							<option value='' selected='selected'>[None]</option>\n" + 
		"							" + strDealerListHTML + "\n" + 
		"						</select>\n" + 
		"					</td>\n" + 
		"				</tr>\n" + 
		"				<tr>\n" + 
		"					<td>Vendor:</td>\n" + 
		"					<td>\n" + 
		"						<select id='Telemarketing_ProposedUpload_Vendor' name='vendor_id'>\n" + 
		"							<option value='' selected='selected'>[None]</option>\n" + 
		"						</select>\n" + 
		"					</td>\n" + 
		"				</tr>\n" + 
		"				<tr>\n" + 
		"					<td>File to wash:</td>\n" + 
		"					<td>\n" + 
		"						<input type='file' id='Telemarketing_ProposedUpload_File' name='file_data' />\n" + 
		"					</td>\n" + 
		"				</tr>\n" + 
		"			</tbody>\n" + 
		"		</table>\n" + 
		"	</div>\n" + 
		"	<div style='width:100%; margin: 0 auto; text-align:center;'>\n" + 
		"		<input type='button' id='Telemarketing_ProposedUpload_Submit' value='Submit' onclick='Flex.Telemarketing.ProposedList.submit()' style='margin-left:3px'></input> \n" + 
		"		<input type='button' id='Telemarketing_ProposedUpload_Cancel' value='Cancel' onclick='Vixen.Popup.Close(this)' style='margin-left:3px'></input>\n" + 
		"	</div>\n" + 
		"</form>\n\n";
		
		// Render the Popup
		this._renderPopup(this.objPopupUpload, strHTML, objResponse);
	}
});