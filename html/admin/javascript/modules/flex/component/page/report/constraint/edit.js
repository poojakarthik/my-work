"use strict";
var H = require('fw/dom/factory'), // HTML
	Class = require('fw/class'),
	Component = require('fw/component'),
	Alert = require('fw/component/popup/alert'),
	XHRRequest = require('fw/xhrrequest'),
	jhr = require('xhr/json-handler'),
	Popup = require('fw/component/popup'),
	Form = require('fw/component/form'),
	Text = require('fw/component/control/text'),
	Select = require('fw/component/control/select'),
	Hidden = require('fw/component/control/hidden'),
	Textarea = require('fw/component/control/textarea'),
	jsonForm = require('json-form');

var self = new Class({
	'extends' : Component,
	_iReportConstraintCount : 0,

	construct : function() {
		this.CONFIG = Object.extend({
			aReportConstraint : {}
		}, this.CONFIG || {});
		// Call the parent constructor
		this._super.apply(this, arguments);
		// Class specific to our component
		this.NODE.addClassName('flex-page-report-constraint-edit');
	},

	_buildUI : function() {
		this._oForm = new Form({onsubmit: this._save.bind(this, null)},
			H.fieldset({'class': 'flex-page-report-constraint-edit-details'},
				H.label({class: 'flex-page-report-constraint-edit-details-name'},
					H.span({class: 'flex-page-report-constraint-edit-details-name-label'}, 'Alias'),
					new Text({
						sName       : 'name',
						sLabel      : 'Name',
						mMandatory  : true,
						fnValidate  : function(oControl) {
							if(oControl.getValue().length>256) {
								throw new Error("Max length is 256 characters");
							}
							if(/^[A-Z,0-9,-, ]$/.test(oControl.getValue()))	{
								throw new Error("Please use lowercase alphabets only");
							}
							return true;
						}
					})
				),
				H.label({class: 'flex-page-report-constraint-edit-details-type'}, 
					H.span({class: 'flex-page-report-constraint-edit-details-type-label'}, 'Type'),
					this._oReportConstraintType = new Select({
						sName       : 'report_constraint_type_id',
						sLabel      : 'Constraint Type',
						mMandatory  : true,
						fnPopulate  : this._populateConstraintTypes.bind(this)
					})
				),
				this._oSourceQueryContainer = H.div(
					H.label({class: 'flex-page-report-constraint-edit-details-query'},
						H.span({class: 'flex-page-report-constraint-edit-details-query-label'}, 'Source Query'),
						this._oReportConstraintSourceQuery = new Textarea({
							sName       : 'source_query',
							sLabel      : 'Source Query',
							mMandatory  : false,
							fnValidate  : function(oControl) {
								if (oControl.getValue().length>10000) {
									throw new Error("Max length is 10000 characters");
								}
								else if (/^update|delete|alter|insert|drop/.test(oControl.getValue().toLowerCase())) {
									throw new Error("Only Select statements are allowed");
								}
								return true;
							}.bind(this)
						})
					)
				),
				this._oValidationContainer = H.div(
					H.label({class: 'flex-page-report-constraint-edit-details-validation'}, 
						H.span({class: 'flex-page-report-constraint-edit-details-validation-label'}, 'Regex Pattern'),
						new Text({
							sName       : 'validation_regex',
							sLabel      : 'Regex Pattern',
							mMandatory  : false,
							fnValidate  : function(oControl) {
								if(oControl.getValue().length>200) {
									throw new Error("Max length is 200 characters");
								}
								return true;
							}
						})
					)
				),
				this._oPlaceholderContainer = H.div(
					H.label({class: 'flex-page-report-constraint-edit-details-placeholder'}, 
						H.span({class: 'flex-page-report-constraint-edit-details-placeholder-label'}, 'Hint Text'),
						new Text({
							sName       : 'placeholder',
							sLabel      : 'Hint Text',
							mMandatory  : false,
							fnValidate  : function(oControl) {
								if(oControl.getValue().length>100) {
									throw new Error("Max length is 100 characters");
								}
								return true;
							}
						})
					)
				)				
			),
			H.fieldset({class: 'flex-page-report-constraint-edit-buttonset'},
				H.button({type: 'button', name: 'add', onclick: this._add.bind(this)}, 'Add'),
				H.button({type: 'button', name: 'cancel', onclick: this._save.bind(this)}, 'Close')
			),
			this._oConstraint = new Form({onsubmit: this._save.bind(this, null)},
				H.table({class: 'reflex highlight-rows'},
					H.caption(
						H.div({id: 'caption_bar', class: 'caption_bar'},
							H.div({id: "caption_title", class: "caption_title"}, 'Constraint List'),
							H.div({id: 'caption_options', class: 'caption_options'})
						)
					),
					H.thead(
						H.tr({class: 'First'},
							H.th('Name'),
							H.th('Constraint Type'),
							H.th('Action')
						)
					),
					this._oConstraintList = H.tbody({class: 'flex-component-report-constraint-list'})
				)
			)
		);
		this._oReportConstraintType.observe('change', this._onConstraintTypeChange.bind(this));
		this.NODE = this._oForm.getNode();
	},

	_syncUI : function() {
		this._oSourceQueryContainer.hide();
		this._oValidationContainer.hide();
		this._oPlaceholderContainer.hide();
		if (!this._bInitialised || !this._onReady) {
			if(this.get('aReportConstraint')) {
				this._getConstraintTypes.bind(this, this._populateReportConstraint(this.get('aReportConstraint')));
			}
			this._onReady();

		}
	},

	_onConstraintTypeChange: function(){
		
		switch(parseInt(this._oForm.control('report_constraint_type_id').getValue())) {
			case self.REPORT_CONSTRAINT_TYPE_FREETEXT:
				this._oSourceQueryContainer.hide();
				this._oValidationContainer.show();
				this._oPlaceholderContainer.show();
				this._oReportConstraintSourceQuery.set('mMandatory', false);
				break
			case self.REPORT_CONSTRAINT_TYPE_DATABASELIST:
				this._oReportConstraintSourceQuery.set('mMandatory', true);
				this._oSourceQueryContainer.show();
				this._oValidationContainer.hide();
				this._oPlaceholderContainer.hide();
				break;
			case self.REPORT_CONSTRAINT_TYPE_DATE:
			case self.REPORT_CONSTRAINT_TYPE_DATETIME:
			default:
				break;
		}
	},

	_setFrequencyTypesPropertyForArray : function(aData) {
		var aFrequencyTypes = {};
		for (var i in aData){
			if(aData.hasOwnProperty(i)){
				// Save Frequency Type
				var iId = aData[i].id;
				var oFrequencyType = aData[i];
				aFrequencyTypes[iId] = oFrequencyType;
			}
		}
		this._aFrequencyTypes.push(aFrequencyTypes);
	},

	_getConstraintTypes : function(fnCallback, oEvent, oTest) {
		if (!oEvent) {
			// Request
			var oReq = new XHRRequest('reflex_json.php/Report_Constraint_Type/getAll', this._populateConstraintTypes.bind(this, fnCallback));
			oReq.send();
		} else {
			// Got response
			var oResponse   = oEvent.getData();
			var aData   = oResponse.getData();
			this._setConstraintTypesPropertyForArray(aData.report_constraint_types);
			if(fnCallback) {
				fnCallback(mData);
			}
		}
	},

	_populateConstraintTypes : function(fnCallback, oEvent, oTest) {
		if (!oEvent) {
			// Request
			var oReq = new XHRRequest('reflex_json.php/Report_Constraint_Type/getAll', this._populateConstraintTypes.bind(this, fnCallback));
			oReq.send();
		} else {
			// Got response
			var oResponse   = oEvent.getData();
			var aData   = oResponse.getData();
			var aOptions = [];

			for (var i in aData.report_constraint_types){
				if(aData.report_constraint_types.hasOwnProperty(i)){
					aOptions.push(
						H.option({value: aData.report_constraint_types[i].id},
							aData.report_constraint_types[i].name
						)
					);
				}
			}
			if (fnCallback) {
				fnCallback(aOptions);
			}
		}
	},

	_populateReportConstraint : function(aData) {
		for (var iKey=0; iKey<aData.length; iKey++) {
			var oReportConstraint = aData[iKey];
			this._oConstraintList.appendChild(
				H.tr({class: 'flex-component-report-constraint-list', id: 'flex-component-report-constraint-list-row-'+oReportConstraint.id},
					H.td(
						new Hidden({sName: 'constraint['+this._iConstraintCount+'].name', mValue: oReportConstraint.name}),
						H.span(oReportConstraint.name)
					),
					H.td(
						new Hidden({sName: 'constraint['+this._iConstraintCount+'].report_constraint_type_id', mValue: oReportConstraint.report_constraint_type_id}),
						H.span(oReportConstraint.constraint_name)
					),
					H.td(
						H.button({type: 'button', name: 'remove'}, 'Remove').observe('click', function(){ this.parentElement.parentElement.remove(); })
					)
				)
			);
			this._iConstraintCount++;
		};
	},

	_add : function() {
		var bValidation = this._oForm.validate();
		if (bValidation) {
			
			if (this._oForm.control('report_constraint_type_id').getValue() == self.REPORT_CONSTRAINT_TYPE_DATABASELIST) {
				if (this._oForm.control('source_query').getValue() == "") {
					new Alert('Source query cannnot be empty');
					return;
				}
				else {
					jhr('Report_Constraint', 'validateSourceQuery', {arguments: [this._oForm.control('source_query').getValue()]}).then(
						function success(request) {
							var response = request.parseJSONResponse();
							if (!response.bSuccess) {
								new Alert(response.sMessage);
								return;
							}
						}.bind(this),
						function (error) {
							var response = error.parseJSONResponse();
							new Alert(response.sMessage);
							return;
						}
					);
				}
			}
			this._oConstraintList.appendChild(
				H.tr({class: 'flex-component-report-constraint-list'},
					H.td(
						H.span(this._oForm.control('name').getValue())
					),
					H.td(
						H.span(this._oForm.control('report_constraint_type_id').getNode().select('select :selected').first().innerHTML)
					),
					H.td(
						new Hidden({sName: 'constraint['+this._iConstraintCount+'].placeholder', mValue: this._oForm.control('placeholder').getValue()}),
						new Hidden({sName: 'constraint['+this._iConstraintCount+'].validation_regex', mValue: this._oForm.control('validation_regex').getValue()}),
						new Hidden({sName: 'constraint['+this._iConstraintCount+'].source_query', mValue: this._oForm.control('source_query').getValue()}),
						new Hidden({sName: 'constraint['+this._iConstraintCount+'].name', mValue: this._oForm.control('name').getValue()}),
						new Hidden({sName: 'constraint['+this._iConstraintCount+'].report_constraint_type_id', mValue: this._oForm.control('report_constraint_type_id').getValue()}),
						H.button({type: 'button', name: 'remove'}, 'Remove').observe('click', function(){ this.parentElement.parentElement.remove(); })
					)
				)
			);
			this._iConstraintCount++;
		}
	},
	_cancel : function(event) {
		this.fire('complete');
	},

	_save : function(event) {
		var oList = this._oConstraint.select('.flex-component-report-constraint-list tr');
		var aResult = [];
		for (var i in oList) {
			if (oList.hasOwnProperty(i)) {
				var aInputData = oList[i].select('.fw-control > input');
				var oData = {};
				for (var x in aInputData) {
					if(aInputData.hasOwnProperty(x)) {
						var oElement = aInputData[x];
						var sName = oElement.name;
						var sValue = oElement.value;
						var aName = sName.split('.');
						var sShortName = aName[1];
						oData[sShortName] = sValue;
					}
				}
				aResult.push(oData);
			}
			this._iConstraintCount++;
		}
		this.set('aReportConstraint', aResult);
		this.fire('complete');
	},

	statics : {
		REPORT_CONSTRAINT_TYPE_FREETEXT: 1,
		REPORT_CONSTRAINT_TYPE_DATABASELIST: 2,
		REPORT_CONSTRAINT_TYPE_DATE: 3,
		REPORT_CONSTRAINT_TYPE_DATETIME: 4,

		createAsPopup : function() {
		var oComponent = self.applyAsConstructor($A(arguments)),
		oPopup = new Popup({
			sExtraClass : 'css-class-name',
			sTitle : 'Edit Report Constraints',
			sIconURI : './img/template/pencil.png',
			bCloseButton : false,
			bModal : true
			},
			oComponent.getNode());
			return oPopup;
		}
	}
});

return self;
