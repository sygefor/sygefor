/**
 * Trainer Add Controller
 */
sygeforApp.controller('TrainerAddController', ['$scope', '$modalInstance', '$dialog', '$dialogParams', '$state', '$user', '$http', 'form', 'growl', function ($scope, $modalInstance, $dialog, $dialogParams, $state, $user, $http, form, growl) {
    $scope.dialog = $modalInstance;
    $scope.dialog.params = angular.copy($dialogParams);
    $scope.session = $dialogParams.session;
    $scope.form = form;

    /**
     * Sets the given trainer as the current selected trainer
     * @param item
     */
    $scope.setTrainer = function (item) {
        $scope.form.children.trainer.value = item.value;
    };

    $scope.$watch('selectedTrainer', function (value) {
        $scope.form.children.trainer.value = value ? value.value : '';
    });

    /**
     * calls the trainer creation modal
     */
    $scope.createTrainer = function () {
        $dialog.open('trainer.create').then(function (result) {
            var trainer = {
                label: result.trainer.fullName,
                value: result.trainer.id,
                organization: result.trainer.organization.name
            };

            $scope.selectedTrainer = trainer;
            $scope.setTrainer(trainer);
        });
    };

    /**
     * gets the list of trainers that corresponds to given prefix
     * @param pref
     * @returns {*|then|then}
     */
    $scope.getTrainerList = function (pref) {
        var url = Routing.generate('trainer.search');
        return $http.post(url, {
            "query": {
                "match": {
                    "fullName.autocomplete": {
                        "query":    angular.lowercase(pref)
                    }
                }
            }
        }).then(function (res) {
            var adresses = [];
            angular.forEach(res.data.items, function (item) {
                adresses.push({label: item.fullName, value: item.id, organization: (item.organization.name) ? item.organization.name : ''})
            });

            return adresses;
        });
    };

    /**
     *
     * @param data
     */
    $scope.onSuccess = function (data) {
        growl.addSuccessMessage("Le formateur a bien été ajouté à la session.");
        $scope.dialog.close(data);
    };
}]);
