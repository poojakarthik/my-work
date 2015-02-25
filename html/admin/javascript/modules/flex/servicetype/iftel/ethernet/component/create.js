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
		this.NODE.addClassName('flex-servicetype-iftel-ethernet-create');
	},

	_buildUI: function () {
		var latestDate = new Date();
		this.NODE = H.div(
			H.label({class: 'flex-servicetype-iftel-ethernet-create-identifier'},
				H.span({class: 'flex-servicetype-iftel-ethernet-create-identifier-label'}, 'Service Number'),
				H.input({name: 'identifier', type: 'text', pattern: '^\\d+$', required: true, onchange: function (oEvent) {
					if (oEvent.target.validity.valid) {
						this.NODE.setAttribute('data-identifier', oEvent.target.value);
					} else {
						this.NODE.setAttribute('data-identifier', '');
					}
				}.bind(this)})
			)
		);
	},

	_syncUI: function () {
		if (!this._bInitialised || !this._bReady) {
			this._onReady();
		}
	}
});

return self;