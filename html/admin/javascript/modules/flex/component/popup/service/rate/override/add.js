"use strict";

var H = require('fw/dom/factory'), // HTML
	Class = require('fw/class'),
	Component = require('fw/component'),
	Layer = require('fw/component/layer'),
	Window = require('fw/component/window'),
	Alert = require('fw/component/popup/alert'),
	xhr = require('xhr'),
	jhr = require('xhr/json-handler'),
	jsonForm = require('json-form'),
	promise = require('promise'),
	delegate = require('delegate'),
	inputDate = require('dom/input/date')
;

var SUCCESS_LAYER_TIMEOUT = 1.5 * 1000; // 2 seconds
var UNIT_TYPES = {
	1: {
		describe: function (value, forceNumber) {
			if (value === 1) {
				if (forceNumber) {
					return '1 second';
				}
				return 'second';
			}
			return '' + value + ' seconds';
		}
	},
	2: {
		describe: function (value, forceNumber) {
			if (value === 1) {
				if (forceNumber) {
					return '1 second';
				}
				return 'item';
			}
			return '' + value + ' items';
		}
	},
	3: {
		describe: function (value, forceNumber) {
			if (value === 1) {
				if (forceNumber) {
					return '1 KB';
				}
				return 'KB';
			}
			// TODO: KB/MB/GB
			return '' + value + ' KB';
		}
	},
	4: {
		describe: function (value, forceNumber) {
			if (value === 1) {
				if (forceNumber) {
					return '1 message';
				}
				return 'message';
			}
			return '' + value + ' messages';
		}
	},
};


function getToday() {
	var date = new Date();
	date.setMilliseconds(0);
	date.setSeconds(0);
	date.setMinutes(0);
	date.setHours(0);
	return date;
}
function formatCurrency(value) {
	var number = Number(value);
	if (/^\d+(\.?\d{0,1})$/.test(number)) {
		return '$' + number.toFixed(2);
	}
	return '$' + number;
}

function buildDayAvailabilityDescription(days) {
	var daysAvailable = Object.keys(days).filter(function (day) {return days[day];});
	if (daysAvailable.length === 7) {
		return H.$fragment();
	}

	var daysElement = H.span({class: 'flex-component-account-service-plan-overriderates-add-search-matches-rate-algorithm-restrictions-days'});
	daysAvailable.forEach(function (day) {
		return daysElement.appendChild(H.span({'data-rate-day': day.substr(0, 2)}, day));
	});
	return daysElement;
}
function buildTimeAvailabilityDescription(startTime, endTime) {
	if (startTime === '00:00:00' && endTime === '23:59:59') {
		return H.$fragment();
	}

	var timesElement = H.span({class: 'flex-component-account-service-plan-overriderates-add-search-matches-rate-algorithm-restrictions-times'});
	timesElement.appendChild(H.$fragment(
		H.span({class: 'flex-component-account-service-plan-overriderates-add-search-matches-rate-algorithm-restrictions-times-start'}, startTime),
		'–',
		H.span({class: 'flex-component-account-service-plan-overriderates-add-search-matches-rate-algorithm-restrictions-times-end'}, endTime)
	));
	return timesElement;
}
function buildRateStageDescription(rateStage) {
	var chargeComponentDescriptions = [];

	if (rateStage.passthrough || rateStage.markup_percentage || rateStage.markup_dollars_per_unit_block) {
		chargeComponentDescriptions.push(H.span({'data-rate-component': 'cost'}, 'Cost'));
	}

	if (rateStage.flagfall) {
		chargeComponentDescriptions.push(H.span({'data-rate-component': 'flagfall'}, formatCurrency(rateStage.flagfall)));
	}
	if (rateStage.rate_per_unit) {
		chargeComponentDescriptions.push(H.span({'data-rate-component': 'rate'},
			H.span({'data-rate-component': 'rate-per-unit-block'}, formatCurrency(rateStage.rate_per_unit)),
			'per',
			H.span({'data-rate-component': 'unit-block-size'}, UNIT_TYPES[rateStage.record_type_unit_type].describe(rateStage.unit_block_size))
		));
	}

	if (rateStage.markup_percentage) {
		chargeComponentDescriptions.push(H.span({'data-rate-component': 'markup-percentage'}, 'Cost × ' + rateStage.markup_percentage + '%'));
	}
	if (rateStage.markup_dollars_per_unit_block) {
		chargeComponentDescriptions.push(H.span({'data-rate-component': 'markup-rate'},
			H.span({'data-rate-component': 'markup-per-unit-block'}, formatCurrency(rateStage.markup_dollars_per_unit_block)),
			'per',
			H.span({'data-rate-component': 'unit-block-size'}, UNIT_TYPES[rateStage.record_type_unit_type].describe(rateStage.unit_block_size))
		));
	}

	// Combine components
	var descriptions = chargeComponentDescriptions.reduce(function (container, component, index, collection) {
		if (index !== 0) {
			container.appendChild(H.$fragment('+'));
		}
		container.appendChild(component);
		return container;
	}, H.span());

	// Minimum chage is applied "around" regular components
	if (rateStage.minimum_charge) {
		descriptions.appendChild(H.$fragment(
			'Minimum of',
			H.span({'data-rate-component': 'minimum'}, formatCurrency(rateStage.minimum_charge))
		));
	}

	// "Free", only if
	if (!descriptions.firstChild) {
		descriptions.appendChild(H.span({'data-rate-component': 'free'}, 'Free'));
	}

	// Start Offset
	if (rateStage.unit_offset) {
		descriptions.insertBefore(H.$fragment(
			'after',
			H.span({'data-rate-component': 'unit-offset'}, UNIT_TYPES[rateStage.record_type_unit_type].describe(rateStage.unit_offset))
		), descriptions.firstChild);
	} else if (rateStage.dollar_offset) {
		descriptions.appendChild(H.$fragment(
			'after',
			H.span({'data-rate-component': 'dollar-offset'}, formatCurrency(rateStage.dollar_offset))
		));
	}

	// Capping (Unit Cap priority)
	if (rateStage.unit_cap) {
		descriptions.appendChild(H.$fragment(
			'for',
			H.span({'data-rate-component': 'unit-cap'}, UNIT_TYPES[rateStage.record_type_unit_type].describe(rateStage.unit_cap))
		));
	} else if (rateStage.dollar_cap) {
		descriptions.appendChild(H.$fragment(
			'up to',
			H.span({'data-rate-component': 'dollar-cap'}, formatCurrency(rateStage.dollar_cap))
		));
	}

	return descriptions;
}

