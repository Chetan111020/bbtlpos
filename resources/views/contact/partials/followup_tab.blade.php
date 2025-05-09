<style>
    table {
        width: 40%;
        margin: auto;
    }

    .in-modal ul,
    .in-modal li {
        list-style: none;
        padding: 0;
    }

    .in-modal {
        margin: 10px;
        padding: 2rem;
        border-radius: 15px;
    }

    .sessions {
        margin-top: 2rem;
        border-radius: 12px;
        position: relative;
    }

    .in-modal li {
        padding-bottom: 1.5rem;
        border-left: 1px solid #abaaed;
        position: relative;
        padding-left: 20px;
        margin-left: 10px;
    }

    .in-modal li:last-child {
        border: 0px;
        padding-bottom: 0;
    }

    .in-modal li:before {
        content: "";
        width: 15px;
        height: 15px;
        background: white;
        border: 1px solid #4e5ed3;
        box-shadow: 3px 3px 0px #bab5f8;
        box-shadow: 3px 3px 0px #bab5f8;
        border-radius: 50%;
        position: absolute;
        left: -10px;
        top: 0px;
    }

    .time {
        color: #2a2839;
        /*font-family: "Poppins", sans-serif;*/
        font-weight: 500;
    }

    .in-modal p {
        color: #4f4f4f;
        font-family: sans-serif;
        line-height: 1.5;
        margin-top: 0.4rem;
    }

    @media screen and (max-width: 600px) {
        .in-modal p {
            font-size: 0.9rem;
        }
    }
</style>
@if (count($followup) > 0)
    <table>
        <td style="width: 50%;vertical-align: top;">
            <h3 style="text-align: center">FollowUp History
            &nbsp;&nbsp;
                {{-- <button class="btn btn-primary" data-toggle="modal" data-target="#followupmodal">+ Add New</button> --}}
            </h3>
            <div class="wrapper in-modal" style="background: #e5ffeb;">
                <ul class="sessions">
                    @foreach ($followup as $sl)
                        <li>
                            <div class="time">{{ date('m-d-Y h:i A', strtotime($sl->created_at)) }}</div>
                            <p>
                                Subject: <b>{{ $sl->title }}</b><br><a class="view-modal"
                                    href="{{ action('\Modules\SmartCRM\Http\Controllers\FollowUpController@view', [$sl->id]) }}">View
                                    Details</a>
                            </p>

                        </li>
                    @endforeach
                </ul>
            </div>
        </td>
    </table>

    <!--Store Follow Up-->
     <div class="modal fade" id="followupmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('smartcrm.followup.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Create a follow up</h4>
                    </div>
                    <div class="modal-body row">

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Subject:</label>
                                <input type="text" class="form-control" name="title" />
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('contact_id',  __('contact.customer') . ':') !!}
                                {!! Form::select('contact_id', $customers, $followup[0]->contact_id, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                            </div>
                        </div>
                        @if(auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Administration#' . auth()->user()->business_id))
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('assigned_to',  __('report.user') . ':') !!}
                                {!! Form::select('assigned_to', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                            </div>
                        </div>
                        @else
                        <input type="hidden" name="assigned_to" value="{{ auth()->user()->id }}">
                        @endif

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Next Schedule At</label>
                                <input type="text" class="form-control" name="scheduled_at" id="scheduled_at"/>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('status',  __('Status') . ':') !!}
                                {!! Form::select('status', $status, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                            </div>
                        </div>
                        <!--<div class="col-md-4">-->
                        <!--    <div class="form-group">-->
                        <!--        {!! Form::label('priority',  __('Priority') . ':') !!}-->
                        <!--        {!! Form::select('priority', $priorities, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}-->
                        <!--    </div>-->
                        <!--</div>-->
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('channel',  __('Channel') . ':') !!}
                                {!! Form::select('channel', $channel, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Conversation Notes:</label>
                                <textarea class="form-control" rows="3" name="notes"></textarea>
                            </div>
                        </div>

                        <div class="col-sm-12" style="display: flex;flex-direction: column;">
                            <label style="width:100%;">Tags:</label>
                            <input type="text" id="tags" name="tags" class="form-control" style="width:100%;" />
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@else
    <div style="text-align: center;">
        <h3>Data not found</h3>

    </div>

@endif

<!--View Modal-->
<div class="modal fade bd-example-modal-lg" id="ViewModal" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLabel" aria-hidden="true">
</div>
<!--View Modal-->
<script>
 $('#scheduled_at').datetimepicker().val('{{ date("m/d/Y H:i:s") }}');
    $(document).on('click', 'a.view-modal', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'html',
            success: function(result) {
                $('#ViewModal')
                    .html(result)
                    .modal('show');
            },
        });
    });
</script>
