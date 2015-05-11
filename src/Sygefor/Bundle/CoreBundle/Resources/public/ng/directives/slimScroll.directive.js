/**
 * directive : sfHref
 */
sygeforApp.directive("slimScroll", ['$timeout', function ($timeout) {
    var options = {height: 'auto'};
    return {
        restrict: "A",
        link: function (scope, element, attrs) {
            // instance-specific options
            var opts = angular.extend({}, options, scope.$eval(attrs.slimScroll));
            $timeout(function() {
                angular.element(element).slimScroll(opts);
            });
        }
    };
}]);
