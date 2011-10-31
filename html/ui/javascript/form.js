
var Form = Class.create(Reflex_Component, {
	initialize : function($super) {
		this.CONFIG = Object.extend({}, this.CONFIG || {});
		$super.apply(this, $A(arguments).splice(1));
		this.NODE.addClassName('form');
	},
	
	_buildUI : function() {
		this.NODE = $T.form();
		this.NODE.observe('submit', this._submit.bind(this));
	},
	
	_syncUI : function() {
		this._onReady();
	}, 
	
	submit : function() {
		this._submit();
	},
	
	getControls : function(bHashByName) {
		bHashByName		= (Object.isUndefined(bHashByName) ? false : bHashByName);
		var mControls 	= (bHashByName ? {} : []);
		this.NODE.select('.control').each(
			function(oElement) {
				var oControl = oElement.oReflexComponent;
				if (bHashByName) {
					var sFieldName = oControl.get('sName');
					if ((oControl instanceof Control_Radio) && !oControl.get('bChecked')) {
						// Ignore it, an unchecked radio control
						return;
					}
					mControls[sFieldName] = oControl;
				} else {
					mControls.push(oControl);
				}
			}
		);
		return mControls;
	},
	
	control : function(sName) {
		var oControl 	= null;
		var aElements	= this.NODE.select('.control');
		for (var i = 0; i < aElements.length; i++) {
			var oElement = aElements[i];
			if (oElement.oReflexComponent && (oElement.oReflexComponent.get('sName') == sName)) {
				oControl = oElement.oReflexComponent;
				break;
			}
		}
		return oControl;
	},
	
	validate : function() {
		var aErrors = [];
		this.NODE.select('.control').each(
			function(oElement) {
				try {
					oElement.oReflexComponent.validate(false);
				} catch(oException) {
					aErrors.push(oException);
				}
			}
		);
		
		if (aErrors.length) {
			var oErrorElement = $T.ul();
			for (var i = 0; i < aErrors.length; i++) {
				oErrorElement.appendChild($T.li(aErrors[i]));
			}
			
			Reflex_Popup.alert(
				$T.div({class: 'validation-error-content'},
					$T.div('There were errors in the form:'),
					oErrorElement
				),
				{sTitle: 'Validation Error', iWidth: 30}
			);
			return false;
		}
		return true;
	},
	
	getData : function() {
		// Validate & extract values from all enabled fields
		var oControls 	= this.getControls(true);
		var oFieldData	= {};
		
		var aNamespaces				= null;
		var sNamespace				= null;
		var oCurrent				= null;
		var oCurrentParent			= null;
		var mValue					= null;
		var sArrayIndex				= null;
		var sPreviousArrayIndex		= null;
		var aArrayMatch				= null;
		var sLastRawFullNamespace	= '';
		var oArrayIndexes			= {};
		var aControls				= [];
		var oPreviousArrayIndexes	= null;
		var sRawFullNamespace		= null;
		
		for (var sFieldName in oControls) {
			aNamespaces				= sFieldName.split('.');
			oCurrent				= oFieldData;
			oCurrentParent			= null;
			sNamespace				= null;
			aArrayMatch				= null;
			sPreviousArrayIndex		= null;
			sArrayIndex				= null;
			sLastRawFullNamespace	= '';
			mValue					= null;
			oPreviousArrayIndexes 	= {};
			sRawFullNamespace 		= '';
			
			// Iterate over each part (namespace) in the field name
			for (var i = 0; i < aNamespaces.length; i++) {
				sNamespace	= aNamespaces[i];
				mValue		= {};
				
				// Check if the namespace defines an array index
				if (aArrayMatch = sNamespace.match(/\[(.*)\]/)) {
					// The current namespace is an array (has [?] in the namespace).
					sArrayIndex			= aArrayMatch[1];
					mValue				= [];
					sRawFullNamespace 	+= sNamespace + '_';
					sNamespace			= sNamespace.replace(/\[.*\]/, '');
					
					if (!oArrayIndexes[sRawFullNamespace]) {
						oArrayIndexes[sRawFullNamespace] = {};
					}
				} else {
					// The current namespace is an object.
					sRawFullNamespace += sNamespace + '_';
				}
				
				oPreviousArrayIndexes[sRawFullNamespace] = sArrayIndex;
				
				if (typeof oCurrent.length !== 'undefined') {
					// The current namespace object is an array, the arrays namespace & the unique index was within the namespace 
					// before this one. Check if it has already been used at the index specified in it's namespace.
					sPreviousArrayIndex	= oPreviousArrayIndexes[sLastRawFullNamespace];
					var oArrayChild		= null;
					if (typeof oArrayIndexes[sLastRawFullNamespace][sPreviousArrayIndex] !== 'undefined') {
						// The array has been used at the index specified in the namespace, reuse it.
						oArrayChild	= oCurrent[oArrayIndexes[sLastRawFullNamespace][sPreviousArrayIndex]];
					} else {
						// The array has not yet been used, create a new object and push it, recording the new index as being used.
						oArrayChild													= {};
						oArrayIndexes[sLastRawFullNamespace][sPreviousArrayIndex]	= oCurrent.push(oArrayChild) - 1;
					}
					
					// Check if the array child has already got a value at the current namespace
					if (typeof oArrayChild[sNamespace] === 'undefined') {
						oArrayChild[sNamespace]	= mValue;
					}
					
					oCurrentParent			= oArrayChild;
					oCurrent				= oArrayChild[sNamespace];
					sLastRawFullNamespace	= sRawFullNamespace;
					continue;
				} else if (typeof oCurrent[sNamespace] === 'undefined') {
					oCurrent[sNamespace] = mValue;
				}
				
				oCurrentParent			= oCurrent;
				oCurrent				= oCurrent[sNamespace];
				sLastRawFullNamespace	= sRawFullNamespace;
			}
			
			// Store the end value
			var mValue	= oControls[sFieldName].getValue();
			if (typeof oCurrent.length !== 'undefined') {
				// Array was the last namespace object so append or override
				if (typeof oArrayIndexes[sLastRawFullNamespace][sArrayIndex] !== 'undefined') {
					oCurrent[oArrayIndexes[sLastRawFullNamespace][sArrayIndex]] = mValue;
				} else {
					oArrayIndexes[sLastRawFullNamespace][sArrayIndex] = oCurrent.push(mValue);
				}
			} else {
				oCurrentParent[sNamespace] = mValue;
			}
		}
		
		return oFieldData;
	},
	
	_submit : function(oEvent) {
		if (this.validate()) {
			this.fire('submit');
		}
		
		if (oEvent) {
			oEvent.stop();
		}
	}
});