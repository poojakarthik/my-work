// Class: Telemarketing
// Handles the Telemarketing File Washing page
var Telemarketing	= Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		
	},
	
	iframeFormSubmit	: function(elmForm, funcResponseHandler)
	{
		// Create a hidden IFrame
		var	strIframeId		= elmForm.id + "_submit_" + Math.floor(Math.random() * 99999);
		var elmDiv			= document.createElement('div');
		elmDiv.innerHTML	= "<iframe style='display:none' src='about:blank' id='" + strIframeId + "' name='" + strIframeId + "' onload='Flex.Telemarketing.iframeFormLoaded(this)'></iframe>";
		elmDiv.id			= strIframeId + '_div';
		document.body.appendChild(elmDiv);
		
		// Attach a Response Handler function
		if (typeof(funcResponseHandler) == 'function')
		{
			$ID(strIframeId).funcResponseHandler	= funcResponseHandler;
		}
		
		// Add a target to the form
		elmForm.setAttribute('target', strIframeId);
		
		// Submit the form
		elmForm.submit();
	},
	
	iframeFormLoaded	: function(elmIframe)
	{
		// Parse Iframe contents for response data (JSON'd PHP Array)
		var objIframeDocument	= (elmIframe.contentDocument) ? elmIframe.contentDocument : (elmIframe.contentWindow) ? elmIframe.contentWindow.document : window.frames[elmIframe.id].document;
		var objResponse			= jQuery.json.decode(objIframeDocument.body.innerHTML);
		
		/*for (i in objResponse)
		{
			alert('objResponse.' + i + ' = "' + objResponse[i] + '"');
		}*/
		
		// Destroy the Div and Iframe
		//document.body.removeChild($ID(elmIframe.id + '_div'));
		
		// Call the Handler Function (if one was supplied)
		if (elmIframe.funcResponseHandler != undefined)
		{
			elmIframe.funcResponseHandler(objResponse);
		}
		
		return;
	}
});

// Init
if (Flex.Telemarketing == undefined)
{
	Flex.Telemarketing	= new Telemarketing();
}