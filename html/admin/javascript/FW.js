//Namespace for our JS Framework
FW						= {};
/****************************************
Static Members
*****************************************/
//local salesportal path variables
//FW.sJavaScriptPath = '/salesportal/html/sales/js/';

//server salesportal path variables
//FW.sJavaScriptPath = '/sales/js/';

//test environment path variables
//FW.sJavaScriptPath = 'js/';

//flex path variable
FW.sJavaScriptPath = 'javascript/';


FW.bLoadError = false;
FW.iTimeLapseBeforeTriggerPathTest = 4000;
FW.iTimeOut = FW.iTimeLapseBeforeTriggerPathTest + 3000;

//for debugging
FW.bDebug = false;
FW.iRequestNumber = 1;

//when doing full package serverside loading, set this to true
FW.bSuppressRequires = false;
//'loading' message
//FW.loading
/*******************************************************************************************
    Public Static Methods
	These are the only ones that should be used by the application that uses the FW
	A typical sequence of events when requirePackage is called is:
		
		1 - FW.RequirePackage
			2 - create new PackageRequest
			3 - FW.Package.derivePackageRequest
				4 -for each package to be loaded: FW.Package.load
					5 -Create new Package Shell
					6 -Derive the js file path and attache a script node to the document
					7 -return the new package object
				8 -return an array of newly created, loading, package objects
			9 -add these objects to the new PackageRequest object
			
		step 6 above, the script node creation, triggers the package definition, as per its .js file
		package definition. It has the following sequence:
		1- FW.Package.create
			2 - get the empty package shell from the DOM
			3 - load its parent class and create the inheritance
			4 - define the package through .addMethods
			5 - kick off the loading of other required packages and set the __bDefined flag to TRUE
			
		The PackageRequest object, containing references to the required packages and the callback,
		follows the following sequence of action:
			1 - add package references through .addPackages
				2 - register itself as observer with each of its packages
			3 - accept notification messages from the package objects it has registered itself as observer:
				4 - to add new packages to the request that also need to be loaded before the callback can be invoked
				5 - to do a status update when one of its package objects is set to bDefined == TRUE
			6 - invoke the callback and clean up the new package objects when bDefined == TRUE for all of its packages

*******************************************************************************************/

/**
	to requie the loading of a package
	param: mPackages, a string representing a single package, or array representing any number of packages
			fnCallback, the function to be invoked when loading is completed
	post: the package loading is initialized
*/

FW.requirePackage = function(mPackages, fnCallback) {
	FW.startLoading();
	FW.bLoadError = false;
	// Normalise to an array of packages
	var aRequestedPackages	= Object.isArray(mPackages) ? mPackages : [mPackages];
	var oRequest = new FW.PackageRequest(fnCallback);
	//derive from the requested packages the name space hierarchy, and initiate the loading process
	var aPackages = FW.Package.derivePackageRequest(aRequestedPackages);
	//add the newly created packages to the request. This is where their load status will be monitored and fnCallback eventually triggered
	oRequest.addPackages(aPackages);
};

/*
	to requie the loading of a script
	param: mScripts, a string representing a single scripts, or array representing any number of scripts
		fnCallback, the function to be invoked when loading is completed
	post: the package loading is initialized
*/
FW.requireScript = function(mScripts, fnCallback, sRequestName) {
	FW.startLoading();
	FW.bLoadError = false;
	// Normalise to an array of scripts
	var aScriptRequests	= Object.isArray(mScripts) ? mScripts : [mScripts];
	var oRequest = new FW.ScriptRequest(fnCallback);
	// Convert to JS file paths
	for (var i = 0; i < aScriptRequests.length; i++) {
		var sCurrentScriptPath = FW.sJavaScriptPath+aScriptRequests[i];
		oRequest.add(FW.Script.create(sCurrentScriptPath));
	}
};

/*****************************************************
 Static Utility Methods - for internal use only
*****************************************************/


 /*
   Creates a new package node on the DOM only if the node specified in the parameter does not already exist.
   Param: string representation of the new package node
   Post: the node, and if needed its parent nodes, have been created and initialized with Class.create()
   Return: the DOM node specified in the parameter that was passed in
 */
FW.createNode = function(sPackage) {
	//if the node already exists, don't recreate it but simply return the existing one
	var node = FW.getNode(sPackage);
	if (node) {
		return node;
	}

	var aTokens = sPackage.split('.');
	var context = window;
	var prevContext;

	if (aTokens.length > 1) {
		for (var x=0; x < aTokens.length; x++) {
			prevContext = context;
			context = context[aTokens[x]];
			if (!context) {
				prevContext[aTokens[x]] = Class.create({});
				context = prevContext[aTokens[x]];
			}
		}

	} else {
		window[sPackage] = Class.create({});
		context = window[sPackage];
	}
	return context;
};

/*
 Returns the node specified by the function parameter, or FALSE if no such node exists or an invalid parameter was passed in
*/
FW.getNode = function (sNodeName) {
	if (typeof(sNodeName)!='string') {
		return false;
	}
	var aTokens = sNodeName.split('.');
	var node = window;

	if (aTokens.length > 1) {
		for (var x=0;x<aTokens.length;x++) {
			node = node[aTokens[x]];
			if (!node) {
				return false;
			}
		}
	} else {
		node = window[sNodeName];
		if (!node) {
			return false;
		}
	}
	return node;
};

