
var	Class	= require('../../class'),
	$D		= require('../../dom/factory'),
	Control	= require('../control');

var self = new Class({
	extends : Control,
	
	construct : function() {
		this.CONFIG = Object.extend({
			iDecimalPlaces : {
				fnSetter : function(iDecimalPlaces) {
					var	mProvidedData	= iDecimalPlaces;
					var iDecimalPlaces	= parseInt(iDecimalPlaces, 10);
					if (typeof iDecimalPlaces === 'number' && iDecimalPlaces === iDecimalPlaces && iDecimalPlaces >= 0) {
						this._oInput.setAttribute('step', 1 / Math.pow(10, iDecimalPlaces));
						return iDecimalPlaces;
					} else if (!mProvidedData || iDecimalPlaces <= 0) {
						this._oInput.removeAttribute('step');
						return null;
					} else {
						throw "Unable to set Decimal Places to '" + mProvidedData + "'";
					}
				}.bind(this)
			},
			fMaximumValue : {
				fnSetter : function(mMaximumValue) {
					var	fMaximumValue = parseFloat(mMaximumValue);
					if (typeof fMaximumValue === 'number' && fMaximumValue === fMaximumValue) {
						this._oInput.setAttribute('max', fMaximumValue);
						return fMaximumValue;
					} else if (!mMaximumValue) {
						this._oInput.removeAttribute('max');
						return null;
					} else {
						throw "Unable to set Maximum Value to '" + mMaximumValue + "'";
					}
				}.bind(this)
			},
			fMinimumValue : {
				fnSetter : function(mMinimumValue) {
					var	fMinimumValue = parseFloat(mMinimumValue);
					if (typeof fMinimumValue === 'number' && fMinimumValue === fMinimumValue) {
						this._oInput.setAttribute('min', fMinimumValue);
						return fMinimumValue;
					} else if (!mMinimumValue) {
						this._oInput.removeAttribute('min');
						return null;
					} else {
						throw "Unable to set Minimum Value to '" + mMinimumValue + "'";
					}
				}.bind(this)
			}
		}, this.CONFIG || {});
		
		this._super.apply(this, arguments);
		this.NODE.addClassName('fw-control-number');
	},
	
	_buildUI : function() {
		this._super();
		this._oInput 	= $D.input({type: 'number'});
		this._oView 	= $D.span({'class': 'fw-control-number-view'})
		this.NODE.appendChild(this._oInput);
		this.NODE.appendChild(this._oView);
		
		var fnChange = this._valueChange.bind(this);
		this._oInput.observe('change', fnChange);
		this._oInput.observe('click', fnChange);
		this._oInput.observe('keyup', fnChange);
	},
	
	_syncUI : function() {
		this._super();
		this._oInput.name = this.get('sName');
		this.validate();
		this._onReady();
	},
	
	// Override
	_isValid : function(bSuppressException) {
		var	bValid	= this._super(bSuppressException);
		var mValue	= this._getValue();
		var fValue	= parseFloat(mValue);
		
		if (fValue === null || mValue === null) {
			return bValid;
		}
	
		var iDecimalPlaces 	= this.get('iDecimalPlaces');
		var fMinimumValue	= this.get('fMinimumValue');
		var fMaximumValue	= this.get('fMaximumValue');
		
		try {
			// General Format
			if (!mValue.match(/^[\-\+]?[\d]*([\.][\d]+)?$/)) {
				throw "Not a numeric value";
			}
	
			// Decimal Places
			if (iDecimalPlaces !== null
				&& mValue.toString().indexOf('.') > -1
				&& iDecimalPlaces < (mValue.toString().length - (mValue.toString().indexOf('.') + 1))
			) {
				throw ((iDecimalPlaces === 0) ? 'No' : 'Only' + iDecimalPlaces) + ' decimal places are allowed';
			}
	
			// Minimum Value
			if (fMinimumValue !== null && fMinimumValue > mValue) {
				throw 'Below the Minimum allowed (' + fMinimumValue + ')';
			}
	
			// Maximum Value
			if (fMaximumValue !== null && fMaximumValue < mValue) {
				throw 'Above the Maximum allowed (' + fMaximumValue + ')';
			}
		} catch (mException) {
			if (bSuppressException === false) {
				throw mException;
			} else {
				bValid = false;
			}
		}
	
		return bValid;
	},
	
	_setEnabled : function() {
		this._oInput.show();
		this._oInput.enable();
		this._oView.hide();
	},
	
	_setDisabled : function() {
		this._oInput.show();
		this._oInput.disable();
		this._oView.hide();
	},
	
	_setReadOnly : function() {
		this._oInput.hide();
		this._oView.show();
	},

	_setMandatory : function(bMandatory) {
		if (bMandatory) {
			this._oInput.setAttribute('required', 'required');
		} else {
			this._oInput.removeAttribute('required');
		}
	},
	
	_setValue : function(mValue) {
		var sValue				= (mValue !== null ? mValue.toString() : '');
		this._oInput.value 		= sValue;
		this._oView.innerHTML	= sValue;
	},
	
	_getValue : function() {
		return this._oInput.value;
	},
	
	_clearValue : function() {
		this._setValue('');
	},
	
	_valueChange : function() {
		this.validate();
		this.fire('change');
	}
});

return self;
