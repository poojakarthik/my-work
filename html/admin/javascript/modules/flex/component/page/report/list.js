"use strict";

var H = require('fw/dom/factory'), // HTML
	Class = require('fw/class'),
	Component = require('fw/component')
;
	
var self = new Class({
	extends: Component,

	_buildUI: function () {
		this.NODE = H.section(
			this.NODE = H.section(
				H.h2(
				H.span({class: 'flex-page-account-service-plan-overriderates-heading-label'}, 'Rate Overrides'),
				this._addOverrideButton = H.button({type: 'button', class: 'flex-page-account-service-plan-overriderates-add', disabled: '', onclick: this._showAddServiceRatePopup.bind(this)}, 'New Override Rate')
			),

			this._recordTypesElement = H.ol({class: 'flex-page-account-service-plan-overriderates-recordtypes'})
		);
	}
});

return self;