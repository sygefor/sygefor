/**
 * Created by maxime on 12/06/14.
 */
/**
 * BatchMailingController
 */
sygeforApp.controller('TraineeMerge', ['$scope', '$http', '$window', '$modalInstance', '$dialogParams','$state', '$dialog','growl', 'config', 'trainees', function($scope, $http, $window, $modalInstance, $dialogParams, $state, $dialog, growl, config, trainees)
{
    $scope.service = 'sygefor_list.batch.trainee_merge';
    $scope.dialog = $modalInstance;
    $scope.items = $dialogParams.items;
    $scope.trainees = trainees;
    $scope.traineeToKeep = null;
    $scope.error = '';
    $scope.indexToKeep = 0;

    $scope.emails = [];

    if($scope.items.length==1){
        $scope.error = "Veuillez selectionner plus d'un individu pour fusionner."
    }

    // order the trainees by dates
    var tmp = [];
    for (var i in $scope.trainees){
        if(!$scope.traineeToKeep || Date.parse($scope.trainees[i].createdAt) > Date.parse($scope.traineeToKeep.createdAt)){
            $scope.traineeToKeep = $scope.trainees[i];
            tmp.unshift($scope.trainees[i]);
        } else {
            var beLast = true;
            for(var j = 1; j < tmp.length; j++){
                if(Date.parse($scope.trainees[i].createdAt) > Date.parse(tmp[j].createdAt)){
                    tmp.splice(j,0,$scope.trainees[i]);
                    beLast=false;
                    break;
                }
            }
            if (beLast) tmp.push($scope.trainees[i]);
        }

    }
    $scope.trainees = tmp;

    // determine email list
    for (var i in $scope.trainees) {
        var trainee = $scope.trainees[i];
        $scope.emails.push({date: trainee.createdAt , email: trainee.email});
        if(!/^user-[^@]*@reseau-urfist[\s\S]fr$/im.test(trainee.email) && !$scope.selectedEmail) {
            $scope.selectedEmail = trainee.email;
        }
    }

    $scope.selectTraineeToKeep = function(trainee,index){
        $scope.traineeToKeep = trainee;
        $scope.indexToKeep = index;
    }

    /**
     * asks for server-side merging
     *
     */
    $scope.ok = function () {

        if(!$scope.selectedEmail) {
            alert('Vous devez sélectionner un email valide.');
            return;
        }

        var url = Routing.generate('sygefor_list.batch_operation.execute', {id: $scope.service});
        var items = $scope.items.map(function(id) {
            return id+"";
        });

        items.splice(items.indexOf($scope.traineeToKeep.id+""),1);

        var data = {
            options: {
                traineeToKeep: $scope.traineeToKeep.id,
                email: $scope.selectedEmail
            },
            ids: items.join(",")
        };

        $http(
            {   method: 'POST',
                url: url,
                data: data
            }).success(
            function (data) {
                growl.addSuccessMessage("La fusion à été correctement effectuée.");
                $scope.dialog.close($scope.traineeToKeep.id);
                //$state.go('trainee.detail.view', {id: $scope.traineeToKeep.id});
            }
        ).error(function (data) {
                growl.addErrorMessage("Il y a eu un problème lors de la tentative de fusion.");
                //$scope.dialog.dismiss();
            }
        );
    };

    $scope.cancel = function () {
        $modalInstance.dismiss();
    }

}]);
