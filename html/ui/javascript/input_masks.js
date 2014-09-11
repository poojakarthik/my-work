//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// input_masks.js
//----------------------------------------------------------------------------//
/**
 * input_masks
 *
 * Event Listeners to handle all input masks that can be applied to textboxes
 *
 * Event Listeners to handle all input masks that can be applied to textboxes
 * An imput mask can be applied to a text box html element by specifying
 * the InputMask attribute
 * ie: <input type='text' InputMask='ShortDate' blah blah blah></input>
 * 
 *
 * @file		input_masks.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.05
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

if (InputMasks == undefined)
{
	var InputMasks = {};
}

InputMasks.shortdate = 	{
							format	: "__/__/____",
							regex	: /^\d$/
						};
InputMasks.time24hr =	{
							format	: "__:__:__",
							regex	: /^\d$/
						};
InputMasks.datetime	=	{
							format	: "__:__:__ __/__/____",
							regex	: /^\d$/
						};
InputMasks.digitsOnly = {
							format	: null,
							regex	: /^\d$/
						};


// Event handler for all input masks
function InputMaskListener(objEvent)
{
	// Handle control keys (like backspace, delete, arrow keys, etc)
	if (objEvent.charCode == 0 || objEvent.altKey || objEvent.ctrlKey)
	{
		return true;
	}

	var elmTarget	= objEvent.currentTarget;
	var strKey		= String.fromCharCode(objEvent.charCode);
	objEvent.preventDefault();
	if (!elmTarget.mask.regex.test(strKey))
	{
		// Not a valid char
		return false;
	}
	
	
	var intSelStart	= elmTarget.selectionStart;
	var intSelEnd	= elmTarget.selectionEnd;
	var strPre		= elmTarget.value.slice(0, intSelStart);
	var strPost		= elmTarget.value.slice(intSelEnd, elmTarget.value.length);
	var strInsert	= "";
	
	var strMaskPart = elmTarget.mask.format.charAt(intSelStart);
	if (strMaskPart != "_")
	{
		// Part of the masking
		strInsert = strMaskPart;
	}
	strInsert += strKey;
	
	// TODO! If the max length of the mask has been reached, then don't add the chars
	
	elmTarget.value = strPre + strInsert + ((intSelStart != intSelEnd)? strPost : strPost.substr(strInsert.length));
	elmTarget.selectionStart = elmTarget.selectionEnd = intSelStart + strInsert.length;
	
	return true;
}

// Add event listener to register all Input Masks when the page loads
Event.startObserving(window, "load", RegisterAllInputMasks, true);

// Registers all Input Masks
function RegisterAllInputMasks()
{
	// Retrieve a collection of all input elements in the DOM
	var arrInputs = document.getElementsByTagName("input");
	for (i=0; i < arrInputs.length; i++)
	{
		// If the element is a textbox and has a valid input mask set, register a listener for it
		if (arrInputs[i].type == "text" && arrInputs[i].hasAttribute("inputmask") && InputMasks[arrInputs[i].getAttribute("inputmask").toLowerCase()] != undefined)
		{
			arrInputs[i].mask = InputMasks[arrInputs[i].getAttribute("inputmask").toLowerCase()];
			Event.startObserving(arrInputs[i], "keypress", InputMaskListener, true);
		}
	}
}


function RegisterAllInputMasksInForm(elmForm)
{
	var inputMask;
	for (var i=0, j=elmForm.elements.length; i<j; i++)
	{
		if (elmForm.elements[i].hasAttribute("inputmask") && ((inputMask = InputMasks[elmForm.elements[i].getAttribute("inputmask").toLowerCase()]) != undefined))
		{
			elmForm.elements[i].mask = inputMask;
			Event.startObserving(elmForm.elements[i], "keypress", InputMaskListener, true);
		}
	}
}
