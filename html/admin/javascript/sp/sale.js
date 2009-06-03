
var Sale = Class.create();

// Static class variables are defined here
Object.extend(Sale, {
	instance: null,
	
	getInstance: function()
	{
		return Sale.instance;
	},

	canCreateSale: false,
	canCancelSale: false,
	canAmendSale: false,
	canVerifySale: false,
	canRejectSale: false,

	loading: null,
	
	startLoading: function()
	{
		if (this.loading != null) return;
		this.loading = new Reflex_Popup.Loading();
		this.loading.display();
	},
	
	endLoading: function()
	{
		if (this.loading == null) return;
		this.loading.hide();
		this.loading = null;
	},
	
	historyPopup: null,
	
	hideHistory: function()
	{
		if (Sale.historyPopup != null)
		{
			Sale.historyPopup.hide();
		}
	},
	
	showHistory: function(saleId)
	{
		Sale.hideHistory();
		if (saleId == null)
		{
			return alert("This is a new sale. It has no history.");
		}
		var showHistory = SalesPortal.getRemoteFunction('Sale', 'history', Sale.onHistoryLoad.bind({ saleId: saleId }));
		showHistory(saleId);
	},
	
	onHistoryLoad: function(historyHTML)
	{
		if (Sale.historyPopup == null)
		{
			Sale.historyPopup = new Reflex_Popup(72.2);
			Sale.historyPopup.addCloseButton();
		}
		Sale.historyPopup.setTitle("History of Sale " + this.saleId);
		Sale.historyPopup.setContent(historyHTML);
		Sale.historyPopup.display();
		Sale.historyPopup.recentre();
	}
});

Sale.BillPaymentType = Class.create();
Object.extend(Sale.BillPaymentType, {
	
	BILL_PAYMENT_TYPE_DIRECT_DEBIT:	2,
	BILL_PAYMENT_TYPE_ACCOUNT: 		1

});

Sale.DirectDebitType = Class.create();
Object.extend(Sale.DirectDebitType, {

	DIRECT_DEBIT_TYPE_CREDIT_CARD:	2,
	DIRECT_DEBIT_TYPE_BANK_ACCOUNT:	1

});

Sale.BillDeliveryType	= Class.create();
Object.extend(Sale.BillDeliveryType, {

	BILL_DELIVERY_TYPE_EMAIL:	2,
	BILL_DELIVERY_TYPE_POST:	1

});

