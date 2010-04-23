
Reflex.Control.Tree.Node.Checkable	= Class.create(/* extends */Reflex.Control.Tree.Node,
{
	initialize	:	function($super, oData, mValue, bEditable, fnOnCheck)
	{
		$super(oData);
		this.fnOnCheck			= fnOnCheck;
		this.mValue				= mValue;
		this.oCheckboxElement	= $T.input({type: 'checkbox'});
		this.oCheckboxElement.observe('click', this._checkedStateChanged.bind(this));
		this.setEditable(bEditable);
		this.setEnabled(true);
	},
	
	setCheckedState	: function(bChecked, bBypassCallback)
	{
		if (this.bEnabled)
		{
			this.oCheckboxElement.checked = (bChecked ? true : false);
			this.setEditable(this.bEditable);
			
			if (!bBypassCallback)
			{
				this._checkedStateChanged();
			}
		}
	},
	
	paintLabel	: function($super, oColumnElement, sName)
	{
		// Attach inset and expand dom
		oColumnElement.appendChild(this.getLabelInsetElement());
		oColumnElement.appendChild(this.getLabelExpandContainer());
		
		// Add checkbox
		oColumnElement.appendChild(
			$T.span({class: 'reflex-tree-node-checkable-checkbox'},
				this.oCheckboxElement
			)
		);
		
		// Add icon
		oColumnElement.appendChild(this.getLabelIconContainer());
		
		// Add text, along with click event for toggling the checkbox
		var oLabelTextElement	= this.getLabelTextElement();
		oLabelTextElement.observe('click', this._toggleCheckedState.bind(this));
		oColumnElement.appendChild(oLabelTextElement);
	},
	
	_checkedStateChanged	: function()
	{
		if (this.fnOnCheck)
		{
			// Call 'on check' callback
			this.fnOnCheck(this);
		}
	},
	
	_toggleCheckedState	: function()
	{
		// Only allow if editable
		if (this.bEditable)
		{
			this.setCheckedState(!this.isChecked());
		}
	},
	
	getValue	: function()
	{
		return this.mValue;
	},
	
	isChecked	: function()
	{
		return this.oCheckboxElement.checked;
	},
	
	setEditable	: function(bEditable)
	{
		// Update this nodes content
		if (bEditable)
		{
			this.oElement.show();
			
			if (this.bEnabled)
			{
				this.oCheckboxElement.style.visibility	= 'visible';
			}
		}
		else 
		{
			if (this.oCheckboxElement.checked)
			{
				this.oElement.show();
				this.oCheckboxElement.style.visibility	= 'hidden';
			}
			else
			{
				this.oElement.hide();
			}
		}
		
		// Set editable on this nodes children
		for (var i = 0; i < this.aChildren.length; i++)
		{
			if (typeof this.aChildren[i].setEditable == 'function')
			{
				this.aChildren[i].setEditable(bEditable);
			}
		}
		
		this.bEditable	= bEditable;
	},
	
	setEnabled	: function(bEnabled)
	{
		if (bEnabled == false)
		{
			this.oCheckboxElement.style.visibility	= 'hidden';
		}
		else if (this.bEnabled == false)
		{
			this.oCheckboxElement.style.visibility	= 'visible';
		}
		
		this.bEnabled	= bEnabled;
	},
	
	isEnabled	: function()
	{
		return this.bEnabled;
	}
});