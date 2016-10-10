/**
 * Core List Controller
 */
sygeforApp.controller('SessionParticipantsSummaryController', ['$scope', '$taxonomy', function($scope, $taxonomy)
{
    $scope.publics = [];
    $taxonomy.getTerms('sygefor_trainee.vocabulary_public_type').then(function(data) {
        $scope.publics = data;
    });

    /**
     * Get the public count
     *
     * @param publicType
     * @returns {*}
     */
    $scope.getCount = function(publicType) {
        for (var i=0; i < $scope.session.participantsSummaries.length; i++) {
            var summary = $scope.session.participantsSummaries[i];
            if(summary.publicType.id == publicType.id) {
                return summary.count;
            }
        }
        return 0;
    }

    /**
     * Get the public count form element
     *
     * @param publicType
     * @returns {*}
     */
    $scope.getFormElement = function(publicType) {
        if(!$scope.form) {
            return null;
        }

        if(!$scope.form.children.participantsSummaries.children) {
            $scope.form.children.participantsSummaries.children = [];
        }

        for (var i=0; i < $scope.form.children.participantsSummaries.children.length; i++) {
            var summary = $scope.form.children.participantsSummaries.children[i];
            if(parseInt(summary.children.publicType.value) == publicType.id) {
                return $scope.form.children.participantsSummaries.children[i].children.count;
            }
        }

        var newElement = {
            name: $scope.form.children.participantsSummaries.children.length,
            children: {
                publicType: {name:'publicType', value: publicType.id },
                count: {name:'count', value: 0}
            }
        };
        $scope.form.children.participantsSummaries.children.push(newElement);
        return newElement;
    }
}]);
