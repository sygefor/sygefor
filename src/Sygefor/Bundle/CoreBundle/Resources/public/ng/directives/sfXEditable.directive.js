$.fn.editable.defaults.emptytext = '<a href="#" class="btn btn-xs btn-default"><i class="fa fa-pencil"></i> renseigner</a>';

/**
 * Symfony2 xeditable form
 */
sygeforApp.directive('sfXeditableForm', ['$http', '$timeout', function($http, $timeout)
{
    /**
     * Extract data from a FormView
     * This function is recursive
     * @param params
     */
    var extractData = function(formView) {
        var name = formView.name;
        var data = {};

        if(formView.children) {
            // if the element has children, get the data from them
            for(var key in formView.children) {
                angular.extend(data, extractData(formView.children[key]));
            }
        } else {
            if(typeof formView.checked != "undefined") {
                // get the data from the checked property
                data = formView.checked;
            } else {
                // get the data from the value property
                data = formView.value;
            }
        }

        var obj = {};
        obj[name] = data;

        return obj;
    }

    /**
     * Extract errors
     * @param form
     */
    var extractErrors = function(form, errors) {
        errors = errors || [];
        if(form.errors && form.errors.length) {
            errors[form.id] = form.errors;
        }
        if(form.children) {
            for(var key in form.children) {
                extractErrors(form.children[key], errors);
            }
        }
        return errors;
    }

    /**
     * directive return
     */
    return {
        restrict: 'A',
        require: 'form',
        scope:{
            "form": "=sfXeditableForm",
            "onSuccess": "&",
            "onError": "&"
        },
        link: function(scope, element, attrs) {

        },
        controller: function($scope, $element, $attrs) {
            /**
             * process the form via a xeditable element
             */
            var process = function(formElt) {
                var deferred = new $.Deferred;
                var url = $attrs.action;

                var data = extractData($scope.form);
                $http.post(url, data)
                    .success(function(data, status, headers, config) {
                        var form = data.form;
                        if(form) {
                            $scope.form = form;
                            if(!form.valid) {
                                var errors = extractErrors(form);
                                // if error is on current submitted field, return this error
                                if(formElt && errors[formElt.id]) {
                                    return deferred.reject(errors[formElt.id][0]);
                                }
                                // if error is on form, display the error on the current edited field
                                if(form.errors && form.errors.length) {
                                    return deferred.reject(form.errors[0]);
                                }
                                // ui element displaying current field is not saved but put aside
                                $element.addClass('editable-unsaved');

                                // if error is on another field, emit event to display error on this field
                                $scope.$parent.$broadcast('field-error', errors);

                                // old way displaying an error occured
                                //return deferred.reject('Erreur lors de la soumission du formulaire');
                            }
                        }
                        $scope.onSuccess({data: data, status: status , headers: headers, config: config});
                        return deferred.resolve();
                    })
                    .error(function(data, status, headers, config) {
                        $scope.onError({data: data, status: status , headers: headers, config: config});
                        return deferred.reject('Erreur lors de la soumission du formulaire');
                    });

                return deferred.promise();
            };

            // attach to the controller
            this.process = process;

            /**
             * Attach the process function to submit method
             */
            $scope.$watch("form", function(form) {
                form.submit = process;
            })

            /**
             * on element shown
             * @param formElt
             */
            this.onElementShown = function(formElt) {
                $element.addClass('editable-shown');
            }

            /**
             * on element hidden
             * @param formElt
             */
            this.onElementHidden = function(formElt) {
                $element.removeClass('editable-shown');
            }
        }
    }
}]);

/**
 * Form xeditable element
 */
