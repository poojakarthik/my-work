"use strict";

var H = require('fw/dom/factory'), // HTML
	Class = require('fw/class'),
	Component = require('fw/component'),
	jhr = require('xhr/json-handler'),
	Constraint = require('./constraint/add'),
	jsonForm = require('json-form');

var self = new Class({

	extends: Component,

	construct   : function() {
		this.CONFIG = Object.extend({
			aConstraints : {}
			// mAccountDefaultRecordTypeVisibility : {}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		//this.NODE.addClassName('flex-page-account-record-type-visibility');
	},

	_buildUI: function () {
		this.NODE = H.section({class: 'flex-component-report-add'},
			H.h1('Add New Report'),
			this._oForm = 
				H.form({method: 'post'},
					H.label({class: 'flex-component-report-add-title'},
						H.span({class: 'flex-component-report-add-title-label'}, 'Title'),
						H.input({type: 'text', name: 'title', maxlength: '256', required: ''})
					),
					H.label({class: 'flex-component-report-add-summary'},
						H.span({class: 'flex-component-report-add-summary-label'}, 'Summary'),
						H.textarea({name: 'summary', maxlength: '512'})
					),
					H.label({class: 'flex-component-report-add-query'},
						H.span({class: 'flex-component-report-add-query-label'}, 'Main SQL'),
						H.textarea({required: '', name: 'query', maxlength: '10000'})
					),
					H.label({class: 'flex-component-report-add-reportemployee'},
						H.span({class: 'flex-component-report-add-reportemployee-label'}, 'Report Employee'),
							this._employeeContainer=H.div({class: 'flex-component-report-add-reportemployee-div'})
					),
					H.fieldset({class: 'flex-component-report-add-buttonset'},
						H.button({type: 'button', name: 'save'}, 'Save').observe('click', this._submit.bind(this, null)),
						H.button({type: 'button', name: 'editConstraint'}, 'Edit Constraint').observe('click', this._editConstraints.bind(this, null))
					)
				)
		);
		// Add to DOM
		$$('.flex-page')[0].appendChild(this.NODE);
	},
	_syncUI: function () {
		if (!this._bInitialised || !this._bReady) {
			jhr('Report', 'getEmployees', {arguments: []}).then(
				function success(request) {
					var response = request.parseJSONResponse();
					response.employees.forEach(function (employee) {
						this._employeeContainer.appendChild(
							H.label({class: 'flex-component-report-add-reportemployee-div-container'},
								H.span({class: 'flex-component-report-add-reportemployee-div-container-label'},employee.FirstName + ' ' + employee.LastName),
								H.input({type: 'checkbox', name: 'reportemployees[]', value: employee.Id})));
					}.bind(this));
					this._onReady();
				}.bind(this),
				function (error) {
					// TODO: Handle Error
				}
			);
		}
	},

	_submit: function() {
		// Save data to server
		this.oData = jsonForm(this._oForm);
		this.aConstraint = this.get('aConstraints');
		/* Save above to database into three tables
		{
			aReport : this.oData,
			aConstraint : this
		}*/
		// XHR to JSON Handler which saves data to database.

	},

	_editConstraints: function() {

		if(!this.get('aConstraints')) {
			this.set('aConstraints', Array());
		}
		var currentConstraints=this.get('aConstraints');
		//console.log('test');
		console.log(currentConstraints);
		var oPopup = Constraint.createAsPopup({
				existingConstraints: currentConstraints,
				oncomplete      : function(formData) {
						// Save data locally, this.aConstraints
						currentConstraints.push(formData.mData);
						this.set('aConstraints',currentConstraints);
						oPopup.hide();
						
				}.bind(this),
				onready : function () {
						oPopup.display();
				}.bind(this),
				oncancel : function() {
						oPopup.hide();
				}
		});
	},

});

return self;