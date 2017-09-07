angular.module('ui.bootstrap.app', ['ui.bootstrap'])
.controller('ContactCtrl', function($scope, $uibModal, $http) {
  $scope.contact = {};
  $scope.open = function() {
    var modalInstance = $uibModal.open({
        animation: true,
        backdrop: true,
        templateUrl: 'contactModalContent.html',
        controller: function ($scope, $uibModalInstance) {
          $scope.submitting = false;
          $scope.submitVal = 'Send';
          $scope.submit = function () {
            $scope.submitting = true;
            $scope.submitVal = 'Sending';
            $http.post('mail.php', {'data': $scope.contact})
              .then(function(resp) {
                if (200 === resp.status) {
                  $scope.submitting = false;
                  $scope.submitVal= 'Send';
                }
              });
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
