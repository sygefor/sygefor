/**
 * Form xeditable element
 */
sygeforApp.directive('xeditable', ['$timeout', function($timeout) {
    return {
        restrict: 'A',
        require: "ngModel",
        link: function(scope, element, attrs, ngModel) {
            var $element = angular.element(element);
            var options = {
                mode: 'inline',
                value: ngModel.$viewValue
            };

            if(attrs.update) {
                options.url = scope[attrs.update];
            }

            if(attrs.params) {
                options.params = scope[attrs.params];
            }

            if(attrs.options) {
                options.source = scope[attrs.options];
            }

            $timeout(function() {

                $element.editable(options);

                $element.on('save', function(e, params) {
                    ngModel.$setViewValue(params.newValue);
                    if(!scope.$$phase && !scope.$root.$$phase) {
                        scope.$apply();
                    }
                });

                attrs.$observe('ngModel', function(value){ // Got ng-model bind path here
                    scope.$watch(value,function(newValue) { // Watch given path for changes
                        $element.editable('option', 'value', newValue);
                    });
                });
            });
        }
    }
}]);
