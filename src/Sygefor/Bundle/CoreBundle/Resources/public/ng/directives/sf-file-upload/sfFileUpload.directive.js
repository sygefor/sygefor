
/** Directive sfFileUpload **/
sygeforApp.directive('sfFileUpload', ['$dialog', 'growl',function($dialog, growl) {
    return {
        scope: {
            queue: "=",
            removeCallback: '=',
            downloadCallback: '=',
            addCallback: '=',
            thref: "=thref"
        },
        link: function (scope, element, attrs) {

            //error messages
            scope.uploadErrors = [];
            scope.errorsTime = new Date().getTime();

            scope.options={
                //overloading add callback in order to manage filesize limit.
                add: function(e, data) {

                    var time = new Date().getTime();

                    //resetting errors list if old
                    if (time - scope.errorsTime > 800) {
                        scope.errorsTime = time;
                        scope.uploadErrors = [];
                    }

                    if (( data.files[0]['size'] > scope.options.maxFileSize )) {
                        //need to use apply here.
                        scope.$apply(function () {
                            scope.uploadErrors.push(data.files[0]['name'] + " : Fichier trop volumineux.");
                        });
                    } else {
                        var files = data.files,
                            file = files[0];

                        //adding properties to files (taken from initial add callback)
                        angular.forEach(files, function (file, index) {
                            file._index = index;
                            file.$state = function () {
                                return data.state();
                            };
                            file.$processing = function () {
                                return data.processing();
                            };
                            file.$progress = function () {
                                return data.progress();
                            };
                            file.$response = function () {
                                return data.response();
                            };
                        });
                        file.$submit = function () {
                            if (!file.error) {
                                return data.submit();
                            }
                        };
                        file.$cancel = function () {
                            return data.abort();
                        };
                        scope.queue.push(data.files[0]);
                        data.submit();
                    }
                },
                autoUpload: true,
                url: Routing.generate(scope.thref.route, scope.thref.params),
                maxFileSize: 50000000

            };

            /* callback for changing file status when upload ok*/
            element.find('#fileupload').bind('fileuploaddone',function (e,data) {
                //new material was added and is returned
                var message = "";
                if (data.result.material) {

                    angular.forEach(data.result.material, function(material) {

                        if (material.id) {
                            angular.forEach(scope.queue, function (queueElt) {
                                if (typeof queueElt.id == 'undefined' && queueElt.name == material.name) {
                                    queueElt.id = material.id;
                                    queueElt.present = true;
                                    queueElt.fileName = queueElt.name;

                                    if (typeof scope.addCallback == 'function') {
                                        scope.addCallback(queueElt);
                                        // file was uploaded so it is local
                                        queueElt.isLocal = true;
                                    }
                                }
                            });
                        }else if (material.error){

                            message += material.name +" : "+material.error;
                            var toRemove;
                            angular.forEach(scope.queue, function(queueElt) {
                                if (typeof queueElt.id == 'undefined' && queueElt.name == material.name) {
                                    toRemove = queueElt;
                                }
                            });
                            scope.queue.splice(scope.queue.indexOf(toRemove),1);
                        }
                        if( message.length > 0 ) {

                            scope.$apply(function() {
                                growl.addErrorMessage( message);
                            });
                        }

                    });
                } else {
                    var toRemove;
                    angular.forEach(scope.queue, function(queueElt) {
                        if (typeof queueElt.id == 'undefined') {
                            toRemove = queueElt;
                        }
                    });
                    scope.queue.splice(scope.queue.indexOf(toRemove),1);
                    scope.$apply(function() {
                        growl.addErrorMessage("Erreur lors du transfert du fichier" + ( (message != "" ) ? (" : " + message) : "" ));
                    });
                }

            } );

            /* setting status for already present files*/
            angular.forEach(scope.queue, function (file) {
                file.present = true;
                file.isLocal = true;
                if (file.url) {file.isLocal = false}

            });

            /**
             * function called to download file (calls the callback
             * @param file
             */
            scope.getFile = function (file) {

                if (scope.downloadCallback) {
                    scope.downloadCallback(file);
                }
            };

            /**
             * Removes a material
             */
            scope.removeFile = function (file) {
                //queue
                if (scope.removeCallback) {
                    scope.removeCallback(file).then(function (){
                        for (var i = 0 ; i < scope.queue.length; i ++) {
                            if (scope.queue[i].id === file.id) {
                                scope.queue.splice(i, 1);
                                break;
                            }
                        }
                    });
                } else {
                    for (var i = 0 ; i < scope.queue.length; i ++) {
                        if (scope.queue[i].id === file.id) {
                            scope.queue.splice(i, 1);
                            break;
                        }
                    }
                }
            };

            /**
             * add a linked material
             */
            scope.addLinkMaterial = function (){

                var params = angular.copy(scope.thref.params);
                var extP = angular.extend(params,{"type":"link"});

                $dialog.open("linkmaterial.add",{route:Routing.generate(scope.thref.route,extP)}).then(function (data){

                        data.material.isLocal = false;
                        scope.addCallback(data.material);
                        scope.queue.push(data.material);
                    }
                );
            }
        },
        templateUrl: 'corebundle/directives/sf-file-upload/sf-file-upload.html'
    }
}]);
