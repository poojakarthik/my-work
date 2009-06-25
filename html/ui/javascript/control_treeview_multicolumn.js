var Control_Treeview_Multicolumn	= Class.create
({
	initialize	: function(oColumnDefinition)
	{
		this.oTable				= {};
		this.oTable.domElement	= document.createElement('table');
		this.oTable.domElement.addClassName('treeview');
	},
	
	getElement	: function()
	{
		return this.oTable.domElement;
	}
});

Control_Treeview_Multicolumn.factory	= function(oColumnDefinition)
{
	return new Control_Treeview_Multicolumn(oColumnDefinition);
};