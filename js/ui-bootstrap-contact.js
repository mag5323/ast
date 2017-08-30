angular.module('ui.bootstrap.app', ['ui.bootstrap'])
.controller('ContactCtrl', function($scope, $uibModal, $http) {
  $scope.contact = {};
  $scope.open = function() {
    var modalInstance = $uibModal.open({
        animation: true,
        backdrop: true,
        templateUrl: 'contactModalContent.html',
        controller: function ($scope, $uibModalInstance) {
          $scope.submit = function () {
            console.log($scope.contact);
            $uibModalInstance.dismiss('cancel');
          }
          $scope.cancel = function () {
            $uibModalInstance.dismiss('cancel');
          };
        }
    });

    modalInstance.result.then(function ($scope) {
      console.log('close');
    }, function () {
      console.log('dismiss');
    });
  };
});
