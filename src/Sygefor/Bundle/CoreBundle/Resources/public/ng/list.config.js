/**
 * TrainingBundle
 */
sygeforApp.config(['$dialogProvider', function($dialogProvider) {

    /**
     * batch operations
     */

    // csv
    $dialogProvider.dialog('batch.export.csv', /* @ngInject */ {
        controller: 'BatchExportCsvController',
        templateUrl: 'corebundle/batch/export/csv/csv.html'
    });

    // pdf
    $dialogProvider.dialog('batch.export.pdf', /* @ngInject */ {
        controller: 'BatchExportPdfController',
        templateUrl: 'corebundle/batch/export/pdf/pdf.html'
    });

    // publiposting
    $dialogProvider.dialog('batch.publipost', /* @ngInject */ {
        controller: 'BatchPublipostController',
        templateUrl: 'corebundle/batch/publipost/publipost.html',
        resolve: {
            config: function($http, $dialogParams) {
                var url = Routing.generate('sygefor_core.batch_operation.modal_config', {service: 'sygefor_core.batch.publipost.'+$dialogParams.service});
                return $http.get(url).then(function(response){ return response.data;} );
            }
        }
    });

    // publiposting
    $dialogProvider.dialog('batch.convert_type', /* @ngInject */ {
        controller: 'BatchConvertTypeController',
        templateUrl: 'corebundle/batch/convert-type/convert-type.html',
        resolve: {
            config: function($http, $dialogParams) {
                var url = Routing.generate('sygefor_core.batch_operation.modal_config', {service: 'sygefor_core.batch.convert_type.'+$dialogParams.service});
                return $http.get(url).then(function(response){ return response.data;} );
            }
        }
    });

    //email
    $dialogProvider.dialog('batch.email', /* @ngInject */ {
        controller: 'BatchEMailController',
        templateUrl: 'corebundle/batch/email/email.html',
        size: 'lg',
        resolve: {
            config: function($http) {
                var url = Routing.generate('sygefor_core.batch_operation.modal_config', {service: 'sygefor_core.batch.email'});
                return $http.get(url).then(function(response){ return response.data;} );
            }
        }
    });

    //email preview
    $dialogProvider.dialog("batch.emailPreview", /* @ngInject */ {
        controller: function($scope, $modalInstance, $dialogParams, email){
            console.log(email);
            $scope.email = {
                subject: email.subject,
                cc: email.cc,
                message: email.message
            };
            $scope.modalInstance = $modalInstance;
        },
        templateUrl: 'corebundle/batch/email/email-preview.html',
        size: 'lg',
        resolve: {
            email: function($http, $dialogParams) {
                var url = Routing.generate('sygefor_core.batch_operation.execute', {id: 'sygefor_core.batch.email'});
                return $http.post(url, {ids: $dialogParams.ids, options: angular.extend($dialogParams.options, {preview: true}) }).then(function (response){
                    return response.data.email;
                });
            }
        }
    });

}]);
