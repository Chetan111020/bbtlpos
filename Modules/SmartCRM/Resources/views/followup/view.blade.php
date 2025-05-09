<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">Follow-up Details</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-6 border-right">
                    <h3 class="blue-heading"><strong>Customer Information</strong></h3>
                    <p><strong>Customer Name:</strong> {{ $followup->contact->name }}</p>
                    <p><strong>@lang('business.business_name'):</strong> {{ $followup->contact->supplier_business_name }}</p>
                    <p><strong>Address:</strong></p>
                    <p class="address-text">{!! $followup->contact->contact_address !!}</p>
                    <p><strong>Mobile:</strong> {{ $followup->contact->mobile }}</p>
                    <p><strong>Email:</strong> {{ $followup->contact->email }}</p>
                </div>
                <div class="col-sm-6">
                    <h3 class="blue-heading"><strong>Follow-up Details</strong></h3>
                    <p><strong>Subject:</strong></p>
                    <p class="subject-text">{{ $followup->title }}</p>
                    <p><strong>Contacted At:</strong> {{ date('m/d/Y', strtotime($followup->created_at)) }}</p>
                    <p><strong>Next Schedule At:</strong> {{ date('m/d/Y', strtotime($followup->scheduled_at)) }}</p>
                     <p><strong>Status:</strong> <span class="uppercase-text label  bg-info">{{ strtoupper($followup->status) }}</span></p>
                    <p><strong>Channel:</strong> <span class="uppercase-text label  bg-info">{{ strtoupper($followup->channel) }}</span></p>
                    <p><strong>Agent:</strong> {{ $followup->agent->first_name . " " . $followup->agent->last_name }}</p>
                    <p><strong>Conversation Notes:</strong> {{ $followup->note }}</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
