
Sale.ProductTypeModule.Service_Landline = Class.create();

Object.extend(Sale.ProductTypeModule.Service_Landline, Sale.ProductTypeModule);
Sale.ProductTypeModule.Service_Landline.prototype = {};

Object.extend(Sale.ProductTypeModule.Service_Landline, {
	
	product_type_module: 'Service_Landline',
	
	unique: 1

});

Object.extend(Sale.ProductTypeModule.Service_Landline.prototype, Sale.ProductTypeModule.prototype);

Object.extend(Sale.ProductTypeModule.Service_Landline.prototype, {

	initialize: function(obj)
	{
		this.uniqueId = Sale.ProductTypeModule.Service_Mobile.unique++;
		Sale.ProductTypeModule.prototype.initialize.bind(this)(obj);
	},
	
	_getBlankDetailsObject: function()
	{
		var saleAccount = Sale.getInstance().getSaleAccount();
		saleAccount.isValid();
		
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
			bill_address_line_2: saleAccount.getAddressLine1(),
			bill_locality: saleAccount.getSuburb(),
			bill_postcode: saleAccount.getPostcode(),
			
		};
	},
	
	buildGUI: function()
	{
		var id = 'service-mobile-table-' + (this.uniqueId);

		this.detailsContainer.innerHTML = ''
		 + '<table>'
			 + '<tr>'
				 + '<td style="width: 50%;">'
					 + '<h3>Service Details</h3>'
					 + '<table id="' + id + '-service-details" class="data-table"></table>'
					 + '<div style="height: 150px;"><table id="' + id + '-landline-type" class="data-table"></table></div>'
					 + '<h3>Bill Address</h3>'
					 + '<table id="' + id + '-bill-details" class="data-table"></table>'
				 + '</td>'
				 + '<td style="width: 20px;"></td>'
				 + '<td style="width: 50%;">'
					 + '<h3>Service Address</h3>'
					 + '<table id="' + id + '-service-address" class="data-table"></table>'
				 + '</td>'
			 + '</tr>'
		 + '</table>';



		var table = $ID(id + '-service-details');

		this.elementGroups.fnn = Sale.GUIComponent.createTextInputGroup(this.getFNN(), true, window._validate.fnnLandLine.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Landline Phone Number', this.elementGroups.fnn);
		
		fncIndialIsValid	= function()
							{
								if (this.elementGroups.has_extension_level_billing != undefined)
								{
									this.elementGroups.has_extension_level_billing.inputs[0].disabled	= !this.elementGroups.is_indial_100.inputs[0].checked;
									this.elementGroups.has_extension_level_billing.inputs[0].checked	= (this.elementGroups.has_extension_level_billing.inputs[0].disabled) ? false : this.elementGroups.has_extension_level_billing.inputs[0].checked;
								}
								return true;
							}
		this.elementGroups.is_indial_100 = Sale.GUIComponent.createCheckboxGroup(this.getIsIndial100(), false, fncIndialIsValid.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Is Indial 100', this.elementGroups.is_indial_100);

		this.elementGroups.has_extension_level_billing = Sale.GUIComponent.createCheckboxGroup(this.getHasExtensionLevelBilling());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Has Extension Level Billing', this.elementGroups.has_extension_level_billing);
		this.elementGroups.is_indial_100.isValid();

		this.elementGroups.landline_type_id = Sale.GUIComponent.createDropDown(
			Sale.ProductTypeModule.Service_Landline.staticData.landlineType.id, 
			Sale.ProductTypeModule.Service_Landline.staticData.landlineType.description, 
			this.getLandlineTypeId(),
			true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Landline Type', this.elementGroups.landline_type_id);


		var onChangeLandlineType = this.changeLandlineType.bind(this);
		Event.observe(this.elementGroups.landline_type_id.inputs[0], 'change', onChangeLandlineType);
		this.changeLandlineType();


		var table = $ID(id + '-bill-details');

		this.elementGroups.bill_name = Sale.GUIComponent.createTextInputGroup(this.getBillName(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Name', this.elementGroups.bill_name);

		this.elementGroups.bill_address_line_1 = Sale.GUIComponent.createTextInputGroup(this.getBillAddressLine1(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Address (Line 1)', this.elementGroups.bill_address_line_1);

		this.elementGroups.bill_address_line_2 = Sale.GUIComponent.createTextInputGroup(this.getBillAddressLine2());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Address (Line 2)', this.elementGroups.bill_address_line_2);

		this.elementGroups.bill_locality = Sale.GUIComponent.createTextInputGroup(this.getBillLocality(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Suburb', this.elementGroups.bill_locality);

		this.elementGroups.bill_postcode = Sale.GUIComponent.createTextInputGroup(this.getBillPostcode(), true, window._validate.postcode.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Postcode', this.elementGroups.bill_postcode);



		var table = $ID(id + '-service-address');

		this.elementGroups.landline_service_address_type_id = Sale.GUIComponent.createDropDown(
			Sale.ProductTypeModule.Service_Landline.staticData.landlineServiceAddressType.id, 
			Sale.ProductTypeModule.Service_Landline.staticData.landlineServiceAddressType.description, 
			this.getLandlineServiceAddressTypeId());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Address Type', this.elementGroups.landline_service_address_type_id);
		Event.observe(this.elementGroups.landline_service_address_type_id.inputs[0], 'change', this.changeLandLineServiceAddressTypeId.bind(this));
		Event.observe(this.elementGroups.landline_service_address_type_id.inputs[0], 'keyup', this.changeLandLineServiceAddressTypeId.bind(this));

		fncMandatoryAddressTypeNumber	= function()
										{
											return (Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_service_address_type_id));
										};
		this.elementGroups.service_address_type_number = Sale.GUIComponent.createTextInputGroup(this.getServiceAddressTypeNumber(), fncMandatoryAddressTypeNumber.bind(this), window._validate.integerPositive.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Address Type Number', this.elementGroups.service_address_type_number);
		
		Event.observe(this.elementGroups.service_address_type_number.inputs[0], 'change', this.changeServiceAddressTypeNumber.bind(this));
		Event.observe(this.elementGroups.service_address_type_number.inputs[0], 'keyup', this.changeServiceAddressTypeNumber.bind(this));
		
		this.elementGroups.service_address_type_suffix = Sale.GUIComponent.createTextInputGroup(this.getServiceAddressTypeSuffix());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Address Type Suffix', this.elementGroups.service_address_type_suffix);
		
		fncStreetNumberMandatory	= function()
									{
										return (Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_street_name));
									}
		this.elementGroups.service_street_number_start = Sale.GUIComponent.createTextInputGroup(this.getServiceStreetNumberStart(), fncStreetNumberMandatory.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Street Number Start', this.elementGroups.service_street_number_start);

		this.elementGroups.service_street_number_end = Sale.GUIComponent.createTextInputGroup(this.getServiceStreetNumberEnd());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Street Number End', this.elementGroups.service_street_number_end);

		this.elementGroups.service_street_number_suffix = Sale.GUIComponent.createTextInputGroup(this.getServiceStreetNumberSuffix());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Street Number Suffix', this.elementGroups.service_street_number_suffix);

		fncStreeNameMandatory	= function()
								{
									return !(Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_property_name) && this.elementGroups.service_property_name.isValid());
								}
		this.elementGroups.service_street_name = Sale.GUIComponent.createTextInputGroup(this.getServiceStreetName(), fncStreeNameMandatory.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Street Name', this.elementGroups.service_street_name);

		this.elementGroups.landline_service_street_type_id = Sale.GUIComponent.createDropDown(
			Sale.ProductTypeModule.Service_Landline.staticData.landlineServiceStreetType.id, 
			Sale.ProductTypeModule.Service_Landline.staticData.landlineServiceStreetType.description, 
			this.getLandlineServiceStreetTypeId());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Street Type', this.elementGroups.landline_service_street_type_id);

		this.elementGroups.landline_service_street_type_suffix_id = Sale.GUIComponent.createDropDown(
			Sale.ProductTypeModule.Service_Landline.staticData.landlineServiceStreetTypeSuffix.id, 
			Sale.ProductTypeModule.Service_Landline.staticData.landlineServiceStreetTypeSuffix.description, 
			this.getLandlineServiceStreetTypeSuffixId());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Street Type Suffix', this.elementGroups.landline_service_street_type_suffix_id);

		fncPropertyNameMandatory	= function()
								{
									return !(Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_street_name) && this.elementGroups.service_street_name.isValid());
								}
		this.elementGroups.service_property_name = Sale.GUIComponent.createTextInputGroup(this.getServicePropertyName(), fncPropertyNameMandatory.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Property Name', this.elementGroups.service_property_name);
		
		this.elementGroups.service_locality = Sale.GUIComponent.createTextInputGroup(this.getServiceLocality(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Locality', this.elementGroups.service_locality);
		
		this.elementGroups.service_postcode = Sale.GUIComponent.createTextInputGroup(this.getServicePostcode(), true, window._validate.postcode.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Postcode', this.elementGroups.service_postcode);

		this.elementGroups.landline_service_state_id = Sale.GUIComponent.createDropDown(
			Sale.ProductTypeModule.Service_Landline.staticData.landlineServiceState.id, 
			Sale.ProductTypeModule.Service_Landline.staticData.landlineServiceState.description, 
			this.getLandlineServiceStateId(),
			true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'State', this.elementGroups.landline_service_state_id);
		
		this.isValid();
		this.changeLandLineServiceAddressTypeId();
	},
	
	isValid: function()
	{
		bolValid	= true;
		
		bolValid	= (this.elementGroups.fnn.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.fnn);
		this.object.fnn = value;

		bolValid	= (this.elementGroups.is_indial_100.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.is_indial_100);
		this.object.is_indial_100 = value;

		bolValid	= (this.elementGroups.has_extension_level_billing.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.has_extension_level_billing);
		this.object.has_extension_level_billing = value;

		bolValid	= (this.elementGroups.landline_type_id.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_type_id);
		this.object.landline_type_id = value;

		bolValid	= (this.elementGroups.landline_service_address_type_id.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_service_address_type_id);
		this.object.landline_service_address_type_id = value;

		bolValid	= (this.elementGroups.service_address_type_number.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_address_type_number);
		this.object.service_address_type_number = value;

		bolValid	= (this.elementGroups.service_address_type_suffix.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_address_type_suffix);
		this.object.service_address_type_suffix = value;

		bolValid	= (this.elementGroups.service_street_number_start.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_street_number_start);
		this.object.service_street_number_start = value;

		bolValid	= (this.elementGroups.service_street_number_end.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_street_number_end);
		this.object.service_street_number_end = value;

		bolValid	= (this.elementGroups.service_street_number_suffix.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_street_number_suffix);
		this.object.service_street_number_suffix = value;

		bolValid	= (this.elementGroups.service_property_name.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_property_name);
		this.object.service_property_name = value;

		bolValid	= (this.elementGroups.service_street_name.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_street_name);
		this.object.service_street_name = value;

		bolValid	= (this.elementGroups.landline_service_street_type_id.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_service_street_type_id);
		this.object.landline_service_street_type_id = value;

		bolValid	= (this.elementGroups.landline_service_street_type_suffix_id.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_service_street_type_suffix_id);
		this.object.landline_service_street_type_suffix_id = value;

		bolValid	= (this.elementGroups.service_locality.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_locality);
		this.object.service_locality = value;

		bolValid	= (this.elementGroups.landline_service_state_id.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_service_state_id);
		this.object.landline_service_state_id = value;

		bolValid	= (this.elementGroups.service_postcode.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_postcode);
		this.object.service_postcode = value;

		bolValid	= (this.elementGroups.bill_name.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.bill_name);
		this.object.bill_name = value;

		bolValid	= (this.elementGroups.bill_address_line_1.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.bill_address_line_1);
		this.object.bill_address_line_1 = value;

		bolValid	= (this.elementGroups.bill_address_line_2.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.bill_address_line_2);
		this.object.bill_address_line_2 = value;

		bolValid	= (this.elementGroups.bill_locality.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.bill_locality);
		this.object.bill_locality = value;

		bolValid	= (this.elementGroups.bill_postcode.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.bill_postcode);
		this.object.bill_postcode = value;

		if (this.landlineAddressDetails != null)
		{
			if (!this.landlineAddressDetails.isValid()) return false;
		}
		else
		{
			return false;
		}

		return bolValid;
	},

	updateChildObjectsDisplay: function($readOnly)
	{
		
	},
	
	showValidationTip: function()
	{
		return false;
	},
	
	renderDetails: function(readOnly)
	{
		
	},
	
	renderSummary: function(readOnly)
	{
		
	},
	
	
	landlineAddressDetails: null,
	
	changeLandlineType: function()
	{
		var id = Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_type_id);
		this.setLandlineTypeId(id);
	},
	
	setLandlineTypeId: function(value)
	{
		var id = 'service-mobile-table-' + this.uniqueId + '-landline-type';

		if (value != this.object.landline_type_id)
		{
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
		if (this.detailsContainer != null)
		{
			this.landlineAddressDetails = new ((value == Sale.ProductTypeModule.Service_Landline.LineType.LINE_TYPE_RESIDENTIAL) 
					? Sale.ProductTypeModule.Service_Landline.LineType.Residential 
					: Sale.ProductTypeModule.Service_Landline.LineType.Business)(this.object.landline_type_details);
			this.object.landline_type_details = this.landlineAddressDetails.object;
			var table = $ID(id);
			this.landlineAddressDetails.setContainers(table);
		}
	},
	
	getLandlineTypeId: function()
	{
		return this.object.landline_type_id;
	},
	
	
	

	setFNN: function(value)
	{
		this.object.fnn = value;
	},
	
	getFNN: function()
	{
		return this.object.fnn;
	},
	

	setIsIndial100: function(value)
	{
		this.object.is_indial_100 = value;
	},
	
	getIsIndial100: function()
	{
		return this.object.is_indial_100;
	},
	
	setHasExtensionLevelBilling: function(value)
	{
		this.object.has_extension_level_billing = value;
	},
	
	getHasExtensionLevelBilling: function()
	{
		return this.object.has_extension_level_billing;
	},
	
	setBillName: function(value)
	{
		this.object.bill_name = value;
	},
	
	getBillName: function()
	{
		return this.object.bill_name;
	},
	
	setBillAddressLine1: function(value)
	{
		this.object.bill_address_line_1 = value;
	},
	
	getBillAddressLine1: function()
	{
		return this.object.bill_address_line_1;
	},
	
	setBillAddressLine2: function(value)
	{
		this.object.bill_address_line_2 = value;
	},
	
	getBillAddressLine2: function()
	{
		return this.object.bill_address_line_2;
	},
	
	setBillLocality: function(value)
	{
		this.object.bill_locality = value;
	},
	
	getBillLocality: function()
	{
		return this.object.bill_locality;
	},
	
	setBillPostcode: function(value)
	{
		this.object.bill_postcode = value;
	},
	
	getBillPostcode: function()
	{
		return this.object.bill_postcode;
	},
	
	setLandlineServiceAddressTypeId: function(value)
	{
		this.object.landline_service_address_type_id = value;
	},
	
	getLandlineServiceAddressTypeId: function()
	{
		return this.object.landline_service_address_type_id;
	},
	
	setServiceAddressTypeNumber: function(value)
	{
		this.object.service_address_type_number = value;
	},
	
	getServiceAddressTypeNumber: function()
	{
		return this.object.service_address_type_number;
	},
	
	setServiceAddressTypeSuffix: function(value)
	{
		this.object.service_address_type_suffix = value;
	},
	
	getServiceAddressTypeSuffix: function()
	{
		return this.object.service_address_type_suffix;
	},
	
	setServiceStreetNumberStart: function(value)
	{
		this.object.service_street_number_start = value;
	},
	
	getServiceStreetNumberStart: function()
	{
		return this.object.service_street_number_start;
	},
	
	setServiceStreetNumberEnd: function(value)
	{
		this.object.service_street_number_end = value;
	},
	
	getServiceStreetNumberEnd: function()
	{
		return this.object.service_street_number_end;
	},
	
	setServiceStreetNumberSuffix: function(value)
	{
		this.object.service_street_number_suffix = value;
	},
	
	getServiceStreetNumberSuffix: function()
	{
		return this.object.service_street_number_suffix;
	},
	
	setServiceStreetName: function(value)
	{
		this.object.service_street_name = value;
	},
	
	getServiceStreetName: function()
	{
		return this.object.service_street_name;
	},
	
	setLandlineServiceStreetTypeId: function(value)
	{
		this.object.landline_service_street_type_id = value;
	},
	
	getLandlineServiceStreetTypeId: function()
	{
		return this.object.landline_service_street_type_id;
	},
	
	setLandlineServiceStreetTypeSuffixId: function(value)
	{
		this.object.landline_service_street_type_suffix_id = value;
	},
	
	getLandlineServiceStreetTypeSuffixId: function()
	{
		return this.object.landline_service_street_type_suffix_id;
	},
	
	setServicePropertyName: function(value)
	{
		this.object.service_property_name = value;
	},
	
	getServicePropertyName: function()
	{
		return this.object.service_property_name;
	},
	
	setServiceLocality: function(value)
	{
		this.object.service_locality = value;
	},
	
	getServiceLocality: function()
	{
		return this.object.service_locality;
	},
	
	setLandlineServiceStateId: function(value)
	{
		this.object.landline_service_state_id = value;
	},
	
	getLandlineServiceStateId: function()
	{
		return this.object.landline_service_state_id;
	},
	
	setServicePostcode: function(value)
	{
		this.object.service_postcode = value;
	},
	
	getServicePostcode: function()
	{
		return this.object.service_postcode;
	},
	
	changeLandLineServiceAddressTypeId: function()
	{
		intValue	= parseInt(Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_service_address_type_id));
		
		// Enable/Disable Inputs
		if (this.isAllotment())
		{
			// Allotment Address
			this.elementGroups.service_address_type_number.inputs[0].disabled					= false;
			this.elementGroups.service_address_type_number.inputs[0].removeClassName('disabled');
			
			this.elementGroups.service_street_number_start.inputs[0].value						= '';
			this.elementGroups.service_street_number_start.inputs[0].disabled					= true;
			this.elementGroups.service_street_number_start.inputs[0].addClassName('disabled');
			
			this.elementGroups.service_street_number_end.inputs[0].value						= '';
			this.elementGroups.service_street_number_end.inputs[0].disabled						= true;
			this.elementGroups.service_street_number_end.inputs[0].addClassName('disabled');
			
			this.elementGroups.service_street_number_suffix.inputs[0].value						= '';
			this.elementGroups.service_street_number_suffix.inputs[0].disabled					= true;
			this.elementGroups.service_street_number_suffix.inputs[0].addClassName('disabled');
			
			this.elementGroups.service_street_name.inputs[0].disabled							= false;
			this.elementGroups.service_street_name.inputs[0].removeClassName('disabled');
			
			this.elementGroups.landline_service_street_type_id.inputs[0].disabled				= false;
			this.elementGroups.landline_service_street_type_id.inputs[0].removeClassName('disabled');
			
			this.elementGroups.landline_service_street_type_suffix_id.inputs[0].disabled		= false;
			this.elementGroups.landline_service_street_type_suffix_id.inputs[0].removeClassName('disabled');
			
			this.elementGroups.service_property_name.inputs[0].disabled							= false;
			this.elementGroups.service_property_name.inputs[0].removeClassName('disabled');
		}
		else if (this.isPostal())
		{
			// Postal Address
			this.elementGroups.service_address_type_number.inputs[0].disabled					= false;
			this.elementGroups.service_address_type_number.inputs[0].removeClassName('disabled');
			
			this.elementGroups.service_street_number_start.inputs[0].value						= '';
			this.elementGroups.service_street_number_start.inputs[0].disabled					= true;
			this.elementGroups.service_street_number_start.inputs[0].addClassName('disabled');
			
			this.elementGroups.service_street_number_end.inputs[0].value						= '';
			this.elementGroups.service_street_number_end.inputs[0].disabled						= true;
			this.elementGroups.service_street_number_end.inputs[0].addClassName('disabled');
			
			this.elementGroups.service_street_number_suffix.inputs[0].value						= '';
			this.elementGroups.service_street_number_suffix.inputs[0].disabled					= true;
			this.elementGroups.service_street_number_suffix.inputs[0].addClassName('disabled');
			
			this.elementGroups.service_street_name.inputs[0].value								= '';
			this.elementGroups.service_street_name.inputs[0].disabled							= true;
			this.elementGroups.service_street_name.inputs[0].addClassName('disabled');
			
			this.elementGroups.landline_service_street_type_id.inputs[0].selectedIndex			= 0;
			this.elementGroups.landline_service_street_type_id.inputs[0].disabled				= true;
			this.elementGroups.landline_service_street_type_id.inputs[0].addClassName('disabled');
			
			this.elementGroups.landline_service_street_type_suffix_id.inputs[0].selectedIndex	= 0;
			this.elementGroups.landline_service_street_type_suffix_id.inputs[0].disabled		= true;
			this.elementGroups.landline_service_street_type_suffix_id.inputs[0].addClassName('disabled');
			
			this.elementGroups.service_property_name.inputs[0].value							= '';
			this.elementGroups.service_property_name.inputs[0].disabled							= true;
			this.elementGroups.service_property_name.inputs[0].addClassName('disabled');
		}
		else
		{
			// Standard Address
			this.elementGroups.service_street_number_start.inputs[0].disabled					= false;
			this.elementGroups.service_street_number_start.inputs[0].removeClassName('disabled');
			
			this.elementGroups.service_street_number_end.inputs[0].disabled						= false;
			this.elementGroups.service_street_number_end.inputs[0].removeClassName('disabled');
			
			this.elementGroups.service_street_number_suffix.inputs[0].disabled					= false;
			this.elementGroups.service_street_number_suffix.inputs[0].removeClassName('disabled');
			
			this.elementGroups.service_street_name.inputs[0].disabled							= false;
			this.elementGroups.service_street_name.inputs[0].removeClassName('disabled');
			
			this.elementGroups.landline_service_street_type_id.inputs[0].disabled				= false;
			this.elementGroups.landline_service_street_type_id.inputs[0].removeClassName('disabled');
			
			this.elementGroups.landline_service_street_type_suffix_id.inputs[0].disabled		= false;
			this.elementGroups.landline_service_street_type_suffix_id.inputs[0].removeClassName('disabled');
			
			this.elementGroups.service_property_name.inputs[0].disabled							= false;
			this.elementGroups.service_property_name.inputs[0].removeClassName('disabled');
			
			if (intValue)
			{
				this.elementGroups.service_address_type_number.inputs[0].disabled				= false;
				this.elementGroups.service_address_type_number.inputs[0].removeClassName('disabled');

				this.elementGroups.service_address_type_suffix.inputs[0].disabled				= false;
				this.elementGroups.service_address_type_suffix.inputs[0].removeClassName('disabled');
			}
			else
			{
				this.elementGroups.service_address_type_number.inputs[0].value					= '';
				this.elementGroups.service_address_type_number.inputs[0].disabled				= true;
				this.elementGroups.service_address_type_number.inputs[0].addClassName('disabled');

				this.elementGroups.service_address_type_suffix.inputs[0].value					= '';
				this.elementGroups.service_address_type_suffix.inputs[0].disabled				= true;
				this.elementGroups.service_address_type_suffix.inputs[0].addClassName('disabled');
			}
		}
		this.changeServiceAddressTypeNumber();
		
		// ReValidate Everything
		this.isValid();
	},
	
	changeServiceAddressTypeNumber	: function()
	{
		intValue	= parseInt(Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_address_type_number));
		if (intValue && this.elementGroups.service_address_type_number.isValid())
		{
			this.elementGroups.service_address_type_suffix.inputs[0].disabled				= false;
			this.elementGroups.service_address_type_suffix.inputs[0].removeClassName('disabled');
		}
		else
		{
			this.elementGroups.service_address_type_suffix.inputs[0].value					= '';
			this.elementGroups.service_address_type_suffix.inputs[0].disabled				= true;
			this.elementGroups.service_address_type_suffix.inputs[0].addClassName('disabled');
		}
	},
	
	// VALIDATION
	isAllotment	: function()
	{
		return (Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_service_address_type_id) == 1);
	},
	
	isPostal	: function()
	{
		intValue	= parseInt(Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_service_address_type_id));
		return (intValue >= 2 && intValue <= 14);
	}
});


Sale.ProductTypeModule.Service_Landline.LineType = {
	
	LINE_TYPE_RESIDENTIAL: 1,
	LINE_TYPE_BUSINESS: 2
	
};
Sale.ProductTypeModule.Service_Landline.LineType.Residential = Class.create();
Object.extend(Sale.ProductTypeModule.Service_Landline.LineType.Residential.prototype, Sale.ProductTypeModule.prototype);

Object.extend(Sale.ProductTypeModule.Service_Landline.LineType.Residential.prototype, {

	_getBlankDetailsObject: function()
	{
		var sale = Sale.getInstance();
		sale.getSaleAccount().isValid();
		var contact = sale.getContacts()[0];
		contact.isValid();
		
		var titleText = contact.elementGroups.contact_title_id.display.textContent;
		var titleId = null;
		for (var i = 0, l = Sale.ProductTypeModule.Service_Landline.staticData.landlineEndUserTitle.description.length; i < l; i++)
		{
			if (titleText == Sale.ProductTypeModule.Service_Landline.staticData.landlineEndUserTitle.description[i])
			{
				titleId = Sale.ProductTypeModule.Service_Landline.staticData.landlineEndUserTitle.id[i];
				break;
			}
		}

		return {
			id: null,
			landline_end_user_title_id: titleId,
			end_user_given_name: contact.getFirstName(),
			end_user_family_name: contact.getLastName(),
			end_user_dob: contact.getDateOfBirth(),
			end_user_employer: sale.getSaleAccount().getBusinessName(),
			end_user_occupation: contact.getPositionTitle()
		};
	},
	
	buildGUI: function()
	{
		var table = this.detailsContainer;
		
		this.elementGroups.landline_end_user_title_id = Sale.GUIComponent.createDropDown(
			Sale.ProductTypeModule.Service_Landline.staticData.landlineEndUserTitle.id, 
			Sale.ProductTypeModule.Service_Landline.staticData.landlineEndUserTitle.description, 
			this.getLandlineEndUserTitleId(),
			true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Title', this.elementGroups.landline_end_user_title_id);

		this.elementGroups.end_user_given_name = Sale.GUIComponent.createTextInputGroup(this.getEndUserGivenName(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Given Name', this.elementGroups.end_user_given_name);

		this.elementGroups.end_user_family_name = Sale.GUIComponent.createTextInputGroup(this.getEndUserFamilyName(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Family Name', this.elementGroups.end_user_family_name);

		this.elementGroups.end_user_dob = Sale.GUIComponent.createDateGroup(this.getEndUserDOB(), true, window._validate.date.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Date Of Birth', this.elementGroups.end_user_dob);

		this.elementGroups.end_user_employer = Sale.GUIComponent.createTextInputGroup(this.getEndUserEmployer());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Employer', this.elementGroups.end_user_employer);

		this.elementGroups.end_user_occupation = Sale.GUIComponent.createTextInputGroup(this.getEndUserOccupation());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Occupation', this.elementGroups.end_user_occupation);
	},
	
	isValid: function()
	{
		bolValid	= true;
		
		bolValid	= (this.elementGroups.landline_end_user_title_id.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_end_user_title_id);
		this.object.landline_end_user_title_id = value;

		bolValid	= (this.elementGroups.end_user_given_name.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.end_user_given_name);
		this.object.end_user_given_name = value;

		bolValid	= (this.elementGroups.end_user_family_name.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.end_user_family_name);
		this.object.end_user_family_name = value;

		bolValid	= (this.elementGroups.end_user_dob.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.end_user_dob);
		this.object.end_user_dob = value;

		bolValid	= (this.elementGroups.end_user_employer.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.end_user_employer);
		this.object.end_user_employer = value;

		bolValid	= (this.elementGroups.end_user_occupation.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.end_user_occupation);
		this.object.end_user_occupation = value;

		return bolValid;
	},
	
	showValidationTip: function()
	{
		return false;
	},
	
	renderDetails: function(readOnly)
	{
		
	},
	
	renderSummary: function(readOnly)
	{
		
	},
	
	setLandlineEndUserTitleId: function(value)
	{
		this.object.landline_end_user_title_id = value;
	},
	
	getLandlineEndUserTitleId: function()
	{
		return this.object.landline_end_user_title_id;
	},
	
	setEndUserGivenName: function(value)
	{
		this.object.end_user_given_name = value;
	},
	
	getEndUserGivenName: function()
	{
		return this.object.end_user_given_name;
	},

	setEndUserFamilyName: function(value)
	{
		this.object.end_user_family_name = value;
	},
	
	getEndUserFamilyName: function()
	{
		return this.object.end_user_family_name;
	},
	
	setEndUserDOB: function(value)
	{
		this.object.end_user_dob = value;
	},
	
	getEndUserDOB: function()
	{
		return this.object.end_user_dob;
	},
	
	setEndUserEmployer: function(value)
	{
		this.object.end_user_employer = value;
	},
	
	getEndUserEmployer: function()
	{
		return this.object.end_user_employer;
	},
	
	setEndUserOccupation: function(value)
	{
		this.object.end_user_occupation = value;
	},
	
	getEndUserOccupation: function()
	{
		return this.object.end_user_occupation;
	}
		

});


Sale.ProductTypeModule.Service_Landline.LineType.Business = Class.create();
Object.extend(Sale.ProductTypeModule.Service_Landline.LineType.Business.prototype, Sale.ProductTypeModule.prototype);
Object.extend(Sale.ProductTypeModule.Service_Landline.LineType.Business.prototype, {

	_getBlankDetailsObject: function()
	{
		var saleAccount = Sale.getInstance().getSaleAccount();
		saleAccount.isValid();

		return {
			id: null,
			company_name: saleAccount.getBusinessName(),
			abn: saleAccount.getABN(),
			trading_name: saleAccount.getTradingName(),
		};
	},
	
	buildGUI: function()
	{
		var table = this.detailsContainer;
		
		this.elementGroups.company_name = Sale.GUIComponent.createTextInputGroup(this.getCompanyName(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Company Name', this.elementGroups.company_name);

		this.elementGroups.abn = Sale.GUIComponent.createTextInputGroup(this.getABN(), true, window._validate.australianBusinessNumber.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'ABN', this.elementGroups.abn);

		this.elementGroups.trading_name = Sale.GUIComponent.createTextInputGroup(this.getTradingName());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Trading Name', this.elementGroups.trading_name);
	},
	
	isValid: function()
	{
		bolValid	= true;
		
		bolValid	= (this.elementGroups.company_name.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.company_name);
		this.object.company_name = value;

		bolValid	= (this.elementGroups.abn.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.abn);
		this.object.abn = value;

		bolValid	= (this.elementGroups.trading_name.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.trading_name);
		this.object.trading_name = value;

		return bolValid;
	},
	
	showValidationTip: function()
	{
		return false;
	},
	
	renderDetails: function(readOnly)
	{
		
	},
	
	renderSummary: function(readOnly)
	{
		
	},
	
	setCompanyName: function(value)
	{
		this.object.company_name = value;
	},
	
	getCompanyName: function()
	{
		return this.object.company_name;
	},
	
	setABN: function(value)
	{
		this.object.abn = value;
	},
	
	getABN: function()
	{
		return this.object.abn;
	},
	
	setTradingName: function(value)
	{
		this.object.trading_name = value;
	},
	
	getTradingName: function()
	{
		return this.object.trading_name;
	}

});



// Load the static data required by this module
Sale.ProductTypeModule.Service_Landline.autoloadAndRegister();
