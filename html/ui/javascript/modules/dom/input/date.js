"use strict";

var H = require('fw/dom/factory'), // HTML
	DatePickerPopup = require('fw/component/popup/datepicker');

var MIN_DATE = '0000-01-01';
var MAX_DATE = (new Date()).getFullYear() + 100;

function isNativelySupported() {
	var input = document.createElement('input');
	input.type = 'date';
	return input.type === 'date';
}

function createDatePickerButton(input) {
	var button = H.button({type: 'button', title: 'Show Date Picker', onclick: function () {
		var picker = new DatePickerPopup({
			bTimePicker: false,
			oDate: Date.$parseDate(input.value, 'd/m/Y'),
			iYearStart: Date.$parseDate(input.min || MIN_DATE, 'Y-m-d').$format('Y'),
			iYearEnd: Date.$parseDate(input.max || MAX_DATE, 'Y-m-d').$format('Y')
		});
		picker.observe('change', function () {
			input.value = picker.get('oDate').$format('d/m/Y');

			var inputEvent = document.createEvent('HTMLEvents');
			inputEvent.initEvent('change', false, true);
			input.dispatchEvent(inputEvent);
		});
		picker.show(input);
	}});

	return button;
}

return (module.exports = {
	createDatePickerButton: createDatePickerButton,
	isNativelySupported: isNativelySupported
});