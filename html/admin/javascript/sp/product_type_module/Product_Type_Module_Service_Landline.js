
Sale.ProductTypeModule.Service_Landline = Class.create();

Object.extend(Sale.ProductTypeModule.Service_Landline, Sale.ProductTypeModule);
Sale.ProductTypeModule.Service_Landline.prototype = {};

Object.extend(Sale.ProductTypeModule.Service_Landline, {
	
	product_type_module: 'Service_Landline',
	
	unique: 1,
	
	copyWindow: null,
	
	hideCopyWindow: function()
	{
		if (Sale.ProductTypeModule.Service_Landline.copyWindow != null)
		{
			Sale.ProductTypeModule.Service_Landline.copyWindow.hide();
			Sale.ProductTypeModule.Service_Landline.copyWindow = null;
		}
	},
	
	showCopyWindow: function(uniqueId)
	{
		uniqueId = parseInt(uniqueId);
		
		Sale.ProductTypeModule.Service_Landline.hideCopyWindow();
		
		var landlineServices = [];
		var target = null;
		for (var instanceId in Sale.Item.instances)
		{
			if (typeof Sale.Item.instances[instanceId] == 'function')
			{
				continue;
			}
			if (Sale.Item.instances[instanceId].object.product_type_module != Sale.ProductTypeModule.Service_Landline.product_type_module)
			{
				continue;
			}
			var module = Sale.Item.instances[instanceId].getProductModule();
			if (module.uniqueId == uniqueId)
			{
				target = module;
			}
			else
			{
				landlineServices[landlineServices.length] = Sale.Item.instances[instanceId];
			}
		}
		if (target == null)
		{
			return alert("Failed to find target service for copy.");
		}
		if (landlineServices.length == 0)
		{
			return alert("There are no other services to copy details from. Enter the details for another service first.");
		}
		
		var copyWindow = Sale.ProductTypeModule.Service_Landline.copyWindow = new Reflex_Popup(60.31);
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
			Sale.ProductTypeModule.Service_Landline.copyDetailsFromOneToOther(this.source, this.target);
		}

		for (var i = 0, l = landlineServices.length; i < l; i++)
		{
			var ls = landlineServices[i];
			var mod = ls.getProductModule();
			var tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode(mod.getSummary(ls.productName)));
			var cpy = document.createElement('input');
			cpy.type = 'button';
			cpy.value = 'Copy';
			tr.insertCell(-1).appendChild(cpy);
			tr.cells[0].style.textAlign = "left";
			tr.cells[1].style.textAlign = "right";
			Event.observe(cpy, 'click', copyFunc.bind({ source: mod.uniqueId, target: uniqueId }), false);
		}

		Event.observe(button, 'click', Sale.ProductTypeModule.Service_Landline.hideCopyWindow.bind(Sale.ProductTypeModule.Service_Landline), false);
		
		copyWindow.setContent(content);
		
		copyWindow.display();
	},
	
	copyDetailsFromOneToOther: function(source, target)
	{
		Sale.ProductTypeModule.Service_Landline.hideCopyWindow();

		source = parseInt(source);
		target = parseInt(target);
		
		if (source == target)
		{
			return alert("The target service cannot be the same as the source service when copying details.");
		}
		
		var t = null, s = null;

		for (var instanceId in Sale.Item.instances)
		{
			if (typeof Sale.Item.instances[instanceId] == 'function')
			{
				continue;
			}
			if (Sale.Item.instances[instanceId].object.product_type_module != Sale.ProductTypeModule.Service_Landline.product_type_module)
			{
				continue;
			}
			var module = Sale.Item.instances[instanceId].getProductModule();
			if (module.uniqueId == source)
			{
				s = module;
			}
			else if (module.uniqueId == target)
			{
				t = module;
			}
			if (s != null && t != null) break;
		}
		
		if (s == null)
		{
			return alert('The source service could not be found.');
		}
		
		if (t == null)
		{
			return alert('The target service could not be found.');
		}
		
		// Now we need to copy the details from one to the other
		t.elementGroups.landline_service_address_type_id.setValue(Sale.GUIComponent.getElementGroupValue(s.elementGroups.landline_service_address_type_id));
		t.changeLandLineServiceAddressTypeId();
		
		t.elementGroups.service_address_type_number.setValue(Sale.GUIComponent.getElementGroupValue(s.elementGroups.service_address_type_number));
		t.changeServiceAddressTypeNumber();
		
		t.elementGroups.service_address_type_suffix.setValue(Sale.GUIComponent.getElementGroupValue(s.elementGroups.service_address_type_suffix));
		t.elementGroups.service_street_number_start.setValue(Sale.GUIComponent.getElementGroupValue(s.elementGroups.service_street_number_start));
		t.elementGroups.service_street_number_end.setValue(Sale.GUIComponent.getElementGroupValue(s.elementGroups.service_street_number_end));
		t.elementGroups.service_street_number_suffix.setValue(Sale.GUIComponent.getElementGroupValue(s.elementGroups.service_street_number_suffix));
		t.elementGroups.service_property_name.setValue(Sale.GUIComponent.getElementGroupValue(s.elementGroups.service_property_name));
		t.changePropertyName();
		t.elementGroups.service_street_name.setValue(Sale.GUIComponent.getElementGroupValue(s.elementGroups.service_street_name));
		t.changeStreetName();
		
		t.elementGroups.landline_service_street_type_id.setValue(Sale.GUIComponent.getElementGroupValue(s.elementGroups.landline_service_street_type_id));
		t.elementGroups.landline_service_street_type_suffix_id.setValue(Sale.GUIComponent.getElementGroupValue(s.elementGroups.landline_service_street_type_suffix_id));
		t.elementGroups.service_locality.setValue(Sale.GUIComponent.getElementGroupValue(s.elementGroups.service_locality));
		t.elementGroups.landline_service_state_id.setValue(Sale.GUIComponent.getElementGroupValue(s.elementGroups.landline_service_state_id));
		t.elementGroups.service_postcode.setValue(Sale.GUIComponent.getElementGroupValue(s.elementGroups.service_postcode));
		t.advancedAddressCheckbox.checked = s.advancedAddressCheckbox.checked;
		t.advancedServiceAddressCheckboxToggle();
	}

});

