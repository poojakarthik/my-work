
var	$D		= require('./dom/factory'),
	Class	= require('./class');

var	_isPlainObject	= function (oObject) {
		return oObject && oObject.constructor.prototype === Object.prototype;
	},
	_parseArguments	= function (aArguments) {
	var	oArguments	= {
		aChildren	: []
	};
	
	// Optional Arguments
	if (aArguments.length) {
		// Param 2: Config (optional)
		if (_isPlainObject(aArgs[0])) {
			var	oAttributes	= aArgs.shift();

			for (var sAttribute in oAttributes) {
				if (oAttributes.hasOwnProperty(sAttribute)) {
					if (sAttribute.length > 2 && sAttribute.substring(0, 2).toLowerCase() == 'on') {
						// Event handler
						if (_isString(oAttributes[sAttribute])) {
							// Attribute on*
							oElement.setAttribute(sAttribute, oAttributes[sAttribute]);
						} else if (_isFunction(oAttributes[sAttribute])) {
							// Single Callback
							oElement.addEventListener(sAttribute.substring(2), oAttributes[sAttribute], false);
						} else if (_isArray(oAttributes[sAttribute])) {
							// Array of Callbacks
							for (i=0; i < oAttributes[sAttribute].length; i++) {
								if (_isFunction(oAttributes[sAttribute][i])) {
									oElement.addEventListener(sAttribute.substring(2), oAttributes[sAttribute][i], false);
								}
							}
						}
					} else {
						// Other attribute
						oElement.setAttribute(sAttribute, oAttributes[sAttribute]);
					}
				}
			}
		}

		// Param 3+: Children (optional)
		var	aChildren	= exports.$parseChildren(aArgs);
		for (i=0; i < aChildren.length; i++) {
			oElement.appendChild(aChildren[i]);
		}
	}

	return oArguments;
};

