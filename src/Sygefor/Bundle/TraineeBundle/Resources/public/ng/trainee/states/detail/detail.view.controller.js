sygeforApp.controller('TraineeDetailViewController', ['$scope', '$taxonomy', '$dialog', '$http', '$window', '$user', '$state', 'search', 'data', function($scope, $taxonomy, $dialog, $http, $window, $user, $state, search, data) {
    $scope.trainee = data.trainee;
    $scope.form = data.form ? data.form : false;
    $scope.$moment = moment;
    $scope.$user = $user;
    $scope.onSuccess = function(data) {
	    $scope.trainee = data.trainee;
	    $scope.updateActiveItem($scope.trainee);
    };

    /**
     * Unset a form children
     * @param key
     */
    $scope.unset = function (key) {
        delete $scope.form.children[key];
    };

    /**
     * Delete the trainee
     */
    $scope.delete = function () {
        $dialog.open('trainee.delete', {trainee: $scope.trainee}).then(function (){
            $state.go('trainee.table', null, { reload:true });
        });
    };

    /**
     * Change the password
     */
    $scope.changePassword = function () {
        $dialog.open('trainee.changePwd', {trainee: $scope.trainee});
    };

    /**
     * Change Organization
     */
    $scope.changeOrganization = function () {
        $dialog.open('trainee.changeOrg', {trainee: $scope.trainee}).then(function(data) {
            $scope.form = data.form;
            $scope.trainee = data.form.value;
        });
    };

    /**
     * Change ignore duplicate
     * @todo refresh duplicate
     */
    $scope.ignoreDuplicate = function (duplicate) {
        $dialog.open('trainee.ignoreDuplicate', {duplicate: duplicate}).then(function(data) {
            duplicate.ignored = data.form.value.ignored;
        });
    };
}]);
