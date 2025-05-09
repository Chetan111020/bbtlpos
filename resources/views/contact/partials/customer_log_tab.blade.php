<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12">
                <table class="table bg-gray">
                    <thead>
                        <tr class="bg-green">
                            <th>User Name</th>
                            <th>Action</th>
                            <th>Message</th>
                            <th width="10%">Date And Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activity_logs as $log)
                            <tr>
                                <td>{{ $log->first_name }}</td>
                                @if ($log->description == 'added')
                                    <td>Added</td>
                                @elseif($log->description == 'edited')
                                    <td>Edited</td>
                                @endif
                                @php $str_array = explode(',',$log->message); @endphp
                                <td>
                                    @foreach ($str_array as $message)
                                        @if ($message != '')
                                            # {{ $message }}<br>
                                        @endif
                                    @endforeach
                                </td>
                                <td width="10%"> {{ Carbon\Carbon::parse($log->datetime)->format('d-m-Y G:i A') }}
                                </td>
                            </tr>
                        @empty
                            <td colspan="4" style="text-align: center;">No logs Found!!</td>
                        @endforelse
                        <tr></tr>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>
