
var JsAutoLoader = {
	loadedScripts : {},
	
	// Dynamically loads a javascript file into the head of the dom
	// strScriptName should include the ".js" extension, and can include a path
	// funcOnLoadEventHandler will be executed as soon as the javascript file finishes loading
	loadScript : function(mixScripts, funcOnLoadEventHandler, bolUseJavascriptPhp, bShowLoading)
	{
		// If necessary, show the loading popup (only if not already shown)
		if (bShowLoading && !this.oLoading)
		{
			this.oLoading	= new Reflex_Popup.Loading('Loading...');
			this.oLoading.display();
		}
		
		bolUseJavascriptPhp	= (!bolUseJavascriptPhp ? false : true);
		
		// This was deprecated because it would pass through to following 'loadScript's a different value than what was passed in. rmctainsh 20100421
		//bolUseJavascriptPhp	= (bolUseJavascriptPhp === undefined) ? false : true;
		
		// Make sure we're working with an array of scripts, then grab the next script to load
		var arrScripts		= Object.isArray(mixScripts) ? mixScripts : [mixScripts];
		var	strScriptName	= arrScripts.shift();
		var fncCallback		= this.loadScriptComplete.bind(this, funcOnLoadEventHandler);
		
		if (arrScripts.length > 0)
		{
			fncCallback	=	JsAutoLoader.loadScript.bind(
								JsAutoLoader, 
								arrScripts, 
								JsAutoLoader.loadScriptComplete.bind(
									JsAutoLoader, 
									funcOnLoadEventHandler
								), 
								bolUseJavascriptPhp, 
								bShowLoading
							);
		}
		
		// Add .js to the end of the script name if it's missing
		if (!strScriptName.match(/\.js$/))
		{
			strScriptName	+= '.js';
		}
		
		// Retrieve the timestamp of when the user started their session
		// This is used as a work around, to stop the browser from using a cached, old version of the script you want
		var sessionTimestamp = Flex.cookie.read('LoggedInTimestamp');
		if (sessionTimestamp == null)
		{
			// The cookie could not be found, so make up a number
			sessionTimestamp = Math.floor(Math.random()*1000000);
		}
	
		var head		= document.getElementsByTagName('head').item(0);
		var strSource	= null;
		if (bolUseJavascriptPhp)
		{
			strSource	= "javascript.php?File[]="+ strScriptName +"&v="+ sessionTimestamp;
		}
		else
		{
			strSource	= strScriptName +"?v="+ sessionTimestamp;
		}
		
		// Check if the script has already been requested
		var scripts	= head.getElementsByTagName('script');
		for (var i = 0, j = scripts.length; i < j; i++)
		{
			if (scripts[i].hasAttribute('src') && scripts[i].getAttribute('src').match(strScriptName) != null)
			{
				// The script has been requested -- Was an onLoad handler provided?
				if (fncCallback != undefined)
				{
					if (this.loadedScripts[strScriptName] != undefined)
					{
						// This script should be loaded, so run the funcOnLoadEventHandler function, in global scope
						// wrapping it in a timeout will give it global scope
						setTimeout(fncCallback, 1);
					}
					else
					{
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
		}
		
		// Load the JS Script
		head.appendChild(script);
	},
	
	loadScriptComplete	: function(fnOnLoadEventHandler)
	{
		// Hide loading (if visible)
		if (this.oLoading)
		{
			this.oLoading.hide();
			delete this.oLoading;
		}
		
		// Callback
		if (fnOnLoadEventHandler)
		{
			fnOnLoadEventHandler();
		}
	},
	
	// This is used to register the fact that a script has been loaded into the dom
	registerLoadedScript : function(strScriptName)
	{
		this.loadedScripts[strScriptName]	= true;
	},
	
	require	: function(mScripts, fnOnLoadCallback)
	{
		JsAutoLoader.loadScript(mScripts, fnOnLoadCallback, true);
	},
	
	registerPreLoadedScripts	: function()
	{
		var scripts	= document.getElementsByTagName('head')[0].getElementsByTagName('script');
		for (var i = 0, j = scripts.length; i < j; i++)
		{
			var sSrc	= scripts[i].getAttribute('src');
			sSrc		= sSrc.replace(/^(.*)(javascript(\/)(.*))$/, '$4');
			
			// Check for javascript.php source
			if (sSrc.match(/javascript\.php/))
			{
				var aFiles	= sSrc.match(/File\[\]=([a-z_]*).js/i);
				
				if (aFiles)
				{
					// Load multiple File[]=file.js sources
					for (var k = 0; k < aFiles.length; k++)
					{
						JsAutoLoader.registerLoadedScript(aFiles[k].split('File[]=')[0]);
					}
				}
			}
			else
			{
				// Loading single source
				JsAutoLoader.registerLoadedScript(sSrc);
			}
		}
	}
}

// On load, register all of the scripts added by other means
window.addEventListener(
	'load', 
	function()
	{
		JsAutoLoader.registerPreLoadedScripts();
	}, 
	false
);
