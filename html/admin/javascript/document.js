// Class: Document
// Handles the Documents in Flex
var Document	= Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		this.pupEmail	= new Reflex_Popup(50);
		this.pupEmail.setTitle('Email Document');
		this.pupEmail.addCloseButton();
		
		this._arrDocuments		= null;
		this._arrEmailAddresses	= new Array();
		this._arrFromAddresses	= new Array();
		this._intAccountId		= null;
		
		this._objDocumentExplorer	= null;
		
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
	
	emailDocument	: function(arrDocuments, strDescription, arrFrom, strSubject, strContent, arrEmailAddresses, intAccount)
	{
		/*if (arrEmailAddresses == null || arrEmailAddresses == undefined)
		{
			this._scourPageForAccountDetails();
		}*/
		
		this._intAccountId	= (intAccount != undefined) ? intAccount : null;
		
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
				objEmail				= arrEmailAddresses[i];
				objEmail.name			= (objEmail.name == undefined) ? '' : objEmail.name;
				var strNameParameter	= (objEmail.name.length) ? '"'+objEmail.name+'"' : 'null';
				
				var strCheckboxId		= "Document_Email_Checkbox_"+(i+1);
				var strChecked			= '';
				if (objEmail.is_primary_content != undefined && objEmail.is_primary_contact == true)
				{
					strChecked			= "checked='checked'";
					Flex.Document.emailAddressAdd(objEmail.address, objEmail.name, strCheckboxId);
				}
				
				strPredefinedEmails	+= "			<tr>\n";
				strPredefinedEmails	+= "				<td style='width:5%; text-align:right;'><input id='"+strCheckboxId+"' type='checkbox' "+strChecked+" value='"+objEmail.address+"' onchange='(this.checked) ? Flex.Document.emailAddressAdd(this.value, "+strNameParameter+", this.id) : Flex.Document.emailAddressRemove(this.value);' /></td>\n";
				strPredefinedEmails	+= "				<td style='width:30%'>"+objEmail.name+"</td>\n";
				strPredefinedEmails	+= "				<td>"+objEmail.address+"</td>\n";
				strPredefinedEmails	+= "			</tr>\n";
			}
		}
		
		// From Options
		var strFromOptions	= '';
		if (arrFrom != undefined)
		{
			this._arrFromAddresses	= arrFrom;
			for (var i = 0; i < this._arrFromAddresses.length; i++)
			{
				objEmail		= this._arrFromAddresses[i];
				objEmail.name	= (objEmail.name == undefined) ? '' : objEmail.name;
				
				var strLabel	= (objEmail.name) ? objEmail.name+" ("+objEmail.address+")" : objEmail.address;
				strFromOptions	+= "<option value='"+i+"'>"+strLabel+"</option>\n";
			}
		}
		else
		{
			throw "No FROM addresses have been specified";
		}
		
		// Attachments
		var strAttachments	= '';
		if (arrDocuments.length)
		{
			this._arrDocuments	= arrDocuments;
			
			strAttachments	= "\n" +
			"				<tr>\n" +
			"					<th valign='top' style='font-size: 10pt;text-align: right;' ><nobr>Attachments : </nobr></td>\n" +
			"					<td valign='top'>\n" +
			"						<div style='max-height:5em; overflow-y:scroll;'>\n";  
			
			for (var i = 0; i < arrDocuments.length; i++)
			{
				objDocument	= arrDocuments[i];
				
				strAttachments	+= "<nobr><span><img src='../admin/reflex.php/File/Image/FileTypeIcon/"+objDocument.file_type_id+"/16x16' style='vertical-align: text-top;' />&nbsp;"+objDocument.strFileName+"&nbsp;("+objDocument.intFileSizeKB+"KB)</span>;</nobr> ";
			}
			
			strAttachments	+= "\n" +
			"						</div>" +
			"					</td>\n" +
			"				</tr>\n";
		}
		else
		{
			throw "There are no Documents to send!";
		}
		
		// Render Email Popup
		var strHTML	= "\n" + 
		"<div class='GroupedContent'>\n" + 
		"	<div>\n" + 
		"		<span>Please specify the email addresses to send the "+strDescription+" to:</span>\n" + 
		"	</div>\n" + 
		"	<table class='reflex' style='margin-top: 8px; margin-bottom: 8px;' width='100%'>\n" + 
		"		<tbody>\n" + 
		strPredefinedEmails + 
		"			<tr>\n" +
		"				<td colspan='2' style='font-weight: bold;font-size: 10pt;text-align:right;'>Add Email Address : </td>\n" +
		"				<td valign='top'><input id='Document_Email_OtherAddress' type='text' size='30' />&nbsp;<img src='../admin/img/template/new.png' title='Add Address' alt='Add Address' onclick='Flex.Document.emailAddressAdd($ID(\"Document_Email_OtherAddress\").value)' style='vertical-align: text-top;' /></td>\n" +  
		"			</tr>\n" + 
		"		</tbody>\n" + 
		"	</table>\n" + 
		"</div>\n" + 
		"<div class='GroupedContent'>\n" + 
		"	<table class='reflex' style='margin-top: 8px; margin-bottom: 8px;' width='100%'>\n" + 
		"		<tbody style=''>\n" + 
		"			<tr>\n" +
		"				<th valign='top' style='font-size: 10pt;text-align: right;' ><nobr>To : </nobr></td>\n" +
		"				<td valign='top'><span id='Document_Email_To'></span></td>\n" +  
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
		"				<td><input id='Document_Email_Subject' type='text' style='vertical-align: top;' size='35' value='"+strSubject+"' /></td>\n" +  
		"			</tr>\n" +
		strAttachments +
		"			<tr>\n" +
		"				<th valign='top' style='font-size: 10pt;text-align: right;' ><nobr>Content : </nobr></td>\n" +
		"				<td><textarea id='Document_Email_Content' style='vertical-align: top;' rows='10' cols='46'>"+strContent+"</textarea></td>\n" +  
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
		
		this._updateEmailTo();
		
		return;
	},
	
	_emailDocumentSubmit	: function()
	{	
		// Ensure that all fields are populated
		var arrErrors	= new Array();

		if (!this._arrEmailAddresses.length)
		{
			arrErrors.push("[!] Please add at least one TO email address");
		}
		else
		{
			for (var i = 0; i < this._arrEmailAddresses.length; i++)
			{
				if (!Vixen.Validation.EmailAddress(this._arrEmailAddresses[i].address))
				{
					arrErrors.push("[!] '"+this._arrEmailAddresses[i].address+"' is not a valid email address!");
				}
			}
		}
		
		if (!$ID('Document_Email_From').value)
		{
			arrErrors.push("[!] Please select the FROM email address");
		}
		var strSubject	= $ID('Document_Email_Subject').value.replace(/(^\s+|\s+$)/g, '');
		if (!strSubject.length)
		{
			arrErrors.push("[!] Please enter a Subject for the email");
		}
		var strContent	= $ID('Document_Email_Content').value.replace(/(^\s+|\s+$)/g, '');
		if (!strContent.length)
		{
			arrErrors.push("[!] Please enter Content for the email");
		}
		var arrTags	= $ID('Document_Email_Content').value.match(/<[\d\w]+>/mig);
		if (arrTags)
		{
			for (var i = 0; i < arrTags.length; i++)
			{
				var strTag	= arrTags[i].replace(/>/mig, '&gt;');
				strTag		= strTag.replace(/</mig, '&lt;');
				arrErrors.push("[!] The placeholder tag '"+strTag+"' appears in the Content.  Please replace it with its respective value or remove it altogether.");
			}
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
		Vixen.Popup.ShowPageLoadingSplash("Delivering Email...", null, null, null, 1);
		
		// Perform AJAX query
		var fncJsonFunc		= jQuery.json.jsonFunction(Flex.Document._emailDocumentResponse.bind(this), null, 'Document', 'sendEmail');
		fncJsonFunc(this._arrEmailAddresses, this._arrFromAddresses[$ID('Document_Email_From').value], $ID('Document_Email_Subject').value, $ID('Document_Email_Content').value, this._arrDocuments, this._intAccountId);
	},
	
	_emailDocumentResponse	: function(objResponse)
	{
		// Close the Loading Splash & Popup
		Vixen.Popup.ClosePageLoadingSplash();
		this.pupEmail.hide();
		
		// Display response message
		if (objResponse.Success)
		{
			$Alert("Email delivered!");
		}
		else
		{
			$Alert(objResponse.Message);
		}
		Flex.Document.initialize();
	},
	
	emailAddressAdd	: function(strAddress, strName, strCheckboxId)
	{
		strAddress	= strAddress.replace(/(^\s+|\s+$)/g, '');
		
		// Validate the Email Address
		if (!strAddress.length)
		{
			return false;
		}
		else if (strAddress && Vixen.Validation.EmailAddress(strAddress))
		{
			// Valid -- Is the Address already in our list?
			if (this._arrEmailAddresses.indexOfAddress(strAddress) > -1)
			{
				// Yes -- don't add, but return true;
				$ID('Document_Email_OtherAddress').value	= '';
				return true;
			}
			else
			{
				// No -- add
				this._arrEmailAddresses.push({
												name			: (strName == undefined) ? '' : strName,
												address			: strAddress,
												strCheckboxId	: (strCheckboxId == undefined) ? null : strCheckboxId
											});
				this._updateEmailTo();
				$ID('Document_Email_OtherAddress').value	= (strCheckboxId == undefined) ? '' : $ID('Document_Email_OtherAddress').value;
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
	
	emailAddressRemove	: function(strAddress)
	{
		// Check if this address exists in the array
		var intIndex	= this._arrEmailAddresses.indexOfAddress(strAddress);
		if (intIndex > -1)
		{
			// Exists -- remove
			if (this._arrEmailAddresses[intIndex].strCheckboxId != undefined)
			{
				$ID(this._arrEmailAddresses[intIndex].strCheckboxId).checked	= false;
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
		
		if (elmSpan)
		{
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
		else
		{
			return false;
		}
	},
	
	initDocumentExplorer	: function()
	{
		JsAutoLoader.loadScript('javascript/document.js', function(){Flex.Document._objDocumentExplorer	= new Document_Explorer();});
	},
	
	byteConvert : function(intBytes, strPower)
	{
		var fltConvert	= intBytes;
		var arrPowers	= new Array('B', 'K', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y');
		for (var i = 0; i < arrPowers.length; i++)
		{
			if (arrPowers[i] == strPower.charAt(0).toUpperCase())
			{
				return fltConvert;
			}
			fltConvert	= fltConvert / 1024;
		}
		throw "Unknown power of Bytes: '"+strPower+"'";
	},
	
	byteRound	: function(intBytes, intDecimalPlaces)
	{
		intDecimalPlaces	= (intDecimalPlaces > 0) ? intDecimalPlaces : 0;
		intRoundFactor		= Math.pow(10, intDecimalPlaces);
		
		var fltConvert	= intBytes;
		var arrPowers	= new Array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		for (var i = 0; i < arrPowers.length; i++)
		{
			var fltConvertTemp	= fltConvert / 1024;			
			if (fltConvertTemp < 1)
			{
				return (Math.round(fltConvert*intRoundFactor)/intRoundFactor).toString()+" "+arrPowers[i];
			}
			else if (i == arrPowers.length-1)
			{
				return (Math.round(fltConvertTemp*intRoundFactor)/intRoundFactor).toString()+" "+arrPowers[i];
			}
			fltConvert	= fltConvertTemp;
		}
	},
	
	_scourPageForAccountDetails	: function()
	{
		// Attempt to find Account information on this page
		var intServiceId	= null;
		
		if (Vixen.AccountDetails)
		{
			intAccountId	= Vixen.AccountDetails.intAccountId;
		}
		else if (Vixen.AccountContactsList)
		{
			intAccountId	= Vixen.AccountContacts.intAccountId;
		}
		else if (Vixen.AccountServices)
		{
			intAccountId	= Vixen.AccountServices.intAccountId;
		}
		else if (Vixen.ServiceBulkAdd)
		{
			intAccountId	= Vixen.ServiceBulkAdd.intAccountId;
		}
		else if (Vixen.ProvisioningPage)
		{
			intAccountId	= Vixen.ProvisioningPage.intAccountId;
		}
		else if (Vixen.ProvisioningHistoryList)
		{
			intAccountId	= Vixen.ProvisioningHistoryList.intAccountId;
		}
		else if (Vixen.NoteList)
		{
			intAccountId	= (Vixen.NoteList.intAccountId) ? Vixen.NoteList.intAccountId : 'null';
			intServiceId	= (Vixen.NoteList.intServiceId) ? Vixen.NoteList.intServiceId : null;
		}
		
		// Retrieve Account Details
		if (intAccountId != 'null')
		{
			// Retrieve Contact Details by Account Id
			//(jQuery.json.jsonFunction(Flex.Document.emailDocument.bind(Flex.Document.Explorer), null, "Document", "delete"))(objChild.id);
		}
		else if (intServiceId)
		{
			// Retrieve Contact Details by Service Id
		}
	}
});

Flex.Document = (Flex.Document == undefined) ? new Document() : Flex.Document;