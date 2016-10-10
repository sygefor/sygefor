/**
 * Created by maxime on 12/06/14.
 */
/**
 * BatchMailingController
 */
sygeforApp.controller('BatchEMailController', ['$scope', '$http', '$window','$modalInstance', '$dialogParams', '$dialog', 'config', 'growl', function($scope, $http, $window, $modalInstance, $dialogParams, $dialog, config, growl)
{
    $scope.dialog = $modalInstance;
    $scope.items = $dialogParams.items;
    $scope.targetClass = $dialogParams.targetClass;

    //building templates contents
    $scope.templates = [];
    for(var i in config.templates) {
        $scope.templates[i] = {
            'key': i,
            'label': config.templates[i]['name'],
            'subject': config.templates[i]['subject'],
            'cc': replaceCCFormat(config.templates[i]['cc']),
            'body': config.templates[i]['body']
        };
    }

    $scope.templates.unshift({
        'key': -1,
        'label': '',
        'subject': '',
        'cc': {
            alternativeEmail: false,
            manager: false,
            trainingCorrespondent: false,
            financialCorrespondent: false
        },
        'body': ''
    });

    if ($scope.templates.length) {
        $scope.message = {
            template: $scope.templates[0],
            subject: $scope.templates[0]['subject'],
            cc: $scope.templates[0]['cc'],
            body: $scope.templates[0]['body'],
            attachment: null
        };
    } else {
        $scope.message = {
            template: null,
            subject: '',
            cc: {
                alternativeEmail: false,
                manager: false,
                trainingCorrespondent: false,
                financialCorrespondent: false
            },
            body: '',
            attachment: null
        };
    }

    $scope.formError = '';

    /**
     * ensures the form was correctly filed (sets an error message otherwise), then asks for server-side message sending
     * if mail sending is performed without errors, the file is asked for download
     */
    $scope.ok = function () {

        if(!($scope.message.subject || $scope.message.message)) {
            $scope.formError = 'Pas de corps de message' ;
            return;
        }

        $scope.formError = '' ;

        var url = Routing.generate('sygefor_core.batch_operation.execute', {id: 'sygefor_core.batch.email'});

        var data = {
            options: {
                targetClass: $scope.targetClass,
                subject: $scope.message.subject,
                cc: $scope.message.cc,
                message: $scope.message.body
                //objects: {'SygeforTrainingBundle:Session': $dialogParams.session.id }
            },
            attachment: $scope.message.attachment,
            ids: $scope.items.join(",")
        };

        $http({method: 'POST',
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
            formData.append("attachment", data.attachment);

            return formData;
        },
        headers: {'Content-Type': undefined},
        data: data
        }).success(function (data) {
            growl.addSuccessMessage("Le message a bien été ajouté à la liste d'envoi.");
        });

        $modalInstance.close();
    };

    /**
     * open a new dialog modal corresponding to a batch email operation in preview mode.
     */
    $scope.preview = function() {
        $dialog.open('batch.emailPreview', {
            ids: $scope.items[0],
            options: {
                targetClass: $scope.targetClass,
                subject: $scope.message.subject,
                cc: $scope.message.cc,
                message: $scope.message.body
            }
        });
    };

    /**
     * Watches selected template. When changed, current field contents are stored,
     * then replaced byselected template values
     */
    $scope.$watch('message.template', function (newValue, oldValue) {
        if(newValue) {
            //storing changes
            if (oldValue && (oldValue.key != newValue.key)) {
                oldValue.subject = $scope.message.subject;
                oldValue.cc = $scope.message.cc;
                oldValue.body = $scope.message.body;
            }
            //replacing values
            $scope.message.subject = newValue.subject;
            $scope.message.cc = newValue.cc;
            $scope.message.body = newValue.body;
        }
    });

    /**
     * watches file upload attachment
     */
    $scope.fileChanged = function(element, $scope) {
        $scope.$apply(function(scope) {
            $scope.message.attachment = element.files[0];
        });
    };

    /**
     * clears attachment
     */
    $scope.resetUpload = function () {
        $scope.message.attachment = null;
        angular.element( $('#inputAttachment')).val(null);
    };
}]);


/**
 * Reformat symfony cc array to angular object
 * @param cc
 * @returns {{alternativeEmail: boolean, manager: boolean, trainingCorrespondent: boolean, financialCorrespondent: boolean}}
 */
function replaceCCFormat(cc)
{
    var reformatCC = {
        alternativeEmail: false,
        manager: false,
        trainingCorrespondent: false,
        financialCorrespondent: false
    };

    if (cc === undefined) {
        return reformatCC;
    }

    reformatCC.alternativeEmail = cc.indexOf('alternativeEmail') >= 0 ? true : false;
    reformatCC.manager = cc.indexOf('manager') >= 0 ? true : false;
    reformatCC.trainingCorrespondent = cc.indexOf('trainingCorrespondent') >= 0 ? true : false;
    reformatCC.financialCorrespondent = cc.indexOf('financialCorrespondent') >= 0 ? true : false;

    return reformatCC;
}
