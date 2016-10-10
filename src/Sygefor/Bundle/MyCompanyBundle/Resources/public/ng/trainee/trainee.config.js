/**
 * TraineeBundle
 */
sygeforApp.config(["$listStateProvider", "$dialogProvider", "$widgetProvider", function($listStateProvider, $dialogProvider, $widgetProvider) {

    // trainee states
    $listStateProvider.state('trainee', {
        url: "/trainee?q",
        abstract: true,
        templateUrl: "corebundle/list.html",
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
                templateUrl: "mycompanybundle/trainee/states/table/table.html"
            },
            detail: {
                url: "/detail",
                icon: "fa-eye",
                label: "Liste détaillée",
                weight: 1,
                templateUrl: "corebundle/states/detail/detail.html",
                controller: 'ListDetailController',
                data:{
                    resultTemplateUrl: "mycompanybundle/trainee/states/detail/result.html"
                },
                states: {
                    view: {
                        url: "/:id",
                        templateUrl: "mycompanybundle/trainee/states/detail/trainee.html",
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
        templateUrl: 'mycompanybundle/trainee/dialogs/create.html',
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
        templateUrl: 'mycompanybundle/trainee/dialogs/delete.html',
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
     * trainee change password modal window
     */
    $dialogProvider.dialog('trainee.changePwd', /* @ngInject */ {
        templateUrl: 'mycompanybundle/trainee/dialogs/change-password.html',
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
        templateUrl: 'mycompanybundle/trainee/dialogs/change-organization.html',
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
     * change trainee activation modal window
     */
    $dialogProvider.dialog('trainee.toggleActivation', /* @ngInject */ {
        templateUrl: 'mycompanybundle/trainee/dialogs/activation.html',
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
        templateUrl: 'mycompanybundle/trainee/widget/trainee.html',
        options: function($user, $filter) {
            return {
                route: 'trainee.search',
                rights: ['sygefor_trainee.rights.trainee.own.view', 'sygefor_trainee.rights.trainee.all.view'],
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
