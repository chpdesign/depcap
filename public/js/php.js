function empty (mixedVar) {
    //  discuss at: http://locutus.io/php/empty/
    // original by: Philippe Baumann
    //    input by: Onno Marsman (https://twitter.com/onnomarsman)
    //    input by: LH
    //    input by: Stoyan Kyosev (http://www.svest.org/)
    // bugfixed by: Kevin van Zonneveld (http://kvz.io)
    // improved by: Onno Marsman (https://twitter.com/onnomarsman)
    // improved by: Francesco
    // improved by: Marc Jansen
    // improved by: Rafał Kukawski (http://blog.kukawski.pl)
    //   example 1: empty(null)
    //   returns 1: true
    //   example 2: empty(undefined)
    //   returns 2: true
    //   example 3: empty([])
    //   returns 3: true
    //   example 4: empty({})
    //   returns 4: true
    //   example 5: empty({'aFunc' : function () { alert('humpty'); } })
    //   returns 5: false

    var undef
    var key
    var i
    var len
    var emptyValues = [undef, null, false, 0, '', '0']

    for (i = 0, len = emptyValues.length; i < len; i++) {
        if (mixedVar === emptyValues[i]) {
            return true
        }
    }

    if (typeof mixedVar === 'object') {
        for (key in mixedVar) {
            if (mixedVar.hasOwnProperty(key)) {
                return false
            }
        }
        return true
    }

    return false
}

function range (low, high, step) {
    //  discuss at: http://locutus.io/php/range/
    // original by: Waldo Malqui Silva (http://waldo.malqui.info)
    //   example 1: range ( 0, 12 )
    //   returns 1: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
    //   example 2: range( 0, 100, 10 )
    //   returns 2: [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100]
    //   example 3: range( 'a', 'i' )
    //   returns 3: ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i']
    //   example 4: range( 'c', 'a' )
    //   returns 4: ['c', 'b', 'a']

    var matrix = []
    var iVal
    var endval
    var plus
    var walker = step || 1
    var chars = false

    if (!isNaN(low) && !isNaN(high)) {
        iVal = low
        endval = high
    } else if (isNaN(low) && isNaN(high)) {
        chars = true
        iVal = low.charCodeAt(0)
        endval = high.charCodeAt(0)
    } else {
        iVal = (isNaN(low) ? 0 : low)
        endval = (isNaN(high) ? 0 : high)
    }

    plus = !(iVal > endval)
    if (plus) {
        while (iVal <= endval) {
            matrix.push(((chars) ? String.fromCharCode(iVal) : iVal))
            iVal += walker
        }
    } else {
        while (iVal >= endval) {
            matrix.push(((chars) ? String.fromCharCode(iVal) : iVal))
            iVal -= walker
        }
    }

    return matrix
}

