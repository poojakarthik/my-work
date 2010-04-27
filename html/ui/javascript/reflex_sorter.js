
var Reflex_Sorter	=	Class.create(
{
	initialize	: function(aFieldDefinitions)
	{
		this.aFieldDefinitions	= aFieldDefinitions;
	},
	
	sort	: function(aDataSet)
	{
		this.quickSort(aDataSet, 0, aDataSet.length);
	},
	
	compare	: function(mA, mB)
	{
		var i			= 0;
		var oDefinition	= this.aFieldDefinitions[i];
		var bResult		= null;
		
		while (bResult === null && oDefinition)
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
	
	// Quick sort algorithm found here: http://en.literateprograms.org/Quicksort_%28JavaScript%29
	quickSortPartition	: function(aArray, iBegin, iEnd, iPivot)
	{
		var mPivot	= aArray[iPivot];
		
		this.quickSortSwap(aArray, iPivot, iEnd - 1);
		
		var iStore	= iBegin;
		var ix		= null;
		for (ix = iBegin; ix < iEnd - 1; ++ix) 
		{
			if (!this.compare(aArray[ix], mPivot)) 
			{
				this.quickSortSwap(aArray, iStore, ix);
				++iStore;
			}
		}
		
		this.quickSortSwap(aArray, iEnd - 1, iStore);
		
		return iStore;
	},

	// Quick sort algorithm found here: http://en.literateprograms.org/Quicksort_%28JavaScript%29
	quickSortSwap	: function(aArray, iA, iB)
	{
		var mTmp	= aArray[iA];
		aArray[iA]	= aArray[iB];
		aArray[iB]	= mTmp;
	},

	// Quick sort algorithm found here: http://en.literateprograms.org/Quicksort_%28JavaScript%29
	quickSort	: function(aArray, iBegin, iEnd)
	{
		if (iEnd - 1 > iBegin) {
			var iPivot	= iBegin + Math.floor(Math.random() * (iEnd - iBegin));
			iPivot		= this.quickSortPartition(aArray, iBegin, iEnd, iPivot);

			this.quickSort(aArray, iBegin, iPivot);
			this.quickSort(aArray, iPivot + 1, iEnd);
		}
	}
});

/*
 * Comparison functions for Reflex_Sorter
 * 
 * Add new common comparison function here. Each function must return one of 3 values:
 *  - true	: If the result is true
 *  - false	: If the result is false
 *  - null	: If the result is neither true nor false 
 */

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


