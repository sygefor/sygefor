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
                    $scope.session.trainers.push(data.trainer);
                });
            }

            /**
             * Remove an associated trainer
             */
            $scope.removeTrainer = function (trainer) {
                $dialog.open('trainer.remove', {session: $scope.session, trainer: trainer}).then(function (){
                    var index = $scope.session.trainers.indexOf(trainer);
                    if (index > -1) {
                        $scope.session.trainers.splice(index, 1);
                    }
                });
            }

        },
        templateUrl: 'trainingbundle/session/directives/trainers.block.html'
    }
}]);