// CLASS
var	Component	= new Class({
	implements	: [require('./observable'), require('./configurable'), require('./pluginable')],

	_bInitialised	: false,
	_bReady			: false,

	construct	: function () {
		
		// General init
		this.CONFIG	= Object.extend({
			sExtraClass	: {
				fnSetter : (function (mValue) {
					var	sExistingClass = this.get('sExtraClass');
					if (sExistingClass) {
						this.getNode().removeClassName(sExistingClass);
					}
					
					if (mValue) {
						mValue = String(mValue);
						this.getNode().addClassName(mValue);
					}
					return mValue ? mValue : null;
				}).bind(this)
			},
			fnOnReady : {
				fnSetter : function(mValue) {
					if (Object.isFunction(mValue)) {
						this.observe('ready', mValue);
						return mValue;
					};
				}.bind(this)
			},
			PLUGINS : {
				fnSetter : function(hPluginConfig) {
					// Check if the plugins value has been set
					if (this.CONFIG.PLUGINS.mValue) {
						throw "Can't set the PLUGINS config value twice.";
					}
					
					// Plug them in
					Object.keys(hPluginConfig).each(
						function(sPluginName) {
							this.plug(sPluginName, hPluginConfig[sPluginName]);
						}.bind(this)
					);
					
					return hPluginConfig;
				}.bind(this)
			}
		}, this.CONFIG || {});

		this.PLUGINS = {};
		
		this.ATTACHMENTS = Object.extend({
			'default' : this.getNode.bind(this)
		}, this.ATTACHMENTS || {});
		
		// Parameters
		var	aArgs	= $A(arguments),
			oConfig	= (_isPlainObject(aArgs[0])) ? aArgs[0] : null;
		
		// Init the DOM
		// Allow a "special" config property called NODE, which is an alternate node to build from
		// A child class may choose to ignore this existing NODE
		this.NODE	= null;
		if (oConfig && oConfig.NODE) {
			this.NODE	= oConfig.NODE;
			delete oConfig.NODE;
		}

		this._buildUI();
		this.NODE.addClassName(Component.COMPONENT_DEFAULT_CLASS);
		this.NODE.oFWComponent	= this;

		// Attach Children
		var	aChildren	= $D.$parseChildren(aArgs.slice(oConfig ? 1 : 0));
		for (var i=0, j=aChildren.length; i < j; i++) {
			this.appendChild(aChildren[i]);
		}
		
		// Set Config
		this.setConfig(oConfig);

		// Sync the UI
		this._syncUI();
		this._bInitialised = true;
	},

	// Configuration
	// set(): Override `Configurable#set()` so we can sync the UI afterwards
	set	: function (sAttribute, mValue) {
		var	oConfig;
		if (typeof sAttribute == 'string') {
			// Manipulating a single value
			oConfig				= {};
			oConfig[sAttribute]	= mValue;
		} else {
			// Updating multiple values
			oConfig	= sAttribute;
		}

		// Update the config
		this._super(oConfig);

		// Re-sync the UI, but only if we have already been initialised
		if (this._bInitialised) {
			this._syncUI();
		}
	},

	// Events
	fire	: function (sEvent) {
		return this._super.apply(this, arguments);
	},

	// Component Lifecycle
	//------------------------------------------------------------------------//
	// _buildUI(): Handles the creation of DOM nodes
	_buildUI	: function () {
		this.NODE	= $D[Component.COMPONENT_DEFAULT_ELEMENT]({'class':Component.COMPONENT_DEFAULT_CLASS});
	},

	// _syncUI(): Updates the UI with the Config.  Should call _onReady() when the UI is ready for use
	_syncUI	: function () {
		// Default implementation does nothing other than set the component to "ready"
		this._onReady();
	},

	// "Final" Methods (no need to override that I can think of)
	//------------------------------------------------------------------------//
	// getNode(): Returns the root node/element for this component
	getNode	: function () {
		return this.NODE;
	},

	// getAttachmentNode(): Gets the (or one of multiple) DOM nodes which we can attach children to.  Can return null.
	getAttachmentNode	: function (sName) {
		sName	= sName || 'default';
		if (this.ATTACHMENTS.hasOwnProperty(sName)) {
			return (typeof this.ATTACHMENTS[sName] === 'function') ? this.ATTACHMENTS[sName]() : this.ATTACHMENTS[sName];
		}
		return null;
	},

	_onReady	: function () {
		// Fire the 'ready' event asynchronously
		this._bReady = true;
		setTimeout(this.fire.bind(this, 'ready'), 0);
	},

	appendChild	: function (mChild) {
		var	oAttachmentNode	= this.getAttachmentNode();
		if (!oAttachmentNode) {
			throw "This Component does not have a default attachment node"
		}
		return oAttachmentNode.appendChild($D.$parseChild(mChild));
	},

	insertBefore	: function (mChild, mBeforeChild) {
		var	oAttachmentNode	= this.getAttachmentNode();
		if (!oAttachmentNode) {
			throw "This Component does not have a default attachment node"
		}
		return oAttachmentNode.insertBefore($D.$parseChild(mChild), $D.$parseChild(mBeforeChild));
	},

	addChildren	: function (aChildren, mBeforeChild) {
		var	oAttachmentNode	= this.getAttachmentNode();
		if (!oAttachmentNode) {
			throw "This Component does not have a default attachment node"
		}

		var oFragment	= $D.$fragment();
		for (var i=0; i < aChildren.length; i++) {
			oFragment.appendChild($D.$parseChild(mChild));
		}
		oAttachmentNode.insertBefore($D.$parseChild(mChild), $D.$parseChild(mBeforeChild));
		return this;
	},

	replaceChild	: function (mChild, mOldChild) {
		var	oAttachmentNode	= this.getAttachmentNode();
		if (!oAttachmentNode) {
			throw "This Component does not have a default attachment node"
		}
		return oAttachmentNode.replaceChild($D.$parseChild(mChild), $D.$parseChild(mOldChild));
	},

	removeChild	: function (mChild) {
		var	oAttachmentNode	= this.getAttachmentNode();
		if (!oAttachmentNode) {
			throw "This Component does not have a default attachment node"
		}
		return oAttachmentNode.removeChild($D.$parseChild(mChild));
	},

	select	: function () {
		var	oNode	= this.getAttachmentNode();
		return oNode.select.apply(oNode, $A(arguments));
	},

	_select	: function () {
		return this.NODE.select.apply(this.NODE, $A(arguments));
	},

	$$	: function () {
		return this.select.apply(this, $A(arguments));
	},

	_$$	: function () {
		return this._select.apply(this, $A(arguments));
	},

	// STATICS
	statics	: {
		COMPONENT_DEFAULT_ELEMENT	: 'div',
		COMPONENT_DEFAULT_CLASS		: 'fw-component'
	}
});

// Register the $D Child Node Parser
$D.$addChildParser(function (mChild) {
	return mChild instanceof Component ? mChild.getNode() : null;
});

// Return constructor as our API/exports
return Component;
