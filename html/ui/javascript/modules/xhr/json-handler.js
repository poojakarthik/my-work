"use strict";

var xhr = require('xhr');

function mixin(target) {
	Array.prototype.slice.call(arguments, 1).forEach(function (source) {
		Object.keys(source).forEach(function (property) {
			target[property] = source[property];
		});
	});
	return target;
}

var DEFAULT_ERROR_MESSAGE = 'There was an unexpected error while processing your request. Please contact support.';
var SUCCESS_PROPERTIES = ['success', 'Success', 'bSuccess'];
var ERROR_MESSAGE_PROPERTIES = ['message', 'Message', 'sMessage'];
var DEBUG_PROPERTIES = ['debug', 'Debug', 'sDebug'];
function parseJSONResponse() {
	// Exception
	var response = JSON.parse(this.responseText);
	if (response.oException) {
		throw mixin({
			response: response,
			message: response.oException.sMessage
		}, response.oException);
	}

	// Success/Message` conventions
	SUCCESS_PROPERTIES.some(function (successProperty) {
		if (response[successProperty] === false) {
			var errorMessage = (ERROR_MESSAGE_PROPERTIES.reduce(function (currentMessage, messageProperty) {
				if (currentMessage) return currentMessage;

				if (response.hasOwnProperty(messageProperty)) {
					return response[messageProperty];
				}
			}, null) || DEFAULT_ERROR_MESSAGE);
			var error = new Error(errorMessage);
			error.response = response;
			throw error;
		}
		if (response[successProperty] === true) {
			return true;
		}
		return null;
	});

	return response;
}

return function jsonHandlerRequest(handler, method, options, callback) {
	options = mixin({
		sync: false,
		headers: {},
		arguments: [],
		parseJSONResponse: false
	}, options || {});

	options.headers = mixin({
		'Content-Type': 'application/x-www-form-urlencoded'
	}, options.headers);
	options.body = 'json=' + encodeURIComponent(JSON.stringify(options.arguments));

	return xhr.post(
		'reflex_json.php/' + encodeURIComponent(handler) + '/' + encodeURIComponent(method),
		options,
		callback
	).then(function (request) {
		request.parseJSONResponse = parseJSONResponse;
		if (options.parseJSONResponse) {
			return request.parseJSONResponse();
		}
		return request;
	});
};