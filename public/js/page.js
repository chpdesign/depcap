(function(history){
    var pushState = history.pushState;
    history.pushState = function(state) {
        if (typeof history.onpushstate === "function") {
            history.onpushstate({state: state});
        }
        // ... whatever else you want to do
        // maybe call onhashchange e.handler
        return pushState.apply(history, arguments);
    };
})(window.history);

window.onpopstate = history.onpushstate = function(e) {
    /*$.ajax({
        url: e.state,
        dataType: 'html',
        success: function (data) {
        }
    });*/
}

$(document).on('keydown','.number',function(e){
    var input = this.value;
    e = e || window.event;
    var key = e.keyCode || e.which;

    //if (key == null)
    //	char = String.fromCharCode(key);    // old IE
    //else if (key != 0 && key != 0)
    //	char = String.fromCharCode(key);	  // All others

    //console.log('char: ' + char);
    //console.log('key: ' + key);
    //console.log('e.keyCode: ' + e.keyCode);
    //console.log('e.which: ' + e.which);

    if(($(this).hasClass('decimal-point') || $(this).hasClass('percent')) && input.indexOf('.') === -1 && key == 190)
    {
        return true;
    }
    // https://prog.hu/tudastar/180161/javascript-adoszam-regex
    if($(this).hasClass('tax') && input.lastIndexOf('-') !== input.length-1 && key == 189)
    {
        return true;
    }
    if($(this).hasClass('percent') && input.indexOf('%') === -1 && key == 53)
    {
        return true;
    }
    if($(this).hasClass('minus') && input.indexOf('-') === -1 && key == 189 && this.selectionStart == 0)
    {
        return true;
    }

    // http://stackoverflow.com/questions/469357/html-text-input-allow-only-numeric-input
    // Allow: backspace, delete, tab, escape, enter and .
    if ($.inArray(key, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
        // Allow: Ctrl+A
        (key == 65 && e.ctrlKey === true) ||
        // Allow: Ctrl+C
        (key == 67 && e.ctrlKey === true) ||
        // Allow: Ctrl+X
        (key == 88 && e.ctrlKey === true) ||
        // Allow: home, end, left, right
        (key >= 35 && key <= 39) ||
        //Allow numbers and numbers + shift key
        ((e.shiftKey && (key >= 48 && key <= 57)) || (key >= 96 && key <= 105))) {
        // let it happen, don't do anything
        return true;
    }

    // mac os x etc 192 = char: Ă >>>>>> 0
    if (key == 192 && window.navigator && window.navigator.platform && window.navigator.platform.toUpperCase().indexOf('MAC') != -1) {
        return true;
    }

    var keys = [13,35,40,34,37,39,36,38,33,46,48,49,50,51,52,53,54,55,56,57,96,97,98,99,100,101,102,103,104,105,112,113,114,115,116,117,118,119,120,121,122,123];
    if($.inArray(key,keys) != -1)
    {
        return true;
    }

    var keys = [8,9,37,38,39,40];
    if(!input.match(/^([0-9]){1,2,3}$/g) && $.inArray(key,keys) == -1){
        return false;
    }
});

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

    $.fn.sack = function (replaceWith, cb) {
        if(typeof replaceWith === "function")
        {
            cb = replaceWith;
            replaceWith = undefined;
        }
        $(this).each(function(){
            var event = jQuery.Event('sack', {target: this});
            if(replaceWith !== undefined)
            {
                $(this).replaceWith(replaceWith);
                $(document).trigger(event);
                if(cb)
                    cb.apply(this);
                $(this).data('called-sack', true);
            }
            else if($(this).data('called-init') === undefined)
            {
                $(document).trigger(event);
                if(cb)
                    cb.apply(this);
                $(this).data('called-sack', true);
            }
            $('[data-sack]',this).sack();
        });
        return this;
    }

    $.sack = function (dom, replaceWith, cb) {
        $.fn.sack.apply(dom, [replaceWith, cb]);
    }

    $(window).load(function() {
        $('[data-sack]').each(function () {
            $(this).sack();
        });
    });

}));

$.ajaxSetup({
    // Disable caching of AJAX responses
    cache: false
});

