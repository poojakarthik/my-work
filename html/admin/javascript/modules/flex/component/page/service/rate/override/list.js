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
	jhr = require('xhr/json-handler'),
	jsonForm = require('json-form'),
	promise = require('promise'),
	mixin = require('mixin')
;
var ServiceRateAdd = require('flex/component/popup/service/rate/override/add');

function objectValues(object) {
	return Object.keys(object).map(function (key) {
		return object[key];
	});
}

function extractRecordTypes(rates) {
	return objectValues(rates.reduce(function (recordTypesDict, rate) {
		if (recordTypesDict[rate.record_type_id] == null) {
			recordTypesDict[rate.record_type_id] = {
				id: rate.record_type_id,
				name: rate.record_type_name,
				description: rate.record_type_description,
				rates: []
			};
		}
		recordTypesDict[rate.record_type_id].rates.push(rate);
		return recordTypesDict;
	}, {}));
}

function replacePromise(old, replacement) {
	old.resolve(replacement);
	return replacement;
}

var self = new Class({
	extends: Component,

	construct: function () {
		this.CONFIG = Object.extend({
			serviceId: {
				fnSetter: function (serviceId) {
					this.CONFIG.serviceRates.mValue = replacePromise(this.CONFIG.serviceRates.mValue, promise()); // Need a new promise for related rates
					return serviceId;
				}.bind(this)
			},

			// serviceRates is internally a promise. Setting fulfills, getting returns a thenable
			serviceRates: {
				fnSetter: function (serviceRates) {
					// Replace existing promise with a new one, filled with the provided rates
					return replacePromise(this.CONFIG.serviceRates.mValue, promise(function (resolve) {
						resolve(serviceRates);
					}));
				}.bind(this),
				fnGetter: function (serviceRatesPromise) {
					return serviceRatesPromise.thenable();
				},
				mValue: promise()
			},

			permissions: {
				mValue: {}
			}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-account-service-plan-overriderates');
	},

	_buildUI: function () {
		this.NODE = H.section(
			H.h2(
				H.span({class: 'flex-page-account-service-plan-overriderates-heading-label'}, 'Rate Overrides'),
				this._addOverrideButton = H.button({type: 'button', class: 'flex-page-account-service-plan-overriderates-add', disabled: '', onclick: this._showAddServiceRatePopup.bind(this)}, 'New Override Rate')
			),

			this._recordTypesElement = H.ol({class: 'flex-page-account-service-plan-overriderates-recordtypes'})
		);
	},

	_buildRecordTypeUI: function (recordType) {
		return H.li({class: 'flex-page-account-service-plan-overriderates-recordtypes-recordtype'},
			H.table({class: 'flex-page-account-service-plan-overriderates-recordtypes-recordtype-rates'},
				H.caption(recordType.description),
				H.thead(
					H.tr(
						H.th('Rate'),
						H.th('Starts'),
						H.th('Ends')
					)
				),
				H.tbody(
					H.$fragment.apply(H, recordType.rates.map(this._buildRateUI.bind(this)))
				)
			)
		);
	},

	_buildRateUI: function (rate) {
		return H.tr({class: 'flex-page-account-service-plan-overriderates-recordtypes-recordtype-rates-rate'},
			H.td(
				H.a({href: "javascript: Vixen.Popup.ShowAjaxPopup('ViewRatePopupId_' + " + rate.rate_id + ", 'medium', 'Rate', 'Rate', 'View', {Rate: {'Id': " + rate.rate_id + "}}, 'nonmodal');"},
					rate.name
				)
			),
			// H.td(Date.$parseDate(rate.start_datetime, 'Y-m-d H:i:s').$format('M j, Y')),
			H.td(rate.start_datetime === '0000-00-00 00:00:00' ? 'Indefinite' : Date.$parseDate(rate.start_datetime, 'Y-m-d H:i:s').$format('M j, Y')),
			H.td(rate.end_datetime === '9999-12-31 23:59:59' ? 'Indefinite' : Date.$parseDate(rate.end_datetime, 'Y-m-d H:i:s').$format('M j, Y'))
		);
	},

	_syncUI: function () {
		var serviceId = this.get('serviceId');
		var serviceRatesPromise = this.get('serviceRates');

		// NOTE: These are re-checked on the server at time of submission
		var permissions = this.get('permissions');
		if (permissions && permissions.newOverrideRate) {
			this._addOverrideButton.disabled = false;
		} else {
			this._addOverrideButton.disabled = true;
		}

		// Dynamically pull serviceRates based on serviceId (if they weren't provided up front)
		this._syncServiceRates().then(this._onReady.bind(this));
	},

	_fetchServiceRates: function (force) {
		return jhr('Service_Rate', 'getActiveOrUpcoming', {arguments: [this.get('serviceId')], parseJSONResponse: true}).then(
			function success(response) {
				this.CONFIG.serviceRates.mValue.resolve(response.serviceRates);
			}.bind(this),
			function failure(reason) {
				var failureWindow = new Alert(reason.message);
			}
		);
	},

	_syncServiceRates: function (force) {
		if (force) {
			this.CONFIG.serviceRates.mValue = replacePromise(this.CONFIG.serviceRates.mValue, this._fetchServiceRates());
		}

		return this.CONFIG.serviceRates.mValue.then(function (serviceRates) {
			this._recordTypesElement.innerHTML = '';
			this._recordTypesElement.appendChild(
				H.$fragment.apply(H,
					extractRecordTypes(serviceRates).map(this._buildRecordTypeUI.bind(this))
				)
			);
		}.bind(this));
	},

	_showAddServiceRatePopup: function () {
		ServiceRateAdd.createAsPopup({
			serviceId: this.get('serviceId'),
			onsave: this._syncServiceRates.bind(this, true)
		});
	}
});

return self;