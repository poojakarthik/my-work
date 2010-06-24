
/*
 * Sort
 * 
 * Controls sorting of given Dataset_Ajax and Pagination objects.
 * 
 * You can specify which field alias' to sort on and the direction in which to sort. 
 * 
 * You can also register an element that when clicked on, toggles the sorting for a particular field alias.
 * 		OFF -> ASC -> DESC -> OFF -> ...
 * 
*/
var Sort	= Class.create
({
	initialize	: function(oDataSetAjax, oPagination, bSingleFieldSort, fFieldUpdateCallback)
	{
		this._oDataSetAjax			= oDataSetAjax;
		this._oPagination			= oPagination;
		this._bSingleFieldSort		= bSingleFieldSort;
		this._aSortedFields			= [];
		this._hFields				= {};
		this._fFieldUpdateCallback	= fFieldUpdateCallback;
	},
	
	//
	// Public methods
	//
	
	registerToggleElement	: function(oElement, sField, sDefaultDirection)
	{
		oElement.observe('click', this._toggleField.bind(this, sField));
		
		if (!this._hFields[sField])
		{
			this.registerField(sField, sDefaultDirection);
		}
	},
	
	registerField	: function(sField, sDefaultDirection)
	{
		this._hFields[sField]	= Sort.DIRECTION_OFF;
		
		if (sDefaultDirection)
		{
			this.sortField(sField, sDefaultDirection, true);
		}
	},
	
	sortField	: function(sField, sDirection, bCancelRefresh)
	{
		if (this._bSingleFieldSort)
		{
			// Clear all other sorted fields
			for (i = 0; i < this._aSortedFields.length; i++)
			{
				if (this._aSortedFields[i] != sField)
				{
					this._setFieldDirection(this._aSortedFields[i], Sort.DIRECTION_OFF, true);
				}
			}
			
			this._aSortedFields	= [];
		}
		
		this._setFieldDirection(sField, sDirection, bCancelRefresh);
		this._aSortedFields.push(sField);
	},
	
	getSortData	: function()
	{
		var hFields		= {};
		var sDirection	= null;
		for (var sField in this._hFields)
		{
			sDirection		= this._hFields[sField];
			
			if (sDirection != Sort.DIRECTION_OFF)
			{
				hFields[sField]	= sDirection;
			}
		}
		
		return hFields;
	},
	
	refreshData	: function(bCancelRefresh)
	{
		// Make a copy of the sort data for data set ajax
		var hFields	= this.getSortData();
		
		// Get the dataset with new sort date
		this._oDataSetAjax.setSortingFields(hFields);
		
		if (!bCancelRefresh)
		{
			this._oPagination.getCurrentPage();
		}
	},
	
	getSortDirection	: function(sField)
	{
		return this._hFields[sField];
	},
	
	isRegistered	: function(sField)
	{
		return (this._hFields[sField] !== null && (typeof this._hFields[sField] != 'undefined'));
	},
	
	//
	// Private methods
	//
	
	_setFieldDirection	: function(sField, sDirection, bCancelRefresh)
	{
		this._hFields[sField]	= sDirection;
		
		if (this._fFieldUpdateCallback)
		{
			this._fFieldUpdateCallback(sField, sDirection);
		}
		
		if (sDirection != Sort.DIRECTION_OFF)
		{
			this.refreshData(bCancelRefresh);
		}
	},
	
	_toggleField	: function(sField)
	{
		var sNewDirection	= Sort.DIRECTION_OFF;
		switch (this._hFields[sField])
		{
			case Sort.DIRECTION_ASC:
				sNewDirection	= Sort.DIRECTION_DESC;
				break;
			case Sort.DIRECTION_OFF:
			case Sort.DIRECTION_DESC:
				sNewDirection	= Sort.DIRECTION_ASC;
				break;
		}
		
		this.sortField(sField, sNewDirection);
	}
});

Sort.DIRECTION_OFF	= '';
Sort.DIRECTION_ASC	= 'ASC';
Sort.DIRECTION_DESC	= 'DESC';

