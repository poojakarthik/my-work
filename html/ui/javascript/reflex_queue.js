
var	Reflex_Queue	= Class.create({
	initialize	: function()
	{
		this._aFunctions	= [];
		this._bStarted	= false;
		this._bFinished	= false;
	},
	
	push	: function(cCallback, fDelay)
	{
		// Can only push if the Queue has not been started yet
		if (this._bStarted)
		{
			throw "Cannot push new functions to the Queue once it has been executed";
		}
		
		fDelay	= (!fDelay) ? 0 : parseFloat(fDelay);
		if (!fDelay)
		{
			fDelay	= 0;
		}
		else if (fDelay !== true)
		{
			fDelay	= Math.max(0, parseFloat(fDelay));
		}
		
		var	oFunctionDefinition	= {'cCallback':cCallback, 'fDelay': fDelay};
		
		this._aFunctions.push(oFunctionDefinition);

		return this;
	},
	
	execute	: function()
	{
		var	oFunctionDefinition	= this._aFunctions.shift();
		
		this._bStarted	= true;
		if (typeof oFunctionDefinition === 'undefined')
		{
			this._bFinished	= true;
		}
		else
		{
			// Prepare execution context
			var	oExecute	= (function()
			{
				try
				{
					oFunctionDefinition.cCallback();
				}
				catch (mError)
				{
					// Do nothing -- just want to ensure the next queue item is run
				}
				this.execute();
			}).bind(this);
			
			// Execute the next function
			if (oFunctionDefinition.fDelay === true)
			{
				oExecute.defer();
			}
			else
			{
				oExecute.delay(oFunctionDefinition.fDelay);
			}
		}
		
		return this;
	},
	
	isExecuting	: function()
	{
		return (this.hasStarted() && !this.hasFinished());
	},
	
	hasStarted	: function()
	{
		return this._bStarted;
	},
	
	hasFinished	: function()
	{
		return this._bFinished;
	}
});

var	$Q	= function(){return new Reflex_Queue()};