Object.extend(Sale.ProductTypeModule.Service_Landline.prototype, Sale.ProductTypeModule.prototype);

Object.extend(Sale.ProductTypeModule.Service_Landline.prototype, {
	
	advancedAddressCheckbox: null,

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
			bill_address_line_2: saleAccount.getAddressLine2(),
			bill_locality: saleAccount.getSuburb(),
			bill_postcode: saleAccount.getPostcode(),
			
		};
	},

	objectHasAdvancedServiceAddressDetails: function()
	{
		return (this.object.landline_service_address_type_id != null && this.object.landline_service_address_type_id != '' && (parseInt(this.object.landline_service_address_type_id) > 0)) 
			|| (this.object.service_address_type_number != null && this.object.service_address_type_number != '')
			|| (this.object.service_address_type_suffix != null && this.object.service_address_type_suffix != '')
			|| (this.object.service_street_number_end != null && this.object.service_street_number_end != '')
			|| (this.object.service_street_number_suffix != null && this.object.service_street_number_suffix != '')
			|| (this.object.service_property_name != null && this.object.service_property_name != '');
	},

	updateSummary: function(suggestion)
	{
		this.summaryContainer.appendChild(document.createTextNode(this.getSummary(suggestion)));
	},

	getSummary: function(suggestion)
	{
		return suggestion + "; Landline Phone Number: " + ((this.object.fnn == undefined || this.object.fnn == null || this.object.fnn == '') ? '[Not set]' : this.object.fnn);
	},

	advancedServiceAddressCheckboxToggle: function() 
	{
		this.isAdvancedAddress.table.className = this.isAdvancedAddress.table.originalClassName + (this.isAdvancedAddress.checkbox.checked ? ' data-display-advanced' : ' data-display-basic');
		this.isValid(); 
	},

	changeStreetName: function()
	{
		if (Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_street_name).strip())
		{
			// enable the street type, and street type suffix fields
			Sale.GUIComponent.enableElementGroup(this.elementGroups.landline_service_street_type_id);
			Sale.GUIComponent.enableElementGroup(this.elementGroups.landline_service_street_type_suffix_id);
		}
		else
		{
			// disable these fields
			Sale.GUIComponent.disableElementGroup(this.elementGroups.landline_service_street_type_id, true);
			Sale.GUIComponent.disableElementGroup(this.elementGroups.landline_service_street_type_suffix_id, true);
		}
		
		this.elementGroups.landline_service_street_type_id.isValid();
		this.elementGroups.landline_service_street_type_suffix_id.isValid();
		this.elementGroups.service_property_name.isValid();
		this.elementGroups.service_street_number_start.isValid();
		this.changeServiceStreetNumberStart();
	},

	changeServiceStreetNumberStart: function()
	{
		if (Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_street_number_start).strip())
		{
			// enable the street number end, and street number suffix fields
			Sale.GUIComponent.enableElementGroup(this.elementGroups.service_street_number_end);
			Sale.GUIComponent.enableElementGroup(this.elementGroups.service_street_number_suffix);
		}
		else
		{
			// disable these fields
			Sale.GUIComponent.disableElementGroup(this.elementGroups.service_street_number_end, true);
			Sale.GUIComponent.disableElementGroup(this.elementGroups.service_street_number_suffix, true);
		}
		
		this.elementGroups.service_street_number_end.isValid();
		this.elementGroups.service_street_number_suffix.isValid();
		this.elementGroups.service_street_name.isValid();
	},

	changePropertyName: function()
	{
		this.elementGroups.service_street_name.isValid();
		this.elementGroups.service_street_number_start.isValid();
	},

	buildGUI: function()
	{
		var id = 'service-mobile-table-' + (this.uniqueId);

		this.detailsContainer.innerHTML = ''
		 + '<style>'
		 + 'table.data-display-basic .data-advanced { display: none; }'
		 + 'table.data-display-advanced tr.data-advanced { display: table-row; }'
		 + 'table.data-display-advanced span.data-advanced { display: inline; }'
		 + '</style>'
		 + '<table style="border-colapse: collapse; margin: 0; padding: 0;">'
			 + '<tr>'
				 + '<td style="width: 50%; padding: 0;">'
					 + '<h3>Service Details</h3>'
					 + '<table id="' + id + '-service-details" class="data-table"></table>'
					 + '<table id="' + id + '-landline-type" class="data-table"></table>'
					 + '<h3>Bill Address</h3>'
					 + '<table id="' + id + '-bill-details" class="data-table"></table>'
				 + '</td>'
				 + '<td style="width: 20px;"></td>'
				 + '<td style="width: 50%; padding: 0;">'
					 + '<h3 style="position: relative; width: 100%;" class="data-entry">Service Address<input type="button" value="Copy from another service" style="width: 180px; position: absolute; right: 0px; bottom: -1px;" onclick="Sale.ProductTypeModule.Service_Landline.showCopyWindow(\'' + this.uniqueId + '\');" /></h3>' 
					 + '<table id="' + id + '-service-address" class="data-table"></table>'
				 + '</td>'
			 + '</tr>'
		 + '</table>';

		var table = $ID(id + '-service-details');

		// This function wraps up the definition of max string length validation, so that it's neater
		var fncGetStringValidationFunc =   function(intMaxLength)
											{
												return window._validate.getStringLengthValidationFunc(intMaxLength);
											}

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


		Event.observe(this.elementGroups.landline_type_id.inputs[0], 'change', this.changeLandlineType.bind(this));
		this.changeLandlineType();


		var table = $ID(id + '-bill-details');

		var lengths = Sale.ProductTypeModule.Service_Landline.staticData.lengths;
		
		this.elementGroups.bill_name = Sale.GUIComponent.createTextInputGroup(this.getBillName(), true, fncGetStringValidationFunc(lengths.landline.billName));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Name', this.elementGroups.bill_name);

		this.elementGroups.bill_address_line_1 = Sale.GUIComponent.createTextInputGroup(this.getBillAddressLine1(), true, fncGetStringValidationFunc(lengths.landline.billAddressLine1));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Address (Line 1)', this.elementGroups.bill_address_line_1);

		this.elementGroups.bill_address_line_2 = Sale.GUIComponent.createTextInputGroup(this.getBillAddressLine2(), false, fncGetStringValidationFunc(lengths.landline.billAddressLine2));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Address (Line 2)', this.elementGroups.bill_address_line_2);

		this.elementGroups.bill_locality = Sale.GUIComponent.createTextInputGroup(this.getBillLocality(), true, fncGetStringValidationFunc(lengths.landline.billLocality));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Suburb', this.elementGroups.bill_locality);

		this.elementGroups.bill_postcode = Sale.GUIComponent.createTextInputGroup(this.getBillPostcode(), true, window._validate.postcode.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Postcode', this.elementGroups.bill_postcode);

		var advancedDisplay = this.objectHasAdvancedServiceAddressDetails();


		var table = $ID(id + '-service-address');

		table.originalClassName = table.className;
		table.className = table.originalClassName + (advancedDisplay ? ' data-display-advanced' : ' data-display-basic');
		var tr = table.insertRow(-1);
		tr.className = "data-entry";
		var td = tr.insertCell(-1);
		td.appendChild(document.createTextNode('View Advanced Options:'));
		td = tr.insertCell(-1);
		var cb = this.advancedAddressCheckbox = document.createElement('input');
		cb.type = 'checkbox';
		cb.checked = advancedDisplay;
		td.appendChild(cb);
		var obj = {
			table: table,
			checkbox: cb
		}
		this.isAdvancedAddress	= obj;

		Event.observe(cb, 'click', this.advancedServiceAddressCheckboxToggle.bind(this));

		this.elementGroups.landline_service_address_type_id = Sale.GUIComponent.createDropDown(
			Sale.ProductTypeModule.Service_Landline.staticData.landlineServiceAddressType.id, 
			Sale.ProductTypeModule.Service_Landline.staticData.landlineServiceAddressType.description, 
			this.getLandlineServiceAddressTypeId());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Address Type', this.elementGroups.landline_service_address_type_id);
		Event.observe(this.elementGroups.landline_service_address_type_id.inputs[0], 'change', this.changeLandLineServiceAddressTypeId.bind(this));
		Event.observe(this.elementGroups.landline_service_address_type_id.inputs[0], 'keyup', this.changeLandLineServiceAddressTypeId.bind(this));
		table.rows[table.rows.length-1].className += " data-advanced";

		fncMandatoryAddressTypeNumber	= function()
										{
											return (Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_service_address_type_id));
										};
		this.elementGroups.service_address_type_number = Sale.GUIComponent.createTextInputGroup(this.getServiceAddressTypeNumber(), fncMandatoryAddressTypeNumber.bind(this), window._validate.integerPositive.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Address Type Number', this.elementGroups.service_address_type_number);
		table.rows[table.rows.length-1].className += " data-advanced";

		Event.observe(this.elementGroups.service_address_type_number.inputs[0], 'change', this.changeServiceAddressTypeNumber.bind(this));
		Event.observe(this.elementGroups.service_address_type_number.inputs[0], 'keyup', this.changeServiceAddressTypeNumber.bind(this));

		this.elementGroups.service_address_type_suffix = Sale.GUIComponent.createTextInputGroup(this.getServiceAddressTypeSuffix(), false, fncGetStringValidationFunc(lengths.landline.serviceAddressTypeSuffix));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Address Type Suffix', this.elementGroups.service_address_type_suffix);
		table.rows[table.rows.length-1].className += " data-advanced";

		fncStreetNumberMandatory	= function()
									{
										return (	(   this.isAdvancedAddress.checkbox.checked 
														&& !this.isAllotment() 
														&& String(Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_street_name)).strip()
														&& !String(Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_property_name)).strip()
													) 
													|| (!this.isAdvancedAddress.checkbox.checked));
									}
		this.elementGroups.service_street_number_start = Sale.GUIComponent.createTextInputGroup(this.getServiceStreetNumberStart(), fncStreetNumberMandatory.bind(this), window._validate.integerPositive.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Street Number Start', this.elementGroups.service_street_number_start);
		var cell = table.rows[table.rows.length-1].cells[0];
		cell.innerHTML = "";
		cell.appendChild(document.createTextNode('Street Number'));
		var span = document.createElement('span');
		span.className = 'data-advanced';
		cell.appendChild(span);
		cell.appendChild(document.createTextNode(':'));
		span.appendChild(document.createTextNode(' Start'));

		Event.observe(this.elementGroups.service_street_number_start.inputs[0], 'change', this.changeServiceStreetNumberStart.bind(this));
		Event.observe(this.elementGroups.service_street_number_start.inputs[0], 'keyup', this.changeServiceStreetNumberStart.bind(this));


		this.elementGroups.service_street_number_end = Sale.GUIComponent.createTextInputGroup(this.getServiceStreetNumberEnd(), false, window._validate.integerPositive.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Street Number End', this.elementGroups.service_street_number_end);
		table.rows[table.rows.length-1].className += " data-advanced";

		this.elementGroups.service_street_number_suffix = Sale.GUIComponent.createTextInputGroup(this.getServiceStreetNumberSuffix(), false, fncGetStringValidationFunc(lengths.landline.serviceStreetNumberSuffix));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Street Number Suffix', this.elementGroups.service_street_number_suffix);
		table.rows[table.rows.length-1].className += " data-advanced";

		this.elementGroups.service_street_name = Sale.GUIComponent.createTextInputGroup(this.getServiceStreetName(), null, fncGetStringValidationFunc(lengths.landline.serviceStreetName));
		this.elementGroups.service_street_name.mixIsMandatory = function()
			{
				// Street Name is only manditory if a property name hasn't been supplied OR if a 'street number start' has been specified
				return ((!(this.isAdvancedAddress.checkbox.checked && String(Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_property_name)).strip())) || String(Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_street_number_start)).strip());
			}.bind(this);
		
		
		Sale.GUIComponent.appendElementGroupToTable(table, 'Street Name', this.elementGroups.service_street_name);
		Element.observe(this.elementGroups.service_street_name.inputs[0], 'change', this.changeStreetName.bind(this));
		Element.observe(this.elementGroups.service_street_name.inputs[0], 'keyup', this.changeStreetName.bind(this));

		fncStreetTypeMandatory	= function()
								{
									return String(Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_street_name)).strip();
								}
		this.elementGroups.landline_service_street_type_id = Sale.GUIComponent.createDropDown(
			Sale.ProductTypeModule.Service_Landline.staticData.landlineServiceStreetType.id, 
			Sale.ProductTypeModule.Service_Landline.staticData.landlineServiceStreetType.description, 
			this.getLandlineServiceStreetTypeId(),
			fncStreetTypeMandatory.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Street Type', this.elementGroups.landline_service_street_type_id);

		this.elementGroups.landline_service_street_type_suffix_id = Sale.GUIComponent.createDropDown(
			Sale.ProductTypeModule.Service_Landline.staticData.landlineServiceStreetTypeSuffix.id, 
			Sale.ProductTypeModule.Service_Landline.staticData.landlineServiceStreetTypeSuffix.description, 
			this.getLandlineServiceStreetTypeSuffixId());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Street Type Suffix', this.elementGroups.landline_service_street_type_suffix_id);

		fncPropertyNameMandatory =  function()
									{
										return !(Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_street_name));
									};
		this.elementGroups.service_property_name = Sale.GUIComponent.createTextInputGroup(this.getServicePropertyName(), fncPropertyNameMandatory.bind(this), fncGetStringValidationFunc(lengths.landline.servicePropertyName));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Property Name', this.elementGroups.service_property_name);
		Element.observe(this.elementGroups.service_property_name.inputs[0], 'change', this.changePropertyName.bind(this));
		Element.observe(this.elementGroups.service_property_name.inputs[0], 'keyup', this.changePropertyName.bind(this));
		table.rows[table.rows.length-1].className += " data-advanced";

		this.elementGroups.service_locality = Sale.GUIComponent.createTextInputGroup(this.getServiceLocality(), true, fncGetStringValidationFunc(lengths.landline.serviceLocality));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Suburb', this.elementGroups.service_locality);

		this.elementGroups.service_postcode = Sale.GUIComponent.createTextInputGroup(this.getServicePostcode(), true, window._validate.postcode.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Postcode', this.elementGroups.service_postcode);

		this.elementGroups.landline_service_state_id = Sale.GUIComponent.createDropDown(
			Sale.ProductTypeModule.Service_Landline.staticData.landlineServiceState.id, 
			Sale.ProductTypeModule.Service_Landline.staticData.landlineServiceState.description, 
			this.getLandlineServiceStateId(),
			true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'State', this.elementGroups.landline_service_state_id);

		this.changeLandLineServiceAddressTypeId();
		this.changeStreetName();
		this.changeServiceStreetNumberStart();
		this.isValid();
	},

	isValid: function()
	{
		var bolValid = true;

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

		// Service Address
		if (this.isAdvancedAddress.checkbox.checked)
		{
			// Advanced -- validate all fields
			bolValid	= (this.elementGroups.landline_service_address_type_id.isValid()) ? bolValid : false;
			value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_service_address_type_id);
			this.object.landline_service_address_type_id = value;

			bolValid	= (this.elementGroups.service_address_type_number.isValid()) ? bolValid : false;
			value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_address_type_number);
			this.object.service_address_type_number = value;

			bolValid	= (this.elementGroups.service_address_type_suffix.isValid()) ? bolValid : false;
			value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_address_type_suffix);
			this.object.service_address_type_suffix = value;

			bolValid	= (this.elementGroups.service_street_number_end.isValid()) ? bolValid : false;
			value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_street_number_end);
			this.object.service_street_number_end = value;

			bolValid	= (this.elementGroups.service_street_number_suffix.isValid()) ? bolValid : false;
			value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_street_number_suffix);
			this.object.service_street_number_suffix = value;

			bolValid	= (this.elementGroups.service_property_name.isValid()) ? bolValid : false;
			value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_property_name);
			this.object.service_property_name = value;

			bolValid	= (this.elementGroups.landline_service_street_type_suffix_id.isValid()) ? bolValid : false;
			value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_service_street_type_suffix_id);
			this.object.landline_service_street_type_suffix_id = value;
		}

		// Validate remaining fields
		bolValid	= (this.elementGroups.service_street_number_start.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_street_number_start);
		this.object.service_street_number_start = value;

		bolValid	= (this.elementGroups.service_street_name.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_street_name);
		this.object.service_street_name = value;

		bolValid	= (this.elementGroups.landline_service_street_type_id.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_service_street_type_id);
		this.object.landline_service_street_type_id = value;

		bolValid	= (this.elementGroups.service_locality.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_locality);
		this.object.service_locality = value;

		bolValid	= (this.elementGroups.landline_service_state_id.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_service_state_id);
		this.object.landline_service_state_id = value;

		bolValid	= (this.elementGroups.service_postcode.isValid()) ? bolValid : false;
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_postcode);
		this.object.service_postcode = value;

		// Billing Details
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
	
