var objService=['$http', function($http) {
    var serviceBase = 'api.php?m=settings&a='
    var obj = {};
    obj.getRoles = function(){
        return $http.get(serviceBase + 'roles');
    }
    obj.getRole = function(roleID){
        return $http.get(serviceBase + 'role&id=' + roleID);
    }
    obj.insertRole = function (rolename,parentid) {
        return $http.post(serviceBase + 'insertRole', {rolename:rolename,parentid:parentid}).then(function (results) {
            return results;
        });
    };
    obj.updateRole = function (id,role) {
        return $http.post(serviceBase + 'updateRole', {id:id, role:role}).then(function (status) {
            return status.data;
        });
    };
    obj.deleteRole = function (id) {
        return $http.delete(serviceBase + 'deleteRole&id=' + id).then(function (results) {
            return results;
        });
    };
    obj.syncRole = function (syncData)
    {
        return $http.post(serviceBase + 'syncRole', syncData).then(function (results) {
            return results;
        });
    };
    obj.login = function (email,pwd)
    {
        return $http.post(serviceBase + 'login', {email:email, pwd:pwd}).then(function (status) {
            return status.data;
        });
    };
    obj.addProfilesToRole = function (objProfile)
    {
        return $http.post(serviceBase + 'addProfilesToRole', objProfile).then(function (status) {
            return status.data;
        });
    };
    obj.deleteProfilesFromRole = function (objProfile)
    {
        return $http.post(serviceBase + 'deleteProfilesFromRole', objProfile).then(function (status) {
            return status.data;
        });
    };
    return obj;   
}];