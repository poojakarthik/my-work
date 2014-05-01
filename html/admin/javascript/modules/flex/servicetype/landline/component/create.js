"use strict";

var H = require('fw/dom/factory'), // HTML
	Class = require('fw/class'),
	Component = require('fw/component'),
	Alert = require('fw/component/popup/alert'),
	promise = require('promise'),
	jhr = require('xhr/json-handler'),
	arrayify = require('arrayify'),
	delegate = require('dom/event/delegate');

var LINE_TYPE = {
	BUSINESS: 0,
	RESIDENTIAL: 1
};

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
		this.NODE.addClassName('flex-servicetype-landline-create');
	},

	_buildUI: function () {
		this.NODE = H.div(
			H.label({class: 'flex-servicetype-landline-create-fnn'},
				H.abbr({class: 'flex-servicetype-landline-create-fnn-label', title: 'Full National Number'}, 'FNN'),
				H.input({name: 'fnn', maxlength: 10, pattern: '^0[2378]\\d{8}$'})
			),

			H.div({class: 'flex-servicetype-landline-create-linetype'},
				H.span({class: 'flex-servicetype-landline-create-linetype-label'}, 'Line Type'),
				H.div({class: 'flex-servicetype-landline-create-linetype-controlset'},
					H.label({class: 'flex-servicetype-landline-create-linetype-residential'},
						H.input({type: 'radio', name: 'line_type', value: LINE_TYPE.RESIDENTIAL, required: true}),
						H.span('Residential')
					),
					H.label({class: 'flex-servicetype-landline-create-linetype-business'},
						H.input({type: 'radio', name: 'line_type', value: LINE_TYPE.BUSINESS, required: true}),
						H.span('Business')
					)
				)
			),

			this._addressContainer = H.div({class: 'flex-servicetype-landline-create-address'},
				H.h3({class: 'flex-servicetype-landline-create-address-heading'}, 'Service Address'),

				H.div({class: 'flex-servicetype-landline-create-address-category'},
					H.span({class: 'flex-servicetype-landline-create-address-category-label'}, 'Address Category'),
					H.div({onchange: this._syncAddressCategory.bind(this)},
						H.label({class: 'flex-servicetype-landline-create-address-category-standard'},
							H.input({type: 'radio', name: 'address.category', value: 'standard', required: true, checked: ''}),
							H.span('Standard')
						),
						H.label({class: 'flex-servicetype-landline-create-address-category-postal'},
							H.input({type: 'radio', name: 'address.category', value: 'postal', required: true}),
							H.span('Postal')
						),
						H.label({class: 'flex-servicetype-landline-create-address-category-allotment'},
							H.input({type: 'radio', name: 'address.category', value: 'allotment', required: true}),
							H.span('Allotment')
						)
					)
				),

				// Standard Addresses
				H.div({class: 'flex-servicetype-landline-create-address-standard', 'data-address-category': 'standard'},
					H.div({class: 'flex-servicetype-landline-create-address-standard-subdivision'},
						H.span({class: 'flex-servicetype-landline-create-address-standard-subdivision-label'}, 'Subdivision'),
						H.div({class: 'flex-servicetype-landline-create-address-standard-subdivision-controlset'},
							this._standardSubdivisionTypeSelect = H.select({name: 'address.subdivision.type', title: 'Subdivision Type', onchange: this._syncStandardSubdivisionType.bind(this)}),
							this._standardSubdivisionNumberInput = H.input({type: 'text', name: 'address.subdivision.number', title: 'Subdivision Number (e.g. 1, 7A)', size: 7, maxlength: 7, placeholder: 'Number', pattern: '^\\s*[1-9]\\d{0,4}\\s*[A-Za-z]{0,2}\\s*$', onchange: this._syncStandardSubdivisionNumber.bind(this)})
						)
					),

					H.label({class: 'flex-servicetype-landline-create-address-standard-propertyname'},
						H.span({class: 'flex-servicetype-landline-create-address-standard-propertyname-label'}, 'Property Name'),
						this._standardPropertyNameInput = H.input({type: 'text', name: 'address.property_name', placeholder: 'e.g. Westfield Mall', size: 30, maxlength: 30, onchange: this._syncStandardPropertyName.bind(this)})
					),

					H.div({class: 'flex-servicetype-landline-create-address-standard-street'},
						H.span({class: 'flex-servicetype-landline-create-address-standard-street-label'}, 'Street Address'),
						H.div({class: 'flex-servicetype-landline-create-address-standard-subdivision'},
							H.input({type: 'text', name: 'address.street.number.composite', title: 'Street Number (e.g. 71, 22B, 156-168)', placeholder: 'Number', size: 11, maxlength: 11, pattern: '^\\s*([1-9]\\d*)\\s*(|[a-zA-Z]|([\\-–—]\\s*[1-9]\\d*))\\s*$'}),
							this._standardStreetNameInput = H.input({type: 'text', name: 'address.street.name', title: 'Street Name', placeholder: 'Street', required: true, size: 30, maxlength: 30, onchange: this._syncStandardStreetName.bind(this)}),
							this._standardStreetTypeSelect = H.select({name: 'address.street.type', title: 'Street Type', onchange: this._syncStandardStreetType.bind(this)}),
							this._standardStreetTypeSuffixSelect = H.select({name: 'address.street.type_suffix', title: 'Street Type Suffix'})
						)
					),

					H.label({class: 'flex-servicetype-landline-create-address-standard-locality'},
						H.span({class: 'flex-servicetype-landline-create-address-standard-locality-label'}, 'Locality'),
						H.input({type: 'text', name: 'address.locality', required: true, size: 30, maxlength: 30})
					),

					H.label({class: 'flex-servicetype-landline-create-address-standard-state'},
						H.span({class: 'flex-servicetype-landline-create-address-standard-state-label'}, 'State'),
						this._standardStateSelect = H.select({name: 'address.state', required: true})
					),

					H.label({class: 'flex-servicetype-landline-create-address-standard-postcode'},
						H.span({class: 'flex-servicetype-landline-create-address-standard-postcode-label'}, 'Postcode'),
						H.input({type: 'text', name: 'address.postcode', required: true, size: 4, maxlength: 4, pattern: '^\\d{4}$'})
					)
				),

				// Postal Addresses
				H.div({class: 'flex-servicetype-landline-create-address-postal', 'data-address-category': 'postal'},
					H.div({class: 'flex-servicetype-landline-create-address-postal-subdivision'},
						H.span({class: 'flex-servicetype-landline-create-address-postal-subdivision-label'}, 'Box'),
						H.div({class: 'flex-servicetype-landline-create-address-postal-subdivision-controlset'},
							this._postalSubdivisionTypeSelect = H.select({name: 'address.subdivision.type', title: 'Box Type', required: true}),
							H.input({type: 'text', name: 'address.subdivision.number', title: 'Box Number (e.g. 1, 7A)', size: 7, maxlength: 7, placeholder: 'Number', required: true, pattern: '^\\s*[1-9]\\d{0,4}\\s*[A-Za-z]{0,2}\\s*$'})
						)
					),

					H.label({class: 'flex-servicetype-landline-create-address-postal-locality'},
						H.span({class: 'flex-servicetype-landline-create-address-postal-locality-label'}, 'Locality'),
						H.input({type: 'text', name: 'address.locality', required: true, size: 30, maxlength: 30})
					),

					H.label({class: 'flex-servicetype-landline-create-address-postal-state'},
						H.span({class: 'flex-servicetype-landline-create-address-postal-state-label'}, 'State'),
						this._postalStateSelect = H.select({name: 'address.state', required: true})
					),

					H.label({class: 'flex-servicetype-landline-create-address-postal-postcode'},
						H.span({class: 'flex-servicetype-landline-create-address-postal-postcode-label'}, 'Postcode'),
						H.input({type: 'text', name: 'address.postcode', required: true, size: 4, maxlength: 4, pattern: '^\\d{4}$'})
					)
				),

				// Allotment Addresses
				H.div({class: 'flex-servicetype-landline-create-address-allotment', 'data-address-category': 'allotment'},
					H.label({class: 'flex-servicetype-landline-create-address-allotment-subdivision'},
						H.span({class: 'flex-servicetype-landline-create-address-allotment-subdivision-label'}, 'Lot'),
						H.input({type: 'hidden', name: 'address.subdivision.type', value: 'LOT'}),
						H.input({type: 'text', name: 'address.subdivision.number', title: 'Lot Number (e.g. 1, 7A)', size: 7, maxlength: 7, placeholder: 'Number', pattern: '^\\s*[1-9]\\d{0,4}\\s*[A-Za-z]{0,2}\\s*$'})
					),

					H.label({class: 'flex-servicetype-landline-create-address-allotment-propertyname'},
						H.span({class: 'flex-servicetype-landline-create-address-allotment-propertyname-label'}, 'Property Name'),
						this._allotmentPropertyNameInput = H.input({type: 'text', name: 'address.property_name', placeholder: 'e.g. Westfield Mall', size: 30, maxlength: 30, onchange: this._syncAllotmentPropertyName.bind(this)})
					),

					H.div({class: 'flex-servicetype-landline-create-address-allotment-street'},
						H.span({class: 'flex-servicetype-landline-create-address-allotment-street-label'}, 'Street Address'),
						H.div({class: 'flex-servicetype-landline-create-address-allotment-subdivision'},
							this._allotmentStreetNameInput = H.input({type: 'text', name: 'address.street.name', title: 'Street Name', placeholder: 'Street', required: true, size: 30, maxlength: 30, onchange: this._syncAllotmentStreetName.bind(this)}),
							this._allotmentStreetTypeSelect = H.select({name: 'address.street.type', title: 'Street Type', onchange: this._syncAllotmentStreetType.bind(this)}),
							this._allotmentStreetTypeSuffixSelect = H.select({name: 'address.street.type_suffix', title: 'Street Type Suffix'})
						)
					),

					H.label({class: 'flex-servicetype-landline-create-address-allotment-locality'},
						H.span({class: 'flex-servicetype-landline-create-address-allotment-locality-label'}, 'Locality'),
						H.input({type: 'text', name: 'address.locality', required: true, size: 30, maxlength: 30})
					),

					H.label({class: 'flex-servicetype-landline-create-address-allotment-state'},
						H.span({class: 'flex-servicetype-landline-create-address-allotment-state-label'}, 'State'),
						this._allotmentStateSelect = H.select({name: 'address.state', required: true})
					),

					H.label({class: 'flex-servicetype-landline-create-address-allotment-postcode'},
						H.span({class: 'flex-servicetype-landline-create-address-allotment-postcode-label'}, 'Postcode'),
						H.input({type: 'text', name: 'address.postcode', required: true, size: 4, maxlength: 4, pattern: '^\\d{4}$'})
					)
				)
			)
		);
	},

	_syncUI: function () {
		if (!this._bInitialised || !this._bReady) {
			promise.all(
				this._populateStandardSubdivisionType(),
				this._populatePostalSubdivisionType(),
				this._populateStreetType(),
				this._populateStreetTypeSuffix(),
				this._populateState()
			).then(
				function fulfilled() {
					this._syncAddressCategory();
					this._syncStandardPropertyName();
					this._syncAllotmentPropertyName();
					this._syncStandardStreetName();
					this._syncAllotmentStreetName();
					this._syncStandardStreetType();
					this._syncAllotmentStreetType();

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

	_syncAddressCategory: function () {
		var addressCategory = this.NODE.querySelector('[name$="address.category"]:checked').value;
		this._addressContainer.setAttribute('data-address-category', addressCategory);

		// Disable irrelevant controls
		arrayify(this._addressContainer.querySelectorAll([
			'[data-address-category]:not(.flex-servicetype-landline-create-address):not([data-address-category="' + addressCategory + '"]) input',
			'[data-address-category]:not(.flex-servicetype-landline-create-address):not([data-address-category="' + addressCategory + '"]) select'
		].join(', '))).forEach(function (control) { control.disabled = true; });

		// Enable relevant controls
		arrayify(this._addressContainer.querySelectorAll([
			'[data-address-category="' + addressCategory + '"]:not(.flex-servicetype-landline-create-address) input',
			'[data-address-category="' + addressCategory + '"]:not(.flex-servicetype-landline-create-address) select'
		].join(', '))).forEach(function (control) { control.disabled = false; });
	},

	_syncStandardSubdivisionType: function () {
		this._standardSubdivisionNumberInput.required = !!this._standardSubdivisionTypeSelect.value;
	},

	_syncStandardSubdivisionNumber: function () {
		this._standardSubdivisionTypeSelect.required = !!this._standardSubdivisionNumberInput.value;
	},

	_syncStandardPropertyName: function () {
		this._standardStreetNameInput.required = !this._standardPropertyNameInput.value;
	},

	_syncAllotmentPropertyName: function () {
		this._allotmentStreetNameInput.required = !this._allotmentPropertyNameInput.value;
	},

	_syncStandardStreetName: function () {
		this._standardPropertyNameInput.required = !this._standardStreetNameInput.value;
		this._standardStreetTypeSelect.required = !!this._standardStreetNameInput.value;
	},

	_syncAllotmentStreetName: function () {
		this._allotmentPropertyNameInput.required = !this._allotmentStreetNameInput.value;
		this._allotmentStreetTypeSelect.required = !!this._allotmentStreetNameInput.value;
	},

	_syncStandardStreetType: function () {
		this._standardStreetTypeSuffixSelect.disabled = (this._standardStreetTypeSelect.value === 'NR');
	},

	_syncAllotmentStreetType: function () {
		this._allotmentStreetTypeSuffixSelect.disabled = (this._allotmentStreetTypeSelect.value === 'NR');
	},

	_fetchStandardSubdivisionTypes: function () {
		if (!this._fetchStandardSubdivisionTypesPromise) {
			this._fetchStandardSubdivisionTypesPromise = jhr('ServiceType_Landline', 'getStandardAddressTypes').then(function (request) {
				return request.parseJSONResponse().standardAddressTypes;
			});
		}
		return this._fetchStandardSubdivisionTypesPromise;
	},

	_populateStandardSubdivisionType: function () {
		return this._fetchStandardSubdivisionTypes().then(function (standardAddressTypes) {
			var fragment = H.$fragment();
			fragment.appendChild(H.option({value: '', selected: ''}, '∅ N/A'));
			Object.keys(standardAddressTypes).forEach(function (standardAddressTypeCode) {
				fragment.appendChild(H.option({value: standardAddressTypeCode}, standardAddressTypes[standardAddressTypeCode] + ' (' + standardAddressTypeCode +')'));
			});
			this._standardSubdivisionTypeSelect.appendChild(fragment);
		}.bind(this));
	},

	_fetchPostalSubdivisionTypes: function () {
		if (!this._fetchPostalSubdivisionTypesPromise) {
			this._fetchPostalSubdivisionTypesPromise = jhr('ServiceType_Landline', 'getPostalAddressTypes').then(function (request) {
				return request.parseJSONResponse().postalAddressTypes;
			});
		}
		return this._fetchPostalSubdivisionTypesPromise;
	},

	_populatePostalSubdivisionType: function () {
		return this._fetchPostalSubdivisionTypes().then(function (postalAddressTypes) {
			this._postalSubdivisionTypeSelect.appendChild(H.$fragment.apply(H, Object.keys(postalAddressTypes).map(function (postalSubdivisionTypeCode) {
				return H.option({value: postalSubdivisionTypeCode}, postalAddressTypes[postalSubdivisionTypeCode] + ' (' + postalSubdivisionTypeCode + ')');
			})));
		}.bind(this));
	},

	_fetchStreetTypes: function () {
		if (!this._fetchStreetTypesPromise) {
			this._fetchStreetTypesPromise = jhr('ServiceType_Landline', 'getStreetTypes').then(function (request) {
				return request.parseJSONResponse().streetTypes;
			});
		}
		return this._fetchStreetTypesPromise;
	},

	_populateStreetType: function () {
		return this._fetchStreetTypes().then(function (streetTypes) {
			var fragment = H.$fragment();
			fragment.appendChild(H.option({value: 'NR'}, '∅ N/A'));
			Object.keys(streetTypes).forEach(function (streetTypeCode) {
				fragment.appendChild(
					H.option({
							value: streetTypeCode,
							selected: (streetTypeCode === 'ST' ? '' : false) // Select STREET as the default
						},
						streetTypes[streetTypeCode] + ' (' + streetTypeCode + ')'
					)
				);
			});
			this._standardStreetTypeSelect.appendChild(fragment.cloneNode(true));
			this._allotmentStreetTypeSelect.appendChild(fragment.cloneNode(true));
		}.bind(this));
	},

	_fetchStreetTypeSuffixes: function () {
		if (!this._fetchStreetTypeSuffixesPromise) {
			this._fetchStreetTypeSuffixesPromise = jhr('ServiceType_Landline', 'getStreetTypeSuffixes').then(function (request) {
				return request.parseJSONResponse().streetTypeSuffixes;
			});
		}
		return this._fetchStreetTypeSuffixesPromise;
	},

	_populateStreetTypeSuffix: function () {
		return this._fetchStreetTypeSuffixes().then(function (streetTypeSuffixes) {
			var fragment = H.$fragment();
			fragment.appendChild(H.option({value: '', selected: ''}, '∅ N/A'));
			Object.keys(streetTypeSuffixes).forEach(function (streetTypeSuffixCode) {
				fragment.appendChild(H.option({value: streetTypeSuffixCode}, streetTypeSuffixes[streetTypeSuffixCode] + ' (' + streetTypeSuffixCode +')'));
			});
			this._standardStreetTypeSuffixSelect.appendChild(fragment.cloneNode(true));
			this._allotmentStreetTypeSuffixSelect.appendChild(fragment.cloneNode(true));
		}.bind(this));
	},

	_fetchStates: function () {
		if (!this._fetchStatesPromise) {
			this._fetchStatesPromise = jhr('ServiceType_Landline', 'getStates').then(function (request) {
				return request.parseJSONResponse().states;
			});
		}
		return this._fetchStatesPromise;
	},

	_populateState: function () {
		return this._fetchStates().then(function (states) {
			var fragment = H.$fragment();
			Object.keys(states).forEach(function (state) {
				fragment.appendChild(H.option({value: state}, state));
			});
			this._standardStateSelect.appendChild(fragment.cloneNode(true));
			this._postalStateSelect.appendChild(fragment.cloneNode(true));
			this._allotmentStateSelect.appendChild(fragment.cloneNode(true));
		}.bind(this));
	}
});

return self;