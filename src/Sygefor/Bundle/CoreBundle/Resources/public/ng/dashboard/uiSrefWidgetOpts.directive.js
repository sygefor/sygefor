/**
 * This directive add the current widget query options to the ui-sref link
 */
sygeforApp.directive('uiSrefWidgetOpts', ['$state', function($state) {
    var parseStateRef = function (ref, current) {
        var preparsed = ref.match(/^\s*({[^}]*})\s*$/), parsed;
        if (preparsed) ref = current + '(' + preparsed[1] + ')';
        parsed = ref.replace(/\n/g, " ").match(/^([^(]+?)\s*(\((.*)\))?$/);
        if (!parsed || parsed.length !== 4) throw new Error("Invalid state ref '" + ref + "'");
        return { state: parsed[1], paramExpr: parsed[3] || null };
    }
    return {
        restrict: 'A',
        priority: -5,
        require: '^widget',
        link: function(scope, element, attrs, widgetCtrl) {
            var options = widgetCtrl.getOptions();
            var q = {
                sorts: options.sorts ? options.sorts : {},
                filters: options.filters ? options.filters : {}
            };
            var ref = parseStateRef(attrs.uiSref, $state.current.name);
            var paramExpr = ref.paramExpr ? ref.paramExpr : '{}';
            paramExpr = paramExpr.replace(/\}$/, ", q: '" + angular.toJson(q) + "'}");
            attrs.uiSref = ref.state + '(' + paramExpr + ')';
        }
    }
}]);
