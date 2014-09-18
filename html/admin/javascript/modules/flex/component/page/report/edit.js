"use strict";

var H			= require('fw/dom/factory'), // HTML
	Class		= require('fw/class'),
	Component	= require('fw/component'),
	XHRRequest  = require('fw/xhrrequest'),
	jhr			= require('xhr/json-handler'),
	Constraint	= require('./constraint/edit'),
	Schedule	= require('./schedule/edit'),
	jsonForm	= require('json-form'),
    Popup		= require('fw/component/popup'),
	Hidden		= require('fw/component/control/hidden'),
	Checkbox	= require('fw/component/control/checkbox'),
	Select		= require('fw/component/control/select'),
	Text		= require('fw/component/control/text'),
	Textarea	= require('fw/component/control/textarea'),
	Form		= require('fw/component/form');

var self = new Class({

	extends: Component,
	_aReportSchedule : {},
	_oReport : {},

	construct   : function() {
		this.CONFIG = Object.extend({
			// Load Existing?
			iReportId : {}
		}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-report-edit');
	},

	_buildUI: function () {
		this._oForm = new Form({onsubmit: this._save.bind(this, null)},
			new Hidden({
				sName : 'id'
			}),
			H.fieldset({'class': 'flex-page-report-edit-details'},
				H.label('Name'),
				this._oName = new Text({
					sExtraClass	: 'flex-page-report-edit-details-name',
					sName 		: 'name',
					sLabel		: 'Name',
					mMandatory	: true,
					fnValidate	: function(oControl) {
						if(oControl.getValue().length>256) {
							throw new Error("Max length is 256 characters");
						}
						return true;
					}
				}),
				H.label('Summary'),
				this._oSummary = new Textarea({
					sExtraClass	: 'flex-page-report-edit-details-summary',
					sName 		: 'summary',
					sLabel		: 'Name',
					mMandatory	: true,
					fnValidate	: function(oControl) {
						if(oControl.getValue().length>512) {
							throw new Error("Max length is 512 characters");
						}
						return true;
					}
				}),
				H.label('SQL Query'),
				this._oQuery = new Textarea({
					sExtraClass	: 'flex-page-report-edit-details-query',
					sName 		: 'query',
					sLabel		: 'Name',
					mMandatory	: true,
					fnValidate	: function(oControl) {
						if(oControl.getValue().length>10000) {
							throw new Error("Max length is 10000 characters");
						}
						return true;
					}
				}),
				H.label('Category'),
				this._oReportCategory = new Select({
						sName 		: 'category',
						sLabel		: 'Category',
						mMandatory	: true,
						fnPopulate	: this._populateReportCategory.bind(this)
					}),
				H.label('Report Employee'),
				H.span(
					this._oEmployeeContainer = H.div({style: 'max-height: 150px; max-width: 200px; overflow-y: scroll; overflow-x: hidden;'})
				),
				H.fieldset({class: 'flex-page-report-edit-buttonset'},
					/*
					H.button({type: 'button', name: 'editSchedule'},
						H.img({src: '/admin/img/template/options.png','width':'16','height':'16'}),
						H.span('Edit Schedules')
					).observe('click', this._editSchedules.bind(this, null)),
					*/
					H.button({type: 'button', name: 'editConstraint'},
						H.img({src: '/admin/img/template/options.png','width':'16','height':'16'}),
						H.span('Edit Constraints')
					).observe('click', this._editConstraints.bind(this, null)),
					H.button({type: 'button', name: 'save'},
						H.img({src: '/admin/img/template/approve.png','width':'16','height':'16'}),
						H.span('Save')
					).observe('click',this._save.bind(this, null)),
					H.button({type: 'button', name: 'cancel'},
						H.img({src: '/admin/img/template/decline.png','width':'16','height':'16'}),
						H.span('Cancel')
					).observe('click',this._cancel.bind(this, null))
				)
			)
		);

		this.NODE = this._oForm.getNode();
		// Add to DOM
		$$('.flex-page')[0].appendChild(this.NODE);
	},

	_syncUI: function () {
		if (!this._bInitialised || !this._bReady) {
			if (this.get('iReportId')) {
				// Load existing
				this._loadReport(function(){});
			} else {
				// Create new
				jhr('Report', 'getEmployees', {arguments: []}).then(
					function success(request) {
						var response = request.parseJSONResponse();
						response.employees.forEach(function (employee) {
							this._oEmployeeContainer.appendChild(
								H.div({class: 'flex-component-report-add-reportemployee-div-container'},
									H.input({style: 'float:left ;', type: 'checkbox', name: 'report_employee[]', value: employee.Id}),
									H.label({class: 'flex-component-report-add-reportemployee-div-container-label'},employee.FirstName + ' ' + employee.LastName)
								)
							);
						}.bind(this));
					}.bind(this),
					function (error) {
						// TODO: Handle Error
					}
				);
			}
			this._onReady();
		}
	},

	_populateReportCategory : function(fnCallback, oEvent, oTest) {
		if (!oEvent) {
			// Request
			var oReq = new XHRRequest('reflex_json.php/Report_Category/getAll', this._populateReportCategory.bind(this, fnCallback));
			oReq.send();
		} else {
			// Got response
			var oResponse	= oEvent.getData();
			var aData	= oResponse.getData();
			var aOptions = [];

			for(var i in aData.report_categories){
				if(aData.report_categories.hasOwnProperty(i)){
					aOptions.push(
						H.option({value: aData.report_categories[i].id},
							aData.report_categories[i].name
						)
					);
				}
			}
			if(fnCallback) {
				fnCallback(aOptions);
			}
		}
	},

	_loadReport : function(fnCallback, oXHREvent) {
		var oData = {
			iReportId : this.get('iReportId')
		};
		new Ajax.Request('/admin/reflex_json.php/Report/getForId', {
			method		: 'post',
			contentType	: 'application/x-www-form-urlencoded',
			postBody	: "json="+encodeURIComponent(JSON.stringify([oData])),
			onSuccess: function (oResponse){
				var oServerResponse = JSON.parse(oResponse.responseText);
				// Load report
				this._oForm.control('id').set('mValue', oServerResponse.aReport.id);
				this._oForm.control('name').set('mValue', oServerResponse.aReport.name);
				this._oForm.control('summary').set('mValue', oServerResponse.aReport.summary);
				this._oForm.control('query').set('mValue', oServerResponse.aReport.query);
				this._oForm.control('category').set('mValue', oServerResponse.aReport.report_category_id);
				// Save report
				this._oReport = oServerResponse.aReport;

				// Load Schedules
				this._aReportSchedule = oServerResponse.aReportSchedule;

				// Load Constraints
				this._aReportConstraint = oServerResponse.aReportConstraint;

				// Load Employees
				oServerResponse.aEmployee.forEach(function (oEmployee) {
					this._oEmployeeContainer.appendChild(
						H.div({class: 'flex-component-report-add-reportemployee-div-container'},
							H.label({class: 'flex-component-report-add-reportemployee-div-container-label'},oEmployee.FirstName + ' ' + oEmployee.LastName),
							new Checkbox({
								bChecked	: (oEmployee.report_id) ? true : false,
								sName 		: 'report_employee[]',
								sLabel		: 'Report Employee',
								mValue		: oEmployee.Id,
								sExtraClass	: 'flex-page-report-edit-report-employee'
							})
						)
					);
				}.bind(this));
			}.bind(this)
		});
	},

	_getSelectedReportEmployees : function() {
		var aElements = this._oEmployeeContainer.select('input:checked');
		var aEmployee = [];
		for(var i in aElements) {
			if(aElements.hasOwnProperty(i)) {
				var oElement = aElements[i];
				var iEmployeeId = parseInt(oElement.value);
				aEmployee.push(iEmployeeId);
			}
		}
		return aEmployee;
	},

	_save: function() {
		var bValidation = this._oForm.validate();
		if(bValidation) {
			var oData = {
				"name" : this._oName.getValue(),
				"summary" : this._oSummary.getValue(),
				"query" : this._oQuery.getValue(),
				"report" : this._oReport,
				"constraint" : this._aReportConstraint,
				//"schedule" : this._aReportSchedule,
				"category" : this._oReportCategory.getValue(),
				"report_employee" : this._getSelectedReportEmployees()
			}
			new Ajax.Request('/admin/reflex_json.php/Report/save', {
				method		: 'post',
				contentType	: 'application/x-www-form-urlencoded',
				postBody	: "json="+encodeURIComponent(JSON.stringify([oData])),
				onSuccess: function (oResponse){
					var oServerResponse = JSON.parse(oResponse.responseText);
					this.fire('complete');
				}.bind(this)
			});
		}
	},

	_cancel : function(event) {
		this.fire('cancel');
	},

	_editConstraints : function() {
		var oPopup = Constraint.createAsPopup({
				aReportConstraint : this._aReportConstraint,
				oncomplete      : function(oData) {
					this._aReportConstraint = oData.oEvent.oTarget.get('aReportConstraint');
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
/*
	_editSchedules : function() {
		var oPopup = Schedule.createAsPopup({
				aReportSchedule : this._aReportSchedule,
				oncomplete : function(oData) {
						this._aReportSchedule = oData.oEvent.oTarget.get('aReportSchedule');
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
*/
	statics : {
		createAsPopup : function() {
			var oComponent      = self.applyAsConstructor($A(arguments)),
			oPopup = new Popup({
					sExtraClass     : 'flex-page-report-edit-popup',
					sTitle          : 'Edit Report',
					sIconURI        : './img/template/pencil.png',
					bCloseButton    : true
				}, oComponent.getNode()
			);
			oPopup.set()
			return oPopup;
		},
		createAsAddPopup : function() {
			var oComponent      = self.applyAsConstructor($A(arguments)),
			oPopup = new Popup({
					sExtraClass     : 'flex-page-report-edit-popup',
					sTitle          : 'Add Report',
					sIconURI        : './img/template/new.png',
					bCloseButton    : true
				}, oComponent.getNode()
			);
			oPopup.set()
			return oPopup;
		}
	}
});

return self;