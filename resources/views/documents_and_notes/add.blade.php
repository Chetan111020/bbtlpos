@extends('layouts.app')
@section('title', __('lang_v1.sell_return'))

@section('content')
<div class="modal-dialog modal-lg" role="document">
    {!! Form::open(['action' => 'DocumentAndNoteController@store', 'id' => 'docus_notes_formz', 'method' => 'post']) !!}
    <div class="modal-content">
        <div class="modal-header">
            <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button> -->
            <h4 class="modal-title">
                @lang('lang_v1.add_note')
            </h4>
        </div>
        <!-- model id like project_id, user_id -->
        <!-- model name like App\User -->
        {!! Form::hidden('notable_type', "App\Contact", ['class' => 'form-control']) !!}
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="customer_name">Suppliers Name.:</label>
                        {!! Form::select('notable_id',$customers,null, ['id' =>'getCustomer','class' => 'form-control select2','placeholder' => 'Please Select']); !!}
                        {{--<input class="form-control" name="customer_name" type="text" id="customer_name">--}}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                   <div class="form-group">
                        {!! Form::label('heading', __('lang_v1.heading') . ':*' )!!}
                        {!! Form::text('heading', null, ['class' => 'form-control', 'required' ]) !!}
                   </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('description', __('lang_v1.description') . ':') !!}
                        {!! Form::textarea('description', null, ['class' => 'form-control ', 'id' => 'docs_note_description']); !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="fileupload">
                            @lang('lang_v1.documents'):
                        </label>
                        <div class="dropzone" id="docusUpload"></div>
                    </div>
                    <input type="hidden" id="docus_notes_media" name="file_name[]" value="">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_private" value="1"> @lang('lang_v1.is_private')
                                <i class="fa fa-info-circle" data-toggle="tooltip" title="@lang('lang_v1.note_will_be_visible_to_u_only')"></i>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary btn-sm">
                @lang('messages.save')
            </button>
            <a class="btn btn-default btn-sm" href="{{action('DocumentAndNoteController@reports')}}">
                @lang('messages.close')
            </a> 
        </div>
    </div><!-- /.modal-content -->
    {!! Form::close() !!}
</div><!-- /.modal-dialog -->

@endsection

@section('javascript')
<script>
var dropzoneForDocsAndNotes = {};
tinymce.init({
        selector: 'textarea#docs_note_description',
    });
    $('form#docus_notes_formz').validate();
    initialize_dropzone_for_docus_n_notes();
function initialize_dropzone_for_docus_n_notes() {
        var file_names = [];

        if (dropzoneForDocsAndNotes.length > 0) {
            Dropzone.forElement("div#docusUpload").destroy();
        }

        dropzoneForDocsAndNotes = $("div#docusUpload").dropzone({
            url: base_path+'/post-document-upload',
            paramName: 'file',
            uploadMultiple: true,
            autoProcessQueue: true,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(file, response) {
                if (response.success) {
                    toastr.success(response.msg);
                    file_names.push(response.file_name);
                    $('input#docus_notes_media').val(file_names);
                } else {
                    toastr.error(response.msg);
                }
            },
        });
    }

    $(document).on('submit', 'form#docus_notes_formz', function(e){
        e.preventDefault();
        var url = $('form#docus_notes_formz').attr('action');
        var method = $('form#docus_notes_formz').attr('method');
        var data = $('form#docus_notes_formz').serialize();
        $.ajax({
            method: method,
            dataType: "json",
            url: url,
            data:data,
            success: function(result){
                if (result.success) {
                    toastr.success(result.msg);
                    window.location.href = "/note-documents/index/reports"; 
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });
</script>
    

@endsection

