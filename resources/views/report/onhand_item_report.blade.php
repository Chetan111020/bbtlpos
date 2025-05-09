@extends('layouts.app')
<title>On Hand Item Report </title>
@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>On Hand Item</h1>
    </section>
    <style>
        tbody, td {
            text-align: leftcenter;
        }
    </style>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('year_list',  __('Year') . ':') !!}
                            {!! Form::select('year_list', $years, date('Y'), ['class' => 'form-control select2','id'=>'year_list', 'style' => 'width:100%']); !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('week_list',  __('Week') . ':') !!}
                            {!! Form::select('week_list', $weeks,'week_'.date('W'), ['class' => 'form-control select2','id'=>'week_list', 'style' => 'width:100%']); !!}
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="onhand_item_report1">
                            <thead>
                            <tr>
                                <th>id</th>
                                <th>product</th>
                                <th>Quantity</th>

                            </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
        </div>
    </section>
    <!-- /.content -->

@endsection

@section('javascript')

    <script type="text/javascript">


        $(function () {
            onhand_item_report1='';
            var selected_week = $('#week_list').val();
            var selected_year = $('#year_list').val();
            getOnhandItems(selected_week,selected_year);
        });

        function getOnhandItems(selected_week,selected_year) {

            // var selected_week = $('#week_list').val();
            // var selected_year = $('#year_list').val();
            onhand_item_report1 = $('#onhand_item_report1').DataTable({
                destroy: true,
                processing: true,
                serverSide: true,

                ajax: {
                    url: '{{ url("reports/on-hand-list") }}',
                    type: "POST",
                    data: function (d) {
                        d.year = selected_year;
                        d.week = selected_week;

                    },
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'products.name'},
                    {data: '' + selected_week + '', name: '' + selected_week + ''},

                ]
            });
        }


        $('#year_list, #week_list').change(function () {
            var selected_week = $('#week_list').val();
            var selected_year = $('#year_list').val();
            onhand_item_report1.ajax.reload();

            getOnhandItems(selected_week,selected_year);
        });


    </script>

@endsection