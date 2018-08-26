(function (factory) {
    if (typeof define === 'function' && define.amd) { // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof exports === 'object') { // Node/CommonJS
        var jQuery = require('jquery');
        module.exports = factory(jQuery);
    } else { // Browser globals (zepto supported)
        factory(window.jQuery || window.Zepto || window.$); // Zepto supported on browsers as well
    }

}(function ($) {
    "use strict";

    $.fn.deserializeJSON = function () {
        return $.deserializeJSON(this);
    }

    $.deserializeJSON = function (json) {
        var deparam = function (querystring) {
            // remove any preceding url and split
            if(querystring.trim() == "")
                return {};
            querystring = querystring.substring(querystring.indexOf('?')+1).split('&');
            var params = {}, pair, i;
            // march and parse
            for (i = querystring.length; i > 0;) {
                pair = querystring[--i].split('=');
                var key = pair.shift();
                var value = pair.join('=');
                params[key] = value;
            }

            return params;
        };//--  fn  deparam
        return deparam(decodeURIComponent( $.param( json ) ));
    }

}));
