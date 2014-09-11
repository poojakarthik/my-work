// This class is fo all new flex specific functionality woking with pototype, jquey & ext

var Flex = {

}

Flex.cookie = {
	create: function(name,value,days) {
		if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		}
		else var expires = "";
		document.cookie = name+"="+value+expires+"; path=/";
	},
	
	read: function(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) { //>
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	},
	
	erase: function(name) {
		Flex.cookie.create(name,"",-1);
	}
}

/* 
 * ===========================================================
 * Flex.Permissions
 * ===========================================================
 * 
 * Used to access a permissions detail object using REST-like paths and verbs.
 * Attributes stored against a certain noun can be accessed as well.
 * Permission checks and attribute values are retrieved using get().E.g.
 * 
 * 	var oData = {
 * 		campaigns : {
 * 			VIEW	: true,
 * 			MODIFY	: false,
 * 			CREATE	: false,
 * 			"#"	: {
 * 				VIEW		: true,
 * 				MODIFY		: true,
 * 				CREATE		: false,
 * 				ATTRIBUTES	: {
 * 					can_do_stuff : 'You Bet!'
 * 				},
 * 				columns	: {
 * 					VIEW	: true,
 * 					MODIFY	: true,
 * 					CREATE	: false,
 * 				}
 * 			}
 *		}
 * 	};
 * 
 * 	var oPermissions = new Flex.Permissions(oData);
 *  if (oPermissions.get('/campaigns/#/columns/:VIEW')) {
 *  	alert("Have view permission on all columns within a campaign");
 *  }
 *  
 *  var mCanDoStuff = oPermissions.get('/campaigns/#/@can_do_stuff');
 *  alert("Can you 'can do stuff' for individual campaigns? " + mCanDoStuff); 
 * 
 */
Flex.Permissions = Class.create({
	initialize : function(oData) {
		this._oData = oData;
	},
	
	get : function(sPath) {
		var aNouns 			= sPath.split('/');
		var aActionMatches	= aNouns.last().match(Flex.Permissions.GET_DETAIL_REGEX);
		if (!aActionMatches) {
			throw "Invalid action type and detail specified.";
		}

		var sActionString			= aNouns.last().match(Flex.Permissions.GET_DETAIL_REGEX)[0];
		aNouns[aNouns.length - 1] 	= aNouns.last().replace(Flex.Permissions.GET_DETAIL_REGEX, '');
		
		var oCurrent 		= this._oData;
		var sCurrentNoun	= "root";
		for (var i = 0; i < aNouns.length; i++) {
			var sNoun = aNouns[i];
			if (sNoun != '') {
				if (oCurrent[sNoun]) {
					oCurrent 		= oCurrent[sNoun];
					sCurrentNoun	= sNoun;
				} else {
					throw "Invalid path specified, " + sNoun + " is an invalid sub-noun of " + sCurrentNoun + ".";
				}
			}
		}
		
		var aActionStringMatches = null;
		if (aActionStringMatches = sActionString.match(Flex.Permissions.PERMISSION_CHECK_REGEX)) {
			// Permission check
			return !!oCurrent[aActionStringMatches[1]];
		} else if (aActionStringMatches = sActionString.match(Flex.Permissions.ATTRIBUTE_VALUE_REGEX)) {
			// Attribute value
			return oCurrent.ATTRIBUTES[aActionStringMatches[1]];
		}
		return false;
	}
});

Object.extend(Flex.Permissions, {
	GET_DETAIL_REGEX		: /(:(VIEW|CREATE|MODIFY))|(@([a-z_]+))/,
	PERMISSION_CHECK_REGEX	: /:(VIEW|CREATE|MODIFY)/,
	ATTRIBUTE_VALUE_REGEX	: /@([a-z_]+)/
	
});
