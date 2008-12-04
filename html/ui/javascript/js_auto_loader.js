var JsAutoLoader = {
	loadedScripts : {},
	
	// Dynamically loads a javascript file into the head of the dom
	// strScriptName should include the ".js" extension, and can include a path
	// funcOnLoadEventHandler will be executed as soon as the javascript file finishes loading
	loadScript : function(strScriptName, funcOnLoadEventHandler, bolUseJavascriptPhp)
	{
		if (bolUseJavascriptPhp == undefined)
		{
			bolUseJavascriptPhp = false;
		}
	
		// Retrieve the timestamp of when the user started their session
		// This is used as a work around, to stop the browser from using a cached, old version of the script you want
		var sessionTimestamp = Flex.cookie.read('LoggedInTimestamp');
		if (sessionTimestamp == null)
		{
			// The cookie could not be found, so make up a number
			sessionTimestamp = Math.floor(Math.random()*1000000);
		}
	
		var head = document.getElementsByTagName('head').item(0);
		var strSource;
		if (bolUseJavascriptPhp)
		{
			strSource = "javascript.php?File[]="+ strScriptName +"v="+ sessionTimestamp;
		}
		else
		{
			strSource = strScriptName +"?v="+ sessionTimestamp;
		}
		
		// Check if the script has already been requested
		var scripts = head.getElementsByTagName('script');
		for (var i=0, j=scripts.length; i < j; i++)
		{
			if (scripts[i].hasAttribute('src') && scripts[i].getAttribute('src').match(strScriptName) != null)
			{
				// The script has been requested -- Was an onLoad handler provided?
				if (funcOnLoadEventHandler != undefined)
				{
					if (this.loadedScripts[strScriptName] != undefined)
					{
						// This script should be loaded, so run the funcOnLoadEventHandler function, in global scope
						// wrapping it in a timeout will give it global scope
						setTimeout((funcOnLoadEventHandler instanceof String) ? eval(funcOnLoadEventHandler) : funcOnLoadEventHandler, 1);
					}
					else
					{
						// The script element has been included in the header, but has not finished loading yet
						// add the funcOnLoadEventHandler to it as an event listener
						Event.startObserving(scripts[i], "load", (funcOnLoadEventHandler instanceof String) ? eval(funcOnLoadEventHandler) : funcOnLoadEventHandler, true);
					}
				}
				return;
			}
		}
		
		// The script is not in the header and not loaded
		var script = document.createElement("script");
		script.setAttribute('type', 'text/javascript');
		script.setAttribute('src', strSource);
		Event.startObserving(script, "load", this.registerLoadedScript.bind(this, strScriptName), true);
		head.appendChild(script);
		
		// Was an onLoad handler provided?
		if (funcOnLoadEventHandler != undefined)
		{
			Event.startObserving(script, "load", (funcOnLoadEventHandler instanceof String) ? eval(funcOnLoadEventHandler) : funcOnLoadEventHandler, true);
		}
	},
	
	// This is used to register the fact that a script has been loaded into the dom
	registerLoadedScript : function(strScriptName)
	{
		this.loadedScripts[strScriptName] = true;
	}
}
