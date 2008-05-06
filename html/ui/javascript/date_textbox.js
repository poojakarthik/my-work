//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// date_textbox.js
//----------------------------------------------------------------------------//
/**
 * date_textbox
 *
 * Event Listeners to handle the date input mask for textboxes
 *
 * Event Listeners to handle the date input mask for textboxes
 * 
 *
 * @file		date_textbox.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.05
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

regexDateInputValidKeys		= /^(\d|[./-])$/;

// Event handler for textbox elements for the ShortDate input mask
function ShorDateInputMaskListener(objEvent)
{

	// Handle backspace and delete keys
	if (objEvent.keyCode == 46 || objEvent.keyCode == 8 || objEvent.keyCode == 37 || objEvent.keyCode == 39)
	{
		// The key isn't a tab so don't do anything
		return true;
	}
var intKey = objEvent.keyCode;
var strKey = String.fromCharCode(objEvent.keyCode);
setTimeout(function(){$Alert(intKey +" - "+ strKey)}, 100);

	if (!regexDateInputValidKeys.test(strKey))
	{
		objEvent.preventDefault();
		return;
	}
	return true;
	
	
	// Prevent the default action of tabbing, which would move the focus to the next element in the tab order
	objEvent.preventDefault();
	
	var elmTarget		= objEvent.target;
	var intScrollTop	= elmTarget.scrollTop;
	var intSelStart		= elmTarget.selectionStart;
	var intSelEnd		= elmTarget.selectionEnd;
	var strPre			= elmTarget.value.slice(0, intSelStart);
	var strPost			= elmTarget.value.slice(intSelEnd, elmTarget.value.length)
	
	elmTarget.value				= strPre + "\t" + strPost;
	elmTarget.selectionStart	= intSelStart + 1;
	elmTarget.selectionEnd		= intSelStart + 1;
	elmTarget.scrollTop			= intScrollTop;
	
	//TODO! Have this handle multiple selected rows and also the shift-tab thing
}

// Find all textboxes with the inputmask='shortdate' parameter
Event.startObserving(window, "load", RegisterAllShortDateTextboxes, true);

function RegisterAllShortDateTextboxes()
{
	var arrInputs = document.getElementsByTagName("input");
	for (i=0; i < arrInputs.length; i++)
	{
		if (arrInputs[i].type == "text" && arrInputs[i].hasAttribute("inputmask") && arrInputs[i].getAttribute("inputmask").toLowerCase() == "shortdate")
		{
			Event.startObserving(arrInputs[i], "keydown", ShorDateInputMaskListener, true);
		}
	}
}
