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
                        {!! Form::select('contact_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('Please Select')]); !!}
                    </div>
                </div>
                {{-- @if(auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Administration#' . auth()->user()->business_id))
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('assigned_to', 'Agent:') !!}
                        {!! Form::select('assigned_to', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('Please Select')]); !!}
                    </div>
                </div>
                @else
                <input type="hidden" name="assigned_to" value="{{ auth()->user()->id }}">
                @endif --}}
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
<script>
    $('.contact_id').select2();
    $('.assigned_to').select2();
    // Initialize date and time picker
    $('#scheduled_at').datetimepicker({
        format: 'MM/DD/YYYY HH:mm:ss'
    });
</script>