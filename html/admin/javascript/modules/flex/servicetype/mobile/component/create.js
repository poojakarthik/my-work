"use strict";

var H = require('fw/dom/factory'), // HTML
	Class = require('fw/class'),
	Component = require('fw/component'),
	Alert = require('fw/component/popup/alert'),
	promise = require('promise'),
	jhr = require('xhr/json-handler'),
	arrayify = require('arrayify'),
	delegate = require('dom/event/delegate'),
	inputDate = require('dom/input/date');

var self = new Class({
	extends: Component,

	construct: function () {
		this.CONFIG = Object.extend({
			serviceId: {
				fnSetter: (function (serviceId) {
					if (this.get('serviceId')) {
						throw new Error('Can\'t redefine the serviceId to: ' + serviceId + ' (already: ' + this.get('serviceId') + ')');
					}
					return serviceId;
				}).bind(this)
			}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-servicetype-mobile-create');
	},

	_buildUI: function () {
		var latestDate = new Date();
		this.NODE = H.div(
			H.label({class: 'flex-servicetype-mobile-create-msn'},
				H.abbr({class: 'flex-servicetype-mobile-create-msn-label', title: 'Mobile Service Number'}, 'MSN'),
				H.input({name: 'msn', maxlength: 10, pattern: '^04\\d{8}$'})
			),

			H.label({class: 'flex-servicetype-mobile-create-puk'},
				H.abbr({class: 'flex-servicetype-mobile-create-puk-label', title: 'Personal Unblocking Key'}, 'PUK'),
				H.input({name: 'puk', maxlength: 50, pattern: '^\\d+$'})
			),

			H.label({class: 'flex-servicetype-mobile-create-esn'},
				H.abbr({class: 'flex-servicetype-mobile-create-esn-label', title: 'Electronic Serial Number'}, 'ESN'),
				H.input({name: 'esn', maxlength: 15, pattern: '^\\d+$'})
			),

			H.label({class: 'flex-servicetype-mobile-create-state'},
				H.span({class: 'flex-servicetype-mobile-create-state-label'}, 'State'),
				this._stateSelect = H.select({name: 'state'})
			),

			H.label({class: 'flex-servicetype-mobile-create-dateofbirth'},
				H.span({class: 'flex-servicetype-mobile-create-dateofbirth-label'}, 'Date of Birth'),
				this._dateOfBirthInput = H.input({name: 'date_of_birth', type: 'date', max: latestDate.$format('Y-m-d')})
			),

			H.label({class: 'flex-servicetype-mobile-create-comments'},
				H.span({class: 'flex-servicetype-mobile-create-comments-label'},'Comments'),
				H.textarea({name: 'comments', maxlength: 4294967296})
			)
		);

		// Add datepickers for legacy browsers
		if (!inputDate.isNativelySupported()) {
			var dateOfBirthButton = inputDate.createDatePickerButton(this._dateOfBirthInput);
			this._dateOfBirthInput.parentNode.appendChild(dateOfBirthButton);
			this._dateOfBirthInput.value = Date.$parseDate(this._dateOfBirthInput.value, 'Y-m-d').$format('d/m/Y');
			this._dateOfBirthInput.placeholder = 'dd/mm/yyyy';
			this._dateOfBirthInput.pattern = '^([0-3]?\\d)/([0-1]?\\d)/(\\d{4})$';
		}
	},

	_syncUI: function () {
		if (!this._bInitialised || !this._bReady) {
			promise.all(
				this._populateState()
			).then(
				function fulfilled() {
					this._onReady();
				}.bind(this),
				function rejected() {
					debugger;
					// new Alert({sOKLabel: 'Retry', onclose: this._syncUI.bind(this)},
					// 	'There was a problem while trying to load some additional details. If this issue doesn\'t resolve itself after retrying, you may need to contact support.'
					// );
				}.bind(this)
			);
		}
	},

	_fetchStates: function () {
		if (!this._fetchStatesPromise) {
			this._fetchStatesPromise = jhr('ServiceType_Mobile', 'getStates').then(function (request) {
				return request.parseJSONResponse().states;
			});
		}
		return this._fetchStatesPromise;
	},

	_populateState: function () {
		return this._fetchStates().then(function (states) {
			var fragment = H.$fragment();
			fragment.appendChild(H.option({value: '', selected: true}, '∅ Not Supplied'));
			Object.keys(states).forEach(function (state) {
				fragment.appendChild(H.option({value: state}, state));
			});
			this._stateSelect.appendChild(fragment.cloneNode(true));
		}.bind(this));
	}
});

return self;