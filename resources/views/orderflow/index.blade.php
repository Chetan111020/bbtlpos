@extends('layouts.open_ui')
@section('title', "Order Queue")
@section('content')
<div ng-app='myapp' ng-controller='myctrl' ng-cloak>
    <div class="max-w-screen-xl px-4 mx-auto">
        <div class="w-full mb-5 grid sm:flex justify-between">
            <div class="">
                <div class="grid max-w-s grid-cols-5 gap-1 p-1 my-2 bg-gray-100 rounded-lg dark:bg-gray-600 my-auto" role="group">
                    <button type="button" ng-class="{'text-white bg-gray-900 dark:bg-gray-900 hover:bg-gray-900': flowtab == 1}" ng-click="changeFlowtab(1)" class="relative px-5 py-1.5 text-xs font-medium text-gray-900 hover:bg-gray-200 dark:text-white dark:hover:bg-gray-700 rounded-lg">
                        All
                        <div ng-class="{'bg-red-500 dark:bg-red-500': flowtab == 1}" class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-gray-400 border-2 border-white rounded-full -top-2 -end-2 dark:border-gray-900 dark:bg-gray-600"><% total_counts.all %></div>
                    </button>
                    <button type="button" ng-class="{'text-white bg-gray-900 dark:bg-gray-900 hover:bg-gray-900': flowtab == 2}" ng-click="changeFlowtab(2)" class="relative px-5 py-1.5 text-xs font-medium text-gray-900 hover:bg-gray-200 dark:text-white dark:hover:bg-gray-700 rounded-lg">
                        Received
                        <div ng-class="{'bg-red-500 dark:bg-red-500': flowtab == 2}" class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-gray-400 border-2 border-white rounded-full -top-2 -end-2 dark:border-gray-900 dark:bg-gray-600"><% total_counts.start_picking %></div>
                    </button>
                    <button type="button" ng-class="{'text-white bg-gray-900 dark:bg-gray-900 hover:bg-gray-900': flowtab == 3}" ng-click="changeFlowtab(3)" class="relative px-5 py-1.5 text-xs font-medium text-gray-900 hover:bg-gray-200 dark:text-white dark:hover:bg-gray-700 rounded-lg">
                        Picking<br/>Started
                        <div ng-class="{'bg-red-500 dark:bg-red-500': flowtab == 3}" class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-gray-400 border-2 border-white rounded-full -top-2 -end-2 dark:border-gray-900 dark:bg-gray-600"><% total_counts.continue_picking %></div>
                    </button>
                    <button type="button" ng-class="{'text-white bg-gray-900 dark:bg-gray-900 hover:bg-gray-900': flowtab == 4}" ng-click="changeFlowtab(4)" class="relative px-5 py-1.5 text-xs font-medium text-gray-900 hover:bg-gray-200 dark:text-white dark:hover:bg-gray-700 rounded-lg">
                        Picking<br/>Completed
                        <div ng-class="{'bg-red-500 dark:bg-red-500': flowtab == 4}" class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-gray-400 border-2 border-white rounded-full -top-2 -end-2 dark:border-gray-900 dark:bg-gray-600"><% total_counts.start_packing %></div>
                    </button>
                    <button type="button" ng-class="{'text-white bg-gray-900 dark:bg-gray-900 hover:bg-gray-900': flowtab == 5}" ng-click="changeFlowtab(5)" class="relative px-5 py-1.5 text-xs font-medium text-gray-900 hover:bg-gray-200 dark:text-white dark:hover:bg-gray-700 rounded-lg">
                        Packing<br/>Started
                        <div ng-class="{'bg-red-500 dark:bg-red-500': flowtab == 5}" class="absolute inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-gray-400 border-2 border-white rounded-full -top-2 -end-2 dark:border-gray-900 dark:bg-gray-600"><% total_counts.continue_packing %></div>
                    </button>
                </div>
                <div class="flex">
                    <div class="mt-2 grid max-w-s grid-cols-3 gap-1 p-1 bg-gray-100 rounded-lg dark:bg-gray-600 my-auto" role="group">
                        <button type="button" ng-class="{'text-white bg-gray-900 hover:bg-gray-700': queuetab == 0}" ng-click="changeQueue(0)" class="relative px-5 py-1.5 text-xs font-medium text-gray-900 hover:bg-gray-200 dark:text-white dark:hover:bg-gray-700 rounded-lg">
                            Main Queue
                        </button>
                        <button type="button" ng-class="{'text-blue-500 bg-blue-100 dark:bg-blue-900': queuetab == 1}" ng-click="changeQueue(1)" class="relative px-5 py-1.5 text-xs font-medium text-gray-900 hover:bg-blue-200 dark:text-white dark:hover:bg-gray-700 rounded-lg">
                            Queue 1
                        </button>
                        <button type="button" ng-class="{'text-emerald-500 bg-emerald-100 dark:bg-emerald-900 ': queuetab == 2}" ng-click="changeQueue(2)" class="relative px-5 py-1.5 text-xs font-medium text-gray-900 hover:bg-emerald-200 dark:text-white dark:hover:bg-gray-700 rounded-lg">
                            Queue 2
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center flex-col">
                <label for="simple-search" class="sr-only">Search</label>
                <div class="relative w-full mt-4">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500 dark:text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                    <input type="text" id="simple-search" ng-model="search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Search orders..." required>
                </div>
                <small style="font-size: smaller;" class="w-full text-end text-gray-700 dark:text-gray-200">
                    Showing <% filtered_orders.length %> of <% orders.length %>
                    <span ng-show="refreshing_orders">
                        <svg aria-hidden="true" role="status" class="inline w-3 h-3 ms-3 text-gray-200 animate-spin dark:text-gray-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="#1C64F2"/>
                        </svg>
                        Refreshing...
                    </span>
                </small>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 3xl:grid-cols-6 mx-4 mb-4">
        <div ng-repeat='o in orders | filter:search | filter:customFilter as filtered_orders' ng-class="{'border-4 border-rose-600 dark:border-rose-600 animate-bounce-slownoooo': o.priority_order == 1}" class="hover:grid-flow-row p-4 bg-white border border-gray-200 rounded-lg shadow sm:p-6 dark:bg-gray-800 dark:border-gray-700 flex flex-col">

            <span ng-show="o.priority_order == 1" class="bg-red-100 text-red-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300" style="margin: -1rem -1rem 0 auto;">Priority</span>

            <div class="flex items-baseline text-gray-900 dark:text-white">
                <span class="text-2xl font-semibold">INVOICE #<% o.invoice_no %></span>
            </div>

            <h6 class="mt-4 text-lg font-medium text-gray-500 dark:text-gray-400" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><% o.customer_name %></h5>
            <div class=" mt-1 flex">
                <span class="text-base font-normal leading-tight text-gray-500 dark:text-gray-400"><% o.city != "" ? o.city : '-- ' %>, <% o.state != "" ? o.state : '--' %></span>
            </div>

            <div class="my-3">
                <div class="flex mt-3 mb-4">
                    <div class="flex space-x-3 item-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="flex-shrink-0 w-5 h-5 text-blue-600 dark:text-blue-500">
                            <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 017.5 3v1.5h9V3A.75.75 0 0118 3v1.5h.75a3 3 0 013 3v11.25a3 3 0 01-3 3H5.25a3 3 0 01-3-3V7.5a3 3 0 013-3H6V3a.75.75 0 01.75-.75zm13.5 9a1.5 1.5 0 00-1.5-1.5H5.25a1.5 1.5 0 00-1.5 1.5v7.5a1.5 1.5 0 001.5 1.5h13.5a1.5 1.5 0 001.5-1.5v-7.5z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-base font-normal leading-tight text-gray-500 dark:text-gray-400"><% o.transaction_date %></span>
                    </div>
                </div>
                <div class="flex mb-4">
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
                <div class="flex mb-3">
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

            <div ng-show="o.staff_note != null" class="flex p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-blue-900 dark:text-blue-400" role="alert">
                <svg class="flex-shrink-0 inline w-4 h-4 me-2 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                <span class="sr-only">Staff Note</span>
                <div>
                    <p class="font-medium pl-2">Note: <% o.staff_note %></p>
                </div>
            </div>
            <div ng-show="o.additional_notes != null" class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                <svg class="flex-shrink-0 inline w-4 h-4 me-2 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                <span class="sr-only">Order Note</span>
                <div>
                    <p class="font-medium pl-2">Order Note: <% o.additional_notes %></p>
                </div>
            </div>

            <div style="margin-top: auto;" class="grid">
                <div class="flex w-full mb-2">
                    <button ng-click="updateQueue(o,1)" ng-class="{'bg-blue-100': (o.queue_user_id == 1)}" class="w-full flex text-blue-500 border border-blue-300 focus:outline-none hover:bg-blue-200 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm p-2.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 me-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m12.75 15 3-3m0 0-3-3m3 3h-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Q1
                    </button>
                    <button ng-click="updateQueue(o,2)" ng-class="{'bg-emerald-100': (o.queue_user_id == 2)}" class="ms-2 w-full flex text-emerald-500 border border-emerald-300 focus:outline-none hover:bg-emerald-200 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm p-2.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 me-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m12.75 15 3-3m0 0-3-3m3 3h-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Q2
                    </button>

                    <button ng-hide="o.info_loader" ng-click="viewDetails(o)" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm p-2.5 ms-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <button ng-show="o.info_loader" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm p-2.5 ms-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">
                        <svg aria-hidden="true" role="status" class="inline w-5 h-5 text-gray-200 animate-spin dark:text-gray-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="#1C64F2"/>
                        </svg>
                    </button>

                    <a target="_blank" href="/sells/pos/packing_slip_blank/<% o.id %>" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm p-2.5 ms-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                            <path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153A2.212 2.212 0 0 1 18 8.653v4.097A2.25 2.25 0 0 1 15.75 15h-.241l.305 1.984A1.75 1.75 0 0 1 14.084 19H5.915a1.75 1.75 0 0 1-1.73-2.016L4.492 15H4.25A2.25 2.25 0 0 1 2 12.75V8.653c0-1.082.775-2.034 1.874-2.198.374-.056.75-.107 1.127-.153L5 6.25v-3.5Zm8.5 3.397a41.533 41.533 0 0 0-7 0V2.75a.25.25 0 0 1 .25-.25h6.5a.25.25 0 0 1 .25.25v3.397ZM6.608 12.5a.25.25 0 0 0-.247.212l-.693 4.5a.25.25 0 0 0 .247.288h8.17a.25.25 0 0 0 .246-.288l-.692-4.5a.25.25 0 0 0-.247-.212H6.608Z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
                <a target="_blank" href="/modules/kitchen/<% o.flow_status %>/order/<% o.id %>" style="line-height: 1.5;" class="text-white bg-<% o.acc_color %>-600 hover:bg-<% o.acc_color %>-700 focus:ring-4 focus:outline-none focus:ring-<% o.acc_color %>-200 dark:focus:ring-<% o.acc_color %>-900 font-medium rounded-lg text-sm px-5 py-2.5 inline-flex justify-center w-full text-center">
                    <% o.flow_label %>
                </a>
            </div>

        </div>
    </div>

    <button style="display: none;" id="modal_hook_btn" data-modal-target="extralarge-modal" data-modal-toggle="extralarge-modal" class="block w-full md:w-auto text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" type="button">
    </button>
    <!-- Extra Large Modal -->
    <div id="extralarge-modal" tabindex="-1" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-7xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 zoomIn">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-medium text-gray-900 dark:text-white">
                        Invoice <span class="dark:text-<% order_info.accent_color %>-500 text-transparent bg-clip-text bg-gradient-to-r to-<% order_info.accent_color %>-600 from-<% order_info.accent_color %>-900">#<% order_info.invoice_no %></span>
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="extralarge-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-4 md:p-5 space-y-4 pt-0 md:pt-0">
                    <div>
                        <div class="flex justify-between items-center">

                            <h5 class="text-xl font-bold dark:text-white">
                                <span class="dark:text-<% order_info.accent_color %>-500 text-transparent bg-clip-text bg-gradient-to-r to-<% order_info.accent_color %>-600 from-<% order_info.accent_color %>-900"><% order_info.contact.name %></span>
                            </h5>

                            <span class="flex items-center justify-between bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-blue-400 border border-blue-400">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 me-2">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd" />
                                </svg>
                                <% order_info.transaction_date %>
                            </span>

                        </div>
                        <div class="flex items-center pt-2 dark:text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 me-2">
                                <path fill-rule="evenodd" d="m9.69 18.933.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 0 0 .281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 1 0 3 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 0 0 2.273 1.765 11.842 11.842 0 0 0 .976.544l.062.029.018.008.006.003ZM10 11.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" clip-rule="evenodd" />
                            </svg>
                            <span style="line-"><% order_info.contact.address %></span>
                        </div>
                        <div class="flex pt-2 dark:text-gray-200">
                            <div class="flex items-center pe-4">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 me-2">
                                    <path d="M8 16.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z" />
                                    <path fill-rule="evenodd" d="M4 4a3 3 0 0 1 3-3h6a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V4Zm4-1.5v.75c0 .414.336.75.75.75h2.5a.75.75 0 0 0 .75-.75V2.5h1A1.5 1.5 0 0 1 14.5 4v12a1.5 1.5 0 0 1-1.5 1.5H7A1.5 1.5 0 0 1 5.5 16V4A1.5 1.5 0 0 1 7 2.5h1Z" clip-rule="evenodd" />
                                </svg>
                                <span style="line-"><% order_info.contact.mobile %></span>
                            </div>
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 me-2">
                                    <path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.148a1.5 1.5 0 0 1 1.465 1.175l.716 3.223a1.5 1.5 0 0 1-1.052 1.767l-.933.267c-.41.117-.643.555-.48.95a11.542 11.542 0 0 0 6.254 6.254c.395.163.833-.07.95-.48l.267-.933a1.5 1.5 0 0 1 1.767-1.052l3.223.716A1.5 1.5 0 0 1 18 15.352V16.5a1.5 1.5 0 0 1-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 0 1 2.43 8.326 13.019 13.019 0 0 1 2 5V3.5Z" clip-rule="evenodd" />
                                </svg>
                                <span style="line-"><% order_info.contact.landline %></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-6 pt-2 my-4">

                            <div class="rounded-lg bg-emerald-100 p-3">
                                <strong class="mb-2">Received</strong><br/>
                                <span class="text-gray-900">by <% order_info.added_by %></span>
                            </div>
                            <div class="rounded-lg bg-emerald-100 p-3">
                                <strong class="mb-2">Delivery Method</strong><br/>
                                <span class="text-gray-900"><% order_info.delivery_method %></span>
                            </div>
                            <div class="rounded-lg bg-<% order_info.picking_color %>-100 p-3">
                                <strong class="mb-2"><% order_info.picking_status %></strong><br/>
                                <span class="text-gray-900">by <% order_info.picked_by %></span>
                            </div>
                            <div class="rounded-lg bg-<% order_info.packing_color %>-100 p-3">
                                <strong class="mb-2"><% order_info.packing_status %></strong><br/>
                                <span class="text-gray-900">by <% order_info.packed_by %></span>
                            </div>
                            <div class="rounded-lg bg-<% order_info.delivery_color %>-100 p-3">
                                <strong class="mb-2"><% order_info.delivery_status %></strong><br/>
                                <span class="text-gray-900"><% order_info.delivered_by %></span>
                            </div>
                            <div class="rounded-lg bg-<% order_info.accent_color %>-100 p-3">
                                <strong class="mb-2">Payment Status</strong><br/>
                                <span class="text-gray-900 flex items-center">
                                    <% order_info.payment_status %>
                                    {{-- 🤣🥲😭😱☠️ --}}
                                    <svg ng-show="order_info.payment_status == 'Paid'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 ms-2">
                                        <path fill-rule="evenodd" d="M16.403 12.652a3 3 0 0 0 0-5.304 3 3 0 0 0-3.75-3.751 3 3 0 0 0-5.305 0 3 3 0 0 0-3.751 3.75 3 3 0 0 0 0 5.305 3 3 0 0 0 3.75 3.751 3 3 0 0 0 5.305 0 3 3 0 0 0 3.751-3.75Zm-2.546-4.46a.75.75 0 0 0-1.214-.883l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>

                        </div>


                        <div class="relative overflow-x-auto pt-4">
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="pe-6 py-3 text-right w-1">
                                            #
                                        </th>
                                        <th scope="col" class="px-6 py-3">
                                            Product name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right">
                                            QTY
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right">
                                            Price
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right">
                                            Tax
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right">
                                            Amount
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr ng-repeat='line in order_info.sell_lines' ng-class="{'bg-red-50': line.quantity == 0}" class="border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-6 py-2 text-right">
                                            <% $index + 1 %>
                                        </td>
                                        <th scope="row" class="px-6 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            <% line.product.name %> - <% line.product.sku %>
                                        </th>
                                        <td class="px-6 py-2 text-right">
                                            <% line.quantity %>
                                        </td>
                                        <td class="px-6 py-2 text-right">
                                            <% line.unit_price %>
                                        </td>
                                        <td class="px-6 py-2 text-right">
                                            <% line.line_tax %>
                                        </td>
                                        <td class="px-6 py-2 text-right">
                                            <% line.line_total %>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="relative overflow-x-auto mt-4">
                            <table class="w-2/5 ms-auto text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <tbody>
                                    <tr class="bg-white dark:bg-gray-800">
                                        <th scope="row" class="px-6 pt-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            Subtotal (Excl. Tax)
                                        </th>
                                        <td class="px-6 pt-3 text-right">
                                            $ <% order_info.subtotal %>
                                        </td>
                                    </tr>
                                    <tr class="bg-white dark:bg-gray-800">
                                        <th scope="row" class="px-6 pt-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            <div class="flex justify-between">
                                                <span>Total Tax</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                                    <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                                                </svg>
                                            </div>
                                        </th>
                                        <td class="px-6 pt-3 text-right">
                                            $ <% order_info.total_tax %>
                                        </td>
                                    </tr>
                                    <tr class="bg-white dark:bg-gray-800">
                                        <th scope="row" class="px-6 pt-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            <div class="flex justify-between">
                                                <span>Shipping Charges</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                                    <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                                                </svg>
                                            </div>
                                        </th>
                                        <td class="px-6 pt-3 text-right">
                                            $ <% order_info.shipping_charges %>
                                        </td>
                                    </tr>
                                    <tr class="bg-white dark:bg-gray-800">
                                        <th scope="row" class="px-6 pt-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            <div class="flex justify-between">
                                                <span>Discount</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                                    <path fill-rule="evenodd" d="M4 10a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H4.75A.75.75 0 0 1 4 10Z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </th>
                                        <td class="px-6 pt-3 text-right">
                                            <% order_info.total_discount %>
                                        </td>
                                    </tr>
                                    <tr class="bg-white dark:bg-gray-800">
                                        <th scope="row" class="px-6 pt-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            Invoice Total
                                        </th>
                                        <td class="px-6 pt-3 text-right">
                                            $ <% order_info.final_total %>
                                        </td>
                                    </tr>
                                    <tr class="bg-white dark:bg-gray-800">
                                        <th scope="row" class="px-6 pt-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            Total Paid
                                        </th>
                                        <td class="px-6 pt-3 text-right">
                                            $ <% order_info.total_paid %>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="font-semibold text-gray-900 dark:text-white dark:bg-gray-900">
                                        <th scope="row" class="px-6 py-3 text-base">Total Due</th>
                                        <td class="px-6 py-2 text-right">$ <% order_info.total_due %></td>
                                    </tr>
                                </tfoot>
                            </table>
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

