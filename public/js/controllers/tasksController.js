(function() {
    'use strict';

    angular.module('taskTrackerApp')
        .controller('tasksController', [ 'User', 'Task', 'Messages', '$location', '$route', '$localStorage', tasksController ]);

    function tasksController(User, Task, Messages, $location, $route, $localStorage)
    {
        var ec = this;

        ec.tasks          = [];
        ec.managedUsers   = [];
        ec.selectedUserId = 0;
        ec.self           = {};
        ec.messageQ       = [];

        ec.filter         = {};
        ec.editedTask     = null;
        ec.creatingNew    = false;

        ec.onFilterBlur = function() {
            applyFilters();
        };

        ec.onFilterKeyUp = function(keyCode) {
            if (keyCode === 13) {
                applyFilters();
            }
        };

        ec.onSelectedUserChange = function() {
            doRevert();
            $localStorage.tasksSelectedUserId = ec.selectedUserId;
            applyFilters();
        };

        ec.onCreateNew = function() {
            doRevert();
            ec.creatingNew   = true;
            ec.editedTask = {
                id: 0,
                date_time: (new Date()).toUTCString(),
                amount: 0.0,
                description: '',
                comment: ''
            };
            ec.tasks.unshift(ec.editedTask);
        };

        ec.onEdit = function(task) {
            if (ec.isEditing(task)) {
                return;
            }
            doRevert();
            ec.editedTask = Object.assign({}, task);
        };

        ec.isEditing = function(task) {
            if (!task) {
                return ec.editedTask !== null;
            }
            return ec.editedTask !== null && ec.editedTask.id === task.id;
        };

        ec.getIntervalTypeChoices = function() {
            return Task.getIntervalTypeChoices();
        };

        ec.onRevert = function(event) {
            event.stopPropagation();
            doRevert();
        };

        ec.onSave = function(task, event) {
            event.stopPropagation();
            doSave(task);
        };

        ec.onDelete = function(task, event) {
            event.stopPropagation();
            doDelete(task);
        };

        var doRevert = function() {
            if (ec.creatingNew) {
                var index = ec.tasks.indexOf(ec.editedTask);
                ec.tasks.splice(index, 1);
            }
            ec.editedTask = null;
            ec.creatingNew   = false;
        };

        var doSave = function(task) {
            Messages.clear(ec.messageQ);
            Task.save(ec.selectedUserId, ec.editedTask)
                .then(function(data) {
                    ec.editedTask = null;
                    ec.creatingNew   = false;
                    Object.assign(task, data.task);
                    Messages.addSuccess('Task record was updated successfully', ec.messageQ);
                    applyFilters();
                })
                .catch(function(error) {
                    Messages.addErrorResponse(error, ec.messageQ);
                });
        };

        var doDelete = function(task) {
            Messages.clear(ec.messageQ);
            Task.delete(task.id)
                .then(function(data) {
                    var index = ec.tasks.indexOf(task);
                    ec.tasks.splice(index, 1);
                })
                .catch(function(error) {
                    Messages.addErrorResponse(error, ec.messageQ);
                });
        };

        var applyFilters = function() {
            for (var name in ec.filter) {
                if (ec.filter.hasOwnProperty(name) && !ec.filter[name]) {
                    delete ec.filter[name];
                }
            }
            $location.search(ec.filter);
            $route.reload();
        };

        var initialize = function() {
            Messages.clear(ec.messageQ);

            if (!User.isLoggedIn()) {
                $location.path('/login');
                return;
            }

            ec.self         = User.getUserObject();
            ec.managedUsers = User.getManagedUsers();
            if (ec.managedUsers && ec.managedUsers.length) {
                ec.managedUsers.unshift(ec.self);
            }

            ec.selectedUserId = ec.self.id;
            if ($localStorage.hasOwnProperty('tasksSelectedUserId')) {
                ec.selectedUserId = $localStorage.tasksSelectedUserId;
            }

            ec.filter = $location.search();
            Task.get(ec.selectedUserId, ec.filter).then(function(data) {
                ec.tasks = data.tasks;
            }).catch(function(error) {
                if (error.type === 'not_authenticated') {
                    $location.path('/login');
                    return;
                }

                Messages.addErrorResponse(error, ec.messageQ);
            });
        };

        initialize();

    }

})();
