
var Class	= require('./class');

var self = new Class({
	construct : function(aFieldDefinitions) {
		// Array of fields
		//		- sField (field name)
		//		- fnCompare (comparison function, optional, self.greaterThan by default)
		//		- bReverse (equivalent to sorting DESC as opposed to ASC)
		if (aFieldDefinitions) {
			this.setFieldDefinitions(aFieldDefinitions);
		}
	},
	
	setFieldDefinitions	: function(aFields) {
		this._aFieldDefinitions	= aFields;
	},
	
	sort : function(aDataSet) {
		this.quickSort(aDataSet, 0, aDataSet.length);
	},
	
	compare : function(mA, mB) {
		var i			= 0;
		var oDefinition	= this._aFieldDefinitions[i];
		var bResult		= null;
		
		while (bResult === null && oDefinition) {
			var fnCompare	= (oDefinition.fnCompare ? oDefinition.fnCompare : self.greaterThan);
			var bResult		= fnCompare(mA[oDefinition.sField], mB[oDefinition.sField]);
			
			// Reverse if need be
			if (bResult !== null && oDefinition.bReverse) {
				bResult	= !bResult;
			}
			
			i++;
			oDefinition	= this._aFieldDefinitions[i];
		}
		
		return bResult;
	},
	
	// Quick sort algorithm found here: http://en.literateprograms.org/Quicksort_%28JavaScript%29
	quickSortPartition : function(aArray, iBegin, iEnd, iPivot) {
		var mPivot = aArray[iPivot];
		
		this.quickSortSwap(aArray, iPivot, iEnd - 1);
		
		var iStore	= iBegin;
		var ix		= null;
		for (ix = iBegin; ix < iEnd - 1; ++ix) {
			if (!this.compare(aArray[ix], mPivot)) {
				this.quickSortSwap(aArray, iStore, ix);
				++iStore;
			}
		}
		
		this.quickSortSwap(aArray, iEnd - 1, iStore);
		
		return iStore;
	},

	// Quick sort algorithm found here: http://en.literateprograms.org/Quicksort_%28JavaScript%29
	quickSortSwap : function(aArray, iA, iB) {
		var mTmp	= aArray[iA];
		aArray[iA]	= aArray[iB];
		aArray[iB]	= mTmp;
	},

	// Quick sort algorithm found here: http://en.literateprograms.org/Quicksort_%28JavaScript%29
	quickSort : function(aArray, iBegin, iEnd) {
		if (iEnd - 1 > iBegin) {
			var iPivot	= iBegin + Math.floor(Math.random() * (iEnd - iBegin));
			iPivot		= this.quickSortPartition(aArray, iBegin, iEnd, iPivot);

			this.quickSort(aArray, iBegin, iPivot);
			this.quickSort(aArray, iPivot + 1, iEnd);
		}
	}
});

/*
 * Comparison functions for sorter
 * 
 * Add new common comparison function here. Each function must return one of 3 values:
 *  - true	: If the result is true
 *  - false	: If the result is false
 *  - null	: If the result is neither true nor false 
 */

Object.extend(self, {
	greaterThan	: function(mA, mB) {
		if (mA > mB) {
			return true;
		} else if(mA == mB) {
			return null;
		} else {
			return false;
		}
	},
	
	stringGreaterThan : function(mA, mB) {
		mA	= mA.toString().toLowerCase();
		mB	= mB.toString().toLowerCase();
		
		if (mA > mB) {
			return true;
		} else if(mA == mB) {
			return null;
		} else {
			return false;
		}
	}
});

return self;
