var self = {
	remoteFunctions: {},

	callRemote: function($remoteClass, $remoteFunction, $onSuccess, $onFailure, $arguments) {
		$remoteFunction = self.getRemoteFunction($remoteClass, $remoteFunction, $onSuccess, $onFailure);
		if ($arguments == null) {
			$remoteFunction();
		} else {
			$remoteFunction($arguments);
		}
	},
	
	getRemoteFunction: function($remoteClass, $remoteFunction, $onSuccess, $onFailure) {
		return new jQuery.json.jsonFunction($onSuccess, $onFailure, 'portal', $remoteClass, $remoteFunction);
	}
};
return self;