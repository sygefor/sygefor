sygeforApp.controller('InstitutionDetailViewController', ['$scope', '$taxonomy', '$dialog', '$http', '$window', '$user', '$state', 'search', 'data', function($scope, $taxonomy, $dialog, $http, $window, $user, $state, search, data) {

    $scope.institution = data.institution;
    $scope.form = data.form ? data.form : false;
    $scope.$moment = moment;
    $scope.$user = $user;

    $scope.onSuccess = function(data) {
        $scope.displayNewTrainingCorrespondentForm = false;
	    $scope.institution = data.institution;
	    $scope.updateActiveItem($scope.institution);
    };

    /**
     * Change Organization
     */
    $scope.changeOrganization = function () {
        $dialog.open('institution.changeOrg', {institution: $scope.institution}).then(function(data) {
            $scope.institution = data.institution;
        });
    };

    $scope.isCorrespondentNotEmpty = function(form) {
        var keys = ['lastName', 'email'];

        for (var i in keys) {
            if (typeof form.children[keys[i]].value !== "undefined" && form.children[keys[i]].value !== "") {
                console.log(typeof form.children[keys[i]].value);
                return true;
            }
        }

        return false;
    };

    /**
     * Delete the manager
     */
    $scope.deleteManager = function (manager) {
        $dialog.open('institution.removeManager', {institution: $scope.institution, manager: manager}).then(function (data) {
            $scope.form = data.form;
            $scope.institution = data.institution;
        });
    };

    /**
     * Delete the institution
     */
    $scope.delete = function () {
        $http.get(Routing.generate('institution.remove', {id : $scope.institution.id})).then(function (data) {
            $dialog.open('institution.delete', {institution: $scope.institution, institutionTrainees: data.data.institutionTrainees, form: data.data.form}).then(function () {
                $state.go('institution.table', null, { reload:true });
            });
        });
    };
}]);
