/**
 * Include a trainers list block for a given session
 *
 * Usage : <div resume-session-block="session"></div>
 */
sygeforApp.directive('resumeSessionBlock', [function() {
    return {
        restrict: 'EA',
        scope: {
            session: '=resumeSessionBlock'
        },
        templateUrl: 'training/session/directives/resumeSession.block.html'
    }
}]);
