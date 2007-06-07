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
		// try to find a previous object
		elmExists = document.getElementById('VixenPopup__' + strId);
		if (elmExists)
		{
			// destroy it
			elmExists.parentNode.removeChild(elmExists);
		}
		
		elmPopup = document.createElement('div');
		elmPopup.setAttribute('className', 'PopupBox');
		elmPopup.setAttribute('class', 'PopupBox');
		elmPopup.setAttribute('Id', 'VixenPopup__' + strId);
		
		
		// Set the content of the popup box
		tempId = '"' + strId + '"';
		
		if (!strContent)
		{
			strContent = "No data<br />Id: " + strId;
		}
		


		strContent = 
		"<div id='VixenPopupTopBar__" + strId + "' class='PopupBox_TopBar'>" +
		"<img src='img/template/close.png' class='PopupBox_Close' onclick='Vixen.Popup.Close(" + tempId + ")'>" + 
		"TelcoBlue Internal System" +
		"</div>" +	strContent;

		//elmPopup.style.visibility = 'visible';			
		elmPopup.innerHTML = strContent;
		elmRoot = document.getElementById('PopupHolder');
		elmRoot.appendChild(elmPopup);

		//Going to run into some problems when having multiple popups
		// on a single page, especially of different types

		// Set the behaviour (modal/modeless/autohide)
		switch (strModal)
		{
			case "modal":
			{
				// Create a div to capture all events
				elmOverlay = document.createElement('div');
				elmOverlay.setAttribute('Id', 'overlay');
				elmRoot.appendChild(elmOverlay);
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
				document.addEventListener('mousedown', CloseHandler, TRUE);
				break;
			}
			default:
			{
				break;
			}
		}
		

		
		// Set the size of the popup box
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
				{
					//default
					elmPopup.style.width = '450px';
					break;
				}
		}
		
		// Set the position (centre/pointer/target)
		if (mixPosition == "centre")
		{
			// center the popup
			//strContent += "str: " + mixPosition;
			elmPopup.style.left = (window.innerWidth / 2) - (elmPopup.offsetWidth / 2);
			elmPopup.style.top = (window.innerHeight / 2) - (elmPopup.offsetHeight / 2);
			
		}
		else if (mixPosition == "[object MouseEvent]")
		{
			// set the popup to the cursor
			//strContent += "eve: " + mixPosition;
			elmPopup.style.left = mixPosition.clientX;
			elmPopup.style.top = mixPosition.clientY;
			
		}
		else if (typeof(mixPosition) == 'object')
		{
			// set the popup to the target
			//strContent += "obj: " + mixPosition;
			elmPopup.style.left = mixPosition.offsetLeft;
			elmPopup.style.top = mixPosition.offsetTop;
		}
		else
		{
			//strContent += "dunnoh";
		}
		
		// Add the handler for dragging the box around
    	dragObj = document.getElementById('VixenPopupTopBar__' + strId);
    	dragObj.addEventListener('mousedown', LoginHandler, false);

		function LoginHandler(event)
		{
			Vixen.Dhtml.Drag(event, 'VixenPopup__' + strId);
		}
		function CloseHandler(event)
		{
			Vixen.Popup.Close(strId);
		}
		
		
	}
	
	this.Close = function(strId)
	{
		var objClose = document.getElementById('VixenPopup__' + strId);
		if (objClose)
		{
			objClose.parentNode.removeChild(objClose);
		}
		var elmOverlay = document.getElementById('overlay');
		if (elmOverlay)
		{
			elmOverlay.parentNode.removeChild(elmOverlay);
		}
	}
}

// Create an instance of the Vixen menu class
Vixen.Popup = new VixenPopupClass();
