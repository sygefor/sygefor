/**
 * CoreBundle
 */
sygeforApp.config(['$listStateProvider', '$tooltipProvider', function($listStateProvider, $tooltipProvider) {

    // dashboard
    $listStateProvider.state('dashboard', {
        url: "/dashboard",
        templateUrl: "core/dashboard/dashboard.html",
        controller: function($scope) {
            $scope.options = {
                title: "Inscriptions en attente de traitement",
                size: 10,
                filters: {
                    'inscriptionStatus.status': 0 // status pending
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
