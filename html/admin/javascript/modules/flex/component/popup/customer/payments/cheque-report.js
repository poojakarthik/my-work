"use strict";

var H = require('fw/dom/factory'), // HTML
	Class = require('fw/class'),
	Component = require('fw/component'),
	Window = require('fw/component/window'),
	Alert = require('fw/component/popup/alert'),
	Control = require('fw/component/control'),
	Form = require('fw/component/form'),
	Text = require('fw/component/control/text'),
	Hidden = require('fw/component/control/hidden'),
	xhr = require('xhr'),
	DatePickerPopup = require('fw/component/popup/datepicker'),
	jsonForm = require('json-form')
;

function _isInputDateSupported() {
	var input = document.createElement('input');
	input.type = 'date';
	return input.type === 'date';
}

function _createDatePickerButton(input) {
	var button = H.button({class: 'flex-popup-customer-payments-chequereport-button-datepicker', type: 'button', title: 'Show Date Picker', onclick: function () {
		var picker = new DatePickerPopup({
			bTimePicker: false,
			oDate: Date.$parseDate(input.value, 'd/m/Y'),
			iYearStart: Date.$parseDate(input.min, 'Y-m-d').$format('Y'),
			iYearEnd: Date.$parseDate(input.max, 'Y-m-d').$format('Y')
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

function _getDateValue(input) {
	var value;
	if (typeof input === 'string') {
		value = input;
	} else {
		if (input.valueAsDate) {
			return input.valueAsDate;
		}
		value = input.value;
	}

	var date;
	if (date = Date.$parseDate(value, 'Y-m-d')) {
		return date;
	}
	if (date = Date.$parseDate(value, 'd/m/Y')) {
		return date;
	}
	return null;
}

var self = new Class({
	extends: Window,

	construct: function () {
		this.CONFIG = Object.extend({
			paymentIds: {
				fnSetter: function (value) {
					if (this._bInitialised) {
						throw new Error('Payment Ids can only be supplied at initialisation');
					}
					return value;
				}
			}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);

		this.set('sTitle', 'Cheque Report');
		this.set('bCloseButton', true);
		this.set('bModal', true);

		this.NODE.addClassName('flex-popup-customer-payments-chequereport');
	},

	_buildUI: function () {
		this._super();

		var earliestDate = (new Date()).shift(-1, 'years');
		earliestDate.setDate(1);
		earliestDate.setMonth(0);
		var latestDate = (new Date());
		this.appendChild(
			this.form = H.form({method: 'POST', action: 'reflex_json.php/Customer_ChequeEntry/generateReportForDateRange', onsubmit: this._submit.bind(this)},
				H.fieldset({class: 'flex-popup-customer-payments-chequereport-constraints'},
					H.label({class: 'flex-popup-customer-payments-chequereport-constraints-datefrom'},
						H.span({class: 'flex-popup-customer-payments-chequereport-constraints-datefrom-label'}, 'Date from'),
						this.dateFromInput = H.input({type: 'date', name: 'date.from', min: earliestDate.$format('Y-m-d'), max: latestDate.$format('Y-m-d'), required: true, value: (new Date()).$format('Y-m-d'), onchange: this._validateDateRangeInput.bind(this)})
					),
					H.label({class: 'flex-popup-customer-payments-chequereport-constraints-dateto'},
						H.span({class: 'flex-popup-customer-payments-chequereport-constraints-datefrom-label'}, 'Date to'),
						this.dateToInput = H.input({type: 'date', name: 'date.to', min: earliestDate.$format('Y-m-d'), max: latestDate.$format('Y-m-d'), required: true, value: (new Date()).$format('Y-m-d'), onchange: this._validateDateRangeInput.bind(this)})
					),
					this.customerGroupFieldset = H.div({class: 'flex-popup-customer-payments-chequereport-constraints-customergroup'},
						H.span({class: 'flex-popup-customer-payments-chequereport-constraints-customergroup-label'}, 'Customer Group'),
						H.div({class: 'flex-popup-customer-payments-chequereport-constraints-customergroup-controls'},
							H.label({class: 'flex-popup-customer-payments-chequereport-constraints-customergroup-enable'},
								H.input({type: 'checkbox', name: 'customergroup.enabled', onchange: (function (event) { this._setCustomerGroupConstraintDisabled(!event.target.checked); }).bind(this)}),
								H.span('Limit to a Customer Group')
							),
							this.customerGroupSelect = H.select({name: 'customergroup.id', title: 'Customer Group', required: true})
						)
					)
				),
				// H.fieldset({class: 'flex-popup-customer-payments-chequereport-payments'},
				// 	this.paymentIdCountOutput = H.output(0)
				// ),
				H.fieldset({class: 'flex-popup-customer-payments-chequereport-buttons'},
					H.button({class: 'flex-popup-customer-payments-chequereport-buttons-generate', type: 'submit'}, 'Generate Report'),
					H.button({class: 'flex-popup-customer-payments-chequereport-buttons-close', type: 'button', onclick: this.hide.bind(this)}, 'Close')
				)
			)
		);

		// Add datepickers for legacy browsers
		if (!_isInputDateSupported()) {
			this.dateFromInput.parentNode.appendChild(_createDatePickerButton(this.dateFromInput));
			this.dateToInput.parentNode.appendChild(_createDatePickerButton(this.dateToInput));

			this.dateFromInput.value = Date.$parseDate(this.dateFromInput.value, 'Y-m-d').$format('d/m/Y');
			this.dateToInput.value = Date.$parseDate(this.dateToInput.value, 'Y-m-d').$format('d/m/Y');

			this.dateFromInput.placeholder = 'dd/mm/yyyy';
			this.dateToInput.placeholder = 'dd/mm/yyyy';

			this.dateFromInput.pattern = '^([0-3]?\\d)/([0-1]?\\d)/(\\d{4})$';
			this.dateToInput.pattern = '^([0-3]?\\d)/([0-1]?\\d)/(\\d{4})$';
		}
	},

	_syncUI: function () {
		this._super();
		if (!this._bInitialised) {
			// TODO: Payment Id "mode"

			// Fetch Customer Groups
			this._setCustomerGroupConstraintDisabled(true);
			if (this.customerGroupSelect.options.length === 0) {
				var customerGroupSelect = this.customerGroupSelect;
				xhr.post(
					'reflex_json.php/Customer_Group/getAll', {
						body: 'json=' + encodeURIComponent(JSON.stringify([]))
					}, function (error, request) {
						if (error) {
							debugger;
							new Alert('Flex encountered an error while listing Customer Groups. Please refresh and try again.');
							return;
						}

						var response = JSON.parse(request.responseText);

						Object.keys(response.aResults).forEach(function (customerGroupId) {
							customerGroupSelect.add(H.option({value: customerGroupId},
								response.aResults[customerGroupId].internal_name
							));
						});
					}
				);
			}
		}
		this._onReady();
	},

	_setCustomerGroupConstraintDisabled: function (disabled) {
		this.customerGroupSelect.disabled = !!disabled;
		if (disabled) {
			this.customerGroupFieldset.dataset.disabled = '';
		} else {
			delete this.customerGroupFieldset.dataset.disabled;
		}
	},

	_validateDateRangeInput: function (event) {
		var targetDate = _getDateValue(event.target);
		if (targetDate.$format('Y-m-d') < event.target.getAttribute('min')) {
			event.target.setCustomValidity('Must be no earlier than ' + Date.$parseDate(event.target.getAttribute('min'), 'Y-m-d').$format('d/m/Y'));
			return;
		}
		if (targetDate.$format('Y-m-d') > event.target.getAttribute('max')) {
			event.target.setCustomValidity('Must be no later than ' + Date.$parseDate(event.target.getAttribute('max'), 'Y-m-d').$format('d/m/Y'));
		}

		var fromDate = _getDateValue(this.dateFromInput);
		var toDate = _getDateValue(this.dateToInput);

		this.dateFromInput.setCustomValidity('');
		this.dateToInput.setCustomValidity('');
		if (fromDate && toDate) {
			if (fromDate > toDate) {
				this.dateToInput.setCustomValidity('Date To must be later than or equal to Date From');
			}
		}
	},

	_submit: function (event) {
		event.preventDefault();

		var data = jsonForm(this.form);
		xhr.post(
			this.form.action,
			{
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: 'json=' + encodeURIComponent(JSON.stringify([
					_getDateValue(data.date.from).$format('Y-m-d'),
					_getDateValue(data.date.to).$format('Y-m-d'),
					data.customergroup.enabled ? Number(data.customergroup.id) : null
				]))
			},
			(function (error, request) {
				if (error) {
					console.log(error);
					debugger;
					new Alert('Flex encountered an error while reporting on your cheques. Please refresh and try again.');
					return;
				}

				var response = JSON.parse(request.responseText);
				if (!response.bSuccess) {
					if (response.validationErrors && Object.keys(response.validationErrors).length) {
						(new Alert({sTitle: 'Validation Errors Encountered', bModal: true},
							H.p('We found some issues with the data you\'ve provided:'),
							H.ol.apply(H,
								response.validationErrors.map(function (validationError) {
									return H.li(validationError);
								})
							)
						)).display();
						return;
					}

					debugger;
					new Alert('Flex encountered an error while reporting on your cheques. Please refresh and try again.');
					return;
				}

				if (response.cheques === 0) {
					// There are no cheques: prompt for download?
					// TODO?
				}

				console.log('Download: ', response.csvData);
				// Download
				var download = H.a({
					href: 'data:text/csv;charset=utf8,' + encodeURIComponent(response.csvData),
					download: (function () {
						var filename = 'cheques-';
						if (data.customergroup.enabled) {
							filename += this.customerGroupSelect.querySelector('[value="' + Number(data.customergroup.id) + '"]').textContent.toLowerCase().replace(/[^a-z0-9]/, '') + '-';
						}
						filename += _getDateValue(data.date.from).$format('Ymd') + '-' + _getDateValue(data.date.to).$format('Ymd');
						return filename + '.csv';
					}.call(this))
				});
				this.NODE.appendChild(download);
				download.click();
				this.NODE.removeChild(download);
			}).bind(this)
		);
	}
});

return self;