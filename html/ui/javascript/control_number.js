
var Control_Number = Class.create(Control, {
	initialize : function($super) {
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
		
		$super.apply(this, $A(arguments).slice(1));
		this.NODE.addClassName('control-number');
	},
	
	_buildUI : function() {
		this.NODE = $T.div(
			this._oInput = $T.input({type: 'number'}),
			this._oView = $T.span()
		);
		
		var fnChange = this._valueChange.bind(this);
		this._oInput.observe('change', fnChange);
		this._oInput.observe('click', fnChange);
		this._oInput.observe('keyup', fnChange);
	},
	
	_syncUI : function($super) {
		$super();
		this._oInput.name = this.get('sName');
		this.validate();
	},
	
	// Override
	_isValid : function($super, bSuppressException) {
		var	bValid	= $super(bSuppressException);
		var mValue	= this.getValue();
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
	
	_setValue : function(mValue) {
		this._oInput.value 		= mValue;
		this._oView.innerHTML	= mValue.toString();
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

Object.extend(Control_Number, {
	YEAR_START	: 1900,
	YEAR_END	: 2050,
	
	DATA_FORMAT				: 'Y-m-d H:i:s',
	DEFAULT_OUTPUT_FORMAT 	: 'd/m/y g:i A'
});