function buildRateDescription(rate) {
	// debugger;
	var descriptionFragment = H.$fragment();

	var hasStandardRate,
		hasExcessRate,
		hasPassthroughRate;
	if (parseInt(rate.PassThrough, 10)) {
		hasPassthroughRate = true;

		// "Passthrough" rates, which are simplified Cost+ rates
		var passthroughStage = buildRateStageDescription({
			passthrough: true,
			flagfall: parseFloat(rate.StdFlagfall),
			minimum_charge: parseFloat(rate.StdMinCharge)
		});
		passthroughStage.dataset.rateStage = 'passthrough';
		passthroughStage.insertBefore(
			H.span({class: 'flex-component-account-service-plan-overriderates-add-search-matches-rate-algorithm-stage-name'}, 'Passthrough Rate'),
			passthroughStage.firstChild
		);
		descriptionFragment.appendChild(passthroughStage);
	} else {
		// Regular Rates
		// Standard Component
		hasStandardRate = (
			parseFloat(rate.StdMinCharge) ||
			parseFloat(rate.StdFlagfall) ||
			parseFloat(rate.StdPercentage) ||
			parseFloat(rate.CapUnits) ||
			(
				parseInt(rate.StdUnits, 10) && (
					parseFloat(rate.StdRatePerUnit) ||
					parseFloat(rate.StdMarkup)
				)
			)
		);

		if (hasStandardRate) {
			var standardStage = buildRateStageDescription({
				record_type_unit_type: rate.record_type_unit_type,
				unit_block_size: parseInt(rate.StdUnits, 10),
				rate_per_unit: parseFloat(rate.StdRatePerUnit),
				flagfall: parseFloat(rate.StdFlagfall),
				markup_percentage: parseFloat(rate.StdPercentage),
				markup_dollars_per_unit_block: parseFloat(rate.StdMarkup),
				minimum_charge: parseFloat(rate.StdMinCharge),
				unit_cap: parseInt(rate.CapUnits, 10),
				dollar_cap: parseFloat(rate.CapCost)
			});
			standardStage.dataset.rateStage = 'standard';
			standardStage.insertBefore(
				H.span({class: 'flex-component-account-service-plan-overriderates-add-search-matches-rate-algorithm-stage-name'}, 'Standard Rate'),
				standardStage.firstChild
			);
			descriptionFragment.appendChild(standardStage);
		}

		// Excess Component
		hasExcessRate = (
			(parseInt(rate.CapUnits, 10) || parseFloat(rate.CapCost)) && (
				parseFloat(rate.StdMinCharge) ||
				parseFloat(rate.StdFlagfall) ||
				parseFloat(rate.StdPercentage) ||
				(
					parseInt(rate.StdUnits, 10) && (
						parseFloat(rate.StdRatePerUnit) ||
						parseFloat(rate.StdMarkup)
					)
				)
			)
		);
		if (parseInt(rate.CapUnits, 10) || parseFloat(rate.CapCost)) {
			if (parseFloat(rate.ExsFlagfall)) {
				hasExcessRate = true;
			} else if (parseFloat(rate.ExsPercentage)) {
				hasExcessRate = true;
			} else if (parseInt(rate.ExsUnits, 10) && (parseFloat(rate.ExsRatePerUnit) || parseFloat(rate.ExsMarkup))) {
				hasExcessRate = true;
			}
		}

		if (hasExcessRate) {
			var excessStage = buildRateStageDescription({
				record_type_unit_type: rate.record_type_unit_type,
				unit_block_size: parseInt(rate.ExsUnits, 10),
				rate_per_unit: parseFloat(rate.ExsRatePerUnit),
				flagfall: parseFloat(rate.ExsFlagfall),
				markup_percentage: parseFloat(rate.ExsPercentage),
				markup_dollars_per_unit_block: parseFloat(rate.ExsMarkup),
				unit_offset: parseInt(rate.CapUsage, 10),
				dollar_offset: parseFloat(rate.CapLimit)
			});
			excessStage.dataset.rateStage = 'excess';
			excessStage.insertBefore(
				H.span({class: 'flex-component-account-service-plan-overriderates-add-search-matches-rate-algorithm-stage-name'}, 'Excess Rate'),
				excessStage.firstChild
			);
			descriptionFragment.appendChild(excessStage);
		}
	}

	// Free?
	if (!hasStandardRate && !hasExcessRate && !hasPassthroughRate) {
		descriptionFragment.appendChild(H.span({'data-rate-stage': 'free'},
			H.span({'data-rate-component': 'free'}, 'Free')
		));
	} else {
		// Applicable for Discounting?
		if (!parseInt(rate.Uncapped, 10)) {
			descriptionFragment.appendChild(
				H.span({'data-rate-stage': 'no-discounting'}, 'No Discounting')
			);
		}
	}

	// Day & time restrictions
	var dayRestrictions = buildDayAvailabilityDescription({
		Sunday: !!parseInt(rate.Sunday),
		Monday: !!parseInt(rate.Monday),
		Tuesday: !!parseInt(rate.Tuesday),
		Wednesday: !!parseInt(rate.Wednesday),
		Thursday: !!parseInt(rate.Thursday),
		Friday: !!parseInt(rate.Friday),
		Saturday: !!parseInt(rate.Saturday)
	}, rate.StartTime, rate.EndTime);
	var timeRestrictions = buildTimeAvailabilityDescription(rate.StartTime, rate.EndTime);
	if (dayRestrictions.firstChild || timeRestrictions.firstChild) {
		descriptionFragment.appendChild(
			H.span({class: 'flex-component-account-service-plan-overriderates-add-search-matches-rate-algorithm-restrictions'},
				'Restricted to',
				dayRestrictions,
				timeRestrictions
			)
		);
	}

	return descriptionFragment;
}

