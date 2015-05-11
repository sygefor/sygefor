/**
 * Open a dialog
 */
sygeforApp.directive('dialogHref', ['$parse', '$dialog', '$timeout', function($parse, $dialog, $timeout)
{
    var parseHref = function (ref, current) {
        var preparsed = ref.match(/^\s*({[^}]*})\s*$/), parsed;
        if (preparsed) ref = current + '(' + preparsed[1] + ')';
        parsed = ref.replace(/\n/g, " ").match(/^([^(]+?)\s*(\((.*)\))?$/);
        if (!parsed || parsed.length !== 4) throw new Error("Invalid dialog ref '" + ref + "'");

        return { key: parsed[1], paramExpr: parsed[3] || null };
    }

    return {
        restrict: 'A',
        priority: -1,
        replace: true,
        compile: function(element, attrs) {
            var ref = parseHref(attrs.dialogHref);
            var parseExp = $parse(ref.paramExpr);
            return function(scope, element, attrs)
            {
                var params = parseExp(scope);
                attrs.$set('href', '#');
                element.bind("click", function(e) {
                    var button = e.which || e.button;
                    if ( !(button > 1 || e.ctrlKey || e.metaKey || e.shiftKey || element.attr('target')) ) {
                        // HACK: This is to allow ng-clicks to be processed before the transition is initiated:
                        var transition = $timeout(function() {
                            $dialog.open(ref.key, params);
                        });

                        e.preventDefault();
                        e.preventDefault = function() {
                            $timeout.cancel(transition);
                        };
                    }
                });
            };
        }
    }
}]);
