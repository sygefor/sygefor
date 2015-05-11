/**
 * SessionDetailViewController
 */
sygeforApp.controller('SessionDetailViewController', ['$scope', '$taxonomy', '$dialog', '$trainingBundle', '$user', '$state', '$window','search', 'data', function($scope, $taxonomy, $dialog, $trainingBundle, $user, $state, $window, search, data) {
    $scope.session = data.session;
    $scope.$trainingBundle = $trainingBundle;
    $scope.form = data.form ? data.form : false;

    /**
     * @param data
     */
    $scope.onSuccess = function(data) {
        $scope.session = data.session;
	    $scope.updateActiveItem($scope.session);
    }

    /**
     * promote
     */
    $scope.promote = function (value) {
        $scope.form.children.promote.checked = !!value;
        $scope.form.submit();
    }

    /**
     * delete
     */
    $scope.delete = function (){
        $dialog.open('session.delete', {session: $scope.session}).then(function() {
            $state.go('session.table', {training: $scope.session.training.id}, {reload:true});
        });

    }

    /**
     * duplicate
     */
    $scope.duplicate = function() {
        $dialog.open('session.duplicate', {session: $scope.session}).then(function(result){
            $state.go('session.detail.view', {id: result.id}, {reload:true});
        });
    }

    /*
    * Request and download balance sheet
    */
    $scope.getEvaluationSheet = function () {
        var url = Routing.generate ('session.evaluations', {id: $scope.session.id});
        $window.location = url;
    }

}]);
