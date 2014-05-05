"use strict";

var matches = (function () {
	var element = document.createElement('div');
	if (typeof element.matches === 'function') return element.matches;
	if (typeof element.msMatchesSelector === 'function') return element.msMatchesSelector;
	if (typeof element.mozMatchesSelector === 'function') return element.mozMatchesSelector;
	if (typeof element.webkitMatchesSelector === 'function') return element.webkitMatchesSelector;

	return function matches(selector) {
		return (Array.prototype.slice.call(document.querySelectorAll(selector), 0).indexOf(this) > -1);
	};
}());

function delegate(selector, handler) {
	return function eventDelegator(event) {
		if (matches.call(event.target, selector)) {
			return handler.call(this, arguments);
		}
	};
}

return (module.exports = delegate);