Sale.GUIComponent = Class.create();
Object.extend(Sale.GUIComponent, {
	
	unique: 1,
	
	createCreditCardExpiryGroup: function($values, mixIsMandatory, clbValidationFunction, arrValidationEvents)
	{
		// Create Inputs
		var curMonth = $values[0];
		var curYear = $values[1];
		
		var intYear = (new Date()).getYear() + 1900;
		
		var month = document.createElement('select');
		var year = document.createElement('select');
		year.className = month.className = 'data-entry';
		for (var i = 0; i <= 12; i++)
		{
			var option = document.createElement('option');
			option.value = i;
			option.selected = (i == curMonth);
			option.appendChild(document.createTextNode((i == 0) ? 'Month' : i));
			month.appendChild(option);
		}
		var option = document.createElement('option');
		option.value = 0;
		option.appendChild(document.createTextNode('Year'));
		year.appendChild(option);
		for (var i = intYear; i <= (intYear + 15); i++)
		{
			var option = document.createElement('option');
			option.value = i;
			option.selected = (i == curYear);
			option.appendChild(document.createTextNode(i));
			year.appendChild(option);
		}
		
		// Create Element Group
		var disp = document.createElement('span');
		disp.className = 'data-display';

		var wrap = document.createElement('span');
		wrap.className = 'data-entry';
		wrap.appendChild(month);
		wrap.appendChild(document.createTextNode(' / '));
		wrap.appendChild(year);

		mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
		var ret = {
			inputs: new Array(month, year),
			elements: new Array(wrap),
			type: 'credit_card_expiry',
			display: disp,
			mixIsMandatory: mixIsMandatory,
			
			setValue: function($values)
			{
				alert("Set value for credit card expiry has not been implemented.");
			}
		}
		
		// Validation
		ret.isValid			= this.validateElementGroup.bind(ret);
		ret.isValidCustom	= clbValidationFunction;
		if (arrValidationEvents == undefined)
		{
			arrValidationEvents	= new Array('change', 'keyup', 'click');
		}
		for (var i = 0; i < arrValidationEvents.length; i++)
		{
			for (t = 0; t < ret.inputs.length; t++)
			{
				strEvent	= arrValidationEvents[i];
				Event.observe(ret.inputs[t], strEvent, this.validateElementGroup.bindAsEventListener(this));
			}
		}
		
		// Give each Input a reference to it's Group
		for (var i = 0; i < ret.inputs.length; i++)
		{
			ret.inputs[i].objElementGroup	= ret;
		}
		
		// Pre-Validate the Group
		ret.isValid();
		
		Sale.GUIComponent.updateElementGroupDisplay(ret);
		
		return ret;
	},
	
	createDateGroup: function($value, mixIsMandatory, clbValidationFunction, arrValidationEvents)
	{
		// Create Inputs
		var curDay   = parseInt("1" + (($value != null && $value.length == 10) ? $value.substr(8, 2) : "00")) - 100;
		var curMonth = parseInt("1" + (($value != null && $value.length == 10) ? $value.substr(5, 2) : "00")) - 100;
		var curYear  = parseInt(($value != null && $value.length == 10) ? $value.substr(0, 4) : 0);
		
		var intYear = (new Date()).getYear() + 1900;
		
		var day = document.createElement('select');
		var month = document.createElement('select');
		var year = document.createElement('select');
		year.className = month.className = day.className = 'data-entry';
		for (var i = 0; i <= 31; i++)
		{
			var option = document.createElement('option');
			option.value = i;
			option.selected = (i == curDay);
			option.appendChild(document.createTextNode((i == 0) ? 'Day' : i));
			day.appendChild(option);
		}
		for (var i = 0; i <= 12; i++)
		{
			var option = document.createElement('option');
			option.value = i;
			option.selected = (i == curMonth);
			option.appendChild(document.createTextNode((i == 0) ? 'Month' : i));
			month.appendChild(option);
		}
		var option = document.createElement('option');
		option.value = 0;
		option.appendChild(document.createTextNode('Year'));
		year.appendChild(option);
		for (var i = (intYear - 10); i >= (intYear - 100); i--)
		{
			var option = document.createElement('option');
			option.value = i;
			option.selected = (i == curYear);
			option.appendChild(document.createTextNode(i));
			year.appendChild(option);
		}
		
		// Create Element Group
		var disp = document.createElement('span');
		disp.className = 'data-display';

		var wrap = document.createElement('span');
		wrap.className = 'data-entry';
		wrap.appendChild(day);
		wrap.appendChild(document.createTextNode(' / '));
		wrap.appendChild(month);
		wrap.appendChild(document.createTextNode(' / '));
		wrap.appendChild(year);

		mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
		var ret = {
			inputs: new Array(day, month, year),
			elements: new Array(wrap),
			type: 'date',
			display: disp,
			mixIsMandatory: mixIsMandatory,
			
			setValue: function($values)
			{
				alert("Set value for date groups has not been implemented.");
			}
		}
		
		// Validation
		ret.isValid			= this.validateElementGroup.bind(ret);
		ret.isValidCustom	= clbValidationFunction;
		if (arrValidationEvents == undefined)
		{
			arrValidationEvents	= new Array('change', 'keyup', 'click');
		}
		for (var i = 0; i < arrValidationEvents.length; i++)
		{
			for (t = 0; t < ret.inputs.length; t++)
			{
				strEvent	= arrValidationEvents[i];
				Event.observe(ret.inputs[t], strEvent, this.validateElementGroup.bindAsEventListener(this));
			}
		}
		
		// Give each Input a reference to it's Group
		for (var i = 0; i < ret.inputs.length; i++)
		{
			ret.inputs[i].objElementGroup	= ret;
		}
		
		// Pre-Validate the Group
		ret.isValid();
		
		Sale.GUIComponent.updateElementGroupDisplay(ret);
		
		return ret;
	},
	
	createDropDown: function($values, $labels, $value, mixIsMandatory, clbValidationFunction, arrValidationEvents)
	{
		// Create Input
		var dropDown = document.createElement('select');
		dropDown.className = 'data-entry';
		var option = document.createElement('option');
		option.value = '';
		option.appendChild(document.createTextNode('[None]'));
		dropDown.appendChild(option);
		for (var i = 0, l = $values.length; i < l; i++)
		{
			var option = document.createElement('option');
			option.value = $values[i];
			option.selected = ($values[i] == $value);
			option.appendChild(document.createTextNode($labels[i]));
			dropDown.appendChild(option);
		}
		
		// Set Validation
		if (arrValidationEvents == undefined)
		{
			arrValidationEvents	= new Array('change', 'keyup', 'click');
		}
		for (var i = 0; i < arrValidationEvents.length; i++)
		{
			strEvent	= arrValidationEvents[i];
			Event.observe(dropDown, strEvent, this.validateInput.bindAsEventListener(this));
		}
		dropDown.isValid	= clbValidationFunction;
		
		var disp = document.createElement('span');
		disp.className = 'data-display';

		mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
		var ret = {
			inputs: new Array(dropDown),
			elements: new Array(dropDown),
			type: 'select',
			display: disp,
			mixIsMandatory: mixIsMandatory,
			
			setValue: function($value)
			{
				for (var i = 0, l = this.inputs[0].options.length; i < l; i++)
				{
					this.inputs[0].options[i].selected = (this.inputs[0].options[i].value == $value);
				}
			}
		}
		
		// Validation
		ret.isValid			= this.validateElementGroup.bind(ret);
		ret.isValidCustom	= clbValidationFunction;
		if (arrValidationEvents == undefined)
		{
			arrValidationEvents	= new Array('change', 'keyup');
		}
		for (var i = 0; i < arrValidationEvents.length; i++)
		{
			for (t = 0; t < ret.inputs.length; t++)
			{
				strEvent	= arrValidationEvents[i];
				Event.observe(ret.inputs[t], strEvent, this.validateElementGroup.bindAsEventListener(this));
			}
		}
		
		// Give each Input a reference to it's Group
		for (var i = 0; i < ret.inputs.length; i++)
		{
			ret.inputs[i].objElementGroup	= ret;
		}
		
		// Pre-Validate the Group
		ret.isValid();
		
		Sale.GUIComponent.updateElementGroupDisplay(ret);
		
		return ret;
	},
	
	createMultipleSelect: function($values, $labels, $selectedValues, mixIsMandatory, clbValidationFunction, arrValidationEvents)
	{
		// Create Input
		var dropDown = document.createElement('select');
		dropDown.multiple = 'multiple';
		dropDown.className = 'data-entry';
		for (var i = 0, l = $values.length; i < l; i++)
		{
			var option = document.createElement('option');
			option.value = $values[i];
			option.selected = Sale.GUIComponent.__array_contains($values, $value);
			option.appendChild(document.createTextNode($labels[i]));
			dropDown.appendChild(option);
		}

		// Create Element Group
		var disp = document.createElement('p');
		disp.className = 'data-display';

		mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
		var ret = {
			inputs: new Array(dropDown),
			elements: new Array(dropDown),
			type: 'select',
			display: disp,
			mixIsMandatory: mixIsMandatory,
			
			setValue: function($values)
			{
				for (var j = 0, k = $values.length; j < k; j++)
				{
					for (var i = 0, l = this.inputs[0].options.length; i < l; i++)
					{
						this.inputs[0].options[i].selected = (this.inputs[0].options[i].value == $values[j]);
						if (this.inputs[0].options[i].selected) break; 
					}
				}
			}
		}
		
		// Validation
		ret.isValid			= this.validateElementGroup.bind(ret);
		ret.isValidCustom	= clbValidationFunction;
		if (arrValidationEvents == undefined)
		{
			arrValidationEvents	= new Array('change', 'keyup', 'click');
		}
		for (var i = 0; i < arrValidationEvents.length; i++)
		{
			for (t = 0; t < ret.inputs.length; t++)
			{
				strEvent	= arrValidationEvents[i];
				Event.observe(ret.inputs[t], strEvent, this.validateElementGroup.bindAsEventListener(this));
			}
		}
		
		// Give each Input a reference to it's Group
		for (var i = 0; i < ret.inputs.length; i++)
		{
			ret.inputs[i].objElementGroup	= ret;
		}
		
		// Pre-Validate the Group
		ret.isValid();
		
		Sale.GUIComponent.updateElementGroupDisplay(ret);
		
		return ret;
	},
	
	__array_contains: function($array, $value)
	{
		for (var i = 0, l = $array.length; i < l; i++)
		{
			if ($array[i] == $value)
			{
				return true;
			}
		}
		return false;
	},
	
	createTextInputGroup: function($value, mixIsMandatory, clbValidationFunction, arrValidationEvents)
	{
		// Create the Input
		var input = document.createElement('input');
		input.type = 'text';
		input.value = $value;
		input.className = 'data-entry';

		// Create Element Group
		var disp = document.createElement('span');
		disp.className = 'data-display';
		
		mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
		var ret = {
			inputs: new Array(input),
			elements: new Array(input),
			type: 'text',
			display: disp,
			mixIsMandatory: mixIsMandatory,
			
			setValue: function($value)
			{
				this.inputs[0].value = $value;
			}
		}
		
		// Validation
		ret.isValid			= this.validateElementGroup.bind(ret);
		ret.isValidCustom	= clbValidationFunction;
		if (arrValidationEvents == undefined)
		{
			arrValidationEvents	= new Array('change', 'keyup');
		}
		for (var i = 0; i < arrValidationEvents.length; i++)
		{
			for (t = 0; t < ret.inputs.length; t++)
			{
				strEvent	= arrValidationEvents[i];
				Event.observe(ret.inputs[t], strEvent, this.validateElementGroup.bindAsEventListener(this));
			}
		}
		
		// Give each Input a reference to it's Group
		for (var i = 0; i < ret.inputs.length; i++)
		{
			ret.inputs[i].objElementGroup	= ret;
		}
		
		// Pre-Validate the Group
		ret.isValid();
		
		Sale.GUIComponent.updateElementGroupDisplay(ret);
		
		return ret;
	},
	
	createPasswordInputGroup: function($value, mixIsMandatory, clbValidationFunction, arrValidationEvents)
	{
		// Create Inputs
		var grp = document.createElement('span');
		grp.className = 'data-entry';
		
		var input = document.createElement('input');
		input.type = 'password';
		input.value = $value;
		grp.appendChild(input);

		var txtConf = document.createTextNode('Confirm:');
		grp.appendChild(txtConf);

		var conf = document.createElement('input');
		conf.type = 'password';
		conf.value = $value;
		grp.appendChild(conf);
		
		// Create Element Group
		var disp = document.createElement('span');
		disp.className = 'data-display';
		disp.appencChild(document.createTextNode('[hidden]'));

		mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
		var ret = {
			inputs: new Array(input),
			elements: new Array(grp),
			type: 'password',
			display: disp,
			mixIsMandatory: mixIsMandatory,
			
			setValue: function($value)
			{
				this.inputs[0].value = $value;
			}
		}
		
		// Validation
		ret.isValid			= this.validateElementGroup.bind(ret);
		ret.isValidCustom	= clbValidationFunction;
		if (arrValidationEvents == undefined)
		{
			arrValidationEvents	= new Array('change', 'keyup');
		}
		for (var i = 0; i < arrValidationEvents.length; i++)
		{
			for (t = 0; t < ret.inputs.length; t++)
			{
				strEvent	= arrValidationEvents[i];
				Event.observe(ret.inputs[t], strEvent, this.validateElementGroup.bindAsEventListener(this));
			}
		}
		
		// Give each Input a reference to it's Group
		for (var i = 0; i < ret.inputs.length; i++)
		{
			ret.inputs[i].objElementGroup	= ret;
		}
		
		// Pre-Validate the Group
		ret.isValid();
		
		Sale.GUIComponent.updateElementGroupDisplay(ret);
		
		return ret;
	},
	
	createTextareaGroup: function($value, mixIsMandatory, clbValidationFunction, arrValidationEvents)
	{
		// Create Input
		var textarea = document.createElement('textarea');
		textarea.value = $value;
		textarea.className = 'data-entry';

		// Create Element Group
		var disp = document.createElement('textarea');
		disp.disabled = true;
		disp.style.color = '#000';
		disp.style.backgroundColor = '#fff';
		disp.className = 'data-display';

		mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
		var ret = {
			inputs: new Array(textarea),
			elements: new Array(textarea),
			type: 'textarea',
			display: disp,
			mixIsMandatory: mixIsMandatory,
			
			setValue: function($value)
			{
				this.inputs[0].value = $value;
			}
		}
		
		// Validation
		ret.isValid			= this.validateElementGroup.bind(ret);
		ret.isValidCustom	= clbValidationFunction;
		if (arrValidationEvents == undefined)
		{
			arrValidationEvents	= new Array('change', 'keyup');
		}
		for (var i = 0; i < arrValidationEvents.length; i++)
		{
			for (t = 0; t < ret.inputs.length; t++)
			{
				strEvent	= arrValidationEvents[i];
				Event.observe(ret.inputs[t], strEvent, this.validateElementGroup.bindAsEventListener(this));
			}
		}
		
		// Give each Input a reference to it's Group
		for (var i = 0; i < ret.inputs.length; i++)
		{
			ret.inputs[i].objElementGroup	= ret;
		}
		
		// Pre-Validate the Group
		ret.isValid();
		
		Sale.GUIComponent.updateElementGroupDisplay(ret);
		
		return ret;
	},
	
	createRadioButtonsGroup: function($values, $labels, $value, mixIsMandatory, clbValidationFunction, arrValidationEvents)
	{
		// Create Inputs
		var radios = new Array();
		var uniqueName = 'name_' + Sale.GUIComponent.unique;
		Sale.GUIComponent.unique++;
		var all = new Array();
		for (var i = 0, l = $values.length; i < l; i++)
		{
			var radio = document.createElement('input');
			var lab = document.createElement('label');
			radio.type = 'radio';
			radio.value = $values[i];
			radio.checked = ($values[i] == $value)
			radio.setAttribute('label', $labels[i]);
			radio.setAttirbute('name', uniqueName);
			radio.className = 'data-entry';
			lab.setAttribute('for', uniqueName);
			lab.id = uniqueName;
			lab.className = 'data-entry';
			radios[i] = radio;
			all[i*2] = radio;
			all[(i*2)+1] = lab;
		}
		
		// Create Element Group
		var disp = document.createElement('span');
		disp.className = 'data-display';

		mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
		var ret = {
			inputs: radios,
			elements: all,
			type: 'radio',
			display: disp,
			mixIsMandatory: mixIsMandatory,
			
			setValue: function($value)
			{
				for (var i = 0, l = $group.inputs.length; i < l; i++)
				{
					this.inputs[i].checked = (this.inputs[i].value == $value);
				}
			}
		}
		
		// Validation
		ret.isValid			= this.validateElementGroup.bind(ret);
		ret.isValidCustom	= clbValidationFunction;
		if (arrValidationEvents == undefined)
		{
			arrValidationEvents	= new Array('change', 'keyup');
		}
		for (var i = 0; i < arrValidationEvents.length; i++)
		{
			for (t = 0; t < ret.inputs.length; t++)
			{
				strEvent	= arrValidationEvents[i];
				Event.observe(ret.inputs[t], strEvent, this.validateElementGroup.bindAsEventListener(this));
			}
		}
		
		// Give each Input a reference to it's Group
		for (var i = 0; i < ret.inputs.length; i++)
		{
			ret.inputs[i].objElementGroup	= ret;
		}
		
		// Pre-Validate the Group
		ret.isValid();
		
		Sale.GUIComponent.updateElementGroupDisplay(ret);
		
		return ret;
	},
	
	createCheckboxGroup: function($checked, mixIsMandatory, clbValidationFunction, arrValidationEvents)
	{
		// Create Input
		var checkbox = document.createElement('input');
		checkbox.type = 'checkbox';
		checkbox.checked = $checked;
		checkbox.className = 'data-entry';
		
		// Create Element Group
		var disp = document.createElement('span');
		disp.className = 'data-display';

		mixIsMandatory	= ((mixIsMandatory == undefined) ? false : mixIsMandatory);
		var ret = {
			inputs: new Array(checkbox),
			elements: new Array(checkbox),
			type: 'checkbox',
			display: disp,
			mixIsMandatory: mixIsMandatory,
			
			setValue: function($value)
			{
				this.inputs[0].checked = $value;
			}
		}
		
		// Validation
		ret.isValid			= this.validateElementGroup.bind(ret);
		ret.isValidCustom	= clbValidationFunction;
		if (arrValidationEvents == undefined)
		{
			arrValidationEvents	= new Array('change', 'keyup');
		}
		for (var i = 0; i < arrValidationEvents.length; i++)
		{
			for (t = 0; t < ret.inputs.length; t++)
			{
				strEvent	= arrValidationEvents[i];
				Event.observe(ret.inputs[t], strEvent, this.validateElementGroup.bindAsEventListener(this));
			}
		}
		
		// Give each Input a reference to it's Group
		for (var i = 0; i < ret.inputs.length; i++)
		{
			ret.inputs[i].objElementGroup	= ret;
		}
		
		// Pre-Validate the Group
		ret.isValid();
		
		Sale.GUIComponent.updateElementGroupDisplay(ret);
		
		return ret;
	},
	
	validateInput: function(objValidator)
	{
		var objInput, strValue, mixIsMandatory;
		if (objValidator == undefined || objValidator == null)
		{
			objInput	= this;
		}
		else if (typeof objValidator == 'object' && objValidator.target != undefined)
		{
			objInput	= objValidator.currentTarget;
		}
		else
		{
			objInput	= objValidator;
		}
		
		// Remove any valid/invalid classes from the Input
		objInput.removeClassName('invalid');
		objInput.removeClassName('valid');
		
		// Convert Value to a string, then strip the whitespace 
		strValue	= String(objInput.value);
		strValue.strip();

		//alert("Validating Input with Value '"+strValue+"'");
		mixIsMandatory	= (objInput.objElementGroup.mixIsMandatory == undefined) ? false : objInput.objElementGroup.mixIsMandatory;
		
		// Is there a validation method set?
		var bolValid	= (objInput.isValid == undefined) ? true : objInput.isValid(objInput.value);

		// Mandatory?
		if (strValue.length == 0 && mixIsMandatory)
		{
			bolValid	= false;
		}
		
		// Set Style
		if (strValue.length > 0 || mixIsMandatory)
		{
			if (!bolValid)
			{
				objInput.addClassName('invalid');
			}
			else
			{
				objInput.addClassName('valid');
			}
		}
		else
		{
			bolValid			= true;
		}
		
		return bolValid;
	},
	
	validateElementGroup: function(objValidator)
	{
		if (objValidator == undefined || objValidator == null)
		{
			objElementGroup	= this;
		}
		else if (typeof objValidator == 'object' && objValidator.target != undefined)
		{
			objElementGroup	= objValidator.currentTarget.objElementGroup;
		}
		else
		{
			objElementGroup	= objValidator;
		}
		//alert("Validating ElementGroup "+objElementGroup);

		// Remove any valid/invalid classes from the Inputs
		for (var i = 0; i < objElementGroup.inputs.length; i++)
		{
			objElementGroup.inputs[i].removeClassName('invalid');
			objElementGroup.inputs[i].removeClassName('valid');
		}
		
		// Convert Value to a string, then strip the whitespace
		mixValue	= Sale.GUIComponent.getElementGroupValue(objElementGroup);
		strValue	= (mixValue === null) ? '' : String(mixValue).strip();
		
		// If mixIsMandatory is a function, then call it
		var bolIsMandatory;
		if ((typeof objElementGroup.mixIsMandatory) === 'function')
		{
			//alert('mixIsMandatory is a function!');
			bolIsMandatory	= objElementGroup.mixIsMandatory();
		}
		else if (objElementGroup.mixIsMandatory == undefined)
		{
			bolIsMandatory	= false;
		}
		else
		{
			bolIsMandatory	= objElementGroup.mixIsMandatory;
		}
		
		// Is there a validation method set?
		var bolValid	= (objElementGroup.isValidCustom == undefined) ? true : objElementGroup.isValidCustom(strValue);

		if (Sale.GUIComponent.isElementGroupDisabled(objElementGroup))
		{
			// Something can't be considered manditory if it is disabled, right?
			// I can't help but think this bold generalisation will bite me in the arse
			bolIsMandatory = false;
		}

		// Mandatory?
		if (strValue.length == 0)
		{
			bolValid = !bolIsMandatory;
		}
		
		// Set Style
		strNewStyle	= null;
		if (strValue.length > 0 || bolIsMandatory)
		{
			if (!bolValid)
			{
				strNewStyle	= 'invalid';
			}
			else
			{
				strNewStyle	= 'valid';
			}
		}
		
		// Apply Styles to all inputs
		var bolDisabled	= true;
		if (strNewStyle)
		{
			for (var i = 0; i < objElementGroup.inputs.length; i++)
			{
				objElementGroup.inputs[i].addClassName(strNewStyle);
				bolDisabled	= (objElementGroup.inputs[i].disabled) ? bolDisabled : false;
			}
		}
		
		// If it's disabled, then it is Valid (all inputs must be disabled)
		return (bolDisabled) ? true : bolValid;
	},
	
	appendElementGroup: function($container, $group)
	{
		for (var i = 0, l = $group.elements.length; i < l; i++)
		{
			$container.appendChild($group.elements[i]);
			$container.appendChild($group.display);
		}
	},
	
	appendElementGroupToTable: function($table, $label, $group)
	{
		$group.row = $table.insertRow(-1);
		var cell = $group.row.insertCell(-1);
		cell.appendChild(document.createTextNode($label + ":"));
		cell = $group.row.insertCell(-1);
		Sale.GUIComponent.appendElementGroup(cell, $group);
	},
	
	updateElementGroupDisplay: function($group)
	{
		switch ($group.type)
		{
			case 'checkbox':
				$group.display.innerHTML = ($group.inputs[0].checked ? 'Yes' : 'No');
				break;
				
			case 'password':
				break;
				
			case 'text':
				$group.display.innerHTML = '';
				$group.display.appendChild(document.createTextNode($group.inputs[0].value));
				break;

			case 'textarea':
				$group.display.value = $group.inputs[0].value;
				break;

			case 'radio':
				$group.display.innerHTML = '';
				for (var i = 0, l = $group.inputs.length; i < l; i++)
				{
					if ($group.inputs[i].checked) 
					{
						$group.display.appendChild(document.createTextNode(document.getElementById($group.inputs[i].name).innerText));
					}
				}
				break;
				
			case 'date':
				$group.display.innerHTML = '';
				var date = $group.inputs[0].options[$group.inputs[0].selectedIndex].value;
				date += " / ";
				date += $group.inputs[1].options[$group.inputs[1].selectedIndex].value;
				date += " / ";
				date += $group.inputs[2].options[$group.inputs[2].selectedIndex].value;
				$group.display.appendChild(document.createTextNode(date));
				break;

			case 'credit_card_expiry':
				$group.display.innerHTML = '';
				var date = $group.inputs[0].options[$group.inputs[0].selectedIndex].value;
				date += " / ";
				date += $group.inputs[1].options[$group.inputs[1].selectedIndex].value;
				$group.display.appendChild(document.createTextNode(date));
				break;

			case 'select':
				$group.display.innerHTML = '';
				for (var i = 0, l = $group.inputs[0].options.length; i < l; i++)
				{
					if ($group.inputs[0].options[i].selected) 
					{
						$group.display.appendChild(document.createTextNode($group.inputs[0].options[i].textContent));
						break;
					}
				}
				break;
				
			case 'multiple':
				$group.display.innerHTML = '';
				var matched = false;
				for (var i = 0, l = $group.inputs[0].options.length; i < l; i++)
				{
					if ($group.inputs[0].options[i].selected) 
					{
						if (matched) $group.display.appendChild(document.createElement('br'));
						$group.display.appendChild(document.createTextNode($group.inputs[0].options[i].textContent));
						matched = true;
					}
				}
				return values;
			default:
				return null;
		}
	},
	
	getElementGroupValue: function($group)
	{
		if ($group == undefined)
		{
			return null;
		}
		
		Sale.GUIComponent.updateElementGroupDisplay($group);
		switch ($group.type)
		{
			case 'checkbox':
				return $group.inputs[0].checked;
			case 'password':
				if ($group.inputs[0].value != $group.inputs[1].value) 
				{
					return null;
				}
			case 'text':
			case 'textarea':
				return $group.inputs[0].value;
			case 'radio':
				for (var i = 0, l = $group.inputs.length; i < l; i++)
				{
					if ($group.inputs[i].checked) 
					{
						return $group.inputs[i].value;
					}
				}
				return null;
			case 'select':
				for (var i = 0, l = $group.inputs[0].options.length; i < l; i++)
				{
					if ($group.inputs[0].options[i].selected) 
					{
						return $group.inputs[0].options[i].value;
					}
				}
				return null;
			case 'multiple':
				var values = new Array();
				for (var i = 0, l = $group.inputs[0].options.length; i < l; i++)
				{
					if ($group.inputs[0].options[i].selected) 
					{
						values[values.length] = $group.inputs[0].options[i].value;
					}
				}
				return values;
			case 'date':
				var year	= $group.inputs[2].options[$group.inputs[2].selectedIndex].value;
				var month	= $group.inputs[1].options[$group.inputs[1].selectedIndex].value;
				var day		= $group.inputs[0].options[$group.inputs[0].selectedIndex].value;
				if (parseInt(year) || parseInt(month) || parseInt(day))
				{
					date = year;
					date += "-";
					if (month.length == 1) month = '0' + month;
					date += month;
					date += "-";
					if (day.length == 1) day = '0' + day;
					date += day;
					return date;
				}
				return null;
				
			case 'credit_card_expiry':
				if (parseInt($group.inputs[0].options[$group.inputs[0].selectedIndex].value) && parseInt($group.inputs[1].options[$group.inputs[1].selectedIndex].value))
				{
					return new Array($group.inputs[0].options[$group.inputs[0].selectedIndex].value, $group.inputs[1].options[$group.inputs[1].selectedIndex].value);
				}
				return null;
			default:
				return null;
		}
	},
	
	// if a group has multiple input elements, it is considered to only be disabled if ALL of the input elements are disabled
	isElementGroupDisabled: function(group)
	{
		switch (group.type)
		{
			case 'checkbox':
			case 'radio':
			case 'text':
			case 'textarea':
			case 'select':
			case 'multiple':
			case 'password':
			case 'date':
			case 'credit_card_expiry':
				for (var i=0, l=group.inputs.length; i<l; i++)
				{
					if (!group.inputs[i].disabled) 
					{
						return false;
					}
				}
				return true;

			default:
				return null;
		}
	},
	
	disableElementGroup: function(group, bolNullifyValue)
	{
		if (bolNullifyValue == undefined)
		{
			bolNullifyValue = false;
		}
		
		switch (group.type)
		{
			case 'checkbox':
				if (bolNullifyValue)
				{
					group.inputs[0].checked = false;
				}
				group.inputs[0].disabled = true;
				break;
			case 'text':
			case 'textarea':
				if (bolNullifyValue)
				{
					group.inputs[0].value = '';
				}
				group.inputs[0].disabled = true;
				break;
			case 'select':
				if (bolNullifyValue)
				{
					group.inputs[0].selectedIndex = 0;
				}
				group.inputs[0].disabled = true;
				break;
			case 'multiple':
				if (bolNullifyValue)
				{
					group.inputs[0].selectedIndex = -1;
				}
				group.inputs[0].disabled = true;
				break;
			case 'password':
				if (bolNullifyValue)
				{
					group.inputs[0].value = '';
					group.inputs[1].value = '';
				}
				group.inputs[0].disabled = true;
				group.inputs[1].disabled = true;
				break;
			case 'date':
				if (bolNullifyValue)
				{
					group.inputs[0].selectedIndex = 0;
					group.inputs[1].selectedIndex = 0;
					group.inputs[2].selectedIndex = 0;
				}
				group.inputs[0].disabled = true;
				group.inputs[1].disabled = true;
				group.inputs[2].disabled = true;
				break;
			
			case 'credit_card_expiry':
				if (bolNullifyValue)
				{
					group.inputs[0].selectedIndex = 0;
					group.inputs[1].selectedIndex = 0;
				}
				group.inputs[0].disabled = true;
				group.inputs[1].disabled = true;
				break;
			case 'radio':
				for (var i=0, l=group.inputs.length; i<l; i++)
				{
					if (bolNullifyValue)
					{
						group.inputs[i].checked = false;
					}
					group.inputs[i].disabled = true;
				}
				break;
		}
	},
	
	enableElementGroup: function(group)
	{
		switch (group.type)
		{
			case 'checkbox':
			case 'text':
			case 'textarea':
			case 'select':
			case 'multiple':
			case 'password':
			case 'date':
			case 'credit_card_expiry':
			case 'radio':
				for (var i=0, l=group.inputs.length; i<l; i++)
				{
					group.inputs[i].disabled = false;
				}
				break;
		}
	}
});

