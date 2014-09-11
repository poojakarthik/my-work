var Class = require('fw/class');

var self = new Class({
	//top level containers for this component
	detailsContainer : null,
	summaryContainer : null,
	//FW.GUIComponent.ElementGroup objects belonging to this component
	elementGroups : null,
	
	//underlying data structure, for submission to the database
	object : null,
	//html table objects that the GUI element groups are displayed in
	oTables : null,
	//the html table object that is currently being built
	oWorkingTable : null,

	//sets the containers and kicks of the GUI building
	setContainers : function (detailsContainer, summaryContainer) {
		this.detailsContainer = detailsContainer;
		this.summaryContainer = summaryContainer;
		//buildGUI is implemented by each child class
		this.buildGUI();
	},

	//to update the oDisplay object of each element group
	updateDisplay : function ($readOnly) {
		if (this.elementGroups != null) {
			for (var i in this.elementGroups) {
				var group = this.elementGroups[i];
				if (typeof group == 'object' && group.aInputs && typeof group.aInputs == 'array' && group.aElements && typeof group.aElements == 'array' && group.sType && typeof group.sType == 'string') {
					group.updateDisplay();
				}
			}
		}

		this.updateChildObjectsDisplay($readOnly);
	},
			
	//to validate all element groups belonging to this component
	isValid : function () {
		var bValid = true;
		var strElementGroup = '';
		for (strElementGroup in this.elementGroups) {
			//we can't just return false upon getting the first 'false', because the isValid method is also used to dynamically highlight fields that are mandatory
			var bElementValid = this.elementGroups[strElementGroup].isValid();
			if (bValid) {
				bValid = bElementValid;
			}
		}
		return bValid;
	},
	
	//to update the underlying data object with the user input. Will only be done if all user input is valid
	updateFromGUI : function () {
		if (this.isValid()) {
			for (var strElementGroup in this.elementGroups) {
				this.elementGroups[strElementGroup].updateDataField();
			}
		} else {
			return false;
		}
		return true;
	},
	
	//to add a new element group the the oWorkingTable object. Also binds oElementGroup to a field in this.object
	//if no sObjectFieldName supplied, the oElementGroup will be bound to object.sElementName - ie the object field name and the elementGroup name are identical in that case
	addElementGroup : function (sElementName, oElementGroup, sLabel, sObjectFieldName) {
		this.elementGroups[sElementName] = oElementGroup;
		//sObjectFieldName is an optional parameter, born out of necessity as the naming of the object members is not in all cases consistent with the naming of gui element names
		if (typeof(sObjectFieldName) == 'undefined') {
			oElementGroup.bindToField(this.object, sElementName);
		} else {
			oElementGroup.bindToField(this.object, sObjectFieldName);
		}
		oElementGroup.appendToTable(this.oWorkingTable, sLabel);
	},
	
	//sets the oTable object to the working table
	setWorkingTable : function (oTable) {
		if(this.oTables == null) {
			this.oTables = {};
		}
		if (this.oTables[oTable.id] != null) {
			this.oTables[oTable.id] = oTable;
		}
		this.oWorkingTable = oTable;
	},
	
	getWorkingTable : function () {
		return this.oWorkingTable;
	},
	
	disable : function () {
		for (var strElementGroup in this.elementGroups) {
			this.elementGroups[strElementGroup].disable();
		}
	},
	
	getTable : function (sId) {
		return this.oTables[sId];
	},
	
	//abstract methods, to be implemented by extending classes
	
	updateChildObjectsDisplay : function ($readOnly) {},

	destroy : function () {},

	buildGUI : function () {},
	
	renderDetails : function (readOnly) {},

	renderSummary : function (readOnly) {},

	statics : {
		unique: 1,

		__array_contains : function ($array, $value) {
			for (var i = 0, l = $array.length; i < l; i++) {
				if ($array[i] == $value) {
					return true;
				}
			}
			return false;
		}
	}

});

return self;