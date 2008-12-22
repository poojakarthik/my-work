// Class: Telemarketing_DNCRUpload
// Handles the Telemarketing DNCR Upload page
var Telemarketing_DNCRUpload	= Class.create
({
	objPopupUpload	: {
							strId		: 'Telemarketing_DNCRUpload',
							strSize		: 'medium',
							strAlign	: 'centre',
							strNature	: 'modal',
							strTitle	: 'Upload DNCR Wash List'
						},
	
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		
	},
	
	submit			: function()
	{
		// Ensure that all fields are populated
		var arrErrors	= new Array();
		
		if ($ID('Telemarketing_DNCRUpload_File').selectedIndex < 1)
		{
			arrErrors.push("[!] Please select a File to Upload");
		}
		
		if (arrErrors.length)
		{
			var strError	= "There is an error with your input.  Please satisfy the following requirements before submitting again:<br />";
			for (i = 0; i < arrErrors.length; i++)
			{
				strError	+=  "<br />" + arrErrors[i];
			}
			$Alert(strError);
			return false;
		}
		
		// Show the Loading Splash
		Vixen.Popup.ShowPageLoadingSplash("Uploading DNCR Wash list...", null, null, null, 100);
		
		// Perform AJAX query
		return Flex.Telemarketing.iframeFormSubmit($ID('Telemarketing_DNCRUpload_Form'), this.uploadReponseHandler.bind(this));
	},
	
	uploadReponseHandler	: function(objResponse)
	{
		// Close the Splash and Popup
		Vixen.Popup.ClosePageLoadingSplash();
		Vixen.Popup.Close(this.objPopupUpload.strId);
		
		// Display confirmation popup
		if (objResponse.Message)
		{
			$Alert(objResponse.Message, null, null, 'modal');
		}
		return;
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
			$Alert("Failed to open the '" + objPopup.strTitle + "' popup" + ((objResponse.ErrorMessage != undefined)? "<br />" + objResponse.ErrorMessage : ""), 'extralarge');
		}
	},
	
	displayPopupUpload	: function()
	{
		this._renderPopupUpload({Success: true});
	},
	
	_renderPopupUpload	: function(objResponse)
	{		
		// Generate Popup HTML
		var strHTML	= "\n" +  
		"<form id='Telemarketing_DNCRUpload_Form' name='Telemarketing_DNCRUpload_Form' method='post' action='../admin/reflex.php/Telemarketing/UploadDNCRWashList/' enctype='multipart/form-data' onsubmit='return Flex.Telemarketing.DNCRUpload.submit()' >\n" +
		"	<div class='GroupedContent'>\n" + 
		"		<table class='form-data' style='width:100%'>\n" + 
		"			<tbody>\n" + 
		"				<tr>\n" + 
		"					<td>File to Upload:</td>\n" + 
		"					<td>\n" + 
		"						<input type='file' id='Telemarketing_DNCRUpload_File' name='Telemarketing_DNCRUpload_File' />\n" + 
		"					</td>\n" + 
		"				</tr>\n" + 
		"			</tbody>\n" + 
		"		</table>\n" + 
		"	</div>\n" + 
		"	<div style='width:100%; margin: 0 auto; text-align:center;'>\n" +
		"		<input type='submit' id='Telemarketing_DNCRUpload_Upload' value='Upload' style='margin-left:3px' /> \n" +
		"		<input type='button' id='Telemarketing_DNCRUpload_Cancel' value='Cancel' onclick='Vixen.Popup.Close(this)' style='margin-left:3px' /> \n" + 
		"	</div>\n\n" +
		"</form>\n\n"; 
		
		// Render the Popup
		this._renderPopup(this.objPopupUpload, strHTML, objResponse);
	}
});

Flex.Telemarketing.DNCRUpload = (Flex.Telemarketing.DNCRUpload == undefined) ? new Telemarketing_DNCRUpload() : Flex.Telemarketing.DNCRUpload;