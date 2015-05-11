/**
 * Search Toolbar Directive
 */
sygeforApp.directive('listToolbar', ['$timeout', function($timeout) {
    return {
        restrict: 'A',
        replace: true,
        transclude: true,
        link: function(scope, element, attrs)
        {

            /**
             * tells wether an add operation is available
             * @param a
             * @returns {*|available}
             */
            scope.addOperationAvailable = function (a) {
                if(typeof a.available == "function") {
                    return a.available();
                } else if(typeof a.available != "undefined") {
                    return a.available;
                } else {
                    return true;
                }
            };

            scope.batchOperationAvailable = function(a) {
                if(typeof a.available == "function") {
                    return a.available();
                } else if(typeof a.available != "undefined") {
                    return a.available;
                } else {
                    return true;
                }
            }
        },
        templateUrl: "listbundle/toolbar/list-toolbar.html"
    }
}]);
