/**
 * Include a training template based on the type and the $utils service
 * Usage : <div training-template="view" type="training.type" default="/bundles/sygefortraining/ng/training/states/detail/training.html"></div>
 */
sygeforApp.directive('trainingTemplate', ['$utils', '$http', '$templateCache', '$compile', function($utils, $http, $templateCache, $compile) {
    return {
        restrict: 'EA',
        link: function(scope, element, attrs) {

            /**
             * Update the template
             * @param training
             */
            var update = function(type) {
                var key = attrs.trainingTemplate;

                // determine the template
                var type = $utils.getType(type),
                    templateUrl = attrs.default;
                if(type && type.templates && type.templates[key]) {
                    templateUrl = type.templates[key];
                }

                // get the template
                template = $http.get(templateUrl, {cache: $templateCache}).
                    success(function(data, status, headers, config) {
                        element.html(data);
                        $compile(element.contents())(scope);
                    }).
                    error(function(data, status, headers, config) {
                        element.html("<div>template not found : " + templateUrl + "</div>");
                    });
            }

            /**
             * watch attr
             */
            scope.$watch(attrs.type, function(type) {
                if(type) {
                    update(type);
                }
            });

        }
    }
}]);
