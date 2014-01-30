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
			} else {
				target[property] = source[property];
			}
		});
	});
	return target;
}

function jsonForm(form) {
	return Array.prototype.reduce.call(form.elements, function (data, element, index, elements) {
		if (element.disabled) {
			return data;
		}

		var name = element.name;
		var nameComponents = name.split(/[\.\[\]]/).filter(function (subject) {
			return !!subject.length;
		});
		var _data = nameComponents.reduceRight(function (data, property, index, components) {
			var _data = {};
			if (data === null) {
				// Last element: value
				if (element.type === 'checkbox') {
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