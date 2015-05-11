/**
 * directive : sfHref
 */
sygeforApp.directive('uiBreadcrumb', ['$state', '$injector', '$compile', function($state, $injector, $compile) {
    return {
        restrict: 'A',
        compile: function(element, attrs) {
            var initialHtml = element.html();

            /**
             * Extract a bredcrumb from a state
             * @param state
             * @returns {Array}
             */
            function extractBreadcrumb(state, scope) {
                var breadcrumb = [];

                if(state.self.breadcrumb !== undefined) {
                    var options = state.self.breadcrumb;
                    compileBreadcrumbPart(state, options, scope, breadcrumb);
                }

                if(state.parent) {
                    var parts = extractBreadcrumb(state.parent, scope);
                    breadcrumb.unshift.apply(breadcrumb, parts);
                }
                return breadcrumb;
            }

            /**
             * Extract a breadcrumb from a state
             * @param state
             * @param options
             * @param scope
             * @param breadcrumb
             * @returns {Array}
             */
            function compileBreadcrumbPart(state, options, scope, breadcrumb) {
                var html = null;

                if(typeof options == "function") {
                    options = $injector.invoke(options, null, state.locals.globals);
                    if(!options) {
                        return null;
                    }
                }

                if(Array.isArray(options)) {
                    for(var i=0; i<options.length; i++) {
                        compileBreadcrumbPart(state, options[i], scope, breadcrumb);
                    }
                    return;
                }

                if(typeof options == "string") {
                    html = options;
                } else {
                    var label = options.label;
                    var sref = options.sref ? options.sref : state.self.name;
                    var options = options.opts ? options.opts : {};
                    options = angular.extend({inherit: false}, options);
                    html = '<a ui-sref="' + sref + '" ui-sref-opts=\'' + angular.toJson(options) + '\'>' + label + '</a>';
                }

                if(!html) {
                    return null;
                }

                var ele = angular.element('<li></li>');
                ele.html(html);

                var newScope = scope.$new();
                angular.extend(newScope, state.locals.globals);
                $compile(ele.contents())(newScope);

                breadcrumb.push(ele);
            }

            /**
             * Link
             */
            return function(scope, element, attrs)
            {
                scope.$on("$stateChangeSuccess", function() {
                    var breadcrumb = extractBreadcrumb($state.$current, scope);
                    element.html(initialHtml);
                    for(var i in breadcrumb) {
                        element.append(breadcrumb[i]);
                    }
                    $("li", element).removeClass("active");
                    $("li:last", element).addClass("active");
                });

                scope.$on("$breadcrumbUpdate", function() {
                    var breadcrumb = extractBreadcrumb($state.$current, scope);
                    element.html(initialHtml);
                    for(var i in breadcrumb) {
                        element.append(breadcrumb[i]);
                    }
                    $("li", element).removeClass("active");
                    $("li:last", element).addClass("active");
                })
            };
        }
    };
}]);
