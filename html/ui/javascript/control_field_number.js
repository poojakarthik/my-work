var Control_Field_Number	= Class.create(/* extends */ Control_Field,
{
	initialize	: function($super, sLabel, sLabelSeparator)
	{
		// Parent
		$super(sLabel, sLabelSeparator);

		// Create the DOM Elements
		this.oControlOutput.oEdit		= document.createElement('input');
		this.oControlOutput.oEdit.type	= 'number';
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oEdit);

		this.oControlOutput.oView	= document.createElement('span');
		this.oControlOutput.oElement.appendChild(this.oControlOutput.oView);

		this._aOnChangeCallbacks	= [];

		this.validate();

		this.addEventListeners();
	},

	setMinimumValue	: function (mMinimumValue) {
		var	fMinimumValue	= parseFloat(mMinimumValue);
		if (typeof fMinimumValue === 'number' && fMinimumValue === fMinimumValue) {
			this._fMinimumValue	= fMinimumValue;
			this.oControlOutput.oEdit.setAttribute('min', this._fMinimumValue);
		} else if (!mMinimumValue) {
			this._fMinimumValue	= null;
			this.oControlOutput.oEdit.removeAttribute('min');
		} else {
			throw "Unable to set Minimum Value to '"+mMinimumValue+"'";
		}
	},

	setMaximumValue	: function (mMaximumValue) {
		var	fMaximumValue	= parseFloat(mMaximumValue);
		if (typeof fMaximumValue === 'number' && fMaximumValue === fMaximumValue) {
			this._fMaximumValue	= fMaximumValue;
			this.oControlOutput.oEdit.setAttribute('max', this._fMaximumValue);
		} else if (!mMaximumValue) {
			this._fMinimumValue	= null;
			this.oControlOutput.oEdit.removeAttribute('max');
		} else {
			throw "Unable to set Maximum Value to '"+mMaximumValue+"'";
		}
	},

	setDecimalPlaces	: function (iDecimalPlaces) {
		var	mProvidedData	= iDecimalPlaces,
			iDecimalPlaces	= parseInt(iDecimalPlaces, 10);
		if (typeof iDecimalPlaces === 'number' && iDecimalPlaces === iDecimalPlaces && iDecimalPlaces >= 0) {
			this._iDecimalPlaces	= iDecimalPlaces;
			this.oControlOutput.oEdit.setAttribute('step', 1 / Math.pow(10, this._iDecimalPlaces));
		} else if (!mProvidedData || iDecimalPlaces <= 0) {
			this._iDecimalPlaces	= null;
			this.oControlOutput.oEdit.removeAttribute('step');
		} else {
			throw "Unable to set Decimal Places to '"+mProvidedData+"'";
		}
	},

	trim	: function($super, bReturnValue) {
		var	mValue	= $super(bReturnValue);

		if (!bReturnValue) {
			mValue	= this.getElementValue();
		}

		mValue	= $A(mValue.match(/[\-\+]?[\d]*[\.]?[\d]*/)).first();

		// Return or set Value
		if (bReturnValue) {
			return mValue;
		} else {
			this.setElementValue(mValue);
		}
	},

	isValid	: function ($super, bSuppressException) {
		//debugger;

		var	bValid	= $super(bSuppressException),
			mValue	= this.getElementValue(),
			fValue	= parseFloat(mValue);
		
		if (fValue === null || mValue === null) {
			return bValid;
		}

		try {
			// General Format
			if (!mValue.match(/[\-\+]?[\d]*([\.][\d]+)?/)) {
				throw "Not a numeric value";
			}

			// Decimal Places
			if (this._iDecimalPlaces !== null
				&& mValue.toString().indexOf('.') > -1
				&& this._iDecimalPlaces < (mValue.toString().length - (mValue.toString().indexOf('.') + 1))
			) {
				throw ((this._iDecimalPlaces === 0) ? 'No' : 'Only' + this._iDecimalPlaces) + ' decimal places are allowed';
			}

			// Minimum Value
			if (this._fMinimumValue !== null
				&& this._fMinimumValue > mValue
			) {
				throw 'Below the Minimum allowed ('+this._fMinimumValue+')';
			}

			// Maximum Value
			if (this._fMaximumValue !== null
				&& this._fMaximumValue < mValue
			) {
				throw 'Above the Maximum allowed ('+this._fMaximumValue+')';
			}
		} catch (mException) {
			if (bSuppressException === false) {
				throw mException;
			} else {
				bValid	= false;
			}
		}

		return bValid;
	},

	getElementValue	: function()
	{
		return this.oControlOutput.oEdit.value;
	},

	setElementValue	: function(mValue)
	{
		this.oControlOutput.oEdit.value	= mValue;
	},

	updateElementValue	: function()
	{
		var	mValue	= this.getValue();

		this.setElementValue(mValue);
		this.oControlOutput.oView.innerHTML	= mValue;
	},

	addEventListeners	: function()
	{
		this.aEventHandlers					= {};
		this.aEventHandlers.fnValueChange	= this._valueChange.bind(this);

		this.oControlOutput.oEdit.observe('click'	, this.aEventHandlers.fnValueChange);
		this.oControlOutput.oEdit.observe('change'	, this.aEventHandlers.fnValueChange);
		this.oControlOutput.oEdit.observe('keyup'	, this.aEventHandlers.fnValueChange);
	},

	removeEventListeners	: function()
	{
		this.oControlOutput.oEdit.stopObserving('click'		, this.aEventHandlers.fnValueChange);
		this.oControlOutput.oEdit.stopObserving('change'	, this.aEventHandlers.fnValueChange);
		this.oControlOutput.oEdit.stopObserving('keyup'		, this.aEventHandlers.fnValueChange);
	},

	addOnChangeCallback	: function(fnCallback)
	{
		this._aOnChangeCallbacks.push(fnCallback);
	},

	_valueChange	: function()
	{
		this.validate();

		for (var i = 0; i < this._aOnChangeCallbacks.length; i++)
		{
			this._aOnChangeCallbacks[i]();
		}
	},

	disableInput	: function()
	{
		if (this.bRenderMode == Control_Field.RENDER_MODE_EDIT)
		{
			this.oControlOutput.oEdit.disabled	= true;
		}
	},

	enableInput	: function()
	{
		if (this.bRenderMode == Control_Field.RENDER_MODE_EDIT)
		{
			this.oControlOutput.oEdit.removeAttribute('disabled');
		}
	}
});