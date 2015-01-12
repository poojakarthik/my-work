"use strict";

var	H = require('fw/dom/factory'), // HTML
	Class = require('fw/class'),
	Component = require('fw/component'),
	jhr	= require('xhr/json-handler'),
	jsonForm = require('json-form'),
	Popup = require('fw/component/popup'),
	Alert = require('fw/component/popup/alert'),
	Hidden = require('fw/component/control/hidden'),
	Select = require('fw/component/control/select'),
	Checkbox = require('fw/component/control/checkbox'),
	Text = require('fw/component/control/text'),
	Radio = require('fw/component/control/radio'),
	Datetime = require('fw/component/control/datetime'),
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
		this.NODE.addClassName('flex-page-report-run');
	},

	_buildUI: function () {
		this._oForm = new Form(
			new Hidden({
				sName : 'id',
				mValue: this.get('iReportId')
			}),
			new Hidden({
				sName : 'selectedDeliveryEmployees'
			}),
			this._obody = H.div(
				H.fieldset({class: 'flex-page-report-run-details'},
					H.div({role:'group', class: 'flex-page-report-run-details-deliveryformat'},
						H.span({class: 'flex-page-report-run-details-deliveryformat-label'}, 'Delivery Format'),
						this._oDeliveryFormatContainer = H.div({class: 'flex-page-report-run-details-deliveryformat-container'})
					),
					H.div({role:'group', class: 'flex-page-report-run-details-deliverymethod'},
						H.span({class: 'flex-page-report-run-details-deliverymethod-label'}, 'Delivery Method'),
						this._oDeliveryMethodContainer = H.div({class: 'flex-page-report-run-details-deliverymethod-container'})
					)
				),
				H.fieldset({class: 'flex-page-report-run-deliveryemployee'},
					this._oDeliveryEmployeeContainer = H.div({role: 'group', class: 'flex-page-report-run-details-deliveryemployee'},
						H.span({class: 'flex-page-report-run-details-deliveryemployee-label'}, 'Deliver To'),
						H.div(
							H.div({class: 'flex-page-report-run-details-deliveryemployee-filter'},
								this._oNameFilter = new Text({
									sName		: 'filter',
									sLabel		: 'Name Filter',
									sPlaceholder: 'Filter by name',
									mMandatory	: false
								})
							),
							this._oEmployeeContainer = H.div({class: 'flex-page-report-run-details-deliveryemployee-controlset'})
						)
					)
				),
				H.fieldset({class: 'flex-page-report-run-details-constraints'},
					this._oConstraintContainer = H.div()
				),
				H.div({class: 'flex-page-report-run-buttonset'},
					H.button({type: 'button', name: 'run'}, 'Run').observe('click',this._executeReport.bind(this, null)),
					H.button({type: 'button', name: 'cancel'}, 'Cancel').observe('click',this._cancel.bind(this, null))
				)
			),
			this._oloading = H.div({'class': 'reflex-loading-image'},
				H.div('Executing Report'),
				H.div('Please wait.')
			)
		);
		this.NODE = this._oForm.getNode();
		this._oloading.hide();
		this._oNameFilter.observe('change', this._filterDeliveryEmployees.bind(this));
	},

	_syncUI: function () {
		this._oDeliveryEmployeeContainer.hide();
		if (!this._bInitialised || !this._bReady) {
			if (this.get('iReportId')) {
				// Get Report Constraints
				this._loadConstraints();
				this._oForm.control('id').set('mValue', this.get('iReportId'));
				this._loadDeliveryFormats();
				this._loadDeliveryMethods();

			}
			this._onReady();
		}
	},

	_filterDeliveryEmployees: function() {
		var aEmployeeElement = $('.flex-component-report-run-deliveryemployee-div-container-label');
		for (var i = 0; i < aEmployeeElement.length; i++) {
			var oRegExp = new RegExp(this._oNameFilter.getValue(), 'i');
			if (this._oNameFilter.getValue() !== "" && !aEmployeeElement[i].innerHTML.match(oRegExp)) {
				aEmployeeElement[i].parentNode.hide();
			} else {
				aEmployeeElement[i].parentNode.show();
			}
		}
	},

	_showDeliveryEmployees: function(sReportDeliveryName) {
		if(sReportDeliveryName == "Email") {
			$('.flex-page-report-run-deliveryemployee').show();
			$('.flex-page-report-run-details-deliveryemployee').show();
			this._loadDeliveryEmployees();
		} else {
			$('.flex-page-report-run-details-deliveryemployee').hide();
			$('.flex-page-report-run-deliveryemployee').hide();
		}
	},

	_loadDeliveryFormats: function() {
		jhr('Report_Delivery_Format', 'getAll', {arguments: []}).then(
			function success(request) {
				var response = request.parseJSONResponse();
				for (var i = 0; i < response.report_delivery_formats.length; i++) {
					var oReportDeliveryFormat = response.report_delivery_formats[i];
					this._oDeliveryFormatContainer.appendChild(
						H.label({title: oReportDeliveryFormat.name},
							new Radio({
								sName		: 'delivery_format',
								sLabel		: oReportDeliveryFormat.name,
								mMandatory	: false,
								mValue		: oReportDeliveryFormat.id
							}),
							H.span(oReportDeliveryFormat.name)
						)
					);
				}
			}.bind(this),
			function (error) {

			}
		);
	},

	_loadDeliveryMethods: function() {
		jhr('Report_Delivery_Method', 'getAll', {arguments: []}).then(
			function success(request) {
				var response = request.parseJSONResponse();
				for (var i = 0; i < response.report_delivery_methods.length; i++) {
					var oReportDeliveryMethod = response.report_delivery_methods[i];
					this._oDeliveryMethodContainer.appendChild(
						H.label({title: oReportDeliveryMethod.name},
							new Radio({
								sName		: 'delivery_method',
								sLabel		: oReportDeliveryMethod.name,
								mMandatory	: false,
								mValue		: oReportDeliveryMethod.id
							}),
							H.span(oReportDeliveryMethod.name)
						).observe('click', this._showDeliveryEmployees.bind(this, oReportDeliveryMethod.name))
					);

				}
			}.bind(this),
			function (error) {

			}
		);
	},

	_loadDeliveryEmployees: function() {
		jhr('Report', 'getEmployees', {arguments: []}).then(
			function success(request) {
				var response = request.parseJSONResponse();
				while (this._oEmployeeContainer.firstChild) {
					this._oEmployeeContainer.removeChild(this._oEmployeeContainer.firstChild);
				}
				response.employees.forEach(function (oEmployee) {
					this._oEmployeeContainer.appendChild(
						H.label({class: 'flex-component-report-run-deliveryemployee-div-container'},
							H.span({class: 'flex-component-report-run-deliveryemployee-div-container-label'},oEmployee.FirstName + ' ' + oEmployee.LastName),
							new Checkbox({
								bChecked	: (oEmployee.report_id) ? true : false,
								sName		: 'delivery_employee[]',
								sLabel		: 'Delivery Employee',
								mValue		: oEmployee.Id,
								sExtraClass	: 'flex-page-report-run-delivery-employee'
							})
						)
					);
				}.bind(this));
			}.bind(this),
			function (error) {

			}
		);
	},

	_loadConstraints:function() {

		var oData = {
			iReportId : this.get('iReportId')
		};
		new Ajax.Request('/admin/reflex_json.php/Report_Constraint/getForReportId', {
			method		: 'post',
			contentType	: 'application/x-www-form-urlencoded',
			postBody	: "json="+encodeURIComponent(JSON.stringify([oData])),
			onSuccess: function (oResponse) {
				var oServerResponse = JSON.parse(oResponse.responseText);
				if (oServerResponse.length) {
					$('.flex-page-report-run-details-constraints').show();
				}
				for (var i = 0;i < oServerResponse.length; i++) {
					//Check for type here

					if (oServerResponse[i]['component_type'] == self.REPORT_CONSTRAINT_TYPE_FREETEXT) {
						if (oServerResponse[i]['validation_regex'] == "null") {
							this._oConstraintContainer.appendChild(
								H.label({class: 'flex-page-report-run-details-constraintContainer'},
									H.span({class: 'flex-page-report-run-details-constraintContainer-label'}, oServerResponse[i]['name']),
									new Text({
										sExtraClass	: 'flex-page-report-run-details-' + oServerResponse[i]['name'].toLowerCase(),
										sName		: oServerResponse[i]['name'],
										sLabel		: oServerResponse[i]['name'],
										mMandatory	: true
									})
								)
							);
						} else {
							this._oConstraintContainer.appendChild(
								H.label({class: 'flex-page-report-run-details-constraintContainer'},
									H.span({class: 'flex-page-report-run-details-constraintContainer-label'}, oServerResponse[i]['name']),
									new Text({
										sExtraClass	: 'flex-page-report-run-details-' + oServerResponse[i]['name'].toLowerCase(),
										sName		: oServerResponse[i]['name'],
										sLabel		: oServerResponse[i]['name'],
										mMandatory	: true,
										fnValidate	: function(oControl) {
											if(!oControl.getValue().match(oServerResponse['validation_regex'])) {
												throw new Error("Pattern validation failed");
											}
											return true;
										}
									})
								)
							);
						}
					} else if (oServerResponse[i]['component_type'] == self.REPORT_CONSTRAINT_TYPE_DATABASELIST) {
						//debugger;
						this._oConstraintContainer.appendChild(
							H.label({class: 'flex-page-report-run-details-constraintContainer'},
								H.span({class: 'flex-page-report-run-details-constraintContainer-label'}, oServerResponse[i]['name']),
								new Select({
									sExtraClass	: 'flex-page-report-run-details-' + oServerResponse[i]['name'].toLowerCase(),
									sName		: oServerResponse[i]['name'],
									sLabel		: oServerResponse[i]['name'],
									mMandatory	: true,
									fnPopulate : function(fnCallback) {
										var aOptions = [];
										for (var j = 0; j < oServerResponse[i]['source_data'].length; j++) {
											aOptions.push(
												H.option({value: oServerResponse[i]['source_data'][j]['value']},
													oServerResponse[i]['source_data'][j]['label']
												)
											);
										}
										fnCallback(aOptions);
									}
								})
							)
						);
					} else if (oServerResponse[i]['component_type'] == self.REPORT_CONSTRAINT_TYPE_DATE) {
						this._oConstraintContainer.appendChild(
							H.label({class: 'flex-page-report-run-details-constraintContainer'},
								H.span({class: 'flex-page-report-run-details-constraintContainer-label'}, oServerResponse[i]['name']),
								new Datetime({
									bTimePicker	: false,
									sName		: oServerResponse[i]['name'],
									sLabel		: oServerResponse[i]['name'],
									mMandatory	: true,
									sExtraClass	: 'flex-page-report-run-details-constraintContainer-datetime'
								})
							)
						);
					} else if (oServerResponse[i]['component_type'] == self.REPORT_CONSTRAINT_TYPE_DATETIME) {
						this._oConstraintContainer.appendChild(
							H.label({class: 'flex-page-report-run-details-constraintContainer'},
								H.span({class: 'flex-page-report-run-details-constraintContainer-label'}, oServerResponse[i]['name']),
								new Datetime({
									bTimePicker	: true,
									sName		: oServerResponse[i]['name'],
									sLabel		: oServerResponse[i]['name'],
									mMandatory	: true,
									sExtraClass	: 'flex-page-report-run-details-constraintContainer-datetime'
								})
							)
						);
					} else if (oServerResponse[i]['component_type'] == self.REPORT_CONSTRAINT_TYPE_MULTIPLESELECTIONLIST) {
						var oConstraintListContainer = H.label({class: 'flex-page-report-run-details-constraintContainer'},
							H.span({class: 'flex-page-report-run-details-constraintContainer-label'},
								oServerResponse[i]['name']
							),
							new Hidden({
								sName : oServerResponse[i]['name'],
								mMandatory : true,
								sLabel : oServerResponse[i]['name']
							})
						);
						var oConstraintListControlset = H.div({class: 'flex-page-report-run-details-constraintContainer-controlset'});
						for (var j = 0; j < oServerResponse[i]['source_data'].length; j++) {
							oConstraintListControlset.appendChild(
								H.label({class: 'flex-page-report-run-details-constraintContainer-div-container'},
									H.span({class: 'flex-page-report-run-details-constraintContainer-controlset-label'}, oServerResponse[i]['source_data'][j]['label']),
									new Checkbox({
										bChecked	: false,
										mValue		: oServerResponse[i]['source_data'][j]['value'],
										sName		: oServerResponse[i]['name'] + 'List[]',
										sLabel		: oServerResponse[i]['name'],
										sExtraClass	: 'flex-page-report-run-details-constraintContainer-checkbox'
									})
								)
							);
						}
						oConstraintListContainer.appendChild(
							oConstraintListControlset
						);
						this._oConstraintContainer.appendChild(
							oConstraintListContainer
						);
					}
				};

			}.bind(this)
		});
	},

	_getSelectedDeliveryEmployees : function() {
		var aElements = this._oEmployeeContainer.select('input:checked');
		var aEmployee = [];
		for (var i in aElements) {
			if (aElements.hasOwnProperty(i)) {
				var oElement = aElements[i];
				var iEmployeeId = parseInt(oElement.value);
				aEmployee.push(iEmployeeId);
			}
		}
		return aEmployee;
	},

	_populateMultipleSelectionConstraints: function() {
		var aMultipleSelectionConstraints = this._oConstraintContainer.select('.flex-page-report-run-details-constraintContainer-controlset');
		if (aMultipleSelectionConstraints.length > 0) {
			for (var j in aMultipleSelectionConstraints) {
				if (aMultipleSelectionConstraints.hasOwnProperty(j)) {
					var aElements = aMultipleSelectionConstraints[j].select('input:checked');
					if (aElements.length === 0) {
						return;
					} else {
						var aConstraintValues = [];
						var sConstraintName = "";
						for (var i in aElements) {
							if (aElements.hasOwnProperty(i)) {
								var oElement = aElements[i];
								var oValue = oElement.value;
								aConstraintValues.push(oValue);
								if (sConstraintName === "") {
									sConstraintName = oElement.name.replace("List[]", "");
								}
							}
						}
						this._oForm.control(sConstraintName).set('mValue', aConstraintValues.join());
					}
				}
			}
		}
	},

	_executeReport: function() {
		this._populateMultipleSelectionConstraints();
		if (this._oForm.validate()) {
			//Add Manual Validation for Delivery Method and Delivery Format radio buttons
			if (this._oForm.control('delivery_method') == null) {
				new Alert("Please Select Delivery Method");
				return;
			}
			if (this._oForm.control('delivery_format') == null) {
				new Alert("Please Select Delivery Format");
				return;
			}
			var aSelectedEmployees = this._getSelectedDeliveryEmployees();
			if (this._oForm.control('delivery_method').getValue() == self.REPORT_DELIVERY_METHOD_EMAIL && aSelectedEmployees.length == 0) {
				new Alert("Please Select Employees To Deliver");
				return;
			} else {
				this._oForm.control('selectedDeliveryEmployees').set('mValue', aSelectedEmployees);

			}
			this._obody.hide();
			this._oloading.show();
			jhr('Report', 'generate', {arguments: this._oForm.getData()}).then(
				function success(request) {
					var oResponse = request.parseJSONResponse();
					this._executeReponse(oResponse);
					this.fire('complete');
				}.bind(this),
				function (error) {

				}
			);
		}
	},

	_executeReponse	: function(oResponse) {
		if (!oResponse) {
			new Alert("The EXCEL file is too large to be generated. Please change the delivery format to CSV instead and try again.");
			return;
		}
		if (oResponse.bSuccess) {
			if (oResponse.bIsEmail) {
				new Alert(oResponse.sMessage);
			} else {
				window.location = 'reflex.php/Report/Download/?sFileName=' + encodeURIComponent(oResponse.sFilename) + '&iCSV=' + (oResponse.sFilename.match(/\.csv/) ? 1 : 0);
			}
		} else {
			// Error occurred in execution
			new Alert(oResponse.sMessage);
		}
	},

	_cancel : function(event) {
		this.fire('cancel');
	},


	statics : {
		REPORT_CONSTRAINT_TYPE_FREETEXT: 1,
		REPORT_CONSTRAINT_TYPE_DATABASELIST: 2,
		REPORT_CONSTRAINT_TYPE_DATE: 3,
		REPORT_CONSTRAINT_TYPE_DATETIME: 4,
		REPORT_CONSTRAINT_TYPE_MULTIPLESELECTIONLIST: 5,
		REPORT_DELIVERY_METHOD_EMAIL: 2,

		createAsPopup : function() {
			var oComponent = self.applyAsConstructor($A(arguments)),
			oPopup = new Popup({
					sExtraClass     : 'flex-page-report-run-popup',
					sTitle          : 'Run Report',
					sIconURI        : './img/template/play.png',
					bCloseButton    : false
				}, oComponent.getNode()
			);
			return oPopup;
		}
	}

});

return self;