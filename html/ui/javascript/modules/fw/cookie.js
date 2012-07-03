
// The logic for these functions are copied from: 
// http://www.webreference.com/js/column8/functions.html

var self = {
	set : function(sName, sValue, oExpires, sPath, sDomain, bSecure) {
		var sCookie	= sName + "=" + escape(sValue) +
						((oExpires) ? "; expires=" + oExpires.toGMTString() : "") +
						"; path=" + ((sPath) ? sPath : "/") +
						((sDomain) ? "; domain=" + sDomain : "") +
						((bSecure) ? "; secure" : "");
		document.cookie	= sCookie;
	},
	
	get : function(sName) {
		var sCookie	= document.cookie;
		var sPrefix	= sName + "=";
		var iBegin	= sCookie.indexOf("; " + sPrefix);
		if (iBegin == -1) {
			iBegin = sCookie.indexOf(sPrefix);
			if (iBegin != 0) {
				return null;
			}
		} else {
			iBegin += 2;
		}
		
		var iEnd = document.cookie.indexOf(";", iBegin);
		if (iEnd == -1) {
			iEnd = sCookie.length;
		}

		return unescape(sCookie.substring(iBegin + sPrefix.length, iEnd));
	},

	unset : function(sName, sPath, sDomain) {
		if (self.get(sName)) {
			document.cookie	= sName + "=" +
								"; path=" + ((sPath) ? sPath : "/") +
								((sDomain) ? "; domain=" + sDomain : "") +
								"; expires=Thu, 01-Jan-70 00:00:01 GMT";
		}
	}
};

return self;