Object.extend(Sale.GUIComponent.prototype, {
	
	detailsContainer: null,
	summaryContainer: null,
	
	elementGroups: null,
	
	setContainers: function(detailsContainer, summaryContainer)
	{
		this.detailsContainer = detailsContainer;
		this.summaryContainer = summaryContainer;
		this.buildGUI();
	},
	
	renderDetails: function(readOnly)
	{
		
	},
	
	renderSummary: function(readOnly)
	{
		
	},
	
	updateDisplay: function($readOnly)
	{
		if (this.elementGroups != null)
		{
			for (var i in this.elementGroups)
			{
				var group = this.elementGroups[i];
				if (typeof group == 'object' && group.inputs && typeof group.inputs == 'array' && group.elements && typeof group.elements == 'array' && group.type && typeof group.type == 'string')
				{
					Sale.GUIComponent.updateElementGroupDisplay(group);
				}
			}
		}
		
		this.updateChildObjectsDisplay($readOnly);
	},
	
	updateChildObjectsDisplay: function($readOnly)
	{
		
	},
	
	destroy: function()
	{
		
	},
	
	buildGUI: function()
	{
		
	}
});

// Member variables are defined here
Object.extend(Sale.prototype, Sale.GUIComponent.prototype);
Object.extend(Sale.prototype, {

	object: null,
	
	newSale: false,
	
	initialize: function(container, saleId, initialStyle)
	{
		Sale.startLoading();
		
		this.detailsContainer = container;
		
		document.body.originalClassName = document.body.className;
		document.body.className = document.body.originalClassName + " " + initialStyle;

		Sale.instance = this;
		this.saleItems = [];
		this.elementGroups = {};
		this.contacts = [];

		if (saleId == undefined || saleId == null)
		{
			this.newSale = true;

			this.buildPageForObject({
				id: null,
				sale_type_id: 1, //null, // FK to sale_type table
				sale_status_id: null,
				created_on: null,
				created_by: null, // dealer_id - FK to dealer table
				commission_paid_on: null,
	
				sale_account: null,
				
				contacts: [], // List of contacts (will only contain 1 for version 1 of sales portal)
				
				items: [] // List of items
			});
		}
		else
		{
			var onLoadFunc = this.buildPageForObject.bind(this);
			var remote = SalesPortal.getRemoteFunction('Sale', 'load', onLoadFunc);
			remote(saleId);
		}
	},
	
	buildPageForObject: function(object)
	{
		this.object = object;

	//	document.body.className = document.body.originalClassName + " data-entry";
		
		this.getSaleAccount();
		this.loadContacts();
		this.loadItems();
		
		this.setContainers(this.detailsContainer);
	},
	
	// Validates the details client side and then submits the details to the server for validation
	submit: function()
	{
		window.scroll(0,0);
		if (this.isValid())
		{
			document.body.className = document.body.originalClassName + " data-display";
			$ID('submit-button-panel').style.display = 'none';
			$ID('commit-button-panel').style.display = 'none';	
			$ID('after-commit-button-panel').style.display = 'none';
			$ID('amend-button-panel').style.display = 'none';
			var submit = SalesPortal.getRemoteFunction('Sale', 'submit', this._submitOK.bind(this), this._submitError.bind(this));
			submit(this.object);
		}
		else
		{
			alert("Please correct all errors (fields in red) and try again.");
		}
	},
	
	_submitOK: function()
	{
		window.scroll(0,0);
		$ID('submit-button-panel').style.display = 'none';
		$ID('commit-button-panel').style.display = 'inline';
		$ID('after-commit-button-panel').style.display = 'none';
		$ID('amend-button-panel').style.display = 'none';
		alert("Please check that all details are correct and then click 'Commit' to save or 'Edit' to make corrections.");
	},
	
	_submitError: function($return)
	{
		window.scroll(0,0);
		document.body.className = document.body.originalClassName + " data-entry";
		$ID('submit-button-panel').style.display = 'inline';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'none';
		$ID('amend-button-panel').style.display = 'none';
		alert($return['ERROR']);
	},
	
	// Confirms the sale and submits the details to the server to be saved
	commit: function()
	{
		window.scroll(0,0);
		document.body.className = document.body.originalClassName + " data-display";
		$ID('submit-button-panel').style.display = 'none';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'none';
		$ID('amend-button-panel').style.display = 'none';
		var submit = SalesPortal.getRemoteFunction('Sale', 'confirm', this._commitOK.bind(this), this._submitError.bind(this));
		submit(this.object);
	},
	
	cancel: function()
	{
		window.scroll(0,0);
		document.body.className = document.body.originalClassName + " data-entry";
		$ID('submit-button-panel').style.display = 'inline';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'none';
		$ID('amend-button-panel').style.display = 'none';
	},
	
	addNewSale: function()
	{
		document.location = document.location.toString().replace(/\/portal\/.*/i, '/portal/sale');
	},
	
	amendSale: function()
	{
		return this.cancel();
	},
	
	cancelAmend: function()
	{
		// Must reload the page as the sale object may have been amended and we need to show the original version
		document.location.reload();
		return;
	},
	
	_commitOK: function($saleId)
	{
		window.scroll(0,0);
		$ID('submit-button-panel').style.display = 'none';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'inline';
		alert("The sale has been saved. The reference number for this sale is " + $saleId + ".");
		if (this.isNewSale()) document.location = document.location.toString().replace(/\/portal\/.*/i, '/portal/sales/view/last');
	},
	
	cancelSale: function()
	{
		this._remoteSaleFunctionCall('cancelSale', this._cancelOK);
	},
	
	_cancelOK: function()
	{
		window.scroll(0,0);
		alert("The sale has been cancelled.");
		document.location = document.location.toString().replace(/\/portal\/.*/i, '/portal/sales/view/last');
	},
	
	rejectSale: function()
	{
		this._remoteSaleFunctionCall('rejectSale', this._rejectOK);
	},
	
	_rejectOK: function()
	{
		window.scroll(0,0);
		alert("The sale has been rejected.");
		document.location = document.location.toString().replace(/\/portal\/.*/i, '/portal/sales/view/last');
	},
	
	verifySale: function()
	{
		this._remoteSaleFunctionCall('verifySale', this._verifyOK);
	},
	
	_verifyOK: function()
	{
		window.scroll(0,0);
		alert("The sale has been verified.");
		document.location = document.location.toString().replace(/\/portal\/.*/i, '/portal/sales/view/last');
	},
	
	_remoteSaleFunctionCall: function($remoteFunName, $okFunc)
	{
		window.scroll(0,0);
		$ID('submit-button-panel').style.display = 'none';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'none';
		var remote = SalesPortal.getRemoteFunction('Sale', $remoteFunName, $okFunc.bind(this), this._processError.bind(this));
		remote(Sale.getInstance().getId());
	},
	
	_processError: function($return)
	{
		window.scroll(0,0);
		$ID('submit-button-panel').style.display = 'none';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'inline';
		alert($return['ERROR']);
	},
	

 	isNewSale: function()
	{
		return this.newSale;
	},
	
	buildGUI: function()
	{
		var buttons = (Sale.canAmendSale ? '&nbsp;<input type="button" value="Amend Sale" onclick="Sale.getInstance().amendSale()">&nbsp;' : '') +
					  (Sale.canCancelSale ? '&nbsp;<input type="button" value="Cancel Sale" onclick="Sale.getInstance().cancelSale()">&nbsp;' : '') +
					  (Sale.canRejectSale ? '&nbsp;<input type="button" value="Reject Sale" onclick="Sale.getInstance().rejectSale()">&nbsp;' : '') +
					  (Sale.canVerifySale ? '&nbsp;<input type="button" value="Verify Sale" onclick="Sale.getInstance().verifySale()">&nbsp;' : '');
		
		// Add contents to this.detailsContainer
		this.detailsContainer.innerHTML = '' 
		+ '<div class="Page">' 
			+ (this.isNewSale() ? '' : '<span onclick="Sale.showHistory(' + this.getId() + ')"><a href="javascript:void(0)">View&nbsp;Sale&nbsp;History</a></span>') 
			+ '</div><div class="FieldContent" align="right">' 
				+ 'Sale Type:' 
				+ '<select name="SaleType">' 
					+ '<option value="New">New</option>' 
			//		+ '<option value="Existing" DISABLED>Existing</option>' 
			//		+ '<option value="Winback" DISABLED>Winback</option>' 
				+ '</select>' 
			+ '</div>' 
		+ '</div>' 
		+ '<div class="MediumSpace"></div>' 
		+ '<table cellpadding="0" cellspacing="0" border="0" width="975">' 
			+ '<tr>' 
				+ '<td width="480">' 
					+ '<div class="PartTitle">Account Details</div>' 
						+ '<div class="PartPage" id="account_details_holder"></div>' 
					+ '</td>' 
					+ '<td width="15"></td>' 
					+ '<td width="480">' 
						+ '<div class="PartTitle">Primary Contact Details</div>' 
						+ '<div class="PartPage" id="primary_contact_details_holder"></div>' 
				+ '</td>' 
			+ '</tr>' 
		+ '</table>' 
		+ '<div class="data-entry">'
			+ '<div class="MediumSpace"></div>' 
			+ '<div class="Title">Available Products</div>' 
			+ '<div class="Page">' 
				+ '<div class="FieldContent">' 
					+ '<TABLE cellpadding="0" cellspacing="0" border="0">' 
						+ '<TR>' 
							+ '<TD>' 
								+ '<div id="divProductType">' 
									+ '<select name="product_type_id" id="sale_product_type_list">' 
										+ '<option value="">Product Type</option>' 
									+ '</select>' 
								+ '</div>' 
							+ '</TD>' 
							+ '<TD>' 
								+ '<div id="divProductList">' 
									+ '<select name="product_id" id="sale_product_list">' 
										+ '<option value="">Product</option>' 
									+ '</select>' 
								+ '</div>' 
							+ '</TD>' 
							+ '<td><input type="button" value="Add Item" onclick="Sale.getInstance().addSaleItem();" /></td>' 
						+ '</TR>' 
					+ '</TABLE>' 
				+ '</div>' 
			+ '</div>' 
		+ '</div>' 
		+ '<div class="MediumSpace"></div>' 
		+ '<div style="position: relative;" class="Title">Sale Items<input type="button" value="Collapse All" style="position: absolute; right: 0px; bottom: -1px;" onclick="if (this.value == \'Collapse All\') { this.value = \'Expand All\'; Sale.Item.collapseAll();} else { this.value = \'Collapse All\'; Sale.Item.expandAll();} " /></div>' 
		+ '<div class="Page">' 
			+ '<div class="FieldContent" style="padding:0; margin:0;">' 
				+ '<table id="sale-items-table" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse; margin: 0; padding: 0; width:100%;">' 
				+ '</table>' 
			+ '</div>' 
		+ '</div>' 
		+ '<div class="MediumSpace"></div>' 
		+ '<div class="Title">Billing Details</div>' 
		+ '<div class="Page">'
				+ '<div><TABLE cellpadding="0" cellspacing="0" border="0">' 
					+ '<TR>' 
						+ '<TD>' 
							+ '<table id="bill-delivery-type-table"></table>' 
						+ '</TD>' 
						+ '<TD' + (Sale.canAmendSale ? ' class="read-only"' : '') + '>' 
							+ '<table id="bill-payment-type-table"></table>' 
						+ '</TD>' 
						+ '<TD' + (Sale.canAmendSale ? ' class="read-only"' : '') + '>' 
							+ '<table id="direct-debit-type-table"></table>'
						+ '</TD>' 
					+ '</TR>' 
				+ '</TABLE></div>' 
			+ '<table cellpadding="0" cellspacing="0" border="0" id="direct-debit-detail-table" class="data-table' + (Sale.canAmendSale ? ' read-only' : '') + '"></table>' 
		+ '</div>' 
		+ '<div class="MediumSpace"></div>'
		+ '<span id="amend-button-panel" class="data-display">&nbsp;<input type="button" value="Add New Sale" onclick="Sale.getInstance().addNewSale();">&nbsp;' + buttons + '</span>'
		+ '<span id="submit-button-panel" class="data-entry"><input type="button" value="Submit" onclick="Sale.getInstance().submit();">' 
		+ (Sale.canAmendSale ? '&nbsp;&nbsp;<input type="button" value="Cancel" onclick="Sale.getInstance().cancelAmend()">' : '')
		+ '</span>'
		+ '<span id="commit-button-panel"><input type="button" value="Commit" onclick="Sale.getInstance().commit()">&nbsp;&nbsp;<input type="button" value="Edit" onclick="Sale.getInstance().cancel()"></span>'
		+ '<span id="after-commit-button-panel">&nbsp;<input type="button" value="Add New Sale" onclick="Sale.getInstance().addNewSale();">&nbsp;' + buttons + '</span>'
		
		
		// WIP: This is for debug purposes only!!! Remove it before deployment!
		//+ '<br/><br/><input type="button" value="Toggle Entry/Display" onclick="$ID(\'submit-button-panel\').style.display = (document.body.className == \'data-display\' ? \'inline\' : \'none\'); $ID(\'commit-button-panel\').style.display = (document.body.className == \'data-display\' ? \'none\' : \'inline\'); document.body.className = (document.body.className == \'data-display\' ? \'data-entry\' : \'data-display\')">'
		
		
		+ '';
		
		var startInEditMode = (this.object.id == null);
		
		$ID('submit-button-panel').style.display = !startInEditMode ? 'none' : 'inline';
		$ID('commit-button-panel').style.display = 'none';
		$ID('after-commit-button-panel').style.display = 'none';
		$ID('amend-button-panel').style.display = !startInEditMode ? 'inline' : 'none';
		
		var saleAccount = this.getSaleAccount();
		saleAccount.setContainers($ID('account_details_holder'));
		
		var contacts = this.getContacts();
		if (contacts.length == 0)
		{
			this.addContact();
		}
		contacts[0].setContainers($ID('primary_contact_details_holder'));
		
		for (var i = 0, l = this.saleItems.length; i < l; i++)
		{
			this.addSaleItem(this.saleItems[i]);
		}
		
		Event.observe($ID('sale_product_type_list'), 'change', this.changeProductType.bind(this), true);

		Sale.endLoading();
	},





	remote$listProductTypesForVendor: null,
	remote$listProductsForProductTypeModuleAndVendor: null,
	
	changeVendor: function()
	{
		this.populateProductTypes({ids: [], labels: []})

		var vendorId = Sale.GUIComponent.getElementGroupValue(this.getSaleAccount().elementGroups.vendor);
		if (vendorId == 0 || vendorId == '' || vendorId == null) return; 

		if (this.remote$listProductTypesForVendor == null)
		{
			var onSuccess = this.populateProductTypes.bind(this);
			this.remote$listProductTypesForVendor = SalesPortal.getRemoteFunction('Sale', 'listProductTypesForVendor', onSuccess);
		}
		this.remote$listProductTypesForVendor(vendorId);
		
		// Re-Validate
		this.getSaleAccount().elementGroups.vendor.isValid();
	},
	
	populateProductTypes: function(arrProductTypeIdName)
	{
		// Need to reset the products list
		this.populateProducts({ids: [], labels: []})

		this.populateSelect($ID('sale_product_type_list'), arrProductTypeIdName, 0, (arrProductTypeIdName.ids.length > 0) ? '[Select Product Type]' : '[No Products Types Available]')
	},
	
	changeProductType: function()
	{
		// Need to reset the products list
		this.populateProducts({ids: [], labels: []})

		var vendorId = Sale.GUIComponent.getElementGroupValue(this.getSaleAccount().elementGroups.vendor);
		if (vendorId == 0 || vendorId == '' || vendorId == null) return; 

		var productTypeModule = $ID('sale_product_type_list').options[$ID('sale_product_type_list').selectedIndex].value;
		if (productTypeModule == 0 || productTypeModule == '' || productTypeModule == null) return; 

		if (this.remote$listProductsForProductTypeModuleAndVendor == null)
		{
			var onSuccess = this.populateProducts.bind(this);
			this.remote$listProductsForProductTypeModuleAndVendor = SalesPortal.getRemoteFunction('Sale', 'listProductsForProductTypeModuleAndVendor', onSuccess);
		}
		this.remote$listProductsForProductTypeModuleAndVendor(productTypeModule, vendorId);
	},
	
	populateProducts: function(arrProductIdName)
	{
		this.populateSelect($ID('sale_product_list'), arrProductIdName, 0, (arrProductIdName.ids.length > 0) ? '[Select Product]' : '[No Products Available]')
	},
	
	populateSelect: function(select, arrIdName, noneValue, noneLabel)
	{
		select.innerHTML = '';
		var option = document.createElement('option');
		select.appendChild(option);
		option.value = noneValue;
		option.appendChild(document.createTextNode(noneLabel));
		for (var i = 0, l = arrIdName.ids.length; i < l; i++)
		{
			var option = document.createElement('option');
			select.appendChild(option);
			option.value = arrIdName.ids[i];
			option.appendChild(document.createTextNode(arrIdName.labels[i]));
		}
	},
	
	addSaleItem: function(saleItem)
	{
		var newOne = (saleItem == undefined || saleItem == null);
		var item = newOne ? this.addItem() : saleItem;

		var productType = null;
		var productTypeModule = null;
		var productName = null;
		var productId = null;

		if (newOne)
		{
			productTypeModule = $ID('sale_product_type_list').options[$ID('sale_product_type_list').selectedIndex].value;
			if (productTypeModule == 0 || productTypeModule == '' || productTypeModule == null) return alert('Please select a product type and product.'); 
	
			productId = $ID('sale_product_list').options[$ID('sale_product_list').selectedIndex].value;
			if (productId == 0 || productId == '' || productId == null) return alert('Please select a product.'); 
	
			productType = $ID('sale_product_type_list').options[$ID('sale_product_type_list').selectedIndex].textContent;
			productName = $ID('sale_product_list').options[$ID('sale_product_list').selectedIndex].textContent;
		}
		else
		{
			productTypeModule = saleItem.object.product_type_module;
			productId = saleItem.object.product_id;
			productType = saleItem.object.product_type;
			productName = saleItem.object.product_name;
		}

		item.setProduct(productTypeModule, productId, productType, productName);
		
		var saleItemTable = $ID('sale-items-table');
		
		var odd = (saleItemTable.rows.length % 4) == 0;
		
		var header = saleItemTable.insertRow(-1);
		header.style.backgroundColor = odd ? '#fff' : '#eaeaea';
		header.id = item.instanceId + '-header';
		var summary = header.insertCell(-1);
		summary.id = item.instanceId + '-summary';
		var controls = header.insertCell(-1);
		controls.width = '200px';
		controls.style.textAlign = 'right';
		controls.id = item.instanceId + '-controls';
		
		var remove = document.createElement('input');
		remove.type = 'button';
		remove.value = 'Remove';
		remove.className = "data-entry";
		var f = function() { Sale.getInstance().removeSaleItem(this.id); }
		var func = f.bind({ id: item.instanceId });
		Event.observe(remove, 'click', func, true);
		controls.appendChild(remove);
		
		var expand = document.createElement('input');
		expand.type = 'button';
		expand.value = 'Collapse';
		expand.id = item.instanceId + '-expand';

		var func = item.toggleExpanso.bind(item);
		Event.observe(expand, 'click', func, true);
		controls.appendChild(expand);
		
		var body = saleItemTable.insertRow(-1);
		body.id = item.instanceId + '-body';
		body.style.backgroundColor = odd ? '#fff' : '#eaeaea';
		var container = body.insertCell(-1);
		container.style.borderTop = '2px dashed ' + (odd ? '#eaeaea' : '#fff');
		container.colSpan = 2;
		container.id = item.instanceId + '-container';
		container.innerHTML = '[No configuration required]';
		
		item.setContainers(container, summary);
	},
	
	removeSaleItem: function(id)
	{
		var saleItem = Sale.Item.getInstance(id);
		this.removeItem(saleItem);
		var header = $ID(id + '-header');
		var body = $ID(id + '-body');
		header.parentNode.removeChild(header);
		body.parentNode.removeChild(body);

		var saleItemTable = $ID('sale-items-table');
		var odd = true;
		for (var i = 0, l = saleItemTable.rows.length; i < l; i+=2)
		{
			saleItemTable.rows[i].style.backgroundColor = odd ? '#fff' : '#eaeaea';
			saleItemTable.rows[i+1].style.backgroundColor = odd ? '#fff' : '#eaeaea';
			saleItemTable.rows[i+1].cells[0].style.borderColor = odd ? '#eaeaea': '#fff';
			odd = !odd;
		}
	},
	
	isValid: function()
	{
		// Validate the values and invoke the isValid method of child objects
		
		// Validate all the fields ...
		var bolValid	= true;
		for (strElementGroup in this.elementGroups)
		{
			////alert(strElementGroup);
			bolValid	= this.elementGroups[strElementGroup];
		}
		
		// And the child objects ...
		if (!this.getSaleAccount().isValid()) return false;
		
		$instances = this.getContacts();
		for (var $i = 0, $l = $instances.length; $i < $l; $i++)
		{
			if ($instances[$i] && !$instances[$i].isValid()) return false;
		}
		
		$instances = this.getItems();
		for (var $i = 0, $l = $instances.length; $i < $l; $i++)
		{
			if ($instances[$i] && !$instances[$i].isValid()) return false;
		}
		
		return true;
	},
	
	updateChildObjectsDisplay: function($readOnly)
	{
		this.getSaleAccount().updateDisplay();

		$instances = this.getContacts();
		for (var $i = 0, $l = $instances.length; $i < $l; $i++)
		{
			if ($instances[$i]) $instances[$i].updateDisplay();
		}
		
		$instances = this.getItems();
		for (var $i = 0, $l = $instances.length; $i < $l; $i++)
		{
			if ($instances[$i]) $instances[$i].updateDisplay();
		}
	},
	
	showValidationTip: function()
	{
		// Validate the values and invoke the isValid method of child objects
		$isValid = true;
		
		// Validate all the fields ...
		
		// WIP

		if (!$isValid) return true;
		
		// And the child objects ...
		
		if (this.getSaleAccount().showValidationTip()) return true;
		
		$instances = this.getContacts();
		for (var $i = 0, $l = $instances.length; $i < $l; $i++)
		{
			if ($instances[$i].showValidationTip()) return true;
		}
		
		$instances = this.getItems();
		for (var $i = 0, $l = $instances.length; $i < $l; $i++)
		{
			if ($instances[$i].showValidationTip()) return true;
		}
		
		return false;
	},
	
	
	
	
	getId: function()
	{
		return this.object.id;
	},
	
	setSaleTypeId: function(sale_type_id)
	{
		this.object.sale_type_id = sale_type_id;
	},
	
	getSaleTypeId: function()
	{
		return this.object.sale_type_id;
	},
	
	setCreatedBy: function(created_by)
	{
		this.object.created_by = created_by;
	},
	
	getCreatedBy: function()
	{
		return this.object.created_by;
	},
	
	saleAccount: null,
	
	getSaleAccount: function()
	{
		if (this.saleAccount == null)
		{
			this.saleAccount = new Sale.SaleAccount(this.object.sale_account);
			this.object.sale_account = this.saleAccount.object;
		}
		return this.saleAccount;
	},
	
	loadContacts: function()
	{
		for (var i = 0, l = this.object.contacts.length; i < l; i++)
		{
			this.contacts[this.contacts.length] = new Sale.Contact(this.object.contacts[i]);
		}
	},
	
	addContact: function()
	{
		var contact = new Sale.Contact(null);
		this.object.contacts[this.object.contacts.length] = contact.object;
		this.contacts[this.contacts.length] = contact;
		return contact;
	},
	
	removeContact: function(instance)
	{
		for (var i = 0, l = this.contacts.length; i < l; i++)
		{
			if (this.contacts[i] == instance)
			{
				instance.destroy();
				delete this.object.contacts[i];
				delete this.contacts[i];
			}
		}
	},
	
	contacts: new Array(),
	
	getContacts: function()
	{
		return this.contacts;
	},
	
	saleItems: null,
	
	loadItems: function()
	{
		for (var i = 0, l = this.object.items.length; i < l; i++)
		{
			this.saleItems[this.saleItems.length] = new Sale.Item(this.object.items[i]);
		}
	},
	
	addItem: function()
	{
		var instance = new Sale.Item(null);
		this.object.items[this.object.items.length] = instance.object;
		this.saleItems[this.saleItems.length] = instance;
		return instance;
	},
	
	removeItem: function(instance)
	{
		for (var i = 0, l = this.saleItems.length; i < l; i++)
		{
			if (this.saleItems[i] == instance)
			{
				instance.destroy();
				delete this.object.items[i];
				delete this.saleItems[i];
				return;
			}
		}
	},
	
	getItems: function()
	{
		return this.saleItems;
	}

});

