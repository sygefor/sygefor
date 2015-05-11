/**
 * TrainingBundle
 */
sygeforApp.config(['$listStateProvider', '$tooltipProvider', function($listStateProvider, $tooltipProvider) {

    // dashboard
    $listStateProvider.state('dashboard', {
        url: "/dashboard",
        templateUrl: "corebundle/dashboard/dashboard.html",
        resolve: {
            inscriptionStatusList: function ($taxonomy) {
                return $taxonomy.getIndexedTerms('sygefor_trainee.vocabulary_inscription_status');
            }
        },
        controller: function($scope, inscriptionStatusList) {
            $scope.options = {
                title: "Inscriptions en attente de traitement",
                size: 5,
                filters: {
                    'inscriptionStatus.name.source': inscriptionStatusList[1].name
                }
            }
        }
    });

    // tooltips
    $tooltipProvider.options({
        placement: 'bottom',
        appendToBody: true
    });
}]);
