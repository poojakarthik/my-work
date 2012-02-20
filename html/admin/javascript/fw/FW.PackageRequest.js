
/*********************************************************
A Class to monitor the processing of package requests so that
the specified callback is executed after all requested packages, and their parent packages and other required packages have been loaded

In order to avoid deadlocks in the loading process caused by circular requires, a package is set to bDefined as soon as
	- its parent package has been set to bDefined, and
	- its own package definition has been done through .addMethods

In other words: a package is set to bDefined at a time when its requires are still loading, and the callback function in the package request object should not yet be executed.
For this reason, the package request will by dynamically expanded throughout the load cycle, and receive references to loading requires of both itself and its parent package.
Only after all these have been set to bDefined, will the callback be executed

This class inherits from FW.PackageRequest and uses a lot of its functionality


***********************************************************/
FW.PackageRequest		= Class.create(FW.ScriptRequest,
{
	
	initialize: function($super, fnCallback)
	{
		$super(fnCallback);
		
	},


	
	/*
		tests whether the package passed in as param is already part of the request
	*/
	exists: function(oScript)
	{
		for (var q=0;q<this.aScriptObjects.length;q++)
		{
			if (this.aScriptObjects[q].__sPackageName ==oScript.__sPackageName)
			{
				return true;
			}

		}
		return false;

	},

	/*
		this method will dynamically add new package objects to the request, during the loading process
		it can only be done dynamically because the requires and extends are only specified in the package definition.js files, which are loaded dynamically
		this method is called every time a loading package notifies each request it is part of to let them know new requires have been requested by it
	*/
	addNewRequires: function()
	{

		//loop through all the packages currently in the request
		for (var q=0;q<this.aScriptObjects.length;q++)
		{
			//first, test if the object was previously fully loaded, which we can tell from the fact that the load specific '__' members no longer exist
			//this would be the case if multiple requests, one after the other, are fired from the same html page
			if(typeof(this.aScriptObjects[q].__aExtends)!='undefined')
			{
				//if the parent node exists on the DOM, add it to this request, this is to monitor the parent's requires
				var parent = FW.getNode(this.aScriptObjects[q].__aExtends);
				if (parent)
				{
					this.add(parent);
				}

				//now loop through the current package's requires, and add them to the current request if they already exist on the DOM
				for (var z=0;z<this.aScriptObjects[q].__aRequires.length;z++)
				{
					var oPackage = FW.getNode(this.aScriptObjects[q].__aRequires[z]);
					if (oPackage)
					{

						this.add(oPackage);

					}
				}
			}
		}
	},

	/*
		adds multiple packages at once to this request
		Should only be used when param aPackages contains the full package request as the callback will be executed when all these packages and their requires are found to have bDefined==true
	*/
	addPackages: function(aPackages)
	{
		this.aScriptObjects = aPackages;
		for (var q=0;q<this.aScriptObjects.length;q++)
		{
			//if the package is still in the process of being fully defined, add this request object as an observer to the current package
			if (typeof(this.aScriptObjects[q].__aObservers)!='undefined')
				this.aScriptObjects[q].__aObservers.push(this);				
		
		}
		
		//the code beyond this point is to handle reqests for packages that have all previously been fully loaded
		//for these kind of requests, the bRequestStatus will immediately be set to true and the callback should be executed.
		
		//we still need to check if all the requires of the requested packages were also already fully loaded
		this.addNewRequires();
		this.updateStatus();
		if (this.bRequestStatus) {
			this.callBack();
		}

	},
	
	/*
	Utility method
	*/
	toString: function() {
		var sString = 'Request Details for request: ' + this.iRequestNumber + ':\n ';
		for (var q=0; q < this.aScriptObjects.length; q++) {
			sString += '['+this.aScriptObjects[q].__sPackageName+ ']: ' + (this.aScriptObjects[q].__bDefined ? 'defined' : 'not defined') + ';\n';
		}
		return sString;
	}
});

Object.extend(FW.PackageRequest, {
	NEWREQUIRE: 1,
	BDEFINED: 2
});

