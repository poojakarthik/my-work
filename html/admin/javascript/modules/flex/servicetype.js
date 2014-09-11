"use strict";

var promise = require('promise');

var _serviceTypeModuleBasePath = 'flex/servicetype/';
return {
	getModule: function (serviceTypeModule) {
		return promise(function (fulfill, reject) {
			var serviceTypeModulePath = _serviceTypeModuleBasePath + '/' + serviceTypeModule.toLowerCase().replace(/_/g, '/');
			module.provide([serviceTypeModulePath], function (provideError) {
				if (provideError) {
					reject(provideError);
					return;
				}

				try {
					fulfill(require(serviceTypeModulePath));
				} catch (error) {
					reject(error);
				}
			});
		});
	}
};