var Email_Template_Table = Class.create(
{

	initialize: function(oBodyDef, oHeaderDef, oTableDef)
	{
		this._body 		= $T.tbody(oBodyDef);
		this._headerRow = $T.tr(	);
		this._header 	= $T.thead(oHeaderDef,this._headerRow );		
		this._table 	= $T.table(oTableDef,
									this._header,
									this._body										
									);	
	},
	
	addHeaderField: function (oHeader)
	{
		this._headerRow.appendChild(oHeader);	
	},
	
	appendRow: function(tr)
	{
		this._body.appendChild(tr);	
	},
	
	getElement: function()
	{
		return this._table;	
	},
	
	rowCount: function()
	{
		return this._body.childElementCount;
	
	}
	


});