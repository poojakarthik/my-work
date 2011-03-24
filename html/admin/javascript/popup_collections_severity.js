
var Popup_Collections_Severity = Class.create(Reflex_Popup, 
{
	initialize : function($super, iSeverityId, bRenderMode, fnOnCommit)
	{
		bRenderMode = (Object.isUndefined(bRenderMode) ? true : !!bRenderMode);
	
		$super(bRenderMode ? 50 : 35);
		
		this._fnOnCommit = fnOnCommit;
		
		var oContentDiv = $T.div();
		new Component_Collections_Severity(oContentDiv, iSeverityId, bRenderMode, this._committed.bind(this), this._createCancelled.bind(this));
		
		if (bRenderMode)
		{
			this.setTitle((iSeverityId ? 'Edit ' : 'Create ') + 'Severity');
			this.setIcon('../admin/img/template/new.png');
		}
		else
		{
			this.setTitle('View Severity');
			this.setIcon('../admin/img/template/magnifier.png');
		}
		this.setContent(oContentDiv);
		this.addCloseButton();
		this.display();
	},
	
	_committed : function(iSeverityId)
	{
		if (this._fnOnCommit)
		{
			this._fnOnCommit(iSeverityId);
		}
		this.hide();
	},
	
	_createCancelled : function()
	{
		this.hide();
	},
});