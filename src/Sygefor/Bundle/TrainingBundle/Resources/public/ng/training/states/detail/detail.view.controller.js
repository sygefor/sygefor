/**
 * Created by maxime on 15/07/14.
 */
sygeforApp.controller('TrainingDetailViewController', ['$scope', '$taxonomy', '$trainingBundle', '$dialog', '$http', '$window', '$user', '$state', 'search', 'data', function($scope, $taxonomy, $trainingBundle, $dialog, $http, $window, $user, $state, search, data) {
    $scope.training = data.training;
    $scope.form = data.form ? data.form : false;
    $scope.$moment = moment;
    $scope.$trainingBundle = $trainingBundle;

    // put the first session in global object
    if($scope.training.session) {
        $scope.session = $scope.training.session;
        $scope.$watch("training.session", function(session) {
            $scope.session = session;
        });
    }

    /**
     * Unset a form children
     * @param key
     */
    $scope.unset = function (key) {
        delete $scope.form.children[key];
    };

    /**
     *
     * @param data
     */
    $scope.onSuccess = function(data) {
        $scope.training = data.training;
	    $scope.updateActiveItem($scope.training, 'training');
    }

    /**
     * promote (single session training)
     */
    $scope.promote = function (value) {
        $scope.form.children.session.children.promote.checked = !!value;
        $scope.form.submit();
    }

    /**
     * calls remove material modal and updates material list
     * @param material
     */
    $scope.removeMaterial = function (material) {
        return $dialog.open('material.remove', {material: material}).then(function() {
            for (var i = 0 ; $scope.training.materials ; i ++) {
                if ($scope.training.materials[i].id === material.id) {
                    $scope.training.materials.splice(i, 1);
                    break;
                }
            }
        });
    }

    /**
     * Choose cloned training type and then fill-in specific training type required fields if needed
     */
    $scope.duplicate = function () {
        $dialog.open('training.choosetypeduplicate', {training: $scope.training}).then(function (result) {
            $dialog.open('training.duplicate', {training: $scope.training, type: result.type}).then(function (result) {
                $state.go('training.detail.view', {id: result.id}, { reload: true });
            });
        });
    }

    /**
     * delete
     */
    $scope.delete = function () {
        $dialog.open('training.delete', {training: $scope.training}).then(function () {
            $state.go('training.table', null, { reload:true });
        });
    }

    /**
     * Add a session
     */
    $scope.addSession = function () {
        $dialog.open('session.create', {training: $scope.training.id}).then(function(data) {
            $scope.training.sessions.push(data.session);
        });
    }

    /**
     * Add material modal
     */
    $scope.addMaterial = function () {
        $dialog.open('training.material.add', {
            training: $scope.training,
            removeCallback: $scope.removeMaterial,
            downloadCallback: $scope.getMaterial,
            addCallback: $scope.addToMaterialList
        });
    }

    /**
     * calls callback
     * @param element
     */
    $scope.addToMaterialList = function(element) {
        $scope.training.materials.push(element);
    }

    /**
     * download material
     * @param material
     */
    $scope.getMaterial = function (material) {
        var url = Routing.generate('material.get', {id: material.id});
        $window.location = url;
    }

    /**
     * Request and download balance sheet
     */
    $scope.getBalanceSheet = function () {
        var url = Routing.generate('training.balancesheet', {id: $scope.training.id});
        $window.location = url;
    }


}]);
