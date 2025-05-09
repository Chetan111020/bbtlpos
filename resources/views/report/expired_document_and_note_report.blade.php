@extends('layouts.app')
@section('title', __( 'Expired Document And Note Report' ))
@section('content')
<section class="content-header">
    <h1> @lang('Expired Document And Note Report')</h1>
</section>

    <!-- Main content -->
<section class="content">
     <div class="row">
        <div class="col-md-12">
            <div class="form-group pull-right">
                <div class="col-md-8">
                    <input type="hidden" id="date" name="date" value="">
                    <div class="form-group">
                        {!! Form::label('expire_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('expire_date_filter', @format_date('first day of this month') . ' ~ ' . @format_date('last day of this month'), ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'expire_date_filter', 'readonly']); !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <button class="btn btn-primary" id="getdata" >Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div> 
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="table-responsive" id="expire-document-table">
                    <table class="table table-bordered table-striped" style="width: 100%;" id="documents_and_notes_table">
                        <thead>
                            <tr>
                                {{-- <th>@lang('messages.action')</th> --}}
                                <th>@lang('Suppliers')</th>
                                <th>@lang('lang_v1.heading')</th>
                                <th>@lang('lang_v1.added_by')</th>
                                <th>@lang('Expired Date')</th>
                                <th>@lang('lang_v1.created_at')</th>
                                <th>@lang('lang_v1.updated_at')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>
@endsection
<!-- /.content -->


@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
    
<script type="text/javascript">
 $('#expire_date_filter').daterangepicker({
        autoUpdateInput: false,
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
        locale: {
            format: moment_date_format,
            //   firstDay: 1,
        },
        ranges: {
          'Within 3 months': [moment(), moment().add(3,'months')],
          'Within 6 months': [moment(), moment().add(6, 'months')],
        }
        })
        $('#expire_date_filter').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format(moment_date_format) + ' ~ ' + picker.endDate.format(moment_date_format));
                $("#date").val($(this).val());
                console.log($(this).val());
        }); 

        $('#expire_date_filter').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            documents_and_notes_table.ajax.reload();
            documents_and_notes_table.ajax.reload();
        });
        
        documents_and_notes_table = $('#documents_and_notes_table').DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            
            mark: true,
            ajax: {
                url: '/reports/expired-document-and-note-report',
                data: function(d) { 
                    if($('#expire_date_filter').val()) {
                        var start = $('input#expire_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        console.log(start);
                        var end = $('input#expire_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        d.start_date = start;
                        d.end_date = end;
                        
                    }
                }
            },
            columns: [
                    { data: 'suppliers', name: 'suppliers' },
                    { data: 'heading', name: 'heading' },
                    { data: 'createdBy'},
                    { data: 'expiry_date', name: 'expiry_date' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'updated_at', name: 'updated_at' },
            ],
        });
    
        // if($('#documents_and_notes_table').length != 0){
        //     documents_and_notes_table.ajax.reload();
        // }
        
        $("#getdata").on('click',function(){
            $("#expire-document-table").removeAttr("style");
            documents_and_notes_table.ajax.reload();        
        });
</script>
@endsection

