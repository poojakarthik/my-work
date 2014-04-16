"use strict";

var eventually = (function () {
	if (typeof setImmediate === 'function') {
		return function eventuallyImmediately(callback) {
			setImmediate.apply(undefined, arguments);
		};
	}
	if (typeof setTimeout === 'function') {
		return function eventuallyTimeout(callback) {
			// Inject a timeout of `0` into the arguments to match the expected signature of setTimeout
			//setTimeout.apply(undefined, Array.prototype.splice.call(arguments, 1, 0, 0));
			var args = Array.prototype.slice.call(arguments, 0);
			args.splice(1, 0, 0); // Inject `0` as the second parameter
			setTimeout.apply(undefined, args);
		};
	}
	throw new Error('No supported deferral mechanism found in the environment (e.g. setImmediate, setTimeout)');
}());

function promise(implementor) {
	var observers = [];
	var finalised = false;
	var resolving = false;
	var rejected = false;
	var fulfilledValue;
	var rejectedReason;

	var _promise = {
		then: then,
		resolve: function (value) {
			//console.log('Outer resolving with value:', value);
			if (finalised || resolving) {
				return _promise;
			}
			resolve(value);
			return _promise;
		},
		reject: function (reason) {
			//console.log('Outer rejecting with reason:', reason);
			if (finalised || resolving) {
				return _promise;
			}
			reject(reason);
			return _promise;
		},
		thenable: function () {
			// A safe version of the promise which allows 3rd-parties to only call .then() (and not resolve/reject)
			return {then: _promise.then};
		},
		denodeify: function () {
			// Handles node-style functions expecting a callback in the form `function callback(error, ...values)`
			return function (error) {
				if (error) {
					_promise.reject(error);
				} else {
					_promise.resolve(Array.prototype.slice.call(arguments, 1));
				}
			};
		},
		nodeify: function (callback) {
			return _promise.then(
				function (value) {
					callback(null, value);
				},
				function (reason) {
					callback(reason);
				}
			);
		},
		attempt: function (callback) {
			try {
				return callback();
			} catch (error) {
				_promise.reject(error);
			}
		}
	};
	_promise.fulfill = _promise.resolve;
	_promise.promisify = _promise.denodeify();


	function then(onFulfilled, onRejected) {
		var observer = {onFulfilled: onFulfilled, onRejected: onRejected, promise: promise()};
		if (finalised) {
			eventually(notify, observer);
		} else {
			observers.push(observer);
		}
		return observer.promise;
	}

	function notify(observer) {
		var handler, argument;
		if (rejected) {
			handler = observer.onRejected;
			argument = rejectedReason;
		} else {
			handler = observer.onFulfilled;
			argument = fulfilledValue;
		}

		var handlerReturn;
		if (typeof handler === 'function') {
			try {
				handlerReturn = handler(argument);
			} catch (error) {
				try {
					observer.promise.reject(error);
					return;
				} catch (rejectError) {
					console.error('Rejecting observer promise with error from observer handler erred:', rejectError);
					throw rejectError;
				}
			}
			observer.promise.resolve(handlerReturn);
		} else {
			if (rejected) {
				observer.promise.reject(rejectedReason);
			} else {
				observer.promise.resolve(fulfilledValue);
			}
		}
	}

	function resolve(value) {
		//console.log('Resolving with value:', value);
		if (value === _promise) {
			reject(new TypeError('Promises cannot be resolved with themselves as the value'));
			return _promise;
		}

		resolving = true;

		// Check for "thenables"
		var then;
		var innerResolving;
		function _resolvePromise(value) {
			if (innerResolving) {
				return;
			}
			innerResolving = true;
			resolve(value);
		}
		function _rejectPromise(reason) {
			if (innerResolving) {
				return;
			}
			innerResolving = true;
			reject(reason);
		}

		if (typeof value === 'function' || (typeof value === 'object' && value !== null)) {
			try {
				then = value.then;
			} catch (thenError) {
				reject(thenError);
				return _promise;
			}

			if (typeof then === 'function') {
				try {
					then.call(value, _resolvePromise, _rejectPromise);
				} catch (thenInvokeError) {
					_rejectPromise(thenInvokeError);
					return _promise;
				}
				return _promise;
			}
		}

		fulfilledValue = value;
		finalise();
	}

	function reject(reason) {
		rejected = true;
		rejectedReason = reason;
		finalise();

		setTimeout(function () {
			if (observers.filter(function (observer) { return (typeof observer.onRejected === 'function'); }).length === 0) {
				console.warn('Potentially unobserved promise rejection:', rejectedReason);
			}
		}, 200);
	}

	function finalise() {
		finalised = true;

		// Notify observers
		eventually(function () {
			observers.forEach(function (observer, index) {
				try {
					notify(observer);
				} catch (error) {
					//console.log('Notifying observer #' + index + ' erred:', error);
				}
			});
		});
	}

	if (typeof implementor === 'function') {
		try {
			implementor(_promise.resolve, _promise.reject);
		} catch (implementorError) {
			_promise.reject(implementorError);
		}
	}
	return _promise;
}

promise.all = function (promises) {
	if (!Array.isArray(promises)) {
		promises = Array.prototype.slice.call(arguments, 0);
	}

	var _promise = promise(),
		remaining = promises.length,
		results = [];
	promises.forEach(function (promise, index) {
		promise.then(function (value) {
			results[index] = value;
			remaining--;
			if (!remaining) {
				_promise.resolve(results);
			}
		}, _promise.reject);
	});
	if (remaining === 0) {
		_promise.resolve(results);
	}
	return _promise;
};

// promises-aplus-tests adaptor
promise.deferred = function () {
	var _promise = promise();
	return {
		promise: _promise,
		resolve: _promise.resolve,
		reject: _promise.reject
	};
};

return (module.exports = promise);