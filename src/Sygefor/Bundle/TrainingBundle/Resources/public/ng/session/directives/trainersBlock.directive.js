/**
 * Include a trainers list block for a given session
 *
 * Usage : <div trainers-block="session"></div>
 */
sygeforApp.directive('trainersBlock', [function() {
    return {
        restrict: 'EA',
        scope: {
            session: '=trainersBlock'
        },
        link: function(scope, element, attrs) {
            // custum empty message
            scope.emptyMsg = attrs.emptyMsg ?  attrs.emptyMsg : "Il n'y a aucun formateur associé à cette session.";
        },
        controller: function($scope, $dialog, $user, growl) {

            /**
             * Associate a new trainer
             */
            $scope.addTrainer = function () {
                $dialog.open('trainer.add', {session: $scope.session}).then(function (data){
                    $scope.session.participations.push(data.participation);
                });
            }

            /**
             * Remove an associated trainer
             */
            $scope.removeTrainer = function (participation) {
                $dialog.open('trainer.remove', {session: $scope.session, participation: participation}).then(function (){
                    var index = $scope.session.participations.indexOf(participation);
                    if (index > -1) {
                        $scope.session.participations.splice(index, 1);
                    }
                });
            }

        },
        templateUrl: 'trainingbundle/session/directives/trainers.block.html'
    }
}]);
