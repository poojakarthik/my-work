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
 * @package	framework_ui
 * @class	Vixen.Popup
 */
function VixenPopupClass()
{
	this.strContentCode = "";
	this.strLocationOnClose = "";
	
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

	this.Exists = function(strId)
	{
		elmExists = document.getElementById('VixenPopup__' + strId);
		if (elmExists)
		{
			return TRUE;
		}
		return FALSE;
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
	
	this.Create = function(strId, strContent, strSize, mixPosition, strModal, strLocationOnClose)
	{
		// set the location to relocate to, when the popup is closed.
		// If null, then a page reload is not performed
		// currently this only works when strModal == autohide 
		this.strLocationOnClose = strLocationOnClose;
	
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
		
		// Quote the id of the popup (argh, double quoting kills me)
		strTempId = '"' + strId + '"';
				
		// Set the content of the popup box
		if (!strContent)
		{
			strContent = "No data<br />Id: " + strId;
		}
				
		this.strContentCode = strContent;
		
		strContent = "<div id='VixenPopupTopBar__" + strId + "' class='PopupBoxTopBar'>" +
						"<img src='img/template/close.png' class='PopupBoxClose' onclick='Vixen.Popup.Close(" + strTempId + ")'>";
		
		// only display the debug button if we are operating in debug mode
		if (DEBUG_MODE)
		{
			strContent += "<img src='img/template/debug.png' class='PopupBoxClose' onclick='Vixen.Popup.ViewContentCode()'>";
		}
		
		strContent += "TelcoBlue Internal System" +
						"</div>" + 
						"<div id='VixenPopupContent__" + strId + "'>" + this.strContentCode + "</div>";
		

		
		// initially hide the popup
		elmPopup.style.visibility = 'hidden';
		
		// set the content of the popup
		elmPopup.innerHTML = strContent;
		
		// Add the popup to the PopupHolder element
		elmRoot = document.getElementById('PopupHolder');
		elmRoot.appendChild(elmPopup);
		
		

		//Going to run into some problems when having multiple popups
		// on a single page, especially of different types
		//  -think this is fixed, havent comprehensively tested though
		
		// Set the behaviour (modal/modeless/autohide)
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
				//alert(window.innerHeight);
                elmOverlay.style.height	= Math.max(document.body.offsetHeight, window.innerHeight);
				
				// BUG! FIXIT! FIX IT! TODO! This line currently isn't working because document.body.offsetWidth does not return the width of the document.  
				// (Not like how document.body.offsetHeight does, anyway)
				// The Vixen title bar always resizes horizontally to fit the window, maybe you should check out how it does it
				elmOverlay.style.width	= Math.max(document.body.offsetWidth, window.innerWidth);
				
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
			case "modeless":
			{
				// do nothing?
				break;
			}
			case "autohide":
			{
				// clicking ANYWHERE will close the div
				//  what about on the div itself?
				document.addEventListener('mousedown', CloseHandler, TRUE);
				document.addEventListener('keyup', CloseHandler, TRUE);
				break;
			}
			case "autohide-reload":
			{
				// clicking ANYWHERE will close the div
				//  what about on the div itself?
				document.addEventListener('mousedown', CloseReloadHandler, TRUE);
				document.addEventListener('keyup', CloseReloadHandler, TRUE);
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
		if (strModal != "modal")
		{
    		mydragObj = document.getElementById('VixenPopupTopBar__' + strId);
    		mydragObj.addEventListener('mousedown', OpenHandler, false);
		}
		
		// Display the popup
		elmPopup.style.visibility = 'visible';
		
		function OpenHandler(event)
		{
			Vixen.Dhtml.Drag(event, 'VixenPopup__' + strId);	
		}
		
		function CloseHandler(event)
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
	
	
	this.Close = function(strId)
	{
		var elmPopup = document.getElementById('VixenPopup__' + strId);
		if (elmPopup)
		{
			//objClose.removeEventListener('mousedown', OpenHandler, false);
			elmPopup.parentNode.removeChild(elmPopup);
			document.body.style.overflow = "visible"; // Why is this done?
			
		}
		
		// If the popup was modal, then move the overlay div to its previous zIndex
		if (elmPopup.hasAttribute("modal"))
		{
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
	
	this.ShowAjaxPopup = function(strId, strSize, strClass, strMethod, objParams, strWindowType)
	{
		objParams.strSize 		= strSize;
		objParams.strId 		= strId;
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
		
		Vixen.Ajax.Send(objParams);
	}
	
	// Replicates the functionality of the standard javascript "alert" function
	// the parameter strSize is optional and defaults to "medium"
	this.Alert = function(strMessage, strSize)
	{
		// set a default value for strSize
		if (strSize == null)
		{
			strSize = "medium";
		}
	
		strContent =	"<p><div align='center'>" + strMessage + 
						"<p><input type='button' id='VixenAlertOkButton' value='OK' onClick='Vixen.Popup.Close(\"VixenAlertBox\")'><br></div>\n" +
						"<script type='text/javascript'>document.getElementById('VixenAlertOkButton').focus()</script>\n";
		Vixen.Popup.Create('VixenAlertBox', strContent, strSize, 'centre', 'autohide');
	}
	
	// Confirm box
	this.Confirm = function(strMessage, mixOkOnClick, mixCancelOnClick, strSize, strOkCaption, strCancelCaption)
	{
		// set default values
		strSize = (strSize == null) ? "medium" : strSize;
		strOkCaption = (strOkCaption == null) ? "Ok" : strOkCaption;
		strCancelCaption = (strCancelCaption == null) ? "Cancel" : strCancelCaption;
		
		
		strOkBtnHtml		= "<input type='button' id='VixenConfirmOkButton' value='" + strOkCaption + "'>";
		strCancelBtnHtml	= "<input type='button' id='VixenConfirmCancelButton' value='" + strCancelCaption + "'>";
		
		strContent =	"<table border='0' width='100%'>" + 
						"<tr><td colspan='2' align='center'><span class='DefaultOutputSpan'>" + strMessage + "</span></td></tr>" +
						"<tr><td align='center'>" + strOkBtnHtml + "</td>" + 
						"<td align='center'>" + strCancelBtnHtml + "</td></tr>";
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
	
}

// Create an instance of the Vixen menu class
Vixen.Popup = new VixenPopupClass();
