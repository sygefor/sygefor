/**
 * Include a inscription table block for a given session
 * Usage : <span registration-label="session"></span>
 */
sygeforApp.directive('registrationLabel', [function() {
    return {
        restrict: 'EA',
        scope: {
            session: '=registrationLabel'
        },
        link: function(scope, element, attrs) {
            scope.$moment = moment;
            scope.class = {
                'label-lg': (typeof attrs.large !== "undefined")
            }
        },
        templateUrl: 'training/session/directives/registration-label.html'
    }
}]);
