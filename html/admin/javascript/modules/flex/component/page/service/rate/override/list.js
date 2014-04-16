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
	jsonForm = require('json-form'),
	promise = require('promise')
;

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

var self = new Class({
	extends: Component,

	construct: function () {
		this.CONFIG = Object.extend({
			serviceId: {
				fnSetter: function (serviceId) {
					this.CONFIG.serviceRates.mValue = promise(); // Need a new promise for related rates
					return serviceId;
				}.bind(this)
			},

			// serviceRates is internally a promise. Setting fulfills, getting returns a thenable
			serviceRates: {
				fnSetter: function (serviceRates) {
					this.CONFIG.serviceRates.mValue.fulfill(serviceRates);
					return this.CONFIG.serviceRates.mValue;
				}.bind(this),
				fnGetter: function (serviceRatesPromise) {
					return serviceRatesPromise.thenable();
				},
				mValue: promise()
			}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-account-service-plan-overriderates');

		this._syncUIPromise = promise();
		this._configCache = {};
	},

	_buildUI: function () {
		this.NODE = H.section(
			H.h2('Rate Overrides'),

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
			H.td(rate.name),
			// H.td(Date.$parseDate(rate.start_datetime, 'Y-m-d H:i:s').$format('M j, Y')),
			H.td(rate.start_datetime === '0000-00-00 00:00:00' ? 'Indefinite' : Date.$parseDate(rate.start_datetime, 'Y-m-d H:i:s').$format('M j, Y')),
			H.td(rate.end_datetime === '9999-12-31 23:59:59' ? 'Indefinite' : Date.$parseDate(rate.end_datetime, 'Y-m-d H:i:s').$format('M j, Y'))
		);
	},

	_syncUI: function () {
		var serviceId = this.get('serviceId');
		var serviceRatesPromise = this.get('serviceRates');

		serviceRatesPromise.then(function (serviceRates) {
			this._recordTypesElement.appendChild(
				H.$fragment.apply(H,
					extractRecordTypes(serviceRates).map(this._buildRecordTypeUI.bind(this))
				)
			);
			this._onReady();
		}.bind(this));

		// TODO: Dynamically pull serviceRates based on serviceId
		// if (serviceId !== this._configCache.serviceId) {
		// 	// New Service
		// 	// TODO

		// 	if (serviceRatesPromise !== this._configCache.serviceRatesPromise) {
		// 		// New Service Rates
		// 		this._recordTypesElement.innerHTML = '';
		// 		serviceRatesPromise.then(function (serviceRates) {
		// 			this._recordTypesElement.appendChild(
		// 				H.$fragment.apply(H,
		// 					extractRecordTypes(serviceRates).map(this._buildRecordTypeUI.bind(this))
		// 				)
		// 			);
		// 		}.bind(this));
		// 	}
		// }
	}
});

return self;