/**
 * CoreBundle
 */
sygeforApp.config(["$listStateProvider", "$dialogProvider", "$widgetProvider", function($listStateProvider, $dialogProvider, $widgetProvider) {

    // trainee states
    $listStateProvider.state('trainee', {
        url: "/trainee?q",
        abstract: true,
        templateUrl: "list.html",
        controller:"TraineeListController",
        breadcrumb: [
            { label: "Publics", sref: "trainee.table" }
        ],
        resolve: {
            search: function ($searchFactory, $stateParams, $user) {
                var search = $searchFactory('trainee.search');
                search.query.sorts = {'lastName.source': 'asc'};
                search.query.filters['organization.name.source'] = $user.organization.name;
                search.extendQueryFromJson($stateParams.q);
                return search.search().then(function() { return search; });
            }
        },
        states: {
            table: {
                url: "",
                icon: "fa-bars",
                label: "Tableau",
                weight: 0,
                controller: 'ListTableController',
                templateUrl: "trainee/states/table/table.html"
            },
            detail: {
                url: "/detail",
                icon: "fa-eye",
                label: "Liste détaillée",
                weight: 1,
                templateUrl: "states/detail/detail.html",
                controller: 'ListDetailController',
                data:{
                    resultTemplateUrl: "trainee/states/detail/result.html"
                },
                states: {
                    view: {
                        url: "/:id",
                        templateUrl: "trainee/states/detail/trainee.html",
                        controller: 'TraineeDetailViewController',
                        resolve: {
                            data: function($http, $stateParams) {
                                var url = Routing.generate('trainee.view', {id: $stateParams.id});
                                return $http({method: 'GET', url: url}).then (function (data) { return data.data; });
                            }
                        },
                        breadcrumb: {
                            label: "{{ data.trainee.fullName }}"
                        }
                    }
                }
            }
        }
    });

    /**
     * DIALOGS
     */
    $dialogProvider.dialog('trainee.create', /* @ngInject */ {
        templateUrl: 'trainee/dialogs/create.html',
        controller: function($scope, $modalInstance, $dialogParams, $state, $http, form, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.form = form;
            $scope.onSuccess = function(data) {
                growl.addSuccessMessage("Le stagiaire a bien été créé.");
                $scope.dialog.close(data);
            };
        },
        resolve:{
            form: function ($http){
                return $http.get(Routing.generate('trainee.create')).then(function (response) {
                    return response.data.form;
                });
            }
        }
    });

    /**
     * trainee deletion modal window
     */
    $dialogProvider.dialog('trainee.delete', /* @ngInject */ {
        templateUrl: 'trainee/dialogs/delete.html',
        controller: function($scope, $modalInstance, $dialogParams, $state, $http, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.ok = function() {
                var url = Routing.generate('trainee.delete', {id: $dialogParams.trainee.id});
                $http.post(url).then(function (response){
                    growl.addSuccessMessage("Le stagiaire a bien été supprimé.");
                    $scope.dialog.close(response.data);
                });
            };
        }

    });

    /**
     * trainee merge modal window
     */
    $dialogProvider.dialog("trainee.merge", /* @ngInject */ {
        controller: 'TraineeMerge',
        templateUrl: 'trainee/batch/traineeMerge/traineeMerge.html',
        size: 'lg',
        resolve: {
            config: function ($http, $dialogParams) {
                var url = Routing.generate('sygefor_core.batch_operation.modal_config', {service: 'sygefor_core.batch.trainee_merge'});
                return $http.get(url).then(function (response) {
                    return response.data;
                });
            },
            trainees: function($dialogParams, $entityManager, $q) {
                var trainees = [];
                angular.forEach ($dialogParams.items, function(item){
                    this.push ($entityManager('SygeforCoreBundle:AbstractTrainee').find(item));
                }, trainees);
                return $q.all(trainees).then(function(results){return results;});
            }
        }
    });

    /**
     * trainee change password modal window
     */
    $dialogProvider.dialog('trainee.changePwd', /* @ngInject */ {
        templateUrl: 'trainee/dialogs/change-password.html',
        controller: function($scope, $modalInstance, $dialogParams, $state, $http, form, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.form = form;
            $scope.onSuccess = function(response) {
                growl.addSuccessMessage("Le mot de passe a bien été changé.");
                $scope.dialog.close(response);
            };
        },
        resolve: {
            form: function ($http, $dialogParams){
                return $http.get(Routing.generate('trainee.changepwd', {id: $dialogParams.trainee.id })).then(function(response) {
                    return response.data.form;
                });
            }
        }

    });

    /**
     * trainee change organization modal window
     */
    $dialogProvider.dialog('trainee.changeOrg', /* @ngInject */ {
        templateUrl: 'trainee/dialogs/change-organization.html',
        controller: function($scope, $modalInstance, $dialogParams, $state, $http, form, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.form = form;
            $scope.onSuccess = function(response) {
                growl.addSuccessMessage("Le stagiaire a bien changé de centre de référence.");
                $scope.dialog.close(response);
            };
        },
        resolve: {
            form: function ($http, $dialogParams){
                return $http.get(Routing.generate('trainee.changeorg', {id: $dialogParams.trainee.id })).then(function(response) {
                    return response.data.form;
                });
            }
        }

    });

    /**
     * ignore a trainee duplicate
     */
    $dialogProvider.dialog('trainee.ignoreDuplicate', /* @ngInject */ {
        templateUrl: 'trainee/dialogs/ignore-duplicate.html',
        controller: function($scope, $modalInstance, $dialogParams, $state, $http, form, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.form = form;
            $scope.onSuccess = function(response) {
                growl.addSuccessMessage("Le statut du doublon a bien été mis à jour.");
                $scope.dialog.close(response);
            };
        },
        resolve: {
            form: function ($http, $dialogParams){
                return $http.get(Routing.generate('trainee.changeduplicateignorance', {id: $dialogParams.duplicate.id })).then(function(response) {
                    return response.data.form;
                });
            }
        }
    });

    /**
     * change trainee activation modal window
     */
    $dialogProvider.dialog('trainee.toggleActivation', /* @ngInject */ {
        templateUrl: 'trainee/dialogs/activation.html',
        controller: function($scope, $modalInstance, $dialogParams, $state, $http, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.ok = function() {
                var url = Routing.generate('trainee.toggleActivation', {id: $dialogParams.trainee.id});
                $http.post(url).then(function (response){
                    growl.addSuccessMessage("Le stagiaire a bien été mis à jour.");
                    $scope.dialog.close(response.data);
                });
            };
        }
    });

    /**
     * WIDGETS
     */
    var date = new Date();
    date.setMonth(date.getMonth() - 2);
    $widgetProvider.widget("trainee", /* @ngInject */ {
        controller: 'WidgetListController',
        templateUrl: 'trainee/widget/trainee.html',
        options: function($user, $filter) {
            return {
                route: 'trainee.search',
                rights: ['sygefor_core.access_right.trainee.own.view', 'sygefor_core.access_right.trainee.all.view'],
                state: 'trainee.table',
                title: 'Derniers stagiaires inscrits',
                size: 10,
                filters:{
                    'organization.name.source': $user.organization.name,
                    "createdAt": {
                        "type": "range",
                        "gte": $filter('date')(date, 'yyyy-MM-dd', 'Europe/Paris')
                    }
                },
                sorts: {'createdAt': 'desc'}
            }
        }
    });
}]);
