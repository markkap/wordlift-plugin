# Set the well-known $ reference to jQuery.
$ = jQuery

# Create the main AngularJS module, and set it dependent on controllers and directives.
angular.module('wordlift.facetedsearch.widget', [ 'wordlift.ui.carousel', 'wordlift.utils.directives' ])
.provider("configuration", ()->
  
  _configuration = undefined
  
  provider =
    setConfiguration: (configuration)->
      _configuration = configuration
    $get: ()->
      _configuration

  provider
)
.filter('filterEntitiesByType', [ '$log', 'configuration', ($log, configuration)->
  return (items, types)->
    
    filtered = []
    for id, entity of items
      if  entity.mainType in types
        filtered.push entity
    filtered

])

.controller('FacetedSearchWidgetController', [ 'DataRetrieverService', 'configuration', '$scope', '$log', (DataRetrieverService, configuration, $scope, $log)-> 

    $scope.entity = undefined
    $scope.posts = []
    $scope.facets = []
    $scope.conditions = {}
    # TODO Load dynamically 
    $scope.supportedTypes = [
      { 'scope' : 'what', 'types' : [ 'thing', 'creative-work' ] }
      { 'scope' : 'who', 'types' : [ 'person', 'organization', 'local-business' ] }
      { 'scope' : 'where', 'types' : [ 'place' ] }
      { 'scope' : 'when', 'types' : [ 'event' ] }
    ]
      
    $scope.configuration = configuration
    $scope.filteringEnabled = true

    $scope.toggleFiltering = ()->
      $scope.filteringEnabled = !$scope.filteringEnabled

    $scope.isInConditions = (entity)->
      if $scope.conditions[ entity.id ]
        return true
      return false

    $scope.addCondition = (entity)->
      $log.debug "Add entity #{entity.id} to conditions array"

      if $scope.conditions[ entity.id ]
        delete $scope.conditions[ entity.id ]
      else
        $scope.conditions[ entity.id ] = entity
      
      DataRetrieverService.load( 'posts', Object.keys( $scope.conditions ) )

        
    $scope.$on "postsLoaded", (event, posts) -> 
      $log.debug "Referencing posts for item #{configuration.post_id} ..."
      $scope.posts = posts
      
    $scope.$on "facetsLoaded", (event, facets) -> 
      $log.debug "Referencing facets for item #{configuration.post_id} ..."
      $scope.facets = facets

])
# Retrieve post
.service('DataRetrieverService', [ 'configuration', '$log', '$http', '$rootScope', (configuration, $log, $http, $rootScope)-> 
  
  service = {}
  service.load = ( type, conditions = [] )->
    uri = "#{configuration.ajax_url}?action=#{configuration.action}&post_id=#{configuration.post_id}&type=#{type}"
    
    $log.debug "Going to search #{type} with conditions"
    
    $http(
      method: 'post'
      url: uri
      data: conditions
    )
    # If successful, broadcast an *analysisReceived* event.
    .success (data) ->
      $rootScope.$broadcast "#{type}Loaded", data
    .error (data, status) ->
       $log.warn "Error loading #{type}, statut #{status}"

  service

])
# Configuration provider
.config([ 'configurationProvider', (configurationProvider)->
  configurationProvider.setConfiguration window.wl_faceted_search_params
])

$(
  container = $("""
  	<div ng-controller="FacetedSearchWidgetController" ng-show="posts.length > 0">
      <div class="wl-facets" ng-show="filteringEnabled">
        <div class="wl-facets-container" ng-repeat="box in supportedTypes">
          <h6>{{box.scope}}</h6>
          <ul>
            <li class="entity" ng-repeat="entity in facets | filterEntitiesByType:box.types" ng-click="addCondition(entity)">     
                <span class="wl-label" ng-class=" { 'selected' : isInConditions(entity) }">
                  <i class="wl-checkbox"></i>
                  <i class="wl-type" ng-class="'wl-fs-' + entity.mainType"></i>  
                  {{entity.label}}
                  <span class="wl-counter">({{entity.counter}})</span>
                </span>
            </li>
          </ul>
        </div>
      </div>
      <div class="wl-posts">
        <div wl-carousel>
          <div class="wl-post wl-card" ng-repeat="post in posts" wl-carousel-pane>
            <div class="wl-card-image"> 
              <img ng-src="{{post.thumbnail}}" />
            </div>
            <div class="wl-card-title"> 
              <a ng-href="{{post.permalink}}">{{post.post_title}}</a>
            </div>
          </div>
        </div>
  
      </div>
     
    </div>
  """)
  .appendTo('#wordlift-faceted-entity-search-widget')

injector = angular.bootstrap $('#wordlift-faceted-entity-search-widget'), ['wordlift.facetedsearch.widget'] 
injector.invoke(['DataRetrieverService', '$rootScope', '$log', (DataRetrieverService, $rootScope, $log) ->
  # execute the following commands in the angular js context.
  $rootScope.$apply(->    
    DataRetrieverService.load('posts') 
    DataRetrieverService.load('facets') 
  )
])

)


