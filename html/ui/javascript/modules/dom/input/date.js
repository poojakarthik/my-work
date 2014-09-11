"use strict";

require('fw/date');
var H = require('fw/dom/factory'), // HTML
	DatePickerPopup = require('fw/component/popup/datepicker');

var MIN_DATE = (new Date()).shift(-100, Date.DATE_INTERVAL_YEAR).$format('Y-m-d');
var MAX_DATE = (new Date()).shift(+100, Date.DATE_INTERVAL_YEAR).$format('Y-m-d');

function isNativelySupported() {
	var input = document.createElement('input');
	input.type = 'date';
	return input.type === 'date';
}

function createDatePickerButton(input, format) {
	format = format || 'Y-m-d';

	var button = H.button({type: 'button', title: 'Show Date Picker', onclick: function () {
		var picker = new DatePickerPopup({
			bTimePicker: false,
			oDate: Date.$parseDate(input.value, format),
			iYearStart: Date.$parseDate(input.min || MIN_DATE, 'Y-m-d').$format('Y'),
			iYearEnd: Date.$parseDate(input.max || MAX_DATE, 'Y-m-d').$format('Y')
		});
		picker.observe('change', function () {
			input.value = picker.get('oDate').$format(format);

			var changeEvent = document.createEvent('HTMLEvents');
			changeEvent.initEvent('change', false, true);
			input.dispatchEvent(changeEvent);

			var inputEvent = document.createEvent('HTMLEvents');
			inputEvent.initEvent('input', false, true);
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