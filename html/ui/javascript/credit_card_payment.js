
var CreditCardPayment = Class.create();
Object.extend(CreditCardPayment, 
{

	CREDIT_CARD_TYPE_VISA: 1,
	CREDIT_CARD_TYPE_MASTERCARD: 2,
	CREDIT_CARD_TYPE_BANKCARD: 3,
	CREDIT_CARD_TYPE_AMEX: 4,
	CREDIT_CARD_TYPE_DINERS: 5,

	emailReg: /^[a-z0-9]+(|[a-z0-9_\-\+]*[a-z0-9]+)(\.[a-z0-9]+(|[a-z0-9_\-\+]*[a-z0-9]+))*\@([a-z0-9]+(|[a-z0-9\-]*[a-z0-9]+)\.)+([a-z]{2,4})$/i,
	cvvReg: /^[0-9]{3,4}$/,
	whitespaceReg: /^[ \n\r\t]+$/,
	monetaryValueReg: /^\d+[\d,]*(\.\d{0,2}|)?$/,
	directDebitTermsAndConditions: null,

	listCardTypes: function()
	{
		return CreditCardType.types;
	},

	checkExpiry: function(month, year)
	{
		month = parseInt(month);
		year = parseInt(year);
		var d = new Date();
		var curr_month = d.getMonth() + 1;
		var curr_year = d.getFullYear();
		return year > curr_year || (year == curr_year && month >= curr_month);
	},

	checkAmount: function(mixAmount)
	{
		return CreditCardPayment.monetaryValueReg.test(strValue);
	},

	checkName: function(strName)
	{
		return CreditCardPayment.checkStringNotBlank(strName);
	},

	checkStringNotBlank: function(strValue)
	{
		return !whitespaceReg.test(strValue);
	},

	checkCardType: function(intCCType)
	{
		return CreditCardPayment.cardTypeForId(intCCType) != false;
	},

	checkEmail: function(mixEmail)
	{
		return CreditCardPayment.emailReg.test(mixEmail);
	},

	checkCVV: function(mixCVV)
	{
		mixCVV = mixCVV.replace(/[^ \n\r\t]+/g, '');
		return CreditCardPayment.cvvReg.test(mixCVV);
	},

	checkCardNumber: function(mixNumber, intCreditCardType)
	{
		// Strip the string of all non-digits
		strNumber = mixNumber.replace(/[^0-9]+/g, '');

		// Get the CC type for the number (this matches on prefixe)
		var ccType = CreditCardType.cardTypeForNumber(strNumber);
		if (!ccType)
		{
			return false;
		}

		// Check the Length is ok for the type
		bolLengthFound = false;
		for (var i = 0, l = ccType['valid_lengths'].length; i < l; i++)
		{
			if (strNumber.length == ccType['valid_lengths'][i])
			{
				bolLengthFound = true;
				break;
			}
		}
		if (!bolLengthFound)
		{
			return false;
		}

		// Check the LUHN of the Credit Card
		return CreditCardPayment.checkLuhn(strNumber);
	},

	// Luhn calculation avoiding all multiplication/division/large number errors that occur in JS
	checkLuhn: function(strNumber)
	{
		var nrDigits = strNumber.length;
		var digits = ("00" + strNumber).split("").reverse();
		var total = 0;
		for (var i = 0; i < nrDigits; i+=2)
		{
			var d1 = parseInt(digits[i]);
			var d2 = 2*parseInt(digits[i + 1]);
			d2 = d2 > 9 ? (d2 - 9) : d2;
			total += d1 + d2;
			total -= total >= 20 ? 20 :(total >= 10 ? 10 : 0);
		}
		// Check that the total is 0
		return total == 0;
	},

	getCCType: function(mixNumber)
	{
		if (mixNumber.length < CreditCardType.minPrefixLength) return false;
		return CreditCardType.cardTypeForNumber(mixNumber);
	}
});

