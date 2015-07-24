(function () {
  'use strict';

  angular.module('demoApp', ['ui.tree', 'ngRoute'])

    .config(['$routeProvider', function ($routeProvider) {
      $routeProvider
        .when('/', {
          templateUrl: 'views/home.html'
        })
        .when('/basic-example', {
          controller: 'BasicExampleCtrl',
          templateUrl: 'views/basic-example.html'
        })
        .when('/connected-trees', {
          controller: 'ConnectedTreesCtrl',
          templateUrl: 'views/connected-trees.html'
        })
        .when('/filter-nodes', {
          controller: 'FilterNodesCtrl',
          templateUrl: 'views/filter-nodes.html'
        })
        .otherwise({
          redirectTo: '/'
        });
    }])

})();