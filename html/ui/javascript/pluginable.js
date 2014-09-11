
var Pluginable = {
	PLUGINS : {},
	
	plug : function(sName, oPluginClass) {
		if (this.PLUGINS[sName]) {
			throw "Plugin name '" + sName + "' is already in use.";
		}

		var oPlugin 		= new oPluginClass(this);
		this.PLUGINS[sName]	= oPlugin;
	},

	plugin : function(sName) {
		if (!this.PLUGINS[sName]) {
			throw "Invalid plugin name, '" + sName + "' not attached.";
		}
		return this.PLUGINS[sName];
	}
};
