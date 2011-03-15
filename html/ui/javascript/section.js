
var Section	= Class.create(
{
	initialize	: function(bFitted, sClassName)
	{
		this._bFitted		= bFitted;
		this._sClassName	= sClassName;
		this._buildContainer();
	},
	
	//
	// Public methods
	//
	
	getElement	: function()
	{
		return this._oContainer;
	},
	
	/*
	 * Dom element to override title contents
	 */
	setTitleContent	: function(mContent, bClearTitleStyling)
	{
		this._checkForTitle();
		
		if (bClearTitleStyling)
		{
			this._oTitle.removeClassName('section-header-title');
		}
		
		this._clearElement(this._oTitle);
		this._oTitle.appendChild(this._getElementFromContent(mContent));
	},
	
	/*
	 * Sets the icon in the title (to the left)
	 */
	setTitleIcon	: function(mIcon)
	{
		this._checkForTitle();
		
		if (this._oTitleIcon)
		{
			this._oTitleIcon.remove();
		}
		
		this._oTitleIcon	= (mIcon.nodeName ? mIcon : $T.img({src: mIcon}));
		
		if (this._oTitleText)
		{
			this._oTitle.insertBefore(this._oTitleIcon, this._oTitleText);
		}
		else
		{
			this._oTitle.appendChild(this._oTitleIcon);
		}
	},
	
	/*
	 * Sets the text in the title (to the right of optional icon)
	 */ 
	setTitleText	: function(sText)
	{
		this._checkForTitle();
		
		if (this._oTitleText)
		{
			this._oTitleText.innerHTML	= sText;
		}
		else
		{
			this._oTitleText	= $T.span(sText);
			this._oTitle.appendChild(this._oTitleText);
		}
	},
	
	/*
	 * Attaches to the content div
	 */ 
	setContent	: function(mContent)
	{
		this._clearElement(this._oContent);
		this._oContent.appendChild(this._getElementFromContent(mContent));
	},
	
	/*
	 * Adds a dom element to the content div
	 */
	addToContent	: function(mContent)
	{
		this._oContent.appendChild(this._getElementFromContent(mContent));
	},
	
	/*
	 * Adds a dom element to the header options div
	 */
	addToHeaderOptions	: function(mContent)
	{
		this._checkForHeaderOptions();
		this._oHeaderOptionsUL.appendChild($T.li(this._getElementFromContent(mContent)));
	},
	
	/*
	 * Overrides the footer content
	 */
	setFooterContent	: function(mContent)
	{
		this._checkForFooter();
		this._clearElement(this._oFooter);
		this._oFooter.appendChild(this._getElementFromContent(mContent));
	},
	
	/*
	 * Adds dom to the footer
	 */
	addToFooter 	: function(mContent)
	{
		this._checkForFooter();
		this._oFooter.appendChild(this._getElementFromContent(mContent));
	},
	
	getContentElement	: function()
	{
		return this._oContent;
	},
	
	//
	// Private methods
	//
	
	_clearElement	: function(oElement)
	{
		var aChildren	= oElement.childElements();
		for (var i = 0; i < aChildren.length; i++)
		{
			aChildren[i].remove();
		}
	},
	
	_buildContainer	: function()
	{
		var oDiv	= 	$T.div({class: 'section' + (this._sClassName ? ' ' + this._sClassName : '')},
							$T.div({class: 'section-header'}
								// section-header-title absent by default
								// section-header-options absent by default
							),
							$T.div({class: 'section-content' + (this._bFitted ? ' section-content-fitted' : '')})
							// section-footer is absent by default
						);
		this._oHeader		= oDiv.select('div.section-header').first();
		this._oContent		= oDiv.select('div.section-content').first();
		this._oContainer	= oDiv;
	},
	
	_checkForTitle	: function()
	{
		if (!this._oTitle)
		{
			this._oTitle	= $T.div({class: 'section-header-title'});
			
			if (this._oHeaderOptions)
			{
				this._oHeader.insertBefore(this._oTitle, this._oHeaderOptions);
			}
			else
			{
				this._oHeader.appendChild(this._oTitle);
			}
		}
	},
	
	_checkForHeaderOptions	: function()
	{
		if (!this._oHeaderOptions)
		{
			this._oHeaderOptionsUL	= $T.ul({class: 'reset horizontal'});
			this._oHeaderOptions	= 	$T.div({class: 'section-header-options'},
											this._oHeaderOptionsUL
										);
			this._oHeader.appendChild(this._oHeaderOptions);
		}
	},
	
	_checkForFooter	: function()
	{
		if (!this._oFooter)
		{
			this._oFooter	= $T.div({class: 'section-footer'});
			this._oContainer.appendChild(this._oFooter);
		}
	},
	
	_getElementFromContent	: function(mContent)
	{
		if (typeof mContent == 'undefined')
		{
			mContent	= '';
		}
		
		if (mContent.nodeName)
		{
			return mContent;
		}
		else
		{
			return document.createTextNode(mContent.toString());
		}
	}
});