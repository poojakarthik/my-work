
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

	displayAmount: null,
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
			this.inputEmail.id = 'cc-input-email';
			this.inputEmail.className = 'required';
			this.inputEmail.value = this.contactEmail;
			this.inputEmail.maxLength = 255;
			this.inputEmail.size = this.hasCancelButton ? 40 : 30;
			this.inputCardType = document.createElement('select');
			this.inputCardType.className = 'required';
			this.inputCardType.id = 'cc-input-type';
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
			this.inputCardNumber.id = 'cc-input-number';
			this.inputCardNumber.maxLength = CreditCardType.maxCardNumberLength + 4;
			this.inputCVV = document.createElement('input');
			this.inputCVV.id = 'cc-input-cvv';
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
			this.inputMonth.id = 'cc-input-month';
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
			this.inputYear.id = 'cc-input-year';
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
			this.inputName.id = 'cc-input-name';
			this.inputAmount = document.createElement('input');
			this.inputAmount.className = 'required';
			this.inputAmount.type= 'text';
			this.inputAmount.value= this.amountOwing;
			this.inputAmount.maxLength = ("" + CreditCardType.maxCardPayment).length + 3;
			this.inputAmount.id = 'cc-input-amount';
			this.inputDD = document.createElement('input');
			this.inputDD.type= 'checkbox';
			this.inputDD.checked = this.inputDD.isChecked = false;
			this.inputDD.id = 'cc-input-dd';

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
			this.appendErrorHelp(tr.cells[1], this.inputEmail);

			this.paymentForm.appendChild(table);

			table = document.createElement('table');
			table.className = 'reflex';

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Credit Card Type:'));
			tr.insertCell(-1).appendChild(this.inputCardType);
			if (this.hasCancelButton) tr.cells[0].style.width = '35%';
			this.appendErrorHelp(tr.cells[1], this.inputCardType);

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Name on Card:'));
			tr.insertCell(-1).appendChild(this.inputName);
			this.appendErrorHelp(tr.cells[1], this.inputName);

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Card Number:'));
			tr.insertCell(-1).appendChild(this.inputCardNumber);
			this.appendErrorHelp(tr.cells[1], this.inputCardNumber);

			tr = table.insertRow(-1);
			var span = document.createElement('span');
			span.appendChild(document.createTextNode('CVV:'));
			
			var tooltip = document.createElement('div');
			tooltip.className = 'cvv-image-tooltip';
			var img = document.createElement('img');
			img.src = '../ui/img/template/cvv_visa.gif';
			tooltip.appendChild(img);
			img = document.createElement('img');
			img.src = '../ui/img/template/cvv_amex.gif';
			tooltip.appendChild(img);
			this.addCvvToolTip(span, tooltip);
			
			tr.insertCell(-1).appendChild(span);
			tr.insertCell(-1).appendChild(this.inputCVV);
			
			this.appendErrorHelp(tr.cells[1], this.inputCVV);

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Expiry Date:'));
			td = tr.insertCell(-1);
			td.appendChild(this.inputMonth);
			td.appendChild(this.inputYear);
			td.appendChild(document.createElement('span'));
			td.childNodes[2].appendChild(document.createTextNode('mm/yyyy'));
			this.appendErrorHelp(tr.cells[1], this.inputMonth);

			if (this.allowDD)
			{
				tr = table.insertRow(-1);
				tr.insertCell(-1).appendChild(document.createTextNode('Use Details For Direct Debit:'));
				tr.insertCell(-1).appendChild(this.inputDD);
				this.appendErrorHelp(tr.cells[1], this.inputDD);
			}

			tr = table.insertRow(-1);
			tr.insertCell(-1).appendChild(document.createTextNode('Amount to Pay:'));
			td = tr.insertCell(-1);
			this.appendCurrency(td)
			td.appendChild(this.inputAmount);
			this.appendErrorHelp(tr.cells[1], this.inputAmount);

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
			Event.observe(this.inputDD, 'click', this.changeDD.bind(this));

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

		this.inputDD.checked = this.inputDD.isChecked;

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
			$Alert('Please correct all errors and try again.', null, null, null, 'Invalid Values Entered');
			return;
		}

		if (!bolConfirmed)
		{
			this.confirmBeforeSubmit();
			return false; 
		}

		// Ensure that the user has agreed to the terms and conditions
		if (this.allowDD && this.inputDD.isChecked && !bolAgreedToTermsAndConditions)
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
				this.displayAmount.value,
				this.displaySurcharge.value,
				this.displayTotal.value,
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
		if (outcome == 'INVALID')
		{
			// Need to display the confirmation message and change buttons to OK
			$Alert('Your payment request could not be processed:\n\n' + response['MESSAGE'] + '\n\nPlease check your details and try again.');
			this.preparePopup();
			this.displayForm();
			return false;
		}

		// UNAVAILABLE = The SecurePay servers could not be contacted
		// FAILED = A problem occurred communicating with the SecurePay servers
		if (outcome == 'UNAVAILABLE' || outcome == 'FAILED')
		{
			// Need to display the confirmation message and change buttons to OK
			$Alert('Your payment request could not be processed:\n\n' + response['MESSAGE'] + '\n\nPlease try again later.');
			this.preparePopup();
			this.displayForm();
			return false;
		}

		// SUCCESS = The payment was made and DD details stored (if appropriate)
		if (outcome == 'SUCCESS')
		{
			// Need to display the confirmation message and change buttons to OK
			this.showConfirmationMessage(response['MESSAGE']);
			return true;
		}

		// The details of the response (the confirmation message)
		// need to be displayed to the user, assuming it all worked.
