/**
 * Include a inscription table block for a given session
 * Usage : <div inscriptions-block="session"></div>
 */
sygeforApp.directive('participantsSummaryBlock', [function() {
    return {
        restrict: 'EA',
        scope: {
            session: '=participantsSummaryBlock',
            form: '=blockForm'
        },
        link: function(scope, element, attrs) {

        },
        controller: 'SessionParticipantsSummaryController',
        templateUrl: 'trainingbundle/session/directives/participants-summary.block.html'
    }
}]);
