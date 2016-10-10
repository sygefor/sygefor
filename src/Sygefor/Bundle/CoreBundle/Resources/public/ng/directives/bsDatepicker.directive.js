/**
 * directive : sfHref
 */
sygeforApp.directive('bsDatepicker', ['$timeout', function($timeout) {
    return {
        restrict: 'A',
        require: "?ngModel",
        link: function(scope, element, attrs, ngModel) {

            var options = {
                language: "fr",
                autoclose: true
            };

            // initialize the jquery plugin
            var datepicker = $(element).datepicker(options);
            datepicker = datepicker.data('datepicker');

            // attribute the result to a scope variable, if any name was passed to the directive
            if(attrs.bsDatepicker) {
                scope[attrs.bsDatepicker] = datepicker;
            }

            scope.viewDate = attrs.bsDatepickerViewDate;

            if(element.hasClass('input-daterange')) {

                /**
                 * daterange
                 */
                var update = function() {
                    $timeout(function() {
                        var from = angular.element(datepicker.inputs[0]).controller('ngModel').$modelValue;
                        var to = angular.element(datepicker.inputs[1]).controller('ngModel').$modelValue;
                        datepicker.pickers[0].update(from);
                        datepicker.pickers[1].update(to);
                        datepicker.pickers[1].setStartDate(from);
                    });
                };

                // add specific class
                for(var i = 0; i < datepicker.inputs.length; i++) {
                    angular.element(datepicker.inputs[i]).addClass('input-datepicker');
                }

                // watch them all
                scope.$watch(function () { return angular.element(datepicker.inputs[0]).controller('ngModel').$modelValue; }, update);
                scope.$watch(function () { return angular.element(datepicker.inputs[1]).controller('ngModel').$modelValue; }, update);

                // viewDate
                if(scope.viewDate) {
                    datepicker.pickers[0].viewDate = new Date(scope.viewDate);
                }

            } else {

                /**
                 * datepicker
                 */
                if(ngModel) {
                    // watch the ngModel to update the value
                    scope.$watch(function () {
                        return ngModel.$modelValue;
                    }, function(newValue) {
                        $timeout(function() {
                            datepicker.update(newValue);
                        });
                    });
                }
                // add specific class
                $(element).addClass('input-datepicker');

                // viewDate
                if(scope.viewDate) {
                    datepicker.viewDate = new Date(scope.viewDate);
                }
            }
        }
    };
}]);
