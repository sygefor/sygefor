sygeforApp.controller('TraineeDetailViewController', ['$scope', '$taxonomy', '$dialog', '$http', '$window', '$user', '$state', 'search', 'data', '$utils', function($scope, $taxonomy, $dialog, $http, $window, $user, $state, search, data, $utils) {
    $scope.trainee = data.trainee;
    $scope.form = data.form ? data.form : false;
    $scope.$moment = moment;
    $scope.$user = $user;
    $scope.$utils = $utils;

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
     * Get nbr of email from entityEmails controller
     */
    $scope.$on('nbrEmails', function(event, value) {
       $scope.trainee.messages = { length: value };
    });

    $scope.toggleActivation = function () {
        $dialog.open('trainee.toggleActivation', {trainee: $scope.trainee}).then(function (data) {
            $scope.trainee = data.trainee;

            angular.forEach($scope.search.result.items, function(result) {
                if ($scope.trainee.id == result.id) {
                    result.isActive = $scope.trainee.isActive;
                    result.class = $scope.trainee.isActive ? '' : 'alert-danger';
                }
            });
        });
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
            $scope.trainee = data.form.value;
            $scope.form = data.form.form;
        });
    };

    /**
     * Change ignore duplicate
     */
    $scope.ignoreDuplicate = function (duplicate) {
        $dialog.open('trainee.ignoreDuplicate', {duplicate: duplicate}).then(function(data) {
            duplicate.ignored = data.form.value.ignored;
        });
    };
}]);
