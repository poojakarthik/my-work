
var Control_Datetime = Class.create(Control, {
	initialize : function($super) {
		this.CONFIG = Object.extend({
			sOutputFormat 	: {
				fnGetter	: function(mValue) {
					return (mValue ? mValue : Control_Datetime.DEFAULT_OUTPUT_FORMAT);
				}
			},
			bTimePicker		: {},
			iYearStart		: {
				fnGetter	: function(mValue) {
					return (mValue ? mValue : Control_Datetime.YEAR_START);
				}
			}, 
			iYearEnd : {
				fnGetter : function(mValue) {
					return (mValue ? mValue : Control_Datetime.YEAR_END);
				}
			}, 
		}, this.CONFIG || {});
		
		$super.apply(this, $A(arguments).slice(1));
		this.NODE.addClassName('control-datetime');
	},
	
	_buildUI : function() {
		this.NODE = $T.div(
			this._oHidden = $T.input({type: 'hidden'}),
			this._oInput = $T.input({type: 'text', readonly: true}),
			this._oIcon = $T.img({class: 'date-time-picker-launch-icon', src: '../admin/img/template/calendar_small.png', alt: 'Choose Date', title: 'Choose Date with Picker...', onclick: this._showDatePicker.bind(this)}),
			this._oView = $T.span()
		);
	},
	
	_syncUI : function($super) {
		$super();
		this._oHidden.name 	= this.get('sName');
		this._oDatePicker 	= new Component_Date_Picker(
			new Date(), 
			this.get('bTimePicker'), 
			this.get('iYearStart'), 
			this.get('iYearEnd'),
			null, 
			this._datePickerValueChange.bind(this)
		);
		this.validate();
	},
	
	getAsDate : function() {
		return Date.$parseDate(this.getValue(), Control_Datetime.DATA_FORMAT);
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
	
	_setValue : function(mValue) {
		if (!mValue || mValue == '') {
			return;
		}
		
		var sDate = null;
		if (mValue instanceof Date) {
			// Date object
			sDate = mValue.$format(Control_Datetime.DATA_FORMAT);
		} else if (!isNaN(mValue)) {
			// Seconds
			sDate = new Date(mValue * 1000).$format(Control_Datetime.DATA_FORMAT);
		} else {
			// Date string
			sDate = mValue;
		}
		
		if (sDate !== null) {
			this._oHidden.value		= sDate;
			var sView				= Date.$parseDate(sDate, Control_Datetime.DATA_FORMAT).$format(this.get('sOutputFormat'));
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
	
	_datePickerValueChange : function(oDate) {
		this.setValue(oDate.$format(Control_Datetime.DATA_FORMAT));
		this.fire('change');
	},
	
	_showDatePicker	: function(oEvent) {
		this._oDatePicker.show(oEvent);
	},
});

Object.extend(Control_Datetime, {
	YEAR_START	: 1900,
	YEAR_END	: 2050,
	
	DATA_FORMAT				: 'Y-m-d H:i:s',
	DEFAULT_OUTPUT_FORMAT 	: 'd/m/y g:i A'
});
