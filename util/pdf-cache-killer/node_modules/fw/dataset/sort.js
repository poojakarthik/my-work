
/*
 * sort
 * 
 * Controls sorting of given dataset and pagination objects.
 * 
 * You can specify which field alias' to sort on and the direction in which to sort. 
 * 
 * You can also register an component that when clicked, toggles the sorting for a particular field alias.
 * 		OFF -> ASC -> DESC -> OFF -> ...
 * 
*/

var	Class 	= require('../class'),
	Dataset = require('../dataset');
	
var self = new Class({
	implements : [require('../observable')],
	
	construct : function(bSingleFieldSort) {
		this.setSingleFieldSort(bSingleFieldSort);
		this._aSortedFields	= [];
		this._hFields		= {};
	},
	
	// Public methods
	
	setSingleFieldSort : function(bSingleFieldSort) {
		this._bSingleFieldSort = bSingleFieldSort;
	},
	
	registerField : function(sField, sDefaultDirection) {
		this._hFields[sField] = self.DIRECTION_OFF;
		if (sDefaultDirection) {
			this.sortField(sField, sDefaultDirection, true);
		}
	},
	
	sortField : function(sField, sDirection, bSingleFieldSortOverride) {
		if (this._bSingleFieldSort) {
			// Clear all other sorted fields
			for (i = 0; i < this._aSortedFields.length; i++) {
				if (this._aSortedFields[i] != sField) {
					this._setFieldDirection(this._aSortedFields[i], self.DIRECTION_OFF, true);
				}
			}
			
			this._aSortedFields	= [];
		}
		
		this._setFieldDirection(sField, sDirection);
		this._aSortedFields.push(sField);
	},
	
	getSortData	: function() {
		var hFields		= {};
		var sDirection	= null;
		for (var sField in this._hFields) {
			sDirection = this._hFields[sField];
			
			if (sDirection != self.DIRECTION_OFF) {
				hFields[sField]	= sDirection;
			}
		}
		
		return hFields;
	},
	
	refreshData	: function() {
		// Make a copy of the sort data and fire the refresh event
		this.fire('refresh', this.getSortData());
	},
	
	getSortDirection : function(sField) {
		return this._hFields[sField];
	},
	
	isRegistered : function(sField) {
		return (this._hFields[sField] !== null && (typeof this._hFields[sField] != 'undefined'));
	},
	
	toggleField	: function(sField) {
		var sNewDirection = self.DIRECTION_OFF;
		switch (this._hFields[sField]) {
			case self.DIRECTION_ASC:
				sNewDirection = self.DIRECTION_DESC;
				break;
			case self.DIRECTION_OFF:
			case self.DIRECTION_DESC:
				sNewDirection = self.DIRECTION_ASC;
				break;
		}
		
		this.sortField(sField, sNewDirection);
		return sNewDirection;
	},
	
	// Private methods
	
	_setFieldDirection : function(sField, sDirection) {
		if (typeof this._hFields[sField] == 'undefined') {
			throw "Cannot sort a field that has not been registered as sortable.";
		}
		
		this._hFields[sField] = sDirection;
		this.fire('update', {sField: sField, sDirection: sDirection});
		
		if (sDirection != self.DIRECTION_OFF) {
			this.refreshData();
		}
	}
});

Object.extend(self, {
	DIRECTION_OFF	: '',
	DIRECTION_ASC	: 'ASC',
	DIRECTION_DESC	: 'DESC'
});

return self;

