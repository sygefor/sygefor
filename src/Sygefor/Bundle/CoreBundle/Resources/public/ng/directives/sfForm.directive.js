/**
 * Symfony2 form
 */
sygeforApp.directive('sfForm', ['$http', function($http) {
    /**
     * Extract data from a FormView
     * This function is recursive
     * @param formView
     */
    var extractData = function(formView) {
        var name = formView.name;
        var data = {};

        if (formView.children) {
            // if the element has children, get the data from them
            for(var key in formView.children) {
                angular.extend(data, extractData(formView.children[key]));
            }
        }
        else {
            if (typeof formView.checked != "undefined") {
                if (typeof formView.value === "string") {
                    data = formView.checked;
                }
                else {
                    data = formView.value;
                }
            }
            else {
                // get the data from the value property
                data = formView.value;
            }
        }

        var obj = {};
        obj[name] = data;

        return obj;
    };

    /**
     * directive return
     */
    return {
        restrict: 'A',
        require: 'form',
        scope:{
            "form": "=sfForm",
            "onSuccess": "&",
            "onError": "&",
            "onPreSubmit": "&"
        },
        link: function(scope, element, attrs) {
            /**
             * Process the form
             */
            var process = function() {

                // build the post array
                var data = extractData(scope.form);

                // calling controller presubmit if it want to manage datas before submition
                if (typeof scope.onPreSubmit == 'function') {
                    scope.onPreSubmit({data: data});
                }

                // send the request
                $http.post(element.attr('action'), data).
                    success(function(data, status, headers, config) {
                        var form = attrs.jsonPath ? data[attrs.jsonPath] : data;
                        if(form !== undefined && form.valid !== undefined && !form.valid) {
                            // invalid form, update the scope object
                            angular.extend( scope.form, form);
                        } else {
                            // success
                            scope.onSuccess({data: data, status: status , headers: headers, config: config});
                        }
                    }).
                    error(function(data, status, headers, config) {
                        scope.onError({data: data, status: status , headers: headers, config: config});
                    });
            };

            /**
             * on form submit, build the query
             * and send it to the server
             */
            element.on('submit', function(event) {
                process();
                return false;
            });
        }
    }
}]);

/**
 * Generate a widget based on a form element
 */
sygeforApp.directive('sfFormWidget', ['$compile', function($compile) {

    var widgets = {
        text: "<input type='text'>",
        textarea: "<textarea></textarea>",
        choice: "<select ng-options=\"choice.v+'' as choice.l for choice in element.choices\" ng-multiple=\"element.multiple\"></select>", // need to force value to string to be compatible
        date: "<input type='text' bs-datepicker>",
        time: "<input type='time'>"
    };

    return {
        restrict: 'EA',
        replace: true,
        scope: {
            element:'=sfFormWidget'
        },
        link: function(scope, element, attrs) {
            /**
             * update
             */
            var update = function() {
                var elt = scope.element;
                if(!elt) {
                    return;
                }

                var type = elt.type || 'text';
                var tpl = "<input type='" + type + "'>";
                if(widgets[type]) {
                    tpl = widgets[elt.type];
                }

                var elem = angular.element(tpl);

                // ng model
                elem.attr('ng-model', 'element.value');
                elem.attr('id', '{{ element.id }}');
                elem.attr('name', '{{ element.full_name }}');
                if (type === "checkbox") {
                    elem.attr('ng-checked', 'element.checked');
                }

                // required
                if(elt.required) {
                    elem.attr('required', true);
                }

                // attr
                if(elt.attr) {
                    for(var name in elt.attr) {
                        elem.attr(name, elt.attr[name]);
                    }
                }

                // get the attrs from the markup to add it to the new element
                for (attr in attrs.$attr) {
                    if (attrs.hasOwnProperty(attr)){
                        // prefer not compiled attribute
                        if (attrs.$attr[attr]) {
                            elem.attr(attrs.$attr[attr], attrs[attr]);
                        }
                        else {
                            elem.attr(attr, attrs[attr]);
                        }
                    }
                }

                // replace the current element
                element.replaceWith($compile(elem)(scope));
            };

            /**
             * watch element
             */
            scope.$watch('element', function() {
                update();
            });
        }
    }
}]);
