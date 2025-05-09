@extends('layouts.app')
@section('title', __('product.add_new_product'))
@section('content')
    <!-- Content Header (Page header) -->
        @if (auth()->user()->id != 6)
       <section class="content-header">
        <h1>@lang('product.add_new_product')</h1> 
     <!-- <ol class="breadcrumb">
           <li><a href="#"><i class="fa fa-dashboard"></i>Level</a></li>
            <li class="active">Here</li>
            </ol> -->
       </section>
         @endif
    @if (auth()->user()->id == 6)
     <section class="content-header">
        <div style="display:flex; justify-content:space-between;"> 
        <h1 style="font-size:24px; margin:0;">@lang('product.add_new_product')</h1>
        <style>
            .btn-goth {
                background: #b8c7ce;
                border-color: #b8c7ce;
                color: #3c3c4e;
            }
            .btn-goth:hover {
                background: #3c3c4e;
                border-color: #3c3c4e;
                color: #fff;
            }
            .btn-goth:focus {
                background: #286090;
                border-color: #122b40;
                color: #fff;
            }
        </style>
            <button type="button" class="btn btn-goth"  onclick="window.open('/magicsnap/dash', '_blank')"> Add From MagicSnap (Beta)</button>
        </div>
    </section> 
    @endif

    @include('product.create_product_model')
    <style>
        .input-upper-case{
            text-transform: uppercase;
        }
    </style>
@endsection