/**
 * directive : reportCrosstab
 */
sygeforApp.directive("reportCrosstab", [function () {
    return {
        restrict: "A",
        scope: {
            "data": "=",
            "caption": "@",
            "percent": "@",
            "labels": "=",
            "precision": "@"
        },
        link: function(scope) {

            // replace col label if labels are passed
            if (typeof scope.labels !== "undefined") {
                for (var i in scope.data['rows']) {
                    for (var j in scope.labels) {
                        if (j == scope.data['rows'][i]['label']) {
                            scope.data['rows'][i]['label'] = scope.labels[j];
                        }
                    }
                }
            }

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
        templateUrl: function(elem, attrs) {
            var template = attrs.template ? 'report-crosstab-' + attrs.template : 'report-crosstab';
            return 'directives/' + template + '.html';
        }
    };
}]);
