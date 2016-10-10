/**
 * Include a inscription table block for a given session
 * Usage : <div inscriptions-block="session"></div>
 */
sygeforApp.directive('inscriptionsBlock', ['$dialog', '$filter', function($dialog, $filter) {
    return {
        restrict: 'EA',
        scope: {
            session: '=inscriptionsBlock'
        },
        link: function(scope, element, attrs) {
            // custum empty message
            scope.emptyMsg = attrs.emptyMsg ?  attrs.emptyMsg : "Il n'y a aucune inscription pour cette session.";
            scope.$dialog = $dialog;
        },
        controller: 'SessionInscriptionsController',
        templateUrl: 'mycompanybundle/training/session/directives/inscriptions.block.html'
    }
}]);

/**
 * Filter : byInscriptionStatus
 */
sygeforApp.filter('byInscriptionStatus', function () {
    return function (input, id) {
        //return input.map(function(o) { return o[property || 'name']; }).join(delimiter || ', ');
    };
});

/**
 * Filter : byPresenceStatus
 */
sygeforApp.filter('byPresenceStatus', function (id) {
    return function (input, id) {
        //return input.map(function(o) { return o[property || 'name']; }).join(delimiter || ', ');
    };
});
