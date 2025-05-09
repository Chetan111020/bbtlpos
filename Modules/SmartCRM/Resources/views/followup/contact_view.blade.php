<div style="padding:0 30px;">
    <button class="btn bg-orange">Previous</button>
    <a href="{{ route('smartcrm.followup.queue',$followup->id) }}" class="btn bg-info">Next</a>
    <button class="btn btn-primary pull-right">Mark as Completed</button>
</div>

<div class="col-sm-12 bg-white" style="margin-top:15px;padding:30px;">
    <span style="display:flex;width:100%;justify-content:space-between;">
        <span>Follow up details</span>
        <div>
            <span id="queue" class="badge" style="border-radius:0;border:solid #a1e1ff 2px;color:#37bfff;background:transparent;">{{ strtoupper($followup->channel) }}</span>
            <span id="status" class="badge" style="border-radius:0;">{{ ucwords($followup->status) }}</span>
        </div>
    </span>
    <h3 style="display:flex;width:100%;justify-content:space-between;">
        <span id="subject_name">{{ $followup->title }}</span>
        <span id="progress">{{ ucwords($followup->priority) }} Priority</span>
    </h3>
    <h5 id="user_name">Scheduled at {{ date('m/d/Y G:i a',strtotime($followup->scheduled_at)) }} for {{ $followup->agent->first_name }}</h5>
    <br/>
    <h5 style="display:flex;width:100%;justify-content:space-between;">
        <span id="display_msg">
            Note: {{ $followup->note }}

            <br/><br/>
            @foreach (explode(',',$followup->tags) as $tag)
                <span class="label bg-info">{{ $tag }}</span>&nbsp;
            @endforeach
        </span>
    </h5>
    <span class="loader">
        <span class="loader-after"></span>
    </span>
</div>