//require('angular/angular');
var sygeforApp = angular.module('conjecto.sygefor.app', ['ui.bootstrap', 'ui.router', 'ngAnimate', 'angular-growl', 'zeroclipboard', 'chieffancypants.loadingBar', 'angularjssearchbox', 'blueimp.fileupload', 'cfp.hotkeys', 'ngStorage', 'checklist-model']);

/**
 * config block
 */
sygeforApp.config(function($httpProvider, $urlRouterProvider, growlProvider, uiZeroclipConfigProvider) {
    // set some default headers
    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    $httpProvider.defaults.headers.common['Accept'] = 'application/json';
    $httpProvider.interceptors.push('httpInterceptor');

    // ui-router : default redirect to the dashboard
    $urlRouterProvider.otherwise(function () {
        return "/dashboard";
    });

    // growl
    growlProvider.globalTimeToLive(10000);

    // ZeroClipboard
    uiZeroclipConfigProvider.setZcConf({
        swfPath: './ZeroClipboard.swf'
    });
});

/**
 * run block
 */
sygeforApp.run(['$rootScope', '$location', '$dialog', '$templateCache', 'hotkeys', function($rootScope, $location, $dialog, $templateCache, hotkeys) {
    // set the $dialog service available in all scopes
    $rootScope.$dialog = $dialog;
    /*$location.replace = function() {

    }*/
    // log stateChange errors
    $rootScope.$on('$stateChangeError',
        function(event, toState, toParams, fromState, fromParams, error){
            //console.log(error);
        });

//    hotkeys.add({
//        combo: 'ctrl+del',
//        description: 'Vide le cache de template',
//        callback: function() {
//            $templateCache.removeAll();
//        }
//    });
}]);

/**
 * Filter : joinObjects
 */
sygeforApp.filter('joinObjects', function () {
    return function (input, property, delimiter) {
        if(!Array.isArray(input)) {
            input = Object.keys(input).map(function (key) {return input[key]});
        }
        return input.map(function(o) { return o[property || 'name']; }).join(delimiter || ', ');
    };
});

/**
 * Filter : orderObjectBy
 */
sygeforApp.filter('orderObjectBy', function() {
  return function(items, field, reverse) {
    var filtered = [];
    angular.forEach(items, function(item) {
      filtered.push(item);
    });
    filtered.sort(function (a, b) {
      return (a[field] > b[field] ? 1 : -1);
    });
    if(reverse) filtered.reverse();
    return filtered;
  };
});

/**
 * Filter : ceil
 */
sygeforApp.filter('ceil', function() {
    return function(input) {
        return Math.ceil(input);
    }
});

/**
 * Filter : floor
 */
sygeforApp.filter('floor', function() {
    return function(input) {
        return Math.floor(input);
    }
});

/**
 * Filter : slice
 */
sygeforApp.filter('slice', function() {
  return function(input, start, end) {
    return arr.slice(input, end);
  };
});

/**
 * Filter : nl2br
 */
sygeforApp.filter('nl2br', function($sce){
    return function(text) {
        return $sce.trustAsHtml(text.replace(/\n/g, '<br>'));
    };
});

/**
 * Filter : typeaheadlist
 */
sygeforApp.filter('typeaheadlist', function() {
    return function(input) {
        var result = [];
        for (var i in input) {
            result.push({'value':i,'label':input[i]});
        }

        return result;
    };
});

/**
 * Filter : nl2br
 */
sygeforApp.filter('nl2br', function($sce) {
    return function(text) {
        return text ? $sce.trustAsHtml(text.replace(/\n/g, '<br/>')) : '';
    };
});


/**
 * Filter : characters (truncate)
 */
sygeforApp.filter('characters', function () {
    return function (input, chars, breakOnWord) {
        if (isNaN(chars)) return input;
        if (chars <= 0) return '';
        if (input && input.length > chars) {
            input = input.substring(0, chars);

            if (!breakOnWord) {
                var lastspace = input.lastIndexOf(' ');
                //get last space
                if (lastspace !== -1) {
                    input = input.substr(0, lastspace);
                }
            }else{
                while(input.charAt(input.length-1) === ' '){
                    input = input.substr(0, input.length -1);
                }
            }
            return input + '...';
        }
        return input;
    };
});

/**
 * Filter : words (truncate)
 */
sygeforApp.filter('words', function () {
    return function (input, words) {
        if (isNaN(words)) return input;
        if (words <= 0) return '';
        if (input) {
            var inputWords = input.split(/\s+/);
            if (inputWords.length > words) {
                input = inputWords.slice(0, words).join(' ') + '...';
            }
        }
        return input;
    };
});


/**
 * Factory : httpInterceptor
 */
sygeforApp.factory('httpInterceptor', function($q, growl) {
    return {
        // optional method
        'requestError': function(rejection) {
            // do something on error
            growl.addErrorMessage("requestError");
            return $q.reject(rejection);
        },

        // optional method
        'response': function(response) {
            // do something on success
            return response;
        },

        // optional method
        'responseError': function(rejection) {
            //if response data contains a message, it is displayed
            if (rejection.data.message) {
                // symfony prod env
                growl.addErrorMessage(rejection.data.message);
            } else if(rejection.data[0] && rejection.data[0].message) {
                // symfony dev env
                growl.addErrorMessage(rejection.data[0].message);
            } else {
                // otherwise the statusText is displayed
                growl.addErrorMessage("Le serveur a renvoyé une erreur.");
                console.log("Server error : " + rejection.statusText);
            }

            return $q.reject(rejection);
        }
    };
});