Sale.SaleAccount = Class.create();
Object.extend(Sale.SaleAccount.prototype, Sale.GUIComponent.prototype);
Object.extend(Sale.SaleAccount.prototype, {

	object: null,
	
	initialize: function(obj)
	{
		if (obj == null)
		{
			this.object = {
				id: null,
				
				state_id: null,
				vendor_id: null,
				
				bill_delivery_type_id: null,
				bill_payment_type_id: null,
				direct_debit_type_id: null,
				
				sale_account_direct_debit_credit_card_id: null,
				sale_account_direct_debit_credit_card: null,
				sale_account_direct_debit_bank_account_id: null,
				sale_account_direct_debit_bank_account: null,
	
				reference_id: null,
				business_name: null,
				trading_name: null,
				abn: null,
				acn: null,
				address_line_1: null,
				address_line_2: null,
				suburb: null,
				postcode: null			
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
		this.detailsContainer.innerHTML = '<table id="account_details_table" class="data-table"></table>';

		var table = $ID('account_details_table');

		this.elementGroups.vendor = Sale.GUIComponent.createDropDown(Sale.vendors.ids, Sale.vendors.labels, this.getVendorId(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Vendor', this.elementGroups.vendor);

		this.elementGroups.businessName = Sale.GUIComponent.createTextInputGroup(this.getBusinessName(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Business Name', this.elementGroups.businessName);

		this.elementGroups.tradingName = Sale.GUIComponent.createTextInputGroup(this.getTradingName());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Trading Name', this.elementGroups.tradingName);

		this.elementGroups.abn = Sale.GUIComponent.createTextInputGroup(this.getABN(), false, window._validate.australianBusinessNumber.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'ABN', this.elementGroups.abn);

		this.elementGroups.acn = Sale.GUIComponent.createTextInputGroup(this.getACN(), false, window._validate.australianCompanyNumber.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'ACN', this.elementGroups.acn);

		this.elementGroups.addressLine1 = Sale.GUIComponent.createTextInputGroup(this.getAddressLine1(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Address (Line 1)', this.elementGroups.addressLine1);

		this.elementGroups.addressLine2 = Sale.GUIComponent.createTextInputGroup(this.getAddressLine2());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Address (Line 2)', this.elementGroups.addressLine2);

		this.elementGroups.suburb = Sale.GUIComponent.createTextInputGroup(this.getSuburb(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Suburb', this.elementGroups.suburb);

		this.elementGroups.postcode = Sale.GUIComponent.createTextInputGroup(this.getPostcode(), true, window._validate.postcode.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Postcode', this.elementGroups.postcode);

		this.elementGroups.state = Sale.GUIComponent.createDropDown(Sale.states.ids, Sale.states.labels, this.getStateId(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'State', this.elementGroups.state);
		
		var table = $ID('bill-delivery-type-table');
		this.elementGroups.bill_delivery_type_id = Sale.GUIComponent.createDropDown(Sale.bill_delivery_type.ids, Sale.bill_delivery_type.labels, this.getBillDeliveryTypeId(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Bill Delivery Method', this.elementGroups.bill_delivery_type_id);

		var table = $ID('bill-payment-type-table');
		this.elementGroups.bill_payment_type_id = Sale.GUIComponent.createDropDown(Sale.bill_payment_type.ids, Sale.bill_payment_type.labels, this.getBillPaymentTypeId(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Bill Payment Method', this.elementGroups.bill_payment_type_id);

		var table = $ID('direct-debit-type-table');
		var isMandatoryFunction	= function(){return (Sale.GUIComponent.getElementGroupValue(Sale.getInstance().getSaleAccount().elementGroups.bill_payment_type_id) == Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT);};
		this.elementGroups.direct_debit_type_id = Sale.GUIComponent.createDropDown(Sale.direct_debit_type.ids, Sale.direct_debit_type.labels, this.getDirectDebitTypeId(), isMandatoryFunction.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Direct Debit Type', this.elementGroups.direct_debit_type_id);

		this.setBillPaymentTypeId(this.object.bill_payment_type_id);
		
		if (Sale.vendors.ids.length == 1)
		{
			var nonOption = this.elementGroups.vendor.inputs[0].options[0];
			nonOption.parentNode.removeChild(nonOption);
			this.elementGroups.vendor.isValid();
		}

		if (Sale.bill_delivery_type.ids.length == 1)
		{
			var nonOption = this.elementGroups.bill_delivery_type_id.inputs[0].options[0];
			nonOption.parentNode.removeChild(nonOption);
			this.elementGroups.bill_delivery_type_id.isValid();
		}

		if (Sale.bill_payment_type.ids.length == 1)
		{
			var nonOption = this.elementGroups.bill_payment_type_id.inputs[0].options[0];
			nonOption.parentNode.removeChild(nonOption);
			this.elementGroups.bill_payment_type_id.isValid();
		}

		if (Sale.direct_debit_type.ids.length == 1)
		{
			var nonOption = this.elementGroups.direct_debit_type.inputs[0].options[0];
			nonOption.parentNode.removeChild(nonOption);
			this.elementGroups.direct_debit_type.isValid();
		}

		Sale.getInstance().changeVendor();
		this.changeBillPaymentType();
		this.changeDirectDebitType();

		Event.observe(this.elementGroups.vendor.inputs[0], 'change', Sale.getInstance().changeVendor.bind(Sale.getInstance()), true);
		
		Event.observe(this.elementGroups.bill_delivery_type_id.inputs[0], 'change', this.changeBillDeliveryType.bind(this));
		Event.observe(this.elementGroups.bill_payment_type_id.inputs[0], 'change', this.changeBillPaymentType.bind(this));
		Event.observe(this.elementGroups.direct_debit_type_id.inputs[0], 'change', this.changeDirectDebitType.bind(this));
	},
	
	changeBillDeliveryType: function()
	{
		this.setBillDeliveryTypeId(Sale.GUIComponent.getElementGroupValue(this.elementGroups.bill_delivery_type_id));
		
		// re-Validate the Email field
		arrContacts	= Sale.getInstance().getContacts();
		for (var i = 0; i < arrContacts.length; i++)
		{
			arrContacts[i].elementGroups.email.isValid();
		}
	},
	
	changeBillPaymentType: function()
	{
		// Update the Bill Payment Type
		this.setBillPaymentTypeId(Sale.GUIComponent.getElementGroupValue(this.elementGroups.bill_payment_type_id));
		
		// Rerun Direct Debit Type Validation
		this.elementGroups.direct_debit_type_id.isValid();
	},

	changeDirectDebitType: function()
	{
		this.setDirectDebitTypeId(Sale.GUIComponent.getElementGroupValue(this.elementGroups.direct_debit_type_id));
	},

	isValid: function()
	{
		// Validate the values and invoke the isValid method of child objects
		

		var bolValid	= true;
		for (strElementGroup in this.elementGroups)
		{
			//alert(strElementGroup);
			bolValid	= this.elementGroups[strElementGroup];
		}
		
		
		// Validate all the fields ...
		bolValid	= true;
		for (strElementGroup in this.elementGroups)
		{
			//alert(strElementGroup);
			bolValid	= (this.elementGroups[strElementGroup].isValid()) ? bolValid : false;
		}
		
		var value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.vendor);
		this.object.vendor_id = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.businessName);
		this.object.business_name = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.tradingName);
		this.object.trading_name = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.abn);
		this.object.abn = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.acn);
		this.object.acn = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.addressLine1);
		this.object.address_line_1 = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.addressLine2);
		this.object.address_line_2 = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.suburb);
		this.object.suburb = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.postcode);
		this.object.postcode = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.state);
		this.object.state_id = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.bill_delivery_type_id);
		this.object.bill_delivery_type_id = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.bill_payment_type_id);
		this.object.bill_payment_type_id = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.direct_debit_type_id);
		this.object.direct_debit_type_id = value;
		
		// WIP
		
		// And the child objects ...
		
		if (this.object.bill_payment_type_id == Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT)
		{
			if (this.object.direct_debit_type_id != Sale.DirectDebitType.DIRECT_DEBIT_TYPE_CREDIT_CARD && this.object.direct_debit_type_id != Sale.DirectDebitType.DIRECT_DEBIT_TYPE_BANK_ACCOUNT)
			{
				// Invalid DD method selected!
				return false;
			}
			if (!this.getSaleAccountDirectDebitTypeDetails().isValid()) return false;
		}
		else if (this.object.bill_payment_type_id != Sale.BillPaymentType.BILL_PAYMENT_TYPE_ACCOUNT)
		{
			// Invalid bill payment method selected!
			return false; 
		}
		
		
		return bolValid;
	},

	updateChildObjectsDisplay: function($readOnly)
	{
		if (this.object.bill_payment_type_id == Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT)
		{
			if (this.object.direct_debit_type_id != Sale.DirectDebitType.DIRECT_DEBIT_TYPE_CREDIT_CARD && this.object.direct_debit_type_id != Sale.DirectDebitType.DIRECT_DEBIT_TYPE_BANK_ACCOUNT)
			{
				return;
			}
			this.getSaleAccountDirectDebitTypeDetails().updateDisplay();
		}
	},
	


	showValidationTip: function()
	{
		// Validate the values and invoke the isValid method of child objects
		$isValid = true;
		
		
		// Validate all the fields ...
		
		// WIP
		
		if (!$isValid) return true;
		
		// And the child objects ...
		
		if (this.object.bill_payment_type_id == Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT)
		{
			if (this.object.direct_debit_type_id == Sale.DirectDebitType.DIRECT_DEBIT_TYPE_CREDIT_CARD
			 || this.object.direct_debit_type_id == Sale.DirectDebitType.DIRECT_DEBIT_TYPE_BANK_ACCOUNT) 
			{
				if (this.getSaleAccountDirectDebitTypeDetails().showValidationTip()) return true;
			}
			else
			{
				// NOTHING SELECTED!!!
				return true;
			}
		}
		else if (this.object.bill_payment_type_id != Sale.BillPaymentType.BILL_PAYMENT_TYPE_ACCOUNT)
		{
			// NOT VALID! MUST BE DD OR ACCOUNT. 
			return true;
		}
		
		return false;
	},






	setStateId: function(state_id)
	{
		this.object.state_id = state_id;
	},
	
	getStateId: function()
	{
		return this.object.state_id;
	},
	
	setVendorId: function(vendor_id)
	{
		this.object.vendor_id = vendor_id;
	},
	
	getVendorId: function()
	{
		return this.object.vendor_id;
	},
	
	setBillDeliveryTypeId: function(value)
	{
		this.object.bill_delivery_type_id = value;
	},
	
	getBillDeliveryTypeId: function()
	{
		return this.object.bill_delivery_type_id;
	},
	
	setBillPaymentTypeId: function(bill_payment_type_id)
	{
		if (this.object.bill_payment_type_id != bill_payment_type_id)
		{
			this.destroyDirectDebitDetails();
		}
		this.object.bill_payment_type_id = bill_payment_type_id;
		$ID('direct-debit-type-table').style.visibility = (this.object.bill_payment_type_id == Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT ? 'visible' : 'hidden')
		this.getSaleAccountDirectDebitTypeDetails();
	},
	
	getBillPaymentTypeId: function()
	{
		return this.object.bill_payment_type_id;
	},
	
	setDirectDebitTypeId: function(direct_debit_type_id)
	{
		if (this.object.direct_debit_type_id != direct_debit_type_id)
		{
			this.destroyDirectDebitDetails();
		}
		this.object.direct_debit_type_id = direct_debit_type_id;
		this.getSaleAccountDirectDebitTypeDetails();
	},
	
	getDirectDebitTypeId: function()
	{
		return this.object.direct_debit_type_id;
	},
	
	destroyDirectDebitDetails: function()
	{
		if (this.directDebitDetails != null)
		{
			this.directDebitDetails.destroy();
			this.directDebitDetails = null;
			var oldTable = $ID('direct-debit-detail-table');
			var newTable = document.createElement('table');
			oldTable.parentNode.replaceChild(newTable, oldTable);
			newTable.id = oldTable.id;
		}
	},
	
	directDebitDetails: null,
	
	getSaleAccountDirectDebitTypeDetails: function()
	{
		if (this.object.bill_payment_type_id != Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT)
		{
			return null;
		}
		if (this.object.direct_debit_type_id != Sale.DirectDebitType.DIRECT_DEBIT_TYPE_BANK_ACCOUNT && this.object.direct_debit_type_id != Sale.DirectDebitType.DIRECT_DEBIT_TYPE_CREDIT_CARD) return null;
		if (this.directDebitDetails == null)
		{
			if (this.object.direct_debit_type_id == Sale.DirectDebitType.DIRECT_DEBIT_TYPE_BANK_ACCOUNT) 
			{
				this.directDebitDetails = new Sale.SaleAccount.DirectDebit.BankAccount(this.object.sale_account_direct_debit_bank_account);
				this.object.sale_account_direct_debit_bank_account = this.directDebitDetails.object;
			}
			else
			{
				this.directDebitDetails = new Sale.SaleAccount.DirectDebit.CreditCard(this.object.sale_account_direct_debit_credit_card);
				this.object.sale_account_direct_debit_credit_card = this.directDebitDetails.object;
			}
			this.directDebitDetails.setContainers($ID('direct-debit-detail-table'));
		}
		return this.directDebitDetails;
	},
	
	setReferenceId: function(value)
	{
		this.object.reference_id = value;
	},
	
	getReferenceId: function()
	{
		return this.object.reference_id;
	},
	
	setBusinessName: function(value)
	{
		this.object.business_name = value;
	},
	
	getBusinessName: function()
	{
		return this.object.business_name;
	},
	
	setTradingName: function(value)
	{
		this.object.trading_name = value;
	},
	
	getTradingName: function()
	{
		return this.object.trading_name;
	},
	
	setABN: function(value)
	{
		this.object.abn = value;
	},
	
	getABN: function()
	{
		return this.object.abn;
	},
	
	setACN: function(value)
	{
		this.object.acn = value;
	},
	
	getACN: function()
	{
		return this.object.acn;
	},
	
	setAddressLine1: function(value)
	{
		this.object.address_line_1 = value;
	},
	
	getAddressLine1: function()
	{
		return this.object.address_line_1;
	},
	
	setAddressLine2: function(value)
	{
		this.object.address_line_2 = value;
	},
	
	getAddressLine2: function()
	{
		return this.object.address_line_2;
	},
	
	setSuburb: function(value)
	{
		this.object.suburb = value;
	},
	
	getSuburb: function()
	{
		return this.object.suburb;
	},
	
	setPostcode: function(value)
	{
		this.object.postcode = value;
	},
	
	getPostcode: function()
	{
		return this.object.postcode;
	}

});

Sale.SaleAccount.DirectDebit = {};

Sale.SaleAccount.DirectDebit.CreditCard = Class.create();
Object.extend(Sale.SaleAccount.DirectDebit.CreditCard.prototype, Sale.GUIComponent.prototype);
Object.extend(Sale.SaleAccount.DirectDebit.CreditCard.prototype, {

	object: null,
	
	initialize: function(obj)
	{
		if (obj == null)
		{
			this.object = {
				id: null,
				credit_card_type_id: null,
				card_name: null,
				card_number: null,
				expiry_month: null,
				expiry_year: null,
				cvv: null
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
		var table = this.detailsContainer;
		
		var fncIsMandatoryFunction	= function()
									{
										bolBillPaymentMethod	= (Sale.GUIComponent.getElementGroupValue(Sale.getInstance().getSaleAccount().elementGroups.bill_payment_type_id) == Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT);
										bolDirectDebitType		= (Sale.GUIComponent.getElementGroupValue(Sale.getInstance().getSaleAccount().elementGroups.direct_debit_type_id) == Sale.DirectDebitType.DIRECT_DEBIT_TYPE_CREDIT_CARD);
										return (bolBillPaymentMethod && bolDirectDebitType);
									};
		this.elementGroups.credit_card_type_id = Sale.GUIComponent.createDropDown(Sale.credit_card_type.ids, Sale.credit_card_type.labels, this.getCreditCardTypeId(), fncIsMandatoryFunction.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Card Type', this.elementGroups.credit_card_type_id);
		
		this.elementGroups.card_name = Sale.GUIComponent.createTextInputGroup(this.getCardName(), fncIsMandatoryFunction.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Card Holder Name', this.elementGroups.card_name);
		
		var fncCreditCardValidation	= function(strNumber)
									{
										intCreditCardType	= (Sale.GUIComponent.getElementGroupValue(Sale.getInstance().getSaleAccount().directDebitDetails.elementGroups.credit_card_type_id));
										return window._validate.creditCardNumber(strNumber, intCreditCardType);
									}
		this.elementGroups.card_number = Sale.GUIComponent.createTextInputGroup(this.getCardNumber(), fncIsMandatoryFunction.bind(this), fncCreditCardValidation.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Card Number', this.elementGroups.card_number);

		
		var fncCCVValidation	= function(strCVV)
								{
									intCreditCardType	= (Sale.GUIComponent.getElementGroupValue(Sale.getInstance().getSaleAccount().directDebitDetails.elementGroups.credit_card_type_id));
									return window._validate.creditCardCVV(strCVV, intCreditCardType);
								}
		this.elementGroups.cvv = Sale.GUIComponent.createTextInputGroup(this.getCVV(), fncIsMandatoryFunction.bind(this), fncCCVValidation.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'CVV', this.elementGroups.cvv);
		
		this.elementGroups.expiry_date = Sale.GUIComponent.createCreditCardExpiryGroup(this.getExpiryDate(), fncIsMandatoryFunction.bind(this), window._validate.creditCardExpiry.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Expiry Date', this.elementGroups.expiry_date);
		
		// onchange Events
		var fncChangeCreditCardType	= function()
									{
										this.elementGroups.card_number.isValid();
										this.elementGroups.cvv.isValid();
									}
		Event.observe(this.elementGroups.credit_card_type_id.inputs[0], 'change', fncChangeCreditCardType.bind(this));
	},
	
	isValid: function()
	{
		// Validate the values and invoke the isValid method of child objects

		var bolValid	= true;
		for (strElementGroup in this.elementGroups)
		{
			//alert(strElementGroup);
			bolValid	= this.elementGroups[strElementGroup];
		}
		
		
		// Validate all the fields ...
		var value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.credit_card_type_id);
		this.object.credit_card_type_id = value;
		
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.card_name);
		this.object.card_name = value;
		
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.card_number);
		this.object.card_number = value;
		
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.cvv);
		this.object.cvv = value;
		
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.expiry_date);
		this.object.expiry_month = value[0];
		this.object.expiry_year = value[1];
		
		// WIP
		
		return bolValid;
	},



	showValidationTip: function()
	{
		return false;
	},

	getExpiryDate: function()
	{
		return new Array(this.getExpiryMonth(), this.getExpiryYear());
	},

	setExpiryDate: function($values)
	{
		this.setExpiryMonth($values[0]);
		this.setExpiryYear($values[1]);
	},


	setCreditCardTypeId: function(value)
	{
		this.object.credit_card_type_id = value;
	},
	
	getCreditCardTypeId: function()
	{
		return this.object.credit_card_type_id;
	},

	setCardName: function(value)
	{
		this.object.card_name = value;
	},
	
	getCardName: function()
	{
		return this.object.card_name;
	},

	setCardNumber: function(value)
	{
		this.object.card_number = value;
	},
	
	getCardNumber: function()
	{
		return this.object.card_number;
	},

	setExpiryMonth: function(value)
	{
		this.object.expiry_month = value;
	},
	
	getExpiryMonth: function()
	{
		return this.object.expiry_month;
	},

	setExpiryYear: function(value)
	{
		this.object.expiry_year = value;
	},
	
	getExpiryYear: function()
	{
		return this.object.expiry_year;
	},

	setCVV: function(value)
	{
		this.object.cvv = value;
	},
	
	getCVV: function()
	{
		return this.object.cvv;
	}

});

Sale.SaleAccount.DirectDebit.BankAccount = Class.create();
Object.extend(Sale.SaleAccount.DirectDebit.BankAccount.prototype, Sale.GUIComponent.prototype);
Object.extend(Sale.SaleAccount.DirectDebit.BankAccount.prototype, {

	object: null,
	
	initialize: function(obj)
	{
		if (obj == null)
		{
			this.object = {
				id: null,
				bank_name: null,
				bank_bsb: null,
				account_number: null,
				account_name: null
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
		var table = this.detailsContainer;
		
		var fncIsMandatoryFunction	= function()
										{
											bolBillPaymentMethod	= (Sale.GUIComponent.getElementGroupValue(Sale.getInstance().getSaleAccount().elementGroups.bill_payment_type_id) == Sale.BillPaymentType.BILL_PAYMENT_TYPE_DIRECT_DEBIT);
											bolDirectDebitType		= (Sale.GUIComponent.getElementGroupValue(Sale.getInstance().getSaleAccount().elementGroups.direct_debit_type_id) == Sale.DirectDebitType.DIRECT_DEBIT_TYPE_BANK_ACCOUNT);
											return (bolBillPaymentMethod && bolDirectDebitType);
										};
		this.elementGroups.bank_name = Sale.GUIComponent.createTextInputGroup(this.getBankName(), fncIsMandatoryFunction.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Bank Name', this.elementGroups.bank_name);
		
		this.elementGroups.bank_bsb = Sale.GUIComponent.createTextInputGroup(this.getBankBSB(), fncIsMandatoryFunction.bind(this), window._validate.bsb.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'BSB', this.elementGroups.bank_bsb);
		
		this.elementGroups.account_number = Sale.GUIComponent.createTextInputGroup(this.getAccountNumber(), fncIsMandatoryFunction.bind(this), window._validate.integerPositive.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Account Number', this.elementGroups.account_number);
		
		this.elementGroups.account_name = Sale.GUIComponent.createTextInputGroup(this.getAccountName(), fncIsMandatoryFunction.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Account Name', this.elementGroups.account_name);
	},


	isValid: function()
	{
		// Validate the values and invoke the isValid method of child objects

		var bolValid	= true;
		for (strElementGroup in this.elementGroups)
		{
			//alert(strElementGroup);
			bolValid	= this.elementGroups[strElementGroup];
		}
		
		
		// Validate all the fields ...
		var value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.bank_name);
		this.object.bank_name = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.bank_bsb);
		this.object.bank_bsb = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.account_number);
		this.object.account_number = value;

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.account_name);
		this.object.account_name = value;
		
		// WIP
		
		return bolValid;
	},



	showValidationTip: function()
	{
		return false;
	},






	setBankName: function(value)
	{
		this.object.bank_name = value;
	},
	
	getBankName: function()
	{
		return this.object.bank_name;
	},

	setBankBSB: function(value)
	{
		this.object.bank_bsb = value;
	},
	
	getBankBSB: function()
	{
		return this.object.bank_bsb;
	},

	setAccountNumber: function(value)
	{
		this.object.account_number = value;
	},
	
	getAccountNumber: function()
	{
		return this.object.account_number;
	},

	setAccountName: function(value)
	{
		this.object.account_name = value;
	},
	
	getAccountName: function()
	{
		return this.object.account_name;
	}
});

Sale.Contact = Class.create();
Object.extend(Sale.Contact.prototype, Sale.GUIComponent.prototype);
Object.extend(Sale.Contact.prototype, {

	object: null,
	
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
				reference_id: null,
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

		var table = $ID('primary_contact_details_table');

		this.elementGroups.contact_title_id = Sale.GUIComponent.createDropDown(Sale.contactTitles.ids, Sale.contactTitles.labels, this.getContactTitleId());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Title', this.elementGroups.contact_title_id);

		this.elementGroups.first_name = Sale.GUIComponent.createTextInputGroup(this.getFirstName(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'First Name', this.elementGroups.first_name);

		this.elementGroups.middle_names = Sale.GUIComponent.createTextInputGroup(this.getMiddleNames());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Middle Names', this.elementGroups.middle_names);

		this.elementGroups.last_name = Sale.GUIComponent.createTextInputGroup(this.getLastName(), true);
		Sale.GUIComponent.appendElementGroupToTable(table, 'Last Name', this.elementGroups.last_name);

		this.elementGroups.position_title = Sale.GUIComponent.createTextInputGroup(this.getPositionTitle());
		Sale.GUIComponent.appendElementGroupToTable(table, 'Position', this.elementGroups.position_title);

		this.elementGroups.date_of_birth = Sale.GUIComponent.createDateGroup(this.getDateOfBirth(), false, window._validate.date.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Date of Birth', this.elementGroups.date_of_birth);

		var isMandatoryFunction	= function()
									{
										var bolPreferredContact	= this.getEmailObj().getIsPrimary();
										var bolDeliveryType		= (parseInt(Sale.GUIComponent.getElementGroupValue(Sale.getInstance().getSaleAccount().elementGroups.bill_delivery_type_id)) == Sale.BillDeliveryType.BILL_DELIVERY_TYPE_EMAIL);
										return (bolPreferredContact || bolDeliveryType);
									};
		this.elementGroups.email = Sale.GUIComponent.createTextInputGroup(this.getEmail(), isMandatoryFunction.bind(this), window._validate.email.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Email', this.elementGroups.email);

		var isMandatoryFunction	= function(){return this.getFaxObj().getIsPrimary();};
		this.elementGroups.fax = Sale.GUIComponent.createTextInputGroup(this.getFax(), isMandatoryFunction.bind(this), window._validate.fnnLandLine.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Fax', this.elementGroups.fax);

		var isMandatoryFunction	= function(){return this.getMobileObj().getIsPrimary();};
		this.elementGroups.mobile = Sale.GUIComponent.createTextInputGroup(this.getMobile(), isMandatoryFunction.bind(this), window._validate.fnnMobile.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Mobile', this.elementGroups.mobile);

		var isMandatoryFunction	= function(){return this.getPhoneObj().getIsPrimary();};
		this.elementGroups.phone = Sale.GUIComponent.createTextInputGroup(this.getPhone(), isMandatoryFunction.bind(this), window._validate.fnnLandLine.bind(this));
		Sale.GUIComponent.appendElementGroupToTable(table, 'Phone', this.elementGroups.phone);

		this.elementGroups.primaryContactMethod = Sale.GUIComponent.createDropDown(
			[this.getEmailObj().getContactMethodTypeId(), this.getFaxObj().getContactMethodTypeId(), this.getPhoneObj().getContactMethodTypeId(), this.getMobileObj().getContactMethodTypeId()], 
			['Email', 'Fax', 'Phone', 'Mobile'], 
			this.getPrimaryContactMethod(), 
			true);
		
		Event.observe(this.elementGroups.primaryContactMethod.inputs[0], 'change', this.changePrimaryContactMethod.bind(this), true);	
		
		Sale.GUIComponent.appendElementGroupToTable(table, 'Preferred Contact Method', this.elementGroups.primaryContactMethod);
	},
	
	changePrimaryContactMethod: function()
	{
		$value = this.elementGroups.primaryContactMethod.inputs[0].options[this.elementGroups.primaryContactMethod.inputs[0].selectedIndex].value;
		this.setPrimaryContactMethod($value);
	},

	isValid: function()
	{
		// Validate the values and invoke the isValid method of child objects
		var bolChildrenValid	= true;
		for (var i = 0; i < this.elementGroups.length; i++)
		{
			bolChildrenValid	= (this.elementGroups[i].isValid()) ? bolChildrenValid : false;
		}
		
		// Validate all the fields ...
		var value;

		//value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.xxxx);
		this.object.contact_association_type_id = value;
		
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.contact_title_id);
		this.object.contact_title_id = value;
		
		//value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.xxxx);
		//this.object.contact_status_id = value;
		
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.first_name);
		this.object.first_name = value;
		
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.middle_names);
		this.object.middle_names = value;
		
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.last_name);
		this.object.last_name = value;
		
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.position_title);
		this.object.position_title = value;
		
		//value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.username);
		//this.object.username = value;
		
		//value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.password);
		//this.object.password = value;
		
		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.date_of_birth);
		this.object.date_of_birth = value;
		
		// WIP
		
		// And the child objects ...
		
		//$instances = this.getContactMethods();
		//for (var $i = 0, $l = $instances.length; $i < $l; $i++)
		//{
		//	if ($instances[$i] && !$instances[$i].isValid()) return false;
		//}
		

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.email);
		this.getEmailObj().setDetails(value);

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.fax);
		this.getFaxObj().setDetails(value);

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.mobile);
		this.getMobileObj().setDetails(value);

		value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.phone);
		this.getPhoneObj().setDetails(value);
		
		return true;
	},

	objFax: null,
	objMobile: null,
	objPhone: null,
	objEmail: null,
	
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
				if (this.object.contact_methods[i].contact_method_type_id == Sale.Contact.Contact_Method.CONTACT_METHOD_TYPE_FAX) 
				{ 
					obj = this.object.contact_methods[i];
					break;  
				}
			}
			this.objFax = this.addContactMethod(obj);
			this.objFax.setContactMethodTypeId(Sale.Contact.Contact_Method.CONTACT_METHOD_TYPE_FAX);
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
				if (this.object.contact_methods[i].contact_method_type_id == Sale.Contact.Contact_Method.CONTACT_METHOD_TYPE_PHONE) 
				{ 
					obj = this.object.contact_methods[i];
					break;  
				}
			}
			this.objPhone = this.addContactMethod(obj);
			this.objPhone.setContactMethodTypeId(Sale.Contact.Contact_Method.CONTACT_METHOD_TYPE_PHONE);
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
				if (this.object.contact_methods[i].contact_method_type_id == Sale.Contact.Contact_Method.CONTACT_METHOD_TYPE_MOBILE) 
				{ 
					obj = this.object.contact_methods[i];
					break;  
				}
			}
			this.objMobile = this.addContactMethod(obj);
			this.objMobile.setContactMethodTypeId(Sale.Contact.Contact_Method.CONTACT_METHOD_TYPE_MOBILE);
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
				if (this.object.contact_methods[i].contact_method_type_id == Sale.Contact.Contact_Method.CONTACT_METHOD_TYPE_EMAIL) 
				{ 
					obj = this.object.contact_methods[i];
					break;  
				}
			}
			this.objEmail = this.addContactMethod(obj);
			this.objEmail.setContactMethodTypeId(Sale.Contact.Contact_Method.CONTACT_METHOD_TYPE_EMAIL);
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
		var contactMethod = new Sale.Contact.Contact_Method(obj);
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
			arr[$i] = new Sale.Contact.Contact_Method(this.object.contact_methods[$i]);			
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

	setReferenceId: function(value)
	{
		this.object.reference_id = value;
	},
	
	getReferenceId: function()
	{
		return this.object.reference_id;
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

});