Object.extend(CreditCardPayment.prototype, 
{
	accountNumber: null,
	abn: null,
	companyName: null,
	contactName: null,
	contactEmail: null,
	amountOwing: null,

	paymentForm: null,
	errorMessage: null,
	popup: null,
	termsPopup: null,
	hasCancelButton: true,
	entryPanelButtons: null,

	inputEmail: null,
	inputCardType: null,
	inputCardNumber: null,
	inputCVV: null,
	inputMonth: null,
	inputYear: null,
	inputName: null,
	inputAmount: null,
	inputDD: null,

	displaySurcharge: null,
	displayTotal: null,
	displayOutstandingPrior: null,
	displayOutstandingAfter: null,

	allowDD: false,

	initialize: function(accountNumber, abn, companyName, contactName, contactEmail, amountOwing, allowDD)
	{
		this.accountNumber = accountNumber;
		this.abn = abn;
		this.companyName = companyName;
		this.contactName = contactName;
		this.contactEmail = contactEmail;
		this.amountOwing = parseFloat(amountOwing);
		if (isNaN(this.amountOwing))
		{
			this.amountOwing = 0.00;
		}
		if (allowDD) this.allowDD = true;

		this.preparePopup();
		this.popup.setTitle('Secure Credit Card Payment');
		this.displayForm();
		this.popup.display();
	},

	preparePopup: function()
	{
		if (this.popup == null) this.popup = new Reflex_Popup(50);
		this.popup.setHeaderButtons([]);
		this.popup.addCloseButton(this.cancel.bind(this));
	},

	displayForm: function(errorMessage)
	{
		if (this.paymentForm == null)
		{
			var option = null;
			this.inputEmail = document.createElement('input');
			this.inputEmail.type= 'text';
			this.inputEmail.className = 'required';
			this.inputEmail.value = this.contactEmail;
			this.inputEmail.maxLength = 255;
			this.inputEmail.size = this.hasCancelButton ? 40 : 30;
			this.inputCardType = document.createElement('select');
			this.inputCardType.className = 'required';
			// Populate the select (Default to none-selected)
			var cardTypes = CreditCardPayment.listCardTypes();
			option = document.createElement('option');
			option.appendChild(document.createTextNode('[Please select]'));
			option.value = '';
			this.inputCardType.appendChild(option);
			for (var i = 0, l = cardTypes.length; i < l; i++)
			{
				option = document.createElement('option');
				option.value = cardTypes[i]['id'];
				option.appendChild(document.createTextNode(cardTypes[i]['name']));
				this.inputCardType.appendChild(option);
			}
			this.inputCardNumber = document.createElement('input');
			this.inputCardNumber.type= 'text';
			this.inputCardNumber.className = 'required';
			this.inputCVV = document.createElement('input');
			this.inputCVV.type= 'text';
			this.inputCVV.className = 'required';
			this.inputCVV.maxLength = CreditCardType.maxCvvLength;
			this.inputCVV.size = CreditCardType.maxCvvLength;

			var tidiedAmount = this.tidyAmount(this.amountOwing);

			this.displaySurcharge = document.createElement('input');
			this.displaySurcharge.value = '0.00';

			this.displayTotal = document.createElement('input');
			this.displayTotal.value = tidiedAmount;

			this.displayOutstandingPrior = document.createElement('input');
			this.displayOutstandingPrior.value = tidiedAmount;

			this.displayOutstandingAfter = document.createElement('input');
			this.displayOutstandingAfter.value = '0.00';

			this.displaySurcharge.disabled = this.displayTotal.disabled = this.displayOutstandingPrior.disabled = this.displayOutstandingAfter.disabled = true;
			this.displaySurcharge.className = this.displayTotal.className = this.displayOutstandingPrior.className = this.displayOutstandingAfter.className = 'displayOnly';

			var d = new Date();
			var curr_month = d.getMonth() + 1;
			var curr_year = d.getFullYear();

			this.inputMonth = document.createElement('select');
			this.inputMonth.className = 'required';
			// Populate the select and default to this month
			for (var month = 1; month <= 12; month++)
			{
				option = document.createElement('option');
				option.value = month;
				if (month == curr_month)
				{
					option.setAttribute('selected', 'selected');
				}
				var strMonth = "0"+month;
				option.appendChild(document.createTextNode(strMonth.substr((strMonth.length - 2))));
				this.inputMonth.appendChild(option);
			}

			this.inputYear = document.createElement('select');
			this.inputYear.className = 'required';
			// Populate the select and default to this year
			for (var year = curr_year; year <= (curr_year + 10); year++)
			{
				option = document.createElement('option');
				option.value = year;
				if (year == curr_year)
				{
					option.setAttribute('selected', 'selected');
				}
				option.appendChild(document.createTextNode(year));
				this.inputYear.appendChild(option);
			}

			this.inputName = document.createElement('input');
			this.inputName.className = 'required';
			this.inputName.type= 'text';
			this.inputName.value= this.contactName;
			this.inputName.maxLength = 255;
			this.inputAmount = document.createElement('input');
			this.inputAmount.className = 'required';
			this.inputAmount.type= 'text';
			this.inputAmount.value= this.amountOwing;
			this.inputAmount.maxLength = ("" + CreditCardType.maxCardPayment).length + 3;
			this.inputDD = document.createElement('input');
			this.inputDD.type= 'checkbox';
			this.inputDD.checked = false;

			this.paymentForm = document.createElement('div');
			this.errorMessage = document.createElement('p');
			this.errorMessage.style.display = 'none';
			this.paymentForm.appendChild(this.errorMessage);

			var table = document.createElement('table');
			table.className = 'reflex';
			var tr = null, td = null;

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Account:'));
			tr.insertCell(-1).appendChild(document.createTextNode(this.accountNumber));
			if (this.hasCancelButton) tr.cells[0].style.width = '35%';

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Company:'));
			tr.insertCell(-1).appendChild(document.createTextNode(this.companyName));

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('ABN:'));
			tr.insertCell(-1).appendChild(document.createTextNode(this.abn));

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Email:'));
			tr.insertCell(-1).appendChild(this.inputEmail);

			this.paymentForm.appendChild(table);

			table = document.createElement('table');
			table.className = 'reflex';

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Credit Card Type:'));
			tr.insertCell(-1).appendChild(this.inputCardType);
			if (this.hasCancelButton) tr.cells[0].style.width = '35%';

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Name on Card:'));
			tr.insertCell(-1).appendChild(this.inputName);

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Card Number:'));
			tr.insertCell(-1).appendChild(this.inputCardNumber);

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('CVV:'));
			tr.insertCell(-1).appendChild(this.inputCVV);

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Expiry Date:'));
			td = tr.insertCell(-1);
			td.appendChild(this.inputMonth);
			td.appendChild(this.inputYear);
			td.appendChild(document.createElement('span'));
			td.childNodes[2].appendChild(document.createTextNode('mm/yyyy'));

			if (this.allowDD)
			{
				tr = table.insertRow(-1);
				tr.insertCell(-1).appendChild(document.createTextNode('Use Details For Direct Debit:'));
				tr.insertCell(-1).appendChild(this.inputDD);
			}

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Amount to Pay:'));
			td = tr.insertCell(-1);
			this.appendCurrency(td)
			td.appendChild(this.inputAmount);

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Credit Card Surcharge:'));
			td = tr.insertCell(-1);
			this.appendCurrency(td)
			td.appendChild(this.displaySurcharge);

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Total Payment:'));
			td = tr.insertCell(-1);
			this.appendCurrency(td)
			td.appendChild(this.displayTotal);

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Current Balance:'));
			td = tr.insertCell(-1);
			this.appendCurrency(td)
			td.appendChild(this.displayOutstandingPrior);

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Balance After Payment:'));
			td = tr.insertCell(-1);
			this.appendCurrency(td)
			td.appendChild(this.displayOutstandingAfter);

			Event.observe(this.inputCardType, 'change', this.changeCardType.bind(this));
			Event.observe(this.inputMonth, 'change', this.changeExpiry.bind(this));
			Event.observe(this.inputYear, 'change', this.changeExpiry.bind(this));
			Event.observe(this.inputCardNumber, 'change', this.changeNumber.bind(this));
			Event.observe(this.inputCardNumber, 'keyup', this.changeNumber.bind(this));
			Event.observe(this.inputAmount, 'change', this.changeAmount.bind(this));
			Event.observe(this.inputAmount, 'keyup', this.changeAmount.bind(this));
			Event.observe(this.inputCVV, 'change', this.changeCVV.bind(this));
			Event.observe(this.inputCVV, 'keyup', this.changeCVV.bind(this));
			Event.observe(this.inputName, 'change', this.changeName.bind(this));
			Event.observe(this.inputName, 'keyup', this.changeName.bind(this));
			Event.observe(this.inputEmail, 'change', this.changeEmail.bind(this));
			Event.observe(this.inputEmail, 'keyup', this.changeEmail.bind(this));

			this.paymentForm.appendChild(table);

			// Add the buttons (cancel & submit)
			var buttonCancel = document.createElement('input');
			var buttonSubmit = document.createElement('input');
			buttonCancel.className = buttonSubmit.className = 'reflex-button';
			buttonCancel.type = buttonSubmit.type = 'button';
			buttonCancel.value = 'Cancel';
			buttonSubmit.value = 'Submit';
			Event.observe(buttonCancel, 'click', this.cancel.bind(this));
			Event.observe(buttonSubmit, 'click', this.submitPayment.bind(this));

			if (!this.hasCancelButton)
			{
				this.entryPanelButtons = [buttonSubmit];
			}
			else
			{
				this.entryPanelButtons = [buttonCancel, buttonSubmit];
			}
		}

		if (errorMessage)
		{
			this.errorMessage.innerHTML = '';
			this.errorMessage.appendChild(document.createTextNode(errorMessage));
			this.errorMessage.style.display = 'default';
		}
		else
		{
			this.errorMessage.style.display = 'none';
		}

		this.popup.setFooterButtons(this.entryPanelButtons);

		this.popup.setContent(this.paymentForm);
		this.popup.recentre();
	},

	submitPayment: function(bolConfirmed, bolAgreedToTermsAndConditions)
	{
		if (typeof bolConfirmed != 'boolean')
		{
			bolConfirmed = false;
		}

		if (typeof bolAgreedToTermsAndConditions != 'boolean')
		{
			bolAgreedToTermsAndConditions = false;
		}

		// Validate the data
		if (!this.validate())
		{
			$Alert('Please correct all errors and try again.', null, 'Invalid Values Entered');
			return;
		}

		if (!bolConfirmed)
		{
			this.confirmBeforeSubmit();
			return false; 
		}

		// Ensure that the user has agreed to the terms and conditions
		if (this.allowDD && this.inputDD.checked && !bolAgreedToTermsAndConditions)
		{
			this.termsAndConditions();
			return false; 
		}

		this.showProcessing();
		var $submit = jQuery.json.jsonFunction(this.submitted.bind(this), null, 'Credit_Card_Payment', 'makePayment');

		// Gather the validated values and submit to the server
		$submit(this.accountNumber,
				this.inputEmail.value,
				this.inputCardType.options[this.inputCardType.selectedIndex].value,
				this.inputCardNumber.value,
				this.inputCVV.value,
				this.inputMonth.options[this.inputMonth.selectedIndex].value,
				this.inputYear.options[this.inputYear.selectedIndex].value,
				this.inputName.value,
				this.inputAmount.value,
				this.inputDD.checked);
	},

	showProcessing: function()
	{
		this.popup.setHeaderButtons([]);
		this.popup.setFooterButtons([]);
		var panel = document.createElement('div');
		panel.className = 'processing-credit-card-payment';

		//panel.style.width = this.paymentForm.offsetWidth + 'px';
		//panel.style.height = this.paymentForm.offsetHeight + 'px';

		var p = null, img = null;

		img = document.createElement('div');
		img.className = 'reflex-loading-image';
		panel.appendChild(img);

		p = document.createElement('p');
		img.appendChild(p);
		p.appendChild(document.createTextNode('Processing payment.'));
		img.appendChild(document.createElement('br'));
		img.appendChild(document.createElement('br'));
		img.appendChild(document.createElement('br'));
		img.appendChild(document.createElement('br'));
		img.appendChild(document.createElement('br'));
		p = document.createElement('p');
		img.appendChild(p);
		p.appendChild(document.createTextNode('Please wait.'));

		this.popup.setContent(panel);
		this.popup.recentre();
	},

	submitted: function(response)
	{
		// Check the 'OUTCOME' property of the response
		var outcome = response['OUTCOME'];

		// INVALID = problem with the submitted values

		// UNAVAILABLE = The SecurePay servers could not be contacted

		// FAILED = A problem occurred communicating with the SecurePay servers

		// SUCCESS = The payment was made and DD details stored (if appropriate)
		if (outcome == 'SUCCESS')
		{
			// Need to display the confirmation message and change buttons to OK
		}

		// The details of the response (the confirmation message)
		// need to be displayed to the user, assuming it all worked.

	},

	confirmBeforeSubmit: function()
	{
		// Set the content of the popup to be the confirmation page
		// Don't forget to do the buttons too!
		var confirmForm = document.createElement('div');
		var message = document.createElement('h1');
		confirmForm.appendChild(message);
		message.appendChild(document.createTextNode("Please check the details below before continuing."));

		var table = document.createElement('table');
		table.className = 'reflex';
		var tr = null, td = null;

		tr = table.insertRow(-1);
		tr.insertCell(-1).appendChild(document.createTextNode('Account:'));
		tr.insertCell(-1).appendChild(document.createTextNode(this.accountNumber));
		if (this.hasCancelButton) tr.cells[0].style.width = '35%';

		tr = table.insertRow(-1);
		tr.insertCell(-1).appendChild(document.createTextNode('Company:'));
		tr.insertCell(-1).appendChild(document.createTextNode(this.companyName));

		tr = table.insertRow(-1);
		tr.insertCell(-1).appendChild(document.createTextNode('ABN:'));
		tr.insertCell(-1).appendChild(document.createTextNode(this.abn));

		tr = table.insertRow(-1);
		tr.insertCell(-1).appendChild(document.createTextNode('Email:'));
		tr.insertCell(-1).appendChild(document.createTextNode(this.inputEmail.value));

		confirmForm.appendChild(table);

		table = document.createElement('table');
		table.className = 'reflex';

		tr = table.insertRow(-1);
		tr.insertCell(-1).appendChild(document.createTextNode('Credit Card Type:'));
		tr.insertCell(-1).appendChild(document.createTextNode(this.getSelectedCardType()['name']));
		if (this.hasCancelButton) tr.cells[0].style.width = '35%';

		tr = table.insertRow(-1);
		tr.insertCell(-1).appendChild(document.createTextNode('Name on Card:'));
		tr.insertCell(-1).appendChild(document.createTextNode(this.inputName.value));

		tr = table.insertRow(-1);
		tr.insertCell(-1).appendChild(document.createTextNode('Card Number:'));
		tr.insertCell(-1).appendChild(document.createTextNode(this.inputCardNumber.value));

		tr = table.insertRow(-1);
		tr.insertCell(-1).appendChild(document.createTextNode('CVV:'));
		tr.insertCell(-1).appendChild(document.createTextNode(this.inputCVV.value));

		tr = table.insertRow(-1);
		tr.insertCell(-1).appendChild(document.createTextNode('Expiry Date:'));
		td = tr.insertCell(-1).appendChild(document.createTextNode(this.inputMonth.options[this.inputMonth.selectedIndex].value + '/' + this.inputYear.options[this.inputYear.selectedIndex].value + ' (mm/yyyy)'));

		if (this.allowDD)
		{
			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Use Details For Direct Debit:'));
			tr.insertCell(-1).appendChild(document.createTextNode(this.inputDD.checked ? 'Store these card details and use for Direct Debit' : 'Do not store these details for Direct Debit'));
		}

		tr = table.insertRow(-1);
		tr.insertCell(-1).appendChild(document.createTextNode('Amount to Pay:'));
		td = tr.insertCell(-1);
		this.appendCurrency(td);
		var el = this.displaySurcharge.cloneNode(false);
		el.appendChild(document.createTextNode(this.tidyAmount(this.inputAmount.value)));
		td.appendChild(el);

		tr = table.insertRow(-1);
		tr.insertCell(-1).appendChild(document.createTextNode('Credit Card Surcharge:'));
		td = tr.insertCell(-1);
		this.appendCurrency(td)
		td.appendChild(this.displaySurcharge.cloneNode(true));

		tr = table.insertRow(-1);
		tr.insertCell(-1).appendChild(document.createTextNode('Total Payment:'));
		td = tr.insertCell(-1);
		this.appendCurrency(td)
		td.appendChild(this.displayTotal.cloneNode(true));

		tr = table.insertRow(-1);
		tr.insertCell(-1).appendChild(document.createTextNode('Current Balance:'));
		td = tr.insertCell(-1);
		this.appendCurrency(td)
		td.appendChild(this.displayOutstandingPrior.cloneNode(true));

		tr = table.insertRow(-1);
		tr.insertCell(-1).appendChild(document.createTextNode('Balance After Payment:'));
		td = tr.insertCell(-1);
		this.appendCurrency(td)
		td.appendChild(this.displayOutstandingAfter.cloneNode(true));

		confirmForm.appendChild(table);

		// Add the buttons (cancel & submit)
		var buttonCancel = document.createElement('input');
		var buttonSubmit = document.createElement('input');
		buttonCancel.className = buttonSubmit.className = 'reflex-button';
		buttonCancel.type = buttonSubmit.type = 'button';
		buttonCancel.value = 'Back';
		buttonSubmit.value = 'Continue';
		Event.observe(buttonCancel, 'click', this.cancelSubmit.bind(this));
		Event.observe(buttonSubmit, 'click', this.confirmSubmit.bind(this));

		this.popup.setFooterButtons([buttonCancel, buttonSubmit]);

		this.popup.setContent(confirmForm);
	},

	confirmSubmit: function()
	{
		this.submitPayment(true, false);
	},

	cancelSubmit: function()
	{
		// Set the content of the popup to be the initial entry form
		// Don't forget to do the buttons too!
		this.preparePopup();
		this.displayForm();
	},

	termsAndConditions: function()
	{
		if (this.termsPopup != null) return;
		// Create a terms and conditions popup
		this.termsPopup = new Reflex_Popup(32);
		this.termsPopup.setTitle('Direct Debit Terms & Conditions');

		var cancelFuncBound = this.rejectTermsAndConditions.bind(this);
		var acceptFuncBound = this.acceptTermsAndConditions.bind(this);

		this.termsPopup.addCloseButton(cancelFuncBound);

		// Create the content div and add the cancel/continue buttons
		var panel = document.createElement('div');
		var terms = document.createElement('div');
		var termsText = document.createElement('div');
		terms.appendChild(termsText);
		panel.appendChild(terms);
		terms.style.width = "25em";
		terms.style.height = "30em";
		terms.style.overflow = "auto";
		terms.style.border = "1px solid black";
		terms.style.background = '#fff';
		termsText.style.color = '#000';

		//alert(CreditCardPayment.directDebitTermsAndConditions);
		var termsAndConditions = CreditCardPayment.directDebitTermsAndConditions.replace(/\t{1,1}/g, '\u00a0\u00a0\u00a0\u00a0').split("\n");
		for(var i = 0, l = termsAndConditions.length; i < l; i++)
		{
			if (i > 0)
			{
				termsText.appendChild(document.createElement('br'));
			}
			termsText.appendChild(document.createTextNode(termsAndConditions[i]));
		}

		this.acceptTermsCheckbox = document.createElement('input');
		this.acceptTermsCheckbox.type = 'checkbox';
		this.acceptTermsCheckbox.id = 'acceptTermsCheckbox';
		var lab = document.createElement('label');
		panel.appendChild(lab);
		lab.appendChild(this.acceptTermsCheckbox);
		lab.setAttribute('for', 'acceptTermsCheckbox');
		lab.appendChild(document.createTextNode('I have read, understood and agree to be bound by the Terms and Conditions shown above.'));

		var buttonCancel = document.createElement('input');
		var buttonSubmit = document.createElement('input');
		buttonCancel.className = buttonSubmit.className = 'reflex-button';
		buttonCancel.type = buttonSubmit.type = 'button';
		buttonCancel.value = 'Cancel';
		buttonSubmit.value = 'Accept';
		Event.observe(buttonCancel, 'click', cancelFuncBound);

		Event.observe(buttonSubmit, 'click', acceptFuncBound);

		this.termsPopup.setContent(panel);
		this.termsPopup.setFooterButtons([buttonCancel, buttonSubmit]);

		this.termsPopup.display();
	},

	acceptTermsAndConditions: function()
	{
		if (!this.acceptTermsCheckbox.checked)
		{
			$Alert("Please tick the checkbox to confirm that you have read, understood and agree be bound by these Terms and Conditions.");
			return false;
		}
		this.termsPopup.hide();
		this.termsPopup = null;
		this.submitPayment(true, true);
	},

	rejectTermsAndConditions: function()
	{
		this.termsPopup.hide();
		this.termsPopup = null;
		this.cancelSubmit();
	},

	appendCurrency: function(td)
	{
		var span = document.createElement('span');
		span.appendChild(document.createTextNode('$'));
		span.style['float'] = 'left';
		td.appendChild(span);
	},

	tidyAmount: function(flt)
	{
		if (typeof flt == 'string') flt = parseFloat(flt.replace(/[^0-9\.]+/g, ''));
		if (isNaN(flt)) flt = 0.00;
		var tidy = "" + Math.round(flt*100)/100;
		if (!tidy.match(/\./)) tidy += ".00";
		if (tidy.match(/\.[0-9]{1,1}$/)) tidy += "0";
		return tidy;
	},

	calculateSurcharge: function(cardType, amount)
	{
		return Math.round((amount * 100) * cardType['surcharge'])/100;
	},

	changeAmount: function(highlightBlankFields)
	{
		highlightBlankFields = typeof highlightBlankFields != 'boolean' ? false : highlightBlankFields;
		var cardType = this.getSelectedCardType();
		var bolAmountEntered = false;
		var bolAmountIsValid = true;
		if (this.inputAmount.value != '')
		{
			var cleansed = this.inputAmount.value.replace(/[^0-9\.]+/g, '');
			bolAmountIsValid = this.inputAmount.value.match(/^ *[0-9]+[\, 0-9]*\.?(|[0-9]+[\, 0-9]*)$/) && !isNaN(parseFloat(cleansed));
			var amount = parseFloat(this.inputAmount.value.replace(/[^0-9\.]+/g, ''));
			if (bolAmountIsValid)
			{
				if (cardType)
				{
					var total = Math.floor((amount + this.calculateSurcharge(cardType, amount)) * 100)/100;
					bolAmountIsValid = cardType['minimum_amount'] <= total && total <= cardType['maximum_amount'];
				}
				else
				{
					amount = Math.floor(amount);
					bolAmountIsValid = amount > 0;
				}
			}
			bolAmountEntered = true;
		}
		if (highlightBlankFields && !bolAmountEntered)
		{
			bolAmountEntered = true;
			bolAmountIsValid = false;
		}
		this.setValidity(this.inputAmount, bolAmountEntered, true, bolAmountIsValid);

		this.updateBalances();
		this.updateSurcharges();
		return bolAmountEntered && bolAmountIsValid;
	},

	changeEmail: function(highlightBlankFields)
	{
		highlightBlankFields = typeof highlightBlankFields != 'boolean' ? false : highlightBlankFields;
		var bolEmailEntered = this.inputEmail.value != '';
		var bolEmailIsValid = bolEmailEntered && CreditCardPayment.checkEmail(this.inputEmail.value);
		if (highlightBlankFields && !bolEmailEntered)
		{
			bolEmailEntered = true;
			bolEmailIsValid = false;
		}
		this.setValidity(this.inputEmail, bolEmailEntered, true, bolEmailIsValid);
		return bolEmailEntered && bolEmailIsValid;
	},

	changeExpiry: function(highlightBlankFields)
	{
		highlightBlankFields = typeof highlightBlankFields != 'boolean' ? false : highlightBlankFields;
		var bolExpiryValid = CreditCardPayment.checkExpiry(this.inputMonth.options[this.inputMonth.selectedIndex].value, this.inputYear.options[this.inputYear.selectedIndex].value);
		this.setValidity(this.inputMonth, true, true, bolExpiryValid);
		this.setValidity(this.inputYear, true, true, bolExpiryValid);
		return bolExpiryValid;
	},

	changeName: function(highlightBlankFields)
	{
		highlightBlankFields = typeof highlightBlankFields != 'boolean' ? false : highlightBlankFields;
		var bolNameEntered = this.inputName.value != '';
		var bolNameIsValid = bolNameEntered && (this.inputName.value.replace(/[^a-zA-Z]+/g, '') != '');
		if (highlightBlankFields && !bolNameEntered)
		{
			bolNameEntered = true;
			bolNameIsValid = false;
		}
		this.setValidity(this.inputName, bolNameEntered, true, bolNameIsValid);
		return bolNameEntered && bolNameIsValid;
	},

	setCardType: function(type, skipInitialTidy)
	{
		var bolFlashRegardless = false;
		if (!skipInitialTidy && this.inputCardType.options[0].value == '')
		{
			this.inputCardType.removeChild(this.inputCardType.options[0]);
			bolFlashRegardless = true;
		}
		var idx = CreditCardType.indexOfType(validateType);
		if (!bolFlashRegardless && idx == this.inputCardType.selectedIndex) return;
		this.inputCardType.selectedIndex = idx;
		this.inputCardType.options[idx].selected = true;
		this.inputCardType.options[idx].setAttribute('selected', 'selected');
		var flash = function()
		{
			var ok = this.countdown %2 == 0;
			this.countdown--;
			this.input.className = ok ? 'valid' : 'invalid';
			if (this.countdown < 0) return;
			window.setTimeout(this.func.bind(this), 300);
		}
		var flashFunc = flash.bind({ countdown: 5, func: flash, input: this.inputCardType});
		window.setTimeout(flashFunc, 200);
		this.changeCardType(skipInitialTidy);
	},

	changeNumber: function(skipInitialTidy, bolDueToChangeType, highlightBlankFields)
	{
		skipInitialTidy = typeof skipInitialTidy != 'boolean' ? false : skipInitialTidy;
		bolDueToChangeType = typeof bolDueToChangeType != 'boolean' ? false : bolDueToChangeType;
		highlightBlankFields = typeof highlightBlankFields != 'boolean' ? false : highlightBlankFields;
		var cardType = validateType = this.getSelectedCardType();

		var bolCardTypeEntered = cardType ? true : false;
		var bolCardTypeIsValid = bolCardTypeEntered;

		// Check that the card number is a valid card number. If not, highlight as invalid.
		var bolCardNumberEntered = this.inputCardNumber.value != '';
		var cardNumber = this.inputCardNumber.value.replace(/[^0-9]+/g, '');
		var bolCardNumberIsValid = !bolCardNumberEntered || (cardNumber.length >= CreditCardType.minCardNumberLength && cardNumber.length <= CreditCardType.maxCardNumberLength);
		if (bolCardNumberEntered && bolCardNumberIsValid)
		{
			validateType = CreditCardPayment.getCCType(cardNumber);
			if (!validateType)
			{
				bolCardNumberIsValid = false;
			}
			else
			{
				if (!bolDueToChangeType)
				{
					this.setCardType(validateType, skipInitialTidy);
				}
				if (cardType && validateType['id'] != cardType['id'])
				{
					bolCardNumberIsValid = false;
					bolCardTypeIsValid = false;
				}
				else
				{
					bolCardTypeEntered = bolCardTypeIsValid = true;
					bolCardNumberIsValid = CreditCardPayment.checkCardNumber(cardNumber, validateType['id']);
				}
			}
		}
		else if (bolCardNumberEntered)
		{
			validateType = CreditCardPayment.getCCType(cardNumber);
			if (validateType)
			{
				this.setCardType(validateType, skipInitialTidy);
				bolCardTypeEntered = bolCardTypeIsValid = true;
			}
		}
		else if (highlightBlankFields)
		{
			bolCardTypeIsValid = false;
			bolCardTypeEntered = true;
			bolCardNumberIsValid = false;
			bolCardNumberEntered = true;
		}

		this.setValidity(this.inputCardType, bolCardTypeEntered, true, bolCardTypeIsValid);
		this.setValidity(this.inputCardNumber, bolCardNumberEntered, true, bolCardNumberIsValid);
		return bolCardTypeEntered && bolCardTypeIsValid && bolCardNumberEntered && bolCardNumberIsValid;
	},

	changeCardType: function(skipInitialTidy, highlightBlankFields)
	{
		skipInitialTidy = typeof skipInitialTidy != 'boolean' ? false : skipInitialTidy;
		highlightBlankFields = typeof highlightBlankFields != 'boolean' ? false : highlightBlankFields;
		// If a number has been entered, verify that the number matches the card type
		if (!skipInitialTidy && this.inputCardType.options[0].value == '')
		{
			this.inputCardType.removeChild(this.inputCardType.options[0]);
		}
		var bolOK = this.getSelectedCardType();
		bolOK = this.changeNumber(skipInitialTidy, true, highlightBlankFields) && bolOK;
		bolOK = this.changeCVV(highlightBlankFields) && bolOK;
		bolOK = this.changeAmount(highlightBlankFields) && bolOK;
		return bolOK;
	},

	changeCVV: function(highlightBlankFields)
	{
		highlightBlankFields = typeof highlightBlankFields != 'boolean' ? false : highlightBlankFields;
		var cardType = this.getSelectedCardType();
		var bolCvvEntered = false;
		var bolCvvIsValid = true;
		if (this.inputCVV.value != '')
		{
			var cleansed = this.inputCVV.value.replace(/[^0-9]+/g, '');
			bolCvvIsValid = this.inputCVV.value.match(/^ *[0-9]+[ 0-9]*$/) && !isNaN(parseInt(cleansed));
			if (bolCvvIsValid)
			{
				if (cardType) bolCvvIsValid = cleansed.length == cardType['cvv_length'];
				else bolCvvIsValid = cleansed.length >= CreditCardType.minCvvLength && cleansed.length <= CreditCardType.maxCvvLength;
			}
			bolCvvEntered = true;
		}
		if (highlightBlankFields && !bolCvvEntered)
		{
			bolCvvEntered = true;
			bolCvvIsValid = false;
		}
		this.setValidity(this.inputCVV, bolCvvEntered, true, bolCvvIsValid);
		return bolCvvEntered && bolCvvIsValid;
	},

	validate: function(skipInitialTidy)
	{
		skipInitialTidy = typeof skipInitialTidy != 'boolean' ? false : skipInitialTidy;
		var highlightBlankFields = true;
		var bolOK = this.changeCardType(skipInitialTidy, highlightBlankFields);
		bolOK = this.changeName(highlightBlankFields) && bolOK;
		bolOK = this.changeExpiry(highlightBlankFields) && bolOK;
		bolOK = this.changeEmail(highlightBlankFields) && bolOK;
		return bolOK
	},

	updateBalances: function()
	{
		var amount = parseFloat(this.inputAmount.value.replace(/[^0-9\.]+/g, ''));
		var amountIsValid = !isNaN(amount);
		amount = isNaN(amount) ? 0.00 : amount;
		var balance = this.tidyAmount(this.amountOwing - amount);
		var bal = parseFloat(balance);
		if (bal < 0)
		{
			balance = balance.replace(/\-/g, '');
			balance += " CR";
		}
		this.displayOutstandingAfter.value = balance;
	},

	updateSurcharges: function()
	{
		this.displaySurcharge.value = '';
		this.displayTotal.value = '';
		var cardType = this.getSelectedCardType();
		var amount = parseFloat(this.inputAmount.value.replace(/[^0-9\.]+/g, ''));
		amount = isNaN(amount) ? 0.00 : amount;
		var surcharge = cardType ? this.calculateSurcharge(cardType, amount) : 0.00;
		var totalAmount = this.tidyAmount(amount + surcharge);
		surcharge = this.tidyAmount(surcharge);
		this.displaySurcharge.value = surcharge;
		this.displayTotal.value = totalAmount;
	},

	setValidity: function(element, entered, required, valid)
	{
		element.className = (!entered || valid) ? (required ? (entered ? 'valid' : 'required') : (entered ? 'valid' : '')) : 'invalid';
	},

	getSelectedCardType: function()
	{
		var idx = this.inputCardType.selectedIndex;
		var cardTypeId = this.inputCardType.options[idx].value;
		return CreditCardType.cardTypeForId(cardTypeId);
	},

	cancel: function()
	{
		this.popup.hide();
	}
});

