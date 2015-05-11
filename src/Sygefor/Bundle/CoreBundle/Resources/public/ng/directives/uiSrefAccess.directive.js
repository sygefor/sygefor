/**
 * directive : uiSrefAccess
 */
sygeforApp.directive('uiSrefAccess', [function() {
    var parseStateRef = function(ref, current) {
        var preparsed = ref.match(/^\s*({[^}]*})\s*$/), parsed;
        if (preparsed) ref = current + '(' + preparsed[1] + ')';
        parsed = ref.replace(/\n/g, " ").match(/^([^(]+?)\s*(\((.*)\))?$/);
        if (!parsed || parsed.length !== 4) throw new Error("Invalid state ref '" + ref + "'");
        return { state: parsed[1], paramExpr: parsed[3] || null };
    }

    return {
        restrict: 'A',
        priority: 401,
        compile: function(element, attrs) {
            var ref = parseStateRef(attrs.uiSref);
            var params = null;
            //var parseExp = $parse(ref.paramExpr);
            return function(scope, element, attrs)
            {
                var update = function() {
                    var access = scope.$eval(attrs.uiSrefAccess);
                    if(!access) {
                        attrs.$set("href", null);
                        element.off("click");
                        element.addClass("inactive");
                        element.contents().unwrap().wrap('<span/>');
                    } /*else {
                        element.contents().unwrap().wrap('<a />');
                    }*/
                }
                if (ref.paramExpr) {
                    scope.$watch(ref.paramExpr, function(newVal, oldVal) {
                        if (newVal !== params) update(newVal);
                    }, true);
                    params = angular.copy(scope.$eval(ref.paramExpr));
                }
                scope.$watch(attrs.uiSrefAccess, function(newVal, oldVal) {
                    update();
                });
                update();
            };
        }
    }
}]);
