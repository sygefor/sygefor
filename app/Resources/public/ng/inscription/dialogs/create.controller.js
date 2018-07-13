/**
 * Controller for Inscription creation
 */
sygeforApp.controller('InscriptionCreate', ['$scope', '$modalInstance', '$dialogParams', '$dialog','$state', '$user', '$http', 'form', 'growl', function($scope, $modalInstance, $dialogParams, $dialog, $state, $user, $http, form, growl)
{
    $scope.dialog = $modalInstance;
    $scope.dialog.params = angular.copy($dialogParams);
    $scope.session = $scope.dialog.params.session;
    $scope.form = form;

    /**
     * Sets the trainee in the form object. Used by typeahead.
     * @param item
     */
    $scope.setTrainee = function (item) {
        $scope.form.children.trainee.value = item.value;
    };

    $scope.$watch('selectedTrainee', function (value){
        $scope.form.children.trainee.value = value ? value.value : '';
    });

    /**
     * gets list of trainees that match prefix from server
     * @param pref the prefix that was typed by user
     */
    $scope.getTraineeList = function (pref) {
        var url = Routing.generate('trainee.search');

        return $http.post(url, {
            "query": {
                "match": {
                    "fullName.autocomplete": {
                        "query":    angular.lowercase(pref)
                    }
                }
            }
        }
        ).then(function (res) {
            var adresses = [];
            angular.forEach (res.data.items, function (item) {
                adresses.push ({label: item.fullName, value:item.id, organization: (item.organization.name) ? item.organization.name : ''})
            });

            return adresses;
        });
    };

    /**
     * @param data
     */
    $scope.onSuccess = function(data) {
        growl.addSuccessMessage("L'inscription a bien été créée.");
        $scope.dialog.close(data);
    };


    $scope.userCanAddTrainee = function() {
        return $user.hasAccessRight('sygefor_core.access_right.trainee.own.create') || $user.hasAccessRight('sygefor_core.access_right.trainee.all.create');
    };

    /**
     * manages user creation dialog and its return.
     **/
    $scope.createUser = function () {
        $dialog.open('trainee.create').then(function (result){
            var trainee = {
                label: result.trainee.fullName,
                value: result.trainee.id,
                organization: result.trainee.organization.name
            };
            $scope.selectedTrainee = trainee ;
            $scope.setTrainee(trainee);
        });
    }
}]);
