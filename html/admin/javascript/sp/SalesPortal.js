
var SalesPortal = {

	remoteFunctions: {},

	callRemote: function($remoteClass, $remoteFunction, $onSuccess, $onFailure, $arguments)
	{
		$remoteFunction = SalesPortal.getRemoteFunction($remoteClass, $remoteFunction, $onSuccess, $onFailure);
		if ($arguments == undefined) $remoteFunction();
		else $remoteFunction($arguments);
	},
	
	getRemoteFunction: function($remoteClass, $remoteFunction, $onSuccess, $onFailure)
	{
		return new jQuery.json.jsonFunction($onSuccess, $onFailure, $remoteClass, $remoteFunction);
	}	
}
