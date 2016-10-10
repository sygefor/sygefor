/**
 * Directive sfFileUpload
 **/
sygeforApp.directive('sfFileUpload', ['growl', '$timeout', function(growl, $timeout) {
    return {
        scope: {
            addCallback: '=',
            thref: "=thref",
            accept: "=?",
            queue: "=?"
        },
        link: function (scope, element) {
            //error messages
            scope.errorsTime = new Date().getTime();

            scope.options = {
                //overloading add callback in order to manage filesize limit.
                add: function(e, data) {
                    var time = new Date().getTime();

                    //resetting errors list if old
                    if (time - scope.errorsTime > 800) {
                        scope.errorsTime = time;
                    }

                    if (data.files[0]['size'] > scope.options.maxFileSize ) {
                        $timeout(function() {
                            growl.addErrorMessage("Le fichier " + data.files[0]['name'] + " est trop volumineux");
                        });
                    }
                    else {
                        var files = data.files;
                        var file = files[0];

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
                                data.submit();
                            }
                        };
                        file.$cancel = function () {
                            return data.abort();
                        };

                        data.files[0].uploading = true;
                        if (scope.queue) {
                            scope.queue.push(data.files[0]);
                        }
                        data.submit();
                    }
                },
                autoUpload: true,
                url: Routing.generate(scope.thref.route, scope.thref.params),
                maxFileSize: 50000000
            };

            /* callback for changing file status when upload ok */
            element.find('#fileupload').bind('fileuploaddone', function(e, data) {
                //new file was added and is returned
                if (data.files) {
                    for (var key in data.files) {
                        // remove uploading file
                        if (scope.queue) {
                            for (var i = 0; scope.queue; i++) {
                                if (scope.queue[i].uploading === true && scope.queue[i].name === data.files[key].name) {
                                    scope.queue.splice(i, 1);
                                    break;
                                }
                            }
                        }
                        scope.addCallback(data.files[key], data.result);
                    }
                }
            });
        },
        template: '' +
        '<form id="fileupload" method="POST" enctype="multipart/form-data" file-upload="options" auto-upload="true">' +
        '   <input type="file" name="files[]" accept="{{ accept }}" multiple>' +
        '</form>'
    }
}]);
