
var FollowUp_Link = Class.create({
	initialize: function () {
		this._oLinkDiv = document.body.select("div#followup-link").first();
		this._oCountSpan = null;
		this._bLinkFlashEnabled = false;
		this._bRefreshOverdueCountTimeoutStarted = false;
		this._aFollowUpContextLists = [];
		this._sNewFollowUpCallbackURL = null;
		this._aNewFollowUpCallbacks = [];

		// Construct the link
		this._buildLink();

		// Get number of overdue followups
		// Look for a cached value in the cookie, so we can defer the initial call
		var iCachedCount = this.getCachedCount();
		if (iCachedCount != null) {
			this._setLinkOverdueCount(iCachedCount, false);
			setTimeout(this.refresh.bind(this, true), FollowUp_Link.INITIAL_DELAY);
		} else {
			setTimeout(this.refresh.bind(this, true), 0);
		}

		// Generate any context lists necessary
		this.generateContextLists();
	},

	//------------------//
	// Public methods
	//------------------//

	refresh: function (bSetTimeout) {
		this._getOverdueCount(bSetTimeout);
	},

	generateContextLists: function () {
		// Fetch array of placeholders from the page
		var aContextListPlaceholders = document.body.select('div.' + FollowUp_Link.PLACEHOLDER_CLASS);
		JsAutoLoader.registerPreLoadedScripts();

		if (aContextListPlaceholders.length > 0) {
			// There are place holders in the page, load constants and js files and then create the context lists
			this._require(
				FollowUp_Link.FOLLOWUP_CONSTANT_GROUPS,
				[
					'../ui/javascript/dataset_ajax.js',
					'../ui/javascript/reflex_sorter.js',
					'../ui/javascript/section.js',
					'../ui/javascript/section_expandable.js',
					'javascript/actions_and_notes.js',
					'javascript/followup_category.js',
					'javascript/followup_status.js',
					'javascript/popup_followup_view.js',
					'javascript/component_followup_context_list.js'
				],
				this._createFollowUpContextLists.bind(this, aContextListPlaceholders)
			);
		}
	},

	showAddFollowUpPopup: function (iType, iTypeDetail, mCallback) {
		if (typeof mCallback == 'function') {
			this.addNewFollowUpCallback(mCallback);
		} else if (mCallback) {
			this._sNewFollowUpCallbackURL = mCallback;
		}

		this._require(
			FollowUp_Link.FOLLOWUP_CONSTANT_GROUPS,
			[
				'../ui/javascript/dataset_ajax.js',
				'../ui/javascript/reflex_validation.js',
				'../ui/javascript/component_date_picker.js',
				'../ui/javascript/control_field.js',
				'../ui/javascript/control_field_select.js',
				'../ui/javascript/control_field_text.js',
				'../ui/javascript/control_field_date_picker.js',
				'javascript/followup_category.js',
				'javascript/popup_followup_add.js'
			],
			function () {
				var oAddPopup = new Popup_FollowUp_Add(
					iType,
					iTypeDetail,
					FollowUpLink._followUpAddComplete.bind(FollowUpLink)
				);
			}
		);
	},

	addNewFollowUpCallback: function (fnCallback, sUrl, bRemoveAfterExecution) {
		if (sUrl) {
			this._sNewFollowUpCallbackURL = sUrl;
		} else {
			this._aNewFollowUpCallbacks.push({
					fnCallback : fnCallback,
					bRemoveAfterExecution : bRemoveAfterExecution
				}
			);
		}
	},

	//------------------//
	// Private methods
	//------------------//

	_followUpAddComplete: function () {
		if (this._sNewFollowUpCallbackURL) {
			// Redirect
			window.location = this._sNewFollowUpCallbackURL;
		} else if (this._aNewFollowUpCallbacks.length) {
			// Execute the callbacks
			var oCallback = null;
			for (var i = 0; i < this._aNewFollowUpCallbacks.length; i++) {
				// Execute
				oCallback = this._aNewFollowUpCallbacks[i];
				oCallback.fnCallback();

				// Remove if necessary
				if (oCallback.bRemoveAfterExecution) {
					this._aNewFollowUpCallbacks.splice(i, 1);
				}
			}
		} else {
			// Refresh by default
			window.location = window.location;
		}
	},

	_buildLink: function () {
		this._oCountSpan = $T.span({class: 'followup-link-count'},
									'?'
								);

		this._oLinkDiv.appendChild(this._oCountSpan);
		this._oLinkDiv.appendChild($T.span('Follow-Ups Due'));

		// Handle click to launch popup
		this._oLinkDiv.observe('click', this._showPopup.bind(this));
	},

	_getOverdueCount: function (bSetTimeout, oResponse) {
		if (typeof oResponse == 'undefined') {
			// Make request
			if (!this._bRefreshing) {
				this._bRefreshing = true;
				var fnGetCount = jQuery.json.jsonFunction(
					this._getOverdueCount.bind(this, bSetTimeout),
					this._ajaxError.bind(this),
					'FollowUp',
					'getOverdueCountForLoggedInEmployee'
				);
				// Send client 'now' seconds
				fnGetCount(Math.floor(new Date().getTime() / 1000));
			}
		} else if (oResponse.Success) {
			// All good
			this.setCachedCount(oResponse.iCount);
			this._setLinkOverdueCount(oResponse.iCount, bSetTimeout);
			this._bRefreshing = false;

			if (bSetTimeout) {
				// Every few seconds refresh the number of overdue followups (can be done manually by calling refresh(), public function)
				setTimeout(this._refreshTimeout.bind(this), FollowUp_Link.REFRESH_TIMEOUT);
			}
		} else {
			// Error
			this._ajaxError(oResponse);
		}
	},

	_refreshTimeout: function () {
		this.refresh(true);
	},

	_setLinkOverdueCount: function (iCount, bFlash) {
		this._oCountSpan.innerHTML = iCount;

		// If the number of overdue followups is > 0, start the flash timer
		// - Every minute, turn on, wait .5 sec, turn off, wait .5 sec, turn on, wait .5 sec, turn off, wait minute...
		// - background-color changes, grey to orange/yellow
		if (bFlash) {
			if (iCount > 0) {
				this._startLinkFlash();
			} else {
				this._stopLinkFlash();
			}
		}
	},

	_ajaxError: function (oResponse) {
		jQuery.json.errorPopup(oResponse);
	},

	_showPopup: function () {
		// Refresh the link text
		this.refresh();

		// Load constant groups, then javascript files, then go!
		this._require(
			FollowUp_Link.FOLLOWUP_CONSTANT_GROUPS,
			[
				'../ui/javascript/dataset_ajax.js',
				'../ui/javascript/component_date_picker.js',
				'../ui/javascript/control_field.js',
				'../ui/javascript/control_field_select.js',
				'../ui/javascript/control_field_date_picker.js',
				'javascript/followup_closure.js',
				'javascript/followup_modify_reason.js',
				'javascript/popup_followup_close.js',
				'javascript/popup_followup_due_date.js',
				'javascript/popup_followup_active.js'
			],
			function () {
				var oPopup = new Popup_FollowUp_Active();
			}
		);
	},

	_startLinkFlash: function () {
		if (this._bLinkFlashEnabled) {
			return;
		}

		this._bLinkFlashEnabled = true;

		// Wait a short time then flash on, starting with a count of 1
		setTimeout(this._flash.bind(this, true, 1), FollowUp_Link.FLASH_TIMEOUT);
	},

	_stopLinkFlash: function () {
		this._bLinkFlashEnabled = false;
	},

	_flash: function (bOn, iCount) {
		if (bOn && this._bLinkFlashEnabled) {
			this._oLinkDiv.addClassName(FollowUp_Link.FLASH_CSS_CLASS);

			// LinkFlash off again
			setTimeout(this._flash.bind(this, false, iCount), FollowUp_Link.FLASH_TIMEOUT);
		} else {
			this._oLinkDiv.removeClassName(FollowUp_Link.FLASH_CSS_CLASS);

			if (this._bLinkFlashEnabled) {
				if (iCount < FollowUp_Link.FLASH_COUNT_LIMIT) {
					// Increment the count then flash on again
					setTimeout(this._flash.bind(this, true, iCount + 1), FollowUp_Link.FLASH_TIMEOUT);
				} else {
					this._stopLinkFlash();
				}
			}
		}
	},

	_require: function (aConstantGroups, aJSFiles, fnCallback) {
		if (aConstantGroups && aConstantGroups.length) {
			// Load constant groups
			Flex.Constant.loadConstantGroup(
				aConstantGroups,
				function () {
					if (aJSFiles && aJSFiles.length) {
						// Load js files then callback
						JsAutoLoader.loadScript(aJSFiles, fnCallback, false);
					} else {
						// No js files, just callback
						fnCallback();
					}
				},
				false
			);
		} else if (aJSFiles && aJSFiles.length) {
			// Load js files then callback
			JsAutoLoader.loadScript(aJSFiles, fnCallback, false);
		}
	},

	_createFollowUpContextLists: function (aContextListPlaceholders) {
		// Create a context list component for each place holder, if one is already present within the place holder, leave it
		// If there are more than a certain number (SINGLE_GENERATE_LIMIT) placeholders, the details for each context list
		// are preloaded in a 'batch' to reduce multiple ajax requests down to one.
		var oPlaceholder = null;
		var sFilled = null;
		var iType = null;
		var iTypeDetail = null;
		var bBatchProcess = aContextListPlaceholders.length > FollowUp_Link.SINGLE_GENERATE_LIMIT;
		var aBatchProcessDetails = (bBatchProcess ? {aPlaceholders: {}, aContexts: []} : null);
		for (var i = 0; i < aContextListPlaceholders.length; i++) {
			oPlaceholder = aContextListPlaceholders[i];
			sListIndex = oPlaceholder.getAttribute('context_list_index');
			if (!sListIndex) {
				iType = parseInt(oPlaceholder.getAttribute('type'), 10);
				iTypeDetail = parseInt(oPlaceholder.getAttribute('type_detail'), 10);
				if (isNaN(iType) || isNaN(iTypeDetail)) {
					continue;
				}

				if (bBatchProcess) {
					// Cache the details for processing once all have been cached
					if (!aBatchProcessDetails.aPlaceholders[iType]) {
						aBatchProcessDetails.aPlaceholders[iType] = {};
					}

					aBatchProcessDetails.aPlaceholders[iType][iTypeDetail] = oPlaceholder;
					aBatchProcessDetails.aContexts.push({iType: iType, iTypeDetail: iTypeDetail});
				} else {
					// Generate a context list
					this._createFollowUpContextList(oPlaceholder, iType, iTypeDetail);
				}
			} else {
				// Tell the context list within the place holder to refresh its contents
				var oContextList = this._aFollowUpContextLists[parseInt(sListIndex, 10)];
				if (oContextList) {
					oContextList.refresh();
				}
			}
		}

		if (bBatchProcess) {
			this._batchProcessFollowUpData(aBatchProcessDetails.aPlaceholders, aBatchProcessDetails.aContexts);
		}
	},

	_batchProcessFollowUpData: function (aPlaceHolders, aContexts, oResponse) {
		if (typeof oResponse == 'undefined') {
			// Make request to get all followup data for the given types & type details
			var fnGetFollowUps = jQuery.json.jsonFunction(
				this._batchProcessFollowUpData.bind(this, aPlaceHolders, aContexts),
				this._ajaxError.bind(this),
				'FollowUp',
				'getFollowUpsFromMultipleContexts'
			);
			fnGetFollowUps(aContexts);
		} else if (oResponse.Success) {
			// Success! Create the context lists
			for (var iType in oResponse.aResults) {
				for (var iTypeDetail in oResponse.aResults[iType]) {
					this._createFollowUpContextList(
						aPlaceHolders[iType][iTypeDetail],
						iType,
						iTypeDetail,
						oResponse.aResults[iType][iTypeDetail]
					);
				}
			}
		} else {
			// Error
			this._ajaxError(oResponse);
		}
	},

	_createFollowUpContextList: function (oPlaceholder, iType, iTypeDetail, oFollowUpData) {
		// Create the context list and insert into the place holder, then update the placeholders list index attribute
		var oList = new Component_FollowUp_Context_List(oPlaceholder, iType, iTypeDetail, oFollowUpData);
		var iListIndex = this._aFollowUpContextLists.push(oList) - 1;
		oPlaceholder.setAttribute('context_list_index', iListIndex);
	},

	getCachedCount: function () {
		return Flex.cookie.read(FollowUp_Link.CACHE_COOKIE_KEY);
	},

	setCachedCount: function (iCount) {
		return Flex.cookie.create(FollowUp_Link.CACHE_COOKIE_KEY, iCount, FollowUp_Link.CACHE_TIMEOUT_DAYS);
	}
});

// Time & Flashing constants
FollowUp_Link.REFRESH_TIMEOUT = 60000;
FollowUp_Link.FLASH_TIMEOUT = 200;
//FollowUp_Link.FLASH_WAIT_TIMEOUT = 60000;
FollowUp_Link.FLASH_COUNT_LIMIT = 3;
FollowUp_Link.FLASH_CSS_CLASS = 'followup-link-flash';

FollowUp_Link.PLACEHOLDER_CLASS = 'followup-context-list-placeholder';

FollowUp_Link.SINGLE_GENERATE_LIMIT = 3;

FollowUp_Link.CACHE_COOKIE_KEY = 'followup.link.count';
FollowUp_Link.CACHE_TIMEOUT_DAYS = 1;
FollowUp_Link.INITIAL_DELAY = FollowUp_Link.REFRESH_TIMEOUT;

// Require constants
FollowUp_Link.FOLLOWUP_CONSTANT_GROUPS = ['followup_closure_type', 'followup_type', 'followup_recurrence_period'];

// Create single instance
FollowUp_Link.windowLoaded = function () {
	FollowUpLink = new FollowUp_Link();
};

var FollowUpLink = null;
Event.observe(window, 'load', FollowUp_Link.windowLoaded);