function parseHeaders(headers, reverse)
{
    //
    // RAW STRING OF HEADERS
    //
    //var headers = request.getAllResponseHeaders();
    //console.log(headers);
    // "date: Fri, 08 Dec 2017 21:04:30 GMT\r\ncontent-encoding: gzip\r\nx-content-type-options: nosniff\r\nserver: meinheld/0.6.1\r\nx-frame-options: DENY\r\ncontent-type: text/html; charset=utf-8\r\nconnection: keep-alive\r\nstrict-transport-security: max-age=63072000\r\nvary: Cookie, Accept-Encoding\r\ncontent-length: 6503\r\nx-xss-protection: 1; mode=block\r\n"


    //
    // ARRAY OF HEADERS
    //
    var arr = headers.trim().split(/[\r\n]+/);

    arr = arr.sort(function(a,b){ return a > b; });

    if(reverse === true)
        arr = arr.reverse();

    //
    // MAP OF HEADERS
    //
    var map = {};
    arr.forEach(function (line) {
        var parts = line.split(': ');
        var header = parts.shift();
        var value = parts.join(': ');
        map[header] = value;
    });

    return map;
}

function parseHeadersArray(headers, reverse)
{
    //
    // RAW STRING OF HEADERS
    //
    //var headers = request.getAllResponseHeaders();
    //console.log(headers);
    // "date: Fri, 08 Dec 2017 21:04:30 GMT\r\ncontent-encoding: gzip\r\nx-content-type-options: nosniff\r\nserver: meinheld/0.6.1\r\nx-frame-options: DENY\r\ncontent-type: text/html; charset=utf-8\r\nconnection: keep-alive\r\nstrict-transport-security: max-age=63072000\r\nvary: Cookie, Accept-Encoding\r\ncontent-length: 6503\r\nx-xss-protection: 1; mode=block\r\n"


    //
    // ARRAY OF HEADERS
    //
    var arr = headers.trim().split(/[\r\n]+/);

    var rarr = [];
    arr.forEach(function (line) {
        var parts = line.split(': ');
        var header = parts.shift();
        var value = parts.join(': ');
        rarr.push({'header': header, 'value': value});
    });

    rarr = rarr.sort(function(a,b){ return a.header > b.header; });

    if(reverse === true)
        rarr = rarr.reverse();

    return rarr;
}


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

    var dotsDefaults = {
        dots: 3,
        dot: " .",
        text: undefined,
        interval: 300
    }

    function getDataInfo($element) {
        var data = $element.data();
        var d = {};
        $.each(data, function(key, value) {
            if(key.indexOf("dots") !== -1) {
                var k = key.substr(3);
                k = k.charAt(0).toLowerCase() + k.slice(1);
                d[k] = value;
            }
        });
        return d;
    }

    var dotsFunctions = {
        init: function(args){
            var options = $.extend(true, {}, getDataInfo($(this)), dotsDefaults, args);
            $(this).each(function(){
                var $this = $(this);
                var buttonDots = $(this).data('dotsInterval');
                if (buttonDots !== undefined)
                    return;
                var text = options.text || ($this.is(':input') ? $this.val() : $this.text());
                var dots = 0;
                var f = function () {
                    if($this.is(':input')) {
                        $this.val(text + options.dot.repeat(dots));
                    }
                    else {
                        $this.text(text + options.dot.repeat(dots));
                    }
                    dots++;
                    if (dots == options.dots+1)
                        dots = 0;
                };
                f();
                buttonDots = setInterval(f, options.interval);
                $(this).data('dotsInterval', buttonDots);
                options.text = text;
                $(this).data('dotsOptions', options);
                var event = jQuery.Event('dots:start', {target: this});
                $(document).trigger(event, [options]);
            })
            return this;
        },
        stop: function(){
            var args = [].slice.apply(arguments);
            args.unshift('stop');
            return dotsFunctions['_end'].apply(this, args);
        },
        end: function(){
            var args = [].slice.apply(arguments);
            args.unshift('end');
            return dotsFunctions['_end'].apply(this, args);
        },
        // ------ PRIVATE METHODS ------ //
        _end: function(eventType){
            $(this).each(function(){
                var buttonDots = $(this).data('dotsInterval');

                if (buttonDots !== undefined)
                    clearInterval(buttonDots);

                $(this).removeData('dotsInterval');

                var options = $(this).data('dotsOptions');

                if($(this).is(':input'))
                    $(this).val(options.text);
                else
                    $(this).text(options.text);
                var event = jQuery.Event('dots:'+eventType, {target: this});
                $(document).trigger(event, [options]);
            });
            return this;
        }
    }

    $.fn.dots = function () {
        var method = arguments.length > 0 ? arguments[0] : 'init';
        if(typeof dotsFunctions[method] !== 'undefined') {
            var arg = [].shift.apply(arguments);
            $(this).each(function () {
                dotsFunctions[method].apply(this, arguments);
            });
        }
        else
        {
            var args = [].slice.apply(arguments);
            $(this).each(function () {
                dotsFunctions['init'].apply(this, args);
            });
        }
        return this;
    }

    $.dots = function (dom) {
        var arg = [].shift.apply(arguments);
        $.fn.dots.apply(dom, arguments);
    }


}));

