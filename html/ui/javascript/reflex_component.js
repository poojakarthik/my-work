
Reflex_Component	= Class.create({
	initialize	: function () {
		// General init
		this.NODE	= null;
		
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
		var	oArgumentData = Reflex_Template.parseArguments($A(arguments));

		// Init the DOM
		this._buildUI();
		this.NODE.addClassName(Reflex_Component.COMPONENT_DEFAULT_CLASS);
		this.NODE.oReflexComponent	= this;

		// Attach Children
		for (var i=0, j=oArgumentData.aChildren.length; i < j; i++) {
			this.appendChild(oArgumentData.aChildren[i]);
		}
		
		// Set Config
		this.setConfig(oArgumentData.oConfig);

		// Sync the UI
		this._syncUI();
	},

	// Component Lifecycle
	//------------------------------------------------------------------------//
	// _buildUI(): Handles the creation of DOM nodes
	_buildUI	: function () {
		this.NODE	= $T(Reflex_Component.COMPONENT_DEFAULT_ELEMENT, {'class':Reflex_Component.COMPONENT_DEFAULT_CLASS});
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
		setTimeout(this.fire.bind(this, 'ready'), 0);
	},

	appendChild	: function (mChild) {
		var	oAttachmentNode	= this.getAttachmentNode();
		if (!oAttachmentNode) {
			throw "This Component does not have a default attachment node"
		}
		return oAttachmentNode.appendChild(Reflex_Template.extractNode(mChild));
	},

	insertBefore	: function (mChild, mBeforeChild) {
		var	oAttachmentNode	= this.getAttachmentNode();
		if (!oAttachmentNode) {
			throw "This Component does not have a default attachment node"
		}
		return oAttachmentNode.insertBefore(Reflex_Template.extractNode(mChild), Reflex_Template.extractNode(mBeforeChild));
	},

	replaceChild	: function (mChild, mOldChild) {
		var	oAttachmentNode	= this.getAttachmentNode();
		if (!oAttachmentNode) {
			throw "This Component does not have a default attachment node"
		}
		return oAttachmentNode.replaceChild(Reflex_Template.extractNode(mChild), Reflex_Template.extractNode(mOldChild));
	},

	removeChild	: function (mChild) {
		var	oAttachmentNode	= this.getAttachmentNode();
		if (!oAttachmentNode) {
			throw "This Component does not have a default attachment node"
		}
		return oAttachmentNode.removeChild(Reflex_Template.extractNode(mChild));
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
	}
});

Reflex_Component.COMPONENT_DEFAULT_ELEMENT	= 'div';
Reflex_Component.COMPONENT_DEFAULT_CLASS	= 'reflex-component';

Reflex_Component._parseChildReflexComponent	= function (mNodeContainer) {
	return mNodeContainer instanceof Reflex_Component ? mNodeContainer.getNode() : null;
};

// Register the Reflex_Template Child Node Parser
if (Reflex_Template && Reflex_Template.aChildNodeParsers && Reflex_Template.aChildNodeParsers.indexOf(Reflex_Template.aChildNodeParsers) === -1) {
	Reflex_Template.aChildNodeParsers.unshift(Reflex_Component._parseChildReflexComponent);
}

Reflex.mixin(Reflex_Component, Observable);
Reflex.mixin(Reflex_Component, Configurable);
Reflex.mixin(Reflex_Component, Pluginable);
