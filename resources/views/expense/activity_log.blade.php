<div class="modal-dialog modal-xl no-print" role="document">
  <div class="modal-content">
    <div class="modal-header">
    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>

<div class="modal-body">

<div class="row">
        <div class="col-md-12">
          <strong>Expenses Activity Log</strong>
        </div>
        <div class="col-md-12">
          <table class="table table-condensed bg-gray">
                  <tr class="bg-green">
                <th>User Name</th>
                <th>Action</th>
                <th>Message</th>
                <th width="10%">Date And Time</th>
              </tr>
              @foreach($ExpensesActivityLog as $log)
              <tr>
                <td>{{$log->first_name}}</td>
                @if($log->description == 'added')
                  <td>Added</td>
                @elseif($log->description == 'edited')
                  <td>Edited</td>
                @endif
                <td>{{$log->message}}</td>
                <td width="10%"> {{ Carbon\Carbon::parse($log->datetime)->format('d-m-Y G:i A') }}</td>
              </tr>
              @endforeach
          </table>
        </div>
</div>
<div class="modal-footer">
      <button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>
  </div>
</div>