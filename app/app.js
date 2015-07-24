var loginid=0;
var objLang=new language();
var app = angular.module('myApp', ['ngRoute', 'ngAnimate', 'toaster']);
app.factory("services", ['$http', function($http) {
  var serviceBase = 'services/api.php?x='
    var obj = {};
    obj.getCustomers = function(){
        return $http.get(serviceBase + 'customers');
    }
    obj.getCustomer = function(customerID){
        return $http.get(serviceBase + 'customer&id=' + customerID);
    }

    obj.insertCustomer = function (customer) {
    return $http.post(serviceBase + 'insertCustomer', customer).then(function (results) {
        return results;
    });
	};

	obj.updateCustomer = function (id,customer) {
	    return $http.post(serviceBase + 'updateCustomer', {id:id, customer:customer}).then(function (status) {
	        return status.data;
	    });
	};

	obj.deleteCustomer = function (id) {
	    return $http.delete(serviceBase + 'deleteCustomer&id=' + id).then(function (status) {
	        return status.data;
	    });
	};
        obj.login = function (email,pwd)
        {
            return $http.post(serviceBase + 'login', {email:email, pwd:pwd}).then(function (status) {
	        return status.data;
	    });
        };
    return obj;   
}]);
app.controller('loginCtrl', function ($scope, $location, services) {
    $scope.login=function (customer)
    {
        services.login(customer.email,customer.password).then(function(data)
        {
            loginid = data["uid"]?data["uid"]:0;
            if(loginid>0) $location.path('/');
        });
    };
});
app.controller('listCtrl', function ($scope, $sce, $templateCache, services) {
    services.getCustomers().then(function(data){
        $scope.customers = data.data;
    });
    $scope.editdata="";
    $scope.clickRecord=function (ind)
    {
        var recordid=jQuery("a#record"+ind).attr("rel");
        for(var i in $scope.customers)
        {
            if($scope.customers[i]["customerNumber"]==recordid)
            {
                var tmp="<table><tr><td><b>Name</b></td><td>:</td><td> " + $scope.customers[i]["customerName"]+ "</td></tr>\
<tr><td><b>EMail</b> </td><td>:</td><td> " + $scope.customers[i]["email"] + "</td></tr>\
<tr><td><b>Address</b> </td><td>:</td><td> " + $scope.customers[i]["address"] + "</td></tr>\
<tr><td><b>City</b> </td><td>:</td><td> " + $scope.customers[i]["city"] + "</td></tr>\
<tr><td><b>State</b> </td><td>:</td><td> " + $scope.customers[i]["state"] + "</td></tr></table>\
";
                $scope.editdata=$sce.trustAsHtml(tmp);
            }
        }
    };
    
});
app.controller('editCtrl', function ($scope, $rootScope, $location, $routeParams, services, customer) {
    var customerID = ($routeParams.customerID) ? parseInt($routeParams.customerID) : 0;    
    $rootScope.title = (customerID > 0) ? 'Edit '+objLang.getLang("Customer") : 'Add '+objLang.getLang("Customer");
    $scope.buttonText = (customerID > 0) ? 'Update '+objLang.getLang("Customer") : 'Add New '+objLang.getLang("Customer");
      var original = customer.data;
      original._id = customerID;
      $scope.customer = angular.copy(original);
      $scope.customer._id = customerID;

      $scope.isClean = function() {
        return angular.equals(original, $scope.customer);
      }

      $scope.deleteCustomer = function(customer) {
        $location.path('/');
        if(confirm("Are you sure to delete customer number: "+$scope.customer._id)==true)
        services.deleteCustomer(customer.customerNumber);
      };

      $scope.saveCustomer = function(customer) {
        $location.path('/');
        if (customerID <= 0) {
            services.insertCustomer(customer);
        }
        else {
            services.updateCustomer(customerID, customer);
        }
    };
});

app.config(['$routeProvider',
  function($routeProvider) {
    $routeProvider.
      when('/', {
        title: 'Customers',
        templateUrl: 'partials/customers.html',
        controller: 'listCtrl'
      })
      .when('/edit-customer/:customerID', {
        title: 'Edit Customers',
        templateUrl: 'partials/edit-customer.html',
        controller: 'editCtrl',
        resolve: {
          customer: function(services, $route){
            var customerID = $route.current.params.customerID;
            return services.getCustomer(customerID);
          }
        }
      }).when('/login', {
            title: 'Login',
            templateUrl: 'partials/login.html',
            controller: 'loginCtrl'
        })
            .when('/logout', {
                title: 'Logout',
                templateUrl: 'partials/login.html',
                controller: 'logoutCtrl'
            })
            .when('/signup', {
                title: 'Signup',
                templateUrl: 'partials/signup.html',
                controller: 'authCtrl'
            })
            .when('/dashboard', {
                title: 'Dashboard',
                templateUrl: 'partials/dashboard.html',
                controller: 'authCtrl'
            })
      .otherwise({
        redirectTo: '/'
      });
}]);
app.run(['$location', '$rootScope', function($location, $rootScope) {
    $rootScope.$on('$routeChangeSuccess', function (event, current, previous) {
        $rootScope.title = current.$$route.title;
    });
}]);