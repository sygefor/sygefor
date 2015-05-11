/**
 * Widget directive
 */
sygeforApp.directive('widget', ['$widget', '$templateFactory', '$resolve', '$controller', '$compile', '$injector', '$user',function($widget, $templateFactory, $resolve, $controller, $compile, $injector, $user) {
    return {
        restrict: 'A',
        replace: true,
        scope:{
            widgetOptions: '='
        },
        link: function(scope, element, attrs) {
            var body = angular.element('[widget-body]', element);
            var initial = body.html();

            /**
             * update the widget content
             */
            var updateContent = function()
            {
                var widget = $widget.get(attrs.widget);
                var options = widget.options;
                if(typeof options == "function" || options instanceof Array) {
                    options = $injector.invoke(options);
                }

                //checking user right. If some rights are defined for widget, we need to check them for user

                if (typeof options.rights != 'undefined' && (options.rights.length > 0)  ) {
                    var hasRight = false;
                    for(var right in options.rights) {
                        if($user.hasAccessRight(options.rights[right])){hasRight = true;}
                    }
                    //if no right, user
                    if (!hasRight) {
                        element.html('');
                        return 0;
                    }
                }

                // extends the base filters
                var filters = angular.extend({}, options.filters, scope.widgetOptions ? scope.widgetOptions.filters : {});

                // extends the base options object with the instance one
                var options = angular.extend({}, options, scope.widgetOptions ? scope.widgetOptions : {});
                options.filters = filters;

                // populate the scope
                scope.options = options;

                body.html(initial);

                // injectables
                var injectables = widget.resolve ? widget.resolve : {};
                injectables.$template = [function () {
                    return $templateFactory.fromConfig(widget);
                }];

                // resolve
                $resolve.resolve(injectables, {options: options}).then(function(locals) {
                    locals.$scope = scope;
                    body.html(locals.$template);
                    var link = $compile(body.contents());
                    $controller(widget.controller, locals);
                    link(scope);
                });
            }

            //scope.$watch(attrs.options, updateContent);
            updateContent();
        },
        controller: function($scope) {
            this.getOptions = function() {
                return $scope.options;
            }
        },
        templateUrl: 'corebundle/dashboard/widget.html'
    }
}]);