alert(outcome);
	},

	showConfirmationMessage: function(message)
	{
		var acknowledgeFuncBound = this.closeAfterCompletion.bind(this);

		// Create the content div and add the cancel/continue buttons
		var panel = document.createElement('div');
		var messageBox = document.createElement('div');
		messageBox.className = 'reflex-popup-text-content';

		var messageBar = document.createElement('h1');
		messageBar.appendChild(document.createTextNode('Your payment has been processed.'));
		panel.appendChild(messageBar);
		panel.appendChild(messageBox);



		var messageLines = message.replace(/\t{1,1}/g, '\u00a0\u00a0\u00a0\u00a0').split("\n");
		for(var i = 0, l = messageLines.length; i < l; i++)
		{
			if (i > 0)
			{
				messageBox.appendChild(document.createElement('br'));
			}
			messageBox.appendChild(document.createTextNode(messageLines[i]));
		}

		this.popup.setContent(panel);
		if (this.hasCancelButton)
		{
			var buttonAcknowledge = document.createElement('input');
			buttonAcknowledge.className = 'reflex-button';
			buttonAcknowledge.type = 'button';
			buttonAcknowledge.value = 'OK';

			Event.observe(buttonAcknowledge, 'click', acknowledgeFuncBound);

			this.popup.addCloseButton(this.cancel.bind(this));
			this.popup.setFooterButtons([buttonAcknowledge]);
		}

		this.popup.display();
		this.popup.recentre();
	},

	closeAfterCompletion: function()
	{
		if (this.hasCancelButton)
		{
			this.cancel();
			document.location.reload();
			return false;
		}
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
		this.displayAmount = this.displaySurcharge.cloneNode(true);
		this.displayAmount.value = this.tidyAmount(this.inputAmount.value);
		td.appendChild(this.displayAmount);

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
		// terms.style.width = "25em";
		// adjustment to make the terms scroll box similar width as its parent div.
		terms.style.width = "405";
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
			//alert("Please tick the checkbox to confirm that you have read, understood and agree be bound by these Terms and Conditions.");
			$Alert("Please tick the checkbox to confirm that you have read, understood and agree be bound by these Terms and Conditions.", null, null, null, 'Direct Debit Setup');
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

	addCvvToolTip: function(forElement, tooltipElement)
	{
		forElement.className += ' tooltip-source-element';
		var tooltip = { tooltip: tooltipElement, tooltipParent: this.getToolTipParent() };
		Event.observe(forElement, 'mouseover', this.showCvvToolTip.bind(tooltip));
		Event.observe(forElement, 'mouseout', this.hideCvvToolTip.bind(tooltip));
	},

	getToolTipParent: function()
	{
		return Reflex_Popup.overlay;
	},

	showCvvToolTip: function(event)
	{
		this.tooltipParent.appendChild(this.tooltip);
		this.tooltip.style.display = 'block';
		var element = Event.element(event = event ? event : document.event);
		var position = Element.cumulativeOffset(element);
		this.tooltip.style.top = '' + (position.top + element.clientHeight + 32) + 'px';
		this.tooltip.style.left = '' + (position.left) + 'px';
		this.tooltip.style.visibility = 'visible';
	},

	hideCvvToolTip: function()
	{
		this.tooltip.style.display = 'none';
		this.tooltip.style.visibility = 'hidden';
		if (this.tooltip.parentNode) this.tooltip.parentNode.removeChild(this.tooltip);
	},

	appendErrorHelp: function(td, errorInput)
	{
		var button  =document.createElement('input');
		button.type = 'button';
		button.className = 'validation-error-tootltip-button';
		var obj = { boundHoverFunction: null, boundUnhoverFunction: null, errorInput: errorInput, tootltip: null, button: button, tooltipParent: this.getToolTipParent() };
		obj.boundHoverFunction = this.showToolTip.bind(obj);
		obj.boundUnhoverFunction = this.hideToolTip.bind(obj);
		Event.observe(button, 'mouseover', obj.boundHoverFunction);
		Event.observe(button, 'mouseout', obj.boundUnhoverFunction);
		td.appendChild(button);
	},

	showToolTip: function(event)
	{
		if (typeof this.tooltip != 'undefined' && this.tooltip != null)
		{
			this.tooltip.innerHTML = '';
		}
		else
		{
			this.tooltip = document.createElement('div');
			this.tooltip.className = 'validation-error-tootltip';
		}
		var position = Element.cumulativeOffset(this.button);
		this.tooltip.appendChild(document.createTextNode(this.errorInput.getAttribute('validityError')));
		this.tooltip.style.top = '' + position.top + 'px';
		this.tooltip.style.left = '' + (position.left + this.button.clientWidth + 8) + 'px';
		this.tooltipParent.appendChild(this.tooltip);
	},

	hideToolTip: function(event)
	{
		if (this.tooltip.parentNode) this.tooltip.parentNode.removeChild(this.tooltip);
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
		var strValidityError = '';
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
					if (!bolAmountIsValid)
					{
						var minAmount = this.tidyAmount(cardType['minimum_amount'] / (1 + cardType['surcharge']));
						var maxAmount = this.tidyAmount(cardType['maximum_amount'] / (1 + cardType['surcharge']));
						strValidityError = 'Amount to Pay must be between $' + minAmount + ' and $' + maxAmount + ' for the selected Credit Card Type.';
					}
				}
				else
				{
					amount = Math.floor(amount);
					bolAmountIsValid = amount > 0;
					if (!bolAmountIsValid)
					{
						strValidityError = 'Amount to Pay must be greater than 0 (zero).';
					}
				}
			}
			else
			{
				strValidityError = 'The Amount to Pay enetered is invalid. It must must be number greater than 0 (zero).';
			}
			bolAmountEntered = true;
		}
		if (highlightBlankFields && !bolAmountEntered)
		{
			bolAmountEntered = true;
			bolAmountIsValid = false;
			if (!bolAmountIsValid)
			{
				strValidityError = 'You must enter an Amount to Pay.';
			}
		}
		this.setValidity(this.inputAmount, bolAmountEntered, true, bolAmountIsValid, strValidityError);

		this.updateBalances();
		this.updateSurcharges();
		return bolAmountEntered && bolAmountIsValid;
	},

	changeDD: function()
	{
		this.inputDD.isChecked = this.inputDD.checked;
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
		var strValidityError = '';
		if (!bolEmailIsValid)
		{
			strValidityError = 'You must enter a valid Email address.';
		}
		this.setValidity(this.inputEmail, bolEmailEntered, true, bolEmailIsValid, strValidityError);
		return bolEmailEntered && bolEmailIsValid;
	},

	changeExpiry: function(highlightBlankFields)
	{
		highlightBlankFields = typeof highlightBlankFields != 'boolean' ? false : highlightBlankFields;
		var bolExpiryValid = CreditCardPayment.checkExpiry(this.inputMonth.options[this.inputMonth.selectedIndex].value, this.inputYear.options[this.inputYear.selectedIndex].value);
		var strValidityError = '';
		if (!bolExpiryValid)
		{
			strValidityError = 'The selecetd Expiry Date is in the past.';
		}
		this.setValidity(this.inputMonth, true, true, bolExpiryValid, strValidityError);
		this.setValidity(this.inputYear, true, true, bolExpiryValid, strValidityError);
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
		var strValidityError = '';
		if (!bolNameIsValid)
		{
			strValidityError = 'You must enter the card holder name as shown on the credit card.';
		}
		this.setValidity(this.inputName, bolNameEntered, true, bolNameIsValid, strValidityError);
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

		var strTypeValidityError = bolCardTypeIsValid ? '' : 'You must select the Credit Card Type.';
		var strNumberValidityError = '';

		// Check that the card number is a valid card number. If not, highlight as invalid.
		var bolCardNumberEntered = this.inputCardNumber.value != '';
		var rubbish = this.inputCardNumber.value.replace(/[0-9 ]+/g, '');
		var cardNumber = this.inputCardNumber.value.replace(/[^0-9]+/g, '');
		var bolCardNumberIsValid = (rubbish == '') && (!bolCardNumberEntered || (cardNumber.length >= CreditCardType.minCardNumberLength && cardNumber.length <= CreditCardType.maxCardNumberLength));
		if (bolCardNumberEntered && bolCardNumberIsValid)
		{
			validateType = CreditCardPayment.getCCType(cardNumber);
			if (!validateType)
			{
				bolCardNumberIsValid = false;
				strNumberValidityError = 'The Card Number entered does not match any of the accepted credit card types.';
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
					strTypeValidityError = strNumberValidityError = 'The selected Credit Card Type does not match the Card Number entered.';
				}
				else
				{
					bolCardTypeEntered = bolCardTypeIsValid = true;
					bolCardNumberIsValid = CreditCardPayment.checkCardNumber(cardNumber, validateType['id']);
					strNumberValidityError = 'The Card Number entered is invalid.';
				}
			}
		}
		else if (bolCardNumberEntered)
		{
			validateType = (rubbish == '') && CreditCardPayment.getCCType(cardNumber);
			if (validateType)
			{
				this.setCardType(validateType, skipInitialTidy);
				bolCardTypeEntered = bolCardTypeIsValid = true;
				var strLens = '';
				for (var i = 0, l = validateType['valid_lengths'].length; i < l; i++)
				{
					strLens += ((i == 0) ? '' : (i == (l-1) ? ' or ' : ', ')) + validateType['valid_lengths'][i];
				}
				strNumberValidityError = 'Card numbers for the selected Credit Card Type are ' + strLens + ' digits long.';
			}
			else if (rubbish != '')
			{
				strNumberValidityError = 'The Card Number can only contain numbers and spaces.';
			}
			else if (cardNumber.length >= CreditCardType.minCardNumberLength)
			{
				strNumberValidityError = 'The Card Number entered does not match any of the accepted credit card types.';
			}
			else
			{
				strNumberValidityError = 'The Card Number entered is not long enough.';
			}
		}
		else if (highlightBlankFields)
		{
			bolCardTypeIsValid = false;
			bolCardTypeEntered = true;
			bolCardNumberIsValid = false;
			bolCardNumberEntered = true;
			strTypeValidityError = 'You must select the type of Credit Card Type for the payment.';
			strNumberValidityError = 'You must enter the Card Number.';
		}

		this.setValidity(this.inputCardType, bolCardTypeEntered, true, bolCardTypeIsValid, strTypeValidityError);
		this.setValidity(this.inputCardNumber, bolCardNumberEntered, true, bolCardNumberIsValid, strNumberValidityError);
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
		var strValidityError = '';
		if (this.inputCVV.value != '')
		{
			var cleansed = this.inputCVV.value.replace(/[^0-9]+/g, '');
			bolCvvIsValid = this.inputCVV.value.match(/^ *[0-9]+[ 0-9]*$/) && !isNaN(parseInt(cleansed));
			if (bolCvvIsValid)
			{
				if (cardType) 
				{
					bolCvvIsValid = cleansed.length == cardType['cvv_length'];
					strValidityError = 'The CVV for the selected Credit Card Type must be ' + cardType['cvv_length'] + ' digits long.';
				}
				else 
				{
					bolCvvIsValid = cleansed.length >= CreditCardType.minCvvLength && cleansed.length <= CreditCardType.maxCvvLength;
					var dif = CreditCardType.maxCvvLength - CreditCardType.minCvvLength;
					strValidityError = (dif == 0) ? 'Valid CVVs are between ' + CreditCardType.minCvvLength + ' digits long.'
												  : ((dif == 1) ? 'Valid CVVs are ' + CreditCardType.minCvvLength + ' or ' + CreditCardType.maxCvvLength + ' digits long.'
												  				: 'Valid CVVs are between ' + CreditCardType.minCvvLength + ' and ' + CreditCardType.maxCvvLength + ' digits long.');
				}
			}
			else
			{
				if (cardType) 
				{
					strValidityError = 'The CVV enetered is invalid. It must must be a ' + cardType['cvv_length'] + ' digit number for the selected Credit Card Type.';
				}
				else
				{
					var dif = CreditCardType.maxCvvLength - CreditCardType.minCvvLength;
					strValidityError = (dif == 0) ? 'The CVV enetered is invalid. It must must be a ' + CreditCardType.minCvvLength + ' digit number.'
												  : ((dif == 1) ? 'The CVV enetered is invalid. It must must be a ' + CreditCardType.minCvvLength + ' or ' + CreditCardType.maxCvvLength + ' digit number.'
												  				: 'The CVV enetered is invalid. It must must be a number between ' + CreditCardType.minCvvLength + ' and ' + CreditCardType.maxCvvLength + ' digits long.');
				}
			}
			bolCvvEntered = true;
		}
		if (highlightBlankFields && !bolCvvEntered)
		{
			bolCvvEntered = true;
			bolCvvIsValid = false;
			strValidityError = 'You must enter the CVV for the credit card.';
		}
		this.setValidity(this.inputCVV, bolCvvEntered, true, bolCvvIsValid, strValidityError);
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

	setValidity: function(element, entered, required, valid, validityError)
	{
		element.className = (!entered || valid) ? (required ? (entered ? 'valid' : 'required') : (entered ? 'valid' : '')) : 'invalid';
		element.setAttribute('validityError', validityError);
		element.parentNode.setAttribute('contains-invalid-input', (entered && !valid) ? 'true' : 'false');
		((entered && !valid) ? Element.addClassName : Element.removeClassName)(element.parentNode, 'contains-invalid-input');
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

	getToolTipParent: function()
	{
		return document.body;
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
		this.cache = document.createElement('div');
		this.cache.style.display = 'none';
		document.body.appendChild(this.cache);
		this.container = container;
		this.titlePane = document.createElement('div');
		this.titlePane.className = 'dynamically-loaded-panel-title';
		this.contentPane = document.createElement('div');
		this.contentPane.className = 'dynamically-loaded-panel-content';
		this.footerPane = document.createElement('div');
		this.footerPane.className = 'dynamically-loaded-panel-footer';
		this.container.appendChild(this.titlePane);
		this.container.appendChild(this.contentPane);
		this.container.appendChild(this.footerPane);
	},

	addCloseButton: function(callback)
	{
	},

	emptyElement: function(element)
	{
		for (var i = element.childNodes.length - 1; i >= 0; i--)
		{
			this.cache.appendChild(element.childNodes[i]);
		}
	},

	setTitle: function(title)
	{
		this.titlePane.innerHTML = '';
		this.titlePane.appendChild(document.createTextNode(title));
	},

	setContent: function(content)
	{
		this.emptyElement(this.contentPane);
		this.contentPane.appendChild(content);
	},

	setHeaderButtons: function(buttons)
	{
	},

	setFooterButtons: function(buttons)
	{
		this.emptyElement(this.footerPane);
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
