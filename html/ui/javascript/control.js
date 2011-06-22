
var Control = Class.create(Reflex_Component, {
	initialize : function($super) {
		this.CONFIG = Object.extend({
			sName 	: {},
			sLabel	: {
				fnGetter : function(mValue) {
					return (mValue ? mValue : '');
				}
			},
			mMandatory 		: {},
			fnValidate		: {},
			iControlState 	: {
				fnSetter : this._setState.bind(this)
			},
			mValue : {
				fnSetter : function(mValue) {
					if (typeof this._sValidationReason != 'undefined') {
						throw "Cannot set mValue after initialisation";
					}
					return mValue;
				}.bind(this)
			},
			bValidationStyling : {
				fnGetter : function(mValue) {
					return ((Object.isUndefined(mValue) || mValue === null) ? true : mValue);
				}
			}
		}, this.CONFIG || {});
		
		$super.apply(this, $A(arguments).splice(1));
		this.NODE.addClassName('control');
	},
	
	// START: Component life-cycle
	
	_buildUI : function() {
		this.NODE = $T.div();
	},

	_syncUI : function() {
		this.NODE.observe('mousemove', this._positionValidationError.bind(this));
		this._oValidationErrorElement = $T.div({class:'control-validation-error'});
		this.NODE.appendChild(this._oValidationErrorElement);
		
		if (this.get('mValue')) {
			this.setValue(this.get('mValue'));
		}
		
		if (!this.get('iControlState')) {
			this.set('iControlState', Control.STATE_ENABLED);
		}
		
		if (Object.isUndefined(this._sValidationReason)) {
			this._sValidationReason = null;
		}
	},
	
	// END: Component life-cycle
	
	validate : function(bSilentFail) {
		this._validate(bSilentFail);
	},
	
	setValue : function(mValue) {
		this._setValue(mValue);
		this._validate(mValue, true);
	},
	
	getValue : function() {
		return this._getValue();
	},
	
	clearValue : function() {
		this._clearValue();
	},
	
	_validate : function(bSilentFail) {
		bSilentFail = (Object.isUndefined(bSilentFail) ? true : !!bSilentFail);
		var mValue 	= this._getValue();
		
		if (this.get('bValidationStyling')) {
			this.NODE.removeClassName('valid');
			this.NODE.removeClassName('invalid');
			this.NODE.removeClassName('mandatory');
		}
		
		if (mValue) {
			var bValid = false;
			try {
				bValid = this._isValid(false);
			} catch (mException) {
				var sValidationReason = '';
				if (typeof mException === 'string') {
					sValidationReason = mException;
				} else {
					sValidationReason = mException.toString();
				}
			}
			
			if (bValid) {
				if (this.get('bValidationStyling')) {
					this.NODE.addClassName('valid');
				}
			} else {
				if (this.get('bValidationStyling')) {
					this.NODE.addClassName('invalid');
				}

				sValidationReason = sValidationReason ? sValidationReason : this._getValidationReason();
				this._setValidationReason(sValidationReason);
				var	sValidationMessage = "'" + mValue + "' is not a valid " + this.get('sLabel') + ". " + sValidationReason;
				if (bSilentFail) {
					return false;
				} else {
					throw sValidationMessage;
				}
			}
		} else if (this._isMandatory()) {
			if (this.get('bValidationStyling')) {
				this.NODE.addClassName('mandatory');
			}
			
			this._setValidationReason('No value supplied, this field is mandatory.');
			
			if (bSilentFail) {
				return false;
			} else {
				throw "No value for mandatory field '" + this.get('sLabel') + "'.";
			}
		}
		return true;
	},
	
	_isValid : function(bSuppressException) {
		var fnValidate = this.get('fnValidate');
		if (typeof fnValidate == 'function') {
			try {
				return !!fnValidate(this);
			} catch (mException) {
				if (bSuppressException === false) {
					throw mException;
				} else {
					return false;
				}
			}
		} else {
			return true;
		}
	},
	
	_isMandatory : function() {
		var mMandatory = this.get('mMandatory');
		var bMandatory = false;
		if (typeof mMandatory == 'function') {
			return mMandatory();
		} else {
			return !!mMandatory;
		}
	},
	
	_setValue : function(mValue) {
		// No base functionality
	},
	
	_getValue : function() {
		// No base functionality
	},
	
	_clearValue : function() {
		// No base functionality
	},
	
	_setEnabled : function() {
		// No base functionality
	},
	
	_setReadOnly : function() {
		// No base functionality
	},
	
	_setDisabled : function() {
		// No base functionality
	},
	
	_setState : function(mState) {
		var iState 	= parseInt(mState, 10);
		this._setValue(this.getValue());
		
		switch (iState) {
			case Control.STATE_ENABLED:
				this._setEnabled();
				break;
				
			case Control.STATE_READ_ONLY:
				this._setReadOnly();
				break;
				
			case Control.STATE_DISABLED:
				this._setDisabled();
				break;
		}
		return iState;
	},
	
	_setValidationReason : function(sReason) {
		this._oValidationErrorElement.innerHTML = (sReason ? sReason.escapeHTML() : '');
		this._sValidationReason 				= sReason;
	},
	
	_getValidationReason : function() {
		return this._sValidationReason;
	},
	
	_positionValidationError : function (oEvent) {
		var sValidationReason = this._getValidationReason();
		if (sValidationReason && sValidationReason.length) {
			this._oValidationErrorElement.setStyle({
				top		: oEvent.clientY + (Object.isNumber(Control.VALIDATION_TOOLTIP_OFFSET_Y) ? Control.VALIDATION_TOOLTIP_OFFSET_Y : 0)+'px',
				left	: oEvent.clientX + (Object.isNumber(Control.VALIDATION_TOOLTIP_OFFSET_X) ? Control.VALIDATION_TOOLTIP_OFFSET_X : 0)+'px'
			});
		}
	},
});

Object.extend(Control, {
	STATE_ENABLED 	: 1,
	STATE_READ_ONLY	: 2,
	STATE_DISABLED 	: 3,

	VALIDATION_TOOLTIP_OFFSET_X	: 12,
	VALIDATION_TOOLTIP_OFFSET_Y	: 4
});
