/**
 * Generate href or form action based on a symfony2 route
 */
sygeforApp.directive('sfHref', ['$parse', function($parse)
{
    var parseSfRoute = function (ref, current) {
        var preparsed = ref.match(/^\s*({[^}]*})\s*$/), parsed;
        if (preparsed) ref = current + '(' + preparsed[1] + ')';
        parsed = ref.replace(/\n/g, " ").match(/^([^(]+?)\s*(\((.*)\))?$/);
        if (!parsed || parsed.length !== 4) throw new Error("Invalid view ref '" + ref + "'");

        return { route: parsed[1], paramExpr: parsed[3] || null };
    }

    return {
        restrict: 'A',
        priority: -1,
        replace: true,
        compile: function(element, attrs) {
            var ref = parseSfRoute(attrs.sfHref);
            var parseExp = $parse(ref.paramExpr);
            return function(scope, element, attrs)
            {
                var params = parseExp(scope);
                var isForm = element[0].nodeName === "FORM";
                var attr = isForm ? "action" : "href";
                var href = element[0][attr];
                var newAttr = Routing.generate(ref.route, params);
                element[0][attr] = newAttr;
                attrs[attr] = newAttr;
            };
        }
    }
}]);
