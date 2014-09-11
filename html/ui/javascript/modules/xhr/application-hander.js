var xhr = require('xhr');

function mixin(target) {
	Array.prototype.slice.call(arguments, 1).forEach(function (source) {
		Object.keys(source).forEach(function (property) {
			target[property] = source[property];
		});
	});
	return target;
}

return function applicationHandlerRequest(handler, method, options, callback) {
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
		'reflex.php/' + encodeURIComponent(handler) + '/' + encodeURIComponent(method),
		options,
		callback
	);
};