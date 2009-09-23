var JsAutoLoader = {
	loadedScripts : {},
	
	// Dynamically loads a javascript file into the head of the dom
	// strScriptName should include the ".js" extension, and can include a path
	// funcOnLoadEventHandler will be executed as soon as the javascript file finishes loading
	loadScript : function(mixScripts, funcOnLoadEventHandler, bolUseJavascriptPhp)
	{
		bolUseJavascriptPhp	= (bolUseJavascriptPhp === undefined) ? false : true;
		
		// Make sure we're working with an array of scripts, then grab the next script to load
		var arrScripts		= Object.isArray(mixScripts) ? mixScripts : [mixScripts];
		var	strScriptName	= arrScripts.shift();
		var fncCallback		= (arrScripts.length > 0) ? JsAutoLoader.loadScript.bind(JsAutoLoader, arrScripts, funcOnLoadEventHandler, bolUseJavascriptPhp) : funcOnLoadEventHandler;
		
		//alert("Dynamically Loading JS File '" + strScriptName + "' (" + arrScripts.length + " JS files left to load: " + arrScripts + ")");
		
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
			strSource = "javascript.php?File[]="+ strScriptName +"&v="+ sessionTimestamp;
		}
		else
		{
			strSource = strScriptName +"?v="+ sessionTimestamp;
		}
		
		//alert("JS Autoload URI: " + strSource);
		
		// Check if the script has already been requested
		var scripts = head.getElementsByTagName('script');
		for (var i=0, j=scripts.length; i < j; i++)
		{
			if (scripts[i].hasAttribute('src') && scripts[i].getAttribute('src').match(strScriptName) != null)
			{
				// The script has been requested -- Was an onLoad handler provided?
				if (fncCallback != undefined)
				{
					if (this.loadedScripts[strScriptName] != undefined)
					{
						//alert(strScriptName + " is already loaded -- calling Callback...");
						
						// This script should be loaded, so run the funcOnLoadEventHandler function, in global scope
						// wrapping it in a timeout will give it global scope
						setTimeout(fncCallback, 1);
					}
					else
					{
						//alert(strScriptName + " is already loading -- creating event listener for Callback...");
						
						// The script element has been included in the header, but has not finished loading yet
						// add the funcOnLoadEventHandler to it as an event listener
						Event.startObserving(scripts[i], "load", fncCallback, true);
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
		
		// Was an onLoad handler provided?
		if (fncCallback != undefined)
		{
			Event.startObserving(script, "load", fncCallback, true);
			
			//alert(strScriptName + " is now loading -- creating event listener for Callback...");
		}
		
		// Load the JS Script
		head.appendChild(script);
	},
	
	// This is used to register the fact that a script has been loaded into the dom
	registerLoadedScript : function(strScriptName)
	{
		this.loadedScripts[strScriptName] = true;
	}
}
