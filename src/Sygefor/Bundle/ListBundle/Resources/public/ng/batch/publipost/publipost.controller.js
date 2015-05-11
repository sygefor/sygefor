/**
 * BatchPublipostController
 */
sygeforApp.controller('BatchPublipostController', ['$scope', '$http', '$window','$modalInstance', '$timeout', '$dialogParams', 'config', function($scope, $http, $window, $modalInstance, $timeout, $dialogParams, config)
{
    var service = 'sygefor_list.batch.publipost.' + $dialogParams.service;
    $scope.dialog = $modalInstance;
    $scope.items = $dialogParams.items;
    $scope.templateList = config.templateList;

    $scope.options = {
        template: '',
        templateFile: '',
        sendPdf: false
    };
    if ($scope.templateList.length) {
        $scope.options.template = $scope.templateList[0];
    }

    $scope.chooseError = '';

    /**
     * ensures the form was correctly filed (sets an error message otherwise), then asks for server-side file generation
     * if generation is performed without errors, the file is asked for download
     */
    $scope.ok = function () {

        if(!($scope.options.template || $scope.options.templateFile)) {
            $scope.chooseError = 'Pas de modèle sélectionné' ;
            return;
        }

        $scope.chooseError = '' ;
        var url = Routing.generate('sygefor_list.batch_operation.execute', {id: service});
        var data = {
            options: {
                template: $scope.options.template.id,
                templateFile: $scope.options.templateFile
            },
            ids: $scope.items.join(","),
            templateFile: $scope.options.templateFile
        };

        $http(
            {method: 'POST',
                url: url,
                transformRequest: function (data) {
                    var formData = new FormData();
                    //need to convert our json object to a string version of json otherwise
                    // the browser will do a 'toString()' on the object which will result
                    // in the value '[Object object]' on the server.
                    formData.append("options", angular.toJson(data.options));
                    //now add all of the assigned files
                    formData.append("ids", angular.toJson(data.ids));
                    //add each file to the form data and iteratively name them
                    formData.append("templateFile", data.templateFile);

                    return formData;
                },
                headers: {'Content-Type': undefined},
                data: data
            }).success(
            function (data) { //response should contain the file url
                if (data.fileUrl) {

                    var fn = '';
                    if ($scope.options.templateFile != '' ) {
                        fn = $scope.options.templateFile.name;
                    }
                    if (!fn && $scope.options.template != '' ){
                        fn = $scope.options.template.fileName;
                    }

                    var url = Routing.generate('sygefor_list.batch_operation.get_file', {service: service, filename: fn, file: data.fileUrl, pdf: $scope.options.sendPdf });
                    // changin location :
                    $window.location = url;
                }
            });

        $modalInstance.close();
    };

    /**
     * watches file upload model, and updates the form accordingly
     */
    $scope.fileChanged = function(element, $scope) {
        $scope.$apply(function(scope) {
            $scope.options.templateFile = element.files[0];
        });
    };

    /**
     * resets file upload form (if user wants the file selected to be used instead
     */
    $scope.resetUpload = function () {
        $scope.options.templateFile = null;
        angular.element( $('#inputTplFile')).val(null);
    };
}]);
