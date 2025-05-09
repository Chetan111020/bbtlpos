@extends('layouts.app')
@section('title', __('lang_v1.my_profile'))
@section('css')
<style>
input[type=checkbox]{
	height: 0;
	width: 0;
	visibility: hidden;
}

.tgl {
	cursor: pointer;
	text-indent: -9999px;
	width: 50px;
	height: 28px;
	background: grey;
	display: block;
	border-radius: 100px;
	position: relative;
    margin: 0;
}

.tgl:after {
	content: '';
	position: absolute;
	top: 5px;
	left: 5px;
	width: 18px;
	height: 18px;
	background: #fff;
	border-radius: 90px;
	transition: 0.3s;
}

input:checked + .tgl {
	background: #346be2;
}

input:checked + .tgl:after {
	left: calc(100% - 5px);
	transform: translateX(-100%);
}

</style>
@endsection
@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('lang_v1.my_profile')</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @if (auth()->user()->roles()->whereIn('id',[9,15])->exists())
        @php
            if (empty($passcode_feature)){
                $passcode_feature = 1;
            }
            if (empty($custom_jadoo_popup)){
                $custom_jadoo_popup = 0;
            }
        @endphp
        <div>
        {{-- <div class="col-xs-6 bg-white" style="display: flex;margin-bottom:15px;padding:10px">
            <div class="col-xs-6" style="display:flex;align-items:center;">
                <strong>Login Page Passcode Feature</strong>
            </div>
            <div class="col-xs-6" style="display:flex;justify-content:end;align-items:center;">
                <input type="checkbox" id="pass_feat" {{ $passcode_feature == 1 ? 'checked' : '' }} /><label for="pass_feat" class="tgl">Toggle</label>
            </div>
        </div> --}}

        <div class="col-xs-6 bg-white" style="display: flex;margin-bottom:15px;padding:10px">
            <div class="col-xs-6" style="display:flex;align-items:center;">
                <div>
                    <strong>Jadoo Popup</strong>
                    <br/><small>(Not Applicable on Taxed or Tier 3 invoices)</small>
                </div>
            </div>
            <div class="col-xs-6" style="display:flex;justify-content:end;align-items:center;">
                <input type="checkbox" id="jadoo_popup_feat" {{ $custom_jadoo_popup == 1 ? 'checked' : '' }} /><label for="jadoo_popup_feat" class="tgl">Toggle</label>
            </div>
        </div>
        </div>
    @endif

{!! Form::open(['url' => action('UserController@updatePassword'), 'method' => 'post', 'id' => 'edit_password_form',
            'class' => 'form-horizontal' ]) !!}
<div class="row">
    <div class="col-sm-12">
        <div class="box box-solid"> <!--business info box start-->
            <div class="box-header">
                <div class="box-header">
                    <h3 class="box-title"> @lang('user.change_password')</h3>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group">
                    {!! Form::label('current_password', __('user.current_password') . ':', ['class' => 'col-sm-3 control-label']) !!}
                    <div class="col-sm-9">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-lock"></i>
                            </span>
                            {!! Form::password('current_password', ['class' => 'form-control','placeholder' => __('user.current_password'), 'required']); !!}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('new_password', __('user.new_password') . ':', ['class' => 'col-sm-3 control-label']) !!}
                    <div class="col-sm-9">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-lock"></i>
                            </span>
                            {!! Form::password('new_password', ['class' => 'form-control','placeholder' => __('user.new_password'), 'required']); !!}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('confirm_password', __('user.confirm_new_password') . ':', ['class' => 'col-sm-3 control-label']) !!}
                    <div class="col-sm-9">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-lock"></i>
                            </span>
                            {!! Form::password('confirm_password', ['class' => 'form-control','placeholder' =>  __('user.confirm_new_password'), 'required']); !!}
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary pull-right">@lang('messages.update')</button>
            </div>
        </div>
    </div>
</div>
{!! Form::close() !!}
{!! Form::open(['url' => action('UserController@updateProfile'), 'method' => 'post', 'id' => 'edit_user_profile_form', 'files' => true ]) !!}
<div class="row">
    <div class="col-sm-8">
        <div class="box box-solid"> <!--business info box start-->
            <div class="box-header">
                <div class="box-header">
                    <h3 class="box-title"> @lang('user.edit_profile')</h3>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group col-md-2">
                    {!! Form::label('surname', __('business.prefix') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-info"></i>
                        </span>
                        {!! Form::text('surname', $user->surname, ['class' => 'form-control','placeholder' => __('business.prefix_placeholder')]); !!}
                    </div>
                </div>
                <div class="form-group col-md-5">
                    {!! Form::label('first_name', __('business.first_name') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-info"></i>
                        </span>
                        {!! Form::text('first_name', $user->first_name, ['class' => 'form-control','placeholder' => __('business.first_name'), 'required']); !!}
                    </div>
                </div>
                <div class="form-group col-md-5">
                    {!! Form::label('last_name', __('business.last_name') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-info"></i>
                        </span>
                        {!! Form::text('last_name', $user->last_name, ['class' => 'form-control','placeholder' => __('business.last_name')]); !!}
                    </div>
                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('email', __('business.email') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-info"></i>
                        </span>
                        {!! Form::email('email',  $user->email, ['class' => 'form-control','placeholder' => __('business.email') ]); !!}
                    </div>
                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('language', __('business.language') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-info"></i>
                        </span>
                        {!! Form::select('language',$languages, $user->language, ['class' => 'form-control select2']); !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        @component('components.widget', ['title' => __('lang_v1.profile_photo')])
            @if(!empty($user->media))
                <div class="col-md-12 text-center">
                    {!! $user->media->thumbnail([150, 150], 'img-circle') !!}
                </div>
            @endif
            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label('profile_photo', __('lang_v1.upload_image') . ':') !!}
                    {!! Form::file('profile_photo', ['id' => 'profile_photo', 'accept' => 'image/*']); !!}
                    <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])</p></small>
                </div>
            </div>
        @endcomponent
    </div>
</div>
@include('user.edit_profile_form_part', ['bank_details' => !empty($user->bank_details) ? json_decode($user->bank_details, true) : null])
<div class="row">
    <div class="col-md-12">
        <button type="submit" class="btn btn-primary pull-right">@lang('messages.update')</button>
    </div>
</div>
{!! Form::close() !!}

</section>
<!-- /.content -->
@endsection
@section('javascript')
<script>
    $(document).ready(function(){
        // $(document).on('change', '#pass_feat', function(){
        //     var set_value = $(this).is(':checked') ? 1 : 0;
        //     $.ajax({
        //         method: 'GET',
        //         url: '/set-key-passcode',
        //         data: {
        //             set_value: set_value
        //         },
        //         dataType: 'json',
        //         success: function(result) {
        //             var pass_status = result == 0 ? 'off' : 'on';
        //             toastr.success('Passcode feature set ' + pass_status);
        //         }
        //     });
        // });

        $(document).on('change', '#jadoo_popup_feat', function(){
            var set_value = $(this).is(':checked') ? 1 : 0;
            $.ajax({
                method: 'GET',
                url: '/set-key-custom',
                data: {
                    set_key: 'jadoo_popup',
                    set_value: set_value
                },
                dataType: 'json',
                success: function(result) {
                    var pass_status = result == 0 ? 'off' : 'on';
                    toastr.success('Feature set ' + pass_status);
                }
            });
        });
    });
</script>
@endsection