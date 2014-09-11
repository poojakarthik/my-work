return function mixin(target) {
	Array.prototype.slice.call(arguments, 1).forEach(function (source) {
		Object.keys(source).forEach(function (property) {
			target[property] = source[property];
		});
	});
	return target;
};