"use strict";

var H = require('fw/dom/factory'), // HTML
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
		this._oForm = new Form(//{onsubmit: this._save.bind(this, null)},
			new Hidden({
				sName : 'id',
				mValue: this.get('iReportId')
			}),
			new Hidden({
				sName : 'selectedDeliveryEmployees'
			}),
			H.fieldset({'class': 'flex-page-report-run-details'},
				H.div({role:'group', class: 'flex-page-report-run-details-deliveryformat'},
					H.span({class: 'flex-page-report-run-details-deliveryformat-label'}, 'Delivery Format'),
					this._oDeliveryFormatContainer = H.div({class: 'flex-page-report-run-details-deliveryformat-container'})
				),
				H.div({role:'group', class: 'flex-page-report-run-details-deliverymethod'},
					H.span({class: 'flex-page-report-run-details-deliverymethod-label'}, 'Delivery Method'),
					this._oDeliveryMethodContainer = H.div({class: 'flex-page-report-run-details-deliverymethod-container'})
				),
				this._oDeliveryEmployeeContainer = H.div({role: 'group', class: 'flex-page-report-run-details-deliveryemployee'},
					H.span({class: 'flex-page-report-run-details-deliveryemployee-label'}, 'Deliver To'),
					this._oEmployeeContainer = H.div({class: 'flex-page-report-run-details-deliveryemployee-controlset'})
				)
			),
			H.fieldset({'class': 'flex-page-report-run-details-constraints'},
				this._oConstraintContainer = H.div()
			),
			H.div({class: 'flex-page-report-run-buttonset'},
				H.button({type: 'button', name: 'run'}, 'Run').observe('click',this._executeReport.bind(this, null)),
				H.button({type: 'button', name: 'cancel'}, 'Cancel').observe('click',this._cancel.bind(this, null))
			)
		);
		this.NODE = this._oForm.getNode();
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

	_showDeliveryEmployees: function(sReportDeliveryName) {
		if(sReportDeliveryName == "Email") {
			$('.flex-page-report-run-details-deliveryemployee').show();
			this._loadDeliveryEmployees();
		}
		else {
			$('.flex-page-report-run-details-deliveryemployee').hide();
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
				// TODO: Handle Error
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
				// TODO: Handle Error
			}
		);
	},

	_loadDeliveryEmployees: function() {
		jhr('Report', 'getEmployees', {arguments: []}).then(
			function success(request) {
				var response = request.parseJSONResponse();
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
				// TODO: Handle Error
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
			onSuccess: function (oResponse){
				var oServerResponse = JSON.parse(oResponse.responseText);
				if (oServerResponse.length) {
					$('.flex-page-report-run-details-constraints').show();
				}
				for (var i = 0;i < oServerResponse.length; i++) {
					//Check for type here

					if (oServerResponse[i]['component_type'] == "Text") {
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
						}
						else {
							this._oConstraintContainer.appendChild(
								H.label({class: 'flex-page-report-run-details-constraintContainer'},
									H.span({class: 'flex-page-report-run-details-constraintContainer-label'}, oServerResponse[i]['name']),
									new Text({
										sExtraClass	: 'flex-page-report-run-details-' + oServerResponse[i]['name'].toLowerCase(),
										sName		: oServerResponse[i]['name'],
										sLabel		: oServerResponse[i]['name'],
										mMandatory	: true,
										fnValidate	: function(oControl) {
											if(!preg_match(oServerResponse['validation_regex'], oControl.getValue())) {
												throw new Error("Pattern validation failed");
											}
											return true;
										}
									})
								)
							);
						}
					}
					else if (oServerResponse[i]['component_type'] == "Select") {
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

					}
					else if (oServerResponse[i]['component_type'] == "Date") {	
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
					}
					else if (oServerResponse[i]['component_type'] == "DateTime") {
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

	_executeReport: function() {
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
			this._oForm.control('selectedDeliveryEmployees').set('mValue', this._getSelectedDeliveryEmployees());
			
			jhr('Report', 'generate', {arguments: this._oForm.getData()}).then(
				function success(request) {
					var oResponse = request.parseJSONResponse();
					this._executeReponse(oResponse);
					this.fire('complete');
				}.bind(this),
				function (error) {
					// TODO: Handle Error
				}
			);
		}
	},

	_executeReponse	: function(oResponse)
	{
		if (oResponse.bSuccess)
		{
			if (oResponse.bIsEmail)
			{
				new Alert(oResponse.sMessage);
			}
			else {
				window.location = 'reflex.php/Report/Download/?sFileName=' + encodeURIComponent(oResponse.sFilename) + '&iCSV=' + (oResponse.sFilename.match(/\.csv/) ? 1 : 0);
			}
		}
		else
		{
			// Error occurred in execution
			new Alert(oResponse.sMessage);
		}
	},

	_cancel : function(event) {
		this.fire('cancel');
	},

	
	statics : {
		createAsPopup : function() {
			var oComponent = self.applyAsConstructor($A(arguments)),
			oPopup = new Popup({
					sExtraClass     : 'flex-page-report-run-popup',
					sTitle          : 'Run Report',
					sIconURI        : './img/template/play.png',
					bCloseButton    : true
				}, oComponent.getNode()
			);
			return oPopup;
		}
	}

});

return self;