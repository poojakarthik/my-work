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
	
	this.objDeleteData = 
	{
		Payment:
		{
			strDescription:			"This is the Delete Payment Description",
			strApplcationTemplate:	"Account",
			strDeleteMethod:		"DeletePayment"
		}
	};

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
		
		// Retrieve the popup element
		var elmPopupContent = document.getElementById("VixenPopupContent__" + strId);


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
				
		// Add the popup to the holder
		//elmPopup.style.visibility = 'visible';			
		elmPopup.innerHTML = strContent;
		
		while (elmPopupContent.childNodes[0])
		{
    		elmPopupContent.removeChild(elmPopupContent.childNodes[0]);
		}
		
		elmPopupContent.appendChild(elmPopup);

		// Set the content of the popup box
		if (!strContent)
		{
			strContent = "No data<br />Id: " + strId;
		}
				
		this.strContentCode = strContent;

		// Add the popup to the holder
		elmPopupContent.innerHTML = strContent;
		return TRUE;
	}

	this.Create = function(strId, strContent, strSize, mixPosition, strModal)
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
				
		this.strContentCode = strContent;
		
		strContent = 
		"<div id='VixenPopupTopBar__" + strId + "' class='PopupBoxTopBar'>" +
		"<img src='img/template/close.png' class='PopupBoxClose' onclick='Vixen.Popup.Close(" + strTempId + ")'>" + 
		"<img src='img/template/debug.png' class='PopupBoxClose' onclick='Vixen.Popup.ViewContentCode()'>" +
		"TelcoBlue Internal System" +
		"</div>" + 
		"<div id='VixenPopupContent__" + strId + "'>" + strContent + "</div>";
		

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
				
				intScroll = document.body.scrollTop;
                document.body.style.overflow = "hidden";
               	document.body.scrollTop = intScroll;
                elmOverlay.style.height = document.body.offsetHeight;
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
			elmPopup.style.top = ((window.innerHeight / 2) - (elmPopup.offsetHeight / 2)) + document.body.scrollTop;
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
			document.body.style.overflow = "visible";
			
		}
		// If it was modal (overlay hiding everything)
		var elmOverlay = document.getElementById('overlay');
		if (elmOverlay)
		{
			elmOverlay.parentNode.removeChild(elmOverlay);
		}
	}
	
	this.ShowAjaxPopup = function(strId, strSize, strClass, strMethod, objParams)
	{
		objParams.strSize 		= strSize;
		objParams.strId 		= strId;
		objParams.TargetType 	= "Popup";
		
		objParams.Class = strClass;
		objParams.Method = strMethod;
		
		Vixen.Ajax.Send(objParams);
	}
	
	this.DeleteRecordPopup = function(strPopupId, strRecordType, objParams)
	{
		var strPopupContent;
		
		//alert("DeleteRecordPopup(): objDeleteData['Payment'].Description = '"+ this.objDeleteData['Payment'].strDescription +"'");
		
		//create the content for the popup 
		
		//var elmPopupContent = document.createElement('div');
		//elmPopupContent.setAttribute("class", "PopupMedium");
		//elmPopupContent.innerHtml = "Hello World";
		
		var strDescription = this.objDeleteData[strRecordType].Description;
		
		
		strPopupContent  = "<div class='PopupMedium'><h2>Delete "+ strRecordType +"</h2>\n"
		strPopupContent += "<div class='DefaultOutput Default'>"+ this.objDeleteData[strRecordType].Description +"</div>\n";
		strPopupContent += "</div>\n";
		
		
		
		this.Create(strPopupId, strPopupContent, "medium", "centre", "modal");
		
		
	}
	
}

// Create an instance of the Vixen menu class
Vixen.Popup = new VixenPopupClass();
