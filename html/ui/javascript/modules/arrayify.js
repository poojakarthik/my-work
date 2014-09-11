"use strict";
return function arrayify(arrayish) {
	return Array.prototype.slice.call(arrayish, 0);
};