/*
  checks whether the class corresponding to the package name
  passed in as parameter has been loaded in the Window object
  but does not check for __bDefined
  this is in fact a deprecated method which should only be used for non Framework classes
*/
FW.classLoaded = function(sPackage) {
	var aTokens = sPackage.split('.');
	var context = window;
	var contextPrototype;
	if (aTokens.length > 1) {
		for (var x=0; x < aTokens.length; x++) {
			context = context[aTokens[x]];
			if (context && context['prototype']) {
				contextPrototype = context['prototype'][aTokens[x + 1]];
				if (contextPrototype) {
					context = contextPrototype;
				}
			}

			if (!context && !contextPrototype) {
				return false;
			}
		}

		if (context || contextPrototype) {
			return true;
		}
	} else {
		if (window[sPackage]) {
			return true;
		}
	}

	return false;
};

/*
  Fires an Ajax request for the sScriptPath
  To test whether the script path is valid
  generates a load error if the script does not exist
*/
FW.testScriptPath = function (sScriptPath, sRequestName) {
   new Ajax.Request(sScriptPath, {
		method:'get',
		onSuccess: function (transport) {
			//var response = transport.responseText || "no response text";
			//alert("Success! \n\n" + response);
		},
		onFailure: function () {
			FW.throwLoadError('Script Path Error', 'JS Filepath Error:<br>The following file does not exist on the server:<br><b>' + sScriptPath +'</b>');
		}
	});
};

/*
 Displays a popup with title and message as passed into the function
*/
FW.displayAlert = function(sTitle,sMessage) {
		var popup = new FW.Popup(42.2);
		popup.addCloseButton();
		popup.setTitle(sTitle);
		var button = document.createElement('input');
		button.type = 'button';
		button.value = 'Close';
		button.onclick = popup.hide.bind(popup);
		var buttonContainer = document.createElement('p');
		buttonContainer.appendChild(button);
		var content = document.createElement('div');
		content.align = 'center';
		content.innerHTML = sMessage;
		content.appendChild(buttonContainer);
		popup.setContent(content);
		popup.display();
		popup.recentre();
};

/*
 Creates an expandable 'more detail/less detail' type message that can be used in FW.displayAlert
*/

FW.createExpandablePopupHtml = function (sSummaryMessage, sDetailMessage) {
	var sHead = '<html><head>';

	var sCss =     '<style type=\'text/css\'> .divStyle {  display: none; border:1px solid black; margin: 10px 10px 10px 10px; }</style>';

	var sBody = sSummaryMessage+'<center> <div id=\'div1\' class=\'divStyle\'> <b>Error Details:</b><br>' + FW.splitLine(sDetailMessage, 70) + '</div><div id = \'detailButton\' onClick=\'FW.toggleDivDisplay("div1")\' style = "color:blue"><a style = "padding: 0 2px 15px 2px; background:  url(' + document.baseURI + '/img/1downarrow1-32.png) bottom center no-repeat; color: blue;">Click for More Detail</a></center>';

	return sCss+sBody;
};




/*
 Framework error handler
 Generates a message as specified in the function parameters and sets FW.bLoadError to TRUE, which will in effect halt all script loading
*/
FW.throwLoadError = function (sMessageTitle, sMessage) {
	FW.endLoading();
	FW.displayAlert(sMessageTitle, FW.createExpandablePopupHtml( 'Problem displaying page. Please contact your system administrator', sMessage));
	FW.bLoadError = true;
};

FW.startLoading = function () {
	if (FW.loading != null) {
		return;
	}
	FW.loading = new FW.Popup.Loading();
	FW.loading.display();
};

FW.endLoading =  function() {
	if (FW.loading== null) {
		return;
	}
	FW.loading.hide();
	FW.loading = null;
};

//for the expandable error message popup
FW.toggleDivDisplay = function(sDiv) {
	var divstyle = '';
	divstyle = document.getElementById(sDiv).style.display;
	if(divstyle.toLowerCase() == 'block') {
		document.getElementById(sDiv).style.display = 'none';
		document.getElementById('detailButton').innerHTML = '<a style = "padding: 0 2px 15px 2px; background:  url(' + document.baseURI + '/img/1downarrow1-32.png) bottom center no-repeat; color: blue;">Click for More Detail</a>';
	} else {
		document.getElementById(sDiv).style.display = 'block';
		document.getElementById('detailButton').innerHTML = '<br><a style = " padding: 15px 0px 0px 0px; background:  url(' + document.baseURI + '/img/1uparrow-32.png) top center no-repeat; color: blue;margin: 10px 10px 10px 10px;">Click for Less Detail</a>';
	}
};

// Line Splitter Function
// copyright Stephen Chapman, 19th April 2006
// you may copy this code but please keep the copyright notice as well
FW.splitLine = function(st,n) {
	var b = '';
	var s = st;
	while (s.length > n) {
		var c = s.substring(0,n);
		var d = c.lastIndexOf(' ');
		var e =c.lastIndexOf('\n');
		var f = c.lastIndexOf(',');
		if (e != -1) d = e;
		if (d == -1) d = f;
		if (d == -1) d = n;
		b += c.substring(0,d+1) + '<br>';s = s.substring(d+1);
	}
	return b+s;
};
