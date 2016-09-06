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

    /*
     * Manage session materials
     */

    /**
     * Add material modal
     */
    $scope.addMaterial = function () {
        $dialog.open('session.material.add', {
            session: $scope.session,
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
        $scope.session.materials.push(element);
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
     * calls remove material modal and updates material list
     * @param material
     */
    $scope.removeMaterial = function (material) {
        return $dialog.open('session.material.remove', {material: material}).then(function() {
            for (var i = 0 ; $scope.session.materials ; i++) {
                if ($scope.session.materials[i].id === material.id) {
                    $scope.session.materials.splice(i, 1);
                    break;
                }
            }
        });
    }
}]);
