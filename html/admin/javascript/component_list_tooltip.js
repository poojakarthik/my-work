
var Component_List_Tooltip = Class.create(
{
	initialize : function(iWidth, iPositionLeftOrRight)
	{
		this._oElement =	$T.div({class: 'component-list-tooltip'},
								$T.table(
									$T.tbody()	
								)
							);
		this._oTBody = this._oElement.select('tbody').first();
		
		if (iWidth)
		{
			this._oElement.style.width = iWidth + 'em';
		}
		
		this._bRemove				= false;
		this._iPositionLeftOrRight	= (iPositionLeftOrRight ? iPositionLeftOrRight : Component_List_Tooltip.POSITION_LEFT);
		this._aRows					= [];
	},
	
	// Public
	
	clearRegisteredRows : function()
	{
		for (var i = 0; i < this._aRows.length; i++)
		{
			this._aRows[i].stopObserving('mouseover');
			this._aRows[i].stopObserving('mouseout');
		}
		this._aRows = [];
	},
	
	registerRow : function(oTR, hContent)
	{
		oTR.observe('mouseover', this._show.bind(this, oTR, hContent));
		oTR.observe('mouseout', this._hide.bind(this));
		this._aRows.push(oTR);
	},
	
	// Protected
	
	_show : function(oTR, hContent, iWidth)
	{
		this._bRemove			= false;
		this._oTBody.innerHTML	= '';
		
		for (var sLabel in hContent)
		{
			this._oTBody.appendChild(
				$T.tr(
					$T.th(sLabel),
					$T.td(hContent[sLabel])
				)
			);
		}
		
		if (!this._oElement.up())
		{
			document.body.appendChild(this._oElement);
		}
		
		// Left/Right postition
		var oPositionedOffset = oTR.viewportOffset();
		switch(this._iPositionLeftOrRight)
		{
			case Component_List_Tooltip.POSITION_LEFT:
				this._oElement.style.left = (oPositionedOffset.left - this._oElement.getWidth()) + 'px';
				break;
			case Component_List_Tooltip.POSITION_RIGHT:
				this._oElement.style.left = (oPositionedOffset.left + oTR.getWidth()) + 'px';
				break;
		}
		
		
		this._oElement.style.top = (oPositionedOffset.top + window.scrollY) + 'px';
	},
	
	_hide : function()
	{
		this._bRemove = true;
		setTimeout(this._remove.bind(this), 500);
	},
	
	_remove : function()
	{
		if (this._bRemove && this._oElement.up())
		{
			this._oElement.remove();
		}
		this._bRemove = false;
	}
});

Object.extend(Component_List_Tooltip, 
{
	POSITION_LEFT	: 1,
	POSITION_RIGHT	: 2
});