/**
 * directive : reportListing
 */
sygeforApp.directive("reportListing", [function () {
    return {
        restrict: "A",
        scope: {
            "data": "=",
            "caption": "@",
            "type": "@"
        },
        link: function(scope) {

        },
        templateUrl: function(elem,attrs) {
            var template = attrs.template ? 'report-listing-' + attrs.template : 'report-listing';
            return 'activityreportbundle/directives/' + template + '.html';
        }
    };

}]);

