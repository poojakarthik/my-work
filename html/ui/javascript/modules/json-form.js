"use strict";

function _isPlainObject(subject) {
	return (subject != null && subject.constructor === Object);
}

function _mixin(target) {
	Array.prototype.slice.call(arguments, 1).forEach(function (source) {
		Object.keys(source).forEach(function (property) {
			target[property] = source[property];
		});
	});
	return target;
}

function _mixinRecursive(target) {
	Array.prototype.slice.call(arguments, 1).forEach(function (source) {
		if (source == null) {
			return;
		}
		Object.keys(source).forEach(function (property) {
			if (_isPlainObject(source[property])) {
				if (_isPlainObject(target[property])) {
					target[property] = _mixinRecursive(target[property], source[property]);
				} else if (target[property] == null) {
					target[property] = source[property];
				} else {
					target[property] = _mixin({}, source[property]);
				}
			} else if (source[property] != null) {
				target[property] = source[property];
			}
		});
	});
	return target;
}

// jsonForm(form)
//	Will take a `form` element and return a hash of control values.
//	If controls are contained within `fieldset` elements with `name` attributes, they
//		will be namespaced (e.g. form fieldset[name='customer'] input[name='name'] will result in {customer: {name: VALUE}})
//	Additionally, if a `name` attribute has `.`s in it, the name will be exploded into components and nested as with fieldsets
//		(e.g. form input[name='customer.date_of_birth'] will result in {customer: {date_of_birth: VALUE}})
//	Combining these, you can get: form fieldset[name='account.customer'] input[name='date_of_birth.year'] will result in
//		{account: {customer: {date_of_birth: {year: VALUE}}}}
//	You can also make psuedo-fieldsets which behave the same way by adding data-fieldset, data-name, and data-disabled (optional)
//		to any element between a form and a child control
function jsonForm(form) {
	return Array.prototype.slice.call(form.elements, 0).filter(function (element) { return element.type !== 'fieldset'; }).reduce(function (data, element, index, elements) {
		if (element.disabled) {
			return data;
		}

		var name = element.name;

		// Look for parent `fieldset`s to namespace the input
		var parent = element.parentNode;
		while (parent && parent !== form) {
			if (parent.type === 'fieldset' || parent.dataset.fieldset != null) {
				if (parent.disabled || parent.dataset.disabled != null) {
					// Disabled fieldsets disable their children
					return data;
				}
				var parentName = parent.name || parent.dataset.name;
				if (parentName) {
					// Prepend fieldset.name to element.name to namespace
					name = parentName + '.' + name;
				}
			}
			parent = parent.parentNode;
		}

		// Namespace the control based on '.' components in the name
		var nameComponents = name.split(/[\.\[\]]/).filter(function (subject) { return !!subject.length; });
		var _data = nameComponents.reduceRight(function (data, property, index, components) {
			var _data = {};
			if (data === null) {
				// Last element: value
				if (element.type === 'checkbox' || element.type === 'radio') {
					if (element.checked) {
						_data[property] = element.value;
					} else {
						_data[property] = null;
					}
				} else {
					_data[property] = element.value;
				}
			} else {
				_data[property] = data;
			}
			return _data;
		}, null);
		data = _mixinRecursive(data, _data);
		return data;
	}, {});
}

return jsonForm;