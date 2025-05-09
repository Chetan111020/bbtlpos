@extends('layouts.open_ui')
@section('title', "Order Flow")
@section('content')
<div ng-app='myapp' ng-controller='myctrl'>
    <div class="max-w-screen-xl px-4 mx-auto">
        <div class="w-full mb-5 flex justify-between">
            <div class="grid max-w-s grid-cols-4 gap-1 p-1 my-2 bg-gray-100 rounded-lg dark:bg-gray-600" role="group">
                <button type="button" ng-class="{'text-white bg-gray-900 dark:bg-gray-300 dark:text-gray-900 hover:bg-gray-900': flowtab == 1}" ng-click="flowtab=1" class="relative px-5 py-1.5 text-xs font-medium text-gray-900 hover:bg-gray-200 dark:text-white dark:hover:bg-gray-700 rounded-lg">
                    Processing
                    <div ng-class="{'bg-red-500': flowtab == 1}" class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-gray-400 border-2 border-white rounded-full -top-2 -end-2 dark:border-gray-900"><% count_pack %></div>
                </button>
                <button type="button" ng-class="{'text-white bg-gray-900 dark:bg-gray-300 dark:text-gray-900 hover:bg-gray-900': flowtab == 2}" ng-click="flowtab=2" class="relative px-5 py-1.5 text-xs font-medium text-gray-900 hover:bg-gray-200 dark:text-white dark:hover:bg-gray-700 rounded-lg">
                    Picked
                    <div ng-class="{'bg-red-500': flowtab == 2}" class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-gray-400 border-2 border-white rounded-full -top-2 -end-2 dark:border-gray-900"><% count_pick %></div>
                </button>
                <button type="button" ng-class="{'text-white bg-gray-900 dark:bg-gray-300 dark:text-gray-900 hover:bg-gray-900': flowtab == 3}" ng-click="flowtab=3" class="relative px-5 py-1.5 text-xs font-medium text-gray-900 hover:bg-gray-200 dark:text-white dark:hover:bg-gray-700 rounded-lg">
                    Edited
                    <div ng-class="{'bg-red-500': flowtab == 3}" class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-gray-400 border-2 border-white rounded-full -top-2 -end-2 dark:border-gray-900"><% count_pack %></div>
                </button>
                <button type="button" ng-class="{'text-white bg-gray-900 dark:bg-gray-300 dark:text-gray-900 hover:bg-gray-900': flowtab == 4}" ng-click="flowtab=4" class="relative px-5 py-1.5 text-xs font-medium text-gray-900 hover:bg-gray-200 dark:text-white dark:hover:bg-gray-700 rounded-lg">
                    Out of Stock
                    <div ng-class="{'bg-red-500': flowtab == 4}" class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-gray-400 border-2 border-white rounded-full -top-2 -end-2 dark:border-gray-900"><% count_pack %></div>
                </button>
            </div>

            <div class="flex items-center">
                <label for="simple-search" class="sr-only">Search</label>
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500 dark:text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                    <input type="text" id="simple-search" ng-model="search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Search orders..." required>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 mb-4">
            <div ng-repeat='o in orders | filter:customFilter | limitTo:1' ng-class="{'border-4 border-rose-600': o.priority_order == 1}" class="hover:grid-flow-row p-4 bg-white border border-gray-200 rounded-lg shadow sm:p-6 dark:bg-gray-800 dark:border-gray-700 flex flex-col">

                <span ng-show="o.priority_order == 1" class="bg-red-100 text-red-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300" style="margin: -1rem -1rem 0 auto;">Priority</span>

                <div class="flex mb-4">
                    <div class="w-1/2">
                        <div class="flex items-baseline text-gray-900 dark:text-white">
                            <span class="text-3xl font-semibold">INVOICE #<% o.invoice_no %></span>
                        </div>

                        <h6 class="mt-4 text-xl font-medium text-gray-500 dark:text-gray-400" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><% o.customer_name %></h5>
                        <div class=" mt-1 flex">
                            <span class="text-base font-normal leading-tight text-gray-500 dark:text-gray-400"><% o.city %>, <% o.state %></span>
                        </div>

                    </div>
                    <div class="w-1/2">
                        <div class="flex mt-4 mb-2">
                            <div class="flex space-x-3 item-center">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="flex-shrink-0 w-5 h-5 text-blue-600 dark:text-blue-500">
                                    <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 017.5 3v1.5h9V3A.75.75 0 0118 3v1.5h.75a3 3 0 013 3v11.25a3 3 0 01-3 3H5.25a3 3 0 01-3-3V7.5a3 3 0 013-3H6V3a.75.75 0 01.75-.75zm13.5 9a1.5 1.5 0 00-1.5-1.5H5.25a1.5 1.5 0 00-1.5 1.5v7.5a1.5 1.5 0 001.5 1.5h13.5a1.5 1.5 0 001.5-1.5v-7.5z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-base font-normal leading-tight text-gray-500 dark:text-gray-400"><% o.transaction_date %></span>
                            </div>
                        </div>
                        <div class="flex mb-2">
                            <div class="flex space-x-3 item-center w-1/2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="flex-shrink-0 w-5 h-5 text-blue-600 dark:text-blue-500">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-base font-normal leading-tight text-gray-500 dark:text-gray-400">$<% o.final_total %></span>
                            </div>
                            <div class="flex space-x-3 item-center w-1/2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="flex-shrink-0 w-5 h-5 text-blue-600 dark:text-blue-500">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                </svg>
                                <span class="text-base font-normal leading-tight text-gray-500 dark:text-gray-400"><% o.total_items %> Items</span>
                            </div>
                        </div>
                        <div class="flex">
                            <div class="flex space-x-3 item-center w-1/2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="flex-shrink-0 w-5 h-5 text-blue-600 dark:text-blue-500">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5V6.108c0-1.135.845-2.098 1.976-2.192.373-.03.748-.057 1.123-.08M15.75 18H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08M15.75 18.75v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5A3.375 3.375 0 006.375 7.5H5.25m11.9-3.664A2.251 2.251 0 0015 2.25h-1.5a2.251 2.251 0 00-2.15 1.586m5.8 0c.065.21.1.433.1.664v.75h-6V4.5c0-.231.035-.454.1-.664M6.75 7.5H4.875c-.621 0-1.125.504-1.125 1.125v12c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V16.5a9 9 0 00-9-9z" />
                                </svg>
                                <span class="text-base font-normal leading-tight text-gray-500 dark:text-gray-400">By <% o.first_name %></span>
                            </div>
                            <div class="flex space-x-3 item-center w-1/2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="flex-shrink-0 w-5 h-5 text-blue-600 dark:text-blue-500">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                </svg>
                                <span class="text-base font-normal leading-tight text-gray-500 dark:text-gray-400"><% o.delivery_method %></span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">

                    <div class="flex mb-auto p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
                        <svg class="flex-shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                        </svg>
                        <span class="sr-only">Staff Info</span>
                        <div>
                            <p class="font-medium pl-2"><% o.p_status %></p>
                        </div>
                    </div>

                    <div ng-show="o.staff_note != null" class="flex p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
                        <svg class="flex-shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                        </svg>
                        <span class="sr-only">Staff Note</span>
                        <div>
                            <p class="font-medium pl-2">Staff Note: <% o.staff_note %></p>
                        </div>
                    </div>
                    <div ng-show="o.additional_notes != null" class="flex p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                        <svg class="flex-shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                        </svg>
                        <span class="sr-only">Order Note</span>
                        <div>
                            <p class="font-medium pl-2">Order Note: <% o.additional_notes %></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>

