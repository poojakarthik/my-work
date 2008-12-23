// Class: Telemarketing_PermittedDownload
// Handles the Telemarketing DNCR Download page
var Telemarketing_PermittedDownload	= Class.create
({
	objPopupDownload	: {
							strId		: 'Telemarketing_PermittedDownload',
							strSize		: 'large',
							strAlign	: 'centre',
							strNature	: 'modal',
							strTitle	: 'Download Permitted Dialling List'
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
		
		if ($ID('Telemarketing_PermittedDownload_File').selectedIndex < 1)
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
		
		// Show the Loading Splash
		//Vixen.Popup.ShowPageLoadingSplash("Processing DNCR Wash List...", null, null, null, 100);
		
		// Perform AJAX query
		return true;
		//return Flex.Telemarketing.iframeFormSubmit($ID('Telemarketing_PermittedDownload_Form'), this.downloadReponseHandler.bind(this));
	},
	
	downloadReponseHandler	: function(objResponse)
	{
		// Close the Splash and Popup
		Vixen.Popup.ClosePageLoadingSplash();
		Vixen.Popup.Close(this.objPopupDownload.strId);
		
		// Display confirmation popup
		if (objResponse.Message)
		{
			$Alert("<pre>"+objResponse.Message+"</pre>", null, null, 'modal');
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
	
	displayPopupDownload	: function()
	{
		var remoteClass		= 'Telemarketing_Wash';
		var remoteMethod	= 'getExportReadyFiles';
		var jsonFunc		= jQuery.json.jsonFunction(this._renderPopupDownload.bind(this), null, remoteClass, remoteMethod);
		Vixen.Popup.ShowPageLoadingSplash("Please Wait", null, null, null, 100);
		jsonFunc();
	},
	
	_renderPopupDownload	: function(objResponse)
	{
		if (objResponse.HasPermissions === false)
		{
			$Alert("You do not have sufficient privileges to download a Permitted Dialling List.");
			return false;
		}
		
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
		"<form id='Telemarketing_PermittedDownload_Form' name='Telemarketing_PermittedDownload_Form' method='post' action='../admin/reflex.php/Telemarketing/DownloadPermittedDiallingList/' enctype='multipart/form-data' onsubmit='return Flex.Telemarketing.PermittedDownload.submit()' >\n" +
		"	<div class='GroupedContent'>\n" + 
		"		<table class='form-data' style='width:100%'>\n" + 
		"			<tbody>\n" + 
		"				<tr>\n" + 
		"					<td>File:</td>\n" + 
		"					<td>\n" + 
		"						<select id='Telemarketing_PermittedDownload_File' name='Telemarketing_PermittedDownload_File'>\n" + 
		"							<option value='' selected='selected'>[None]</option>\n" + 
		"							" + strFileListHTML + "\n" + 
		"						</select>\n" + 
		"					</td>\n" + 
		"				</tr>\n" + 
		"			</tbody>\n" + 
		"		</table>\n" + 
		"	</div>\n" + 
		"	<div style='width:100%; margin: 0 auto; text-align:center;'>\n" +
		"		<input type='submit' id='Telemarketing_PermittedDownload_Download' value='Download' style='margin-left:3px' /> \n" +
		"		<input type='button' id='Telemarketing_PermittedDownload_Cancel' value='Close' onclick='Vixen.Popup.Close(this)' style='margin-left:3px' /> \n" + 
		"	</div>\n\n" +
		"</form>\n\n"; 
		
		// Render the Popup
		this._renderPopup(this.objPopupDownload, strHTML, objResponse);
	}
});

Flex.Telemarketing.PermittedDownload = (Flex.Telemarketing.PermittedDownload == undefined) ? new Telemarketing_PermittedDownload() : Flex.Telemarketing.PermittedDownload;