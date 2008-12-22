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
		var	strIframeId			= elmForm.id + "_iframe";
		
		var elmDiv				= document.createElement('div');
		elmDiv.id				= strIframeId + '_div';
		elmDiv.style.visibility	= 'hidden';
		document.body.appendChild(elmDiv);

		var elmIframe				= document.createElement('iframe');
		elmIframe.id				= strIframeId;
		elmIframe.name				= strIframeId;
		//elmIframe.setAttribute('onload', 'Flex.Telemarketing.iframeFormLoaded(this)');
		elmIframe.style.visibility	= 'hidden';
		elmDiv.appendChild(elmIframe);
		
		// Schedule Iframe onLoad Polling
		setTimeout(this.iframeFormLoaded.bind(this, elmIframe), 100);
		
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
		if (window.frames[elmIframe.id].document.readyState !== 4)
		{
			alert(window.frames[elmIframe.id].document.readyState);
			
			// Reschedule Iframe onLoad Polling
			setTimeout(this.iframeFormLoaded.bind(this, elmIframe), 1000);
			return false;
		}
		
		// Parse Iframe contents for response data (JSON'd PHP Array)
		var objIframeDocument	= (elmIframe.contentDocument) ? elmIframe.contentDocument : (elmIframe.contentWindow) ? elmIframe.contentWindow.document : window.frames[elmIframe.id].document;
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
		//setTimeout(this._iframeCleanup.bind(this, elmIframe), 100);
		this._iframeCleanup(elmIframe);
		
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
	}
});

// Init
if (Flex.Telemarketing == undefined)
{
	Flex.Telemarketing	= new Telemarketing();
}