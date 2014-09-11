//3150 - 3646
FW.Package.create('SP.Sale.Contact', {
	requires: ['SP.Sale.Contact.Contact_Method','SP.Sale.BillDeliveryType','FW.GUIComponent.DateGroup'],
	extends: 'FW.GUIComponent',



			objFax: null,
			objMobile: null,
			objPhone: null,
			objEmail: null,

			initialize: function(obj)
			{
				if (obj == null)
				{
					this.object = {
						id: null,
						created_on: null,
						contact_association_type_id: null,
						contact_title_id: null,
						contact_status_id: null,
						external_reference: null,
						created_on: null,
						first_name: null,
						middle_names: null,
						last_name: null,
						position_title: null,
						username: null,
						password: null,
						date_of_birth: null,

						contact_methods: []
					};
				}
				else
				{
					this.object = obj;
				}

				this.elementGroups = {};
			},

			buildGUI: function()
			{
				this.detailsContainer.innerHTML = '<table id="primary_contact_details_table" class="data-table"></table>';

				this.setWorkingTable($ID('primary_contact_details_table'));
				this.addElementGroup('contact_title_id', new FW.GUIComponent.DropDown(SP.Sale.contactTitles.ids, SP.Sale.contactTitles.labels, this.getContactTitleId()),'Title');
				this.addElementGroup('first_name', new FW.GUIComponent.TextInputGroup(this.getFirstName(), true),'First Name');
				this.addElementGroup('middle_names', new FW.GUIComponent.TextInputGroup(this.getMiddleNames()),'Middle Names');
				this.addElementGroup('last_name', new FW.GUIComponent.TextInputGroup(this.getLastName(), true),'Last Name');
				this.addElementGroup('position_title', new FW.GUIComponent.TextInputGroup(this.getPositionTitle()),'Position');
				this.addElementGroup('date_of_birth', new FW.GUIComponent.DateGroup(this.getDateOfBirth(), false, window._validate.date.bind(this)),'Date of Birth');
				

				var isMandatoryFunction	= function()
											{
												var bolPreferredContact	= this.getEmailObj().getIsPrimary();
												var bolDeliveryType		= (parseInt(SP.Sale.getInstance().getSaleAccount().elementGroups.bill_delivery_type_id.getValue()) == SP.Sale.BillDeliveryType.BILL_DELIVERY_TYPE_EMAIL);
												return (bolPreferredContact || bolDeliveryType);
											};
				this.addElementGroup('email', new FW.GUIComponent.TextInputGroup(this.getEmail(), isMandatoryFunction.bind(this), window._validate.email.bind(this)),'Email');				

				var isMandatoryFunction	= function(){return this.getFaxObj().getIsPrimary();};
				this.addElementGroup('fax', new FW.GUIComponent.TextInputGroup(this.getFax(), isMandatoryFunction.bind(this), window._validate.fnnLandLine.bind(this)),'Fax');				

				var isMandatoryFunction	= function(){return this.getMobileObj().getIsPrimary();};
				this.addElementGroup('mobile', new FW.GUIComponent.TextInputGroup(this.getMobile(), isMandatoryFunction.bind(this), window._validate.fnnMobile.bind(this)),'Mobile');				

				var isMandatoryFunction	= function(){return this.getPhoneObj().getIsPrimary();};
				this.addElementGroup('phone', new FW.GUIComponent.TextInputGroup(this.getPhone(), isMandatoryFunction.bind(this), window._validate.fnnLandLine.bind(this)),'Phone');
				

				this.addElementGroup('primaryContactMethod', new FW.GUIComponent.DropDown(
					[this.getEmailObj().getContactMethodTypeId(), this.getFaxObj().getContactMethodTypeId(), this.getPhoneObj().getContactMethodTypeId(), this.getMobileObj().getContactMethodTypeId()],
					['Email', 'Fax', 'Phone', 'Mobile'],
					this.getPrimaryContactMethod(),
					true), 'Preferred Contact Method');
				Event.observe(this.elementGroups.primaryContactMethod.aInputs[0], 'change', this.changePrimaryContactMethod.bind(this), true);

				

				// Disable the inputs if the Sale is to an existing customer
				switch (SP.Sale.getInstance().getSaleTypeId())
				{
					case SP.Sale.SaleType.SALE_TYPE_EXISTING:
					case SP.Sale.SaleType.SALE_TYPE_WINBACK:
						for (var sElementGroup in this.elementGroups)
						{
							this.elementGroups[sElementGroup].disable();
						}
						break;
				}
			},

			changePrimaryContactMethod: function()
			{
				$value = this.elementGroups.primaryContactMethod.aInputs[0].options[this.elementGroups.primaryContactMethod.aInputs[0].selectedIndex].value;
				this.setPrimaryContactMethod($value);
			},

			updateFromGUI: function($super)
			{
				var bUpdateOk = $super();
				if (bUpdateOk)
				{
					this.getEmailObj().setDetails(this.elementGroups.email.getValue());
					this.getFaxObj().setDetails(this.elementGroups.fax.getValue());
					this.getMobileObj().setDetails(this.elementGroups.mobile.getValue());
					this.getPhoneObj().setDetails(this.elementGroups.phone.getValue());
				}
				return bUpdateOk;
			},

			getPrimaryContactMethod: function()
			{
				if (this.getFaxObj().getIsPrimary()) return this.getFaxObj().getContactMethodTypeId();
				if (this.getEmailObj().getIsPrimary()) return this.getEmailObj().getContactMethodTypeId();
				if (this.getPhoneObj().getIsPrimary()) return this.getPhoneObj().getContactMethodTypeId();
				if (this.getMobileObj().getIsPrimary()) return this.getMobileObj().getContactMethodTypeId();
				return -1;
			},

			setPrimaryContactMethod: function($value)
			{
				// Set the Primary Contact Method
				this.getFaxObj().setIsPrimary($value == this.getFaxObj().getContactMethodTypeId());
				this.getEmailObj().setIsPrimary($value == this.getEmailObj().getContactMethodTypeId());
				this.getPhoneObj().setIsPrimary($value == this.getPhoneObj().getContactMethodTypeId());
				this.getMobileObj().setIsPrimary($value == this.getMobileObj().getContactMethodTypeId());

				// ReRun validation on the elements
				this.elementGroups.fax.isValid();
				this.elementGroups.email.isValid();
				this.elementGroups.phone.isValid();
				this.elementGroups.mobile.isValid();
			},

			getFaxObj: function()
			{
				if (this.objFax == null)
				{
					var obj = null;
					for (var i = 0, l = this.object.contact_methods.length; i < l; i++)
					{
						if (this.object.contact_methods[i].contact_method_type_id == SP.Sale.Contact.Contact_Method.CONTACT_METHOD_TYPE_FAX)
						{
							obj = this.object.contact_methods[i];
							break;
						}
					}
					this.objFax = this.addContactMethod(obj);
					this.objFax.setContactMethodTypeId(SP.Sale.Contact.Contact_Method.CONTACT_METHOD_TYPE_FAX);
				}
				return this.objFax;
			},

			getFax: function()
			{
				return this.getFaxObj().getDetails();
			},

			setFax: function($value)
			{
				return this.getFaxObj().setDetails($value);
			},

			getPhoneObj: function()
			{
				if (this.objPhone == null)
				{
					var obj = null;
					for (var i = 0, l = this.object.contact_methods.length; i < l; i++)
					{
						if (this.object.contact_methods[i].contact_method_type_id == SP.Sale.Contact.Contact_Method.CONTACT_METHOD_TYPE_PHONE)
						{
							obj = this.object.contact_methods[i];
							break;
						}
					}
					this.objPhone = this.addContactMethod(obj);
					this.objPhone.setContactMethodTypeId(SP.Sale.Contact.Contact_Method.CONTACT_METHOD_TYPE_PHONE);
				}
				return this.objPhone;
			},

			getPhone: function()
			{
				return this.getPhoneObj().getDetails();
			},

			setPhone: function($value)
			{
				return this.getPhoneObj().setDetails($value);
			},

			getMobileObj: function()
			{
				if (this.objMobile == null)
				{
					var obj = null;
					for (var i = 0, l = this.object.contact_methods.length; i < l; i++)
					{
						if (this.object.contact_methods[i].contact_method_type_id == SP.Sale.Contact.Contact_Method.CONTACT_METHOD_TYPE_MOBILE)
						{
							obj = this.object.contact_methods[i];
							break;
						}
					}
					this.objMobile = this.addContactMethod(obj);
					this.objMobile.setContactMethodTypeId(SP.Sale.Contact.Contact_Method.CONTACT_METHOD_TYPE_MOBILE);
				}
				return this.objMobile;
			},

			getMobile: function()
			{
				return this.getMobileObj().getDetails();
			},

			setMobile: function($value)
			{
				return this.getMobileObj().setDetails($value);
			},

			getEmailObj: function()
			{
				if (this.objEmail == null)
				{
					var obj = null;
					for (var i = 0, l = this.object.contact_methods.length; i < l; i++)
					{
						if (this.object.contact_methods[i].contact_method_type_id == SP.Sale.Contact.Contact_Method.CONTACT_METHOD_TYPE_EMAIL)
						{
							obj = this.object.contact_methods[i];
							break;
						}
					}
					this.objEmail = this.addContactMethod(obj);
					this.objEmail.setContactMethodTypeId(SP.Sale.Contact.Contact_Method.CONTACT_METHOD_TYPE_EMAIL);
				}
				return this.objEmail;
			},

			getEmail: function()
			{
				return this.getEmailObj().getDetails();
			},

			setEmail: function($value)
			{
				return this.getEmailObj().setDetails($value);
			},

			updateChildObjectsDisplay: function($readOnly)
			{
				$instances = this.getContactMethods();
				for (var $i = 0, $l = $instances.length; $i < $l; $i++)
				{
					if ($instances[$i]) $instances[$i].updateDisplay();
				}
			},



			showValidationTip: function()
			{
				return false;
			},




			addContactMethod: function(obj)
			{
				var contactMethod = new SP.Sale.Contact.Contact_Method(obj);
				if (obj == null)
				{
					this.object.contact_methods[this.object.contact_methods.length] = contactMethod.object;
				}
				return contactMethod;
			},

			removeContactMethod: function(instance)
			{
				for (var i in this.object.contact_methods)
				{
					if (this.object.contact_methods[i] == instance)
					{
						instance.destroy();
						delete this.object.contact_methods[i];
						return;
					}
				}
			},

			getContactMethods: function()
			{
				var arr = new Array();
				for (var $i = 0, $l = this.object.contact_methods.length; $i < $l; $i++)
				{
					arr[$i] = new SP.Sale.Contact.Contact_Method(this.object.contact_methods[$i]);
				}
				return arr;
			},

			setContactAssociationTypeId: function(value)
			{
				this.object.contact_association_type_id = value;
			},

			getContactAssociationTypeId: function()
			{
				return this.object.contact_association_type_id;
			},

			setContactTitleId: function(value)
			{
				this.object.contact_title_id = value;
			},

			getContactTitleId: function()
			{
				return this.object.contact_title_id;
			},

			setContactStatusId: function(value)
			{
				this.object.contact_status_id = value;
			},

			getContactStatusId: function()
			{
				return this.object.contact_status_id;
			},

			setExternalReference: function(value)
			{
				this.object.external_reference = value;
			},

			getExternalReference: function()
			{
				return this.object.external_reference;
			},

			setCreatedOn: function(value)
			{
				this.object.created_on = value;
			},

			getCreatedOn: function()
			{
				return this.object.created_on;
			},

			setFirstName: function(value)
			{
				this.object.first_name = value;
			},

			getFirstName: function()
			{
				return this.object.first_name;
			},

			setMiddleNames: function(value)
			{
				this.object.middle_names = value;
			},

			getMiddleNames: function()
			{
				return this.object.middle_names;
			},

			setLastName: function(value)
			{
				this.object.last_name = value;
			},

			getLastName: function()
			{
				return this.object.last_name;
			},

			setPositionTitle: function(value)
			{
				this.object.position_title = value;
			},

			getPositionTitle: function()
			{
				return this.object.position_title;
			},

			setUsername: function(value)
			{
				this.object.username = value;
			},

			getUsername: function()
			{
				return this.object.username;
			},

			setPassword: function(value)
			{
				this.object.password = value;
			},

			getPassword: function()
			{
				return this.object.password;
			},

			setDateOfBirth: function(value)
			{
				this.object.date_of_birth = value;
			},

			getDateOfBirth: function()
			{
				return this.object.date_of_birth;
			}

		}

);