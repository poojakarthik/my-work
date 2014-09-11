
var Correspondence = Class.create({});
Object.extend(Correspondence, {
	getForId	: function(iId, fnCallback, oResponse) {
		if (!oResponse) {
			var fnGetAll = jQuery.json.jsonFunction(
				Correspondence.getForId.curry(iId, fnCallback),
				Correspondence.getForId.curry(iId, fnCallback),
				'Correspondence',
				'getForId'
			);
			fnGetAll(iId);
		} else {
			var hAdditionalColumns = oResponse.aAdditionalColumns;
			if (typeof oResponse.aAdditionalColumns.length != 'undefined') {
				// Is an array, convert to a hash object
				hAdditionalColumns = {};
				for (var i = 0; i < oResponse.aAdditionalColumns.length; i++) {
					hAdditionalColumns[i] = oResponse.aAdditionalColumns[i];
				}
			}
			fnCallback(oResponse.aData, hAdditionalColumns);
		}
	}
});
