
Control_Tree	= Class.create
({
	initialize	: function()
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
		this.oRootNode	= new Control_Tree_Node();
		oBody.appendChild(this.oRootNode.getElement());
		
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
			var sName	= Control_Tree.sanitiseName(sColumnName);
			this.oColumns[sName]	=	{
											sName	: sName,
											sTitle	: oColumns[sColumnName].sTitle ? oColumns[sColumnName].sTitle : sColumnName,
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
			iCount++;
			oWidths[sName]	= this.oColumns[sName].fWidth;
		}
		
		oWidths	= Control_Tree.normalisePercentages(oWidths);
		
		for (sName in this.oColumns)
		{
			this.oColumns[sName].fWidth	= oWidths[sName];
		}
		
		this.paint();
	},
	
	getRootNode	: function()
	{
		return this.oRootNode;
	},
	
	paint	: function()
	{
		// Redraw the Columns
		this.oHeader.childElements().each(Element.remove, Element);
		for (sName in this.oColumns)
		{
			var	oColumn	= document.createElement('li');
			oColumn.innerHTML	= this.oColumns[sName].sTitle.escapeHTML();
			this.oHeader.appendChild(oColumn);
		}
		
		// Redraw the children!
		this.oRootNode.paint(this.oColumns);
	}
});

Control_Tree.sanitiseName	= function(sName)
{
	return sName.strip().toLowerCase().replace(/\W+/g, '-');
};

Control_Tree.normalisePercentages	= function(mPercentages)
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
		fTotalPercent	+= (fPercent === 'NaN') ? 100 / iCount : fPercent;
	}
	
	var fRatio	= fTotalPercent / 100;
	
	var oNormalisedPercentages	= {};
	for (i in oPercentages)
	{
		oNormalisedPercentages[i]	= fRatio * oPercentages[i];
	}
	
	return oNormalisedPercentages;
};
