(function() {
    'use strict';

    angular.module('taskTrackerApp')
        .controller('inviteController', [ 'User', 'Messages', '$location', inviteController ]);

    function inviteController(User, Messages, $location)
    {
        var ic = this;

        ic.email    = '';
        ic.messageQ = [];

        if (!User.isLoggedIn() || User.getUserObject().role !== 'admin') {
            $location.path('/');
            return;
        }

        ic.sendInvite = function() {
            Messages.clear(ic.messageQ);
            User.sendInvite(ic.email)
                .then(function() {
                    Messages.addSuccess('Invite successfully sent to ' + ic.email, ic.messageQ);
                    ic.email = '';
                }).catch(function(error) {
                    Messages.addErrorResponse(error, ic.messageQ);
                });
        };
    }

})();
