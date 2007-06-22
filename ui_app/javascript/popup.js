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
	this.Create = function(strContent, strId, strSize, mixPosition, strModal)
	{
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
				
		strContent = 
		"<div id='VixenPopupTopBar__" + strId + "' class='PopupBox_TopBar'>" +
		"<img src='img/template/close.png' class='PopupBox_Close' onclick='Vixen.Popup.Close(" + strTempId + ")'>" + 
		"TelcoBlue Internal System" +
		"</div>" +	strContent;

		// Add the popup to the holder
		//elmPopup.style.visibility = 'visible';			
		elmPopup.innerHTML = strContent;
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
				elmOverlay = document.createElement('div');
				elmOverlay.setAttribute('Id', 'overlay');
				elmRoot.appendChild(elmOverlay);
				elmOverlay.style.zIndex = ++dragObj.zIndex;
				
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
			elmPopup.style.left = (window.innerWidth / 2) - (elmPopup.offsetWidth / 2);
			elmPopup.style.top = (window.innerHeight / 2) - (elmPopup.offsetHeight / 2);
			
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
			// set the popup, well, where ever it wants
		}
		
		// Add the handler for dragging the popup around
    	mydragObj = document.getElementById('VixenPopupTopBar__' + strId);
    	mydragObj.addEventListener('mousedown', OpenHandler, false);
		
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
			}
		}
	}
	
	this.Close = function(strId)
	{
		var objClose = document.getElementById('VixenPopup__' + strId);
		if (objClose)
		{
			//objClose.removeEventListener('mousedown', OpenHandler, false);
			objClose.parentNode.removeChild(objClose);
			
		}
		// If it was modal (overlay hiding everything)
		var elmOverlay = document.getElementById('overlay');
		if (elmOverlay)
		{
			elmOverlay.parentNode.removeChild(elmOverlay);
		}
	}
	
	this.ShowAjaxPopup = function(strId, strSize, objParams)
	{
		// We want to call Vixen.Ajax.Send passing objParams 
		// objParams is currently in JSON notation, but I think Vixen.Ajax.Send is expecting it to be in some other format
		
		// Vixen.Ajax.Send will execute the application template.
		// We want to use the output of the application template (echoed html code) and pass it to a popup window

		objParams.strSize 		= strSize;
		objParams.strId 		= strId;
		objParams.TargetType 	= "Popup";
		
		Vixen.Ajax.Send(objParams);
	}
}

// Create an instance of the Vixen menu class
Vixen.Popup = new VixenPopupClass();
