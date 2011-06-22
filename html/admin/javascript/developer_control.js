
var Developer_Control = Class.create(Reflex_Component, {
	initialize : function($super) {
		this.CONFIG = Object.extend({}, this.CONFIG || {});
		$super.apply(this, $A(arguments).splice(1));
	},
	
	_buildUI : function() {
		this.NODE = $T.div(
			this._oForm = new Form(
				{
					onsubmit : function() {
						var aControls 	= this._oForm.getControls();
						var oData 		= this._oForm.getData();
						debugger;
					}.bind(this)
				},
				$T.table(
					$T.tbody(
						this._text('my.name'),
						this._select('my.blah.type'),
						this._checkbox('my.enabled'),
						this._radio('my.thing.stuff[0]'),
						this._datetime('my.thing.stuff[1].date'),
						this._number('my.thing.stuff[2].number'),
						this._password('my.pass'),
						this._textarea('my.story'),
						this._hidden('my.useme'),
						this._textajax('my.template')
					)
				),
				$T.div($T.button('Submit'))
			),
			$T.div(
				$T.button({onclick: this._setState.bind(this, Control.STATE_ENABLED)},
					'Enable'
				),
				$T.button({onclick: this._setState.bind(this, Control.STATE_DISABLED)},
					'Disable'
				),
				$T.button({onclick: this._setState.bind(this, Control.STATE_READ_ONLY)},
					'Read-Only'
				),
				$T.button({onclick: this._validate.bind(this)},
					'Validate'
				),
				$T.button({onclick: this._submit.bind(this)},
					'Submit'
				)
			)
		);
	},
	
	_setState : function(iState) {
		this.NODE.select('.control').each(
			function(oElement) {
				oElement.oReflexComponent.set('iControlState', iState);
			}
		);
	},
	
	_text : function(sName) {
		return $T.tr(
			$T.td(
				this._oFirstText = new Control_Text({
					sName 		: sName,
					sLabel		: 'Text',
					mMandatory	: true,
					fnValidate	: function(oControl) {
						if (oControl.getValue() != 'test') {
							throw "Not equal to 'test'";
						}
						return true;
					},
					mValue : 'Not test'
				})
			)
		);
	},
	
	_select : function(sName) {
		return $T.tr(
			$T.td(
				new Control_Select({
					sName 		: sName,
					sLabel		: 'Select',
					mMandatory	: true,
					fnValidate	: function(oControl) {
						if (oControl.getValue() != 1) {
							throw "Not equal to '1'";
						}
						return true;
					},
					fnPopulate	: this._getSelectOptions.bind(this),
					mValue 		: 1
				})
			)
		);
	},
	
	_checkbox : function(sName) {
		return $T.tr(
			$T.td(
				new Control_Checkbox({
					sName 		: sName,
					sLabel		: 'Checkbox',
					mMandatory	: false,
					onchange	: this._checkboxChange.bind(this, 1),
					mValue 		: 1
				})
			)
		);
	},
	
	_radio : function(sName) {
		return $T.tr(
			$T.td(
				new Control_Radio({
					sName 		: sName,
					sLabel		: 'Radio',
					mMandatory	: false,
					onchange	: this._radioChange.bind(this, 1),
					mValue 		: 1
				})
			),
			$T.td(
				new Control_Radio({
					sName 			: sName,
					sLabel			: 'Radio',
					mMandatory		: false,
					onchange		: this._radioChange.bind(this, 2),
					mValue 			: 2,
					bChecked		: true
				})
			)
		);
	},
	
	_datetime : function(sName) {
		return $T.tr(
			$T.td(
				this._oFirstDatetime = new Control_Datetime({
					sName 		: sName,
					sLabel		: 'Datetime',
					mMandatory	: true,
					fnValidate	: this._validateFutureDate.bind(this),
					bTimePicker	: true,
					onchange	: function() {
						var mValue = this._oFirstDatetime.getValue();
						alert('Data value: ' + mValue);
					}.bind(this),
					mValue : new Date().getTime() / 1000
				})
			)
		);
	},
	
	_number : function(sName) {
		return $T.tr(
			$T.td(
				this._oFirstNumber = new Control_Number({
					sName 		: sName,
					sLabel		: 'Number',
					mMandatory	: true,
					mValue		: 1,
					onchange	: function() {
						var mValue = this._oFirstNumber.getValue();
					}.bind(this),
					iDecimalPlaces	: 2,
					fMaximumValue	: 10,
					fMinimumValue	: -5
				})
			)
		);
	},

	_password : function(sName) {
		return $T.tr(
			$T.td(
				this._oFirstPwd = new Control_Password({
					sName 		: sName,
					sLabel		: 'Password',
					mMandatory	: true,
					mValue		: 'test',
					fnValidate	: function(oControl) {
						if (oControl.getValue().length < 4) {
							throw "Must be atleast 4 characters in length.";
						}
						return true;
					},
					onchange : function() {
						document.title = 'Password: ' + this._oFirstPwd.getValue();
					}.bind(this)
				})
			)
		);
	},
	
	_textarea : function(sName) {
		return $T.tr(
			$T.td(
				this._oFirstTextarea = new Control_Textarea({
					sName 		: sName,
					sLabel		: 'Textarea',
					mMandatory	: true,
					mValue		: '<test>',
					fnValidate	: function(oControl) {
						if (oControl.getValue().length < 4) {
							throw "Must be atleast 4 characters in length.";
						}
						return true;
					},
					onchange : function() {
						document.title = 'Password: ' + this._oFirstPwd.getValue();
					}.bind(this),
					iRows 				: 10,
					iCols				: 10,
					bAllowTabbedContent	: true
				})
			)
		);
	},
	
	_hidden : function(sName) {
		return $T.tr(
			$T.td(
				this._Hidden = new Control_Hidden({
					sName 		: sName,
					sLabel		: 'Hidden',
					mMandatory	: true,
					mValue		: 'value'
				})
			)
		);
	},
	
	_textajax : function(sName) {
		var oTextAjax = new Control_Text_AJAX({
			sName			: sName,
			sLabel			: 'Text Ajax',
			mMandatory		: true,
			oDatasetAjax	: new Dataset_Ajax(
				Dataset_Ajax.CACHE_MODE_NO_CACHING,
				new Reflex_AJAX_Request('Correspondence_Template', 'removeMe')
			),
			sDisplayValueProperty	: 'name',
			iResultLimit			: 10,
			oColumnProperties 		: {
				name		: {},
				description	: {}
			}
		});
		oTextAjax.getNode().select('input').last().style.width = '40em';
		return $T.tr(
			$T.td(oTextAjax)
		);
	},
	
	_checkboxChange : function(iPosition) {
		var oCheckbox = this.NODE.select('tbody > tr:nth-child(3) > td:nth-child(' + iPosition + ') > .control').first().oReflexComponent;
		alert((oCheckbox.get('bChecked') ? 'Checked ' : 'Unchecked ') + oCheckbox.getValue());
	},
	
	_radioChange : function(iPosition) {
		var oCheckbox = this.NODE.select('tbody > tr:nth-child(4) > td:nth-child(' + iPosition + ') > .control').first().oReflexComponent;
		alert((oCheckbox.get('bChecked') ? 'Checked ' : 'Unchecked ') + oCheckbox.getValue());
	},
	
	_validateFutureDate : function(oControl) {
		if (oControl.getAsDate().getTime() <= new Date().getTime()) {
			throw "Must be in the future.";
		}
		return true;
	},
	
	_getSelectOptions : function(fnCallback) {
		var aOptions = [$T.option({value: 1},
			'One'
		), $T.option({value: 2},
			'Two'
		), $T.option({value: 3},
			'Three'
		)];
		fnCallback(aOptions);
	},
	
	_validate : function() {
		this._oForm.validate();
	},
	
	_submit : function() {
		this._oForm.submit();
	}
});

Object.extend(Developer_Control, {
	createAsPopup : function() {
		var	oComponent	= Developer_Control.constructApply($A(arguments)),
		oPopup			= new Reflex_Popup(60);
		oPopup.setTitle('New Control Fields');
		oPopup.addCloseButton();
		oPopup.setContent(oComponent.getNode());
		return oPopup;
	}
});