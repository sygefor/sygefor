/**
 * Include a trainers list block for a given session
 *
 * Usage : <div resume-trainer-block="session"></div>
 */
sygeforApp.directive('resumeTrainerBlock', [function() {
    return {
        restrict: 'EA',
        scope: {
            trainer: '=resumeTrainerBlock',
            year: '=year',
            status: '=status'
        },
        templateUrl: 'mycompanybundle/trainer/directives/resumeTrainer.block.html'
    }
}]);
