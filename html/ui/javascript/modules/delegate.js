"use strict";
function matches(selector, event) {
	return (Array.prototype.indexOf.call(document.querySelectorAll(selector), event.target) > -1);
}

module.exports = function delegate(filter, handler) {
	if (typeof filter === 'string') {
		// Filter is a CSS selector
		filter = matches.bind(null, filter);
	}

	return function (event) {
		if (filter.apply(null, arguments)) {
			handler.apply(null, arguments);
		}
	};
};