function parse_url (str, component) { // eslint-disable-line camelcase
                                      //       discuss at: http://locutus.io/php/parse_url/
                                      //      original by: Steven Levithan (http://blog.stevenlevithan.com)
                                      // reimplemented by: Brett Zamir (http://brett-zamir.me)
                                      //         input by: Lorenzo Pisani
                                      //         input by: Tony
                                      //      improved by: Brett Zamir (http://brett-zamir.me)
                                      //           note 1: original by http://stevenlevithan.com/demo/parseuri/js/assets/parseuri.js
                                      //           note 1: blog post at http://blog.stevenlevithan.com/archives/parseuri
                                      //           note 1: demo at http://stevenlevithan.com/demo/parseuri/js/assets/parseuri.js
                                      //           note 1: Does not replace invalid characters with '_' as in PHP,
                                      //           note 1: nor does it return false with
                                      //           note 1: a seriously malformed URL.
                                      //           note 1: Besides function name, is essentially the same as parseUri as
                                      //           note 1: well as our allowing
                                      //           note 1: an extra slash after the scheme/protocol (to allow file:/// as in PHP)
                                      //        example 1: parse_url('http://user:pass@host/path?a=v#a')
                                      //        returns 1: {scheme: 'http', host: 'host', user: 'user', pass: 'pass', path: '/path', query: 'a=v', fragment: 'a'}
                                      //        example 2: parse_url('http://en.wikipedia.org/wiki/%22@%22_%28album%29')
                                      //        returns 2: {scheme: 'http', host: 'en.wikipedia.org', path: '/wiki/%22@%22_%28album%29'}
                                      //        example 3: parse_url('https://host.domain.tld/a@b.c/folder')
                                      //        returns 3: {scheme: 'https', host: 'host.domain.tld', path: '/a@b.c/folder'}
                                      //        example 4: parse_url('https://gooduser:secretpassword@www.example.com/a@b.c/folder?foo=bar')
                                      //        returns 4: { scheme: 'https', host: 'www.example.com', path: '/a@b.c/folder', query: 'foo=bar', user: 'gooduser', pass: 'secretpassword' }

    var query

    var mode = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.parse_url.mode') : undefined) || 'php'

    var key = [
        'source',
        'scheme',
        'authority',
        'userInfo',
        'user',
        'pass',
        'host',
        'port',
        'relative',
        'path',
        'directory',
        'file',
        'query',
        'fragment'
    ]

    // For loose we added one optional slash to post-scheme to catch file:/// (should restrict this)
    var parser = {
        php: new RegExp([
            '(?:([^:\\/?#]+):)?',
            '(?:\\/\\/()(?:(?:()(?:([^:@\\/]*):?([^:@\\/]*))?@)?([^:\\/?#]*)(?::(\\d*))?))?',
            '()',
            '(?:(()(?:(?:[^?#\\/]*\\/)*)()(?:[^?#]*))(?:\\?([^#]*))?(?:#(.*))?)'
        ].join('')),
        strict: new RegExp([
            '(?:([^:\\/?#]+):)?',
            '(?:\\/\\/((?:(([^:@\\/]*):?([^:@\\/]*))?@)?([^:\\/?#]*)(?::(\\d*))?))?',
            '((((?:[^?#\\/]*\\/)*)([^?#]*))(?:\\?([^#]*))?(?:#(.*))?)'
        ].join('')),
        loose: new RegExp([
            '(?:(?![^:@]+:[^:@\\/]*@)([^:\\/?#.]+):)?',
            '(?:\\/\\/\\/?)?',
            '((?:(([^:@\\/]*):?([^:@\\/]*))?@)?([^:\\/?#]*)(?::(\\d*))?)',
            '(((\\/(?:[^?#](?![^?#\\/]*\\.[^?#\\/.]+(?:[?#]|$)))*\\/?)?([^?#\\/]*))',
            '(?:\\?([^#]*))?(?:#(.*))?)'
        ].join(''))
    }

    var m = parser[mode].exec(str)
    var uri = {}
    var i = 14

    while (i--) {
        if (m[i]) {
            uri[key[i]] = m[i]
        }
    }

    if (component) {
        return uri[component.replace('PHP_URL_', '').toLowerCase()]
    }

    if (mode !== 'php') {
        var name = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.parse_url.queryKey') : undefined) || 'queryKey'
        parser = /(?:^|&)([^&=]*)=?([^&]*)/g
        uri[name] = {}
        query = uri[key[12]] || ''
        query.replace(parser, function ($0, $1, $2) {
            if ($1) {
                uri[name][$1] = $2
            }
        })
    }

    delete uri.source
    return uri
}

function unparse_url(parsed_url) {
    var scheme   = typeof parsed_url['scheme'] !== 'undefined' ? parsed_url['scheme'] + '://' : '';
    var host     = typeof parsed_url['host'] !== 'undefined' ? parsed_url['host'] : '';
    var port     = typeof parsed_url['port'] !== 'undefined' ? ':' + parsed_url['port'] : '';
    var user     = typeof parsed_url['user'] !== 'undefined' ? parsed_url['user'] : '';
    var pass     = typeof parsed_url['pass'] !== 'undefined' ? ':' + parsed_url['pass']  : '';
        pass     = (user || pass) ? pass + '@' : '';
    var path     = typeof parsed_url['path'] !== 'undefined' ? parsed_url['path'] : '';
    var query    = typeof parsed_url['query'] !== 'undefined' ? '?' + parsed_url['query'] : '';
    var fragment = typeof parsed_url['fragment'] !== 'undefined' ? '#' + parsed_url['fragment'] : '';
    return scheme + user + pass + host + port + path + query + fragment;
}

