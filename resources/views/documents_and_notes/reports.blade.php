@extends('layouts.app')
@section('title', __( 'restaurant.orders' ))
@section('content')
<section class="content-header">
    <h1> @lang('Document And Note')</h1>
</section>

    <!-- Main content -->
<section class="content">
    <div class="table-responsive">
            <div class="pull-right">
                <a class="btn btn-sm btn-primary pull-right" href="{{action('DocumentAndNoteController@create')}}">
                    @lang('messages.add')&nbsp;
                    <i class="fa fa-plus"></i>
                </a> 
            </div> <br><br>
        <table class="table table-bordered table-striped" style="width: 100%;" id="documents_and_notes_table">
            <thead>
                <tr>
                    <th>@lang('messages.action')</th>
                    <th>@lang('Suppliers')</th>
                    <th>@lang('lang_v1.heading')</th>
                    <th>@lang('lang_v1.added_by')</th>
                    <th>@lang('lang_v1.created_at')</th>
                    <th>@lang('lang_v1.updated_at')</th>
                </tr>
            </thead>
        </table>
    </div>
    <div class="modal fade docus_note_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
</section>
@endsection

@section('javascript')
<script>
initializeDocumentAndNoteDataTable1()
function initializeDocumentAndNoteDataTable1() {
    documents_and_notes_data_table = $('#documents_and_notes_table').DataTable({
        processing: true,
        serverSide: true,
        ajax:{
            url: '/note-documents/index/reports',
            data: null
        },
        columnDefs: [
            {
                targets: [0, 2, 4],
                orderable: false,
                searchable: false,
            },
        ],
        aaSorting: [[3, 'asc']],
        columns: [
            { data: 'action', name: 'action' },
            { data: 'suppliers', name: 'suppliers' },
            { data: 'heading', name: 'heading' },
            { data: 'createdBy'},
            { data: 'created_at', name: 'created_at' },
            { data: 'updated_at', name: 'updated_at' },
        ]
    });
}

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
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });
</script>
@endsection