function ajaxError(event, xhr, settings){
    //alert(xhr.responseText);
    try
    {
        var j = {};
        if(xhr.responseText.indexOf('}{"error":') !== -1)
        {
            var json = xhr.responseText.split('}{"error":');
            j = JSON.parse(json[0]+"}");
        }
        else
        {
            j = JSON.parse(xhr.responseText);
        }
        //console.log(j);
        if(j.error)
        {
            var errorMessage = '';
            if(j.error.constructor === {}.constructor)
            {
                function errorOut(error, level, collapse) {
                    if(level == undefined)
                        level = 0;
                    if(collapse == undefined)
                        collapse = 0;
                    var errorMessage = '';
                    $.each(error, function (key, value) {
                        if (value != null && (value.constructor === {}.constructor || value.constructor === [].constructor)) {
                            var id = 'error_'+makeid();
                            errorMessage += '<div data-toggle="collapse" data-target="#'+id+'" aria-expanded="false" aria-controls="'+id+'" style="cursor: pointer;">\n' +
                                '  \n' + key + '<span class="caret"></span>' +
                                '</div>\n' +
                                '<div class="collapse '+ (level > collapse ? 'in' : '') + '" id="'+id+'" style="margin-left: 20px;">\n' +
                                '    ' +  errorOut(value, level+1, collapse) +
                                '</div>'
                        }
                        else
                        {
                            errorMessage += '<div>' + key + ': ' + value + '</div>';
                        }
                    });
                    return errorMessage;
                }
                errorMessage = errorOut(j.error);
            }
            else
            {
                errorMessage = j.error;
            }
            var c = $.confirm({
                title: 'Hiba!',
                content: errorMessage,
                columnClass: 'col-md-12',
                type: 'red',
                escapeKey: 'ok',
                onOpen: function () {
                    var c = $('#jconfirm-box'+this._id);
                    $('pre code',c).each(function(i, block) {
                        hljs.highlightBlock(block);
                    });
                },
                buttons: {
                    ok: {
                        text: 'Értettem',
                        btnClass: 'btn-red',
                        keys: ['enter', 'space', 'shift'],
                        action: function(){

                        }
                    }
                }
            });
            return true;
        }
    } catch (e) {
        //console.log(e);
    }
    return xhr.responseText;
}
$.ajaxSetup(
    {
        /*statusCode: {
            500: function(response) {
                ajaxError(undefined, response, undefined);
            }
        },*/
        converters: {
            "text json": function ( result ) {
                ajaxError(undefined, {responseText: result}, undefined);
                return JSON.parse(result);
            },
            "text html": function ( result ) {
                if(ajaxError(undefined, {responseText: result}, undefined) !== true)
                    return result;
            },
            "text xml": function ( result ) {
                if(ajaxError(undefined, {responseText: result}, undefined) !== true)
                    return jQuery.parseXML(result);
            },
        }
    }
);
//$(document).ajaxError(ajaxError);
//$(document).ajaxSuccess(ajaxError);
function debug(event, xhr, settings){
    var headers = parseHeadersArray(xhr.getAllResponseHeaders(), true);
    headers.forEach(function(head){
        var header = head.header;
        var value = head.value;
        //$.each(headers, function (header, value){
        if(header.toLowerCase().indexOf("php-debug") === 0)
        {
            var parser = header.toLowerCase().split("-");
            parser = parser.pop();
            if(jQuery.ajaxSettings.converters["text "+parser]) {
                parser = jQuery.ajaxSettings.converters["text " + parser];
            }
            else if(jQuery.ajaxSettings.converters["* "+parser]) {
                parser = jQuery.ajaxSettings.converters["* " + parser];
            }
            else {
                parser = function (r) {
                    return r;
                };
            }
            console.dir(parser(decodeURIComponent(value.replace(/\+/g, ' '))));
        }
    });
}
$(document).ajaxError(debug);
$(document).ajaxSuccess(debug);