/*	changeLandLineServiceAddressTypeId: function()
	{
		intValue	= parseInt(Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_service_address_type_id));
		
		// Enable/Disable Inputs
		if (this.isAllotment())
		{
			// Allotment Address
			this.elementGroups.service_address_type_number.inputs[0].disabled					= false;
			
			this.elementGroups.service_street_number_start.inputs[0].value						= '';
			this.elementGroups.service_street_number_start.inputs[0].disabled					= true;
			
			this.elementGroups.service_street_number_end.inputs[0].value						= '';
			this.elementGroups.service_street_number_end.inputs[0].disabled						= true;
			
			this.elementGroups.service_street_number_suffix.inputs[0].value						= '';
			this.elementGroups.service_street_number_suffix.inputs[0].disabled					= true;
			
			this.elementGroups.service_street_name.inputs[0].disabled							= false;
			
			this.elementGroups.landline_service_street_type_id.inputs[0].disabled				= false;
			
			this.elementGroups.landline_service_street_type_suffix_id.inputs[0].disabled		= false;
			
			this.elementGroups.service_property_name.inputs[0].disabled							= false;
		}
		else if (this.isPostal())
		{
			// Postal Address
			this.elementGroups.service_address_type_number.inputs[0].disabled					= false;
			
			this.elementGroups.service_street_number_start.inputs[0].value						= '';
			this.elementGroups.service_street_number_start.inputs[0].disabled					= true;
			
			this.elementGroups.service_street_number_end.inputs[0].value						= '';
			this.elementGroups.service_street_number_end.inputs[0].disabled						= true;
			
			this.elementGroups.service_street_number_suffix.inputs[0].value						= '';
			this.elementGroups.service_street_number_suffix.inputs[0].disabled					= true;
			
			this.elementGroups.service_street_name.inputs[0].value								= '';
			this.elementGroups.service_street_name.inputs[0].disabled							= true;
			
			this.elementGroups.landline_service_street_type_id.inputs[0].selectedIndex			= 0;
			this.elementGroups.landline_service_street_type_id.inputs[0].disabled				= true;
			
			this.elementGroups.landline_service_street_type_suffix_id.inputs[0].selectedIndex	= 0;
			this.elementGroups.landline_service_street_type_suffix_id.inputs[0].disabled		= true;
			
			this.elementGroups.service_property_name.inputs[0].value							= '';
			this.elementGroups.service_property_name.inputs[0].disabled							= true;
		}
		else
		{
			// Standard Address
			this.elementGroups.service_street_number_start.inputs[0].disabled					= false;
			
			this.elementGroups.service_street_number_end.inputs[0].disabled						= false;
			
			this.elementGroups.service_street_number_suffix.inputs[0].disabled					= false;
			
			this.elementGroups.service_street_name.inputs[0].disabled							= false;
			
			this.elementGroups.landline_service_street_type_id.inputs[0].disabled				= false;
			
			this.elementGroups.landline_service_street_type_suffix_id.inputs[0].disabled		= false;
			
			this.elementGroups.service_property_name.inputs[0].disabled							= false;
			
			if (intValue)
			{
				this.elementGroups.service_address_type_number.inputs[0].disabled				= false;

				this.elementGroups.service_address_type_suffix.inputs[0].disabled				= false;
			}
			else
			{
				this.elementGroups.service_address_type_number.inputs[0].value					= '';
				this.elementGroups.service_address_type_number.inputs[0].disabled				= true;

				this.elementGroups.service_address_type_suffix.inputs[0].value					= '';
				this.elementGroups.service_address_type_suffix.inputs[0].disabled				= true;
			}
		}
		this.changeServiceAddressTypeNumber();
		
		// ReValidate Everything
		this.isValid();
	},
*/
	
	changeLandLineServiceAddressTypeId: function()
	{
		intValue	= parseInt(Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_service_address_type_id));
		
		// Enable/Disable Inputs
		if (this.isAllotment())
		{
			// Allotment Address
			Sale.GUIComponent.enableElementGroup(this.elementGroups.service_address_type_number);
			
			Sale.GUIComponent.disableElementGroup(this.elementGroups.service_street_number_start, true);
			
			//Sale.GUIComponent.disableElementGroup(this.elementGroups.service_street_number_end, true);
			
			//Sale.GUIComponent.disableElementGroup(this.elementGroups.service_street_number_suffix, true);
			
			Sale.GUIComponent.enableElementGroup(this.elementGroups.service_street_name);
			
			Sale.GUIComponent.enableElementGroup(this.elementGroups.landline_service_street_type_id);
			
			Sale.GUIComponent.enableElementGroup(this.elementGroups.landline_service_street_type_suffix_id);
			
			Sale.GUIComponent.enableElementGroup(this.elementGroups.service_property_name);
		}
		else if (this.isPostal())
		{
			// Postal Address
			Sale.GUIComponent.enableElementGroup(this.elementGroups.service_address_type_number);
			
			Sale.GUIComponent.disableElementGroup(this.elementGroups.service_street_number_start, true);
			
			//Sale.GUIComponent.disableElementGroup(this.elementGroups.service_street_number_end, true);
			
			//Sale.GUIComponent.disableElementGroup(this.elementGroups.service_street_number_suffix, true);
			
			Sale.GUIComponent.disableElementGroup(this.elementGroups.service_street_name, true);
			
			Sale.GUIComponent.disableElementGroup(this.elementGroups.landline_service_street_type_id, true);
			
			Sale.GUIComponent.disableElementGroup(this.elementGroups.landline_service_street_type_suffix_id, true);
			
			Sale.GUIComponent.disableElementGroup(this.elementGroups.service_property_name, true);
		}
		else
		{
			// Standard Address
			Sale.GUIComponent.enableElementGroup(this.elementGroups.service_street_number_start);
			
			//Sale.GUIComponent.enableElementGroup(this.elementGroups.service_street_number_end);
			
			//Sale.GUIComponent.enableElementGroup(this.elementGroups.service_street_number_suffix);
			
			Sale.GUIComponent.enableElementGroup(this.elementGroups.service_street_name);
			
			Sale.GUIComponent.enableElementGroup(this.elementGroups.landline_service_street_type_id);
			
			Sale.GUIComponent.enableElementGroup(this.elementGroups.landline_service_street_type_suffix_id);
			
			Sale.GUIComponent.enableElementGroup(this.elementGroups.service_property_name);
			
			if (intValue)
			{
				Sale.GUIComponent.enableElementGroup(this.elementGroups.service_address_type_number);

				Sale.GUIComponent.enableElementGroup(this.elementGroups.service_address_type_suffix);
			}
			else
			{
				Sale.GUIComponent.disableElementGroup(this.elementGroups.service_address_type_number, true);

				Sale.GUIComponent.disableElementGroup(this.elementGroups.service_address_type_suffix, true);
			}
		}
		this.changeServiceAddressTypeNumber();
		this.changeStreetName();
		this.changeServiceStreetNumberStart();

		
		// ReValidate Everything
		this.isValid();
	},

	changeServiceAddressTypeNumber	: function()
	{
		intValue	= parseInt(Sale.GUIComponent.getElementGroupValue(this.elementGroups.service_address_type_number));
		if (intValue && this.elementGroups.service_address_type_number.isValid())
		{
			Sale.GUIComponent.enableElementGroup(this.elementGroups.service_address_type_suffix);
		}
		else
		{
			Sale.GUIComponent.disableElementGroup(this.elementGroups.service_address_type_suffix, true);
		}
	},
	
	// VALIDATION
	// TODO! this should be checking the landline_service_address_type_category
	isAllotment	: function()
	{
		return (Sale.GUIComponent.getElementGroupValue(this.elementGroups.landline_service_address_type_id) == 1);
	},
	
	// TODO! this should be checking the landline_service_address_type_category
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
		
		// This function wraps up the definition of max string length validation, so that it's neater
		var fncGetStringValidationFunc =	function(intMaxLength)
											{
												return window._validate.getStringLengthValidationFunc(intMaxLength);
											}

		var lengths = Sale.ProductTypeModule.Service_Landline.staticData.lengths.landlineResidential;
		
		this.elementGroups.landline_end_user_title_id = Sale.GUIComponent.createDropDown(
			Sale.ProductTypeModule.Service_Landline.staticData.landlineEndUserTitle.id, 
			Sale.ProductTypeModule.Service_Landline.staticData.landlineEndUserTitle.description, 
			this.getLandlineEndUserTitleId(),
			true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Title', this.elementGroups.landline_end_user_title_id);

		this.elementGroups.end_user_given_name = Sale.GUIComponent.createTextInputGroup(this.getEndUserGivenName(), true, fncGetStringValidationFunc(lengths.endUserGivenName));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Given Name', this.elementGroups.end_user_given_name);

		this.elementGroups.end_user_family_name = Sale.GUIComponent.createTextInputGroup(this.getEndUserFamilyName(), true, fncGetStringValidationFunc(lengths.endUserFamilyName));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Family Name', this.elementGroups.end_user_family_name);

		this.elementGroups.end_user_dob = Sale.GUIComponent.createDateGroup(this.getEndUserDOB(), true, window._validate.date.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Date Of Birth', this.elementGroups.end_user_dob);

		this.elementGroups.end_user_employer = Sale.GUIComponent.createTextInputGroup(this.getEndUserEmployer(), false, fncGetStringValidationFunc(lengths.endUserEmployer));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Employer', this.elementGroups.end_user_employer);

		this.elementGroups.end_user_occupation = Sale.GUIComponent.createTextInputGroup(this.getEndUserOccupation(), false, fncGetStringValidationFunc(lengths.endUserOccupation));
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
		
		// This function wraps up the definition of max string length validation, so that it's neater
		var fncGetStringValidationFunc =	function(intMaxLength)
											{
												return window._validate.getStringLengthValidationFunc(intMaxLength);
											}

		var lengths = Sale.ProductTypeModule.Service_Landline.staticData.lengths.landlineBusiness;
		
		this.elementGroups.company_name = Sale.GUIComponent.createTextInputGroup(this.getCompanyName(), true, fncGetStringValidationFunc(lengths.companyName));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Company Name', this.elementGroups.company_name);

		this.elementGroups.abn = Sale.GUIComponent.createTextInputGroup(this.getABN(), true, window._validate.australianBusinessNumber.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'ABN', this.elementGroups.abn);

		this.elementGroups.trading_name = Sale.GUIComponent.createTextInputGroup(this.getTradingName(), false, fncGetStringValidationFunc(lengths.tradingName));
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
