//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// event_handler.js
//----------------------------------------------------------------------------//
/**
 * event_handler
 *
 * A very simple event handling model for custom events required of Vixen
 *
 * A very simple event handling model for custom events required of Vixen
 * 
 *
 * @file		event_handler.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.10
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenEventHandlerClass
//----------------------------------------------------------------------------//
/**
 * VixenEventHandlerClass
 *
 * A very simple event handling model for custom events required of Vixen
 *
 * A very simple event handling model for custom events required of Vixen
 * 
 *
 * @package	ui_app
 * @class	VixenEventHandlerClass
 * 
 */
function VixenEventHandlerClass()
{
	//------------------------------------------------------------------------//
	// _objEventListeners
	//------------------------------------------------------------------------//
	/**
	 * _objEventListeners
	 *
	 * Stores an array of listeners for each EventType registered
	 *
	 * Stores an array of listeners for each EventType registered
	 * _objEventListeners.{EventType1}.[0] = ListenerFunc1
	 * _objEventListeners.{EventType1}.[1] = ListenerFunc2
	 * _objEventListeners.{EventType2}.[0] = ListenerFunc3
	 * 
	 * @type		object
	 *
	 * @property
	 */
	this._objEventListeners = {};

	//------------------------------------------------------------------------//
	// AddListener
	//------------------------------------------------------------------------//
	/**
	 * AddListener
	 *
	 * Adds a listener function to the list of event listeners, for the given EventType
	 *
	 * Adds a listener function to the list of event listeners, for the given EventType
	 *
	 * @param	string		strEventType	Name of the event that will trigger the listener (case-insensitive)
	 * @param	function	funcListener	Function pointer to the listener
	 *										When the listener function is executed, it will be passed an object
	 *										storing data specific to the Event being fired
	 * @param	object		objParent		optional, parent object of the funcListener function.  This will
	 *										be passed to funcListener as the second parameter if it is specified,
	 *										so that the function will have a pointer to its parent object
	 *
	 * @return	function	funcListener	If funcListener is an anonymous function, then you might want to
	 *										store a pointer for it, so it can be removed at a later stage
	 * @method
	 */
	this.AddListener = function(strEventType, funcListener, objParent)
	{
		// Set default values
		objParent = (objParent == undefined) ? null : objParent;
		
		strEventType = strEventType.toLowerCase();
		
		if (this._objEventListeners[strEventType] == undefined)
		{
			// There are currently no event listeners for this EventType
			this._objEventListeners[strEventType] = new Array();
		}
		
		// Make sure funcListener is not already in the list of listeners
		var intLength = this._objEventListeners[strEventType].length;
		for (var i=0; i < intLength; i++)
		{
			if (this._objEventListeners[strEventType][i].funcListener == funcListener)
			{
				// funcListener is already in the list of listeners, so don't add it again
				return funcListener;
			}
		}
		
		// Append the event listener to the end of the list of listeners for this EventType
		var objListener				= {};
		objListener.funcListener	= funcListener;
		objListener.objParent		= objParent;
		this._objEventListeners[strEventType].push(objListener);
		
		return funcListener;
	}
	
	//------------------------------------------------------------------------//
	// RemoveListener
	//------------------------------------------------------------------------//
	/**
	 * RemoveListener
	 *
	 * Removes a listener function from the list of event listeners, for the given EventType
	 *  
	 * Removes a listener function from the list of event listeners, for the given EventType
	 *
	 * @param	string		strEventType	Name of the event that will trigger the listener (case-insensitive)
	 * @param	function	funcListener	Function pointer to the listener
	 *
	 * @return	void
	 * @method
	 */
	this.RemoveListener = function(strEventType, funcListener)
	{
		strEventType = strEventType.toLowerCase();
		
		// Check that a list of event listeners actually exists
		if (this._objEventListeners[strEventType] != undefined)
		{
			// Store the length of the list of Event Listeners
			var intLength = this._objEventListeners[strEventType].length;
			
			// Find the Event Listener in the list
			for (var i=0; i < intLength; i++)
			{
				if (this._objEventListeners[strEventType][i].funcListener == funcListener)
				{
					// Remove the Event Listener from the list
					this._objEventListeners[strEventType].splice(i, 1);
					
					// The Event Listener should only be in the list once
					return;
				}
			}
		}
	}
	
	// Removes all listeners of the given strEventType EventType
	this.RemoveAllListeners = function(strEventType)
	{
		//BUG! this isn't working
		delete(this._objEventListeners[strEventType]);
		return;
	}
	
	
	//------------------------------------------------------------------------//
	// FireEvent
	//------------------------------------------------------------------------//
	/**
	 * FireEvent
	 *
	 * Executes each Event Listener that is registered for the specified Event Type
	 *  
	 * Executes each Event Listener that is registered for the specified Event Type
	 *
	 * @param	string		strEventType	Name of the event to fire (case-insensitive)
	 * @param	object		objEventData	Data specific to the event (should be documented elsewhere)
	 *
	 * @return	void
	 * @method
	 */
	this.FireEvent = function(strEventType, objEventData)
	{
		strEventType = strEventType.toLowerCase();
		var funcEventListener;
		var objParent;
		var intOldLength;
		var i = 0;
		
		// Check that a list of event listeners actually exists
		if (this._objEventListeners[strEventType] != undefined)
		{
			// Create the Event object to pass to the listener
			var objEvent = {};
			objEvent.Type = strEventType;
			objEvent.Data = objEventData;
			
			// Execute each event listener in the list
			while (i < this._objEventListeners[strEventType].length)
			{
				// Store the length of the list of listeners before this listener is fired
				intOldLength = this._objEventListeners[strEventType].length;
				
				//TODO! I should probably check that the listener function still exists in memory
				// although javascript has automatic garbage collection, and if this pointer points
				// to the function, then it shouldn't be automatically freed.
				
				// Calling the event listener this way, will make the "this" pointer
				// point to the window element
				// I would prefer it to point to the object that the listener is a method
				// of, but I can't get it to do that
				funcEventListener	= this._objEventListeners[strEventType][i].funcListener;
				objParent			= this._objEventListeners[strEventType][i].objParent;
				funcEventListener(objEvent, objParent);
			
				// When event listeners are called this way, the "this" pointer
				// points to this function (FireEvent), even from within the 
				// event listener
				//this._objEventListeners[strEventType][i](objEvent);
				
				// Only increment the iterator if the length of the list of listeners hasn't changed
				// If it has changed, then that means that the listener removed itself from the list of listeners
				if (intOldLength == this._objEventListeners[strEventType].length)
				{
					// Move on to the next listener
					i++;
				}
			}
		}
	},
	
	this.fireEventForElement	= function(domElement, strEventType)
	{
		if (document.createEvent)
		{
			// Standards Compliant
			var objEvent	= document.createEvent("HTMLEvents");
			objEvent.initEvent(strEventType, true, true);
	        return !domElement.dispatchEvent(objEvent);
		}
		else
		{
			// Fallback (for IE, apparently)
			var objEvent	= document.createEventObject();
	        return domElement.fireEvent('on' + strEventType, objEvent);
		}
	}
}

// Instanciate the object
if (Vixen.EventHandler == undefined)
{
	Vixen.EventHandler = new VixenEventHandlerClass;
}
