//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// textarea.js
//----------------------------------------------------------------------------//
/**
 * textarea
 *
 * Event Listeners for textareas
 *
 * Event Listeners for textareas
 * 
 *
 * @file		textarea.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


// Event handler for textarea elements so that the user can tab
function TextAreaTabListener(objEvent)
{
	if (objEvent.keyCode != 9)
	{
		// The key isn't a tab so don't do anything
		return true;
	}
	
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

// Find all text areas and register the listeners
Event.startObserving(window, "load", RegisterAllTextAreas, true);

function RegisterAllTextAreas()
{
	var arrTextAreas = document.getElementsByTagName("textarea");
	for (i=0; i < arrTextAreas.length; i++)
	{
		Event.startObserving(arrTextAreas[i], "keydown", TextAreaTabListener, true);
	}
}
