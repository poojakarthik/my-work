
/******************************************************************
Class to represent a script request and monitor it load status
fnCallback will be invoked when all scripts have been set to dDefined
Note: for package requests, the FW.PackageRequest class, which inherits from FW.ScriptRequest, must be instantiated

******************************************************************/
FW.ScriptRequest = Class.create({

	initialize: function(fnCallback) {
		this.fnCallback = fnCallback;
		this.bRequestStatus = false;
		this.aScriptObjects = [];
		this.bRequestStartedProcessing = false;
		this.iProcessStartTime = 0;
		this.iRequestNumber = FW.iRequestNumber;
		FW.iRequestNumber++;
		this.monitorRequest();
	},

	/*
		adds a new script to the current request, checks for duplicates and only adds oScript if it does not yet exist
		works for both packages and scripts
	*/
	add: function(oScript) {
		if (!this.exists(oScript)) {
			this.aScriptObjects.push(oScript);
			if (typeof oScript.__aObservers != 'undefined') {
				oScript.__aObservers.push(this);
			}
		}
	},
	
	/*
		this method monitors the processing time for this request, and makes it time out with a load error if too much time has lapsed(as specified in FW.iTimeOut)
	*/
	monitorRequest: function() {
		if (!this.bRequestStartedProcessing) {
			this.bRequestStartedProcessing = true;
			this.iProcessStartTime = new Date().getTime();
		}
		
		//this.updateStatus();
		if (!this.bRequestStatus && !FW.bLoadError) {
			//test for request time. If acceptable time is exceeded, trigger error message
			if (((new Date()).getTime() - this.iProcessStartTime) > FW.iTimeOut) {
				/*// One last-ditch attempt at checking load status
				this.updateStatus();*/
				if (!this.bRequestStatus) {
					FW.throwLoadError('Package Load Error', '<b>A script request has timed out. <br>' + this.toString() +'<br>');
					return false;
				}
			} else {
				setTimeout(this.monitorRequest.bind(this), 200);
			}
		}
	},
	
	/*
		used by loading packages to notify PackageRequest objects of the following changes of state:
			- the load of new 'requires' has been initialised by a package object, and these must be added to this request
			- the package has been set to bDefined == TRUE, in which case the current request should run an update status event to see if it is ready for callback
	*/
	notify: function(iEventType) {
		if (iEventType == FW.PackageRequest.NEWREQUIRE) {
			this.addNewRequires();
		} else if (iEventType == FW.PackageRequest.BDEFINED) {
			this.updateStatus();
		}
	},

	/*
		works for both packages and scripts
	*/
	exists: function(oScript) {
		for (var q=0; q < this.aScriptObjects.length; q++) {
			if (this.aScriptObjects[q].src ==oScript.src) {
				return true;
			}
		}
		return false;
	},

	/*
	Iterates over all the script objects that are part of this request to check their __bDefined status, and sets bRequestStatus accordingly
	*/
	updateStatus: function() {
		var bStatus= true;
		for (var q=0; q < this.aScriptObjects.length; q++) {
			if (bStatus && !this.aScriptObjects[q].__bDefined) {
				bStatus = false;
			}
		}
		this.bRequestStatus = bStatus;
		if (this.bRequestStatus) {
			console.log('Request #' + this.iRequestNumber + ' is ready');
			this.callBack();
		}
	},

	/*
	Utility method
	*/
	toString: function() {
		var sString = 'Request Details for request: ' + this.iRequestNumber + ':\n ';
		for (var q=0; q < this.aScriptObjects.length; q++) {
			sString += '['+this.aScriptObjects[q].__src+ ']: ' + (this.aScriptObjects[q].__bDefined ? 'defined' : 'not defined') + ';\n';
		}
		return sString;
	},

	/*
		invokes the request callback, and cleans up
	*/
	callBack: function() {
		//cleaning up: deregister this request as observer of its scripts/packages and where appropriate, clean up the script/package objects by stripping its load members off it
		if (!FW.bDebug) {
			FW.endLoading();
			for (var q=0; q < this.aScriptObjects.length; q++) {
				if (typeof this.aScriptObjects[q].__aObservers == 'object') {
					//we're calling the deregisterObserver and deleteLoadMember methods in FW.Package, but they will work for plain script objects as well......
					FW.Package.deregisterObserver(this.aScriptObjects[q],this);
					//if no more observers left for this script/package we may delete its members that only serve a purpose when loading the script/package
					if (this.nullArray(this.aScriptObjects[q].__aObservers)) {
						FW.Package.deleteLoadMembers(this.aScriptObjects[q]);
					}
				}
			}
		}
		
		if (FW.bDebug) {
			FW.displayAlert('perfomance message', 'The JS load for this request ('+ this.iRequestNumber + ') took: ' + (new Date().getTime() - this.iProcessStartTime)/1000 + 'seconds');
		}
		
		this.fnCallback();
	},
	
	/*
		utility method to check if all values in an array are null
		used to decide is a script/package has any observers left
		this method should probably be moved to another place
	*/
	nullArray: function(array) {
		for (var a=0;a<array.length;a++) {
			if (array[a] != null) {
				return false;
			}
		}
		return true;
	}

});
