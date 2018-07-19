/**
 * CoreBundle
 */
sygeforApp.config(["$utilsProvider", "$listStateProvider", "$dialogProvider", function($utilsProvider, $listStateProvider, $dialogProvider) {

    // training states
    $listStateProvider.state('training', {
        url: "/training?q&type",
        abstract: true,
        templateUrl: "list.html",
        controller:"TrainingListController",
        resolve: {
            search: function ($searchFactory, $stateParams, $utils, $user) {
                var search = $searchFactory('training.search');
                search.query.filters = {
                    'training.organization.name.source': $user.organization.name,
                    'year': moment().format('YYYY'),
                    'semester': Math.ceil(moment().format('M')/6)
                };
                if($stateParams.type) {
                    var type = $utils.getType($stateParams.type);
                    if(type) {
                        search.query.filters["training.typeLabel.source"] = type.label;
                    }
                }
                search.extendQueryFromJson($stateParams.q);
                return search.search().then(function() { return search; });
            }
        },
        breadcrumb: function($stateParams, $utils) {
            var breadcrumb = [{ label: "Événements", sref: "training.table({type: null})" }];
            if($stateParams.type) {
                breadcrumb.push({ label: $utils.getType($stateParams.type).label, sref: "training.table({type: '" + $stateParams.type + "'})" });
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
                templateUrl: "training/training/states/table/table.html"
            },
            detail: {
                url: "/detail",
                icon: "fa-eye",
                label: "Liste détaillée",
                weight: 1,
                templateUrl: "states/detail/detail.html",
                controller: 'ListDetailController',
                data:{
                    resultTemplateUrl: "training/training/states/detail/result.html"
                },
                states: {
                    view: {
                        url: "/:id",
                        resolve: {
                            data: function($http, $stateParams) {
                                var id = $stateParams.id;
                                if(typeof id == "string" && id.indexOf('_') > 0) { // semestered_training
                                    id = id.substring(0, id.indexOf('_'));
                                }
                                var url = Routing.generate('training.view', {id: id});
                                return $http({method: 'GET', url: url}).then (function (data) { return data.data; });
                            }
                        },
                        template: '<div training-template="view" type="training.type" default="/bundles/sygefortraining/ng/training/states/detail/training.html"></div>',
                        controller: 'TrainingDetailViewController',
                        breadcrumb: function($stateParams, data, $utils) {
                            var breadcrumb = [];
                            if(!$stateParams.type) {
                                breadcrumb.push({ label: $utils.getType(data.training.type).label, sref: "training.table({type: data.training.type})" });
                            }
                            breadcrumb.push({ label: "{{ data.training.name }}" });
                            return breadcrumb;
                        }
                    }
                }
            }
        }
    });

    /**
     * DIALOGS
     */

    $dialogProvider.dialog('training.create', /* @ngInject */ {
        controller: function($scope, $modalInstance, $dialogParams, $state, $utils, form, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.form = form;
            $scope.trainingType = $utils.getType($dialogParams.type);

            // if training is singleSession type, the session name is copied from training name
            if ($scope.trainingType.singleSession && $scope.trainingType.singleSession === true) {
                $scope.$watch(function () {
                    return $scope.form.children.name.value;
                }, function(newValue) {
                    $scope.form.children.session.children.name.value = newValue;
                });
            }

            $scope.onSuccess = function(data) {
                growl.addSuccessMessage("La formation a bien été créée.");
                $scope.dialog.close(data);
            };
        },
        template: '<div training-template="create" type="dialog.params.type" default="/bundles/sygefortraining/ng/training/dialogs/create/training.html"></div>',
        resolve:{
            form: function ($http, $dialogParams){
                return $http.get(Routing.generate('training.create', {type: $dialogParams.type })).then(function(response) {
                    var form = response.data.form;
                    // pre-fill some fields with search filters
                    if(form.children.firstSessionPeriodYear) {
                        form.children.firstSessionPeriodYear.value = $dialogParams.filters.year;
                    }
                    if(form.children.firstSessionPeriodSemester) {
                        form.children.firstSessionPeriodSemester.value = $dialogParams.filters.semester + "";
                    }
                    return form;
                });
            }
        }
    });



    /**
     * Choose cloned training type and first period params
     */
    $dialogProvider.dialog('training.choosetypeduplicate', /* @ngInject */ {
        controller: function($scope, $modalInstance, $dialogParams, $state, $utils, $http, form) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.training = $dialogParams.training;
            $scope.form = form;
            $scope.onSuccess = function(response) {
                $scope.dialog.close(response);
            };
        },
        templateUrl: 'training/training/dialogs/duplicate/choose-type.html',
        resolve:{
            form: function ($http, $dialogParams){
                return $http.get(Routing.generate('training.choosetypeduplicate')).then(function(response) {
                    var form = response.data.form;
                    form.children.duplicatedType.value = $dialogParams.training.type;
                    if (form.children.duplicatedType.value === "long_training") {
                        form.children.duplicatedType.value = "longtraining";
                    }
                    return form;
                });
            }
        }
    });

    $dialogProvider.dialog('training.duplicate', /* @ngInject */ {
        controller: function($scope, $modalInstance, $dialogParams, $state, $utils, $http, form, growl, $timeout) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.form = form;
            $scope.training = $dialogParams.training;
            $scope.type = $dialogParams.type;

            $scope.onSuccess = function(response) {
                growl.addSuccessMessage("La formation a bien été dupliquée. Vous êtes à présent sur la fiche de la nouvelle formation.");
                // used to close modal if there are no supplementary fields to fill-in
                $timeout(function() {
                    $scope.dialog.close(response.training);
                })
            };
        },
        template: '<div training-template="duplicate" type="dialog.params.type" default="/bundles/sygefortraining/ng/training/dialogs/duplicate/training.html"></div>',
        resolve:{
            form: function ($http, $dialogParams){
                var params= {};
                params.id = $dialogParams.training.id;
                params.type = $dialogParams.type;
                return $http.get(Routing.generate('training.duplicate', params)).then(function(response) {
                    return response.data.form;
                });
            }
        }
    });

    $dialogProvider.dialog('training.delete', /* @ngInject */ {
        controller: function($scope, $modalInstance, $dialogParams, $state, $utils, $http, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.ok = function() {
                var url = Routing.generate('training.remove', {id: $dialogParams.training.id});
                $http.post(url).then(function (response){
                    growl.addSuccessMessage("La formation a bien été supprimée.");
                    $scope.dialog.close(response.data);
                });
            };
        },
        templateUrl: 'training/training/dialogs/remove/training.html'
    });

    // edit module
    $dialogProvider.dialog('training.module.edit', /* @ngInject */ {
        controller:function ($scope, $modalInstance, $dialogParams, form, $dialog) {
            $scope.dialog = $modalInstance;
            $scope.module = $dialogParams.module;
            $scope.form = form;
            $scope.dialog.params = angular.copy($dialogParams);

            $scope.onSuccess = function(data) {
                $scope.dialog.close(data);
            };
        },
        resolve:{
            form: function ($http, $dialogParams){
                return $http.get(Routing.generate('module.edit', {id: $dialogParams.module.id})).then(function(response) {
                    return response.data.form;
                });
            }
        },
        templateUrl: 'training/training/dialogs/module/edit-module.html'
    });

    /**
     * TRAINING TYPES
     */
    $utilsProvider.addType('internship', {
        label: 'Stage',
        templates: {
            view: 'training/training/states/detail/internship.html',
            create: 'training/training/dialogs/create/training.html',
            duplicate: 'training/training/dialogs/duplicate/training.html'
        }
    });
    $utilsProvider.addType('long_training', {
        label: 'Formation longue',
        templates: {
            view: 'training/training/states/detail/long_training.html',
            create: 'training/training/dialogs/create/training.html',
            duplicate: 'training/training/dialogs/duplicate/training.html'
        }
    });
    $utilsProvider.addType('meeting', {
        label: 'Rencontre scientifique',
        templates: {
            view: 'training/training/states/detail/meeting.html',
            create: 'training/training/dialogs/create/meeting.html',
            duplicate: 'training/training/dialogs/duplicate/meeting.html'
        }
    });
}]);
