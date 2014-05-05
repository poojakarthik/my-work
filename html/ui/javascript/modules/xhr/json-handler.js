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
	var response = JSON.parse(this.responseText);
	if (response.oException) {
		throw response.oException;
	}

	SUCCESS_PROPERTIES.some(function (successProperty) {
		if (response[successProperty] === false) {
			throw new Error(ERROR_MESSAGE_PROPERTIES.reduce(function (currentMessage, messageProperty) {
				if (currentMessage) return currentMessage;

				if (response.hasOwnProperty(messageProperty)) {
					return response[messageProperty];
				}
			}) || DEFAULT_ERROR_MESSAGE);
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
		arguments: []
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
		return request;
	});
};