angularapp.controller('myctrl', function($scope, $location, $http) {
    $http.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

    //collections
    $scope.flowtab = 1;
    $scope.queuetab = 0;
    $scope.have_data = false;
    $scope.orders = [];
    $scope.filtered_orders = [];
    $scope.order_info = [];
    $scope.search = '';
    $scope.total_counts = {};
    $scope.last_view_model_id = 0;
    $scope.refreshing_orders = false;

    //get init
    var initLoader = function(){
        var tab = $location.search().tab;
        var queue = $location.search().queue;
        if (tab) {
            $scope.flowtab = tab;
        }
        if (queue) {
            $scope.queuetab = queue;
        }
        $scope.getOrders();
    }

    $scope.getOrders = function(){
        $scope.have_data = false;
        $scope.refreshing_orders = true;
        $http.get('{{route("orderflow.getorders")}}').then(function (response) {
            $scope.orders = response.data.orders;
            $scope.total_counts = response.data.total_counts;
            $scope.have_data = true;
            $scope.refreshing_orders = false;
        }, function (reason) {
            toastr2.error('Server Error');
            $scope.have_data = true;
            $scope.refreshing_orders = false;
        });
    }

    $scope.customFilter = function(item) {
        var filter1 = $scope.flowtab == 1
            || ($scope.flowtab == 2 && item.order_picking_status == 0)
            || ($scope.flowtab == 3 && item.order_picking_status == 1)
            || ($scope.flowtab == 4 && item.order_picking_status == 2 && item.order_packing_status == 0)
            || ($scope.flowtab == 5 && item.order_picking_status == 2 && item.order_packing_status == 1);

        var filter2 = $scope.queuetab == 0
            || ($scope.queuetab == item.queue_user_id);

        return filter1 && filter2;
    };

    $scope.viewDetails = function(order){
        order.info_loader = true;
        $scope.last_view_model_id = order.id;
        $http.get('/order-flow/view/' + order.id).then(function (response) {
            if(response.data.success == 1){
                if($scope.last_view_model_id == order.id){
                    $scope.order_info = response.data.order_info;
                    $('#modal_hook_btn').trigger('click');
                }
            }
            else{
                toastr2.error('Something went wrong');
            }
            order.info_loader = false;
        }, function (reason) {
            toastr2.error('Server Error');
            order.info_loader = false;
        });
    };

    $scope.updateQueue = function(order,queue_id){
        var op = 'add';
        if(order.queue_user_id == queue_id){
            op = 'remove';
            order.queue_user_id = 0;
        }
        else{
            order.queue_user_id = queue_id;
        }
        $http.get('/order-flow/mylist/add/' + order.id + '?op=' + op + '&queueval=' + queue_id).then(function (response) {
            if(response.data.success){
                toastr2.success(response.data.msg);
            }
            else{
                toastr2.error(response.data.msg);
            }
        }, function (reason) {
            toastr2.error('Server Error');
        });

    };

    $scope.changeFlowtab = function(tab){
        $scope.flowtab = tab;
        $location.search('tab', tab);
    }
    $scope.changeQueue = function(queue){
        $scope.queuetab = queue;
        $location.search('queue', queue);
    }

    initLoader();

    setInterval(() => {
        $scope.getOrders();
    }, 30000);
});
</script>
@endsection