@endsection
@section('javascript')
<script>
var angularapp = angular.module('myapp', []);

angularapp.config(function($interpolateProvider) {
    $interpolateProvider.startSymbol('<%');
    $interpolateProvider.endSymbol('%>');
});

angularapp.controller('myctrl', function($scope, $http) {
    $http.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

    //collections
    $scope.flowtab = 1;
    $scope.have_data = false;
    $scope.orders = [];
    $scope.search = '';

    $scope.count_pick = 0;
    $scope.count_pack = 0;

    //get init
    var initLoader = function(){
        $scope.getOrders();
    }

    $scope.getOrders = function(){
        $scope.have_data = false;
        $http.get('{{route("orderflow.getorders")}}').then(function (response) {
            console.log(response.data);
            if(response.data.orders.length > 0){
                $scope.orders = response.data.orders;
                $scope.count_pick = response.data.total_counts['pick'];
                $scope.count_pack = response.data.total_counts['pack'];
                $scope.have_data = true;
            }
            else{
                // toastr.success('Something went wrong');
            }
        }, function (reason) {
            // toastr.warning(reason.data.error);
            $scope.have_data = true;
        });
    }

    $scope.customFilter = function(item) {
        if(($scope.flowtab == 2 && item.flow_status != 'picking') || ($scope.flowtab == 3 && item.flow_status != 'packing')){
            return false;
        }
        $scope.search = ($scope.search === undefined) ? '' : $scope.search;
        return item.customer_name.toLowerCase().includes($scope.search.toLowerCase()) ||
                item.invoice_no.toLowerCase().includes($scope.search.toLowerCase());
    };

    initLoader();

    setInterval(() => {
        $scope.getOrders();
    }, 30000);
});
</script>
@endsection
