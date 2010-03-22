

Account_Create = Class.create({
	
	initialize	: function(oForm)
	{
	
	
		//----------------------------------------------------------------//
		// Load Constants
		//----------------------------------------------------------------//
	
		// Flex.Constant.loadConstantGroup(['BillingType', 'account_status'], this._onConstantLoad.bind(this))
		Flex.Constant.loadConstantGroup('BillingType', this._onConstantLoad.bind(this))
		
		this.oForm					= oForm;
		this.oForm.oAccountCreate	= this;

		
		//----------------------------------------------------------------//
		// Validate Proposed Account
		//----------------------------------------------------------------//

		// Validate Business Name
		this.oForm.getInputs('text','Account[BusinessName]').first().validate = function ()
		{
			if (!/\S/.test(this.value))
			{
				this.className = "invalid";
				return "Invalid Business Name";
			}
			this.className = "valid";
			return true;
		}
		
		// Validate Trading Name
		this.oForm.getInputs('text','Account[TradingName]').first().validate = function ()
		{
			this.className = "valid";
			return true;
		}

		// Validate an ABN
		this.oForm.getInputs('text','Account[ABN]').first().validate = function ()
		{
			if (!Reflex_Validation.abn(this.value) && Reflex_Validation.acn($ID('Account[ACN]').value) && this.value !== '')
			{
				this.className = "invalid";
				return "Invalid ABN specified";
			}
			if (Reflex_Validation.abn(this.value) && $ID('Account[ACN]').value == '')
			{
				this.className = "valid";
				$ID('Account[ACN]').className = "valid";
				return true;
			}
			if (!Reflex_Validation.abn(this.value) && !Reflex_Validation.acn($ID('Account[ACN]').value))
			{
				this.className = "invalid";
				$ID('Account[ACN]').className = "invalid";
				return "Invalid ABN specified";
			}
			this.className = "valid";
			return true;
		}

		// Validate an ACN
		this.oForm.getInputs('text','Account[ACN]').first().validate = function ()
		{
			if (!Reflex_Validation.acn(this.value) && Reflex_Validation.abn($ID('Account[ABN]').value) && this.value !== '')
			{
				this.className = "invalid";
				return "Invalid ACN specified";
			}
			if (Reflex_Validation.acn(this.value) && $ID('Account[ABN]').value == '')
			{
				this.className = "valid";
				$ID('Account[ABN]').className = "valid";
				return true;
			}
			if (!Reflex_Validation.acn(this.value) && !Reflex_Validation.abn($ID('Account[ABN]').value))
			{
				this.className = "invalid";
				$ID('Account[ABN]').className = "invalid";
				return "Invalid ACN specified";
			}
			this.className = "valid";
			return true;
		}

		// Validate Address line 1
		this.oForm.getInputs('text','Account[Address1]').first().validate = function ()
		{
			if (!/\S/.test(this.value))
			{
				this.className = "invalid";
				return "Invalid Address (Line 1)";
			}
			this.className = "valid";
			return true;
		}
		
		// Validate Address line 2
		this.oForm.getInputs('text','Account[Address2]').first().validate = function ()
		{
			this.className = "valid";
			return true;
		}

		// Validate Suburb
		this.oForm.getInputs('text','Account[Suburb]').first().validate = function ()
		{
			if (!/\S/.test(this.value))
			{
				this.className = "invalid";
				return "Invalid Suburb";
			}
			this.className = "valid";
			return true;
		}

		// Validate Postcode
		this.oForm.getInputs('text','Account[Postcode]').first().validate = function ()
		{
			if (this.value.match (/^\d{4}$/) === null)
			{
				this.className = "invalid";
				return "Invalid Postcode";	
			}
			this.className = "valid";
			return true;
		}

		// Validate State
		this.oForm.select('select[name="Account[State]"]').first().validate = function ()
		{
			if (!/\S/.test(this.value))
			{
				this.className = "invalid";
				return "Invalid State";	
			}
			this.className = "valid";
			return true;
		}

		// Validate Customer Group
		this.oForm.select('select[name="Account[CustomerGroup]"]').first().validate = function ()
		{
			if (!/\S/.test(this.value))
			{
				this.className = "invalid";
				return "Invalid Customer Group";	
			}
			this.className = "valid";
			return true;
		}

		this.oForm.select('select[name="Account[DeliveryMethod]"]').first().validate = function ()
		{
			if (!/\S/.test(this.value))
			{	
				this.className = "invalid";
				return "Invalid Delivery Method";
			}
			this.className = "valid";
			return true;
		}
		//----------------------------------------------------------------//
		// Validate Proposed Billing Details
		//----------------------------------------------------------------//
		
		// Validate Direct Debit Bank Name
		this.oForm.getInputs('text','DDR[BankName]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Account[BillingType]"]:checked').first();
			if (oSelectedRadio && !/\S/.test(this.value))
			{
				this.className = "invalid";
				return (oSelectedRadio.value == $CONSTANT.BILLING_TYPE_DIRECT_DEBIT) ? "Invalid Direct Debit Account BankName" : true;
			}
			this.className = "valid";
			return true;
		}

		// Validate Direct Debit BSB Number
		this.oForm.getInputs('text','DDR[BSB]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Account[BillingType]"]:checked').first();
			if (oSelectedRadio && !/\S/.test(this.value))
			{
				this.className = "invalid";
				return (oSelectedRadio.value == $CONSTANT.BILLING_TYPE_DIRECT_DEBIT) ? "Invalid Direct Debit Account BSB" : true;
			}
			this.className = "valid";
			return true;
		}

		// Validate Direct Debit Account Number
		this.oForm.getInputs('text','DDR[AccountNumber]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Account[BillingType]"]:checked').first();
			if (oSelectedRadio && !/\S/.test(this.value))
			{
				this.className = "invalid";
				return (oSelectedRadio.value == $CONSTANT.BILLING_TYPE_DIRECT_DEBIT) ? "Invalid Direct Debit Account Number" : true;
			}
			this.className = "valid";
			return true;
		}

		// Validate Direct Debit Account Name
		this.oForm.getInputs('text','DDR[AccountName]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Account[BillingType]"]:checked').first();
			if (oSelectedRadio && !/\S/.test(this.value))
			{
				this.className = "invalid";
				return (oSelectedRadio.value == $CONSTANT.BILLING_TYPE_DIRECT_DEBIT) ? "Invalid Direct Debit Account Name" : true;
			}
			this.className = "valid";
			return true;
		}
		
		
		// Validate CC Name
		this.oForm.getInputs('text','CC[Name]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Account[BillingType]"]:checked').first();
			if (oSelectedRadio && !/\S/.test(this.value))
			{
				this.className = "invalid";
				return (oSelectedRadio.value == $CONSTANT.BILLING_TYPE_CREDIT_CARD) ? "Invalid Direct Debit Credit Card Name" : true;
			}
			this.className = "valid";
			return true;
		}
		
		// Validate CC
		this.oForm.select('select[name="CC[CardType]"]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Account[BillingType]"]:checked').first();
			if (oSelectedRadio && !_validate.creditCardNumber($ID('CC[CardNumber]').value, this.value))
			{	
				this.className = "invalid";
				$ID('CC[CardNumber]').className = "invalid";
				return (oSelectedRadio.value == $CONSTANT.BILLING_TYPE_CREDIT_CARD) ? "Invalid Direct Debit Credit Card Card Type" : true;
			}
			this.className = "valid";
			$ID('CC[CardNumber]').className = "valid";
			return true;
		}
		this.oForm.getInputs('text','CC[CardNumber]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Account[BillingType]"]:checked').first();
			if (oSelectedRadio && !_validate.creditCardNumber(this.value, $ID('CC[CardType]').value))
			{
				this.className = "invalid";
				$ID('CC[CardType]').className = "invalid";
				return (oSelectedRadio.value == $CONSTANT.BILLING_TYPE_CREDIT_CARD) ? "Invalid Direct Debit Credit Card Number" : true;
			}
			this.className = "valid";
			$ID('CC[CardType]').className = "valid";
			return true;
		}

		// Validate CC Expiry
		this.oForm.select('select[name="CC[ExpMonth]"]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Account[BillingType]"]:checked').first();
			if (oSelectedRadio && !CreditCardPayment.checkExpiry(this.value, $ID('CC[ExpYear]').value))
			{			
				this.className = "invalid";			
				$ID('CC[ExpYear]').className = "invalid";
				return (oSelectedRadio.value == $CONSTANT.BILLING_TYPE_CREDIT_CARD) ? "Invalid Direct Debit Credit Card Expiry Month" : true;
			}
			this.className = "valid";
			$ID('CC[ExpYear]').className = "valid";
			return true;
		}
		this.oForm.select('select[name="CC[ExpYear]"]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Account[BillingType]"]:checked').first();
			if (oSelectedRadio && !CreditCardPayment.checkExpiry($ID('CC[ExpMonth]').value, this.value))
			{			
				this.className = "invalid";			
				$ID('CC[ExpMonth]').className = "invalid";
				return (oSelectedRadio.value == $CONSTANT.BILLING_TYPE_CREDIT_CARD) ? "Invalid Direct Debit Credit Card Expiry Year" : true;
			}
			this.className = "valid";
			$ID('CC[ExpMonth]').className = "valid";
			return true;
		}
		
		// Validate CC CVV
		this.oForm.getInputs('text','CC[CVV]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Account[BillingType]"]:checked').first();
			if (oSelectedRadio && this.value.match (/^\d{3,4}$/) === null)
			{
				this.className = "invalid";
				return (oSelectedRadio.value == $CONSTANT.BILLING_TYPE_CREDIT_CARD) ? "Invalid Direct Debit Credit Card CVV" : true;
			}
			this.className = "valid";
			return true;
		}


		//----------------------------------------------------------------//
		// Validate Proposed Primary Contact
		//----------------------------------------------------------------//
		this.oForm.select('select[name="Contact[Title]"]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Contact[USE]"]:checked').first();
			if (oSelectedRadio && this.value.length == 0)
			{			
				this.className = "invalid";
				return (oSelectedRadio.value != 1) ? "Invalid Contact Title Selected" : true;
			}
			this.className = "valid";
			return true;
		}
		this.oForm.getInputs('text','Contact[FirstName]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Contact[USE]"]:checked').first();
			if (oSelectedRadio && !/\S/.test(this.value))
			{
				this.className = "invalid";
				return (oSelectedRadio.value != 1) ? "Invalid First Name" : true;
			}
			this.className = "valid";
			return true;
		}
		this.oForm.getInputs('text','Contact[LastName]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Contact[USE]"]:checked').first();
			if (oSelectedRadio && !/\S/.test(this.value))
			{
				this.className = "invalid";
				return (oSelectedRadio.value != 1) ? "Invalid Last Name" : true;
			}
			this.className = "valid";
			return true;
		}
		
		
		// Date of birth
		this.oForm.select('select[name="Contact[DOB][Day]"]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Contact[USE]"]:checked').first();
			if (oSelectedRadio && this.value.length == 0)
			{			
				this.className = "invalid";
				return (oSelectedRadio.value != 1) ? "Invalid Date Of Birth Day" : true;
			}
			this.className = "valid";
			return true;
		}
		this.oForm.select('select[name="Contact[DOB][Month]"]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Contact[USE]"]:checked').first();
			if (oSelectedRadio && this.value.length == 0)
			{			
				this.className = "invalid";
				return (oSelectedRadio.value != 1) ? "Invalid Date Of Birth Month" : true;
			}
			this.className = "valid";
			return true;
		}
		this.oForm.select('select[name="Contact[DOB][Year]"]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Contact[USE]"]:checked').first();
			if (oSelectedRadio && this.value.length == 0)
			{
				this.className = "invalid";
				return (oSelectedRadio.value != 1) ? "Invalid Date Of Birth Year" : true;
			}
			this.className = "valid";
			return true;
		}
		
		// Job Title
		this.oForm.getInputs('text','Contact[JobTitle]').first().validate = function ()
		{
			this.className = "valid";
			return true;
		}
		
		// Email
		this.oForm.getInputs('text','Contact[Email]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Contact[USE]"]:checked').first();
			if (oSelectedRadio && !_validate.email(this.value))
			{
				this.className = "invalid";
				return (oSelectedRadio.value != 1) ? "Invalid Contact Email" : true;
			}
			this.className = "valid";
			return true;
		}
		
		// Validate Phone
		this.oForm.getInputs('text','Contact[Phone]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Contact[USE]"]:checked').first();
			if (oSelectedRadio && oSelectedRadio.value != 1)
			{
				if (!_validate.fnnLandLine(this.value) && _validate.fnnMobile($ID('Contact[Mobile]').value) && this.value !== '')
				{
					this.className = "invalid";
					return "Invalid Phone specified";
				}
				if (_validate.fnnLandLine(this.value) && $ID('Contact[Mobile]').value == '')
				{
					this.className = "valid";
					$ID('Contact[Mobile]').className = "valid";
					return true;
				}
				if (!_validate.fnnLandLine(this.value) && !_validate.fnnMobile($ID('Contact[Mobile]').value))
				{
					this.className = "invalid";
					$ID('Contact[Mobile]').className = "invalid";
					return "Invalid Phone specified";
				}
				this.className = "valid";
				return true;
			}
			else
			{						
				if (!_validate.fnnLandLine(this.value) && _validate.fnnMobile($ID('Contact[Mobile]').value) && this.value !== '')
				{
					this.className = "invalid";
					return true;
				}
				if (_validate.fnnLandLine(this.value) && $ID('Contact[Mobile]').value == '')
				{
					this.className = "valid";
					$ID('Contact[Mobile]').className = "valid";
					return true;
				}
				if (!_validate.fnnLandLine(this.value) && !_validate.fnnMobile($ID('Contact[Mobile]').value))
				{
					this.className = "invalid";
					$ID('Contact[Mobile]').className = "invalid";
					return true;
				}
				this.className = "valid";
				return true;
			}
		}

		// Validate Mobile
		this.oForm.getInputs('text','Contact[Mobile]').first().validate = function ()
		{

			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Contact[USE]"]:checked').first();
			if (oSelectedRadio && oSelectedRadio.value != 1)
			{
				if (!_validate.fnnMobile(this.value) && _validate.fnnLandLine($ID('Contact[Phone]').value) && this.value !== '')
				{
					this.className = "invalid";
					return "Invalid Mobile specified";
				}
				if (_validate.fnnMobile(this.value) && $ID('Contact[Phone]').value == '')
				{
					this.className = "valid";
					$ID('Contact[Phone]').className = "valid";
					return true;
				}
				if (!_validate.fnnMobile(this.value) && !_validate.fnnLandLine($ID('Contact[Phone]').value))
				{
					this.className = "invalid";
					$ID('Contact[Phone]').className = "invalid";
					return "Invalid Mobile specified";
				}
				this.className = "valid";
				return true;
			}
			else
			{
				if (!_validate.fnnMobile(this.value) && _validate.fnnLandLine($ID('Contact[Phone]').value) && this.value !== '')
				{
					this.className = "invalid";
					return true;
				}
				if (_validate.fnnMobile(this.value) && $ID('Contact[Phone]').value == '')
				{
					this.className = "valid";
					$ID('Contact[Phone]').className = "valid";
					return true;
				}
				if (!_validate.fnnMobile(this.value) && !_validate.fnnLandLine($ID('Contact[Phone]').value))
				{
					this.className = "invalid";
					$ID('Contact[Phone]').className = "invalid";
					return true;
				}
				this.className = "valid";
				return true;
			}
		}

		this.oForm.getInputs('text','Contact[Fax]').first().validate = function ()
		{
			if (!_validate.fnnLandLine(this.value) && this.value != '')
			{
				this.className = "invalid";
				return "Invalid Fax specified";
			}
			this.className = "valid";
			return true;
		}
			
		this.oForm.getInputs('text','Contact[Password]').first().validate = function ()
		{
			var	oSelectedRadio	= $ID('account-create').select('input[type=radio][name="Contact[USE]"]:checked').first();
			if (oSelectedRadio && this.value.length < Account_Create.DEFAULT_PASSWORD_LENGTH_REQUIREMENT)
			{
				this.className = "invalid";
				return (oSelectedRadio.value != 1) ? 'Invalid Password, minimum ' + Account_Create.DEFAULT_PASSWORD_LENGTH_REQUIREMENT + ' characters' : true;
			}
			this.className = "valid";
			return true;
		}


		//----------------------------------------------------------------//
		// Setup Event listeners
		//----------------------------------------------------------------//
		
		for (var aInputs = this.oForm.getInputs(), i = 0, j = aInputs.length; i < j; i++)
		{
			if (aInputs[i].validate)
			{
				aInputs[i].observe('keyup', aInputs[i].validate.bind(aInputs[i]));
				aInputs[i].observe('change', aInputs[i].validate.bind(aInputs[i]));
			}
		}
		
		for (var aSelects = this.oForm.select('select'), i = 0, j = aSelects.length; i < j; i++)
		{
			if (aSelects[i].validate)
			{
				aSelects[i].observe('keyup', aSelects[i].validate.bind(aSelects[i]));
				aSelects[i].observe('change', aSelects[i].validate.bind(aSelects[i]));
			}
		}
		
		this._isValid();
		

	},
	
	
	_isValid	: function()
	{
		
		//----------------------------------------------------------------//
		// Run Event listeners
		//----------------------------------------------------------------//
		
		var aErrors	= [];
		for (var aInputs = this.oForm.getInputs(), i = 0, j = aInputs.length; i < j; i++)
		{
			if (aInputs[i].validate)
			{
				var mValid	= aInputs[i].validate();
				if (mValid !== true)
				{
					aErrors.push(mValid);
				}
			}
		}
		for (var aSelects = this.oForm.select('select'), i = 0, j = aSelects.length; i < j; i++)
		{
			if (aSelects[i].validate)
			{
				var mValid	= aSelects[i].validate();
				if (mValid !== true)
				{
					aErrors.push(mValid);
				}
			}
		}
		

		//----------------------------------------------------------------//
		// Validate Proposed Primary Contact
		//----------------------------------------------------------------//	
		
		var	intCheckContactUSE = $ID('account-create').select('input[type=radio][name="Contact[USE]"]:checked').first();
		if(intCheckContactUSE && intCheckContactUSE.value != 0)
		{
			if (isNaN($ID('Contact[Id]').value))
			{
				aErrors.push('Invalid Primary Contact Selected');
			}
		}
	
		//----------------------------------------------------------------//
		// Check Radio Boxes
		//----------------------------------------------------------------//
		
		var	intCheckBillingType = $ID('account-create').select('input[type=radio][name="Account[BillingType]"]:checked').first();
		if(!intCheckBillingType)
		{
			aErrors.push('Invalid Payment Method selected');
		}
		var	intCheckDisableLatePayment = $ID('account-create').select('input[type=radio][name="Account[DisableLatePayment]"]:checked').first();
		if(!intCheckDisableLatePayment)
		{
			aErrors.push('No Late Payment option selected');
		}		
		var	intCheckContact = $ID('account-create').select('input[type=radio][name="Contact[USE]"]:checked').first();
		if(!intCheckContact)
		{
			aErrors.push('Invalid Primary Contact Details');
		}
		
		return aErrors;
		
	},
	
	
	submit	: function()
	{

		//----------------------------------------------------------------//
		// Submits New Account
		//----------------------------------------------------------------//
		
		var aErrors = this._isValid();
		
		if (aErrors.length)
		{
	
			var oErrorTemplate = $T.div({id: 'PopupPageBody'},
			
					$T.div({class: 'MsgNotice'}, 'Please check the following:'),
			
					$T.div({class: 'account-create-errors'})
			        
			);
			var oAccountCreateErrors = oErrorTemplate.select('.account-create-errors').first();
			
			for (var i = 0, j = aErrors.length; i < j; i++)
			{
				oAccountCreateErrors.appendChild($T.div(aErrors[i]));
			}
						
			var oPopup = new Reflex_Popup(40);
			oPopup.setTitle("Error");
			oPopup.addCloseButton();
			oPopup.setIcon("../admin/img/template/user_edit.png");
			oPopup.setContent(oErrorTemplate);
			oPopup.domCloseButton = document.createElement('button');
			oPopup.domCloseButton.style.width = '60px';
			oPopup.domCloseButton.innerHTML = "OK";
			oPopup.domCloseButton.observe('click', oPopup.hide.bind(oPopup));
			oPopup.setFooterButtons([oPopup.domCloseButton], false);
			oPopup.display();

			return false;
		}

		return true;
	
	},
	
	_onConstantLoad : function ()
	{
		this.bConstantsLoaded = true;
	}

});


//----------------------------------------------------------------//
// Constants
//----------------------------------------------------------------//

Account_Create.DEFAULT_PASSWORD_LENGTH_REQUIREMENT	= 8;

