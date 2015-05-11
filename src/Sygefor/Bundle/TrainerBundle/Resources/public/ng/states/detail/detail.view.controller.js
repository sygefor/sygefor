sygeforApp.controller('TrainerDetailViewController', ['$scope', '$user', '$state', '$dialog', 'search', 'data', function($scope, $user, $state, $dialog, search, data) {
    $scope.trainer = data.trainer;
    $scope.form = data.form ? data.form : false;
    $scope.onSuccess = function(data) {
	    $scope.trainer = data.trainer;
	    $scope.updateActiveItem($scope.trainer);
    };

    /**
     * Delete
     */
    $scope.delete = function(){
        $dialog.open('trainer.delete', {trainer: $scope.trainer}).then(function (){
            $state.go('trainer.table', null, {reload: true});
        });
    }
}]);
