//4026 -4155
FW.Package.create('SP.Sale.ProductTypeModule', {


	extends: 'FW.GUIComponent',


			staticDataRequested: false,
			_getBlankDetailsObject: null,

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

			showValidationTip: function()
			{
				return false;
			},

			hasLoaded: function()
			{
				return true;
			}
		}
);

Object.extend(SP.Sale.ProductTypeModule, {

	// THESE VALUES SHOULD BE SET IN THE SUBCLASSES!
	product_type_module: null,

	// The following code should not be changed by subclasses!
	registeredModules: [],

	staticData: null,

	registerModule: function(module_class)
	{
		//alert("Registering module " + module_class.product_type_module);
		SP.Sale.ProductTypeModule.registeredModules[module_class.product_type_module] = module_class;
	},

	getModuleInstance: function(product_type_module, obj)
	{
		var module_class = SP.Sale.ProductTypeModule.registeredModules[product_type_module];
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
		//Event.observe(window, 'load', this.getLoadStaticDataFunction());
		this.getLoadStaticDataFunction()();
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