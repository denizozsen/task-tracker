(function() {
    'use strict';

    angular.module('taskTrackerApp')
        .controller('verificationController', [ 'User', 'Messages', '$location', verificationController ]);

    function verificationController(User, Messages, $location)
    {
        var vc = this;

        vc.email            = '';
        vc.verificationCode = '';
        vc.messageQ         = [];

        vc.submit = function() {
            Messages.clear(vc.messageQ);
            User.setVerified(vc.email, vc.verificationCode)
                .then(function() {
                    $location.path('/');
                }).catch(function(error) {
                    Messages.addErrorResponse(error, vc.messageQ);
                });
        };

        vc.resend = function() {
            Messages.clear(vc.messageQ);
            User.resendVerificationCode(vc.email)
                .then(function() {
                    Messages.addSuccess('The verification email has been resent.', vc.messageQ);
                }).catch(function(error) {
                    Messages.addErrorResponse(error, vc.messageQ);
                });
        };
    }

})();
