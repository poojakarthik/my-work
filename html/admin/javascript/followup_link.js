
var FollowUp_Link	= Class.create
({
	initialize	: function()
	{
		this._oLinkDiv								= document.body.select('div#followup_link').first();
		this._oCountSpan							= null;
		this._bLinkFlashEnabled						= false;
		this._bRefreshOverdueCountTimeoutStarted	= false;
		this._aFollowUpContextLists					= [];
		this._sNewFollowUpCallbackURL				= null;
		this._aNewFollowUpCallbacks					= [];
		
		// Create the overdue count request json function
		this._fnGetCount	= 	jQuery.json.jsonFunction(
									this._getOverdueCount.bind(this, null), 
									this._ajaxError.bind(this),
									'FollowUp',
									'getOverdueCountForLoggedInEmployee'
								);

		// Construct the link
		this._buildLink();
		
		// Get number of overdue followups
		this._getOverdueCount(null);
		
		// Generate any context lists necessary
		this.generateContextLists();
	},
	
	//------------------//
	// Public methods
	//------------------//
	
	refresh	: function()
	{
		this._getOverdueCount(null);
	},
	
	generateContextLists	: function()
	{
		// Fetch array of placeholders from the page
		var aContextListPlaceholders	= document.body.select('div.' + FollowUp_Link.PLACEHOLDER_CLASS);
		if (aContextListPlaceholders.length > 0)
		{
			// There are place holders in the page, load constants and js files and then create the context lists
			this._require(
				FollowUp_Link.FOLLOWUP_CONSTANT_GROUPS, 
				[
				 	'../ui/javascript/reflex_date_format.js',
				 	'../ui/javascript/reflex_sorter.js',
				 	'javascript/component_followup_context_list.js'
				],
				this._createFollowUpContextLists.bind(this, aContextListPlaceholders)
			);
		}
	},
	
	_createFollowUpContextLists	: function(aContextListPlaceholders)
	{
		var oPlaceholder	= null;
		var sFilled			= null;
		var iType			= null;
		var iTypeDetail		= null;
		for (var i = 0; i < aContextListPlaceholders.length; i++)
		{
			oPlaceholder	= aContextListPlaceholders[i];
			sListIndex		= oPlaceholder.getAttribute('context_list_index');
			if (!sListIndex)
			{
				iType		= parseInt(oPlaceholder.getAttribute('type'));
				iTypeDetail	= parseInt(oPlaceholder.getAttribute('type_detail'));
				if (isNaN(iType) || isNaN(iTypeDetail))
				{
					continue;
				}
				
				this._createFollowUpContextList(oPlaceholder, iType, iTypeDetail);
			}
			else
			{
				//
				// NOTE: Not sure if this will ever happen
				//
				
				// Update the context list within the place holder
				var oContextList	= this._aFollowUpContextLists[parseInt(sListIndex)];
				if (oContextList)
				{
					oContextList.refresh();
				}
			}
		}
	},
	
	_createFollowUpContextList	: function(oPlaceholder, iType, iTypeDetail)
	{
		// Create the context list and insert into the place holder, then update the placeholders list index attribute
		var oList		= new Component_FollowUp_Context_List(oPlaceholder, iType, iTypeDetail);
		var iListIndex	= this._aFollowUpContextLists.push(oList) - 1;
		oPlaceholder.setAttribute('context_list_index', iListIndex);
	},
	
	showAddFollowUpPopup	: function(iType, iTypeDetail, mCallback)
	{
		if (typeof mCallback == 'function')
		{
			this.addNewFollowUpCallback(mCallback);
		}
		else if (mCallback)
		{
			this._sNewFollowUpCallbackURL	= mCallback;
		}
		
		this._require(
			FollowUp_Link.FOLLOWUP_CONSTANT_GROUPS, 
			[
			 	'../ui/javascript/dataset_ajax.js',
			 	'../ui/javascript/reflex_date_format.js',
			 	'../ui/javascript/reflex_validation.js', 
			 	'../ui/javascript/date_time_picker_dynamic.js',
			 	'../ui/javascript/control_field.js',
			 	'../ui/javascript/control_field_select.js',
			 	'../ui/javascript/control_field_text.js',
			 	'../ui/javascript/control_field_date_picker.js',
			 	'javascript/followup_category.js',
			 	'javascript/popup_followup_add.js'
			],
			function()
			{
				var oAddPopup	= 	new Popup_FollowUp_Add(
										iType, 
										iTypeDetail, 
										FollowUpLink._followUpAddComplete.bind(FollowUpLink)
									);
			}
		);
	},
	
	addNewFollowUpCallback	: function(fnCallback, sUrl, bRemoveAfterExecution)
	{
		if (sUrl)
		{
			this._sNewFollowUpCallbackURL	= sUrl;
		}
		else
		{
			this._aNewFollowUpCallbacks.push(
				{
					fnCallback				: fnCallback,
					bRemoveAfterExecution	: bRemoveAfterExecution
				}
			);
		}
	},
	
	//------------------//
	// Private methods
	//------------------//

	_followUpAddComplete	: function()
	{
		if (this._sNewFollowUpCallbackURL)
		{
			// Redirect
			window.location	= this._sNewFollowUpCallbackURL;
		}
		else if (this._aNewFollowUpCallbacks.length)
		{
			// Execute the callbacks
			var oCallback	= null;
			for (var i = 0; i < this._aNewFollowUpCallbacks.length; i++)
			{
				// Execute
				oCallback	= this._aNewFollowUpCallbacks[i];
				oCallback.fnCallback();
				
				// Remove if necessary
				if (oCallback.bRemoveAfterExecution)
				{
					this._aNewFollowUpCallbacks.splice(i, 1);
				}
			}
		}
		else
		{
			// Refresh by default
			window.location	= window.location;
		}
	},
	
	_buildLink	: function()
	{
		this._oCountSpan	= 	$T.span({id: 'followup_link_count'},
									'?'
								);
		this._oLinkDiv.appendChild(this._oCountSpan);
		this._oLinkDiv.appendChild($T.span('Follow-Ups Due'));
		
		// - Handle click to launch popup
		this._oLinkDiv.observe('click', this._showPopup.bind(this));
	},
	
	_getOverdueCount	: function(iTimeout, oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Make request
			this._fnGetCount();
		}
		else if (oResponse.Success)
		{
			// All good
			this._setLinkOverdueCount(oResponse.iCount);
			
			if (!this._bRefreshOverdueCountTimeoutStarted)
			{
				// Every 30s re-get the number of overdue followups (can be done manually by calling refresh(), public function)
				setTimeout(this._getOverdueCount.bind(this), FollowUp_Link.REFRESH_TIMEOUT);
				this._bRefreshOverdueCountTimeoutStarted	= true;
			}
		}
		else
		{
			// Error
			this._ajaxError(oResponse);
		}
	},
	
	_setLinkOverdueCount	: function(iCount)
	{
		this._oCountSpan.innerHTML	= iCount;
		
		// If the number of overdue followups is > 0, start the flash timer
		// - Every minute, turn on, wait .5 sec, turn off, wait .5 sec, turn on, wait .5 sec, turn off, wait minute...
		// - background-color changes, grey to orange/yellow
		if (iCount > 0)
		{
			this._startLinkFlash();
		}
		else
		{
			this._stopLinkFlash();
		}
	},
	
	_ajaxError	: function(oResponse)
	{
		var oConfig	= {sTitle: 'FollowUp Link Error'};
		
		if (oResponse.Message)
		{
			Reflex_Popup.alert(oResponse.Message, oConfig);
		}
		else if (oResponse.ERROR)
		{
			Reflex_Popup.alert(oResponse.ERROR, oConfig);
		}
	},
	
	_showPopup	: function()
	{
		// Refresh the link text
		this.refresh();
		
		// Load constant groups, then javascript files, then go!
		this._require(
			FollowUp_Link.FOLLOWUP_CONSTANT_GROUPS, 
			[
			 	'../ui/javascript/dataset_ajax.js',
			 	'../ui/javascript/reflex_date_format.js', 
			 	'../ui/javascript/date_time_picker_dynamic.js', 
			 	'../ui/javascript/control_field.js',
			 	'../ui/javascript/control_field_select.js', 
			 	'../ui/javascript/control_field_date_picker.js',
			 	'javascript/followup_closure.js',
			 	'javascript/popup_followup_close.js',
			 	'javascript/popup_followup_due_date.js',
			 	'javascript/popup_followup_active.js'
			],
			function()
			{
				var oPopup	= new Popup_FollowUp_Active();
			}
		);
	},
	
	_startLinkFlash	: function()
	{
		if (this._bLinkFlashEnabled)
		{
			return;
		}
		
		this._bLinkFlashEnabled	= true;
		
		// Wait a short time then flash on, starting with a count of 1
		setTimeout(this._flash.bind(this, true, 1), FollowUp_Link.FLASH_TIMEOUT);
	},
	
	_stopLinkFlash	: function()
	{
		this._bLinkFlashEnabled	= false;
	},
	
	_flash	: function(bOn, iCount)
	{
		if (bOn && this._bLinkFlashEnabled)
		{
			this._oLinkDiv.addClassName(FollowUp_Link.FLASH_CSS_CLASS);
			
			// LinkFlash off again
			setTimeout(this._flash.bind(this, false, iCount), FollowUp_Link.FLASH_TIMEOUT);
		}
		else
		{
			this._oLinkDiv.removeClassName(FollowUp_Link.FLASH_CSS_CLASS);
			
			if (this._bLinkFlashEnabled)
			{
				if (iCount == FollowUp_Link.FLASH_COUNT_LIMIT)
				{
					// The maximum flash count has been reached. Wait longer, then flash on again, restart the count
					setTimeout(this._flash.bind(this, true, 1), FollowUp_Link.FLASH_WAIT_TIMEOUT);
				}
				else
				{
					// Increment the count then flash on again
					setTimeout(this._flash.bind(this, true, iCount + 1), FollowUp_Link.FLASH_TIMEOUT);
				}
			}
		}
	},
	
	_require	: function(aConstantGroups, aJSFiles, fnCallback)
	{
		if (aConstantGroups && aConstantGroups.length)
		{
			// Load constant groups
			Flex.Constant.loadConstantGroup(
				aConstantGroups,
				function()
				{
					if (aJSFiles && aJSFiles.length)
					{
						// Load js files then callback
						JsAutoLoader.loadScript(aJSFiles, fnCallback, false);
					}
					else
					{
						// No js files, just callback
						fnCallback();
					}
				}, 
				false
			);
		}
		else if (aJSFiles && aJSFiles.length)
		{
			// Load js files then callback
			JsAutoLoader.loadScript(aJSFiles, fnCallback, false);
		}
	}
});

// Time & Flashing constants
FollowUp_Link.REFRESH_TIMEOUT		= 300000;
FollowUp_Link.FLASH_TIMEOUT			= 200;
FollowUp_Link.FLASH_WAIT_TIMEOUT	= 60000;
FollowUp_Link.FLASH_COUNT_LIMIT		= 3;
FollowUp_Link.FLASH_CSS_CLASS		= 'followup_link_flash';

FollowUp_Link.PLACEHOLDER_CLASS		= 'followup-context-list-placeholder';

// Require constants
FollowUp_Link.FOLLOWUP_CONSTANT_GROUPS	= ['followup_closure_type', 'followup_type', 'followup_recurrence_period'];

// Create single instance
FollowUp_Link.windowLoaded	= function()
{
	FollowUpLink	= new FollowUp_Link();
}

var FollowUpLink	= null;
Event.observe(window, 'load', FollowUp_Link.windowLoaded);