Sale.Contact.Contact_Method = Class.create();
Object.extend(Sale.Contact.Contact_Method, {
	CONTACT_METHOD_TYPE_EMAIL: 		1,
	CONTACT_METHOD_TYPE_FAX: 		2,
	CONTACT_METHOD_TYPE_PHONE: 		3,
	CONTACT_METHOD_TYPE_MOBILE: 	4
});

Object.extend(Sale.Contact.Contact_Method.prototype, Sale.GUIComponent.prototype);
Object.extend(Sale.Contact.Contact_Method.prototype, {

	object: null,
	
	initialize: function(obj)
	{
		if (obj == null)
		{
			this.object = {
				id: null,
				contact_method_type_id: null,
				details: null,
				is_primary: false
			};
		}
		else
		{
			this.object = obj;
		}
	},
	

	isValid: function()
	{
		// Validate the values and invoke the isValid method of child objects		
		
		// Validate all the fields ...
		//value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.contact_method_type_id);
		//this.object.contact_method_type_id = value;

		//value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.details);
		//this.object.details = value;

		//value = Sale.GUIComponent.getElementGroupValue(this.elementGroups.is_primary);
		//this.object.primary = value;
		
		// WIP
		
		return true;
	},



	showValidationTip: function()
	{
		return false;
	},





	setContactMethodTypeId: function(value)
	{
		this.object.contact_method_type_id = value;
	},
	
	getContactMethodTypeId: function()
	{
		return this.object.contact_method_type_id;
	},
	
	setDetails: function(value)
	{
		this.object.details = value;
	},
	
	getDetails: function()
	{
		return this.object.details;
	},
	
	setIsPrimary: function(value)
	{
		this.object.is_primary = value;
	},
	
	getIsPrimary: function()
	{
		return this.object.is_primary;
	}

});

