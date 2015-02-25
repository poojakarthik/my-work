"use strict";

var H = require('fw/dom/factory'), // HTML
	Class = require('fw/class'),
	Component = require('fw/component'),
	Alert = require('fw/component/popup/alert'),
	promise = require('promise'),
	jhr = require('xhr/json-handler');

var ServiceType = require('flex/servicetype');

var self = new Class({
	extends: Component,

	construct: function () {
		this.CONFIG = Object.extend({
			id: {
				fnSetter: (function (id) {
					if (this.get('id')) {
						throw new Error('Can\'t redefine the id to: ' + id + ' (already: ' + this.get('id') + ')');
					}
					return id;
				}).bind(this)
			},
			account: {
				fnSetter: (function (account) {
					if (this.get('account')) {
						throw new Error('Can\'t redefine the account to: ' + JSON.stringify(account) + ' (already: ' + JSON.stringify(this.get('account')) + ')');
					}
					return account;
				}).bind(this)
			},
			costCentres: {}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-service-add-servicelist-service');

		if (this.get('id') == null) {
			throw new Error('Can\'t create an Add Services Service entry without supplying the `id` config property');
		}
		if (!this.get('account')) {
			throw new Error('Can\'t create an Add Services Service entry without supplying the `account` config property');
		}

		this._identifierAttributeObserver = new MutationObserver(function (aMutations) {
			aMutations.forEach(function (oMutation) {
				if (oMutation.type === 'attributes' && oMutation.attributeName === 'data-identifier' && oMutation.target === this._propertiesContent) {
					this._syncHeading();
				}
			}.bind(this));
		}.bind(this));
	},

	_buildUI: function () {
		this.NODE = H.div({'data-fieldset': true},
			H.h3({class: 'flex-page-service-add-servicelist-service-heading', title: 'Service'},
				H.button({name: 'collapse', type: 'button', onclick: this._syncVisibility.bind(this)}),
				this.headingServiceType = H.img({class: 'flex-page-service-add-servicelist-service-heading-servicetype'}),
				this.headingIdentifier = H.span({class: 'flex-page-service-add-servicelist-service-heading-serviceidentifier'}),
				this.headingRatePlan = H.span({class: 'flex-page-service-add-servicelist-service-heading-rateplan'}),
				H.button({name: 'remove', type: 'button', onclick: this._remove.bind(this)})
			),

			H.div({class: 'flex-page-service-add-servicelist-service-details'},
				H.label({class: 'flex-page-service-add-servicelist-service-details-servicetype'},
					H.span({class: 'flex-page-service-add-servicelist-service-details-servicetype-label'}, 'Service Type'),
					this._serviceTypeSelect = H.select({name: 'service_type_id', required: true, onchange: [
						this._populateRatePlan.bind(this),
						this._populateServiceProperties.bind(this),
						this._syncHeading.bind(this)
					]})
				),

				H.label({class: 'flex-page-service-add-servicelist-service-details-rateplan'},
					H.span({class: 'flex-page-service-add-servicelist-service-details-rateplan-label'}, 'Rate Plan'),
					this._ratePlanSelect = H.select({name: 'rate_plan_id', onchange: this._syncHeading.bind(this)},
						H.option({value: ''}, '∅ No Rate Plan')
					)
				),

				H.label({class: 'flex-page-service-add-servicelist-service-details-costcentre'},
					H.span({class: 'flex-page-service-add-servicelist-service-details-costcentre-label'}, 'Cost Centre'),
					this._costCentreSelect = H.select({name: 'cost_centre_id'},
						H.option({value: ''}, '∅ No Cost Centre')
					)
				),

				this._activateImmediately = H.label({class: 'flex-page-service-add-servicelist-service-details-activateimmediately'},
					H.span({class: 'flex-page-service-add-servicelist-service-details-activateimmediately-label'}, 'Activation'),
					H.span({class: 'flex-page-service-add-servicelist-service-details-activateimmediately-controls'},
						this._activateImmediatelyControl = H.input({type: 'checkbox', name: 'activate_immediately', value: true}),
						H.span({class: 'flex-page-service-add-servicelist-service-details-activateimmediately-description'}, 'Activate this service immediately')
					)
				)
			),
			this._propertiesContainer = H.div({class: 'flex-page-service-add-servicelist-service-properties', 'data-fieldset': true, 'data-name': 'properties'})
		);
	},

	_syncUI: function () {
		if (!this._bInitialised || !this._bReady) {
			this.NODE.setAttribute('data-id', this.get('id'));
			this.NODE.dataset.name = 'service.' + this.get('id');

			this._fetchServiceTypesPromise = null;
			this._fetchRatePlansPromises = {};

			promise.all([
				// Service Types & Rate Plans
				this._populateServiceType().then(function () {
					return promise.all([
						this._populateRatePlan(),
						this._populateServiceProperties()
					]);
				}.bind(this)),
				this._syncActivateImmediately(),
				this._syncHeading()
			]).then(
				this._onReady.bind(this),
				(function rejected() {
					debugger;
					// new Alert({sOKLabel: 'Retry', onclose: this._syncUI.bind(this)},
					// 	'There was a problem while trying to load some additional details. If this issue doesn\'t resolve itself after retrying, you may need to contact support.'
					// );
				}).bind(this)
			);
		}
	},

	_fetchServiceTypes: function () {
		if (!this._fetchServiceTypesPromise) {
			var _promise = this._fetchServiceTypesPromise = promise();
			jhr('ServiceType', 'getAll', {}).then((function (request) {
				_promise.fulfill(request.parseJSONResponse().serviceTypes);
			}).bind(this), _promise.reject);
		}
		return this._fetchServiceTypesPromise;
	},

	_populateServiceType: function () {
		return this._fetchServiceTypes().then(function (serviceTypes) {
			// Remove Dialup, as it's not supported (unneeded & no definition for FNN)
			Object.keys(serviceTypes).forEach(function (serviceTypeId) {
				if (serviceTypes[serviceTypeId].const_name === 'SERVICE_TYPE_DIALUP') {
					delete serviceTypes[serviceTypeId];
				}
			});

			Object.keys(serviceTypes).map(function (serviceTypeId) {
				return H.option({value: serviceTypeId, 'data-module': serviceTypes[serviceTypeId].module}, serviceTypes[serviceTypeId].name);
			}).forEach(function (option) {
				this._serviceTypeSelect.appendChild(option);
			}.bind(this));
		}.bind(this));
	},

	_syncServiceType: function () {
		this._populateServiceProperties();
	},

	_fetchRatePlans: function () {
		var serviceType = this._serviceTypeSelect.value;
		if (!this._fetchRatePlansPromises[serviceType]) {
			var _promise = this._fetchRatePlansPromises[serviceType] = promise();
			jhr('Rate_Plan', 'getForCustomerGroupAndServiceType', {
				arguments: [this.get('account').CustomerGroup, parseInt(serviceType, 10)]
			}).then((function (request) {
				_promise.fulfill(request.parseJSONResponse().ratePlans);
			}).bind(this), _promise.reject);
		}
		return this._fetchRatePlansPromises[serviceType];
	},

	_populateRatePlan: function () {
		Array.prototype.forEach.call(this._ratePlanSelect.querySelectorAll(':not([value=""])'), function (option) {
			option.parentNode.removeChild(option);
		}.bind(this));

		return this._fetchRatePlans().then(function (ratePlans) {
			// Populate the Select for Rate Plans
			Object.keys(ratePlans).sort(function (a, b) {
				if (ratePlans[a].Name < ratePlans[b].Name) {
					return -1;
				}
				if (ratePlans[a].Name > ratePlans[b].Name) {
					return 1;
				}
				return 0;
			}).map(function (ratePlanId) {
				return H.option({value: ratePlanId}, ratePlans[ratePlanId].Name);
			}).forEach(function (option) {
				this._ratePlanSelect.appendChild(option);
			}.bind(this));
		}.bind(this));
	},

	_syncActivateImmediately: function () {
		if (this.get('account').account_status.const_name !== 'ACCOUNT_STATUS_ACTIVE') {
			this._activateImmediately.setAttribute('data-disabled', '');
			this._activateImmediatelyControl.disabled = true;
		} else {
			this._activateImmediately.removeAttribute('data-disabled');
			this._activateImmediatelyControl.disabled = false;
		}

		// For consistency with other "sync*" methods, "wrap" in a promise (not really, because this is sync)
		return promise().fulfill();
	},

	_populateServiceProperties: function () {
		this._propertiesContainer.innerHTML = '';
		this._propertiesContent = null;
		this._identifierAttributeObserver.disconnect();
		return this._fetchServiceTypes().then(function (serviceTypes) {
			return ServiceType.getModule(serviceTypes[this._serviceTypeSelect.value].module).then(function (serviceTypeModule) {
				this._propertiesContent = serviceTypeModule.getCreateNode();
				this._propertiesContainer.appendChild(this._propertiesContent);
				this._identifierAttributeObserver.observe(this._propertiesContent, {attributes: true, attributeFilter: ['data-identifier']});
			}.bind(this));
		}.bind(this));
	},

	_syncHeading: function () {
		// Service Type Icon
		if (this._serviceTypeSelect.value) {
			var serviceTypeOption = this._serviceTypeSelect.querySelector(':checked');
			this.headingServiceType.src = '/admin/img/template/servicetype/' + serviceTypeOption.dataset.module.toLowerCase() + '.png';
			this.headingServiceType.title = 'Service Type: ' + serviceTypeOption.innerText;
			this.headingServiceType.alt = 'Service Type: ' + serviceTypeOption.innerText;
		} else {
			if (this.headingServiceType.hasAttribute('src')) {
				this.headingServiceType.removeAttribute('src');
			}
			this.headingServiceType.title = '';
			this.headingServiceType.alt = '';
		}

		// Identifier
		if (this._propertiesContent && this._propertiesContent.hasAttribute('data-identifier')) {
			this.headingIdentifier.innerText = this._propertiesContent.getAttribute('data-identifier');
		} else {
			this.headingIdentifier.innerText = '';
		}

		// Rate Plan
		if (this._ratePlanSelect.value) {
			var ratePlanOption = this._ratePlanSelect.querySelector(':checked');
			this.headingRatePlan.innerText = ratePlanOption.innerText;
		} else {
			this.headingRatePlan.innerText = '';
		}

		// For consistency with other "sync*" methods, "wrap" in a promise (not really, because this is sync)
		return promise().fulfill();
	},

	_syncVisibility: function () {
		if (this.NODE.hasAttribute('data-collapsed')) {
			this.NODE.removeAttribute('data-collapsed');
		} else {
			this.NODE.setAttribute('data-collapsed', '');
		}

		// For consistency with other "sync*" methods, "wrap" in a promise (not really, because this is sync)
		return promise().fulfill();
	},

	_remove: function () {
		this.NODE.parentNode.removeChild(this.NODE);
	}
});

return self;