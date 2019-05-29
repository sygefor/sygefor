/**
 * InscriptionStatusChange
 */
sygeforApp.controller('InscriptionStatusChange', ['$scope', '$http', '$window', '$modalInstance', '$dialogParams', '$dialog', 'config', '$filter', '$q', function ($scope, $http, $window, $modalInstance, $dialogParams, $dialog, config, $filter, $q)
{
    $scope.service = 'sygefor_core.batch.inscription_status_change';
    $scope.dialog = $modalInstance;
    $scope.config = config;
    $scope.items = $dialogParams.items;
    $scope.inscriptionStatus = $dialogParams.inscriptionStatus;
    $scope.presenceStatus = $dialogParams.presenceStatus;
    $scope.send = {Mail: !!config.templates.length};
    $scope.attachmentTemplates = config.attachmentTemplates;
    $scope.attachments = [];
    $scope.attCheckList = $scope.attachmentTemplates;
    $scope.formError = '';
    $scope.sending = false;
    $scope.error = false;

    // building templates contents
    $scope.templates = [];
    for (var i in config.templates) {
        $scope.templates[i] = {
            'key': i,
            'label': config.templates[i]['name'],
            'cc': config.templates[i]['cc'],
            'subject': config.templates[i]['subject'],
            'body': config.templates[i]['body'],
            'attachmentTemplates': config.templates[i]['attachmentTemplates']
        };
    }

    $scope.message = {};
    if ($scope.templates.length) {
        $scope.message.template = $scope.templates[0];
        $scope.message.subject = $scope.templates[0].subject;
        $scope.message.cc = $scope.templates[0].cc;
        $scope.message.body = $scope.templates[0].body;
    }
    $scope.message.attachments = [];

    /**
     * watches file upload attachment
     */
    $scope.fileChanged = function (element, $scope) {
        $scope.$apply(function (scope) {
            for (var key in element.files) {
                if (typeof element.files[key] === "object") {
                    $scope.message.attachments.push(element.files[key]);
                }
            }
        });
        angular.element($('#inputAttachment')).val(null);
    };

    /**
     * Remove file attachment
     * @param key
     */
    $scope.removeAttachment = function(key) {
        $scope.message.attachments.splice(key, 1);
        angular.element($('#inputAttachment')).val(null);
    };

    /**
     * ensures the form was correctly filed (sets an error message otherwise), then asks for server-side message sending
     * if mail sending is performed without errors, the file is asked for download
     */
    $scope.ok = function () {
        if ($scope.getAttachmentTotalSize() > 10000000) {
            return;
        }

        if ($scope.send.Mail && !($scope.message.subject || $scope.message.message)) {
            $scope.formError = 'Pas de corps de message';
            return;
        }

        $scope.formError = '';
        var url = Routing.generate('sygefor_core.batch_operation.execute', {id: $scope.service});
        var attTemplates = [];
        if (typeof $scope.attCheckList != 'undefined') {
            angular.forEach($scope.attCheckList, function (tpl) {
                if (( typeof tpl.selected != 'undefined' ) && tpl.selected) {
                    attTemplates.push(tpl.id);
                }
            });
        }

        var data = {
            options: {
                //inscriptionStatus: $scope.inscriptionStatus.id,
                targetClass: 'AppBundle\\Entity\\Inscription',
                sendMail: $scope.send.Mail,
                subject: $scope.message.subject,
                cc: $scope.message.cc,
                additionalCC: $scope.message.additionalCC,
                message: $scope.message.body,
                templateAttachments: attTemplates,
                objects: {'AppBundle\\Entity\\Session': ($dialogParams.session) ? $dialogParams.session.id : 0}
            },
            attachments: $scope.message.attachments,
            ids: $scope.items.join(",")
        };

        if (typeof $scope.inscriptionStatus != 'undefined') {
            data['options']['inscriptionStatus'] = $scope.inscriptionStatus.id
        }

        if (typeof $scope.presenceStatus != 'undefined') {
            data['options']['presenceStatus'] = $scope.presenceStatus.id
        }

        $scope.sending = true;
        $scope.error = false;
        $http({
            method: 'POST',
            url: url,
            transformRequest: function (data) {
                var formData = new FormData();
                formData.append("options", angular.toJson(data.options));
                //now add all of the assigned files
                formData.append("ids", angular.toJson(data.ids));
                //add each file to the form data and iteratively name them

                for (var key in data.attachments) {
                    formData.append("attachment_" + key, data.attachments[key]);
                }
                return formData;
            },
            headers: {'Content-Type': undefined},
            data: data
        }).success(function(data) {
            $modalInstance.close(data);
        }).error(function() {
            $scope.sending = false;
            $scope.error = true;
        });
    };

    $scope.preview = function () {
        $dialog.open('batch.emailPreview', {
            ids: $scope.items[0],
            options: {
                targetClass: 'AppBundle:Inscription',
                subject: $scope.message.subject,
                message: $scope.message.body,
                templateAttachments: $filter('filter')($scope.attCheckList, {selected: true})
            },
            attachments : $scope.message.attachments
        });
    };

    $scope.previewAttachment = function (attachmentTemplate) {

        var url = Routing.generate('sygefor_core.batch_operation.execute', {id: 'sygefor_core.batch.publipost.inscription'});

        var data = {
            options: {
                template: attachmentTemplate.id
            },
            ids: $scope.items[0]
        };

        $http({
            method: 'POST',
            url: url,
            transformRequest: function (data) {
                var formData = new FormData();
                //need to convert our json object to a string version of json otherwise
                // the browser will do a 'toString()' on the object which will result
                // in the value '[Object object]' on the server.
                formData.append("options", angular.toJson(data.options));
                //now add all of the assigned files
                formData.append("ids", angular.toJson(data.ids));

                return formData;
            },
            headers: {'Content-Type': undefined},
            data: data
        }).success(
            function (data) { //response should contain the file url
                if (data.fileUrl) {
                    var url = Routing.generate('sygefor_core.batch_operation.get_file', {
                        service: 'sygefor_core.batch.publipost.inscription',
                        file: data.fileUrl,
                        filename: attachmentTemplate.fileName,
                        pdf: true
                    });
                    // changing location
                    $window.location = url;
                }
            });
    };

    $scope.getNumberOfSelectedPublipostTemplates = function() {
        var i = 0;
        if (typeof $scope.attCheckList != 'undefined') {
            for (var key in $scope.attCheckList) {
                if ($scope.attCheckList[key].selected === true) {
                    ++i;
                }
            }
        }

        return i;
    };

    $scope.getAttachmentTotalSize = function() {
        var totalSize = 0;
        if (typeof $scope.attCheckList != 'undefined') {
            for (var key in $scope.attCheckList) {
                if ($scope.attCheckList[key].selected === true) {
                    totalSize += $scope.attCheckList[key].fileSize;
                }
            }
        }

        return totalSize;
    };

    $scope.getAttachmentTotalSize = function() {
        var totalSize = 0;
        if (typeof $scope.attCheckList != 'undefined') {
            for (var key in $scope.attCheckList) {
                if ($scope.attCheckList[key].selected === true) {
                    totalSize += $scope.attCheckList[key].fileSize;
                }
            }
        }

        for (var key in $scope.message.attachments) {
            totalSize += $scope.message.attachments[key].size;
        }

        return totalSize;
    };

    /**
     * Watches selected template. When changed, current field contents are stored,
     * then replaced byselected template values
     */
    $scope.$watch('message.template', function (newValue) {
        if (newValue) {
            // replacing values
            $scope.message.subject = newValue.subject;
            $scope.message.cc = $scope.replaceCCFormat(newValue.cc);
            $scope.message.body = newValue.body;
            $scope.attCheckList = newValue.attachmentTemplates;

            for (var i in newValue.attachmentTemplates) {
                newValue.attachmentTemplates[i].selected = true;
            }
        }
    });

    /**
     * Reformat symfony cc array to angular object
     * @param cc
     * @returns {{alternativeEmail: boolean, manager: boolean, trainingCorrespondent: boolean, financialCorrespondent: boolean}}
     */
    $scope.replaceCCFormat = function(cc) {
        var ccOptions = {};
        for (var i in $scope.config.ccResolvers) {
            ccOptions[$scope.config.ccResolvers[i]['name']] = $scope.config.ccResolvers[i]['checked'];
        }

        if (cc !== undefined) {
            for (var key in cc) {
                var resolver = cc[key];
                var name = function (ccResolvers, resolver) {
                    for (var j in ccResolvers) {
                        if (j == resolver) {
                            return ccResolvers[j]['name'];
                        }
                    }
                }($scope.config.ccResolvers, resolver);
                if (typeof name !== "undefined") {
                    ccOptions[name] = true;
                }
            }
        }

        return ccOptions;
    };

    $scope.objectLength = function(object) {
        var i = 0;
        for (var key in object) {
            i++;
        }

        return i;
    };
}]);
