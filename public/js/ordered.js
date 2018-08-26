// based on the https://github.com/eight04/ordered-json and https://github.com/eight04/ordered-object
// thanks for it... really!!!
(function(JSON){
    function wrap(obj) {
        if (typeof obj === "object") {
            if (Array.isArray(obj)) {
                obj = obj.map(wrap);
            } else {
                obj = create(obj);
                for (var key in Object.keys(obj)) {
                    obj[key] = wrap(obj[key]);
                }
            }
        }
        return obj;
    }

    function create(obj, keys, unordered) {
        if(keys === undefined)
            keys = Object.keys(obj);
        if(unordered === undefined)
            unordered = null;
        obj = Object.assign({}, obj);
        if (unordered) {
            const keySet = new Set(keys);
            if (unordered === "trim") {
                for (var key in Object.keys(obj)) {
                    if (!keySet.has(key)) {
                        delete obj[key];
                    }
                }
            } else if (unordered === "start") {
                keys = Object.keys(obj).filter(function(k){ return !keySet.has(k) }).concat(keys);
            } else if (unordered === "end") {
                keys = keys.concat(Object.keys(obj).filter(function(k){ return !keySet.has(k) }));
            } else if (unordered === "keep") {
                var i = 0;
                keys = Object.keys(obj).map(function(key) {
                    if (keySet.has(key)) {
                    return keys[i++];
                }
                return key;
            });
            } else {
                throw new Error('Invalid argument "unordered": '.unordered);
            }
        }
        return new Proxy(obj, {
            set: function (target, prop, value) {
            if (!(prop in target)) {
            keys.push(prop);
        }
        target[prop] = value;
        return true;
    },
        deleteProperty: function (target, prop) {
            const i = keys.indexOf(prop);
            if (i >= 0) {
                keys.splice(i, 1);
            }
            delete target[prop];
            return true;
        },
        ownKeys: function() {
            return keys;
        }
    });
    }

    var oldParse = JSON.parse;
    var oldStringify = JSON.stringify;

    function parse(json) {
        return wrap(oldParse(json));
    }

    function stringify(target, options) {
        if(options === undefined || options === null)
            options = {};
        if (Array.isArray(options)) {
            options = {order: options};
        }
        function replacer(key, value) {
            return value;
        }
        var replacer = options && options.replacer ? options.replacer : replacer,
            space = options && options.space ? options.space : '',
            order = options.order;
        if (order) {
            target = applyOrder(target, order);
        }
        return oldStringify(target, replacer, space);
    }

    function applyOrder(obj, order) {
        const cleanOrder = order.map(function(s) { return typeof s === "string" ? s : s[0]; });
        const nested = order.filter(Array.isArray);
        obj = orderedObject.create(obj, cleanOrder, "keep");
        for (var key in nested) {
            var order = nested[key];
            obj[key] = applyOrder(obj[key], order);
        }
        return obj;
    }

    JSON.parse = parse;
    JSON.stringify = stringify;
})(JSON);