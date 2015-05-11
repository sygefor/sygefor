/**
 * InscriptionDetailViewController
 */
sygeforApp.controller('InscriptionDetailViewController', ['$scope', '$state', '$trainingBundle', '$dialog', 'data', 'inscriptionStatusList', 'presenceStatusList', function($scope, $state, $trainingBundle, $dialog, data, inscriptionStatusList, presenceStatusList) {
    $scope.inscription = data.inscription;
    $scope.form = data.form ? data.form : false;
    $scope.$trainingBundle = $trainingBundle;
    $scope.$moment = moment;

    $scope.inscriptionStatus = inscriptionStatusList;
    $scope.presenceStatus = presenceStatusList;


    /**
     * Open the inscription status dialog
     *
     * @param inscription
     * @param status
     * @returns {promise|*|promise|promise|promise|promise}
     */
    $scope.updateInscriptionStatus = function(status) {
        return $dialog.open('inscription.changeStatus', {
            items: [$scope.inscription.id],
            inscriptionStatus: status
        }).then(function() {
            $scope.inscription.inscriptionStatus = status;
	        $scope.updateActiveItem($scope.inscription);
        });
    }

    /**
     * Open the presence status dialog
     *
     * @param inscription
     * @param status
     * @returns {promise|*|promise|promise|promise|promise}
     */
    $scope.updatePresenceStatus = function(status) {
        return $dialog.open('inscription.changeStatus', {
            items: [$scope.inscription.id],
            presenceStatus: status
        }).then(function() {
            $scope.inscription.presenceStatus = status;
	        $scope.updateActiveItem($scope.inscription);
        });
    }

    /**
     * Delete
     */
    $scope.delete = function() {
        $dialog.open('inscription.delete', {id: $scope.inscription.id}).then(function() {
            $state.go('inscription.table', {session: $scope.inscription.session.id}, { reload:true });
        });
    }

}]);


