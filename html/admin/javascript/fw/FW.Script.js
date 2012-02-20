
/*************************************************
A Class to represent a Javascript file
- It handles the loading of the JS file to the DOM
- It makes sure that the same script is not loaded more than once
- It monitors the load process and launches a file path test if it takes too long
****************************************************/
FW.Script = Class.create();

Object.extend(FW.Script, {

	/*
	creates the script object, and kicks off its loading.
	returns: a reference to the script object
	*/
	create: function(sSrc) {
		var oScript = document.createElement('script');
		oScript.type = 'text/javascript';
		FW.Script.addLoadEvent(oScript, FW.Script.onScriptLoad.bind(this, oScript));
		oScript.src = sSrc;
		oScript.__sSrc = sSrc;
		oScript.id = sSrc;
		oScript.__bDefined = false;
		oScript.iWaitTime = 0;
		oScript.__bPathTestEventTriggered = false;
		oScript.__aObservers = [];
		return FW.Script.load(oScript);
	},

	/*
		to notify registered observers of the bDefined == TRUE state
	*/
	notify: function(oScript) {
		for (var i=0; i < oScript.__aObservers.length; i++) {
			oScript.__aObservers[i].notify(FW.PackageRequest.BDEFINED);
		}
	},

	/*
		the method invoked through the 'onLoad' event
	*/
	onScriptLoad: function(oScript) {
		console.log("Script '" + oScript.__sSrc + "' ready");
		oScript.__bDefined = true;
		FW.Script.notify(oScript);
	},


	/*
		will kick of the DOM operation, only if a script with this script source does not yet exist
		returns: the new dom node if it did not yet exist, or the already existing one
	*/
	load: function(oScript) {
		if (oScript == null) {
			debugger;
		}

		if (FW.bLoadError) {
			return;
		}

		var oHead	= $$('head').first();
		//test if a node with this ID already exists on the DOM
		var node = null;//$$(oScript.__sSrc);
		var aScripts = document.getElementsByTagName('script');
		for(i=0; i<aScripts.length; i++) {
			if (aScripts[i].src.indexOf(oScript.__sSrc) != -1) {
				node = aScripts[i];
				break;
			}
		}
		
		//if no such node exists, append this script to the document header
		if(!node) {
			oHead.appendChild(oScript);
			FW.Script.waitForScript(oScript);
			oScript.__iLoadStartTime = new Date().getTime();
			return oScript;
		} else {
			// the node already exists, instead of adding it again, make this script object refer to it, and start monitoring its loading
			//if this script does not have a .__defined property it was not dynamically loaded throught our framework, and we have to assume its loading has completed
			if (typeof(node.__bDefined) == 'undefined') {
				node.__bDefined = true;
			}
			return node;
		}
	},

	/*
	dynamically adds onload and onreadystatechange events to the script
	There is more sophistication than needed in this method, as it caters for dynamically adding multiple onload functions
	both onload and onreadystatechange are set here as together they should cover both IE and Firefox
	*/
	addLoadEvent: function(oScript,fnCallback) {
		var oldonload = oScript.onload;
		var oldonready = oScript.onreadystatechange;
		//first process the onload event, if none exists, simply set 'func' to be the one
		if (typeof oScript.onload != 'function') {
			oScript.onload = fnCallback;
		} else {
			//if one was already defined before, preserve that one by passing into the new one as oldonload, then add 'func'
			oScript.onload = function() {
				if (oldonload) {
					oldonload();
				}
				fnCallback();
			};
		}
  
		//do the same for the onreadystatechange event
		if (typeof oScript.onreadystatechange != 'function') {
			oScript.onreadystatechange = fnCallback;
		} else {
			oScript.onreadystatechange = function() {
				if (oldonready) {
					oldonready();
				}
				fnCallback();
			};
		}
	},
	
	
	/*
		monitors the loading, and triggers an investigation into the script path when too much time has lapsed
	*/
	waitForScript:	function(oScript) {
		if (!FW.bLoadError) {
			if (oScript.__bDefined) {
				return true;
			}
			//if this script has been loading for longer than the configured time lapse
			//check the validity of the script path
			if ((new Date().getTime() - oScript.__iLoadStartTime) > FW.iTimeLapseBeforeTriggerPathTest && !oScript.__bPathTestEventTriggered) {
				oScript.__bPathTestEventTriggered = true;
				FW.testScriptPath(oScript.__sSrc);
			}
			setTimeout(FW.Script.waitForScript.curry(oScript), 0);
			return false;
		}
	}

});