function parse_str (str, array) { // eslint-disable-line camelcase
                                  //       discuss at: http://locutus.io/php/parse_str/
                                  //      original by: Cagri Ekin
                                  //      improved by: Michael White (http://getsprink.com)
                                  //      improved by: Jack
                                  //      improved by: Brett Zamir (http://brett-zamir.me)
                                  //      bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
                                  //      bugfixed by: Brett Zamir (http://brett-zamir.me)
                                  //      bugfixed by: stag019
                                  //      bugfixed by: Brett Zamir (http://brett-zamir.me)
                                  //      bugfixed by: MIO_KODUKI (http://mio-koduki.blogspot.com/)
                                  // reimplemented by: stag019
                                  //         input by: Dreamer
                                  //         input by: Zaide (http://zaidesthings.com/)
                                  //         input by: David Pesta (http://davidpesta.com/)
                                  //         input by: jeicquest
                                  //           note 1: When no argument is specified, will put variables in global scope.
                                  //           note 1: When a particular argument has been passed, and the
                                  //           note 1: returned value is different parse_str of PHP.
                                  //           note 1: For example, a=b=c&d====c
                                  //        example 1: var $arr = {}
                                  //        example 1: parse_str('first=foo&second=bar', $arr)
                                  //        example 1: var $result = $arr
                                  //        returns 1: { first: 'foo', second: 'bar' }
                                  //        example 2: var $arr = {}
                                  //        example 2: parse_str('str_a=Jack+and+Jill+didn%27t+see+the+well.', $arr)
                                  //        example 2: var $result = $arr
                                  //        returns 2: { str_a: "Jack and Jill didn't see the well." }
                                  //        example 3: var $abc = {3:'a'}
                                  //        example 3: parse_str('a[b]["c"]=def&a[q]=t+5', $abc)
                                  //        example 3: var $result = $abc
                                  //        returns 3: {"3":"a","a":{"b":{"c":"def"},"q":"t 5"}}

    var strArr = String(str).replace(/^&/, '').replace(/&$/, '').split('&')
    var sal = strArr.length
    var i
    var j
    var ct
    var p
    var lastObj
    var obj
    var undef
    var chr
    var tmp
    var key
    var value
    var postLeftBracketPos
    var keys
    var keysLen

    var _fixStr = function (str) {
        return decodeURIComponent(str.replace(/\+/g, '%20'))
    }

    var $global = (typeof window !== 'undefined' ? window : global)
    $global.$locutus = $global.$locutus || {}
    var $locutus = $global.$locutus
    $locutus.php = $locutus.php || {}

    if (!array) {
        array = $global
    }

    for (i = 0; i < sal; i++) {
        tmp = strArr[i].split('=')
        key = _fixStr(tmp[0])
        value = (tmp.length < 2) ? '' : _fixStr(tmp[1])

        while (key.charAt(0) === ' ') {
            key = key.slice(1)
        }
        if (key.indexOf('\x00') > -1) {
            key = key.slice(0, key.indexOf('\x00'))
        }
        if (key && key.charAt(0) !== '[') {
            keys = []
            postLeftBracketPos = 0
            for (j = 0; j < key.length; j++) {
                if (key.charAt(j) === '[' && !postLeftBracketPos) {
                    postLeftBracketPos = j + 1
                } else if (key.charAt(j) === ']') {
                    if (postLeftBracketPos) {
                        if (!keys.length) {
                            keys.push(key.slice(0, postLeftBracketPos - 1))
                        }
                        keys.push(key.substr(postLeftBracketPos, j - postLeftBracketPos))
                        postLeftBracketPos = 0
                        if (key.charAt(j + 1) !== '[') {
                            break
                        }
                    }
                }
            }
            if (!keys.length) {
                keys = [key]
            }
            for (j = 0; j < keys[0].length; j++) {
                chr = keys[0].charAt(j)
                if (chr === ' ' || chr === '.' || chr === '[') {
                    keys[0] = keys[0].substr(0, j) + '_' + keys[0].substr(j + 1)
                }
                if (chr === '[') {
                    break
                }
            }

            obj = array
            for (j = 0, keysLen = keys.length; j < keysLen; j++) {
                key = keys[j].replace(/^['"]/, '').replace(/['"]$/, '')
                lastObj = obj
                if ((key !== '' && key !== ' ') || j === 0) {
                    if (obj[key] === undef) {
                        obj[key] = {}
                    }
                    obj = obj[key]
                } else {
                    // To insert new dimension
                    ct = -1
                    for (p in obj) {
                        if (obj.hasOwnProperty(p)) {
                            if (+p > ct && p.match(/^\d+$/g)) {
                                ct = +p
                            }
                        }
                    }
                    key = ct + 1
                }
            }
            lastObj[key] = value
        }
    }
}

function rawurlencode (str) {
    //       discuss at: http://locutus.io/php/rawurlencode/
    //      original by: Brett Zamir (http://brett-zamir.me)
    //         input by: travc
    //         input by: Brett Zamir (http://brett-zamir.me)
    //         input by: Michael Grier
    //         input by: Ratheous
    //      bugfixed by: Kevin van Zonneveld (http://kvz.io)
    //      bugfixed by: Brett Zamir (http://brett-zamir.me)
    //      bugfixed by: Joris
    // reimplemented by: Brett Zamir (http://brett-zamir.me)
    // reimplemented by: Brett Zamir (http://brett-zamir.me)
    //           note 1: This reflects PHP 5.3/6.0+ behavior
    //           note 1: Please be aware that this function expects \
    //           note 1: to encode into UTF-8 encoded strings, as found on
    //           note 1: pages served as UTF-8
    //        example 1: rawurlencode('Kevin van Zonneveld!')
    //        returns 1: 'Kevin%20van%20Zonneveld%21'
    //        example 2: rawurlencode('http://kvz.io/')
    //        returns 2: 'http%3A%2F%2Fkvz.io%2F'
    //        example 3: rawurlencode('http://www.google.nl/search?q=Locutus&ie=utf-8')
    //        returns 3: 'http%3A%2F%2Fwww.google.nl%2Fsearch%3Fq%3DLocutus%26ie%3Dutf-8'

    str = (str + '')

    // Tilde should be allowed unescaped in future versions of PHP (as reflected below),
    // but if you want to reflect current
    // PHP behavior, you would need to add ".replace(/~/g, '%7E');" to the following.
    return encodeURIComponent(str)
        .replace(/!/g, '%21')
        .replace(/'/g, '%27')
        .replace(/\(/g, '%28')
        .replace(/\)/g, '%29')
        .replace(/\*/g, '%2A')
}

function urlencode (str) {
    //       discuss at: http://locutus.io/php/urlencode/
    //      original by: Philip Peterson
    //      improved by: Kevin van Zonneveld (http://kvz.io)
    //      improved by: Kevin van Zonneveld (http://kvz.io)
    //      improved by: Brett Zamir (http://brett-zamir.me)
    //      improved by: Lars Fischer
    //         input by: AJ
    //         input by: travc
    //         input by: Brett Zamir (http://brett-zamir.me)
    //         input by: Ratheous
    //      bugfixed by: Kevin van Zonneveld (http://kvz.io)
    //      bugfixed by: Kevin van Zonneveld (http://kvz.io)
    //      bugfixed by: Joris
    // reimplemented by: Brett Zamir (http://brett-zamir.me)
    // reimplemented by: Brett Zamir (http://brett-zamir.me)
    //           note 1: This reflects PHP 5.3/6.0+ behavior
    //           note 1: Please be aware that this function
    //           note 1: expects to encode into UTF-8 encoded strings, as found on
    //           note 1: pages served as UTF-8
    //        example 1: urlencode('Kevin van Zonneveld!')
    //        returns 1: 'Kevin+van+Zonneveld%21'
    //        example 2: urlencode('http://kvz.io/')
    //        returns 2: 'http%3A%2F%2Fkvz.io%2F'
    //        example 3: urlencode('http://www.google.nl/search?q=Locutus&ie=utf-8')
    //        returns 3: 'http%3A%2F%2Fwww.google.nl%2Fsearch%3Fq%3DLocutus%26ie%3Dutf-8'

    str = (str + '')

    // Tilde should be allowed unescaped in future versions of PHP (as reflected below),
    // but if you want to reflect current
    // PHP behavior, you would need to add ".replace(/~/g, '%7E');" to the following.
    return encodeURIComponent(str)
        .replace(/!/g, '%21')
        .replace(/'/g, '%27')
        .replace(/\(/g, '%28')
        .replace(/\)/g, '%29')
        .replace(/\*/g, '%2A')
        .replace(/%20/g, '+')
}

function http_build_query (formdata, numericPrefix, argSeparator, encType) { // eslint-disable-line camelcase
                                                                             //  discuss at: http://locutus.io/php/http_build_query/
                                                                             // original by: Kevin van Zonneveld (http://kvz.io)
                                                                             // improved by: Legaev Andrey
                                                                             // improved by: Michael White (http://getsprink.com)
                                                                             // improved by: Kevin van Zonneveld (http://kvz.io)
                                                                             // improved by: Brett Zamir (http://brett-zamir.me)
                                                                             //  revised by: stag019
                                                                             //    input by: Dreamer
                                                                             // bugfixed by: Brett Zamir (http://brett-zamir.me)
                                                                             // bugfixed by: MIO_KODUKI (http://mio-koduki.blogspot.com/)
                                                                             // improved by: Will Rowe
                                                                             //      note 1: If the value is null, key and value are skipped in the
                                                                             //      note 1: http_build_query of PHP while in locutus they are not.
                                                                             //   example 1: http_build_query({foo: 'bar', php: 'hypertext processor', baz: 'boom', cow: 'milk'}, '', '&amp;')
                                                                             //   returns 1: 'foo=bar&amp;php=hypertext+processor&amp;baz=boom&amp;cow=milk'
                                                                             //   example 2: http_build_query({'php': 'hypertext processor', 0: 'foo', 1: 'bar', 2: 'baz', 3: 'boom', 'cow': 'milk'}, 'myvar_')
                                                                             //   returns 2: 'myvar_0=foo&myvar_1=bar&myvar_2=baz&myvar_3=boom&php=hypertext+processor&cow=milk'
                                                                             //   example 3: http_build_query({foo: 'bar', php: 'hypertext processor', baz: 'boom', cow: 'milk'}, '', '&amp;', 'PHP_QUERY_RFC3986')
                                                                             //   returns 3: 'foo=bar&amp;php=hypertext%20processor&amp;baz=boom&amp;cow=milk'

    var encodeFunc

    switch (encType) {
        case 'PHP_QUERY_RFC3986':
            encodeFunc = rawurlencode
            break

        case 'PHP_QUERY_RFC1738':
        default:
            encodeFunc = urlencode
            break
    }

    var value
    var key
    var tmp = []

    var _httpBuildQueryHelper = function (key, val, argSeparator) {
        var k
        var tmp = []
        if (val === true) {
            val = '1'
        } else if (val === false) {
            val = '0'
        }
        if (val !== null) {
            if (typeof val === 'object') {
                for (k in val) {
                    if (val[k] !== null) {
                        tmp.push(_httpBuildQueryHelper(key + '[' + k + ']', val[k], argSeparator))
                    }
                }
                return tmp.join(argSeparator)
            } else if (typeof val !== 'function') {
                return encodeFunc(key) + '=' + encodeFunc(val)
            } else {
                throw new Error('There was an error processing for http_build_query().')
            }
        } else {
            return ''
        }
    }

    if (!argSeparator) {
        argSeparator = '&'
    }
    for (key in formdata) {
        value = formdata[key]
        if (numericPrefix && !isNaN(key)) {
            key = String(numericPrefix) + key
        }
        var query = _httpBuildQueryHelper(key, value, argSeparator)
        if (query !== '') {
            tmp.push(query)
        }
    }

    return tmp.join(argSeparator)
}