var self = new Class({
	extends: Component,

	construct: function () {
		this.CONFIG = Object.extend({
			serviceId: {}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-component-account-service-plan-overriderates-add');

		this._syncUIPromise = promise();
		this._configCache = {};
	},

	_buildUI: function () {
		this.NODE = this._form = H.form({onsubmit: this._submit.bind(this)/* DEBUG *//*, novalidate: ''*//* DEBUG */},
			this._serviceElement = H.input({type: 'hidden', name: 'service.id'}),

			H.div({role: 'group', class: 'flex-component-account-service-plan-overriderates-add-search'},
				H.h3({class: 'flex-component-account-service-plan-overriderates-add-search-label'}, 'Override Rate'),

				H.div({class: 'flex-component-account-service-plan-overriderates-add-search-controlset'},
					// this._rateGroupElement = H.select({name: 'rate_group_id'}),
					this._rateSearchElement = H.input({type: 'search', name: 'search', 'aria-label': 'Search available rates', placeholder: 'Search available rates…', required: '', autocomplete: 'off', oninput: this._searchRates.bind(this)}),
					this._rateSearchMatchesElement = H.div({class: 'flex-component-account-service-plan-overriderates-add-search-matches'})
				)
			),

			H.div({role: 'group', class: 'flex-component-account-service-plan-overriderates-add-dates'},
				H.div({role: 'group', class: 'flex-component-account-service-plan-overriderates-add-dates-starts'},
					H.h3({class: 'flex-component-account-service-plan-overriderates-add-dates-starts-label'}, 'Starts'),

					H.div({class: 'flex-component-account-service-plan-overriderates-add-dates-starts-controlset',
							onchange: [
								delegate('[name="starts"]', this._syncDateStarts.bind(this)),
								delegate('[name="starts"]', this._syncDateStartsDate.bind(this)),
								delegate('[name="starts"]', this._validateDates.bind(this))
							]
						},
						H.label({class: 'flex-component-account-service-plan-overriderates-add-dates-starts-immediately'},
							this._dateStartsImmediately = H.input({type: 'radio', name: 'starts', value: 'immediately', required: '', checked: ''}),
							H.span({class: 'flex-component-account-service-plan-overriderates-add-dates-starts-immediately-label'}, 'Immediately')
						),
						H.div({role: 'group', class: 'flex-component-account-service-plan-overriderates-add-dates-starts-ondate'},
							H.label({class: 'flex-component-account-service-plan-overriderates-add-dates-starts-ondate-control'},
								this._dateStartsDate = H.input({type: 'radio', name: 'starts', value: 'date', required: ''}),
								H.span({class: 'flex-component-account-service-plan-overriderates-add-dates-starts-ondate-date-label'}, 'On date')
							),
							this._dateStartsDateValue = H.input({type: 'date', name: 'start_date', required: '', oninput: [this._validateDates.bind(this), this._syncDateStartsDate.bind(this)]}),
							this._dateStartsDateMessage = H.p({class: 'flex-component-account-service-plan-overriderates-add-dates-starts-ondate-message'})
						)
					)
				),

				H.div({role: 'group', class: 'flex-component-account-service-plan-overriderates-add-dates-ends'},
					H.h3({class: 'flex-component-account-service-plan-overriderates-add-dates-ends-label'}, 'Ends'),

					H.div({class: 'flex-component-account-service-plan-overriderates-add-dates-ends-controlset',
							onchange: [
								delegate('[name="ends"]', this._syncDateEnds.bind(this)),
								delegate('[name="ends"]', this._validateDates.bind(this))
							]
						},
						H.label({class: 'flex-component-account-service-plan-overriderates-add-dates-ends-indefinite'},
							this._dateEndsIndefinitely = H.input({type: 'radio', name: 'ends', value: 'indefinite', required: '', checked: ''}),
							H.span({class: 'flex-component-account-service-plan-overriderates-add-dates-ends-indefinite-label'}, 'Indefinitely')
						),
						H.div({role: 'group', class: 'flex-component-account-service-plan-overriderates-add-dates-ends-ondate'},
							H.label({class: 'flex-component-account-service-plan-overriderates-add-dates-ends-ondate-control'},
								this._dateEndsDate = H.input({type: 'radio', name: 'ends', value: 'date', required: ''}),
								H.span({class: 'flex-component-account-service-plan-overriderates-add-dates-ends-date-label'}, 'On date')
							),
							this._dateEndsDateValue = H.input({type: 'date', name: 'end_date', required: '', oninput: this._validateDates.bind(this)})
						)
					)
				)
			),

			H.div({role: 'group', class: 'flex-component-account-service-plan-overriderates-add-buttons'},
				this._applyButton = H.button({type: 'submit', name: 'apply'}, 'Apply Override Rate'),
				this._cancelButton = H.button({type: 'button', name: 'cancel', onclick: this.fire.bind(this, 'cancel')}, 'Cancel')
			)
		);

		// Date input polyfills
		if (!inputDate.isNativelySupported()) {
			var dateStartPickerButton = inputDate.createDatePickerButton(this._dateStartsDateValue);
			var dateEndPickerButton = inputDate.createDatePickerButton(this._dateEndsDateValue);

			dateStartPickerButton.classList.add('flex-component-account-service-plan-overriderates-add-dates-starts-ondate-datepicker');
			dateEndPickerButton.classList.add('flex-component-account-service-plan-overriderates-add-dates-ends-ondate-datepicker');

			this._dateStartsDateValue.parentNode.insertBefore(dateStartPickerButton, this._dateStartsDateValue.nextSibling);
			this._dateEndsDateValue.parentNode.insertBefore(dateEndPickerButton, this._dateEndsDateValue.nextSibling);

			this._dateStartsDateValue.placeholder = 'yyyy-mm-dd';
			this._dateEndsDateValue.placeholder = 'yyyy-mm-dd';

			this._dateStartsDateValue.pattern = '^(\\d{4})\\-([0-1]?\\d)\\-([0-3]?\\d)$';
			this._dateEndsDateValue.pattern = '^(\\d{4})\\-([0-1]?\\d)\\-([0-3]?\\d)$';
		}
	},

	_buildRateUI: function (rate) {
		return H.label({class: 'flex-component-account-service-plan-overriderates-add-search-matches-rate'},
			H.input({type: 'radio', name: 'rate.id', value: rate.Id, required: ''}),
			H.span({class: 'flex-component-account-service-plan-overriderates-add-search-matches-rate-calltype'},
				H.span({class: 'flex-component-account-service-plan-overriderates-add-search-matches-rate-calltype-recordtype'}, rate.record_type_name),
				H.span({class: 'flex-component-account-service-plan-overriderates-add-search-matches-rate-calltype-destination'}, rate.destination_description)
			),
			H.span({class: 'flex-component-account-service-plan-overriderates-add-search-matches-rate-algorithm'}, buildRateDescription(rate)),
			H.button({type: 'button', class: 'flex-component-account-service-plan-overriderates-add-search-matches-rate-details', onclick: function () {
				Vixen.Popup.ShowAjaxPopup('ViewRatePopupId_' + rate.id, 'medium', 'Rate', 'Rate', 'View', {'Rate': {'Id': rate.Id}}, 'nonmodal');
			}}, 'View Rate Details')
		);
	},

	_syncUI: function () {
		this._serviceElement.value = this.get('serviceId');
		this._syncDateStarts();
		this._syncDateEnds();

		this._onReady();
	},

	_searchRates: function (event) {
		this._rateSearchMatchesElement.innerHTML = '';
		var constraints = this._getConstraints();

		// Only search if we have some constraints
		if (!constraints.include_terms.length && !constraints.exclude_terms.length) {
			return;
		}

		// Search
		jhr(
			'Service',
			'searchAvailableRates',
			{arguments: [this.get('serviceId'), constraints]}
		).then(
			function searchComplete(request) {
				// If the constraints now are the same as they were at time of request, we're good to go
				if (JSON.stringify(constraints) === JSON.stringify(this._getConstraints())) {
					// Populate to resultset
					this._rateSearchMatchesElement.appendChild(
						H.$fragment.apply(H, request.parseJSONResponse().rates.map(this._buildRateUI.bind(this)))
					);
					this._validateSearch();
				}
			}.bind(this)
		);
	},

	_getConstraints: function () {
		// TODO: -markup -flagfall -excess +flagfall -rate -markup$ -markup%

		// Allows space separated terms or "quoted ""exact"" phrases"
		// debugger;
		var termRegex = /(?:"([^"]+)"|(\S+))/g,
			search = this._rateSearchElement.value,
			inclusiveTerms = [],
			exclusiveTerms = [],
			termMatch,
			term;
		while (termMatch = termRegex.exec(search)) {
			// Replace opening and closing " with nothing, and " pairs with just a single " to unescape
			term = termMatch[0].replace(/(^"|"$)/g, '').replace(/""/g, '"');
			if (term[0] === '-') {
				exclusiveTerms.push(term.substr(1));
			} else {
				inclusiveTerms.push(term);
			}
		}

		return {
			include_terms: inclusiveTerms,
			exclude_terms: exclusiveTerms,
			record_type_id: null // TODO
		};
	},

	_syncDateStarts: function () {
		if (this._dateStartsDate.checked) {
			this._dateStartsDateValue.disabled = false;
		} else {
			this._dateStartsDateValue.disabled = true;
		}
	},

	_syncDateEnds: function () {
		if (this._dateEndsDate.checked) {
			this._dateEndsDateValue.disabled = false;
		} else {
			this._dateEndsDateValue.disabled = true;
		}
	},

	_syncDateStartsDate: function () {
		var startDate;
		if (this._dateStartsDate.checked) {
			startDate = new Date(this._dateStartsDateValue.value);
			if (startDate < getToday()) {
				this._dateStartsDateMessage.textContent = 'Warning: Making an override rate start in the past can result in charge inconsistencies between already-invoiced and yet-to-be-invoiced usage.';
				return;
			}
		}
		this._dateStartsDateMessage.textContent = '';
	},

	_validateSearch: function () {
		if (!this._rateSearchMatchesElement.firstChild) {
			// No matches
			this._rateSearchElement.setCustomValidity('You must search for and select a rate to override with');
		} else {
			this._rateSearchElement.setCustomValidity('');
		}
	},

	_validateDates: function () {
		var startDate;
		if (this._dateStartsDate.checked) {
			startDate = new Date(this._dateStartsDateValue.value);
		} else if (this._dateStartsImmediately.checked) {
			startDate = new Date();
		}

		var endDate;
		if (this._dateEndsDate.checked) {
			endDate = new Date(this._dateEndsDateValue.value);
		} else if (this._dateEndsIndefinitely.checked) {
			endDate = new Date('9999-12-31');
		}

		if (startDate && endDate) {
			if (startDate > endDate) {
				this._dateEndsDateValue.setCustomValidity('The override must end after it starts');
				return;
			}
		}
		this._dateEndsDateValue.setCustomValidity('');
	},

	_submit: function (event) {
		event.preventDefault();

		// Check validity
		if (!this._form.noValidate) {
			this._validateSearch();
			this._validateDates();
			if (!this._form.checkValidity()) {
				return;
			}
		}

		this._applyButton.disabled = true;
		this._cancelButton.disabled = true;
		this._form.classList.add('-saving');

		var formData = jsonForm(this._form);
		jhr('Service_Rate', 'saveNew', {arguments: [formData], parseJSONResponse: true}).then(
			function success() {
				// Success overlay
				var successWindow = new Window({sExtraClass: 'flex-popup-account-service-plan-overriderates-add-saved', bModal: false},
					H.p('Override Rate Saved')
				);
				successWindow.display();
				setTimeout(successWindow.hide.bind(successWindow), SUCCESS_LAYER_TIMEOUT);
				this.fire('save');
			}.bind(this),
			function failure(reason) {
				var failureFragment;
				if (reason.response.errors) {
					failureFragment = H.$fragment(
						H.p('There were issues when trying to apply the override rate:'),
						H.ul(
							H.$fragment.apply(H, reason.response.errors.map(function (error) {
								return H.li(error);
							}))
						)
					);
				} else {
					failureFragment = H.$fragment(H.p(reason.sMessage));
				}

				var failureWindow = new Window({
						sTitle: 'Override Rate Error',
						sExtraClass: 'flex-popup-account-service-plan-overriderates-add-savefailed'
				});
				failureWindow.appendChild(H.$fragment(
					failureFragment,
					H.fieldset(
						H.button({onclick: failureWindow.hide.bind(failureWindow)}, 'OK')
					)
				));
				failureWindow.display();
			}
		).finally(function () {
			this._applyButton.disabled = false;
			this._cancelButton.disabled = false;
			this._form.classList.remove('-saving');
		}.bind(this));
	},

	statics: {
		createAsPopup: function (config) {
			var component = new self(config);
			var popup = new Window({
				sTitle: 'Add Override Rate',
				sExtraClass: 'flex-popup-account-service-plan-overriderates-add'
			}, component);
			popup.display();
			component.observe('save', popup.hide.bind(popup));
			component.observe('cancel', popup.hide.bind(popup));
			// component.display();
			return component;
		}
	}
});

return self;