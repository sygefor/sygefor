/**
 * uiSref Directive
 * Add some logics to the original uiSref directive
 */
/*sygeforApp.directive('uiSref', ['$state', function($state)
{
    function parseStateRef(ref) {
        var parsed = ref.replace(/\n/g, " ").match(/^([^(]+?)\s*(\((.*)\))?$/);
        if (!parsed || parsed.length !== 4) throw new Error("Invalid state ref '" + ref + "'");
        return { state: parsed[1], paramExpr: parsed[3] || null };
    }

    function stateContext(el) {
        var stateData = el.parent().inheritedData('$uiView');
        if (stateData && stateData.state && stateData.state.name) {
            return stateData.state;
        }
    }

    return {
        restrict: 'A',
        priority: 400,
        link: function(scope, element, attrs, uiSrefActive) {
            var ref = parseStateRef(attrs.uiSref);
            var params = null, url = null, base = stateContext(element) || $state.$current;
            var isForm = element[0].nodeName === "FORM";
            var attr = isForm ? "action" : "href", nav = true;

            var update = function(newVal) {
                // get the root state name
                var root = ref.state.split(".")[0];
                if(element[0][attr] && !$state.includes(root + '.**')) {
                    // if the referenced state is not a child of the current state
                    var rootState = $state.get(root);
                    // change the a pathname to the root url (the ng url must be the same as the sf url)
                    element[0].pathname = rootState.url + '/';
                    // unbind click from origin uiSref
                    element.unbind("click");
                }
            };

            if (ref.paramExpr) {
                // watch the expression
                scope.$watch(ref.paramExpr, function(newVal, oldVal) {
                    if (newVal !== params) update(newVal);
                }, true);
                params = scope.$eval(ref.paramExpr);
            }
            update();

            if (isForm) return;
        }
    };
}]);*/

/**
 * uiSrefSearch Directive
 * This directive add search params for a state
 */
/*sygeforApp.directive('uiSrefSearch', [function()
{
    var parseViewSearch = function (search, current) {
        parsed = search.replace(/\n/g, " ").match(/^\s*({.*})\s*$/);
        if (!parsed || parsed.length !== 2) throw new Error("Invalid search '" + search + "'");
        return { expr: parsed[1] || null };
    }

    return {
        restrict: 'A',
        replace: true,
        priority: 401,
        compile: function(element, attrs) {
            var search = parseViewSearch(attrs.uiSrefSearch);
            return function(scope, element, attrs)
            {
                var params = null;

                var update = function(newVal) {
                    if (newVal) params = newVal;
                    var query = [];
                    for(key in params) {
                        query.push(key + "=" + encodeURIComponent(angular.toJson(params[key])));
                    }
                    element[0].hash =  element[0].hash.replace(/\?.*$/g, "") + "?" + query.join("&");
                };

                scope.$watch(search.expr, function(newVal, oldVal) {
                    if (newVal !== params) update(newVal);
                }, true);
                params = scope.$eval(search.expr);
                update();
            };
        }
    }
}]);*/

