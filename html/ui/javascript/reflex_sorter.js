
var Reflex_Sorter	=	Class.create(
{
	initialize	: function(aFieldDefinitions)
	{
		this.aFieldDefinitions	= aFieldDefinitions;
	},
	
	sort	: function(aDataSet)
	{
		this.quick_sort(aDataSet);
	},
	
	compare	: function(mA, mB)
	{
		var i			= 0;
		var oDefinition	= this.aFieldDefinitions[i];
		var bResult		= null;
		
		while(bResult === null && oDefinition)
		{
			var fnCompare	= (oDefinition.fnCompare ? oDefinition.fnCompare : Reflex_Sorter.greaterThan);
			var bResult		= fnCompare(mA[oDefinition.sField], mB[oDefinition.sField]);
			
			// Reverse if need be
			if (bResult !== null && oDefinition.bReverse)
			{
				bResult	= !bResult;
			}
			
			i++;
			oDefinition	= this.aFieldDefinitions[i];
		}
		
		return bResult;
	},
	
	partition	: function(array, begin, end, pivot)
	{
		var piv=array[pivot];
		this.swap(array, pivot, end-1);
		var store=begin;
		var ix;
		for(ix=begin; ix<end-1; ++ix) {
			if (!this.compare(array[ix], piv)) {
				this.swap(array, store, ix);
				++store;
			}
		}
		this.swap(array, end-1, store);

		return store;
	},

	swap	: function(array, a, b)
	{
		var tmp=array[a];
		array[a]=array[b];
		array[b]=tmp;
	},

	qsort	: function(array, begin, end)
	{
		if(end-1>begin) {
			var pivot=begin+Math.floor(Math.random()*(end-begin));

			pivot=this.partition(array, begin, end, pivot);

			this.qsort(array, begin, pivot);
			this.qsort(array, pivot+1, end);
		}
	},
	
	// Algorithm URL: http://en.literateprograms.org/Quicksort_%28JavaScript%29
	quick_sort	: function(array)
	{
		this.qsort(array, 0, array.length);
	}
});

Reflex_Sorter.greaterThan	= function(mA, mB)
{
	if (mA > mB)
	{
		return true;
	}
	else if(mA == mB)
	{
		return null;
	}
	else
	{
		return false;
	}
};

Reflex_Sorter.stringGreaterThan	= function(mA, mB)
{
	mA	= mA.toString().toLowerCase();
	mB	= mB.toString().toLowerCase();
	
	if (mA > mB)
	{
		return true;
	}
	else if(mA == mB)
	{
		return null;
	}
	else
	{
		return false;
	}
};