Sale.Item = Class.create();
Object.extend(Sale.Item, {
	
	unique: 1,
	
	instances: {},
	
	register: function(instance)
	{
		instance.instanceId = 'sale-item-' + (Sale.Item.unique++);
		Sale.Item.instances[instance.instanceId] = instance;
	},
	
	getInstance: function(instanceId)
	{
		return Sale.Item.instances[instanceId];
	},
	
	collapseAll: function()
	{
		for (var instanceId in Sale.Item.instances)
		{
			if (typeof Sale.Item.instances[instanceId] == 'function')
			{
				continue;
			}
			Sale.Item.instances[instanceId].collapse();
		}
	},
	
	expandAll: function()
	{
		for (var instanceId in Sale.Item.instances)
		{
			if (typeof Sale.Item.instances[instanceId] == 'function')
			{
				continue;
			}
			Sale.Item.instances[instanceId].expand();
		}
	}
	
});
Object.extend(Sale.Item.prototype, Sale.GUIComponent.prototype);
Object.extend(Sale.Item.prototype, {

	object: null,
	
	product_type_module: null,
	
	instanceId: null,
	
	initialize: function(obj)
	{
		if (obj == null)
		{
			this.object = {
				id: null,
				product_type_module: null,
				product_id: null,
				created_on: null,
				created_by: null,
				sale_item_status_id: null,
				commission_paid_on: null,
				product_detail: null
			};
		}
		else
		{
			this.object = obj;
		}

		elementGroups = {};
		
		Sale.Item.register(this);
	},
	
	collapse: function()
	{
		if (this.isExpanded()) this.toggleExpanso();
	},
	
	expand: function()
	{
		if (!this.isExpanded()) this.toggleExpanso();
	},
	
	isExpanded: function()
	{
		return $ID(this.instanceId + "-body").style.display != 'none';
	},
	
	toggleExpanso: function()
	{
		var collapsed = !this.isExpanded(); 
		this.updateSummary(collapsed);
		$ID(this.instanceId + "-body").style.display = (collapsed ? 'table-row' : 'none'); 
		$ID(this.instanceId + "-expand").value = (collapsed ? 'Collapse' : 'Expand');
	},
	
	buildGUI: function()
	{
		this.summaryContainer.innerHTML = '';
		this.summaryContainer.appendChild(document.createTextNode('Loading product module...'));
		
		this.getProductModuleDelayed(this.buildGUIDelayed.bind(this));
	},
	
	buildGUIDelayed: function(module)
	{
		//alert('loaded: ' + module);
		this.summaryContainer.innerHTML = '';
		this.summaryContainer.appendChild(document.createTextNode(this.productType + ": " + this.productName));
		module.setContainers(this.detailsContainer, this.summaryContainer);
	},
	
	updateSummary: function(expanded)
	{
		if (expanded)
		{
			this.summaryContainer.innerHTML = '';
			this.summaryContainer.appendChild(document.createTextNode(this.productType + ": " + this.productName));
		}
		else
		{
			this.summaryContainer.innerHTML = '';
			var isValid = this.getProductModule().isValid();
			this.getProductModule().updateSummary(this.productType + ": " + this.productName);
			if (!isValid)
			{
				var span = document.createElement("span");
				span.appendChild(document.createTextNode(" [INCOMPLETE]"));
				span.style.color = "#f00";
				span.style.fontWeight = "bolder";
				this.summaryContainer.appendChild(span);
			}
		}
	},


	isValid: function()
	{
		// Validate the values and invoke the isValid method of child objects		
		
		// Validate all the fields ...
		
		// WIP
		
		// And the child objects ...
		
		if (!this.getProductModule().isValid()) return false;
		
		return true;
	},

	updateChildObjectsDisplay: function($readOnly)
	{
		this.getProductModule().updateDisplay();
	},
	


	showValidationTip: function()
	{
		return false;
	},




	productType: null,
	productName: null,


	setProduct: function (product_type_module, product_id, productType, productName)
	{
		if (this.object.product_type_module != product_type_module)
		{
			this.object.product_detail = null;
			if (this.productModule != null)
			{
				this.productModule.destroy();
				this.productModule = null;
			}
		}
		this.productType = productType;
		this.productName = productName;
		this.object.product_type_module = product_type_module;
		this.object.product_id = product_id;
	},
	
	getProductId: function ()
	{
		return this.object.product_id;
	},
	
	getProductTypeModule: function()
	{
		return this.object.product_type_module;
	},
	
	productModule: null,
	
	getProductModule: function()
	{
		if (this.productModule == null)
		{
			this.productModule = Sale.ProductTypeModule.getModuleInstance(this.object.product_type_module, this.object.product_detail);
			if (this.productModule != undefined && this.productModule != null) this.object.product_detail = this.productModule.object;
			else this.productModule = null;
		}
		return this.productModule;
	},
	
	getProductModuleDelayed: function(onGet)
	{
		var pm = this.getProductModule();
		if (pm == null)
		{
			var f = function() { this.loader.getProductModuleDelayed(this.onGetFunction) };
			window.setTimeout(f.bind({ onGetFunction: onGet, loader: this }), 1000);
		}
		else
		{
			onGet(pm);
		}
	},
	
	receiveModuleInstance: function(instance)
	{
		
	},
	
	setCreatedOn: function(value)
	{
		this.object.created_on = value;
	},
	
	getCreatedOn: function()
	{
		return this.object.created_on;
	},
	
	setCreatedBy: function(value)
	{
		this.object.created_by = value;
	},
	
	getCreatedBy: function()
	{
		return this.object.created_by;
	},
	
	setSaleItemStatusId: function(value)
	{
		this.object.sale_item_status_id = value;
	},
	
	getSaleItemStatusId: function()
	{
		return this.object.sale_item_status_id;
	},
	
	setCommissionPaidOn: function(value)
	{
		this.object.commission_paid_on = value;
	},
	
	getCommissionPaidOn: function()
	{
		return this.object.commission_paid_on;
	},
	
	setProductDetail: function(value)
	{
		this.object.product_detail = value;
	},
	
	getProductDetail: function()
	{
		return this.object.product_detail;
	}

});

