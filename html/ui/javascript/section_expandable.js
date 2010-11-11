
var Section_Expandable	= Class.create( /* extends */ Section,
{
	initialize	: function($super, bFitted, sClassName, bStartExpanded, bUseExpandIcon)
	{
		this._bUseExpandIcon	= ((Object.isUndefined(bUseExpandIcon) || bUseExpandIcon) ? true : false);
		
		$super(bFitted, sClassName);
		
		this.setExpanded((Object.isUndefined(bStartExpanded) || bStartExpanded) ? true : false);
	},
	
	//
	// Public methods
	//
	
	setExpanded	: function(bExpanded)
	{
		this._bExpanded	= (bExpanded ? true : false);
		this._updateContent();
		this._updateExpandIcon();
	},
	
	// Override
	addToHeaderOptions	: function(mContent)
	{
		this._checkForHeaderOptions();
		
		var oNewOption 	= $T.li(this._getElementFromContent(mContent));
		if (this._oExpandLI)
		{
			this._oHeaderOptionsUL.insertBefore(oNewOption, this._oExpandLI);
		}
		else
		{
			this._oHeaderOptionsUL.appendChild(oNewOption);
		}
		
		return oNewOption;
	},
	
	//
	// Private methods
	//
	
	// Override
	_buildContainer	: function($super)
	{
		$super();
		
		// Create the expand/retract icon
		this._oExpandImage	= $T.img();
		this._oExpandDiv	= 	$T.div({class: 'section-expandable-expand'},
									this._oExpandImage
								);
		this._oExpandDiv.observe('click', this._toggleExpandedState.bind(this));
		
		this._updateExpandIcon();
		
		// Add to the header
		this._checkForHeaderOptions();
		this._oHeaderOptions.addClassName('section-expandable-header-options');
		
		if (this._bUseExpandIcon)
		{
			this._oExpandLI	= this.addToHeaderOptions(this._oExpandDiv);
		}
		else
		{
			this._oExpandLI	= false;
		}
	},
	
	_toggleExpandedState	: function()
	{
		this._bExpanded	= !this._bExpanded;
		this._updateContent();
		this._updateExpandIcon();
	},
	
	_updateContent	: function()
	{
		if (this._bExpanded)
		{
			this._oContent.show();
		}
		else
		{
			this._oContent.hide();
		}
	},
	
	_updateExpandIcon	: function()
	{
		var sSrc	= '';
		var sAlt	= '';
		if (this._bExpanded)
		{
			sSrc	= Section_Expandable.EXPANDED_IMAGE_SOURCE;
			sAlt	= Section_Expandable.EXPANDED_ALT_TEXT;
			this._oHeader.removeClassName('section-expandable-header-retracted');
		}
		else
		{
			sSrc	= Section_Expandable.RETRACTED_IMAGE_SOURCE;
			sAlt	= Section_Expandable.RETRACTED_ALT_TEXT;
			this._oHeader.addClassName('section-expandable-header-retracted');
		}
		
		this._oExpandImage.src		= sSrc;
		this._oExpandImage.alt		= sAlt;
		this._oExpandImage.title	= sAlt;
	}
});

Section_Expandable.EXPANDED_IMAGE_SOURCE	= '../admin/img/template/retract.png';
Section_Expandable.EXPANDED_ALT_TEXT		= 'Retract the Section';
Section_Expandable.RETRACTED_IMAGE_SOURCE	= '../admin/img/template/expand.png';
Section_Expandable.RETRACTED_ALT_TEXT		= 'Expand the Section';
