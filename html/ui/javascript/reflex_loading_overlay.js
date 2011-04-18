
var Reflex_Loading_Overlay = Class.create(
{
	initialize	: function(sMessage, sExtraClass)
	{
		this._oElement = 	$T.div({class: 'reflex-loading-overlay' + (!!sExtraClass ? ' ' + sExtraClass : '')},
								$T.div({class: 'reflex-loading-overlay-overlay'}),
								$T.div({class: 'reflex-loading-overlay-message'},
									$T.div({class: 'reflex-loading-overlay-message-text'},
										sMessage ? sMessage : 'Loading'
									),
									$T.img({class: 'reflex-loading-overlay-message-flower', src: '../admin/img/template/loading.gif'})
								)
							);
	},
	
	// Public
	
	attachTo : function(oElement)
	{
		var oPositionedOffset 		= oElement.positionedOffset();
		this._oElement.style.left	= oPositionedOffset.left + 'px';
		this._oElement.style.top 	= oPositionedOffset.top + 'px';
		this._oElement.style.width 	= oElement.getWidth() + 'px';
		this._oElement.style.height	= oElement.getHeight() + 'px';
		oElement.appendChild(this._oElement);
		oElement.addClassName('reflex-loading-overlay-parent');
	},
	
	detach : function()
	{
		if (this._oElement.up())
		{
			this._oElement.removeClassName('reflex-loading-overlay-parent');
			this._oElement.remove();
		}
	}
});
