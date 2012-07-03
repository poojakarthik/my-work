var Class = require('fw/class');
var SalesPortal = require('sp/salesportal');

var self = new Class({
	extends : require('sp/guicomponent'),

	staticDataRequested: false,
	_getBlankDetailsObject: null,

	construct : function (obj) {
		if (obj == null) {
			this.object = this._getBlankDetailsObject();
		} else {
			this.object = obj;
		}

		this.elementGroups = {};
	},

	buildGUI : function () {},

	updateSummary : function (suggestion) {
		this.summaryContainer.appendChild(document.createTextNode(suggestion));
	},

	destroy : function () {

	},

	showValidationTip : function () {
		return false;
	},

	hasLoaded : function () {
		return true;
	},

	statics : {
		// Child classes should copy these properties (STATIC_INHERITANCE)
		STATIC_INHERITANCE : {
			// THESE VALUES SHOULD BE EXPLICITLY SET IN THE SUBCLASSES!
			product_type_module: null,

			// The following code should not be changed by subclasses!
			staticData: null,

			getProductTypeId : function () {
				return this.product_type_id;
			},

			getLoadStaticDataFunction : function () {
				return this.loadStaticData.bind(this);
			},

			autoloadAndRegister : function () {
				//Event.observe(window, 'load', this.getLoadStaticDataFunction());
				this.getLoadStaticDataFunction()();
			},

			loadStaticData : function () {
				//debugger;
				if (this.staticDataRequested || this.hasLoaded()) {
					if (!this.staticDataRequested) {
						self.registerModule(this);
						this.staticDataRequested = true;
						return;
					}
				}
				this.staticDataRequested = true;
				var remote$loadStaticData = SalesPortal.getRemoteFunction("ProductTypeModule", "loadData", this.receiveStaticData.bind(this));
				remote$loadStaticData(this.product_type_module);
			},

			receiveStaticData : function (staticData) {
				// Register this product type module
				this.staticData = staticData;
				self.registerModule(this);
			},

			hasLoaded : function () {
				return this.staticData != null;
			}
		},

		// Child classes do not need to copy these properties
		registeredModules: {},

		_getModulePath : function (sProductTypeModule) {
			return './producttypemodule/' + sProductTypeModule.toLowerCase().replace('_', '');
		},

		registerModule : function (module_class) {
			//alert("Registering module " + module_class.product_type_module);
			self.registeredModules[module_class.product_type_module] = module_class;
		},

		getModuleInstance : function (sProductTypeModule, oData) {
			if (!self.registeredModules[sProductTypeModule]) {
				//console.log(sProductTypeModule + " not registered");
				var sProductTypeModulePath = self._getModulePath(sProductTypeModule);
				module.provide([sProductTypeModulePath], function () {
					// We need to require() it to register
					require(sProductTypeModulePath);
					//console.log(sProductTypeModule + " module provided");
				});
			} else {
				return new (self.registeredModules[sProductTypeModule])(oData);
			}
		}

		/* Deprecated: based on old FW.Package architecture
		getModuleInstance: function(product_type_module, obj) {
			var module_class = self.registeredModules[product_type_module];
			if (module_class == null) {
				//alert(product_type_module + " not registered");
				console.log(product_type_module + " not registered");
				return null;
			} else {
				//alert(module_class);
				var instance = new module_class(obj);
				return instance;
			}
		}
		*/
	}
});

return self;