var Permission_Tree	= Class.create
({
	initialize	: function(sRenderHeirarchy)
	{
		// Create Tree Grid
		this._oTreeGrid				= {};
		this._oTreeGrid.oControl	= new Control_Tree_Grid();
		
		this._oTreeGrid.oColumns									= {};
		this._oTreeGrid.oColumns[Control_Tree_Grid.COLUMN_CHECK]	= {};
		this._oTreeGrid.oColumns[Control_Tree_Grid.COLUMN_LABEL]	= {};
		//this._oTreeGrid.oColumns['start_date']						= {};
		//this._oTreeGrid.oColumns['end_date']						= {};
		//this._oTreeGrid.oColumns['edit_dates']						= {};
		this._oTreeGrid.oControl.setColumns(this._oTreeGrid.oColumns);
		
		this._oTreeGrid.oControl.getElement().style.overflowY	= 'hidden';
		
		this._oTreeGrid.oLoading						= {};
		this._oTreeGrid.oLoading.domElement				= document.createElement('div');
		this._oTreeGrid.oLoading.domElement.innerHTML	= "<div><span><img src='../admin/img/template/loading.gif' alt='' title='Loading' /> Retrieving list of Operations...</span></div>";
		this._oTreeGrid.oLoading.domElement.addClassName('loading');
		
		this._oTreeGrid.oControl.getElement().appendChild(this._oTreeGrid.oLoading.domElement);
		
		this.setRenderHeirarchy(sRenderHeirarchy);
	},
	
	setLoadingSplashVisible	: function (bVisible)
	{
		if (bVisible)
		{
			this._oTreeGrid.oControl.getElement().style.overflowY	= 'hidden';
			this._oTreeGrid.oLoading.domElement.style.display		= 'block';
		}
		else
		{
			this._oTreeGrid.oControl.getElement().style.overflowY	= 'scroll';
			this._oTreeGrid.oLoading.domElement.style.display		= 'none';
		}
	},
	
	getTreeGrid	: function()
	{
		return this._oTreeGrid.oControl;
	},
	
	getElement	: function()
	{
		return this._oTreeGrid.oControl.getElement();
	},
	
	setRenderHeirarchy	: function(sRenderHeirarchy)
	{
		switch (sRenderHeirarchy)
		{
			// Valid
			case Permission_Tree.RENDER_HEIRARCHY_INCLUDES:
			case Permission_Tree.RENDER_HEIRARCHY_GROUPED:
			case Permission_Tree.RENDER_HEIRARCHY_NONE:
				this._sRenderHeirarchy	= sRenderHeirarchy;
				break;
			
			// Invalid
			case undefined:
			case null:
				this._sRenderHeirarchy	= Permission_Tree.RENDER_HEIRARCHY_NONE;
				break;
				
			default:
				throw "'"+sRenderHeirarchy+"' is not a valid Render Heirarchy";
				break;
		}
		
		if (this._bLoaded)
		{
			// Rebuild the Tree
			this._buildTree();
		}
	},
	
	getRenderHeirarchy	: function()
	{
		return this._sRenderHeirarchy;
	},
	
	setEditable	: function(bEditable)
	{
		// Set Tree Editable
		this._oTreeGrid.oControl.setEditable(bEditable);
	},
	
	isEditable	: function()
	{
		return this._oTreeGrid.oControl.isEditable();
	},
	
	render	: function()
	{
		this._oTreeGrid.oControl.render();
	}
});

Permission_Tree.RENDER_HEIRARCHY_NONE		= 'none';
Permission_Tree.RENDER_HEIRARCHY_INCLUDES	= 'includes';
Permission_Tree.RENDER_HEIRARCHY_GROUPED	= 'grouped';

Permission_Tree.TREE_GRID_DATATYPE_OPERATION				= {};
Permission_Tree.TREE_GRID_DATATYPE_OPERATION.sName			= 'operation';
Permission_Tree.TREE_GRID_DATATYPE_OPERATION.sDescription	= 'Operation';
Permission_Tree.TREE_GRID_DATATYPE_OPERATION.sIconSource	= '../admin/img/template/operation.png';

Permission_Tree.TREE_GRID_DATATYPE_OPERATION_PROFILE				= {};
Permission_Tree.TREE_GRID_DATATYPE_OPERATION_PROFILE.sName			= 'operation_profile';
Permission_Tree.TREE_GRID_DATATYPE_OPERATION_PROFILE.sDescription	= 'Operation Profile';
Permission_Tree.TREE_GRID_DATATYPE_OPERATION_PROFILE.sIconSource	= '../admin/img/template/operation_profile.png';