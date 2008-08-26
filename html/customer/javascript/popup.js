//----------------------------------------------------------------------------//
// VixenPopupClass
//----------------------------------------------------------------------------//
/**
 * VixenPopupClass
 *
 * Vixen popup class
 *
 * Vixen popup class
 *
 *
 *
 * @package	ui_app
 * @class	Vixen.Popup
 */
function VixenPopupClass()
{
	this.strContentCode						= "";
	this.strLocationOnClose					= "";
	this.intTimeoutIdForPageLoadingSplash	= null;
	
	// This stores a stack of zIndex values of the overlay div for each popup openned modally so 
	// that we can keep a track of where to place the div overlay, when a modal
	// popup is closed, but there are still modal popups on the screen
	this.arrOverlayZIndexHistory = new Array;
	
	this.ViewContentCode = function()
	{
		//Vixen.debug = TRUE;
		//debug(this.strSourceCode);
		DebugWindow = window.open("", 'Debug Mode', 'scrollbars=yes');
		DebugWindow.document.write('<xmp>');
		DebugWindow.document.write(this.strContentCode);
		DebugWindow.document.write('</xmp>');
		DebugWindow.document.close();
	}

	// Returns TRUE if the popup exists, else FALSE
	this.Exists = function(strId)
	{
		elmExists = this.GetPopupElement(strId);
		if (elmExists)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	// Returns TRUE if there are popups present on the page
	this.PopupsExist = function()
	{
		var elmPopupContainer = document.getElementById("PopupHolder");
		
		if (elmPopupContainer.childNodes.length != 0)
		{
			// Popups exist
			return TRUE;
		}
		else
		{
			// There are no popups
			return FALSE;
		}
	}
	
	
	// Returns the popup element identified by strId
	// Returns null if the popup cannot be found
	this.GetPopupElement = function(strId)
	{
		return document.getElementById('VixenPopup__' + strId);
	}
	
	this.SetContent = function(strId, strContent)
	{
		//check that the popup exists; if it doesn't then return false
		if (!(this.Exists(strId)))
		{
			return FALSE;
		}
		
		// Retrieve the current popup content element
		var elmOldPopupContent = document.getElementById("VixenPopupContent__" + strId);

		// create a new one which will replace the old one
		var elmNewPopupContent = document.createElement('div');
		elmNewPopupContent.setAttribute('Id', 'VixenPopupContent__' + strId);
		
		// Set the content of the popup box
		if (!strContent)
		{
			strContent = "No data<br />Id: " + strId;
		}
				
		// Add the popup to the holder
		//elmPopup.style.visibility = 'visible';
		elmNewPopupContent.innerHTML = strContent;

		// Retrieve the container div of the VixenPopupContent__ div
		var elmPopupContainer = elmOldPopupContent.parentNode;
		
		// Remove the old content div and add the new one
		elmPopupContainer.removeChild(elmOldPopupContent);
		elmPopupContainer.appendChild(elmNewPopupContent);
		
		// Save the new content
		this.strContentCode = strContent;
		return TRUE;
	}
	
	this.Create = function(strId, strContent, strSize, mixPosition, strModal, strTitle, strLocationOnClose)
	{
		// set the location to relocate to, when the popup is closed.
		// If null, then a page reload is not performed
		// currently this only works when strModal == autohide 

		this.strLocationOnClose = strLocationOnClose;
		
		// If the title isn't specified then use the application name
		strTitle = (strTitle == null) ? VIXEN_APPLICATION_NAME : strTitle;
		// Try to find a previous popup
		elmExists = document.getElementById('VixenPopup__' + strId);
		if (elmExists)
		{
			// destroy it . . .
			elmExists.parentNode.removeChild(elmExists);
		}
		
		// . . . and create it
		elmPopup = document.createElement('div');
		elmPopup.setAttribute('className', 'PopupBox');
		elmPopup.setAttribute('class', 'PopupBox');
		elmPopup.setAttribute('Id', 'VixenPopup__' + strId);
		
		// Set the content of the popup box
		if (!strContent)
		{
			strContent = "No data<br />Id: " + strId;
		}
				
		this.strContentCode = strContent;
		
		strContent = 	"<div id='VixenPopupTopBar__" + strId + "' class='PopupBoxTopBar'>" +
						"<img src='img/template/close.png' class='PopupBoxClose' onclick='Vixen.Popup.Close(\"" + strId + "\")'>";
		
		// only display the debug button if we are operating in debug mode
		if (DEBUG_MODE)
		{
			strContent += "<img src='img/template/debug.png' class='PopupBoxClose' onclick='Vixen.Popup.ViewContentCode()'>";
		}
		
		strContent += 	"<div id='VixenPopupTopBarTitle__" + strId + "'>" + strTitle + "</div></div>\n" +
						"<div id='VixenPopupContent__" + strId + "'>\n" + this.strContentCode + "</div>\n";
		
		// initially hide the popup
		elmPopup.style.visibility = 'hidden';
		
		// set the content of the popup
		elmPopup.innerHTML = strContent;

		// set the top of the popup to the body.scrollTop, so that it doesn't move the page when it is added to it
		elmPopup.style.top	= document.body.scrollTop;

		// Add the popup to the PopupHolder element
		elmRoot = document.getElementById('PopupHolder');
		elmRoot.appendChild(elmPopup);

		//Going to run into some problems when having multiple popups
		// on a single page, especially of different types
		//  -think this is fixed, havent comprehensively tested though
		
		// Set the behaviour (modal/non-modal/autohide)
		switch (strModal)
		{
			case "modal":
			{				
				// Create a div to capture all events
				
				// But first check if this overlay div already exists
				var  elmOverlay = document.getElementById("overlay");
				if (elmOverlay == null)
				{
					// the overlay div does not currently exist, so create it
					elmOverlay = document.createElement('div');
					elmOverlay.setAttribute('Id', 'overlay');
				}
				else
				{
					// record the current zIndex of the elmOverlay
					this.arrOverlayZIndexHistory.push(elmOverlay.style.zIndex);
				}
				
				elmOverlay.style.zIndex = ++dragObj.zIndex;
				
				intScroll = document.body.scrollTop;
				//intScrollLeft = document.body.scrollLeft;
                //document.body.style.overflow = "hidden";
               	//document.body.scrollTop = intScroll;
				

				
                elmOverlay.style.height	= Math.max(document.body.offsetHeight, window.innerHeight);
				
				// all of these return width of browser, with or without scrollbars
				// document.body.offsetWidth does not return the width of the document (like it does with the height)
				//alert(window.innerWidth);
				//alert(document.body.offsetWidth);
				//alert(document.body.clientWidth);
				
				// Find the width of the actual page by using the PageBody div, and adding its own width
				// to the offset from the left side of the page (needs to include margins?)
				var divPageBody = document.getElementById("PageBody");
				var intPageWidth = divPageBody.offsetWidth + divPageBody.offsetLeft;
				
				//var intPageWidth = parseInt(divPageBody.style.marginLeft) + parseInt(divPageBody.offsetWidth) + parseInt(divPageBody.offsetLeft);
				// alert(intPageWidth);
				
				elmOverlay.style.width	= Math.max(document.body.offsetWidth, intPageWidth);
				
				if (this.arrOverlayZIndexHistory.length == 0)
				{
					// elmOverlay has not been added to the document tree yet
					// I don't know what happens if you try to append an element to a parent that already has the element as a child.
					elmRoot.appendChild(elmOverlay);
				}
				
				// flag this popup as being modal
				elmPopup.setAttribute("modal", "modal");
				
                break;
			}
			case "nonmodal":
			{
				// flag this popup as being non-modal
				elmPopup.setAttribute("nonmodal", "nonmodal");
				
				break;
			}
			case "autohide":
			{
				// clicking ANYWHERE will close the div
				//  what about on the div itself?
				document.addEventListener('mousedown', CloseHandler, TRUE);
				document.addEventListener('keydown', CloseHandler, TRUE);

				// flag this popup as being autohide
				elmPopup.setAttribute("autohide", "autohide");

				break;
			}
			case "autohide-reload":
			{
				// clicking ANYWHERE will close the div
				//  what about on the div itself?
				document.addEventListener('mousedown', CloseReloadHandler, TRUE);
				document.addEventListener('keydown', CloseReloadHandler, TRUE);
				
				// flag this popup as being autohide-reload
				elmPopup.setAttribute("autohide-reload", "autohide-reload");
				
				break;
			}
			default:
			{
				break;
			}
		}
		
		// Bring the popup to the front
		//  check the zindex in CSS, might need to be increased somewhat
		elmPopup.style.zIndex = ++dragObj.zIndex;

		// Set the size of the popup
		switch (strSize.toLowerCase())
		{
			case "small":
				{	//small
					elmPopup.style.width = '200px';
					break;
				}
			case "medium":
				{	//medium
					elmPopup.style.width = '450px';
					break;
				}
			case "mediumlarge":
				{	//medium
					elmPopup.style.width = '575px';
					break;
				}
			case "large":
				{	//large
					elmPopup.style.width = '700px';
					break;
				}
			case "extralarge":
				{
					// Extra Large
					elmPopup.style.width = '850px';
					break;
				}
			case "alertsize":
				{
					// Unique size for alert popups, so that they stand out from other popups
					elmPopup.style.width = '470px';
					break;
				}
			default:
				{   //default
					elmPopup.style.width = '450px';
					break;
				}
		}
		
		// Set the position (centre/pointer/target)
		if (mixPosition == "centre")
		{
			// center the popup
			elmPopup.style.left	= ((window.innerWidth / 2) - (elmPopup.offsetWidth / 2)) + document.body.scrollLeft;
			elmPopup.style.top	= ((window.innerHeight / 2) - (elmPopup.offsetHeight / 2)) + document.body.scrollTop;
		}
		else if (mixPosition == "[object MouseEvent]")
		{
			// set the popup to the cursor
			elmPopup.style.left = mixPosition.clientX;
			elmPopup.style.top = mixPosition.clientY;
			
		}
		else if (typeof(mixPosition) == 'object')
		{
			// set the popup to the target
			elmPopup.style.left = mixPosition.offsetLeft;
			elmPopup.style.top = mixPosition.offsetTop;
		}
		else
		{
			// set the popup, well, wherever it wants
		}
		
		// Add the handler for dragging the popup around
		// if (strModal != "modal")
		// {
    		mydragObj = document.getElementById('VixenPopupTopBar__' + strId);
    		mydragObj.addEventListener('mousedown', OpenHandler, false);
		// }
		
		// Display the popup
		elmPopup.style.visibility = 'visible';
		
		function OpenHandler(event)
		{
			Vixen.Dhtml.Drag(event, 'VixenPopup__' + strId);	
		}
		
		function CloseHandler(event)
		{
			// for AUTOHIDE only (strId is a parameter of the Create method, of which this function is defined within)
			if (event.target.id.indexOf(strId) >= 0)
			{
				// Top bar, looking to drag 
			}			
			else
			{
				// MouseDown on page
				Vixen.Popup.Close(strId);
				
				// Remove the Event listeners required to make it an autohide popup
				// This is currently handled by the VixenPopupClass->Close method
				// The following commented out lines can be removed
				document.removeEventListener('mousedown', CloseHandler, TRUE);
				document.removeEventListener('keyup', CloseHandler, TRUE);
				
				// load the new location if one was specified
				if (Vixen.Popup.strLocationOnClose)
				{
					//FIX IT! I don't know if this will work if there are multiple popups open which set Vixen.Popup.strLocationOnClose
					window.location = Vixen.Popup.strLocationOnClose;
				}
			}
		}
		
		function CloseReloadHandler(event)
		{
			// for AUTOHIDE only
			if (event.target.id.indexOf(strId) >= 0)
			{
				// Top bar, looking to drag
			}			
			else
			{
				// MouseDown on page
				Vixen.Popup.Close(strId);
				document.removeEventListener('mousedown', CloseReloadHandler, TRUE);
				document.removeEventListener('keyup', CloseReloadHandler, TRUE);
				window.location = window.location;
			}
		}
	}
	
	// mixId can be the id of the popup as a string or it can be a pointer to any element on the popup
	this.Close = function(mixId)
	{
		// Work out how we are going to find the popup element
		if (typeof(mixId) == 'string')
		{
			// The id of the popup has been specified, find the popup element by id
			var strPopupId = 'VixenPopup__' + mixId
			var elmPopup = document.getElementById(strPopupId);
		}
		else if (typeof(mixId) == 'object')
		{
			// An element on the popup has been specified, find the popup element through retracing the parents of this element
			var elmElement = mixId;
			var bolFoundPopup = false;
			while (elmElement.tagName != "BODY")
			{
				if (elmElement.id.substr(0, 12) == "VixenPopup__")
				{
					bolFoundPopup = true;
					break;
				}
				elmElement = elmElement.parentNode;
			}
			if (bolFoundPopup)
			{
				elmPopup = elmElement;
			}
			else
			{
				alert("Could not find the popup to close");
				return;
			}
		}
		else
		{
			alert("Could not close the popup.\nmixId must be a string or element on the popup.\nmixId = " + mixId.toString());
			return;
		}
		
		
		if (elmPopup)
		{
			elmPopup.parentNode.removeChild(elmPopup);
			document.body.style.overflow = "visible"; // Why is this done?
			
			// Do clean up actions, specific to the type of popup
			if (elmPopup.hasAttribute("modal"))
			{
				// The popup was modal.  Move the overlay div to its previous zIndex
				var elmOverlay = document.getElementById("overlay");
				if (this.arrOverlayZIndexHistory.length != 0)
				{
					// Set the zIndex of the overlay to its previous zIndex
					elmOverlay.style.zIndex = this.arrOverlayZIndexHistory.pop();
				}
				else
				{
					// remove elmOverlay alltogether
					elmOverlay.parentNode.removeChild(elmOverlay);
				}
			}
		}
	}
	
	this.ShowAjaxPopup = function(strId, strSize, strTitle, strClass, strMethod, objParams, strWindowType)
	{
		if (objParams == undefined)
		{
			var objParams = {};
		}
		objParams.strId 		= strId;
		objParams.strSize 		= strSize;
		objParams.strTitle		= strTitle;
		objParams.TargetType 	= "Popup";
		if (strWindowType == undefined)
		{
			objParams.WindowType = "modal";
		}
		else
		{
			objParams.WindowType = strWindowType;
		}
		
		objParams.Class = strClass;
		objParams.Method = strMethod;

		// Draw the Page Loading splash (this will show after 1 second)
		Vixen.Popup.ShowPageLoadingSplash("Please wait", null, null, null, 1000);

		Vixen.Ajax.Send(objParams);
	}
	
	//------------------------------------------------------------------------//
	// Alert
	//------------------------------------------------------------------------//
	/**
	 * Alert()
	 *
	 * Replicates the functionality of the standard javascript "alert" function
	 *
	 * Replicates the functionality of the standard javascript "alert" function
	 * 
	 * @param	string	strMessage			message to display
	 * @param	string	strSize				optional, size of the popup box ("small|medium|large")
	 *										Defaults to "medium"
	 * @return	void
	 *
	 * @method
	 */
	this.Alert = function(strMessage, strSize, strTitle)
	{
		// set a default value for strSize
		if (strSize == null)
		{
			strSize = "AlertSize";
		}
		if (strTitle == null)
		{
			strTitle = VIXEN_APPLICATION_NAME;
		}
	
		strContent =	"<p><div align='center' style='margin: 5px 10px 10px 10px'>" + strMessage + 
						//"<p><input type='button' id='VixenAlertOkButton' value='OK' onClick='Vixen.Popup.Close(\"VixenAlertBox\")'><br></div>\n" +
						"<p></div>\n" +
						"<div align='center' style='margin-bottom: 10px'><input type='button' id='VixenAlertOkButton' value='OK'><br></div>" +
						"<" + "script type='text/javascript'>document.getElementById('VixenAlertOkButton').focus()</" + "script>\n";
		Vixen.Popup.Create('VixenAlertBox', strContent, strSize, 'centre', 'autohide', strTitle);
	}
	
	//------------------------------------------------------------------------//
	// Confirm
	//------------------------------------------------------------------------//
	/**
	 * Confirm()
	 *
	 * Replicates the functionality of the standard javascript "confirm" function
	 *
	 * Replicates the functionality of the standard javascript "confirm" function
	 * Regardless of what button is clicked, the popup is always automatically closed
	 * 
	 * @param	string	strMessage			message to display
	 * @param	mix		mixOkOnClick		can be either a function reference or a string containing code to execute when the Ok button is triggered
	 * @param	mix		mixCancelOnClick	can be either a function reference or a string containing code to execute when the Cancel button is triggered
	 *										if no value is given (null) then the Cancel action will close the confirm popup
	 * @param	string	strSize				"small|mediam|large", defaults to medium
	 * @param	string	strOkCaption		caption for the ok button
	 * @param	string	strCancelCaption	caption for the cancel button
	 *
	 * @return	void
	 *
	 * @method
	 */
	this.Confirm = function(strMessage, mixOkOnClick, mixCancelOnClick, strSize, strOkCaption, strCancelCaption)
	{
		// set default values
		strSize = (strSize == null) ? "AlertSize" : strSize;
		strOkCaption = (strOkCaption == null) ? "Ok" : strOkCaption;
		strCancelCaption = (strCancelCaption == null) ? "Cancel" : strCancelCaption;
		
		
		strOkBtnHtml		= "<input type='button' id='VixenConfirmOkButton' value='" + strOkCaption + "'>";
		strCancelBtnHtml	= "<input type='button' id='VixenConfirmCancelButton' value='" + strCancelCaption + "'>";
		
		strContent =	"<table border='0' width='100%'>" + 
						"<tr><td colspan='2' align='left' style='padding: 5px 10px 10px 10px'><span align='justify' style='line-height:1.5'>" + strMessage + "</span></td></tr>" +
						"<tr><td align='center' width='50%'>" + strOkBtnHtml + "</td>" + 
						"<td align='center' width='50%'>" + strCancelBtnHtml + "</td></tr>";
		Vixen.Popup.Create('VixenConfirmBox', strContent, strSize, 'centre', 'modal');
		
		// get references to the Ok and Cancel buttons and attach the event listeners
		var elmOkButton = document.getElementById("VixenConfirmOkButton");
		var elmCancelButton = document.getElementById("VixenConfirmCancelButton");
		
		if (typeof(mixOkOnClick) == 'function')
		{
			// the button action is a function
			elmOkButton.addEventListener("click", function() {Vixen.Popup.Close("VixenConfirmBox"); mixOkOnClick();}, false);
		}
		else if (typeof(mixOkOnClick) == 'string')
		{
			// the button action is code stored as a string
			elmOkButton.addEventListener("click", function() {Vixen.Popup.Close("VixenConfirmBox"); eval(mixOkOnClick);}, false);
		}
		else
		{
			// No valid action was declared for the ok button.
			elmOkButton.addEventListener("click", function() {alert("No action has been declared"); Vixen.Popup.Close("VixenConfirmBox");}, false);
		}

		if (typeof(mixCancelOnClick) == 'function')
		{
			// the button action is a function
			elmCancelButton.addEventListener("click", function() {Vixen.Popup.Close("VixenConfirmBox"); mixCancelOnClick();}, false);
		}
		else if (typeof(mixCancelOnClick) == 'string')
		{
			// the button action is code stored as a string
			elmCancelButton.addEventListener("click", function() {Vixen.Popup.Close("VixenConfirmBox"); eval(mixCancelOnClick);}, false);
		}
		else if (mixCancelOnClick == null)
		{
			// No action was specified so just close the popup
			elmCancelButton.addEventListener("click", function() {Vixen.Popup.Close("VixenConfirmBox");}, false);
		}
		
		// set focus to the Ok button
		elmOkButton.focus();
	}

	//------------------------------------------------------------------------//
	// ShowPageLoadingSplash
	//------------------------------------------------------------------------//
	/**
	 * ShowPageLoadingSplash()
	 *
	 * Renders a Splash popup 
	 *
	 * Renders a Splash popup 
	 * Used to show that a page is loading
	 * 
	 * @param	string	strMessage			optional, message to display.  Default = "Page Loading"
	 * @param 	string	strSize				optional, size of the splash popup. Default = "small"
	 * @param 	string	strImage			optional, image to display. Default = "img/template/pablo_load.gif"
	 * @param	string	strElement			optional, If supplied, the splash will appear above the element (not over the element)
	 * @param	int		intWait				optional, If supplied, the splash will not appear until intWait miliseconds have transpired
	 * @param	bool	bolAnimateSplash	optional, If supplied, the splash will animate some dots, so it looks like it's doing something.
	 *										This does not affect the animated image, however depending on what is happening, the image may not
	 *										automatically animate.  This happens when a page reload occurrs.
	 *
	 * @return	void
	 *
	 * @method
	 */
	this.ShowPageLoadingSplash = function(strMessage, strSize, strImage, strElement, intWait, bolAnimateSplash)
	{
		// Make sure this splash isn't already displayed
		if (this.Exists("Splash"))
		{
			// It's already open, so don't show it again
			return;
		}
	
		// Make sure this splash isn't also waiting to be displayed
		if (this.intTimeoutIdForPageLoadingSplash != null)
		{
			// The splash is waiting to be displayed.  Cancel it
			clearTimeout(this.intTimeoutIdForPageLoadingSplash);
			this.intTimeoutIdForPageLoadingSplash = null;
		}
		
		if (intWait != null)
		{
			// A waiting period has been specified
			this.intTimeoutIdForPageLoadingSplash = setTimeout(function(){Vixen.Popup.ShowPageLoadingSplash(strMessage, strSize, strImage, strElement, null, bolAnimateSplash);}, intWait);
			return;
		}
		
		
		// set the default message
		if (strMessage == null)
		{
			strMessage = "Page Loading";
		}
		// set a default value for strSize
		if (strSize == null)
		{
			strSize = "small";
		}
		
		if (strImage == null)
		{
			strImage = "img/template/loading.gif";
		}
		
		strContent =	"<div align='center' style='height:200px'><img id='SplashImage' src='" + strImage + "'></img>" +
						"<p><span id='VixenSplashDots'></span></p>" + 
						"<br /><p><h2>" + strMessage + "</h2></p></div>\n";
		
		this.CreateSplash(strContent, strSize, null, strElement);
		
		// Animate the splash
		if (bolAnimateSplash)
		{
			this.AnimateSplash();
		}
	}
	
	//------------------------------------------------------------------------//
	// AnimateSplash
	//------------------------------------------------------------------------//
	/**
	 * AnimateSplash()
	 *
	 * Animates the PageLoadingSplash 
	 *
	 * Animates the PageLoadingSplash
	 * 
	 * @param	int		intNumOfDots	optional, The number of dots to display
	 *
	 * @return	void
	 *
	 * @method
	 */
	this.AnimateSplash = function(intNumOfDots)
	{
		if (intNumOfDots == null || intNumOfDots > 20)
		{
			intNumOfDots = 1;
		}
		
		var elmDots = document.getElementById("VixenSplashDots");
		if (elmDots == null)
		{
			// The splash has been closed
			return;
		}
		
		var strDots = "..................................";
		
		strDots = strDots.slice(0, intNumOfDots);
		elmDots.innerHTML = strDots;
		
		intNumOfDots++;
		setTimeout(function(){Vixen.Popup.AnimateSplash(intNumOfDots)}, 200);
	}
	
	//------------------------------------------------------------------------//
	// ClosePageLoadingSplash
	//------------------------------------------------------------------------//
	/**
	 * ClosePageLoadingSplash()
	 *
	 * Closes the Splash page
	 *
	 * Closes the Splash page
	 * 
	 * @return	void
	 * @method
	 */
	this.ClosePageLoadingSplash = function()
	{
		// Check if the splash is waiting to be displayed
		if (this.intTimeoutIdForPageLoadingSplash != null)
		{
			// The splash is waiting to be displayed.  Stop it
			clearTimeout(this.intTimeoutIdForPageLoadingSplash);
			this.intTimeoutIdForPageLoadingSplash = null;
		}
	
		this.Close("Splash");
	}
	
	//------------------------------------------------------------------------//
	// CreateSplash
	//------------------------------------------------------------------------//
	/**
	 * CreateSplash()
	 *
	 * Creates a splash, which is essentially a popup without a title bar
	 *
	 * Creates a splash, which is essentially a popup without a title bar
	 * 
	 * @param	string	strContent		html code to be displayed in the splash
	 * @param	string	strSize			optional, Defaults to "medium"
	 * @param	int		intTime			optional, If set, the splash will disapear after intTime miliseconds
	 * @param	string	strElement		optional, If supplied, the splash will appear above the element (not over the element)
	 *
	 * @return	void
	 * @method
	 */
	this.CreateSplash = function(strContent, strSize, intTime, strElement)
	{
		// set defaults
		if (strSize == null)
		{
			strSize = "medium";
		}
		
		var elmElement = null;
		if (strElement)
		{
			elmElement = document.getElementById(strElement);
		}
	
		// Try to find a previous splash
		var elmExists = document.getElementById('VixenPopup__Splash');
		if (elmExists)
		{
			// destroy it . . .
			elmExists.parentNode.removeChild(elmExists);
		}
		
		// . . . and create it
		var elmPopup = document.createElement('div');
		elmPopup.setAttribute('className', 'PopupBox');
		elmPopup.setAttribute('class', 'PopupBox');
		elmPopup.setAttribute('Id', 'VixenPopup__Splash');
		
		// Set the content of the splash box
		if (!strContent)
		{
			strContent = "No data<br />";
		}
				
		// initially hide the splash
		elmPopup.style.visibility = 'hidden';
		
		// set the content of the splash
		elmPopup.innerHTML = strContent;

		// set the top of the splash to the body.scrollTop, so that it doesn't move the page when it is added to it
		elmPopup.style.top	= document.body.scrollTop;

		// Add the splash to the PopupHolder element
		elmRoot = document.getElementById('PopupHolder');
		elmRoot.appendChild(elmPopup);
		
		// Bring the splash to the front
		//  check the zindex in CSS, might need to be increased somewhat
		elmPopup.style.zIndex = ++dragObj.zIndex;

		// Set the size of the splash
		switch (strSize)
		{
			case "small":
				{	//small
					elmPopup.style.width = '200px';
					break;
				}
			case "medium":
				{	//medium
					elmPopup.style.width = '450px';
					break;
				}
			case "large":
				{	//large
					elmPopup.style.width = '700px';
					break;
				}
			default:
				{   //default
					elmPopup.style.width = '450px';
					break;
				}
		}

		// Set the position
		// MSIE and Firefox use different properties to find out the width and height of the window
		if (window.innerWidth)
		{
			var intWindowInnerWidth = window.innerWidth;
			var intWindowInnerHeight = window.innerHeight;
		}
		else if (document.body.offsetWidth)
		{
			var intWindowInnerWidth = document.body.offsetWidth;
			var intWindowInnerHeight = document.body.offsetHeight;
		}
	
		// center the splash
		elmPopup.style.left	= ((intWindowInnerWidth / 2) - (elmPopup.offsetWidth / 2)) + document.body.scrollLeft;
		elmPopup.style.top	= ((intWindowInnerHeight / 2) - (elmPopup.offsetHeight / 2));
		// Declaring it as being fixed position, must be done after left and top are set, not before it
		elmPopup.style.position = "fixed";
		
		// If elmElement has been defined, then position the splash above the element
		// This has been incorporated into the functionality because sometimes in MSIE elements like comboboxes will
		// always appear in front of the splash, regardless of their zIndex
		if (elmElement)
		{
			// Find the absolute position of the element
			var intOffsetTop = elmElement.offsetTop;

			while (elmElement.offsetParent)
			{
				elmElement = elmElement.offsetParent;
				intOffsetTop += elmElement.offsetTop;
			}
	
			elmPopup.style.top = intOffsetTop - elmPopup.offsetHeight - 10;
		}
		
		// Display the splash
		elmPopup.style.visibility = 'visible';

		// Close the splash if intTime has been specified
		if (intTime)
		{
			setTimeout(function(){Vixen.Popup.Close("Splash")}, intTime);
		}
	}

}

// Create an instance of the Vixen popup class if it has not already been created
if (Vixen.Popup == undefined)
{
	Vixen.Popup = new VixenPopupClass();
}
