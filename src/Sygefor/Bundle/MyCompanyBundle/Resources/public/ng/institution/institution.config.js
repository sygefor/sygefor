/**
 * InstitutionBundle
 */
sygeforApp.config(["$listStateProvider", "$dialogProvider", function($listStateProvider, $dialogProvider) {

    // institution states
    $listStateProvider.state('institution', {
        url: "/institution?q",
        abstract: true,
        templateUrl: "list.html",
        controller:"InstitutionListController",
        breadcrumb: [
            { label: "Etablissements", sref: "institution.table" }
        ],
        resolve: {
            search: function ($searchFactory, $stateParams, $user) {
                var search = $searchFactory('institution.search');
                search.query.sorts = {'name.source': 'asc'};
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
                templateUrl: "mycompanybundle/institution/states/table/table.html"
            },
            detail: {
                url: "/detail",
                icon: "fa-eye",
                label: "Liste détaillée",
                weight: 1,
                templateUrl: "states/detail/detail.html",
                controller: 'ListDetailController',
                data:{
                    resultTemplateUrl: "mycompanybundle/institution/states/detail/result.html"
                },
                states: {
                    view: {
                        url: "/:id",
                        templateUrl: "mycompanybundle/institution/states/detail/institution.html",
                        controller: 'InstitutionDetailViewController',
                        resolve: {
                            data: function($http, $stateParams) {
                                var url = Routing.generate('institution.view', {id: $stateParams.id});
                                return $http({method: 'GET', url: url}).then (function (data) { return data.data; });
                            }
                        },
                        breadcrumb: {
                            label: "{{ data.institution.name }}"
                        }
                    }
                }
            }
        }
    });

    /**
     * DIALOGS
     */
    $dialogProvider.dialog('institution.create', /* @ngInject */ {
        templateUrl: 'mycompanybundle/institution/dialogs/create.html',
        controller: function($scope, $modalInstance, $dialogParams, $state, $http, form, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.form = form;
            $scope.onSuccess = function(data) {
                growl.addSuccessMessage("L'établissement a bien été créé.");
                $scope.dialog.close(data);
            };
        },
        resolve:{
            form: function ($http){
                return $http.get(Routing.generate('institution.create')).then(function (response) {
                    return response.data.form;
                });
            }
        }
    });

    /**
     * institution change organization modal window
     */
    $dialogProvider.dialog('institution.changeOrg', /* @ngInject */ {
        templateUrl: 'mycompanybundle/institution/dialogs/change-organization.html',
        controller: function($scope, $modalInstance, $dialogParams, form, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.form = form;

            $scope.onSuccess = function(response) {
                growl.addSuccessMessage("L'établissement a bien changé de centre de référence.");
                $scope.dialog.close(response);
            };
        },
        resolve: {
            form: function ($http, $dialogParams){
                return $http.get(Routing.generate('institution.changeorg', {id: $dialogParams.institution.id })).then(function(response) {
                    return response.data.form;
                });
            }
        }
    });

    /**
     * institution deletion modal window
     */
    $dialogProvider.dialog('institution.removeManager', /* @ngInject */ {
        templateUrl: 'mycompanybundle/institution/dialogs/remove-manager.html',
        controller: function($scope, $modalInstance, $dialogParams, $state, $http, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;

            $scope.ok = function() {
                var url = Routing.generate('institution.removeManager', {
                    idInstitution: $dialogParams.institution.id,
                    id: $dialogParams.manager.id
                });
                $http.post(url).then(function (response){
                    growl.addSuccessMessage("Le directeur de l'établissement a été supprimé.");
                    $scope.dialog.close(response.data);
                });
            };
        }
    });

    /**
     * institution deletion modal window
     */
    $dialogProvider.dialog('institution.delete', /* @ngInject */ {
        templateUrl: 'mycompanybundle/institution/dialogs/delete.html',
        controller: function($scope, $modalInstance, $dialogParams, $state, $http, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.form = $dialogParams.form;
            $scope.institution = $dialogParams.institution;
            $scope.institutionTrainees = $dialogParams.institutionTrainees;
            $scope.onSuccess = function(response) {
                growl.addSuccessMessage("L'établissement a bien été supprimé.");
                $scope.dialog.close(response.data);
            };
        }
    });
}]);
