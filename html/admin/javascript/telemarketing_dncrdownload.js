// Class: Telemarketing_DNCRDownload
// Handles the Telemarketing DNCR Download page
var Telemarketing_DNCRDownload	= Class.create
({
	objPopupDownload	: {
							strId		: 'Telemarketing_DNCRDownload',
							strSize		: 'large',
							strAlign	: 'centre',
							strNature	: 'modal',
							strTitle	: 'Download DNCR Wash List'
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
		
		if ($ID('Telemarketing_DNCRDownload_File').selectedIndex < 1)
		{
			arrErrors.push("[!] Please select a File to download");
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
		
		// Close the Popup
		Vixen.Popup.Close(this.objPopupDownload.strId);
		
		return true;
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
	
	displayPopupDownload	: function()
	{
		var remoteClass		= 'Telemarketing_Wash';
		var remoteMethod	= 'getImportedFiles';
		var jsonFunc		= jQuery.json.jsonFunction(this._renderPopupDownload.bind(this), null, remoteClass, remoteMethod);
		Vixen.Popup.ShowPageLoadingSplash("Please Wait", null, null, null, 100);
		jsonFunc();
	},
	
	_renderPopupDownload	: function(objResponse)
	{
		this._arrImportedFiles	= objResponse.arrImportedFiles;
		
		// Generate File List
		var strFileListHTML	= '';
		if (!this._arrImportedFiles.each)
		{
			for (i in this._arrImportedFiles)
			{
				var strDealerName	= this._arrImportedFiles[i].dealer_first_name + ((this._arrImportedFiles[i].dealer_last_name) ? ' ' + this._arrImportedFiles[i].dealer_last_name : '');
				strFileListHTML	+= "<option value='" + i + "'>(" + this._arrImportedFiles[i].file_imported_on + ") " + strDealerName + " -- " + this._arrImportedFiles[i].file_name + "</option>\n";
			}
		}
		
		// Generate Popup HTML
		var strHTML	= "\n" +  
		"<form id='Telemarketing_DNCRDownload_Form' name='Telemarketing_DNCRDownload_Form' method='post' action='../admin/reflex.php/Telemarketing/DownloadDNCRWashList/' enctype='multipart/form-data' onsubmit='return Flex.Telemarketing.DNCRDownload.submit()' >\n" +
		"	<div class='GroupedContent'>\n" + 
		"		<table class='form-data' style='width:100%'>\n" + 
		"			<tbody>\n" + 
		"				<tr>\n" + 
		"					<td>File:</td>\n" + 
		"					<td>\n" + 
		"						<select id='Telemarketing_DNCRDownload_File' name='Telemarketing_DNCRDownload_File'>\n" + 
		"							<option value='' selected='selected'>[None]</option>\n" + 
		"							" + strFileListHTML + "\n" + 
		"						</select>\n" + 
		"					</td>\n" + 
		"				</tr>\n" + 
		"			</tbody>\n" + 
		"		</table>\n" + 
		"	</div>\n" + 
		"	<div style='width:100%; margin: 0 auto; text-align:center;'>\n" +
		"		<input type='submit' id='Telemarketing_DNCRDownload_Download' value='Download' style='margin-left:3px' /> \n" +
		"		<input type='button' id='Telemarketing_DNCRDownload_Cancel' value='Cancel' onclick='Vixen.Popup.Close(this)' style='margin-left:3px' /> \n" + 
		"	</div>\n\n" +
		"</form>\n\n"; 
		
		// Render the Popup
		this._renderPopup(this.objPopupDownload, strHTML, objResponse);
	}
});

Flex.Telemarketing.DNCRDownload = (Flex.Telemarketing.DNCRDownload == undefined) ? new Telemarketing_DNCRDownload() : Flex.Telemarketing.DNCRDownload;