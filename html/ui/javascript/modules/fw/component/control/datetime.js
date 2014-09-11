
var	Class		= require('../../class'),
	$D			= require('../../dom/factory'),
	Control		= require('../control'),
	DatePicker	= require('../popup/datepicker');

require('../../date');

var self = new Class({
	extends : Control,
	
	construct : function() {
		this.CONFIG = Object.extend({
			sOutputFormat 	: {
				fnGetter	: function(mValue) {
					if (mValue) {
						return mValue;
					} else if (this.get('bTimePicker')) {
						return self.DEFAULT_OUTPUT_FORMAT_DATE_TIME;
					} else {
						return self.DEFAULT_OUTPUT_FORMAT_DATE;
					}
				}.bind(this)
			},
			bTimePicker		: {},
			iYearStart		: {
				fnGetter	: function(mValue) {
					return (mValue ? mValue : self.YEAR_START);
				}
			}, 
			iYearEnd : {
				fnGetter : function(mValue) {
					return (mValue ? mValue : self.YEAR_END);
				}
			}, 
		}, this.CONFIG || {});
		
		this._super.apply(this, arguments);
		this.NODE.addClassName('fw-control-datetime');
	},
	
	_buildUI : function() {
		this._super();
		this._oHidden 	= $D.input({type: 'hidden'});
		this._oInput 	= $D.input({type: 'text', readonly: true});
		this._oIcon 	= $D.img({class: 'fw-control-datetime-launch-icon', src: self.PICKER_LAUNCH_ICON, alt: 'Choose Date', title: 'Choose Date with Picker...', onclick: this._showDatePicker.bind(this)});
		this._oView 	= $D.span();
		this.NODE.appendChild(this._oHidden);
		this.NODE.appendChild(this._oInput);
		this.NODE.appendChild(this._oIcon);
		this.NODE.appendChild(this._oView);
	},
	
	_syncUI : function() {
		this._super();
		
		this._oHidden.name = this.get('sName');
		
		if (!this._oDatePicker) {
			this._oDatePicker = new DatePicker({
				oDate		: new Date(), 
				bTimePicker	: this.get('bTimePicker'), 
				iYearStart	: this.get('iYearStart'), 
				iYearEnd	: this.get('iYearEnd'),
				onchange	: this._datePickerValueChange.bind(this)
			});
		}
		
		this.validate();
		this._onReady();
	},
	
	getAsDate : function() {
		return Date.$parseDate(this._getValue(), this._getDataFormat());
	},
	
	_setEnabled : function() {
		this._oIcon.show();
		this._oInput.show();
		this._oInput.enable();
		this._oView.hide();
	},
	
	_setDisabled : function() {
		this._oIcon.hide();
		this._oInput.show();
		this._oInput.disable();
		this._oView.hide();
	},
	
	_setReadOnly : function() {
		this._oIcon.hide();
		this._oInput.hide();
		this._oView.show();
	},
	
	_setMandatory : function(bMandatory) {
		this._oInput.setAttribute('required', bMandatory);
	},
	
	_setValue : function(mValue) {
		if (!mValue || mValue == '') {
			return;
		}
		
		var sDate = null;
		if (mValue instanceof Date) {
			// Date object
			sDate = mValue.$format(this._getDataFormat());
		} else if (!isNaN(mValue)) {
			// Seconds
			sDate = new Date(mValue * 1000).$format(this._getDataFormat());
		} else {
			// Date string
			sDate = mValue;
		}
		
		if (sDate !== null) {
			this._oHidden.value		= sDate;
			var oDate				= Date.$parseDate(sDate, this._getDataFormat());
			var sView				= (oDate ? oDate.$format(this.get('sOutputFormat')) : '');
			this._oView.innerHTML 	= sView.escapeHTML();
			this._oInput.value		= sView;
		}
	},
	
	_getValue : function() {
		return this._oHidden.value;
	},
	
	_clearValue : function() {
		this._oHidden.value		= '';
		this._oView.innerHTML 	= '';
		this._oInput.value		= '';
	},
	
	_datePickerValueChange : function() {
		this.set('mValue', this._oDatePicker.get('oDate').$format(this._getDataFormat()));
		this.fire('change');
	},
	
	_showDatePicker	: function(oEvent) {
		this._oDatePicker.set('oDate', this.getAsDate());
		this._oDatePicker.show(oEvent);
	},
	
	_getDataFormat : function() {
		return (this.get('bTimePicker') ? self.DATA_FORMAT_DATE_TIME : self.DATA_FORMAT_DATE);
	}
});

Object.extend(self, {
	YEAR_START	: 1900,
	YEAR_END	: 2050,
	
	DATA_FORMAT_DATE_TIME			: 'Y-m-d H:i:s',
	DATA_FORMAT_DATE				: 'Y-m-d',
	DEFAULT_OUTPUT_FORMAT_DATE_TIME : 'd/m/y g:i A',
	DEFAULT_OUTPUT_FORMAT_DATE 		: 'd/m/y',
	
	PICKER_LAUNCH_ICON : 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHOSURBVDjLpZOxa1RBEIe/d/e8FKeFEA2IGBWCICZYBSESBCFglUDSCJZaRBBbK1HQ0s4/QQlCgoKdoBA9sVBshCBETCNRiUUg5PDt7MxY7HuXdxgEycKwyzJ88/vN7Gbuzl5WDvDozeZtd66p21EzQw2iGaqGmhPVaqFodNTs/f0rI+M5gLnfmB0/MPg/le88+TLWU6BmgwDtpevgDhrBFETSORQgAQoBEbZvvUJEB2qAqg8ORw6BxRQeS0gBUkAMsPIdAIm60wNVKwEZrG+AW1JilpRotQNDQwCEOiCWgIXhe1w+f/if3hffrXMhxH4Fooa5kzdT0rNPi3TWlrl6bp7PP1d4ufqCiyNTzIzOUYiz1RWCJECjsuBA3swAmBmdoxu6APza3uDB9EM6a8sAFFEJYsRoOwBRww3yxt+Su6FLq9nqAQuxst11QDTcnX2lhc7XVO3jtw8cOzjMzafzTJ26RJUL0B7Ia020dNlsJAsTJyaZODlZziVj+swsWZb1AarJJUCMeCnn8esfaWruiIKoEtQIkry3mlUx+qfg7owd389prd6+9/7CbsvMrfaQ/O3dhdWzQa0tUZGoaDREjahxV8Dm1u/nANlev/MfAjw0JrMu09AAAAAASUVORK5CYII='
});

return self;
