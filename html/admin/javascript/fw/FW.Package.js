
/************************************************
Class to represent a Package, extends FW.Script
*************************************************/

FW.Package = Class.create();


/***********************************************************************************************************************************************************************
	The following static methods contain the functionality to handle loading packages to the DOM
	It is based on the prototype.js Class.create, Object.extend and .addMethods functionality with added functionality for:
		* the dynamic loading of parent packages, that is, packages that the current package inherits from in terms of class hierarchy
		* the dynamic loading of other packages that are required for the proper functioning of the loading package
************************************************************************************************************************************************************************/


Object.extend(FW.Package, {
	PACKAGE_SPECIAL_PROPERTIES : [
		'__bDefined',
		'__sPackageName',
		'__iLoadStartTime',
		'__bPathTestEventTriggered',
		'__aRequires',
		'__aExtends',
		'__sSrc',
		'__aObservers',
		'superclass',
		'subclasses',
		'addMethods'
	],

	/*
		-Creates a package definition on the DOM, and defines its member variables and methods
		-only if the package has not already been created on the DOM is it newly created, and only if it has not yet been fully defined (indicated by __bDefined) will it be defined
		param:
		1 the package name;
		2 package definition in JSON format.
			the first two members in the package definition can optionally be the following:
				*requires: [string representations of packages that the current package requires]
				*extends: string representation of the parent package of the current package
					the 'requires' specification must always be the first member in the class definition.
					the 'extends' specification must be the first (if no 'requires' is specified), or second (if a 'requires' is specified) member in the class definition
		3 optionally: a boolean value to indicate if __bDefined must be set to true. This will only work in the case of packages without requires or extends. 'false' should only be passed in
			when a package without extends or requires is created, and the package definition file has further code to add static members to the package, done with FW.Package.extend.
		post: the create method has three possible exit points:
				* if 'extends' is specified it will pass control to the loadParent method and return the new package with __bDefined set to FALSE. 'loadParent' will call back to 'create' to complete package definition as soon as the parent package has been fully defined
				* if 'require' is specified, control will be passed to the 'loadRequires' method and the new package will be returned with __bDefined set to FALSE. 'loadRequires' will call back to the 'requireLoaded' method which will set __bDefined to TRUE.
				* if no 'require' is specified the create method will set the __bDefined flag to TRUE and return the new package.


	*/
	create: function() {
		if(FW.bLoadError) {
			return;
		}

		function subclass() {}

		var sParent = null, aProperties = $A(arguments);
		if (aProperties.length === 0) {
			FW.throwLoadError('Package Load Error', 'please supply a valid package name for FW.Package.create to function properly');
			return;
		}
		var sPackageName = aProperties[0];
		var bSetDefined = true;
		if (aProperties.length === 3) {
			bSetDefined = aProperties[2];
		}
		
		//the shell will be created using the prototype Class.create() method. the createPackageShell method will only create a new Package shell if it does not yet exist on the DOM.
		var oPackage = FW.Package.createPackageShell(sPackageName);
		if (!oPackage.__bDefined) {
			var oPackageDef = aProperties[1];
			var aPackageDefKeys = Object.keys(oPackageDef);
			//the code within this conditional block deals with class inheritance
			if (aPackageDefKeys[0] =='extends' || (aPackageDefKeys[0] =='requires' && aPackageDefKeys[1] == 'extends')) {
				//determine the index of the 'extends' information, this can be either 0 or 1 depending on whether 'requires' is present
				var iExtendsIndex;
				if (aPackageDefKeys[1] =='extends') {
					sParent = oPackageDef[aPackageDefKeys[1]];
					iExtendsIndex = 1;
				} else {
					sParent = oPackageDef[aPackageDefKeys[0]];
					iExtendsIndex = 0;
				}

				//test whether the parent package is specified correctly
				if (typeof sParent != 'string') {
					FW.throwLoadError('Package Load Error', 'JS Package Load Error:<br>parent class reference must be a string. <b>\'' + typeof(sParent) + '\' supplied in ' + sPackageName +'</b>');
					return false;
				}

				//define the __aExtends member of the new oPackage, later used to dynamically include it in the monitoring package request object
				oPackage.__aExtends = oPackageDef[aPackageDefKeys[iExtendsIndex]];
				
				var oParentNode = FW.getNode(sParent);
				var fnCallback = FW.Package.create.bind(this, sPackageName, oPackageDef);
				//if the parent package has not started loading yet, kick it off and come back later.
				if (!oParentNode || typeof oParentNode.__bDefined == 'undefined') {
					//alert('about to call loadParent for ' + package.__sPackageName);
					FW.Package.loadParent(sParent, fnCallback, oPackage);
					return oPackage;
				} else if (!oParentNode.__bDefined) {
					//if the parent package has started loading, but has not yet been fully defined, wait.
					setTimeout(fnCallback, 5);
					return oPackage;
				} else {
					//if the parent package has been fully defined, continue defining the current package
					oPackage.superclass = oParentNode;
					oPackage.subclasses = [];
					subclass.prototype = oParentNode.prototype;
					oPackage.prototype = new subclass();
					oParentNode.subclasses.push(oPackage);

					//delete the 'extends' section from the package definition
					delete oPackageDef[aPackageDefKeys[iExtendsIndex]];
				}

			}//here's where the parent stuff ends

			//the block of code inside this conditional statement deals with loading packages required by this package
			if (aPackageDefKeys[0] == 'requires' && !FW.bSuppressRequires) {
				//set the 'requires' member of the oPackage object, needed later for dependency checking
				oPackage.__aRequires = Object.isArray(oPackageDef[aPackageDefKeys[0]]) ? oPackageDef[aPackageDefKeys[0]] : [oPackageDef[aPackageDefKeys[0]]];//oPackageDef[aPackageDefKeys[0]];
				//delete the 'requires' section from the oPackage definition
				delete oPackageDef[aPackageDefKeys[0]];
				//define the oPackage further, which can be done independently of the requires
				oPackage.addMethods(oPackageDef);
				//load the required packages
				FW.Package.loadRequires(oPackage, bSetDefined);
				return oPackage;
			}

			//if no requires are specified, define the oPackage fully
			oPackage.addMethods(oPackageDef);
			//if there are no requires, then we can consider the package to be fully defined at this point, if bSetDefined agrees, that is
			if ((aPackageDefKeys[0] != 'requires' || FW.bSuppressRequires) && bSetDefined) {
				FW.Package.setDefined(oPackage);
			}
		}
		return oPackage;
	},
	
	/*
		performs all the actions related to setting a packge to defined. Currently this only involves notifying the observing package request objects.
	*/
	setDefined: function (oPackage) {
		//debugger;
		oPackage.__bDefined = true;
		FW.Package.notifyObservers(oPackage, FW.PackageRequest.BDEFINED);
	},

	/*
		notifies all package request objects when 'package' has changed state in terms of:
			having its parent package or required packaged defined, in which case iEventType must be FW.PackageRequest.NEWREQUIRE
			having set its status to bDefined, in which case iEventType must be FW.PackageRequest.BDEFINED
	*/
	notifyObservers: function(oPackage, iEventType) {
		for (var i=0; i < oPackage.__aObservers.length; i++) {
			oPackage.__aObservers[i].notify(iEventType);
		}
	},

	/*
		loads the parent Package of the currently loading package, in order to successfully implement package inheritance
		param:	sParent: string representation of the parent package;
				fnCallback: function reference to FW.Package.create, bound with the parameters that define the currently loading class
				child:	the currently loading package
		post: if the parent has been fully defined: fnCallback is invoked; if the parent is in the process of being defined: wait; if the parent does not yet exist: parent loading is kicked off
	*/
	loadParent: function(sParent, fnCallback, child) {
		//kick off the package loading of the parent package
		FW.Package.derivePackageRequest([sParent]);
		//let the registered package request objects know that it needs to add the loading parent to the request
		FW.Package.notifyObservers(child, FW.PackageRequest.NEWREQUIRE);
		//wait for the parent package to be defined
		FW.Package.loadParentMonitor(sParent, fnCallback);
	},

	/*
		waits for the parent package to be fully defined, then calls back to the FW.Package.create method, as specified by fnCallback
	*/
	loadParentMonitor: function(sParent, fnCallback) {
		var func = FW.Package.loadParentMonitor.bind(this, sParent, fnCallback);
		var oParent = FW.getNode(sParent);
		if (oParent && oParent.__bDefined) {
			fnCallback();
		} else {
			setTimeout(func, 5);
		}
	},

	/*
		will process the required packages into a package request object and add them to the FW.oLoadQueue object for loading
		param: package, reference to the currently loading class
		post: the loading of requires is kicked off
	*/
	loadRequires: function (oPackage) {
		var aRequires	= Object.isArray(oPackage.__aRequires) ? oPackage.__aRequires : [oPackage.__aRequires];
		
		//kick off the loading of the required packages
		FW.Package.derivePackageRequest(aRequires);
		
		//At this point we may consider this package to be defined, even though its requires are still loading
		//this will avoid deadlock when somewhere down the 'requires' chain there is an inclusion of this package.
		//To avoid a premature execution of the callback, the ScriptRequest object that controls the callback will also monitor the load status of the requires.
		FW.Package.notifyObservers(oPackage, FW.PackageRequest.NEWREQUIRE);
		FW.Package.setDefined(oPackage);
	},

	/*
		delete the package static members that were only required during the load process
		__bDefined is preserved because there may be new requests coming in for this packages, and they need to know that the oPackage was already defined
	*/
	deleteLoadMembers: function(oPackage) {
		//this may happen at the same time as the notifyObservers action has not been fully completed
		//so building in a delay will prevent problems that could arise from deleting the __aObservers array
		if (typeof oPackage.__iLoadStartTime != 'undefined') {
			delete oPackage.__iLoadStartTime;
			setTimeout(FW.Package.deleteLoadMembers.bind(this, oPackage), 2000);
		} else {
			delete oPackage.__iLoadStartTime;
			delete oPackage.__bPathTestEventTriggered;
			delete oPackage.__aRequires;
			delete oPackage.__aExtends;
			delete oPackage.__sSrc;
			delete oPackage.__aObservers;
		}
	},

	/*
		just after the callback in the oPackage request object is executed, the request object will deregister itself as an observer
		When there are no observers left, the request object will call FW.Package.deleteLoadMembers
	*/
	deregisterObserver: function(oPackage, oObserver) {
		if (oPackage.__aObservers.indexOf(oObserver) > -1) {
			oPackage.__aObservers.splice(oPackage.__aObservers.indexOf(oObserver), 1);
		}
	},
	
	/*
		creates a new, empty, package object on the DOM, with __bDefined set to FALSE.
		FW.CreateNode is used to to the node DOM node creation. It will only create a new node if the currently requested node does not yet exist.
	*/
	createPackageShell: function(sPack) {
		//here is where we'll either get a brand new,empty, DOM node back (created through an empty Class.create()), or one that was previously created and defined as a Framework Package
		var oNewPackage = FW.createNode(sPack);
		//only extend the package if it was just newly created, not if it already existed on the DOM
		if (typeof (oNewPackage.__bDefined) == 'undefined') {
			Object.extend(oNewPackage, {
				__bDefined: false,
				__sPackageName: sPack,
				__iLoadStartTime:null,
				__bPathTestEventTriggered: false,
				__aRequires: [],
				__aExtends: null,
				__sSrc: null,
				__aObservers: []
			});
		}
		return oNewPackage;
	},

	/*
		processes required packages into the full package requests. For example: if SP.Sale.SaleAccount is required,
		the full request should be for SP, SP.Sale, and SP.Sale.SaleAccount
		param: an array with required packages
		post: the loading of the requested packages has been initiated
		return: an array with package objects for the current request
	*/
	derivePackageRequest: function(aRequiredPackages) {

		//the array of new package objects we are about to construct, and return at the end
		var aPackageObjects = [];
		//this array will be used to monitor what has been added to the aPackageObjects array. A clumsy way to prevent duplicates. Future work on the FW should include replacing traditional arrays with associative arrays or objects (new Object())
		var aPackages = [];
		
		//break down the domain for each requesting package and add the higher level domains as separate package requests
		for (var i=0; i < aRequiredPackages.length; i++) {
			if (typeof(aRequiredPackages[i]) != 'string') {
				FW.throwLoadError('Package Definition Error', 'Syntax error in package request definition: <b>' + aRequiredPackages +'</b>');
			}

			var aTokens = aRequiredPackages[i].split('.');
			var packageToPush = '';
			//it this is a . separated package, it needs deconstructing
			if (aTokens.length>1) {
				for (var x=0; x < aTokens.length; x++) {
					packageToPush += aTokens[x];
					if (aPackages.indexOf(packageToPush) === -1 && packageToPush != 'FW') {
						aPackages.push(packageToPush);
						//invoke the FW.Package.load method to actually create the package object and kick off its load, then add the package objects returned by it to the aPackageObjects array
						aPackageObjects.push(FW.Package.load(packageToPush));
					}
					packageToPush += '.';
				}
			} else {
				//if it is a 'one token' package (eg SP)
				if (aPackages.indexOf(packageToPush) === -1) {
					aPackages.push(aRequiredPackages[i]);
					aPackageObjects.push(FW.Package.load(aRequiredPackages[i]));
				}
			}
		}

		return aPackageObjects;
	},

	/*
		Only if it does not yet exist: creates the actual package object and appends its defining .js file to the document header
		param: sPackage - string representation of the package to load
		post: the package indicated by sPackage exists and is in the process of loading
		return: the package object indicated by sPackage, which is either a newly created package or a package that already existed prior to invoking this method
	
	*/
	load: function(sPackage) {
		if (FW.bLoadError) {
			return;
		}

		//get the DOM node
		var oPackage = FW.Package.createPackageShell(sPackage);
		//if package is not already loading, start loading it
		if (!oPackage.__bDefined && oPackage.__iLoadStartTime == null) {
			//derive the file path of the package definition .js file
			var sCurrentScriptPath = FW.sJavaScriptPath;
			var aPackageTokens = sPackage.split('.');
			for (var x = 0; x < aPackageTokens.length - 1; x++) {
				sCurrentScriptPath += aPackageTokens[x].toLowerCase() + '/';
			}
			sCurrentScriptPath = sCurrentScriptPath + sPackage + '.js';

			//create the Script Node
			var oScript	= document.createElement('script');
			oScript.type = 'text/javascript';
			oScript.src = sCurrentScriptPath;
			oScript.id = sCurrentScriptPath;
			var oHead = $$('head').first();
			oHead.appendChild(oScript);

			//start monitoring the load process
			oPackage.__iLoadStartTime = new Date().getTime();
			oPackage.__sSrc = sCurrentScriptPath;
			FW.Package.waitForPackage(oPackage);
		}
		return oPackage;
	},

	/*
		monitors the load process of oPackage, and launches an investigation into the validity of the supplied script path if too much time lapses
	*/
	waitForPackage:	function(oPackage) {
		//FW.bLoadError is set to true if somewhere in the process a load error was generated. It stops all processing
		if (!FW.bLoadError) {
			if (oPackage.__bDefined) {
				return true;
			}
			//if this script has been loading for longer than the configured max time, check the validity of the script path
			if ((new Date().getTime() - oPackage.__iLoadStartTime > FW.iTimeLapseBeforeTriggerPathTest) && !oPackage.__bPathTestEventTriggered) {
				oPackage.__bPathTestEventTriggered = true;
				//if the path is found to be faulty a load error is generated
				FW.testScriptPath(oPackage.__sSrc);
			}
			setTimeout(FW.Package.waitForPackage.curry(oPackage), 0);
			return false;
		}
	},

	/*
		an override of the prototype Object.extend
		it does not copy the special __ members from source to destination
		this should be used instead of Object.exted for the FW to work properly
	*/
	extend: function(oDestination, oSource, bSetDefined) {
		if (typeof oDestination != 'function') {
			//debugger;
			FW.throwLoadError(
				'Package Definition Error',
				'FW.Package.extend requires function, but ' + (typeof oDestination) + ' was passed as parameter(' + oDestination + ')'
			);
		}
		var bSetPackageToDefined = false;
		if (typeof(bSetDefined) == 'boolean') {
			bSetPackageToDefined = bSetDefined;
		}

		/*for (var property in oSource) {
			if (oSource.hasOwnProperty(property) && !oSource[property].__sPackageName && property!='__bDefined' && property!='__sPackageName' && property!='__iLoadStartTime' && property!='__bPathTestEventTriggered' && property!='__aRequires' && property!='__aExtends' && property!='__sSrc' && property!='__aObservers') {
				oDestination[property] = oSource[property];
			}
		}*/
		for (var property in oSource) {
			if (oSource.hasOwnProperty(property) && FW.Package.PACKAGE_SPECIAL_PROPERTIES.indexOf(property) === -1 && (!oSource[property] || oSource[property].__bDefined == null)) {
				oDestination[property] = oSource[property];
			}
		}
		if (bSetPackageToDefined) {
			FW.Package.setDefined(oDestination);
		}
		return oDestination;
	},

	/*
		checks if package1 requires package2
	*/
	checkDependency: function(oPackage1,oPackage2) {
		if (oPackage1.requires == null) {
			return false;
		}

		for (var i=0; i < oPackage1.requires.length; i++) {
			if (oPackage1.requires[i]==oPackage2.__sPackageName) {
				return true;
			}
		}

		return false;
	}
});
