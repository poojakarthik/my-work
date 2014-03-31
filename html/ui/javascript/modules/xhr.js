var P = require('promise'),
	mixin = require('mixin');

var READYSTATE = {
	UNSENT: 0,
	OPENED: 1,
	HEADERS_RECEIVED: 2,
	LOADING: 3,
	DONE: 4
};

function xhr(url, options, callback) {
	options = mixin({
		method: 'get',
		sync: false,
		headers: {}
	}, options || {});

	var promise = P();
	var request = new XMLHttpRequest();
	var completed = false;
	request.onreadystatechange = function xhrReadyStateChanged() {
		if (request.readyState === READYSTATE.DONE && !completed) {
			completed = true;
			/*if (request.status < 200 || request.status > 299) {
				var error = new Error(request.status + ': ' + request.statusText);
				error.request = request;
				callback(error, request);
			} else {
				callback(null, request);
			}*/
			if (request.status < 200 || request.status > 299) {
				var error = new Error(request.status + ': ' + request.statusText);
				error.request = request;
				promise.reject(error);
			} else {
				promise.fulfill(request);
			}
		}
	};

	request.open(options.method, url, !options.sync, null, null);

	Object.keys(options.headers).forEach(function (key) {
		request.setRequestHeader(key, options.headers[key]);
	});

	// Fire off XHR
	// TODO: Fancy body handling (e.g. auto JSON detection)
	request.send(options.body ? options.body : null);

	// Hook callback up to the promise, if supplied
	if (typeof callback === 'function') {
		promise.then(function (request) {
			return callback(null, request);
		}, function (error) {
			return callback(error, request);
		});
	}
	return promise;
}

return (module.exports = {
	xhr: xhr,

	get: function (url, options, callback) {
		return xhr(url, mixin({method: 'get'}, options), callback);
	},

	post: function (url, options, callback) {
		return xhr(url, mixin({method: 'post'}, options), callback);
	},

	put: function (url, options, callback) {
		return xhr(url, mixin({method: 'put'}, options), callback);
	},

	delete: function (url, options, callback) {
		return xhr(url, mixin({method: 'delete'}, options), callback);
	},

	patch: function (url, options, callback) {
		return xhr(url, mixin({method: 'patch'}, options), callback);
	}
});