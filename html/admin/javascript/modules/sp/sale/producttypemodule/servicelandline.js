var Class = require('fw/class');
var TextInputGroup = require('sp/guicomponent/textinputgroup'),
	CheckboxGroup = require('sp/guicomponent/checkboxgroup'),
	DropDown = require('sp/guicomponent/dropdown');
var Sale = require('sp/sale'),
	SaleItem = require('sp/sale/item'),
	ProductTypeModule = require('../producttypemodule'),
	LineType = require('./servicelandline/linetype'),
	Validation = require('sp/validation');
// NOTE: These requires must run later to prevent circular dependencies
var	LineType_Business,
	LineType_Residential;

var self = new Class({
	extends : ProductTypeModule,

	advancedAddressCheckbox: null,
	landlineAddressDetails: null,

	construct : function (obj) {
		// Fulfill remaining requires
		LineType_Business = require('./servicelandline/linetype/business');
		LineType_Residential = require('./servicelandline/linetype/residential');

		this.uniqueId = self.unique++;
		this._super(obj);
	},

	_getBlankDetailsObject : function () {
		var saleAccount = Sale.getInstance().getSaleAccount();
		//saleAccount.updateFromGUI();

		return {
			id: null,
			fnn: null,
			is_indial_100: null,
			has_extension_level_billing: null,
			landline_type_id: null,
			landline_type_details: null,

			landline_service_address_type_id: null,

			service_address_type_number: null,
			service_address_type_suffix: null,

			service_street_number_start: null,
			service_street_number_end: null,
			service_street_number_suffix: null,

			service_property_name: null,
			service_street_name: null,
			landline_service_street_type_id: null,
			landline_service_street_type_suffix_id: null,
			service_locality: null,
			landline_service_state_id: null,
			service_postcode: null,

			bill_name: saleAccount.getBusinessName(),
			bill_address_line_1: saleAccount.getAddressLine1(),
			bill_address_line_2: saleAccount.getAddressLine2(),
			bill_locality: saleAccount.getSuburb(),
			bill_postcode: saleAccount.getPostcode()
		};
	},

	objectHasAdvancedServiceAddressDetails : function () {
		return (
			(this.object.landline_service_address_type_id != null && this.object.landline_service_address_type_id !== '' && (parseInt(this.object.landline_service_address_type_id, 10) > 0))
			|| (this.object.service_address_type_number != null && this.object.service_address_type_number !== '')
			|| (this.object.service_address_type_suffix != null && this.object.service_address_type_suffix !== '')
			|| (this.object.service_street_number_end != null && this.object.service_street_number_end !== '')
			|| (this.object.service_street_number_suffix != null && this.object.service_street_number_suffix !== '')
			|| (this.object.service_property_name != null && this.object.service_property_name !== '')
		);
	},

	updateSummary : function (suggestion) {
		this.summaryContainer.appendChild(document.createTextNode(this.getSummary(suggestion)));
	},

	getSummary : function (suggestion) {
		return suggestion + "; Landline Phone Number: " + ((this.object.fnn == undefined || this.object.fnn == null || this.object.fnn == '') ? '[Not set]' : this.object.fnn);
	},

	advancedServiceAddressCheckboxToggle : function () {
		this.isAdvancedAddress.table.className = this.isAdvancedAddress.table.originalClassName + (this.isAdvancedAddress.checkbox.checked ? ' data-display-advanced' : ' data-display-basic');
		this.isValid();
	},

	changeStreetName : function () {
		if (this.elementGroups.service_street_name.getValue().strip()) {
			// enable the street type, and street type suffix fields
			this.elementGroups.landline_service_street_type_id.enable();
			this.elementGroups.landline_service_street_type_suffix_id.enable();//ElementGroup(this.elementGroups.landline_service_street_type_suffix_id);
		} else {
			// disable these fields
			this.elementGroups.landline_service_street_type_id.disable( true);
			this.elementGroups.landline_service_street_type_suffix_id.disable(true);
		}

		this.elementGroups.landline_service_street_type_id.isValid();
		this.elementGroups.landline_service_street_type_suffix_id.isValid();
		this.elementGroups.service_property_name.isValid();
		this.elementGroups.service_street_number_start.isValid();
		this.changeServiceStreetNumberStart();
	},

	changeServiceStreetNumberStart : function () {
		if (this.elementGroups.service_street_number_start.getValue().strip()) {
			// enable the street number end, and street number suffix fields
			this.elementGroups.service_street_number_end.enable();
			this.elementGroups.service_street_number_suffix.enable();
		} else {
			// disable these fields
			this.elementGroups.service_street_number_end.disable( true);
			this.elementGroups.service_street_number_suffix.disable(true);
		}

		this.elementGroups.service_street_number_end.isValid();
		this.elementGroups.service_street_number_suffix.isValid();
		this.elementGroups.service_street_name.isValid();
	},

	changePropertyName : function () {
		this.elementGroups.service_street_name.isValid();
		this.elementGroups.service_street_number_start.isValid();
	},

	buildGUI : function () {
		var id = 'service-mobile-table-' + (this.uniqueId);

		this.detailsContainer.innerHTML = '' +
			'<style>' +
			'table.data-display-basic .data-advanced { display: none; }' +
			'table.data-display-advanced tr.data-advanced { display: table-row; }' +
			'table.data-display-advanced span.data-advanced { display: inline; }' +
			'</style>' +
			'<table style="border-colapse: collapse; margin: 0; padding: 0;">' +
				'<tr>' +
					'<td style="width: 50%; padding: 0;">' +
						'<h3>Service Details</h3>' +
						'<table id="' + id + '-service-details" class="data-table"></table>' +
						'<table id="' + id + '-landline-type" class="data-table"></table>' +
						'<h3>Bill Address</h3>' +
						'<table id="' + id + '-bill-details" class="data-table"></table>' +
					'</td>' +
					'<td style="width: 20px;"></td>' +
					'<td style="width: 50%; padding: 0;">' +
						'<h3 style="position: relative; width: 100%;" class="data-entry">Service Address<input class="service-address-copy" type="button" value="Copy from another service" style="width: 180px; position: absolute; right: 0px; bottom: -1px;" /></h3>' +
						'<table id="' + id + '-service-address" class="data-table"></table>' +
					'</td>' +
			'</tr>' +
		'</table>';

		this.detailsContainer.select('.service-address-copy')[0].observe('click', self.showCopyWindow.bind(self, this.uniqueId));

		this.setWorkingTable($ID(id + '-service-details'));

		// This function wraps up the definition of max string length validation, so that it's neater
		var fncGetStringValidationFunc = function (intMaxLength) {
			return Validation.getStringLengthValidationFunc(intMaxLength);
		};

		this.addElementGroup('fnn', new TextInputGroup(this.getFNN(), true, Validation.fnnLandLine.bind(this)),'Landline Phone Number');
		
		fncIndialIsValid = function () {
			if (this.elementGroups.has_extension_level_billing != null) {
				this.elementGroups.has_extension_level_billing.aInputs[0].disabled = !this.elementGroups.is_indial_100.aInputs[0].checked;
				this.elementGroups.has_extension_level_billing.aInputs[0].checked = (this.elementGroups.has_extension_level_billing.aInputs[0].disabled) ? false : this.elementGroups.has_extension_level_billing.aInputs[0].checked;
			}
			return true;
		};
		this.addElementGroup('is_indial_100', new CheckboxGroup(this.getIsIndial100(), false, fncIndialIsValid.bind(this)),'Is Indial 100');
		this.addElementGroup('has_extension_level_billing', new CheckboxGroup(this.getHasExtensionLevelBilling()),'Has Extension Level Billing');
		this.elementGroups.is_indial_100.isValid();

		this.addElementGroup('landline_type_id', new DropDown(
			self.staticData.landlineType.id,
			self.staticData.landlineType.description,
			this.getLandlineTypeId(),
			true
		),'Landline Type');
		Event.observe(this.elementGroups.landline_type_id.aInputs[0], 'change', this.changeLandlineType.bind(this));
		this.changeLandlineType();

		this.setWorkingTable($ID(id + '-bill-details'));

		var lengths = self.staticData.lengths;

		this.addElementGroup('bill_name', new TextInputGroup(this.getBillName(), true, fncGetStringValidationFunc(lengths.landline.billName)),'Name');
		this.addElementGroup('bill_address_line_1', new TextInputGroup(this.getBillAddressLine1(), true, fncGetStringValidationFunc(lengths.landline.billAddressLine1)),'Address (Line 1)');
		this.addElementGroup('bill_address_line_2', new TextInputGroup(this.getBillAddressLine2(), false, fncGetStringValidationFunc(lengths.landline.billAddressLine2)),'Address (Line 2)');
		this.addElementGroup('bill_locality', new TextInputGroup(this.getBillLocality(), true, fncGetStringValidationFunc(lengths.landline.billLocality)),'Suburb');
		this.addElementGroup('bill_postcode', new TextInputGroup(this.getBillPostcode(), true, Validation.postcode.bind(this)),'Postcode');
		
		var advancedDisplay = this.objectHasAdvancedServiceAddressDetails();

		this.setWorkingTable($ID(id + '-service-address'));

		this.getWorkingTable().originalClassName = this.getWorkingTable().className;
		this.getWorkingTable().className = this.getWorkingTable().originalClassName + (advancedDisplay ? ' data-display-advanced' : ' data-display-basic');
		var tr = this.getWorkingTable().insertRow(-1);
		tr.className = "data-entry";
		var td = tr.insertCell(-1);
		td.appendChild(document.createTextNode('View Advanced Options:'));
		td = tr.insertCell(-1);
		var cb = this.advancedAddressCheckbox = document.createElement('input');
		cb.type = 'checkbox';
		cb.checked = advancedDisplay;
		td.appendChild(cb);
		var obj = {
			table: this.getWorkingTable(),
			checkbox: cb
		};
		this.isAdvancedAddress	= obj;

		Event.observe(cb, 'click', this.advancedServiceAddressCheckboxToggle.bind(this));

		this.addElementGroup('landline_service_address_type_id', new DropDown(
			self.staticData.landlineServiceAddressType.id,
			self.staticData.landlineServiceAddressType.description,
			this.getLandlineServiceAddressTypeId()
		), 'Address Type');
		
		Event.observe(this.elementGroups.landline_service_address_type_id.aInputs[0], 'change', this.changeLandLineServiceAddressTypeId.bind(this));
		Event.observe(this.elementGroups.landline_service_address_type_id.aInputs[0], 'keyup', this.changeLandLineServiceAddressTypeId.bind(this));
		this.getWorkingTable().rows[this.getWorkingTable().rows.length-1].className += " data-advanced";

		fncMandatoryAddressTypeNumber = function () {
			return (this.elementGroups.landline_service_address_type_id.getValue());
		};
		this.addElementGroup('service_address_type_number', new TextInputGroup(this.getServiceAddressTypeNumber(), fncMandatoryAddressTypeNumber.bind(this), Validation.integerPositive.bind(this)),'Address Type Number');
		
		this.getWorkingTable().rows[this.getWorkingTable().rows.length-1].className += " data-advanced";

		Event.observe(this.elementGroups.service_address_type_number.aInputs[0], 'change', this.changeServiceAddressTypeNumber.bind(this));
		Event.observe(this.elementGroups.service_address_type_number.aInputs[0], 'keyup', this.changeServiceAddressTypeNumber.bind(this));

		this.addElementGroup('service_address_type_suffix', new TextInputGroup(this.getServiceAddressTypeSuffix(), false, fncGetStringValidationFunc(lengths.landline.serviceAddressTypeSuffix)),'Address Type Suffix');
				
		this.getWorkingTable().rows[this.getWorkingTable().rows.length-1].className += " data-advanced";
		this.addElementGroup('service_street_number_start', new TextInputGroup(this.getServiceStreetNumberStart(), null, Validation.integerPositive.bind(this)),'Street Number Start');
		var fnIsMandatory = function () {
			return (
				(
					this.isAdvancedAddress.checkbox.checked
					&& !this.isAllotment()
					&& String(this.elementGroups.service_street_name.getValue()).strip()
					&& !String(this.elementGroups.service_property_name.getValue()).strip()
				)
				|| (!this.isAdvancedAddress.checkbox.checked)
			);//fncStreetNumberMandatory
		};
		this.elementGroups.service_street_number_start.mIsMandatory = fnIsMandatory.bind(this);
		var cell = this.getWorkingTable().rows[this.getWorkingTable().rows.length-1].cells[0];
		cell.innerHTML = "";
		cell.appendChild(document.createTextNode('Street Number'));
		var span = document.createElement('span');
		span.className = 'data-advanced';
		cell.appendChild(span);
		cell.appendChild(document.createTextNode(':'));
		span.appendChild(document.createTextNode(' Start'));

		Event.observe(this.elementGroups.service_street_number_start.aInputs[0], 'change', this.changeServiceStreetNumberStart.bind(this));
		Event.observe(this.elementGroups.service_street_number_start.aInputs[0], 'keyup', this.changeServiceStreetNumberStart.bind(this));

		this.addElementGroup('service_street_number_end', new TextInputGroup(this.getServiceStreetNumberEnd(), false, Validation.integerPositive.bind(this)),'Street Number End');
		this.getWorkingTable().rows[this.getWorkingTable().rows.length-1].className += " data-advanced";

		this.addElementGroup('service_street_number_suffix', new TextInputGroup(this.getServiceStreetNumberSuffix(), false, fncGetStringValidationFunc(lengths.landline.serviceStreetNumberSuffix)),'Street Number Suffix');
		this.getWorkingTable().rows[this.getWorkingTable().rows.length-1].className += " data-advanced";

		this.addElementGroup('service_street_name', new TextInputGroup(this.getServiceStreetName(), null, fncGetStringValidationFunc(lengths.landline.serviceStreetName)),'Street Name');
		this.elementGroups.service_street_name.mIsMandatory = function () {
			// Street Name is only manditory if a property name hasn't been supplied OR if a 'street number start' has been specified
			return ((!(this.isAdvancedAddress.checkbox.checked && String(this.elementGroups.service_property_name.getValue()).strip())) || String(this.elementGroups.service_street_number_start.getValue()).strip());
		}.bind(this);

		Element.observe(this.elementGroups.service_street_name.aInputs[0], 'change', this.changeStreetName.bind(this));
		Element.observe(this.elementGroups.service_street_name.aInputs[0], 'keyup', this.changeStreetName.bind(this));

		fncStreetTypeMandatory = function () {
			return String(this.elementGroups.service_street_name.getValue()).strip();
		};
		this.addElementGroup('landline_service_street_type_id', new DropDown(
			self.staticData.landlineServiceStreetType.id,
			self.staticData.landlineServiceStreetType.description,
			this.getLandlineServiceStreetTypeId(),
			fncStreetTypeMandatory.bind(this)
		),'Street Type');
		
		this.addElementGroup('landline_service_street_type_suffix_id', new DropDown(
			self.staticData.landlineServiceStreetTypeSuffix.id,
			self.staticData.landlineServiceStreetTypeSuffix.description,
			this.getLandlineServiceStreetTypeSuffixId()
		),'Street Type Suffix');
		
		fncPropertyNameMandatory = function () {
			return !(this.elementGroups.service_street_name.getValue());
		};
		this.addElementGroup('service_property_name', new TextInputGroup(this.getServicePropertyName(), fncPropertyNameMandatory.bind(this), fncGetStringValidationFunc(lengths.landline.servicePropertyName)),'Property Name');
		Element.observe(this.elementGroups.service_property_name.aInputs[0], 'change', this.changePropertyName.bind(this));
		Element.observe(this.elementGroups.service_property_name.aInputs[0], 'keyup', this.changePropertyName.bind(this));
		this.getWorkingTable().rows[this.getWorkingTable().rows.length-1].className += " data-advanced";

		this.addElementGroup('service_locality', new TextInputGroup(this.getServiceLocality(), true, fncGetStringValidationFunc(lengths.landline.serviceLocality)),'Suburb');
		this.addElementGroup('service_postcode', new TextInputGroup(this.getServicePostcode(), true, Validation.postcode.bind(this)),'Postcode');
		this.addElementGroup('landline_service_state_id', new DropDown(
			self.staticData.landlineServiceState.id,
			self.staticData.landlineServiceState.description,
			this.getLandlineServiceStateId(),
			true
		),'State');
		
		this.changeLandLineServiceAddressTypeId();
		this.changeStreetName();
		this.changeServiceStreetNumberStart();
		this.isValid();
	},

	updateFromGUI : function () {
		var bUpdateOk = this._super();
		if (bUpdateOk) {
			this.landlineAddressDetails.updateFromGUI();
		}
		return bUpdateOk;
	},

	isValid : function () {
		if(!this._super()) {
			return false;
		}
		if (this.landlineAddressDetails != null) {
			if (!this.landlineAddressDetails.isValid()) return false;
		} else {
			return false;
		}

		return true;
	},

	updateChildObjectsDisplay : function ($readOnly) {

	},

	showValidationTip : function () {
		return false;
	},

	renderDetails : function (readOnly) {

	},

	renderSummary : function (readOnly) {

	},

	changeLandlineType : function () {
		var id = this.elementGroups.landline_type_id.getValue();
		this.setLandlineTypeId(id);
	},

	setLandlineTypeId : function (value) {
		var id = 'service-mobile-table-' + this.uniqueId + '-landline-type';

		if (
			(value != this.object.landline_type_id)
			|| (value == LineType.LINE_TYPE_RESIDENTIAL && this.landlineAddressDetails instanceof LineType_Business)
			|| (value == LineType.LINE_TYPE_BUSINESS && this.landlineAddressDetails instanceof LineType_Residential)
		) {
			this.object.landline_type_id = value;
			this.object.landline_type_details = null;
			if (this.landlineAddressDetails != null) this.landlineAddressDetails.destroy();
			this.landlineAddressDetails = null;
			var newTable = document.createElement('table');
			newTable.className = 'data-table';
			$ID(id).parentNode.replaceChild(newTable, $ID(id));
			newTable.id = id;
		}
		this.object.landline_type_id = value;
		if (value == '' || value == '0' || value == null) return;
		if (this.detailsContainer != null) {
			this.landlineAddressDetails = (value == LineType.LINE_TYPE_RESIDENTIAL) ?
				new LineType_Residential(this.object.landline_type_details) :
				new LineType_Business(this.object.landline_type_details);
			this.object.landline_type_details = this.landlineAddressDetails.object;
			var table = $ID(id);
			this.landlineAddressDetails.setContainers(table);
		}
	},

	getLandlineTypeId : function () {
		return this.object.landline_type_id;
	},

	setFNN : function (value) {
		this.object.fnn = value;
	},

	getFNN : function () {
		return this.object.fnn;
	},

	setIsIndial100 : function (value) {
		this.object.is_indial_100 = value;
	},

	getIsIndial100 : function () {
		return this.object.is_indial_100;
	},

	setHasExtensionLevelBilling : function (value) {
		this.object.has_extension_level_billing = value;
	},

	getHasExtensionLevelBilling : function () {
		return this.object.has_extension_level_billing;
	},

	setBillName : function (value) {
		this.object.bill_name = value;
	},

	getBillName : function () {
		return this.object.bill_name;
	},

	setBillAddressLine1 : function (value) {
		this.object.bill_address_line_1 = value;
	},

	getBillAddressLine1 : function () {
		return this.object.bill_address_line_1;
	},

	setBillAddressLine2 : function (value) {
		this.object.bill_address_line_2 = value;
	},

	getBillAddressLine2 : function () {
		return this.object.bill_address_line_2;
	},

	setBillLocality : function (value) {
		this.object.bill_locality = value;
	},

	getBillLocality : function () {
		return this.object.bill_locality;
	},

	setBillPostcode : function (value) {
		this.object.bill_postcode = value;
	},

	getBillPostcode : function () {
		return this.object.bill_postcode;
	},

	setLandlineServiceAddressTypeId : function (value) {
		this.object.landline_service_address_type_id = value;
	},

	getLandlineServiceAddressTypeId : function () {
		return this.object.landline_service_address_type_id;
	},

	setServiceAddressTypeNumber : function (value) {
		this.object.service_address_type_number = value;
	},

	getServiceAddressTypeNumber : function () {
		return this.object.service_address_type_number;
	},

	setServiceAddressTypeSuffix : function (value) {
		this.object.service_address_type_suffix = value;
	},

	getServiceAddressTypeSuffix : function () {
		return this.object.service_address_type_suffix;
	},

	setServiceStreetNumberStart : function (value) {
		this.object.service_street_number_start = value;
	},

	getServiceStreetNumberStart : function () {
		return this.object.service_street_number_start;
	},

	setServiceStreetNumberEnd : function (value) {
		this.object.service_street_number_end = value;
	},

	getServiceStreetNumberEnd : function () {
		return this.object.service_street_number_end;
	},

	setServiceStreetNumberSuffix : function (value) {
		this.object.service_street_number_suffix = value;
	},

	getServiceStreetNumberSuffix : function () {
		return this.object.service_street_number_suffix;
	},

	setServiceStreetName : function (value) {
		this.object.service_street_name = value;
	},

	getServiceStreetName : function () {
		return this.object.service_street_name;
	},

	setLandlineServiceStreetTypeId : function (value) {
		this.object.landline_service_street_type_id = value;
	},

	getLandlineServiceStreetTypeId : function () {
		return this.object.landline_service_street_type_id;
	},

	setLandlineServiceStreetTypeSuffixId : function (value) {
		this.object.landline_service_street_type_suffix_id = value;
	},

	getLandlineServiceStreetTypeSuffixId : function () {
		return this.object.landline_service_street_type_suffix_id;
	},

	setServicePropertyName : function (value) {
		this.object.service_property_name = value;
	},

	getServicePropertyName : function () {
		return this.object.service_property_name;
	},

	setServiceLocality : function (value) {
		this.object.service_locality = value;
	},

	getServiceLocality : function () {
		return this.object.service_locality;
	},

	setLandlineServiceStateId : function (value) {
		this.object.landline_service_state_id = value;
	},

	getLandlineServiceStateId : function () {
		return this.object.landline_service_state_id;
	},

	setServicePostcode : function (value) {
		this.object.service_postcode = value;
	},

	getServicePostcode : function () {
		return this.object.service_postcode;
	},

	changeLandLineServiceAddressTypeId : function () {
		var intValue	= parseInt(this.elementGroups.landline_service_address_type_id.getValue(), 10);

		// Enable/Disable Inputs
		if (this.isAllotment()) {
			// Allotment Address
			this.elementGroups.service_address_type_number.enable();

			this.elementGroups.service_street_number_start.disable(true);

			//Sale.GUIComponent.disableElementGroup(this.elementGroups.service_street_number_end, true);

			//Sale.GUIComponent.disableElementGroup(this.elementGroups.service_street_number_suffix, true);

			this.elementGroups.service_street_name.enable();

			this.elementGroups.landline_service_street_type_id.enable();

			this.elementGroups.landline_service_street_type_suffix_id.enable();

			this.elementGroups.service_property_name.enable();
		} else if (this.isPostal()) {
			// Postal Address
			this.elementGroups.service_address_type_number.enable();

			this.elementGroups.service_street_number_start.disable(true);

			//Sale.GUIComponent.disableElementGroup(this.elementGroups.service_street_number_end, true);

			//Sale.GUIComponent.disableElementGroup(this.elementGroups.service_street_number_suffix, true);

			this.elementGroups.service_street_name.disable(true);

			this.elementGroups.landline_service_street_type_id.disable(true);

			this.elementGroups.landline_service_street_type_suffix_idt.disable(true);

			this.elementGroups.service_property_name.disable(true);
		} else {
			// Standard Address
			this.elementGroups.service_street_number_start.enable();

			//Sale.GUIComponent.enableElementGroup(this.elementGroups.service_street_number_end);

			//Sale.GUIComponent.enableElementGroup(this.elementGroups.service_street_number_suffix);

			this.elementGroups.service_street_name.enable();

			this.elementGroups.landline_service_street_type_id.enable();

			this.elementGroups.landline_service_street_type_suffix_id.enable();

			this.elementGroups.service_property_name.enable();

			if (intValue) {
				this.elementGroups.service_address_type_number.enable();

				this.elementGroups.service_address_type_suffix.enable();
			} else {
				this.elementGroups.service_address_type_number.disable(true);

				this.elementGroups.service_address_type_suffix.disable(true);
			}
		}
		this.changeServiceAddressTypeNumber();
		this.changeStreetName();
		this.changeServiceStreetNumberStart();


		// ReValidate Everything
		this.isValid();
	},

	changeServiceAddressTypeNumber : function () {
		var intValue = parseInt(this.elementGroups.service_address_type_number.getValue(), 10);
		if (intValue && this.elementGroups.service_address_type_number.isValid()) {
			this.elementGroups.service_address_type_suffix.enable();
		} else {
			this.elementGroups.service_address_type_suffix.disable(true);
		}
	},

	// VALIDATION
	// TODO! this should be checking the landline_service_address_type_category
	isAllotment	: function () {
		return (this.elementGroups.landline_service_address_type_id.getValue() == 1);
	},

	// TODO! this should be checking the landline_service_address_type_category
	isPostal : function () {
		var intValue = parseInt(this.elementGroups.landline_service_address_type_id.getValue(), 10);
		return (intValue >= 2 && intValue <= 14);
	},

	statics : Object.extend(Object.extend({}, ProductTypeModule.STATIC_INHERITANCE), {
		product_type_module: 'Service_Landline',

		unique: 1,

		copyWindow: null,

		hideCopyWindow : function () {
			if (self.copyWindow != null) {
				self.copyWindow.hide();
				self.copyWindow = null;
			}
		},

		showCopyWindow : function (uniqueId) {
			uniqueId = parseInt(uniqueId, 10);

			self.hideCopyWindow();

			var landlineServices = [];
			var target = null;
			for (var instanceId in SaleItem.instances) {
				if (typeof SaleItem.instances[instanceId] == 'function') {
					continue;
				}
				if (SaleItem.instances[instanceId].object.product_type_module != self.product_type_module) {
					continue;
				}
				var module = SaleItem.instances[instanceId].getProductModule();
				if (module.uniqueId == uniqueId) {
					target = module;
				} else {
					landlineServices[landlineServices.length] = SaleItem.instances[instanceId];
				}
			}
			if (target == null) {
				return alert("Failed to find target service for copy.");
			}
			if (landlineServices.length === 0) {
				return alert("There are no other services to copy details from. Enter the details for another service first.");
			}

			var copyWindow = self.copyWindow = new Reflex_Popup(60.31);
			copyWindow.addCloseButton();
			copyWindow.setTitle('Copy Landline Service Address Details');
			var button = document.createElement('input');
			button.type = 'button';
			button.value = 'Cancel';
			copyWindow.setFooterButtons([button]);
			var content = document.createElement('div');
			var form = document.createElement('form');
			form.method = "post";
			form.action = "JavaScript:void(0)";
			form.id = "json-copy-landline-service-details-form";
			content.style.textAlign = 'center !important';
			var table = document.createElement('table');
			table.style.width = "100%";
			table.style.margin = "0px 0px 7px 0px";
			var message = document.createElement('p');
			message.style.padding = "0px 12px";
			message.style.textAlign = "left";
			message.appendChild(document.createTextNode("Select a service to copy the details from."));
			content.appendChild(message);
			content.appendChild(form);
			form.appendChild(table);
			var tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Description'));
			tr.insertCell(-1).appendChild(document.createTextNode('Action'));
			tr.cells[0].style.fontWeight = tr.cells[1].style.fontWeight = "bold";
			tr.cells[0].style.textAlign = "left";
			tr.cells[1].style.textAlign = "right";

			var copyFunc = function() {
				self.copyDetailsFromOneToOther(this.source, this.target);
			};

			for (var i = 0, l = landlineServices.length; i < l; i++) {
				var ls = landlineServices[i];
				var mod = ls.getProductModule();
				tr = table.insertRow(-1);
				tr.insertCell(-1).appendChild(document.createTextNode(mod.getSummary(ls.productName)));
				var cpy = document.createElement('input');
				cpy.type = 'button';
				cpy.value = 'Copy';
				tr.insertCell(-1).appendChild(cpy);
				tr.cells[0].style.textAlign = "left";
				tr.cells[1].style.textAlign = "right";
				Event.observe(cpy, 'click', copyFunc.bind({ source: mod.uniqueId, target: uniqueId }), false);
			}

			Event.observe(button, 'click', self.hideCopyWindow.bind(self), false);

			copyWindow.setContent(content);

			copyWindow.display();
		},

		copyDetailsFromOneToOther : function (source, target) {
			self.hideCopyWindow();

			source = parseInt(source, 10);
			target = parseInt(target, 10);

			if (source == target) {
				return alert("The target service cannot be the same as the source service when copying details.");
			}

			var t = null, s = null;

			for (var instanceId in SaleItem.instances) {
				if (typeof SaleItem.instances[instanceId] == 'function') {
					continue;
				}
				if (SaleItem.instances[instanceId].object.product_type_module != self.product_type_module) {
					continue;
				}
				var module = SaleItem.instances[instanceId].getProductModule();
				if (module.uniqueId == source) {
					s = module;
				} else if (module.uniqueId == target) {
					t = module;
				}
				if (s != null && t != null) break;
			}

			if (s == null) {
				return alert('The source service could not be found.');
			}

			if (t == null) {
				return alert('The target service could not be found.');
			}

			// Now we need to copy the details from one to the other
			t.elementGroups.landline_service_address_type_id.setValue(s.elementGroups.landline_service_address_type_id.getValue());
			t.changeLandLineServiceAddressTypeId();

			t.elementGroups.service_address_type_number.setValue(s.elementGroups.service_address_type_number.getValue());
			t.changeServiceAddressTypeNumber();

			t.elementGroups.service_address_type_suffix.setValue(s.elementGroups.service_address_type_suffix.getValue());
			t.elementGroups.service_street_number_start.setValue(s.elementGroups.service_street_number_start.getValue());
			t.elementGroups.service_street_number_end.setValue(s.elementGroups.service_street_number_end.getValue());
			t.elementGroups.service_street_number_suffix.setValue(s.elementGroups.service_street_number_suffix.getValue());
			t.elementGroups.service_property_name.setValue(s.elementGroups.service_property_name.getValue());
			t.changePropertyName();
			t.elementGroups.service_street_name.setValue(s.elementGroups.service_street_name.getValue());
			t.changeStreetName();

			t.elementGroups.landline_service_street_type_id.setValue(s.elementGroups.landline_service_street_type_id.getValue());
			t.elementGroups.landline_service_street_type_suffix_id.setValue(s.elementGroups.landline_service_street_type_suffix_id.getValue());
			t.elementGroups.service_locality.setValue(s.elementGroups.service_locality.getValue());
			t.elementGroups.landline_service_state_id.setValue(s.elementGroups.landline_service_state_id.getValue());
			t.elementGroups.service_postcode.setValue(s.elementGroups.service_postcode.getValue());
			t.advancedAddressCheckbox.checked = s.advancedAddressCheckbox.checked;
			t.advancedServiceAddressCheckboxToggle();
		}
	})
});

// Load the static data required by this module
self.autoloadAndRegister();

return self;