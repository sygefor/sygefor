/**
 * SessionDetailViewController
 */
sygeforApp.controller('SessionDetailViewController', ['$scope', '$taxonomy', '$dialog', '$utils', '$user', '$state', '$window','search', 'data', '$timeout', function($scope, $taxonomy, $dialog, $utils, $user, $state, $window, search, data, $timeout)
{
    $scope.session = data.session;
    $scope.$utils = $utils;
    $scope.form = data.form ? data.form : false;

    /**
     * Get the public count
     *
     * @returns int
     */
    $scope.getTotal = function () {
        var total = 0;
        for (var i = 0; i < $scope.session.participantsSummaries.length; i++) {
            total += $scope.session.participantsSummaries[i].count;
        }
        return total;
    };

    /**
     * @param data
     */
    $scope.onSuccess = function(data) {
        $scope.session = data.session;
	    $scope.updateActiveItem($scope.session);
    };

    /**
     * promote
     */
    $scope.promote = function (value) {
        $scope.form.children.promote.checked = !!value;
        $scope.form.submit();
    };

    /**
     * Get nbr of email from entityEmails controller
     */
    $scope.$on('nbrEmails', function(event, value) {
        $scope.session.messages = { length: value };
    });

    /**
     * delete
     */
    $scope.delete = function (){
        $dialog.open('session.delete', {session: $scope.session}).then(function() {
            $state.go('session.table', {training: $scope.session.training.id}, {reload:true});
        });
    };

    /**
     * duplicate
     */
    $scope.duplicate = function() {
        $dialog.open('session.duplicate', {session: $scope.session}).then(function(result){
            $state.go('session.detail.view', {id: result.id}, {reload:true});
        });
    };

    $scope.getEvaluationSynthesis = function() {
        $dialog.open('session.evaluationSynthesis', {session: $scope.session});
    };

    /*
     * Request and download balance sheet
     */
    $scope.getEvaluationSheet = function () {
        var url = Routing.generate('session.evaluations', {id: $scope.session.id});
        $window.location = url;
    };

    $scope.onLinkCopy = function() {
        var link = angular.element(document.querySelector('#session-link'));
        link.fadeOut();
        $timeout(function() {
            link.fadeIn();
        });
    };
}]);
