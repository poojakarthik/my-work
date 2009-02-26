// Class: Telemarketing
// Handles the Telemarketing File Washing page
var Telemarketing	= Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		this.pupBlackListAdd	= null;
	},
	
	iframeFormSubmit	: function(elmForm, funcResponseHandler)
	{
		// Create a hidden IFrame
		var	strIframeId			= elmForm.id + "_iframe";
		
		var elmDiv				= document.createElement('div');
		elmDiv.id				= strIframeId + '_div';
		elmDiv.style.visibility	= 'hidden';
		document.body.appendChild(elmDiv);

		var elmIframe				= document.createElement('iframe');
		elmIframe.id				= strIframeId;
		elmIframe.name				= strIframeId;
		elmIframe.setAttribute('onload', 'Flex.Telemarketing.iframeFormLoaded(this)');
		elmIframe.style.visibility	= 'hidden';
		elmDiv.appendChild(elmIframe);
		
		// Schedule Iframe onLoad Polling
		//var objIframeDocument	= (elmIframe.contentDocument) ? elmIframe.contentDocument : (elmIframe.contentWindow) ? elmIframe.contentWindow.document : window.frames[elmIframe.id].document;
		//objIframeDocument.onreadystatechange	= this.iframeFormLoaded.bind(this, elmIframe);
		//setTimeout(this.iframeFormLoaded.bind(this, elmIframe), 100);
		
		// Attach a Response Handler function
		if (typeof(funcResponseHandler) == 'function')
		{
			elmIframe.funcResponseHandler	= funcResponseHandler;
		}
		
		// Add a target to the form
		elmForm.target			= elmIframe.id;
		
		return true;
	},
	
	iframeFormLoaded	: function(elmIframe)
	{
		var objIframeDocument	= (elmIframe.contentDocument) ? elmIframe.contentDocument : (elmIframe.contentWindow) ? elmIframe.contentWindow.document : window.frames[elmIframe.id].document;
		
		/*alert(objIframeDocument.location);
		
		if (!objIframeDocument.body.location)
		{
			// Reschedule Iframe onLoad Polling
			alert("No content yet");
			setTimeout(this.iframeFormLoaded.bind(this, elmIframe), 1000);
			return false;
		}*/
		
		// Parse Iframe contents for response data (JSON'd PHP Array)
		var objResponse			= jQuery.json.decode(objIframeDocument.body.innerHTML);
		objResponse				= (objResponse) ? objResponse : {Message: objIframeDocument.body.innerHTML};
		
		/*for (i in objResponse)
		{
			alert('objResponse.' + i + ' = "' + objResponse[i] + '"');
		}*/
		
		// Call the Handler Function (if one was supplied)
		if (elmIframe.funcResponseHandler != undefined)
		{
			elmIframe.funcResponseHandler(objResponse);
		}
		
		// Schedule Iframe Cleanup
		setTimeout(this._iframeCleanup.bind(this, elmIframe), 100);
		//this._iframeCleanup(elmIframe);
		
		elmIframe.bolLoaded	= true;
	},
	
	_iframeCleanup		: function(elmIframe)
	{
		// If the IFrame exists and is loaded, then remove it
		if ($ID(elmIframe.id) && elmIframe.bolLoaded)
		{
			// Destroy the Div and Iframe
			document.body.removeChild($ID(elmIframe.id + '_div'));
		}
		else
		{
			// Otherwise schedule another cleanup
			setTimeout(this._iframeCleanup.bind(this, elmIframe), 100);
		}
	},
	
	// addFNNToBlacklist
	addFNNToBlacklist	: function(strFNN)
	{
		if (strFNN == undefined)
		{
			// Display the input popup
			var strHTML	=	"<div class='GroupedContent'>\n" +
							"	<table class='relfex'>\n" +
							"		<tr>\n" +
							"			<th style='font-size:10pt; vertical-align:middle;'>Service FNN : </th>\n" +
							"			<td><input id='Telemarketing_Blacklist_FNN' type='text' maxlength='10' size='20' /></td>\n" +
							"		</tr>\n" +
							"	</table>\n" +
							"</div>\n" + 
							"<div style='margin: 0pt auto; margin-top: 4px; margin-bottom: 4px; width: 100%; text-align: center;'>\n" + 
							"	<input id='Telemarketing_Blacklist_Add' value='Add' type='button' onclick='Flex.Telemarketing.addFNNToBlacklist($ID(\"Telemarketing_Blacklist_FNN\").value)' /> \n" + 
							"	<input id='Telemarketing_Blacklist_Cancel' value='Cancel' onclick='Flex.Telemarketing.pupBlackListAdd.hide();' style='margin-left: 3px;' type='button' /> \n" + 
							"</div>\n";
			
			this.pupBlackListAdd	= new Reflex_Popup(25);
			this.pupBlackListAdd.setTitle("Add FNN to Telemarketing Blacklist");
			this.pupBlackListAdd.addCloseButton();
			this.pupBlackListAdd.setContent(strHTML);
			this.pupBlackListAdd.display();
		}
		else
		{
			// Validate
			if (Vixen.Validation.fnn(strFNN))
			{
				// Valid
				$Alert("FNN is valid");
			}
			else
			{
				$Alert("FNN is invalid");
			}
		}
	}
});

// Init
if (Flex.Telemarketing == undefined)
{
	Flex.Telemarketing	= new Telemarketing();
}