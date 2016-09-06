/**
 * TrainingBundle
 */
sygeforApp.config(["$trainingBundleProvider", "$listStateProvider", "$dialogProvider", function($trainingBundleProvider, $listStateProvider, $dialogProvider) {

    // training states
    $listStateProvider.state('training', {
        url: "/training?q&type",
        abstract: true,
        templateUrl: "listbundle/list.html",
        controller:"TrainingListController",
        resolve: {
            search: function ($searchFactory, $stateParams, $trainingBundle, $user) {
                var search = $searchFactory('training.search');
                search.query.filters = {
                    'training.organization.name.source': $user.organization.name,
                    'year': moment().format('YYYY'),
                    'semester': Math.ceil(moment().format('M')/6)
                };
                if($stateParams.type) {
                    var type = $trainingBundle.getType($stateParams.type);
                    if(type) {
                        search.query.filters["training.typeLabel.source"] = type.label;
                    }
                }
                search.extendQueryFromJson($stateParams.q);
                return search.search().then(function() { return search; });
            }
        },
        breadcrumb: function($stateParams, $trainingBundle) {
            var breadcrumb = [{ label: "Événements", sref: "training.table({type: null})" }];
            if($stateParams.type) {
                breadcrumb.push({ label: $trainingBundle.getType($stateParams.type).label, sref: "training.table({type: '" + $stateParams.type + "'})" });
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
                templateUrl: "trainingbundle/training/states/table/table.html"
            },
            detail: {
                url: "/detail",
                icon: "fa-eye",
                label: "Liste détaillée",
                weight: 1,
                templateUrl: "listbundle/states/detail/detail.html",
                controller: 'ListDetailController',
                data:{
                    resultTemplateUrl: "trainingbundle/training/states/detail/result.html"
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
                        breadcrumb: function($stateParams, data, $trainingBundle) {
                            var breadcrumb = [];
                            if(!$stateParams.type) {
                                breadcrumb.push({ label: $trainingBundle.getType(data.training.type).label, sref: "training.table({type: data.training.type})" });
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
        controller: function($scope, $modalInstance, $dialogParams, $state, $trainingBundle, form, growl) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.form = form;
            $scope.trainingType = $trainingBundle.getType($dialogParams.type);
            $scope.onSuccess = function(data) {
                growl.addSuccessMessage("La formation a bien été créée.");
                $scope.dialog.close(data);
            };
        },
        template: '<div training-template="create" type="dialog.params.type" default="/bundles/sygefortraining/ng/training/dialogs/create/training.html"></div>',
        resolve:{
            // @todo blaise : fix form directive to remove this resolve
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
        controller: function($scope, $modalInstance, $dialogParams, $state, $trainingBundle, $http, form) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = $dialogParams;
            $scope.training = $dialogParams.training;
            $scope.form = form;
            $scope.onSuccess = function(response) {
                $scope.dialog.close(response);
            };
        },
        templateUrl: 'trainingbundle/training/dialogs/duplicate/choose-type.html',
        resolve:{
            form: function ($http, $dialogParams){
                return $http.get(Routing.generate('training.choosetypeduplicate')).then(function(response) {
                    var form = response.data.form;
                    form.children.duplicatedType.value = $dialogParams.training.type;
                    // @todo why have no underscores ?
                    if (form.children.duplicatedType.value === "diversetraining") {
                        form.children.duplicatedType.value = "diverse_training";
                    }
                    else if (form.children.duplicatedType.value === "trainingcourse") {
                        form.children.duplicatedType.value = "training_course";
                    }
                    else if (form.children.duplicatedType.value === "doctoraltraining") {
                        form.children.duplicatedType.value = "doctoral_training";
                    }
                    return form;
                });
            }
        }
    });

    $dialogProvider.dialog('training.duplicate', /* @ngInject */ {
        controller: function($scope, $modalInstance, $dialogParams, $state, $trainingBundle, $http, form, growl, $timeout) {
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
            // @todo blaise : fix form directive to remove this resolve
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
        controller: function($scope, $modalInstance, $dialogParams, $state, $trainingBundle, $http, growl) {
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
        templateUrl: 'trainingbundle/training/dialogs/remove/training.html'
    });

    // add material (general dialog)
    $dialogProvider.dialog('training.material.add', /* @ngInject */ {
        controller:function ($scope, $modalInstance, $dialogParams) {
            $scope.dialog = $modalInstance;
            $scope.dialog.params = angular.copy($dialogParams);
            $scope.removeCallback = $dialogParams.removeCallback;
            $scope.downloadCallback = $dialogParams.downloadCallback;
            $scope.addCallback = $dialogParams.addCallback;
        },
        templateUrl: 'trainingbundle/training/dialogs/material/add.html'
    });

    // remove material
    $dialogProvider.dialog('material.remove', /* @ngInject */ {
        controller:function ($scope, $modalInstance, $dialogParams, $http) {
            $scope.dialog = angular.copy($modalInstance);
            $scope.dialog.params = $dialogParams;
            $scope.ok = function() {
                var url = Routing.generate('material.remove', {id: $dialogParams.material.id});
                $http.get(url).then($scope.dialog.close);
            }
        },
        templateUrl: 'trainingbundle/training/dialogs/material/remove.html'
    });

    //add link material (specific dialog)
    $dialogProvider.dialog('training.linkmaterial.add', /* @ngInject */ {
        controller:function ($scope, $modalInstance, $dialogParams, form) {
            $scope.dialog = $modalInstance;
            $scope.formRoute = $dialogParams.route
            $scope.form = form;
            $scope.dialog.params = angular.copy($dialogParams);

            $scope.onSuccess = function(data) {
                $scope.dialog.close(data);

            };
        },
        resolve:{
            // @todo blaise : fix form directive to remove this resolve
            form: function ($http, $dialogParams){
                return $http.get($dialogParams.route).then(function(response) {
                    var form = response.data.form;

                    return form;
                });
            }
        },
        templateUrl: 'trainingbundle/training/dialogs/material/add-link-material.html'

    });
    /**
     * TRAINING TYPES
     */
    $trainingBundleProvider.addType('internship', {
        label: 'Stage',
        templates: {
            view: 'trainingbundle/training/states/detail/internship.html',
            create: 'trainingbundle/training/dialogs/create/internship.html',
            duplicate: 'trainingbundle/training/dialogs/duplicate/internship.html'
        }
    });

    // training_course
    $trainingBundleProvider.addType('training_course', {
        label: 'Enseignement de cursus',
        templates: {
            view: 'trainingbundle/training/states/detail/training-course.html',
            create: 'trainingbundle/training/dialogs/create/training-course.html',
            duplicate: 'trainingbundle/training/dialogs/duplicate/training-course.html'
        }
    });

    // doctoral_training
    $trainingBundleProvider.addType('doctoral_training', {
        label: 'Formation doctorale',
        templates: {
            view: 'trainingbundle/training/states/detail/doctoral-training.html',
            create: 'trainingbundle/training/dialogs/create/doctoral-training.html',
            duplicate: 'trainingbundle/training/dialogs/duplicate/doctoral-training.html'
        }
    });

    // diverse_training
    $trainingBundleProvider.addType('diverse_training', {
        label: 'Action diverse',
        templates: {
            view: 'trainingbundle/training/states/detail/diverse-training.html',
            create: 'trainingbundle/training/dialogs/create/diverse-training.html',
            duplicate: 'trainingbundle/training/dialogs/duplicate/diverse-training.html'
        }
    });

    // meeting
    $trainingBundleProvider.addType('meeting', {
        label: 'Rencontre scientifique',
        templates: {
            view: 'trainingbundle/training/states/detail/meeting.html',
            create: 'trainingbundle/training/dialogs/create/meeting.html',
            duplicate: 'trainingbundle/training/dialogs/duplicate/meeting.html'
        }
    });
}]);
