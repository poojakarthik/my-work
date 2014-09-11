
var Component_Debug_Log = Class.create(Reflex_Component, {
	initialize : function($super) {
		// Additional Configuration
		this.CONFIG	= Object.extend({
			// No additional config... yet
		}, this.CONFIG || {});
	
		// Parent Constructor
		$super.apply(this, $A(arguments).slice(1));
		
		this.NODE.addClassName('component-debug-log');
	},
	
	_buildUI : function() {
		var oCheckbox 	= $T.input({type: 'checkbox', onchange: this._toggleLogEnabled.bind(this)});
		var oIcon		= $T.img({class: 'pointer', src: '../admin/img/template/bug.png', alt: 'View Debug Log', title: 'View Debug Log', onclick: this._viewDebugLog.bind(this)});
		this.NODE 		= $T.div(
			oCheckbox,
			$T.span('Enable Debug Log'),
			oIcon
		);
		
		if (Flex.cookie.read(Component_Debug_Log.LOG_COOKIE_NAME) == '1') {
			oCheckbox.checked = true;
		} else {
			this._updateIcon(0);
		}
	},
	
	_syncUI : function() {
		this._onReady();
	},
	
	_toggleLogEnabled : function(oEvent) {
		var iLogEnabled = (oEvent.target.checked ? 1 : 0);
		Flex.cookie.create(Component_Debug_Log.LOG_COOKIE_NAME, iLogEnabled, 365);
		this._updateIcon(iLogEnabled);
	},
	
	_updateIcon : function(iLogEnabled) {
		var oIcon = this.NODE.select('img').first();
		if (iLogEnabled) {
			oIcon.removeClassName('component-debug-log-disabled-icon');
			oIcon.alt 	= 'View Debug Log';
			oIcon.title	= oIcon.alt;
		} else {
			oIcon.addClassName('component-debug-log-disabled-icon');
			oIcon.alt 	= 'View Debug Log (Disabled)';
			oIcon.title	= oIcon.alt;
		}
	},
	
	_viewDebugLog : function() {
		var oDiv = $T.div({class: 'component-debug-log-view'});
		if (!Component_Debug_Log._aLogStrings.length) {
			// No logging
			Reflex_Popup.alert('There is no logging to display', {sTitle: 'Debug Log'});
		} else {
			// Show each string
			for (var i = 0; i < Component_Debug_Log._aLogStrings.length; i++) {
				var sClassName = 'component-debug-log-view-string';
				if ((Component_Debug_Log._iLatestViewedString !== null) && (i <= Component_Debug_Log._iLatestViewedString)) {
					sClassName += ' component-debug-log-view-string-old'
				}
				oDiv.appendChild(
					$T.pre({class: sClassName},
						Component_Debug_Log._aLogStrings[i]
					)
				);
			}
			Component_Debug_Log._iLatestViewedString = Component_Debug_Log._aLogStrings.length - 1;
			Reflex_Popup.alert(oDiv, {iWidth: 50, sTitle: 'Debug Log'});
		}
	}
});

Object.extend(Component_Debug_Log, {
	LOG_COOKIE_NAME : 'json_handler_log_enabled',
	
	_aLogStrings 			: [],
	_iLatestViewedString 	: null,
	
	createInPlaceholder : function() {
		var oContainer = document.body.select('#component-debug-log-container').first();
		if (!oContainer)
		{
			Flex.cookie.erase(Component_Debug_Log.LOG_COOKIE_NAME);
			return;
		}
		
		if (!oContainer.childNodes.length)
		{
			var oComponent = new Component_Debug_Log();
			oContainer.appendChild(oComponent.getNode());
		}
	},
	
	extractLogStringFromJSONResponse : function(oResponse) {
		if (oResponse instanceof Reflex_AJAX_Response) {
			Component_Debug_Log._aLogStrings.push(oResponse.getDebugLog());
		} else if (oResponse.sDebug && (oResponse.sDebug != '')) {
			Component_Debug_Log._aLogStrings.push(oResponse.sDebug);
		}
	}
});

Event.observe(window, 'load', Component_Debug_Log.createInPlaceholder);