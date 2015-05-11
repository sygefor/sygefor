/**
 * directive : reportCrosstab
 */
sygeforApp.directive("reportCrosstab", [function () {
    return {
        restrict: "A",
        scope: {
            "data": "=",
            "caption": "@"
        },
        link: function(scope) {

            /**
             * used in the 3way cross table
             * @param row
             * @param col
             * @returns {number}
             */
            scope.sum = function(row, key) {
                var sum = 0;
                for(var i=0; i<row.data.length; i++) {
                    for(var j=0; j<row.data[i].data.length; j++) {
                        if(row.data[i].data[j].key == key) {
                            sum += row.data[i].data[j].value;
                        }
                    }
                }
                return sum;
            }

        },
        templateUrl: function(elem,attrs) {
            var template = attrs.template ? 'report-crosstab-' + attrs.template : 'report-crosstab';
            return 'activityreportbundle/directives/' + template + '.html';
        }
    };

}]);

