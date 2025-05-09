<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500&display=swap" rel="stylesheet">
<style type="text/css">
    @media print{
        .each_label{
            page-break-after: always;
        }
        @page {
            margin: 0;
            size: {{$label_w}}in {{$label_h}}in !important;
        }
    }
    .each_label{
        width: {{$label_w}}in !important;
        height: {{$label_h}}in !important;
    }
    .each_div{
        display: flex;
        justify-content: center;
        align-items: center;
        width: 20%;
    }
    .each_text_div{
        text-align: center;
    }
    *{
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Inter';
    }
</style>
@foreach ($labels as $label)
<div class="each_label" style="display:flex;">
    <div class="each_div">
        <img style="width:90%" src="data:image/png;base64,{{DNS2D::getBarcodePNG($label['QR'],'QRCODE')}}">
    </div>
    <div class="each_div" style="background: #ff205b;">
        <div class="each_text_div" style="display:{{ $show_params['A'] ? 'block' : 'none' }};">
            <h3>{{ $label['A'] }}</h3>
            <br/>
            <h3>Aisle</h3>
        </div>
    </div>
    <div class="each_div" style="background: #99ff40;">
        <div class="each_text_div" style="display:{{ $show_params['R'] ? 'block' : 'none' }};">
            <h3>{{ $label['R'] }}</h3>
            <br/>
            <h3>Rack</h3>
        </div>
    </div>
    <div class="each_div" style="background: #55a1ff;">
        <div class="each_text_div" style="display:{{ $show_params['S'] ? 'block' : 'none' }};">
            <h3>{{ $label['S'] }}</h3>
            <br/>
            <h3>Shelf</h3>
        </div>
    </div>
    <div class="each_div" style="background: #f5ff8e;">
        <div class="each_text_div" style="display:{{ $show_params['B'] ? 'block' : 'none' }};">
            <h3>{{ $label['B'] }}</h3>
            <br/>
            <h3>Bin</h3>
        </div>
    </div>
</div>
@endforeach