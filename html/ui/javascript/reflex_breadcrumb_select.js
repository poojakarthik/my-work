
var Reflex_Breadcrumb_Select = Class.create(
{
	initialize : function(sTitle, aLevels, fnOnChange)
	{
		// Config
		this._sTitle 		= sTitle;
		this._aLevels 		= aLevels;
		this._fnOnChange	= fnOnChange;
		
		// Properties
		this._aCrumbs			= [];
		this._aPopulated		= [];
		this._aOptions			= [];
		this._aSelectedOptions	= [];
		this._iMaxLevel			= 0;
		this._bInitialised		= false;
		
		document.body.observe('mousedown', this._documentBodyMouseDown.bind(this));
		
		this._buildUI();
		this._refresh();
	},
	
	// Public
	
	populate : function()
	{
		this._clearValuesFromMaxLevel();
		this._refresh();
	},
	
	getValueAtLevel : function(iLevel)
	{
		if (Object.isUndefined(this._aOptions[iLevel]))
		{
			// Level not populated yet
			return null;
		}
		return this._aOptions[iLevel][this._aSelectedOptions[iLevel]].mValue;
	},
	
	getElement : function()
	{
		return this._oElement;
	},
	
	// Private
	
	_buildUI : function()
	{
		this._oUL		= 	$T.ul({class: 'reset horizontal'});
		this._oElement 	= 	$T.div({class: 'reflex-breadcrumb-select'},
									$T.span(this._sTitle),
									this._oUL
								);
		
		for (var i = 0; i < this._aLevels.length; i++)
		{
			var oCrumb =	$T.li({class: 'reflex-breadcrumb-select-crumb'},
								$T.img({class: 'reflex-breadcrumb-select-crumb-separator', src: '../admin/img/template/menu_open_right.png'}),
								$T.div({class: 'reflex-breadcrumb-select-crumb-value-container'},
									$T.span({class: 'reflex-breadcrumb-select-crumb-value'},
										''
									),
									$T.img({class: 'reflex-breadcrumb-select-crumb-value-remove', src: '../admin/img/template/delete.png', alt: 'Clear', title: 'Clear'}).observe('mousedown', this._clearLevel.bind(this, i))
								).observe('mousedown', this._showList.bind(this, i))
							);
			
			this._aCrumbs.push(oCrumb);
			this._oUL.appendChild(oCrumb);
		}
		
		this._oList = $T.div({class: 'reflex-breadcrumb-select-list'});
	},
	
	_createSeparator : function()
	{
		return 	$T.span({class: 'reflex-breadcrumb-select-separator'},
					'>'
				);
	},
	
	_refresh : function(iLevel)
	{
		iLevel = (iLevel ? iLevel : 0);
		if (this._aLevels[iLevel])
		{
			if (this._aPopulated[iLevel])
			{
				// Populated already
				// Show the value at this level
				this._updateDisplayValue(iLevel);
				
				// Refresh next level
				this._refresh(iLevel + 1);
			}
			else
			{
				// Populate then refresh the next level
				this._aLevels[iLevel].fnPopulate(this, this._populateSelect.bind(this, iLevel, this._refresh.bind(this, iLevel + 1)));
			}
		}
		else
		{
			// No more levels to refresh
			this._bInitialised = true;
		}
	},
	
	_updateDisplayValue : function(iLevel)
	{
		var iWidthBefore		= this._aCrumbs[iLevel].offsetWidth;
		var oValueElement 		= this._aCrumbs[iLevel].select('.reflex-breadcrumb-select-crumb-value').first();
		oValueElement.innerHTML	= this._aOptions[iLevel][this._aSelectedOptions[iLevel]].sText;
		var iWidthAfter			= this._aCrumbs[iLevel].offsetWidth;
		
		if (iWidthAfter != iWidthBefore)
		{
			this._oList.style.left = (this._oList.offsetLeft + (iWidthBefore - iWidthAfter)) + 'px';
		}
	},
	
	_populateSelect	: function(iLevel, fnCallback, aOptions)
	{
		// Add an all option
		aOptions.unshift({mValue: null, sText: 'All ' + this._aLevels[iLevel].sName});
		
		this._aOptions[iLevel]		= aOptions;
		this._aPopulated[iLevel]	= true;
		
		var iSelectedOptionIndex = this._aSelectedOptions[iLevel];
		if (Object.isUndefined(iSelectedOptionIndex))
		{
			this._aSelectedOptions[iLevel] = 0;
		}
		
		this._updateDisplayValue(iLevel);
		
		if (fnCallback)
		{
			fnCallback();
		}
	},
	
	_showList : function(iLevel)
	{
		// Position the list
		var oElement	= this._aCrumbs[iLevel].select('.reflex-breadcrumb-select-crumb-value-container').first();
		var iValueT		= 0;
		var iValueL		= 0;
		var iHeight		= oElement.offsetHeight;
		do 
		{
			iValueT += oElement.offsetTop || 0;
			iValueL += oElement.offsetLeft || 0;
			oElement = oElement.offsetParent;
		}
		while (oElement);
		
		iPositionX	= iValueL + 2;
		iPositionY	= iValueT + iHeight + window.scrollY;
		
		this._oList.style.left	= iPositionX + 'px';
		this._oList.style.top	= iPositionY + 'px';
		
		// Clear list
		while (this._oList.firstChild)
		{
			this._oList.firstChild.remove();
		}
		
		// Add options to list
		if (this._aOptions[iLevel])
		{
			for (var i = 0; i < this._aOptions[iLevel].length; i++)
			{
				this._oList.appendChild(this._createOption(iLevel, i, this._aOptions[iLevel][i]));
			}
		}
		
		// Show list
		document.body.appendChild(this._oList);
	},
	
	_createOption : function(iLevel, iIndex, oOptionData)
	{
		var oDiv = 	$T.div({class : 'reflex-breadcrumb-select-list-option'},
						oOptionData.sText
					);
		
		oDiv.iLevel			= iLevel;
		oDiv.iOptionIndex	= iIndex;
		
		oDiv.observe('click', this._optionSelected.bind(this));
		return oDiv;
	},
	
	_clearValuesFromMaxLevel : function()
	{
		for (var i = this._iMaxLevel; i < this._aLevels.length; i++)
		{
			this._aPopulated[i] 		= false;
			this._aSelectedOptions[i]	= 0;
		}
	},
	
	_clearLevel : function(iLevel, oEvent)
	{
		this._iMaxLevel = iLevel;
		
		this._hideList();
		this._clearValuesFromMaxLevel();
		this._refresh();
		
		if (this._bInitialised && this._fnOnChange)
		{
			this._fnOnChange();
		}
		
		oEvent.stop();
	},
	
	_optionSelected : function(oEvent)
	{
		var iLevel 						= oEvent.target.iLevel;
		this._aSelectedOptions[iLevel]	= oEvent.target.iOptionIndex;
		this._iMaxLevel 				= iLevel + 1;
		
		this._hideList();
		this._clearValuesFromMaxLevel();
		this._refresh();
		
		if (this._bInitialised && this._fnOnChange)
		{
			this._fnOnChange();
		}
	},
	
	_documentBodyMouseDown	: function(oEvent)
	{
		if (this._oList.visible() && this._oList.up())
		{
			var bHide	= true;
			var oTarget	= oEvent.target;
			while (oTarget && (oTarget !== document.body))
			{
				if ((oTarget === this._oList) || (oTarget === this._oElement))
				{
					bHide = false;
					break;
				}
				oTarget	= oTarget.parentNode;
			}
			
			if (bHide)
			{
				this._hideList();
			}
		}
	},
	
	_hideList : function()
	{
		if (this._oList.up())
		{
			this._oList.remove();
		}
	}
});