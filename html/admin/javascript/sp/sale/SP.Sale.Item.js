//3741 - 4042
FW.Package.create('SP.Sale.Item', {

	requires: ['SP.Sale.ProductTypeModule'],
	extends: 'FW.GUIComponent',


	product_type_module: null,
	instanceId: null,
	productType: null,
	productName: null,
	productModule: null,

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

		SP.Sale.Item.register(this);
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

	updateFromGUI: function()
	{
		if (this.isValid())
		{
			this.getProductModule().updateFromGUI();
		}
		else
		{
			return false;
		}
		return true;
	},

	isValid: function()
	{
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

	getProductModule: function()
	{
		if (this.productModule == null)
		{
			this.productModule = SP.Sale.ProductTypeModule.getModuleInstance(this.object.product_type_module, this.object.product_detail);
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

}, false);

FW.Package.extend(SP.Sale.Item, {

	unique: 1,
	instances: {},

	register: function(instance)
	{
		instance.instanceId = 'sale-item-' + (SP.Sale.Item.unique++);
		SP.Sale.Item.instances[instance.instanceId] = instance;
	},
	
	deregister: function(instance)
	{
		delete SP.Sale.Item.instances[instance.instanceId];
	
	},

	getInstance: function(instanceId)
	{
		return SP.Sale.Item.instances[instanceId];
	},

	collapseAll: function()
	{
		for (var instanceId in SP.Sale.Item.instances)
		{
			if (typeof SP.Sale.Item.instances[instanceId] == 'function')
			{
				continue;
			}
			SP.Sale.Item.instances[instanceId].collapse();
		}
	},

	expandAll: function()
	{
		for (var instanceId in SP.Sale.Item.instances)
		{
			if (typeof SP.Sale.Item.instances[instanceId] == 'function')
			{
				continue;
			}
			SP.Sale.Item.instances[instanceId].expand();
		}
	}

}, true);