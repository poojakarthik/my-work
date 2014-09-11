var Permission_Tree_Operation_Profile	= Class.create(/* extends */ Permission_Tree,
({
	initialize	: function($super, sRenderHeirarchy, aSelected)
	{
		// Parent Constructor
		$super(sRenderHeirarchy);
		
		// Create Tree Grid		
		this._oTreeGrid.oControl.addDataType(Permission_Tree.TREE_GRID_DATATYPE_OPERATION.sName, Permission_Tree.TREE_GRID_DATATYPE_OPERATION.sDescription, Permission_Tree.TREE_GRID_DATATYPE_OPERATION.sIconSource);
		this._oTreeGrid.oControl.addDataType(Permission_Tree.TREE_GRID_DATATYPE_OPERATION.sName, Permission_Tree.TREE_GRID_DATATYPE_OPERATION_PROFILE.sDescription, Permission_Tree.TREE_GRID_DATATYPE_OPERATION_PROFILE.sIconSource, this.onSelectHandler.bind(this));
		
		// Set Selected Operation Profiles
		this._aSelected	= [];
		this.setSelected(aSelected);
		
		this.setRenderHeirarchy(sRenderHeirarchy);
		
		// Load Operations
		Operation.getAllIndexed(this._loadOperations.bind(this));
		Operation_Profile.getAllIndexed(this._loadOperationProfiles.bind(this));
	},
	
	_loadOperations	: function(oResultSet)
	{
		this.oOperations		= oResultSet;
		
		for (iOperationId in this.oOperations)
		{
			this._oOperationDetails[iOperationId]					= {};
			this._oOperationDetails[iOperationId].bSelected			= false;
			this._oOperationDetails[iOperationId].aNodeInstances	= [];
		}
		this._bLoaded	= true;
		
		// Rebuild the Tree
		this._buildTree();
		
		// Hide Loading screen
		this._oTreeGrid.oControl.getElement().style.overflowY	= 'scroll';
		this._oTreeGrid.oLoading.domElement.style.display		= 'none';
	},
	
	_loadOperationProfiles	: function(oResultSet)
	{
		this.oOperationProfiles		= oResultSet;
		this._oOperationProfileDetails	= {};
		
		for (iOperationProfileId in this.oOperationProfiles)
		{
			this._oOperationProfileDetails[iOperationProfileId]					= {};
			this._oOperationProfileDetails[iOperationProfileId].bSelected		= false;
			this._oOperationProfileDetails[iOperationProfileId].aNodeInstances	= [];
		}
		this._bLoaded	= true;
		
		// Hide Loading screen
		this._load();
	},
	
	_load	: function()
	{
		this._bLoaded	= (this.oOperations && this.oOperationProfiles);
		
		if (this._bLoaded)
		{
			this.setLoadingSplashVisible(false);
			
			// Rebuild the Tree
			this._buildTree();
		}
	},
	
	_buildTree	: function()
	{
		// Remove the existing tree nodes
		this._oTreeGrid.oControl.purgeChildren();
		
		// Rebuild the tree
		for (iOperationProfileId in this.oOperationProfiles)
		{
			// Render top-level Nodes
			switch (this._sRenderHeirarchy)
			{
				case Permission_Tree.RENDER_HEIRARCHY_INCLUDES:
					// Only Operation Profiles with no dependants
					if (!this.oOperationProfiles[iOperationProfileId].aDependants || !this.oOperationProfiles[iOperationProfileId].aDependants.length)
					{
						this._oTreeGrid.oControl.appendChild(this._convertOperationProfileToTreeNode(iOperationProfileId));
					}
					break;
					
				case Permission_Tree.RENDER_HEIRARCHY_GROUPED:
					// Only Operation Profiles with no prerequisites
					if (!this.oOperationProfiles[iOperationProfileId].aPrerequisites || !this.oOperationProfiles[iOperationProfileId].aPrerequisites.length)
					{
						this._oTreeGrid.oControl.appendChild(this._convertOperationProfileToTreeNode(iOperationProfileId));
					}
					break;
				
				default:
					// All Operation Profiles
					this._oTreeGrid.oControl.appendChild(this._convertOperationProfileToTreeNode(iOperationProfileId));
					break;
			}
		}
		
		// Render the Tree
		this._oTreeGrid.oControl.render();
	},
	
	_convertOperationToTreeNode	: function(iOperationId)
	{
		if (!this.oOperations[iOperationId])
		{
			throw "Operation with Id #"+iOperationId+" does not exist!";
		}
		
		var oNodeContent	=	{}
		oNodeContent[Control_Tree_Grid.COLUMN_LABEL]	= this.oOperations[iOperationId].name;
		oNodeContent[Control_Tree_Grid.COLUMN_VALUE]	= iOperationId;
		oNodeContent[Control_Tree_Grid.COLUMN_CHECK]	=	{
																mValue		: iOperationId,
																bChecked	: (this._aSelected.indexOf(iOperationId) > -1)
															};
		var oNode			= new Control_Tree_Grid_Node_Data(oNodeContent, Permission_Tree.TREE_GRID_DATATYPE_OPERATION.sName);
		
		this._oOperationDetails[iOperationId].aNodeInstances.push(oNode);
		
		switch (this._sRenderHeirarchy)
		{
			case Permission_Tree.RENDER_HEIRARCHY_INCLUDES:
				// Render all prerequisites
				if (this.oOperations[iOperationId].aPrerequisites && this.oOperations[iOperationId].aPrerequisites.length)
				{
					for (var i = 0; i < this.oOperations[iOperationId].aPrerequisites.length; i++)
					{
						oNode.appendChild(this._convertOperationToTreeNode(this.oOperations[iOperationId].aPrerequisites[i]));
					}
				}
				break;
				
			case Permission_Tree.RENDER_HEIRARCHY_GROUPED:
				// Render all dependants
				if (this.oOperations[iOperationId].aDependants && this.oOperations[iOperationId].aDependants.length)
				{
					for (var i = 0; i < this.oOperations[iOperationId].aDependants.length; i++)
					{
						oNode.appendChild(this._convertOperationToTreeNode(this.oOperations[iOperationId].aDependants[i]));
					}
				}
				break;
		}
		
		return oNode;
	},
	
	_convertOperationToTreeNode	: function(iOperationId)
	{
		if (!this.oOperations[iOperationId])
		{
			throw "Operation with Id #"+iOperationId+" does not exist!";
		}
		
		var oNodeContent	=	{}
		oNodeContent[Control_Tree_Grid.COLUMN_LABEL]	= this.oOperations[iOperationId].name;
		oNodeContent[Control_Tree_Grid.COLUMN_VALUE]	= iOperationId;
		var oNode			= new Control_Tree_Grid_Node_Data(oNodeContent, Permission_Tree.TREE_GRID_DATATYPE_OPERATION.sName);
		
		this._oOperationDetails[iOperationId].aNodeInstances.push(oNode);
		
		return oNode;
	},
	
	setOperationSelected	: function(iOperationId, bSelected)
	{
		if (!this.oOperations[iOperationId] || !this._oOperationDetails[iOperationId])
		{
			throw "Operation with Id #"+iOperationId+" does not exist!";
		}
		
		this._oOperationDetails[iOperationId].bSelected	= bSelected;
		
		// Update all Node instances
		for (var i = 0; i < this._oOperationDetails[iOperationId].aNodeInstances.length; i++)
		{
			this._oOperationDetails[iOperationId].aNodeInstances[i].setSelected(bSelected, true);
		}
		
		// Update all Prerequisites
		if (this._oOperationDetails[iOperationId].bSelected === true)
		{
			for (var i = 0; i < this.oOperations[iOperationId].aPrerequisites.length; i++)
			{
				this.setOperationSelected(this.oOperations[iOperationId].aPrerequisites[i], true);
			}
		}
		
		// Update all Dependants
		if (this._oOperationDetails[iOperationId].bSelected === false)
		{
			for (var i = 0; i < this.oOperations[iOperationId].aDependants.length; i++)
			{
				this.setOperationSelected(this.oOperations[iOperationId].aDependants[i], false);
			}
		}
	},
	
	onSelectHandler	: function(oNode)
	{
		this.setOperationSelected(oNode.getValue(), oNode.isSelected());
	},
	
	setSelected	: function(aSelected)
	{
		//Reflex_Debug.asHTMLPopup(aSelected);
		
		if (aSelected)
		{
			for (iOperationId in this.oOperations)
			{
				this.setOperationSelected(iOperationId, aSelected.indexOf(parseInt(iOperationId)) > -1);
			}
		}
		else
		{
			//alert("Setting "+aSelected+" selected Operations");
		}
	},
	
	getSelected	: function()
	{
		var aSelected	= [];
		for (iOperationId in this._oOperationDetails)
		{
			if (this._oOperationDetails[iOperationId].bSelected)
			{
				aSelected.push(iOperationId);
			}
		}
		
		return aSelected;
	}
});