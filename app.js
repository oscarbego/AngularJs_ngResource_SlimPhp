var projectsApp = angular.module('projects', ['ngResource']);


projectsApp.config(function($routeProvider) {
  $routeProvider.
          when('/', 
                    {
                      controller: 'ProjectListCtrl',
                      templateUrl: 'projectlist.html'
                    }
              ).
          when('/detail/:id', 
                    {
                      controller: 'ProjectDetailCtrl',
                      templateUrl: 'template/projectdetail.html'
                    }).
          when('/add/:id', 
                    {
                      controller:  'ProjectDetailCtrl',
                      templateUrl: 'template/projectAdd.html'
                    }).
          otherwise('/');
});


projectsApp.factory( 'RestFull', [ 
  '$resource', function( $resource ) {
      return function( url, params, methods ) {
         var defaults = {
           update: { method: 'put', isArray: false },
           create: { method: 'post' }
         };
         
         methods = angular.extend( defaults, methods );
     
         var resource = $resource( url, params, methods );
     
         resource.prototype.$save = function(f) {
           if ( !this.id ) {
             this.$create(f);
           }
           else {
             this.$update(f);
           }
         };
     
         return resource;
      };
    }
  ]
);


projectsApp.factory( 'Project', [ 'RestFull', function( $resource ) {
      return $resource( 'rest/projects/:id', {} );
      //return $resource( 'rest/projects/:id', { id: '@id' } );
    }
  ]
);


projectsApp.controller('ProjectListCtrl', function(Project, $scope) {

    Project.query(
      function(data)
      {
        $scope.projects = data;
        console.log("Proyectos:");
        console.log($scope.projects);
        console.log("----------------------");
      }
    );


    $scope.delete = function(pro, index)
    {
      pro.$delete({id: pro.id},
        function()
        {
          $scope.projects.splice(index, 1);
        }
      );
    }


});


projectsApp.controller('ProjectDetailCtrl', function(Project, $routeParams, $scope, $location) {

  $scope.project = $routeParams.id
    ? Project.get({id: $routeParams.id})
    : new Project();

  $scope.save = function()
  {
    $scope.project.$save(
      function(p){
        console.log("Save or UpDate Project");
        console.log(p);          
        //$scope.projects.push(p);
        console.log("----------------------");
        $location.path('/');
      }
    );
  }

});