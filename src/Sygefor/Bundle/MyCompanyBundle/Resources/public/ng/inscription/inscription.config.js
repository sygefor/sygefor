/**
 * Application config
 */
sygeforApp.config(["$listStateProvider", "$dialogProvider", "$widgetProvider", function($listStateProvider, $dialogProvider, $widgetProvider) {

    // inscription states
    $listStateProvider.state('inscription', {
        url: "/inscription?q&session&trainee&status",
        abstract: true,
        templateUrl: "corebundle/list.html",
        controller:"InscriptionListController",
        resolve: {
            session: function($stateParams, $entityManager) {
                if($stateParams.session) {
                    return $entityManager('SygeforTrainingBundle:Session\\AbstractSession').find($stateParams.session);
                }
                return null;
            },
            trainee: function($stateParams, $entityManager) {
                if($stateParams.trainee) {
                    return $entityManager('SygeforTraineeBundle:AbstractTrainee').find($stateParams.trainee);
                }
                return null;
            },
            inscriptionStatusList: function ($taxonomy) {
                return $taxonomy.getIndexedTerms('sygefor_inscription.vocabulary_inscription_status');
            },
            presenceStatusList: function ($taxonomy) {
                return $taxonomy.getIndexedTerms('sygefor_inscription.vocabulary_presence_status');
            },
            search: function ($searchFactory, $stateParams, session, trainee, $user, inscriptionStatusList) {
                var search = $searchFactory('inscription.search');
                search.query.sorts = {'createdAt': 'desc'};
                if(session) {
                    search.filters["session.id"] = session.id;
                } else if(trainee) {
                    search.filters["trainee.id"] = trainee.id;
                } else {
                    search.query.filters['session.training.organization.name.source'] = $user.organization.name;
                }
                if($stateParams.status && inscriptionStatusList[$stateParams.status]) {
                    search.query.filters["inscriptionStatus.name.source"] = inscriptionStatusList[$stateParams.status].name;
                }
                search.extendQueryFromJson($stateParams.q);
                return search.search().then(function() { return search; });
            }
        },
        breadcrumb:function(session, trainee, $filter, $trainingBundle) {
            var breadcrumb = { label: "Inscriptions", sref: "inscription.table" };
            if(trainee) {
                // stagiaire
                return [
                    { label: "Public", sref: "trainee.table" },
                    { label: trainee.fullName, sref: "trainee.detail.view({id: " + trainee.id + " })" },
                    { label: "Inscriptions", sref: "inscription.table({trainee: " + trainee.id + "})" }
                ];
            }
            if(session) {
                // session
                return [
                    { label: "Évènements", sref: "training.table" },
                    { label: $trainingBundle.getType(session.training.type).label, sref: "training.table({type: " + session.training.type + "})" },
                    { label: session.training.name, sref: "training.detail.view({id: " + session.training.id + " })" },
                    { label: 'Sessions', sref: "session.table({training: " + session.training.id + "})" },
                    { label: $filter('date')(session.dateBegin, 'dd MMMM y'), sref: "session.detail.view({id: " + session.id + ", training: " + session.training.id + "})"},
                    { label: "Inscriptions", sref: "inscription.table({session: " + session.id + "})" }
                ];
            }
            return breadcrumb;
        },
        states: {
            table: {
                url: "",
                icon: "fa-bars",
                label: "Tableau",
                weight: 0,
                controller: 'ListTableController',
                templateUrl: "mycompanybundle/inscription/states/table/table.html"
            },
            detail: {
                url: "/detail",
                icon: "fa-eye",
                label: "aListe détaillée",
                weight: 1,
                templateUrl: "corebundle/states/detail/detail.html",
                controller: 'ListDetailController',
                data:{
                    resultTemplateUrl: "mycompanybundle/inscription/states/detail/result.html"
                },
                states: {
                    view: {
                        url: "/:id",
                        templateUrl: "mycompanybundle/inscription/states/detail/inscription.html",
                        controller: 'InscriptionDetailViewController',
                        resolve: {
                            data: function($http, $stateParams) {
                                var url = Routing.generate('inscription.view', {id: $stateParams.id});
                                return $http({method: 'GET', url: url}).then (function (data) { return data.data; });
                            }
                        },
                        breadcrumb: {
                            label: "{{ data.inscription.trainee.lastName }} {{ data.inscription.trainee.firstName }}"
                        }
                    }
                }
            }
        }
    });

    /**
     * DIALOGS
     */
    $dialogProvider.dialog('inscription.create', /* @ngInject */ {
        controller: 'InscriptionCreate',
        templateUrl: "mycompanybundle/inscription/dialogs/create.html",
        resolve:{
            form: function ($http, $dialogParams){
                return $http.get(Routing.generate('inscription.create', {session: $dialogParams.session.id })).then(function (response) {
                    return response.data.form;
                });
            }
        }
    });

    // update status dialog
    $dialogProvider.dialog("inscription.changeStatus", /* @ngInject */ {
        controller: 'InscriptionStatusChange',
        templateUrl: 'mycompanybundle/inscription/batch/inscriptionStatusChange/inscriptionStatusChange.html',
        size: 'lg',
        resolve: {
            config: function ($http, $dialogParams) {
                var url = Routing.generate('sygefor_core.batch_operation.modal_config', {service: 'sygefor_inscription.batch.inscription_status_change'});
                var optionsArray = {targetClass: 'SygeforInscriptionBundle:AbstractInscription'};
                if (typeof $dialogParams.inscriptionStatus != 'undefined') {
                    optionsArray['inscriptionStatus'] = $dialogParams.inscriptionStatus.id;
                }
                if (typeof $dialogParams.presenceStatus != 'undefined') {
                    optionsArray['presenceStatus'] = $dialogParams.presenceStatus.id;
                }
                return $http.get(url, {params: {options: optionsArray}}).then(function (response) {
                    return response.data;
                });
            }
        }
    });

    // delete dialog
    $dialogProvider.dialog('inscription.delete', /* @ngInject */ {
        templateUrl: 'mycompanybundle/inscription/dialogs/delete.html',
        resolve:{
            data: function ($http, $dialogParams) {
                var url = Routing.generate('inscription.view', {id: $dialogParams.id});
                return $http.get(url).then(function (response) {
                    return response.data;
                });
            }
        },
        controller: function($scope, $modalInstance, $dialogParams, $state, $http, growl, data) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.inscription = data.inscription;
            $scope.ok = function() {
                var url = Routing.generate('inscription.delete', {id: $scope.inscription.id});
                $http.post(url).then(function (response){
                    growl.addSuccessMessage("L'inscription a bien été supprimée.");
                    $scope.dialog.close(response.data);
                });
            };
        }
    });

    // duplicate dialog
    $dialogProvider.dialog('inscription.duplicate', /* @ngInject */ {
        templateUrl: 'trainingbundle/session/dialogs/crud/duplicate.html',
        resolve:{
            data: function ($http, $dialogParams) {
                var url = Routing.generate('session.duplicate', {id: 0, inscriptionIds: angular.toJson($dialogParams.items)});
                return $http.get(url).then(function (response) {
                    return response.data;
                });
            }
        },
        controller: function($scope, $modalInstance, $dialogParams, $state, $http, growl, data) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.form = data.form;
            $scope.session = data.session;
            $scope.inscriptions = angular.toJson(data.inscriptions);


            $scope.onSuccess = function (response) {
                $state.go('session.detail.view', {id: response.session.id}, {reload: true});
                growl.addSuccessMessage("La session a bien été dupliquée. Vous êtes à présent sur la fiche de la nouvelle session.");
                $scope.dialog.close(response.session);
            };
        }
    });

    /**
     * WIDGETS
     */
    $widgetProvider.widget("inscription", /* @ngInject */ {
        controller: 'WidgetListController',
        templateUrl: 'mycompanybundle/inscription/widget/inscription.html',
        options: function($user) {
            return {
                route: 'inscription.search',
                rights: ['sygefor_inscription.rights.inscription.own.view'],
                state: 'inscription.table',
                title: 'Dernières inscriptions',
                size: 10,
                filters:{
                    'session.training.organization.name.source': $user.organization.name,
                    'inscriptionStatus.name.source': 'Attente de validation'
                },
                sorts: {'createdAt': 'desc'}
            }
        }
    });

    var date = new Date();
    date.setMonth(date.getMonth() - 1);
    $widgetProvider.widget("disclaimer", /* @ngInject */ {
        controller: 'WidgetListController',
        templateUrl: 'mycompanybundle/inscription/widget/disclaimer.html',
        options: function($user, $filter) {
            return {
                route: 'inscription.search',
                rights: ['sygefor_inscription.rights.inscription.own.view'],
                state: 'inscription.table',
                title: 'Derniers désistements',
                size: 5,
                filters:{
                    'inscriptionStatus.machine_name': 'desist',
                    "inscriptionStatusUpdatedAt": {
                        "type": "range",
                        "gte": $filter('date')(date, 'yyyy-MM-dd')
                    },
                    'inscription.session.training.organization.name.source': $user.organization.name
                },
                sorts: {'inscriptionStatusUpdatedAt': 'desc'}
            }
        }
    });
}]);
