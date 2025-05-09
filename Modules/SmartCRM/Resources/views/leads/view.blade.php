<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">{{ $contact->name }} - ({{ $contact->contact_id }})</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <h4> <strong>{{ $contact->name }}</strong> </h4>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <p><strong>Address :</strong> {{ $contact->full_address }}</p>
                    <p><strong>Email :</strong> {{ $contact->email }}</p>
                    <p><strong>Mobile :</strong> {{ $contact->mobile }}</p>
                    <p><strong>Creted By :</strong> {{ $contact->user->first_name }}</p>
                     
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
