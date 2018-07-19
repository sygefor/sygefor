/**
 * Created by maxime on 15/07/14.
 */
sygeforApp.controller('TrainingDetailViewController', ['$scope', '$taxonomy', '$utils', '$dialog', '$http', '$window', '$user', '$state', '$sce', 'search', 'data', function($scope, $taxonomy, $utils, $dialog, $http, $window, $user, $state, $sce, search, data) {
    $scope.training = data.training;
    $scope.form = data.form ? data.form : false;
    $scope.$moment = moment;

    // display as HTML
    $scope.training.description = $sce.trustAsHtml($scope.training.description);
    $scope.training.program = $sce.trustAsHtml($scope.training.program);
    $scope.training.teachingMethods = $sce.trustAsHtml($scope.training.teachingMethods);

    // put the first session in global object
    if($scope.training.session) {
        $scope.session = $scope.training.session;
        $scope.$watch("training.session", function(session) {
            $scope.session = session;
        });
    }

    /**
     * Find sessions without module for longTrainings
     * @returns {Array}
     */
    $scope.getSessionWithoutModule = function() {
        var sessionsWithoutModules = [];
        for (var keySession in $scope.training.sessions) {
            var found = false;
            for (var keyModule in $scope.training.modules) {
                for (var keyModuleSession in $scope.training.modules[keyModule].sessions) {
                    if ($scope.training.sessions[keySession].id === $scope.training.modules[keyModule].sessions[keyModuleSession].id) {
                        found = true;
                        break;
                    }
                }
            }
            if (!found) {
                sessionsWithoutModules.push($scope.training.sessions[keySession]);
            }
        }

        return sessionsWithoutModules;
    };
    if ($scope.training.modules) {
        $scope.sessionsWithoutModule = $scope.getSessionWithoutModule();
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

        // display as HTML
        $scope.training.description = $sce.trustAsHtml($scope.training.description);
        $scope.training.program = $sce.trustAsHtml($scope.training.program);
        $scope.training.teachingMethods = $sce.trustAsHtml($scope.training.teachingMethods);

	    $scope.updateActiveItem($scope.training, 'training');
    };

    /**
     * promote (single session training)
     */
    $scope.promote = function (value) {
        $scope.form.children.session.children.promote.checked = !!value;
        $scope.form.submit();
    };

    /**
     * Choose cloned training type and then fill-in specific training type required fields if needed
     */
    $scope.duplicate = function () {
        $dialog.open('training.choosetypeduplicate', {training: $scope.training}).then(function (result) {
            $dialog.open('training.duplicate', {training: $scope.training, type: result.type}).then(function (result) {
                $state.go('training.detail.view', {id: result.id}, { reload: true });
            });
        });
    };

    /**
     * delete
     */
    $scope.delete = function () {
        $dialog.open('training.delete', {training: $scope.training}).then(function () {
            $state.go('training.table', null, { reload:true });
        });
    };

    /**
     * Edit or delete a module
     * @param module
     */
    $scope.editModule = function(module) {
        $dialog.open('training.module.edit', {module: module}).then(function(data) {
            $scope.training.modules = data.modules;
        });
    };

    /**
     * Add a session
     * Retrieve new created module and session module for DOM update
     */
    $scope.addSession = function () {
        $dialog.open('session.create', {training: $scope.training}).then(function(data) {
            $scope.training.sessions.push(data.session);
            if ($scope.training.modules) {
                if ($scope.training.modules.length !== data.training.modules.length) {
                    for (var keyUpdatedModules in data.training.modules) {
                        var found = false;
                        for (var keyInitialModules in $scope.training.modules) {
                            if (data.training.modules[keyUpdatedModules].id === $scope.training.modules[keyInitialModules].id) {
                                found = true;
                                break;
                            }
                        }
                        if (!found) {
                            data.training.modules[keyUpdatedModules].sessions = [data.session];
                            $scope.training.modules.push(data.training.modules[keyUpdatedModules]);
                            break;
                        }
                    }
                }
                else if (data.session.module) {
                    var sessionModuleId = data.session.module.id;
                    for (var keyModule in $scope.training.modules) {
                        if ($scope.training.modules[keyModule].id === sessionModuleId) {
                            $scope.training.modules[keyModule].sessions.push(data.session);
                        }
                    }
                }
                $scope.sessionsWithoutModule = $scope.getSessionWithoutModule();
            }
        });
    };

    /**
     * download material
     * @param material
     */
    $scope.getMaterial = function (material) {
        var url = Routing.generate('material.get', {id: material.id});
        $window.location = url;
    };

    /**
     * Request and download balance sheet
     */
    $scope.getBalanceSheet = function () {
        var url = Routing.generate('training.balancesheet', {id: $scope.training.id});
        $window.location = url;
    };
}]);
