"use strict";

var H = require('fw/dom/factory'), // HTML
	Class = require('fw/class'),
	Component = require('fw/component'),
	XHRRequest = require('fw/xhrrequest'),
	jhr = require('xhr/json-handler'),
	Constraint = require('./constraint/edit'),
	jsonForm = require('json-form'),
    Popup = require('fw/component/popup'),
    Alert = require('fw/component/popup/alert'),
    Confirm = require('fw/component/popup/confirm'),
	Hidden = require('fw/component/control/hidden'),
	Checkbox = require('fw/component/control/checkbox'),
	Select = require('fw/component/control/select'),
	Text = require('fw/component/control/text'),
	Textarea = require('fw/component/control/textarea'),
	Form = require('fw/component/form');

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
		this._oReport = null;
		this._bSaveChangesTrigger = false;
		this._aReportConstraint = Array();

		this._oForm = new Form({onsubmit: this._save.bind(this, null)},
			new Hidden({
				sName : 'id'
			}),
			H.fieldset({'class': 'flex-page-report-edit-details'},
				H.label({class: 'flex-page-report-edit-details-name'},
					H.span({class: 'flex-page-report-edit-details-name-label'}, 'Name'),
					this._oName = new Text({
						sName 		: 'name',
						sLabel		: 'Name',
						mMandatory	: true,
						fnValidate	: function(oControl) {
							if(oControl.getValue().length>256) {
								throw new Error("Max length is 256 characters");
							}
							return true;
						}
					})
				),
				H.label({class: 'flex-page-report-edit-details-summary'},
					H.span({class: 'flex-page-report-edit-details-summary-label'}, 'Summary'),
					this._oSummary = new Textarea({
						sName 		: 'summary',
						sLabel		: 'Name',
						mMandatory	: true,
						fnValidate	: function(oControl) {
							if(oControl.getValue().length>512) {
								throw new Error("Max length is 512 characters");
							}
							return true;
						}
					})
				),
				H.label({class: 'flex-page-report-edit-details-query'},
					H.span({class: 'flex-page-report-edit-details-query-label'}, 'SQL Query'),
					this._oQuery = new Textarea({
						sName 		: 'query',
						sLabel		: 'Name',
						mMandatory	: true,
						fnValidate	: function(oControl) {
							if(oControl.getValue().length>10000) {
								throw new Error("Max length is 10000 characters");
							}
							return true;
						}
					})
				),
				H.label({class: 'flex-page-report-edit-details-category'},
					H.span({class: 'flex-page-report-edit-details-category-label'}, 'Category'),
					this._oReportCategory = new Select({
							sName 		: 'category',
							sLabel		: 'Category',
							mMandatory	: true,
							fnPopulate	: this._populateReportCategory.bind(this)
						})
				),
				H.div({role: 'group', class: 'flex-page-report-edit-details-reportemployee'},
					H.span({class: 'flex-page-report-edit-details-reportemployee-label'}, 'Runnable By'),
					this._oEmployeeContainer = H.div({class: 'flex-page-report-edit-details-reportemployee-controlset'})
				)
			),
			H.div({class: 'flex-page-report-edit-buttonset'},
				H.button({type: 'button', name: 'editConstraint'}, 'Edit Constraints').observe('click', this._editConstraints.bind(this, null)),
				H.button({type: 'button', name: 'save'}, 'Save').observe('click',this._save.bind(this, null)),
				H.button({type: 'button', name: 'cancel'}, 'Cancel').observe('click',this._cancel.bind(this, null))
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
				jhr('Report', 'getEmployees', {arguments: [true]}).then(
					function success(request) {
						var response = request.parseJSONResponse();
						response.employees.forEach(function (oEmployee) {
							this._oEmployeeContainer.appendChild(
								H.label({class: 'flex-component-report-add-reportemployee-div-container'},
									H.span({class: 'flex-component-report-add-reportemployee-div-container-label'},oEmployee.FirstName + ' ' + oEmployee.LastName),
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

			for (var i in aData.report_categories){
				if(aData.report_categories.hasOwnProperty(i)){
					aOptions.push(
						H.option({value: aData.report_categories[i].id},
							aData.report_categories[i].name
						)
					);
				}
			}
			if (fnCallback) {
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

				if(oServerResponse.aReportSchedule.length > 0) {
					this._oForm.control('query').set('iControlState', 3);
					$('.flex-page-report-edit-buttonset button[name="editConstraint"]').hide();
				}
				// Load Constraints
				this._aReportConstraint = oServerResponse.aReportConstraint;
				this._iReportConstraintCount = this._aReportConstraint.length;
				// Load Employees
				oServerResponse.aEmployee.forEach(function (oEmployee) {
					this._oEmployeeContainer.appendChild(
						H.label({class: 'flex-component-report-add-reportemployee-div-container'},
							H.span({class: 'flex-component-report-add-reportemployee-div-container-label'},oEmployee.FirstName + ' ' + oEmployee.LastName),
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
		for (var i in aElements) {
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
		if (bValidation) { 
			for (var i = 0; i < this._aReportConstraint.length; i++) {
				if(!this._oQuery.getValue().match("<" + this._aReportConstraint[i].name + ">")) {
					debugger;;
					new Alert("Cannot save the report. Constraint not found in query.")
					return;
				}
			}
			var oData = {
				"name" : this._oName.getValue(),
				"summary" : this._oSummary.getValue(),
				"query" : this._oQuery.getValue(),
				"report" : this._oReport,
				"constraint" : this._aReportConstraint,
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
		if(this._bSaveChangesTrigger) {
			var oConfirm = new Confirm({sIconURI : './img/template/confirm.png'}, 'There are some unsaved changes in Report Constraints. Are you sure you want cancel?');
			oConfirm.observe('yes', this._close.bind(this));
		}
		else {
			this._close();
		}			
	},

	_close : function(event) {
		this.fire('cancel');
	},

	_editConstraints : function() {
		var oPopup = Constraint.createAsPopup({
				aReportConstraint : this._aReportConstraint,
				oncomplete      : function(oData) {
					this._aReportConstraint = oData.oEvent.oTarget.get('aReportConstraint');
					if(this._iReportConstraintCount != this._aReportConstraint.length) {
						this._bSaveChangesTrigger = true;
					}
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

	statics : {
		createAsPopup : function() {
			var oComponent      = self.applyAsConstructor($A(arguments)),
			oPopup = new Popup({
					sExtraClass     : 'flex-page-report-edit-popup',
					sTitle          : 'Configure Report',
					sIconURI        : './img/template/options.png',
					bCloseButton    : false
				}, oComponent.getNode()
			);
			oPopup.set()
			return oPopup;
		},
		createAsAddPopup : function() {
			var oComponent = self.applyAsConstructor($A(arguments)),
			oPopup = new Popup({
					sExtraClass     : 'flex-page-report-edit-popup',
					sTitle          : 'Add Report',
					sIconURI        : './img/template/new.png',
					bCloseButton    : false
				}, oComponent.getNode()
			);
			oPopup.set()
			return oPopup;
		}
	}
});

return self;