(function(){
var app = angular.module("movie", ["ui.bootstrap"])
.run(["$rootScope", "$modal", "$http", function($scope, $modal,$http) {
            $scope.seeMore = function(movie) {
            	$scope.moviemodal=[];
            	$scope.moviemodal=movie;

            	$url = 'http://api.themoviedb.org/3/genre/movie/list?api_key=ef4224b2b7c8bdd52a7b0364788e0e85';
            	$http({
            		method: 'GET',
            		url: $url
            	}).success(function(data, status, headers, config){
            		var genres =[];
            		angular.forEach(data.genres, function(value){
            			var genre_id_list = value.id;
            			var genre_name = value.name;
            			var genre_id = movie.genre_ids;
            			

            			// console.log(movie.genre_ids);
            			angular.forEach(genre_id, function(value){
            				if(value == genre_id_list){
            					genres.push(genre_name);
            				}
            				
            			});
            		});
            		$scope.genres = genres;
        

            	}).error(function(data, status, headers, config){

            	});
            	
                $modal.open({templateUrl:"modal.html",scope: $scope});
                
            };

            

        }]);

app.controller('MovieController',function($scope,$http){
	$page =1;
	$scope.movies = [];
	$scope.selected = false;
    $scope.open = false;

	$scope.loadMore = function() {
            console.log("clicked");
			$url = 'http://api.themoviedb.org/3/discover/movie?api_key=ef4224b2b7c8bdd52a7b0364788e0e85&page=' + $page;
			$http({
			method: 'GET',
			url: $url
		}).success(function(data, status, headers, config){
				var log = [];
				angular.forEach(data.results, function(value) {
					$scope.movies.push(value);
				}, log);
				
		}).
		error(function(data, status, headers, config){
			console.log(status);
		});
		$page++;
	};
	
	$scope.loadMore();

}).directive('whenScrolled', function($window) {
  return function(scope, elm, attr) {
    var raw = elm[0];
    angular.element($window).bind('scroll', function() {
      if (raw.scrollTop + raw.offsetHeight >= raw.scrollHeight) {
        scope.$apply(attr.whenScrolled);
      }
    });
  };
});



})();
