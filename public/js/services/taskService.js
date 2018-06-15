(function() {
    'use strict';

    angular.module('taskTrackerApp')
        .factory('Task', [ 'Request', 'User', '$q', taskService ]);

    function taskService(Request, User, $q)
    {
        var prepareModel = function(task) {
            task.date_time = new Date(task.date_time).toUTCString();
        };

        return {

            get: function(userId, filters) {
                var token  = User.getSessionToken();

                return Request.get('/api/users/' + userId + '/tasks', filters, token)
                    .then(function(data) {
                        for (var key in data.tasks) {
                            if (data.tasks.hasOwnProperty(key)) {
                                prepareModel(data.tasks[key]);
                            }
                        }
                        return data;
                    });
            },

            save: function(userId, task) {
                var token  = User.getSessionToken();

                var requestParams = Object.assign({}, task);
                requestParams['userId'] = userId;

                if (task.hasOwnProperty('id') && task.id) {
                    return Request.put('/api/users/' + userId + '/tasks/' + task.id, requestParams, token)
                        .then(function(data) {
                            prepareModel(data.task);
                            return data;
                        });
                } else {
                    return Request.post('/api/users/' + userId + '/tasks', requestParams, token)
                        .then(function(data) {
                            prepareModel(data.task);
                            return data;
                        });
                }
            },

            delete: function(id) {
                var userId = User.getUserObject().id;
                var token  = User.getSessionToken();
                return Request.delete('/api/users/' + userId + '/tasks/' + id, token);
            },

            getIntervalTypeChoices: function() {
                return ['day', 'week', 'month', 'year'];
            }

        }
    }

})();
