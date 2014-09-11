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
	jsonForm = require('json-form')
;

var ChequeReport = require('flex/component/popup/customer/payments/cheque-report');

var self = new Class({
	extends: Component,

	construct: function () {
		this.CONFIG = Object.extend({}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-customer-payments-chequeentry');
	},

	_buildUI: function () {
		this.NODE = H.section(
			H.header({class: 'flex-page-customer-payments-chequeentry-heading'},
				H.h2('Cheque Entry'),
				H.nav(
					H.button({class: 'flex-page-customer-payments-chequeentry-heading-generatereport', type: 'button', onclick: function () { (new ChequeReport()).display(); }},
						'Generate Historic Report'
					)
				)
			),

			this.form = H.form({method: 'POST', action: 'reflex_json.php/Customer_ChequeEntry/process', onsubmit: this._submit.bind(this)},
				H.table({class: 'flex-page-customer-payments-chequeentry-data'},
					H.thead(
						H.tr(
							H.th({colspan: 2}, 'Flex Account'),
							H.th('Cheque Number'),
							H.th('Cheque BSB'),
							H.th('Cheque Account'),
							H.th('Amount'),
							H.th()
						)
					),

					this.tbody = H.tbody({
						onchange: [
							this._ensureEmptyEntryExists.bind(this),
							this._validateFlexAccount.bind(this),
							this._updateTotalAmount.bind(this),
							this._updateTotalCheques.bind(this),
							this._toggleRequired.bind(this)
						],
						onclick: [
							this._removeEntry.bind(this)
						]
					}),

					H.tfoot(
						H.tr(
							H.th({class: 'flex-page-customer-payments-chequeentry-data-addentry'},
								H.button({class: 'flex-page-customer-payments-chequeentry-data-addentry-button', type: 'button', onclick: this._addEntry.bind(this)}, 'Add Entry')
							),
							H.th({colspan: 4}, 'Total Value'),
							H.td(
								this.totalValueElement = H.output({class: 'flex-page-customer-payments-chequeentry-data-totalvalue'}, '0.00')
							),
							H.th()
						)
					)
				),

				H.fieldset({class: 'flex-customer-payments-chequeentry-buttons'},
					H.button({class: 'flex-customer-payments-chequeentry-buttons-submit', type: 'submit'}, 'Process Cheques')
				)
			)
		);
		this.tbody.appendChild(this._buildEntry());

		//this.NODE.querySelector('[name="cheque[0].account_id"]').setAttribute('autofocus');
	},

	_buildEntry: function () {
		var id = this._getNextEntryId();
		return H.tr({'data-id': id},
			H.td({class: 'flex-page-customer-payments-chequeentry-data-entry-account'},
				H.input({name: 'cheque[' + id + '].account_id', type: 'text', required: true, title: 'Flex Account Number'})
			),
			H.td({class: 'flex-page-customer-payments-chequeentry-data-entry-accountdetails'},
				H.span({class: 'flex-page-customer-payments-chequeentry-data-entry-account-accountname'}),
				H.span({class: 'flex-page-customer-payments-chequeentry-data-entry-account-tradingname'}),
				H.span({class: 'flex-page-customer-payments-chequeentry-data-entry-account-customergroup'})
			),
			H.td({class: 'flex-page-customer-payments-chequeentry-data-entry-chequenumber'},
				H.input({name: 'cheque[' + id + '].cheque_number', type: 'text', required: true, pattern: '^(\\s*\\d\\s*)+$'})
			),
			H.td({class: 'flex-page-customer-payments-chequeentry-data-entry-chequebsb'},
				H.input({name: 'cheque[' + id + '].cheque_bsb', type: 'text', size: 7, maxlength: 7, required: true, pattern: '^\\s*(\\d{3})\\s*-?\\s*(\\d{3})\\s*$', title: 'NNN-NNN (e.g. 484-799)'})
			),
			H.td({class: 'flex-page-customer-payments-chequeentry-data-entry-chequeaccount'},
				H.input({name: 'cheque[' + id + '].cheque_account', type: 'text', required: true, pattern: '^(\\s*\\d\\s*)+$'})
			),
			H.td({class: 'flex-page-customer-payments-chequeentry-data-entry-chequeamount'},
				H.span('$'),
				H.input({name: 'cheque[' + id + '].amount', type: 'number', required: true, min: 0.01, step: 0.01})
			),
			H.td({class: 'flex-page-customer-payments-chequeentry-data-entry-actions'},
				H.button({class: 'flex-page-customer-payments-chequeentry-data-entry-actions-remove', name: 'cheque[' + id + '].remove', title: 'Remove', type: 'button'})
			)
		);
	},

	_addEntry: function () {
		var entry = this._buildEntry();
		this.tbody.appendChild(entry);
		entry.querySelector('[name$=".account_id"]').focus();
	},

	_ensureEmptyEntryExists: function (event) {
		if (this.tbody.querySelector('tr:last-child').contains(event.target)) {
			// A field in the last row has been changed, add in a new row
			this.tbody.appendChild(this._buildEntry());
		}
	},

	_getNextEntryId: function () {
		var entries = [].slice.call(this.NODE.querySelectorAll('tbody > tr'), 0);

		if (!entries.length) {
			return 0;
		}

		return 1 + parseInt(entries.sort(function (elementA, elementB) {
			return parseInt(elementB.getAttribute('data-id'), 10) - parseInt(elementA.getAttribute('data-id'), 10);
		})[0].getAttribute('data-id'), 10);
	},

	_updateTotalAmount: function (event) {
		if (/^cheque\[\d+\]\.amount$/.test(event.target.name)) {
			this.totalValueElement.value = Array.prototype.reduce.call(this.tbody.querySelectorAll('[name$=".amount"]'), function (sum, element) {
				if (element.checkValidity()) {
					return sum + Number(element.value);
				}
				return sum;
			}, 0).toFixed(2);
		}
	},

	_updateTotalCheques: function (event) {
		// TODO
	},

	_toggleRequired: function (event) {
		// Entries that are not the first one (must have at least one entry)
		if (Array.prototype.slice.call(this.tbody.querySelectorAll('tr:not(:first-child)'), 0).indexOf(event.target) > -1) {
			// If any fields have values, all are required
			var inputs = Array.prototype.slice.call(event.target.querySelectorAll('input'), 0);
			var hasValues = inputs.some(function (input) {
				return input.value.length > 0;
			});
			inputs.forEach(function (input) {
				input.required = hasValues;
			});
		}
	},

	_removeEntry: function (event) {
		if (/^cheque\[\d+\]\.remove$/.test(event.target.name)) {
			this.tbody.removeChild(this.tbody.querySelector('[data-id="' + event.target.name.match(/^cheque\[(\d+)\]\.remove$/)[1] + '"]'));
			if (this.tbody.querySelectorAll('tr').length === 0) {
				this.tbody.appendChild(this._buildEntry());
			}
		}
	},

	_syncUI: function () {
		this._onReady();
	},

	_validateFlexAccount: function (event) {
		var input = event.target;

		if (!/^cheque\[(\d+)\].account_id$/.test(input.name)) {
			return;
		}

		var entryId = input.name.match(/^cheque\[(\d+)\].account_id$/)[1];
		var entry = this.tbody.querySelector('[data-id="' + entryId + '"]');

		var accountNameElement = entry.querySelector('.flex-page-customer-payments-chequeentry-data-entry-account-accountname');
		var tradingNameElement = entry.querySelector('.flex-page-customer-payments-chequeentry-data-entry-account-tradingname');
		var customerGroupElement = entry.querySelector('.flex-page-customer-payments-chequeentry-data-entry-account-customergroup');

		accountNameElement.textContent = '';
		tradingNameElement.textContent = '';
		customerGroupElement.textContent = '';

		xhr.post('reflex_json.php/Customer_ChequeEntry/getAccountDetailsForId',
			{
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: 'json=' + encodeURIComponent(JSON.stringify([input.value]))
			},
			function (error, request) {
				if (error) {
					// TODO: Handle error
					debugger;
					return;
				}

				var response = JSON.parse(request.responseText);
				if (!response.accountDetails) {
					// No match: Invalid
					input.setCustomValidity('No such Account');
					return;
				}
				var accountDetails = response.accountDetails;

				// Found an Account: populate data
				input.setCustomValidity(''); // Identifies as "valid"

				accountNameElement.textContent = accountDetails.account_name;

				tradingNameElement.textContent = accountDetails.trading_name;

				customerGroupElement.textContent = accountDetails.customer_group_internal_name;
				customerGroupElement.style.color = '#' + accountDetails.customer_group_color_primary;
				//customerGroupElement.style.textShadow = '0 0 1px #' + accountDetails.customer_group_color_secondary;

				if (accountDetails.cheque_bsb) {
					entry.querySelector('[name="cheque[' + entryId + '].cheque_bsb"]').value = accountDetails.cheque_bsb;
				}
				if (accountDetails.cheque_account) {
					entry.querySelector('[name="cheque[' + entryId + '].cheque_account"]').value = accountDetails.cheque_account;
				}

				// Amount
				var payableBalance = Math.round(accountDetails.outstanding_balance * 100) / 100;
				if (payableBalance > 0) {
					entry.querySelector('[name="cheque[' + entryId + '].amount"]').value = payableBalance;
				}
			}
		);
	},

	_submit: function(event) {
		event.preventDefault();

		// Build data
		var data = jsonForm(this.form);

		// Perform XHR
		xhr.post(
			this.form.action,
			{
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: 'json=' + encodeURIComponent(JSON.stringify([data.cheque]))
			},
			function (error, request) {
				if (error) {
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
								Object.keys(response.validationErrors).reduce(function (items, chequeId) {
									items.push.apply(items, response.validationErrors[chequeId].map(function (validationError) {
										return H.li(validationError);
									}));
									return items;
								}, [])
							)
						)).display();
						return;
					} else {
						new Alert('Flex encountered an error while reporting on your cheques. Please refresh and try again.');
						return;
					}
				}

				debugger;

				// Prompt to download report for just-submitted dataset
				var dialog = new Alert({sExtraClass: 'flex-page-customer-payments-chequeentry-processeddialog', sTitle : 'Cheques Processed', bModal: true},
					H.p('Successfully processed ' + response.paymentIds.length + ' cheque' + (response.paymentIds.length !== 1 ? 's' : '') + '.')
				);
				dialog.observe('ok', function (event) {
					debugger;
					event.cancel();
					document.location.reload();
				});
				//dialog.display();
			}
		);
	},

	statics : {
		createAsPopup : function () {
			var popup;
			Window.apply(popup = Object.create(Window.prototype), arguments);
			popup.appendChild(new self());
			return popup;
		}
	}
});

return self;