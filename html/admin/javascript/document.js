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
		
		this._arrEmailAddresses	= new Array();
		
		this._arrEmailAddresses.indexOfAddress	=	function(strAddress)
													{
														for (var i = 0; i < this.length; i++)
														{
															if (this[i].address === strAddress)
															{
																// Exists -- return Index
																return i;
															}
														}
														return -1;
													};
	},
	
	emailDocument	: function(intDocumentId, strDescription, arrFrom, strSubject, strContent, arrEmailAddresses)
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
		
		strSubject		= (strSubject == undefined) ? '' : strSubject;
		strContent		= (strContent == undefined) ? '' : strContent;
		strDescription	= (strDescription == undefined) ? 'Document' : strDescription;
		
		this.pupEmail.setTitle('Email '+strDescription);
		
		// Predefined To's
		var strPredefinedEmails	= ''; 
		this.arrPredefinedEmails	= new Array();
		if (arrEmailAddresses != undefined)
		{
			for (var i = 0; i < arrEmailAddresses.length; i++)
			{
				objEmail		= arrEmailAddresses[i];
				objEmail.name	= (objEmail.name == undefined) ? '' : objEmail.name;
				
				strPredefinedEmails	+= "			<tr>\n";
				strPredefinedEmails	+= "				<td style='width:5%; text-align:right;'><input id='Document_Email_Checkbox_"+(i+1)+"' type='checkbox' value='"+objEmail.address+"' onchange='(this.checked) ? Flex.Document.emailAddressAdd(this.value) : Flex.Document.emailAddressRemove(this.value);' /></td>\n";
				strPredefinedEmails	+= "				<td style='width:30%'>"+objEmail.name+"</td>\n";
				strPredefinedEmails	+= "				<td>"+objEmail.address+"</td>\n";
				strPredefinedEmails	+= "			</tr>\n";
			}
		}
		
		// From Options
		var strFromOptions	= '';
		if (arrFrom != undefined)
		{
			for (var i = 0; i < arrFrom.length; i++)
			{
				objEmail		= arrFrom[i];
				objEmail.name	= (objEmail.name == undefined) ? '' : objEmail.name;
				
				var strLabel	= (objEmail.name) ? objEmail.name+" ("+objEmail.address+")" : objEmail.address;
				strFromOptions	+= "<option value='"+objEmail.address+"'>"+strLabel+"</option>\n";
			}
		}
		else
		{
			throw Exception("No FROM addresses have been specified");
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
		"				<td colspan='2' style='font-weight: bold;font-size: 10pt;text-align:right;'>Add Email Address : </td>\n" +
		"				<td valign='top'><input id='Document_Email_OtherAddress' type='text' size='40' />&nbsp;<img src='../admin/img/template/new.png' title='Add Address' alt='Add Address' onclick='Flex.Document.emailAddressAdd($ID(\"Document_Email_OtherAddress\").value)' style='vertical-align: text-top;' /></td>\n" +  
		"			</tr>\n" + 
		"		</tbody>\n" + 
		"	</table>\n" + 
		"</div>\n" + 
		"<div class='GroupedContent'>\n" + 
		"	<table class='reflex' style='margin-top: 8px; margin-bottom: 8px;' width='100%'>\n" + 
		"		<tbody style=''>\n" + 
		"			<tr>\n" +
		"				<th valign='top' style='font-size: 10pt;text-align: right;' ><nobr>To : </nobr></td>\n" +
		"				<td valign='top'><span id='Document_Email_To'>&lt; No addresses specified &gt;</span></td>\n" +  
		"			</tr>\n" +
		"			<tr>\n" +
		"				<th valign='top' style='font-size: 10pt;text-align: right;' ><nobr>From : </nobr></td>\n" +
		"				<td>\n" +
		"					<select id='Document_Email_From'>\n" +
		strFromOptions +
		"					</select>\n" +
		"				</td>\n" +  
		"			</tr>\n" +
		"			<tr>\n" +
		"				<th valign='top' style='font-size: 10pt;text-align: right;' ><nobr>Subject : </nobr></td>\n" +
		"				<td><input type='text' style='vertical-align: top;' size='50' value='"+strSubject+"' /></td>\n" +  
		"			</tr>\n" +
		"			<tr>\n" +
		"				<th valign='top' style='font-size: 10pt;text-align: right;' ><nobr>Content : </nobr></td>\n" +
		"				<td><textarea style='vertical-align: top;' rows='10' cols='46'>"+strContent+"</textarea></td>\n" +  
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
	},
	
	emailAddressAdd	: function(strAddress, strName, elmCheckbox)
	{
		strAddress.strip();
		
		// Validate the Email Address
		if (strAddress && Vixen.Validation.EmailAddress(strAddress))
		{
			// Valid -- Is the Address already in our list?
			if (this._arrEmailAddresses.indexOfAddress(strAddress) > -1)
			{
				// Yes -- don't add, but return true
				return true;
			}
			else
			{
				// No -- add
				this._arrEmailAddresses.push({
												name		: (strName == undefined) ? '' : strName
												address		: strAddress
												elmCheckbox	: (elmCheckbox == undefined) ? null : elmCheckbox
											});
				this._updateEmailTo();
				$ID('Document_Email_OtherAddress').value	= '';
				return true;
			}
		}
		else
		{
			// Invalid -- Alert the user
			$Alert("'"+strAddress+"' is not a valid email address.");
			return false;
		}
	},
	
	emailAddressRemove	: function(strAddress, strName)
	{
		// Check if this address exists in the array
		var intIndex	= this._arrEmailAddresses.indexOfAddress(strAddress);
		if (intIndex > -1)
		{
			// Exists -- remove
			if (this._arrEmailAddresses[intIndex].elmCheckbox != undefined)
			{
				this._arrEmailAddresses[intIndex].elmCheckbox.checked	= false;
			}
			this._arrEmailAddresses.splice(intIndex, 1);
			this._updateEmailTo();
			return true;
		}
		
		// No matches
		//$Alert("Email '"+strAddress+"' does not exist!");
		return true;
	},
	
	_updateEmailTo	: function()
	{
		elmSpan	= $ID('Document_Email_To');
		
		var strToEmails	= '';
		for (var i = 0; i < this._arrEmailAddresses.length; i++)
		{
			objEmail		= this._arrEmailAddresses[i];
			objEmail.name	= (objEmail.name == undefined) ? '' : objEmail.name;
			
			var strLabel	= (objEmail.name.length) ? objEmail.name+" ("+objEmail.address+")" : objEmail.address;
			var strImg		= "<img onclick='Flex.Document.emailAddressRemove(\""+objEmail.address+"\");' alt='Remove this Address' title='Remove this Address' src='../admin/img/template/delete.png' style='vertical-align: text-top;' />";
			strToEmails	+= "<nobr>"+strLabel+"&nbsp;"+strImg+"</nobr>; \n";
		}
		
		elmSpan.innerHTML	= (strToEmails.length > 0) ? strToEmails : '&lt; No addresses specified &gt;';
		return true;
	}
});

Flex.Document = (Flex.Document == undefined) ? new Document() : Flex.Document;