$(document).ajaxStart(function() { Pace.restart(); });

$(document).on('change','input[type="file"].import',function(){

    var form_data = new FormData();
    var file_data = this.files[0];
    form_data.append(this.name, file_data);
    this.value = '';

    var $this = $(this);
    url = window.location.href;
    if($(this).data('url') != undefined)
        url = $(this).data('url');

    var c = $.confirm({
        title: 'Importálás',
        content: '<div class="progress"><div class="progress-bar progress-bar-striped progress-bar-site active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%"></div></div>',
        type: 'default',
        columnClass: 'col-md-12 col-xs-12',
        buttons: {
            ok: {
                text: 'Rendben',
                btnClass: 'btn-default hidden',
                action: function(){
                    if($this.data('reload') != undefined)
                        window.location.reload();
                    return true;
                }
            },
        }
    });

    var run = false;

    function check()
    {
        if(run == true)
            return;
        run = true;
        $.ajax({
            url: url,
            dataType: 'text',
            type: 'post',
            data: {},
            error: function(){
                c.setContent('Túl sokáig tartott az importálás! A kérés feldolgozása félbe szakadt!');
                c.buttons.ok.removeClass('hidden');
            },
            success: function(response){
                if(response == '[]')
                {
                    run = false;
                    check();
                }
                else
                {
                    response = JSON.parse(response);
                    c.setContent('<div style="max-height: '+(window.innerHeight-300)+'px;overflow: auto;">'+response.text+'</div>');
                    c.buttons.ok.removeClass('hidden');
                }
            }
        });
    }

    function completed(percentComplete)
    {
        if(percentComplete == 1)
        {
            check();
        }
    }

    $.ajax({
        xhr: function()
        {
            var xhr = new window.XMLHttpRequest();
            //Upload progress
            xhr.upload.addEventListener("progress", function(evt){
                if (evt.lengthComputable) {
                    var percentComplete = evt.loaded / evt.total;
                    completed(percentComplete);
                }
            }, false);
            //Download progress
            xhr.addEventListener("progress", function(evt){
                if (evt.lengthComputable) {
                    var percentComplete = evt.loaded / evt.total;
                    completed(percentComplete);
                }
            }, false);
            return xhr;
        },
        dataType: 'json',  // what to expect back from the PHP script, if anything
        cache: false,
        contentType: false,
        url: url,
        processData: false,
        data: form_data,
        type: 'post',
        error: function(){

        },
        success: function(response){

        }
    });

});

function makeid()
{
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < 5; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}

moment.locale(lang);

$(document).ready(function () {
    $('.login-box').on('submit', 'form', function () {
        var parent = $(this).parents('.login-box');
        var $this = $(this);
        var type = $this.attr('method') !== undefined ? $this.attr('method') : 'GET';
        $.ajax({
            data: $(this).serialize(),
            dataType: 'JSON',
            type: type,
            beforeSend: function () {
                $('input', $this).attr('disabled', 'disabled').prop('disabled', true);
                $('.message', parent).hide().removeClass('alert-danger').removeClass('alert-success');
            },
            success: function (data) {
                $('input', $this).removeAttr('disabled', 'disabled').prop('disabled', false);
                if (data.message) {
                    $('.message', parent).show().removeClass('alert-danger').removeClass('alert-success');
                    $('.message', parent).text(data.message);
                }
                else {
                    $('.message', parent).hide();
                }
                if (data.result > 0) {
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                    $('.message', parent).removeClass('alert-danger').addClass('alert-success');
                }
                else {
                    $('.message', parent).removeClass('alert-success').addClass('alert-danger');
                }
            }
        });
        return false;
    });


    var base = document.getElementsByTagName('base')[0].href;

    var url = window.location.href.substr(base.length);
    $('.sidebar-menu [href="' + window.location.href + '"], .sidebar-menu [href="' + url + '"]').parent().addClass('current').addClass('active');
    var $treeview = $('.sidebar-menu [href="' + window.location.href + '"], .sidebar-menu [href="' + url + '"]').parents('.treeview');
    if ($treeview.length > 0) {
        $treeview.addClass('menu-open');
        $('.treeview-menu', $treeview).slideDown();
    }
});