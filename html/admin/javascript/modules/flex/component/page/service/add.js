"use strict";

var H = require('fw/dom/factory'), // HTML
	Class = require('fw/class'),
	Component = require('fw/component'),
	Window = require('fw/component/window'),
	Alert = require('fw/component/popup/alert'),
	xhr = require('xhr'),
	jhr = require('xhr/json-handler'),
	jsonForm = require('json-form'),
	promise = require('promise')
;

var Entry = require('flex/component/page/service/add/entry');

var self = new Class({
	extends: Component,

	construct: function () {
		this.CONFIG = Object.extend({
			accountId: {
				fnSetter: (function (accountId) {
					if (this.get('accountId')) {
						throw new Error('Can\'t redefine the Account to: ' + accountId + ' (already: ' + this.get('accountId') + ')');
					}
					return accountId;
				}).bind(this)
			}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-service-add');
	},

	_buildUI: function () {
		this.NODE = H.section(
			H.h1(
				'Add Services – ',
				this._accountLabel = H.span({class: 'flex-page-service-add-account'})
			),

			this._accountStatusMessage = H.p({class: 'flex-page-service-add-accountstatusmessage'}),

			this._form = H.form({action: 'reflex_json.php/Service/save', enctype: 'application/x-www-form-urlencoded', onsubmit: this._submit.bind(this)},
				this._accountInput = H.input({type: 'hidden', name: 'account_id'}),

				this._serviceList = H.div({class: 'flex-page-service-add-servicelist'}),

				H.fieldset({class: 'flex-page-service-add-buttons'},
					H.button({type: 'button', name: 'add', onclick: this._addEntry.bind(this)}, 'Add Service'),
					H.button({type: 'submit', name: 'save'}, 'Save')
				)
			)
		);
	},

	_addEntry: function () {
		return this._fetchAccount().then(function (account) {
			var entry = new Entry({
				id: this._getNextEntryId(),
				account: account
			});
			this._serviceList.appendChild(entry.getNode());
		}.bind(this));
	},

	_getNextEntryId: function () {
		var entries = [].slice.call(this._serviceList.querySelectorAll('.flex-page-service-add-servicelist-service'), 0);

		if (!entries.length) {
			return 0;
		}

		return 1 + parseInt(entries.sort(function (elementA, elementB) {
			return parseInt(elementB.getAttribute('data-id'), 10) - parseInt(elementA.getAttribute('data-id'), 10);
		})[0].getAttribute('data-id'), 10);
	},

	_syncUI: function () {
		if (!this._bInitialised || !this._bReady) {
			this._fetchAccountPromise = null;

			this._accountInput.value = this.get('accountId');
			promise.all([
				this._syncAccountName(),
				this._syncAccountStatusMessage()
			]).then(
				function () {
					this._addEntry().then(function () {
						this._onReady();
					}.bind(this));
				}.bind(this),
				function () {
					debugger;
					// new Alert({sExtraClass: 'flex-page-service-add-error', sOKLabel: 'Retry', onclose: this._syncUI.bind(this)},
					// 	'There was a problem while trying to load some additional details. If this issue doesn\'t resolve itself after retrying, you may need to contact support.'
					// );
				}.bind(this)
			);
		}
	},

	_fetchAccount: function () {
		if (!this._fetchAccountPromise) {
			var _promise = this._fetchAccountPromise = promise();
			jhr('Account', 'getForId', {arguments: [this.get('accountId')]}).then((function (request) {
				_promise.fulfill(request.parseJSONResponse().oAccount);
			}).bind(this), _promise.reject);
		}
		return this._fetchAccountPromise;
	},

	_syncAccountName: function () {
		return this._fetchAccount().then(function (account) {
			this._accountLabel.textContent = account.BusinessName;
		}.bind(this));
	},

	_syncAccountStatusMessage: function () {
		return this._fetchAccount().then(function (account) {
			if (account.account_status.const_name === 'ACCOUNT_STATUS_PENDING_ACTIVATION') {
				this._accountStatusMessage.innerText = self.ACCOUNT_STATUS_MESSAGE;
			} else {
				this._accountStatusMessage.innerText = '';
			}
		}.bind(this));
	},

	_submit: function (event) {
		event.preventDefault();

		debugger;
		var data = jsonForm(this._form);
		console.log(data);
		// new Alert(JSON.stringify(data));
		if (data.service == null || Object.keys(data.service).length < 1) {
			throw new Error('You must add at least one Service to save your changes');
		}

		jhr('Service', 'save', {
			arguments: [data.account_id, data.service]
		}).then(function (result) {
			debugger;
			console.log(result);
		}, function (reason) {
			debugger;
			console.log(reason);
		});

		return false;
	},

	_fetchCostCentres: function (forceRefresh) {
		var _promise = promise();
		if (this._fetchCostCentresPromise && forceRefresh) {
			try {
				this._fetchCostCentresPromise.fulfill(_promise);
			} catch (error) {
				// no op
			}
		}
		if (!this._fetchCostCentresPromise) {
			var _promise = this._fetchCostCentresPromise = promise();
			jhr('Account', 'getCostCentres', {arguments: [this.get('accountId')]}).then(function (request) {
				_promise.fulfill(request.parseJSONResponse().aCostCentres);
			}, _promise.reject);
		}
		return this._fetchCostCentresPromise;
	},

	syncCostCentres: function (forceRefresh) {
		this._fetchCostCentres(forceRefresh).then(function () {
			Array.prototype.forEach.call(this._serviceList.querySelectorAll('.flex-page-service-add-servicelist-service'), function (serviceEntry) {
				serviceEntry.oFWComponent.set('costCentres');
			});
		}.bind(this));
	},

	statics : {
		createAsPopup : function () {
			var instance;
			self.apply(instance = Object.create(self.prototype), arguments);
			return new Window({
				sExtraClass: 'flex-page-service-add-debugwindow'
			}, instance);
		},

		ACCOUNT_STATUS_MESSAGE: 'This Account is pending activation. New Services cannot be activated until the Account is activated.'
	}
});

return self;