sygeforApp.directive('sfXeditable', ['$timeout', function($timeout) {

    /**
     * Convert choices to XEditable source
     */
    var choicesToSource = function(choices) {
        var source = [];

        for(var i=0; i < choices.length; i++) {
            var choice = choices[i];
            if(typeof choice.l != "string") {
                source.push({text:choice.v, children:choicesToSource(choice.l)});
            } else {
                source.push({text:choice.l, value: choice.v});
            }
        }

        return source;
    }

    return {
        restrict: 'A',
        require: '^sfXeditableForm',
        scope: {
          'sfXeditable': "=",
          'onChange':'&'
        },
        link: function(scope, element, attrs, formCtrl)
        {
            var $element = angular.element(element);

            /**
             * element update
             */
            var update = function(formElt) {
                if(!formElt) {
                    // if the element has been already initialized, destroy it
                    if($element.data('editable')) {
                        $element.editable('destroy');
                    }
                    return;
                }

                var type = formElt.type;

                var options = {
                    name: formElt.name,
                    url: formCtrl.process,
                    value: formElt.value,
                    type: 'text',
                    mode: 'inline',
                    //onblur: 'ignore',
                    params: function(params) {
                        // call onchange event
                        scope.onChange({params: params});
                        formElt.value = params['value'];
                        return formElt;
                    },
                    display: false // let angular update value
                };


                // type from the form definition
                switch(type) {
                    case "choice":
                        // choice list
                        options.source = choicesToSource(formElt.choices);
                        /*options.source = formElt.choices;
                        if(Array.isArray(options.source)) {
                            options.source = arrayToObject(options.source);
                        }*/
                        options.type = 'select';
                        break;
                    case "date":
                        options.onshown = function(e, editable) {
                            editable.input.$tpl.datepicker({
                                weekStart: 1,
                                format: 'dd/mm/yyyy',
                                viewformat: 'dd/mm/yyyy',
                                language: 'fr'
                            }).on('changeDate', function(ev){
                                $(this).datepicker('hide');
                            });
                        };
                        break;
                    case "checkbox":
                        // yes/no choice list
                        options.source = [{value: 1, text: "Oui"}, {value: 0, text: "Non"}];
                        options.type = 'select';
                        options.value = formElt.checked ? 1 : 0;
                        options.params = function(params) {
                            formElt.checked = !!parseInt(params.value);
                            return formElt;
                        };
                        break;
                }

                // override by data-*
                for(key in options) {
                    if($element.data(key)) {
                        options[key] = $element.data(key);
                    }
                }

                // type from the xeditable attributes
                switch(options.type) {
                    case "text":
                        options.tpl = '<input type="text" name="' + formElt.name + '">';
                        break;
                    case "select2":
                        options.select2 = {};
                        if(formElt.choices) {
                            if(type == "text") {
                                // select2 tags style
                                // we only need the array version of choices
                                options.select2.tags = $.map(formElt.choices, function(value) { return value.l; });
                            } else {
                                options.select2.multiple = !!formElt.multiple;
                                options.source = $.map(formElt.choices, function(value) { return {id:value.v,text:value.l}; });
                            }
                        }
                        break;
                }

                // initialize the xeditable element
                if($element.data('editable')) {
                    // if the element is already initialized, apply all these options to the element
                    for(var key in options) {
                        $element.editable('option', key, options[key]);
                    }
                } else {
                    // else, initialize the element
                    $element.editable(options);

                    // attach options events, if any
                    if(options.onshown) {
                        $element.on('shown', options.onshown);
                    }
                    if(options.onhidden) {
                        $element.on('hidden', options.onhidden);
                    }

                    // attach some events
                    $element.on('shown', function(e, editable) {
                        if(editable.container.$tip) {
                            editable.container.$tip.addClass('editable-' + editable.options.type);
                            editable.container.$tip.attr('id', 'editable-container-' + editable.options.name);
                        }
                        formCtrl.onElementShown(editable);
                    });
                    $element.on('hidden', function(e, editable) {
                        formCtrl.onElementHidden(editable);
                    });

                    // optional : support jquery autosize for textarea
                    if(element.data("type") == "textarea" && jQuery && jQuery().autosize) {
                        $element.on('shown', function(e, editable) {
                            $timeout(function() {
                                $('textarea', editable.$element.siblings(".editable-container")).autosize();
                            });
                        });
                    }
                }
            }

            /**
             * handle empty value
             * @todo : blaise : pas trop satisfait du principe, à revoir...
             */
            var handleEmpty = function() {
                var value = element.html();
                var isEmpty = $.trim(value) === '';

                if(!scope.sfXeditable) {
                    return false;
                }

                if($.trim(value) == $.fn.editable.defaults.emptytext) {
                    return false;
                }

                var emptyElm = element.data('$empty');
                if(!emptyElm) {
                    emptyElm = $($.fn.editable.defaults.emptytext);
                    element.data('$empty', emptyElm);
                }

                if(isEmpty) {
                    emptyElm.appendTo(element);
                    element.addClass($.fn.editable.defaults.emptyclass);
                } else {
                    emptyElm.remove();
                    element.removeClass($.fn.editable.defaults.emptyclass);
                }
            }

            /**
             * listen sfXeditableForm field-error event to display focus and display the error at correct field
             * @param $event
             * @param array errors : array of all errors
             */
            scope.$on('field-error', function ($event, errors) {
                for (var key in errors) {
                    // if error concerns this field
                    if (key === scope.sfXeditable.id) {
                        $element.editable('show');
                        $element.data('editableContainer').$form.data('editableform').error(errors[key]);
                        break;
                    }
                }
            });

            /**
             * watch element
             */
            scope.$watch('sfXeditable', function(formElt) {
                update(formElt);
            });

            /**
             * watch empty content
             */
            scope.$watch(function() { return element.html(); }, handleEmpty);
            $timeout(handleEmpty);
        }
    }
}]);