var CreditCardPaymentPanel = Class.create();
Object.extend(CreditCardPaymentPanel.prototype, CreditCardPayment.prototype);
Object.extend(CreditCardPaymentPanel.prototype, 
{
	CreditCardPayment$initialize: CreditCardPayment.prototype.initialize,

	container: null,
	hasCancelButton: false,

	initialize: function(accountNumber, abn, companyName, contactName, contactEmail, amountOwing, allowDD, containerId)
	{
		this.container = $ID(containerId);
		this.CreditCardPayment$initialize(accountNumber, abn, companyName, contactName, contactEmail, amountOwing, allowDD);
	},

	preparePopup: function()
	{
		if (this.popup == null) this.popup = new DynamicallyLoadedPanel(this.container);
	}
});

var DynamicallyLoadedPanel = Class.create();
Object.extend(DynamicallyLoadedPanel.prototype, 
{
	container: null,
	titlePane: null,
	contentPane: null,
	footerPane: null,

	initialize: function(container)
	{
		this.container = container;
		this.titlePane = document.createElement('div');
		this.contentPane = document.createElement('div');
		this.footerPane = document.createElement('div');
		this.container.appendChild(this.titlePane);
		this.container.appendChild(this.contentPane);
		this.container.appendChild(this.footerPane);
	},

	addCloseButton: function(callback)
	{
	},

	setTitle: function(title)
	{
		this.titlePane.innerHTML = '';
		this.titlePane.appendChild(document.createTextNode(title));
	},

	setContent: function(content)
	{
		this.contentPane.innerHTML = '';
		this.contentPane.appendChild(content);
	},

	setHeaderButtons: function(buttons)
	{
	},

	setFooterButtons: function(buttons)
	{
		this.footerPane.innerHTML = '';
		for (var i = 0, l = buttons.length; i < l; i++)
		{
			this.footerPane.appendChild(buttons[i]);
		}
	},

	display: function()
	{
		this.container.style.display  = 'block';
	},

	recentre: function()
	{
		
	},

	hide: function()
	{
		this.container.style.display  = 'none';
	}
});
