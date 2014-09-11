
var	Class		= require('../class'),
	$D			= require('../dom/factory'),
	Component	= require('../component');

var self = new Class({
	extends	: Component,

	construct : function() {
		this.CONFIG = Object.extend({
			sName 	: {},
			sLabel 	: {
				fnGetter : function(sLabel) {
					return (sLabel ? sLabel : '');
				}
			},
			mMandatory 		: {},
			fnValidate 		: {},
			iControlState 	: {},
			mValue 			: {
				fnSetter : function(mValue) {
					return {
						mValue 		: mValue,
						bChanged	: mValue != this.get('mValue')
					};
				}.bind(this)
			}
		}, this.CONFIG || {});
		
		this._sValidationReason = null;
		this._oValidationError	= null;
		
		this._super.apply(this, arguments);
		
		this.NODE.addClassName('fw-control');
	},
	
	// Component Lifecycle
	//------------------------------------------------------------------------//
	_buildUI : function() {
		this._oValidationError 	= $D.div({'class': 'fw-control-validation-error'});
		this.NODE 				= $D.span();
		this.NODE.observe('mousemove', this._positionValidationError.bind(this));
		this.NODE.appendChild(this._oValidationError);
	},

	_syncUI : function() {
		var	mValue			= this.get('mValue'),
			iControlState	= this.get('iControlState');
		
		this._setState(iControlState ? iControlState : self.STATE_ENABLED);
		
		if (mValue && mValue.bChanged) {
			this._setValue(mValue.mValue);
		} else if (!this._bInitialised) {
			// Still initialising the control, clear the value because none provided
			this.clearValue();
		}
		
		this._validate(true);
	},
	//------------------------------------------------------------------------//
	
	validate : function(bSilentFail) {
		return this._validate(bSilentFail);
	},
	
	getValue : function() {
		return this._getValue();
	},
	
	clearValue : function() {
		this._clearValue();
	},
	
	isValid : function(bSuppressException) {
		return this._isValid(bSuppressException);
	},
	
	_validate : function(bSilentFail) {
		bSilentFail = (Object.isUndefined(bSilentFail) ? true : !!bSilentFail);
		var mValue 	= this._getValue();
		
		this.NODE.removeClassName('-valid');
		this.NODE.removeClassName('-invalid');
		this.NODE.removeClassName('-novalue');
		
		var bMandatory = this._isMandatory();		
		if (mValue) {
			var bValid = false;
			try {
				bValid = this._isValid(false);
			} catch (mException) {
				var sValidationReason = '';
				if (typeof mException === 'string') {
					sValidationReason = mException;
				} else if (mException.message) {
					sValidationReason = mException.message;
				} else {
					sValidationReason = mException.toString();
				}
			}
			
			if (bValid) {
				this.NODE.addClassName('-valid');
			} else {
				this.NODE.addClassName('-invalid');
				
				sValidationReason = sValidationReason ? sValidationReason : this._getValidationReason();
				this._setValidationReason(sValidationReason);
				var	sValidationMessage = "'" + mValue + "' is not a valid " + this.get('sLabel') + ". " + sValidationReason;
				if (bSilentFail) {
					return false;
				} else {
					throw sValidationMessage;
				}
			}
		} else if (bMandatory) {
			this.NODE.addClassName('-novalue');
			
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
			bMandatory = mMandatory();
		} else {
			bMandatory = !!mMandatory;
		}
		
		this.NODE.setAttribute('required', bMandatory);
		this._setMandatory(bMandatory);
		return bMandatory;
	},
	
	_setValue : function(mValue) {
		// No base functionality
	},
	
	_getValue : function() {
		// No base functionality
	},
	
	_setEnabled : function() {
		// No base functionality
	},
	
	_setReadOnly : function() {
		// No base functionality
	},

	_setMandatory : function () {
		// No base functionality
	},
	
	_setState : function(mState) {
		var iState 	= parseInt(mState, 10);
		this._setValue(this._getValue());
		
		switch (iState) {
			case self.STATE_ENABLED:
				this._setEnabled();
				break;
				
			case self.STATE_READ_ONLY:
				this._setReadOnly();
				break;
				
			case self.STATE_DISABLED:
				this._setDisabled();
				break;
		}
		return iState;
	},
		
	_setValidationReason : function(sReason) {
		this._oValidationError.innerHTML 	= (sReason ? sReason.escapeHTML() : '');
		this._sValidationReason 			= sReason;
	},
	
	_getValidationReason : function() {
		return (this._sValidationReason ? this._sValidationReason : '');
	},
	
	_positionValidationError : function (oEvent) {
		var sValidationReason = this._getValidationReason();
		if (sValidationReason && sValidationReason.length) {
			this._oValidationError.setStyle({
				top		: oEvent.clientY + (Object.isNumber(self.VALIDATION_TOOLTIP_OFFSET_Y) ? self.VALIDATION_TOOLTIP_OFFSET_Y : 0)+'px',
				left	: oEvent.clientX + (Object.isNumber(self.VALIDATION_TOOLTIP_OFFSET_X) ? self.VALIDATION_TOOLTIP_OFFSET_X : 0)+'px'
			});
		}
	},
	
	statics	: {
		STATE_ENABLED 	: 1,
		STATE_READ_ONLY	: 2,
		STATE_DISABLED 	: 3,

		VALIDATION_TOOLTIP_OFFSET_X	: 12,
		VALIDATION_TOOLTIP_OFFSET_Y	: 4,
		
		EXTENSION_POINTS : ['getValue', '_getValue']
	}
});

return self;
