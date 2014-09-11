
Reflex.Control.Tree	= Class.create(/* extends */Reflex.Control,
{
	initialize	: function($super)
	{
		// Create DOM Elements
		this.oContainer	= document.createElement('div');
		this.oContainer.addClassName('reflex-tree');
		
		this.oHeader	= document.createElement('ul'),
		this.oBody		= document.createElement('ul'),
		this.oFooter	= document.createElement('ul');
		
		this.oHeader.addClassName('reflex-tree-header');
		this.oBody.addClassName('reflex-tree-body');
		this.oFooter.addClassName('reflex-tree-footer');
		
		// Create Root Node
		this.oRootNode	= new Reflex.Control.Tree.Node.Root({label : 'ROOT NODE'});
		this.oBody.appendChild(this.oRootNode.getElement());
		this.oRootNode.setExpanded(true);
		
		this.oContainer.appendChild(this.oHeader);
		this.oContainer.appendChild(this.oBody);
		this.oContainer.appendChild(this.oFooter);
	},
	
	getElement	: function()
	{
		return this.oContainer;
	},
	
	setColumns	: function(oColumns)
	{
		this.oColumns	= {};
		for (sColumnName in oColumns)
		{
			var sName	= Reflex.Control.Tree.sanitiseName(sColumnName);
			this.oColumns[sName]	=	{
											sName	: sName,
											sTitle	: ((typeof oColumns[sColumnName].sTitle != 'undefined') && (oColumns[sColumnName].sTitle != null)) ? oColumns[sColumnName].sTitle : sColumnName,
											fWidth	: oColumns[sColumnName].fWidth ? oColumns[sColumnName].fWidth : 'auto'
										};
		}
		
		// Force a 'label' Column
		if (!('label' in this.oColumns))
		{
			this.oColumns[sName]	=	{
											sName	: 'label',
											sTitle	: '',		// No Title
											fWidth	: 'auto'
										};
		}
		
		var	oWidths	= {};
		for (sName in this.oColumns)
		{
			oWidths[sName]	= this.oColumns[sName].fWidth;
		}
		
		oWidths	= Reflex.Control.Tree.normalisePercentages(oWidths);
		
		var	oReflexStyle	= Reflex_Style.getInstance();
		
		for (sName in this.oColumns)
		{
			this.oColumns[sName].fWidth	= oWidths[sName];
			
			// Create a CSS Class for this Column
			this.oColumns[sName].sClassName	= ['-reflex-tree-column', sName, Reflex.Control.Tree.iColumnCSSClassCounter].join('-');
			oReflexStyle.addRule(
				"li." + this.oColumns[sName].sClassName, 
				"width: " + this.oColumns[sName].fWidth + "%;" + 
				" min-width: " + this.oColumns[sName].fWidth + "%;" + 
				" max-width: " + this.oColumns[sName].fWidth + "%"
			);
		}
		
		// Increment so that if another tree is created on the same page the column width css doesn't conflict
		Reflex.Control.Tree.iColumnCSSClassCounter++;
		
		this.paint();
	},
	
	getRootNode	: function()
	{
		return this.oRootNode;
	},
	
	expandAll	: function()
	{
		this.oRootNode.expandAll();
	},
	
	collapseAll	: function()
	{
		this.oRootNode.collapseAll();
	},
	
	paint	: function()
	{
		// Redraw the Columns
		this.oHeader.childElements().each(Element.remove, Element);
		for (sName in this.oColumns)
		{
			var	oColumn	= document.createElement('li');
			oColumn.innerHTML	= "<span>"+this.oColumns[sName].sTitle.escapeHTML()+"</span>";
			oColumn.addClassName('reflex-tree-header-column');
			oColumn.addClassName(this.oColumns[sName].sClassName);
			this.oHeader.appendChild(oColumn);
		}
		
		// Redraw the children!
		this.oRootNode.setExpanded(true, !this.oRootNode.isExpanded());
		this.oRootNode.paint(this.oColumns);
	},
	
	setEditable	: function(bEditable)
	{
		this._isEditable	= bEditable;
	},
	
	isEditable	: function()
	{
		return this._isEditable;
	}
});

Reflex.Control.Tree.sanitiseName	= function(sName)
{
	return sName.strip().toLowerCase().replace(/\W+/g, '-');
};


Reflex.Control.Tree.iColumnCSSClassCounter	= 0;

Reflex.Control.Tree.normalisePercentages	= function(mPercentages)
{
	var oPercentages;
	if (mPercentages instanceof Array)
	{
		for (var i = 0, j = mPercentages.length; i < j; i++)
		{
			oPercentages[i]	= mPercentages[i];
		}
	}
	else
	{
		oPercentages	= mPercentages;
	}
	
	var iCount	= 0;
	for (i in oPercentages)
	{
		iCount++;
	}
	
	var	fTotalPercent	= 0;
	for (i in oPercentages)
	{
		var fPercent	= parseFloat(oPercentages[i]);
		oPercentages[i]	= isNaN(fPercent) ? 100 / iCount : fPercent;
		fTotalPercent	+= oPercentages[i];
	}
	
	var fRatio	= fTotalPercent / 100;
	
	var oNormalisedPercentages	= {};
	for (i in oPercentages)
	{
		oNormalisedPercentages[i]	= fRatio * oPercentages[i];
	}
	
	return oNormalisedPercentages;
};
