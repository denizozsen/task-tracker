(function() {
    'use strict';

    angular.module('taskTrackerApp')
        .controller('profileController', [ 'User', 'Messages', 'Request', '$q', profileController ]);

    function profileController(User, Messages, Request, $q)
    {
        var pc = this;

        pc.self             = {};
        pc.editedUser       = {};
        pc.managedUsers     = [];
        pc.unblockedUserIds = [];
        pc.pictureToUpload  = null;
        pc.messageQ         = [];

        if (!User.isLoggedIn()) {
            $location.path('/login');
        }

        pc.getRoleChoices = function(user) {
            if (user.id === pc.self.id || pc.self.role === 'standard') {
                return [];
            }

            switch(pc.self.role) {
                case 'standard':
                    return [];
                case 'manager':
                    return [ 'standard', 'manager' ];
                case 'admin':
                    return [ 'standard', 'manager', 'admin' ];
            }
        };

        pc.isAccountBlocked = function(user) {
            return user.login_fail_count >= 3;
        };

        pc.isAccountUnblocked = function(user) {
            return pc.unblockedUserIds.indexOf(user.id) >= 0;
        };

        pc.unblockAccount = function(user) {
            user.login_fail_count = 0;
            pc.unblockedUserIds.push(user.id);
        };

        pc.submit = function() {
            Messages.clear(pc.messageQ);

            var pictureUpload = null;
            if (pc.pictureToUpload) {
                pictureUpload = Request.upload(
                    '/api/users/' + pc.editedUser.id + '/picture',
                    { file: pc.pictureToUpload },
                    User.getSessionToken()
                );

                pictureUpload.then(function (response) {
                    pc.pictureToUpload.result = response.data;
                    pc.editedUser.picture = pc.pictureToUpload.name;
                }, function (error) {
                    Messages.addErrorResponse(error, pc.messageQ);
                }, function (event) {
                    // Math.min is to fix IE which reports 200% sometimes
                    pc.pictureToUpload.progress = Math.min(100, parseInt(100.0 * event.loaded / event.total));
                });
            } else {
                pictureUpload = $q.when();
            }

            pictureUpload.then(function() {
                User.save(pc.editedUser)
                    .then(function() {
                        Messages.addSuccess('Updated profile info for ' + pc.editedUser.name, pc.messageQ);
                        initialize();
                    }).catch(function(error) {
                    Messages.addErrorResponse(error, pc.messageQ);
                });
            });
        };

        var initialize = function() {
            pc.self         = User.getUserObject();
            pc.editedUser   = pc.self;

            pc.managedUsers = User.getManagedUsers();
            if (pc.managedUsers && pc.managedUsers.length) {
                pc.managedUsers.unshift(pc.self);
            }
        };

        initialize();
    }

})();