Sale.ProductTypeModule = Class.create();
Object.extend(Sale.ProductTypeModule, {
	
	// THESE VALUES SHOULD BE SET IN THE SUBCLASSES!
	product_type_module: null,
	
	// The following code should not be changed by subclasses!
	registeredModules: [],
	
	staticData: null,

	registerModule: function(module_class)
	{
		//alert("Registering module " + module_class.product_type_module);
		Sale.ProductTypeModule.registeredModules[module_class.product_type_module] = module_class;
	},
	
	getModuleInstance: function(product_type_module, obj)
	{
		var module_class = Sale.ProductTypeModule.registeredModules[product_type_module];
		if (module_class == undefined)
		{
			//alert(product_type_module + " not registered");
			return null;
		}
		else
		{
			//alert(module_class);
			var instance = new module_class(obj);
			return instance;
		}
	},
	
	getProductTypeId: function()
	{
		return this.product_type_id;
	},
	
	getLoadStaticDataFunction: function()
	{
		return this.loadStaticData.bind(this);
	},
	
	autoloadAndRegister: function()
	{
		Event.observe(window, 'load', this.getLoadStaticDataFunction());
	},
	
	loadStaticData: function()
	{
		if (this.staticDataRequested || this.hasLoaded()) 
		{
			if (!this.staticDataRequested)
			{
				this.registerModule(this);
				this.staticDataRequested = true;
				return;
			}
		}
		this.staticDataRequested = true;
		var remote$loadStaticData = SalesPortal.getRemoteFunction("ProductTypeModule", "loadData", this.receiveStaticData.bind(this));
		remote$loadStaticData(this.product_type_module);
	},
	
	receiveStaticData: function(staticData)
	{
		// Register this product type module
	 	this.staticData = staticData; 
		this.registerModule(this);
	},
	
	hasLoaded: function()
	{
		return this.staticData != null;
	}
	
});

Object.extend(Sale.ProductTypeModule.prototype, Sale.GUIComponent.prototype);
Object.extend(Sale.ProductTypeModule.prototype, {

	object: null,
	
	staticDataRequested: false,

	initialize: function(obj)
	{
		if (obj == null)
		{
			this.object = this._getBlankDetailsObject();
		}
		else
		{
			this.object = obj;
		}
		
		this.elementGroups = {};
	},
	
	buildGUI: function()
	{
	},
	
	updateSummary: function(suggestion)
	{
		this.summaryContainer.appendChild(document.createTextNode(suggestion));
	},
	
	destroy: function()
	{
		
	},
	
	_getBlankDetailsObject: null,
	
	isValid: function()
	{
		return true;
	},
	
	showValidationTip: function()
	{
		return false;
	},
	
	hasLoaded: function()
	{
		return true;
	}
});
