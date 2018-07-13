/**
 * CoreBundle
 */
sygeforApp.config(["$listStateProvider", "$dialogProvider",  function($listStateProvider, $dialogProvider, $dialogParams) {

    // trainee states
    $listStateProvider.state('trainer', {
        url: "/trainer?q&session",
        abstract: true,
        templateUrl: "list.html",
        controller:"TrainerListController",
        breadcrumb: [
            { label: "Intervenants", sref: "trainer.table" }
        ],
        resolve: {
            session: function($stateParams, $entityManager) {
                if($stateParams.session) {
                    return $entityManager('SygeforCoreBundle:AbstractSession').find($stateParams.session);
                }
                return null;
            },
            search: function ($searchFactory, $stateParams, $user, session) {
                var search = $searchFactory('trainer.search');
                search.query.sorts = {'lastName.source': 'asc'};
                if(session) {
                    search.filters["sessions.id"] = session.id;
                }
                //search.query.filters['isArchived'] = false;
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
                templateUrl: "trainer/states/table/table.html"
            },
            detail: {
                url: "/detail",
                icon: "fa-eye",
                label: "Liste détaillée",
                weight: 1,
                templateUrl: "states/detail/detail.html",
                controller: 'ListDetailController',
                data:{
                    resultTemplateUrl: "trainer/states/detail/result.html"
                },
                states: {
                    view: {
                        url: "/:id",
                        templateUrl: "trainer/states/detail/trainer.html",
                        controller: 'TrainerDetailViewController',
                        resolve: {
                            data: function($http, $stateParams) {
                                var url = Routing.generate('trainer.view', {id: $stateParams.id});
                                return $http({method: 'GET', url: url}).then (function (data) { return data.data; });
                            }
                        },
                        breadcrumb: {
                            label: "{{ data.trainer.fullName }}"
                        }
                    }
                }
            }
        }
    });

    /**
     * DIALOGS
     */
    $dialogProvider.dialog('trainer.create', /* @ngInject */ {
        templateUrl: 'trainer/dialogs/create.html',
        controller: function($scope, $modalInstance, $dialogParams, $state, $http, form, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.form = form;
            $scope.onSuccess = function(data) {
                growl.addSuccessMessage("L'intervenant a bien été créé.");
                $scope.dialog.close(data);
            };
        },
        resolve:{
            form: function ($http){
                return $http.get(Routing.generate('trainer.create')).then(function (response) {
                    return response.data.form;
                });
            }
        }
    });

    $dialogProvider.dialog('yearDocuments.add', /* @ngInject */ {
        templateUrl: 'trainer/dialogs/yearDocuments/yearDocuments-add.html',
        controller: function($scope, $modalInstance, $dialogParams, $http, form, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.form = form;

            $scope.onSuccess = function(data) {
                growl.addSuccessMessage("Le dossier a bien été créé.");
                $scope.dialog.close(data);
            };
        },
        resolve:{
            form: function ($http, $dialogParams) {
                return $http.get(Routing.generate('yearDocuments.create', {id: $dialogParams.trainer.id, year: ($dialogParams.year ? $dialogParams.year : null)})).then(function (response) {
                    return response.data.form;
                });
            }
        }
    });

    $dialogProvider.dialog('yearDocuments.edit', /* @ngInject */ {
        templateUrl: 'trainer/dialogs/yearDocuments/yearDocuments-edit.html',
        controller: function($scope, $dialog, $modalInstance, $dialogParams, $http, form, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.form = form;

            $scope.deleteYearDocuments = function() {
                $dialog.open('yearDocuments.remove', {yearDocuments: $scope.dialog.params.yearDocuments}).then(function(data) {
                    $scope.dialog.close(data);
                })
            };

            $scope.onSuccess = function(data) {
                growl.addSuccessMessage("Le dossier a bien été mis à jour.");
                $scope.dialog.close(data);
            };
        },
        resolve:{
            form: function ($http, $dialogParams) {
                return $http.get(Routing.generate('yearDocuments.edit', {id: $dialogParams.yearDocuments.id})).then(function (response) {
                    return response.data.form;
                });
            }
        }
    });

    $dialogProvider.dialog('yearDocuments.remove', /* @ngInject */ {
        templateUrl: 'trainer/dialogs/yearDocuments/yearDocuments-remove.html',
        controller: function($scope, $modalInstance, $dialogParams, $http, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;

            $scope.ok = function() {
                var url = Routing.generate('yearDocuments.delete', {id: $dialogParams.yearDocuments.id});
                $http.post(url).then(function (){
                    $scope.dialog.close();
                    growl.addSuccessMessage("Le dossier a bien été supprimé.");

                });
            };
        }
    });

    /**
     * trainer deletion modal window
     */
    $dialogProvider.dialog('trainer.delete', /* @ngInject */ {
        templateUrl: 'trainer/dialogs/delete.html',
        controller: function($scope, $modalInstance, $dialogParams, $state, $http, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.ok = function() {
                var url = Routing.generate('trainer.delete', {id: $dialogParams.trainer.id});
                $http.post(url).then(function (response){
                    $scope.dialog.close();
                    growl.addSuccessMessage("Le intervenant a bien été supprimé.");

                });
            };
        }

    });

    /**
     * trainer change organization modal window
     */
    $dialogProvider.dialog('trainer.changeOrg', /* @ngInject */ {
        templateUrl: 'trainer/dialogs/change-organization.html',
        controller: function($scope, $modalInstance, $dialogParams, $state, $http, form, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.form = form;
            $scope.onSuccess = function(response) {
                growl.addSuccessMessage("Le intervenant a bien changé de centre de référence.");
                $scope.dialog.close(response);
            };
        },
        resolve: {
            form: function ($http, $dialogParams){
                return $http.get(Routing.generate('trainer.changeorg', {id: $dialogParams.trainer.id })).then(function(response) {
                    return response.data.form;
                });
            }
        }

    });

}]);


