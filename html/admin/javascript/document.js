// Class: Document
// Handles the Documents in Flex
var Document	= Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		this.pupEmail	= new Reflex_Popup(40);
		this.pupEmail.setTitle('Email Document');
		this.pupEmail.addCloseButton();
	},
	
	emailDocument	: function(intDocumentId, strDescription, arrEmailAddresses)
	{
		// DEBUG
		/**/
		arrEmailAddresses	= new Array();
		
		arrEmailAddresses.push(	{
									name	: 'Rich Davis',
									address	: 'rdavis@ybs.net.au'
								});
		arrEmailAddresses.push(	{
									name	: 'Mark Sergeant',
									address	: 'msergeant@ybs.net.au'
								});
		arrEmailAddresses.push(	{
									name	: 'Rich Davis',
									address	: 'turdminator@hotmail.com'
								});
		/**/
		
		strDescription	= (strDescription != undefined) ? strDescription : 'Document';
		
		var strPredefinedEmails	= ''; 
		this.arrPredefinedEmails	= new Array();
		if (arrEmailAddresses != undefined)
		{
			for (var i = 0; i < arrEmailAddresses.length; i++)
			{
				objEmail		= arrEmailAddresses[i];
				objEmail.name	= (objEmail.name == undefined) ? '&nbsp;' : objEmail.name;
				
				strPredefinedEmails	+= "			<tr>\n";
				strPredefinedEmails	+= "				<td style='width:5%; text-align:right;'><input id='Document_Email_Checkbox_"+(i+1)+"' type='checkbox' /></td>\n";
				strPredefinedEmails	+= "				<td style='width:30%'>"+objEmail.name+"</td>\n";
				strPredefinedEmails	+= "				<td>"+objEmail.address+"</td>\n";
				strPredefinedEmails	+= "			</tr>\n";
			}
		}
		
		// Render Email Popup
		var strHTML	= "\n" + 
		"<div class='GroupedContent'>\n" + 
		"	<div>\n" + 
		"		<span>Please specify the email addresses to send this "+strDescription+" to:</span>\n" + 
		"	</div>\n" + 
		"	<table class='reflex' style='margin-top: 8px; margin-bottom: 8px;' width='100%'>\n" + 
		"		<tbody>\n" + 
		strPredefinedEmails + 
		"			<tr>\n" +
		"				<td colspan='2' style='font-weight: bold;font-size: 10pt;text-align:right;'>Other Email Address(es) : </td>\n" +
		"				<td><input id='Document_Email_OtherAddress' type='text' size='40' /></td>\n" +  
		"			</tr>\n" + 
		"		</tbody>\n" + 
		"	</table>\n" + 
		"</div>\n" + 
		"<div class='GroupedContent'>\n" + 
		"	<table class='reflex' style='margin-top: 8px; margin-bottom: 8px;' width='100%'>\n" + 
		"		<tbody style=''>\n" + 
		"			<tr>\n" +
		"				<th valign='top' style='font-size: 10pt;text-align: right;' >To : </td>\n" +
		"				<td valign='top'><span id='Document_Email_To'></span></td>\n" +  
		"			</tr>\n" +
		"			<tr>\n" +
		"				<th valign='top' style='font-size: 10pt;text-align: right;' >From : </td>\n" +
		"				<td><span style='vertical-align: top;'></span></td>\n" +  
		"			</tr>\n" +
		"			<tr>\n" +
		"				<th valign='top' style='font-size: 10pt;text-align: right;' >Subject : </td>\n" +
		"				<td><input type='text' style='vertical-align: top;' size='50' /></td>\n" +  
		"			</tr>\n" +
		"			<tr>\n" +
		"				<th valign='top' style='font-size: 10pt;text-align: right;' >Content : </td>\n" +
		"				<td><textarea style='vertical-align: top;' rows='10' cols='40'></textarea></td>\n" +  
		"			</tr>\n" + 
		"		</tbody>\n" + 
		"	</table>\n" + 
		"</div>\n" + 
		"<div style='margin: 0pt auto; margin-top: 4px; margin-bottom: 4px; width: 100%; text-align: center;'>\n" + 
		"	<input id='Plan_SetBrochure_Submit' value='Send' type='button' onclick='Flex.Document._emailDocumentSubmit();' /> \n" + 
		"	<input id='Plan_SetBrochure_Cancel' value='Cancel' onclick='Flex.Document.pupEmail.hide();' style='margin-left: 3px;' type='button' /> \n" + 
		"</div>\n";
		
		this.pupEmail.setContent(strHTML);
		this.pupEmail.display();
		
		return;
	},
	
	_emailDocumentSubmit	: function()
	{
		$Alert("Not implemented yet!");
		return false;
		
		// Ensure that all fields are populated
		var arrErrors	= new Array();
		
		if (!$ID('Plan_SetBrochure_File').value)
		{
			arrErrors.push("[!] Please select a valid PDF Brochure file to upload");
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
		Vixen.Popup.ShowPageLoadingSplash("Uploading Plan Brochure...", null, null, null, 100);

		// Perform AJAX query
		return jQuery.json.jsonIframeFormSubmit($ID('Plan_SetBrochure_Form'), Flex.Plan._setBrochureResponse.bind(this));
	},
	
	_emailDocumentResponse	: function(objResponse)
	{
		// Close the Loading Splash & Popup
		Vixen.Popup.ClosePageLoadingSplash();
		this.pupEmail.hide();
		
		// Display response message
		if (objResponse.Success)
		{
			$Alert("The Brochure was successfully uploaded", null, null, 'autohide-reload');
		}
		else
		{
			$Alert(objResponse.Message);
		}
	}
});

Flex.Document = (Flex.Document == undefined) ? new Document() : Flex.Document;