@extends('layouts.app')
@section('title', __( 'Audit Report' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Audit Report
    </h1>
</section>

<!-- Main content -->
<section class="content" ng-app="auditapp" ng-controller="auditctrl">
    <div class="col-md-12">
        <div class="row g-3">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('moduleselect',  __('Module') . ':') !!}
                    <select id="moduleselect" class="form-control select2" ng-model="module_val" style="width:100%;">
                        <option value="all">All</option>
                        @foreach($modules as $item)
                        <option value="{{$item}}">{{$item}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('eventselect',  __('Event') . ':') !!}
                    <select id="eventselect" class="form-control select2" ng-model="event_val" style="width:100%;">
                        <option value="all">All</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">

                    {!! Form::label('spr_date_filter', __('report.date_range') . ':') !!}
                    {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'spr_date_filter','ng-model' => 'date_val', 'readonly']); !!}
                </div>
            </div>
            <div class="col-md-12">
                <button class="btn btn-primary" style="" type="button" ng-click="reloadData()">Apply</button>
            </div>
        @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 shadow rounded table-responsive">
            <table class="table table-bordered table-stripeds bg-white">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>ID</th>
                        <th>Module</th>
                        <th>Event</th>
                        <th style="width:100px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat-start="item in audits">
                        <td><% item.created_at | date:"dd/MM/yyyy HH:mm:ss" %></td>
                        <td><% item.auditable_id %></td>
                        <td><% item.auditable_type %></td>
                        <td><% item.event %></td>
                        <td>
                            <div>
                                <span class="btn" ng-click="showData($index)" ng-hide="item.visibility">Show Details</span>
                                <span class="btn" ng-click="hideData($index)" ng-show="item.visibility">Hide Details</span>
                            </div>
                        </td>
                    </tr>
                    
                    <tr ng-repeat-end ng-show="item.visibility">
                        <td colspan="4">
                            <table class="table">
                                <tr>
                                    <td class="bg-danger" style="width:50% !important">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th colspan="2" style="text-align:center">Old Values</th>
                                            </tr>
                                            <tr ng-repeat="(key,value) in item.old_values">
                                                <th style="width:50% !important"><% key %></th>
                                                <td style="width:50% !important"><% value %></td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td class="bg-success" style="width:50% !important">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th colspan="2" style="text-align:center">New Values</th>
                                            </tr>
                                            <tr ng-repeat="(key,value) in item.new_values">
                                                <th style="width:50% !important"><% key %></th>
                                                <td style="width:50% !important"><% value %></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
</section>
<!-- /.content -->
@stop
@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
<script>
var angularapp = angular.module('auditapp', []);

angularapp.config(function($interpolateProvider) {
    $interpolateProvider.startSymbol('<%');
    $interpolateProvider.endSymbol('%>');
  });

angularapp.controller('auditctrl', function ($scope, $http) {
    
    var path = "/api/reports/audit";
    
    $scope.showData = function(index){
        $scope.audits[index].visibility = true;
    };
    $scope.hideData = function(index){
        $scope.audits[index].visibility = false;
    };
    $scope.module_val = "all";
    $scope.event_val = "all";
    
    $scope.reloadData = function(){
      path = "/api/reports/audit/"+$scope.module_val + "/" + $scope.event_val + "?fromto=" + $scope.date_val;
      console.log(path);
      initLoader();
    };
    var initLoader = function(){
        
        $http.get(path).then(function (response) {
            console.log(response.data);
            $scope.audits = response.data;

        }, function (reason) {
            
        });
    }
    initLoader();
});
</script>
@endsection