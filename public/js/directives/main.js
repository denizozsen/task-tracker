(function() {
    'use strict';

    angular.module('taskTrackerApp')
        .directive('listFilter', [ listFilterDirective ]);

    function listFilterDirective()
    {
        return {
            restrict: 'E',
            scope: {
                ngModel: '=',
                controller: '=',
                placeholder: '@',
                extraClass: '@'
            },
            controller: function($scope, $timeout) {
                return {
                    onFilterBlur: function() {
                        $timeout($scope.controller.onFilterBlur);
                    }
                };
            },
            controllerAs: 'lfController',
            template:
                '<input type="text" class="form-control input-sm small-text {{extraClass}}" placeholder="{{placeholder}}"\n' +
                '    ng-model="ngModel"\n' +
                '    ng-model-options="{updateOn: \'blur keyup\', debounce: { \'keyup\': 500, \'blur\': 0 }}"\n' +
                '    ng-change="lfController.onFilterBlur()">\n' +
                '</input>'
        };
    }

})();
