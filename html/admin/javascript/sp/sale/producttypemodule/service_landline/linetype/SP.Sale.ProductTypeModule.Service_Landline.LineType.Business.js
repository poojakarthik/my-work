FW.Package.create('SP.Sale.ProductTypeModule.Service_Landline.LineType.Business', {
	requires: ['FW.GUIComponent'],
	extends: 'SP.Sale.ProductTypeModule',


	_getBlankDetailsObject: function()
	{
		var saleAccount = SP.Sale.getInstance().getSaleAccount();
		//saleAccount.updateFromGUI();

		return {
			id: null,
			company_name: saleAccount.getBusinessName(),
			abn: saleAccount.getABN(),
			trading_name: saleAccount.getTradingName(),
		};
	},

	buildGUI: function()
	{
		this.setWorkingTable(this.detailsContainer);

		// This function wraps up the definition of max string length validation, so that it's neater
		var fncGetStringValidationFunc =	function(intMaxLength)
											{
												return window._validate.getStringLengthValidationFunc(intMaxLength);
											}

		var lengths = SP.Sale.ProductTypeModule.Service_Landline.staticData.lengths.landlineBusiness;
		this.addElementGroup('company_name', new FW.GUIComponent.TextInputGroup(this.getCompanyName(), true, fncGetStringValidationFunc(lengths.companyName)),'Company Name');
		this.addElementGroup('abn', new FW.GUIComponent.TextInputGroup(this.getABN(), true, window._validate.australianBusinessNumber.bind(this)),'ABN');
		this.addElementGroup('trading_name', new FW.GUIComponent.TextInputGroup(this.getTradingName(), false, fncGetStringValidationFunc(lengths.tradingName)),'Trading Name');
		
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