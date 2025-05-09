<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('TaxRateController@update', [$tax_rate->id]), 'method' => 'PUT', 'id' => 'tax_rate_edit_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'tax_rate.edit_taxt_rate' )</h4>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-md-6 custom-column">
          <div class="col-md-12"><h3 class="m-underline">General Information</h3></div>

          <div class="col-md-12">
            <div class="form-group">
              {!! Form::label('name', __( 'tax_rate.name' ) . ':*') !!}
                {!! Form::text('name', $tax_rate->name, ['class' => 'form-control', 'required', 'placeholder' => __( 'tax_rate.name' )]); !!}
            </div>
          </div>
          {{-- <div class="col-md-6">
            <div class="form-group">
              {!! Form::label('amount', __( 'tax_rate.rate' ) . ':*') !!} @show_tooltip(__('lang_v1.tax_exempt_help'))
                {!! Form::text('amount', $tax_rate->amount, ['class' => 'form-control input_number', 'required']); !!}
            </div>
          </div> --}}
          <div class="col-md-6">
            <div class="form-group">
              {!! Form::label('begining_date', __('tax_rate.start_date') . ':') !!}
              <div class="input-group">
                  <span class="input-group-addon">
                      <i class="fa fa-calendar"></i>
                  </span>
                  {!! Form::text('begining_date', $tax_rate->begining_date, ['class' => 'form-control', 'id' => 'datepicker', 'placeholder' => __('tax_rate.start_date'), 'readonly']); !!}
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('end_date', __('tax_rate.end_date') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    {!! Form::text('end_date', $tax_rate->end_date, ['class' => 'form-control', 'id' => 'datepicker1', 'placeholder' => __('tax_rate.end_date'), 'readonly']); !!}
                </div>
            </div>
          </div>
          {{-- <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('state', __('tax_rate.state') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-map-marker"></i>
                    </span>
                    {!! Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('tax_rate.state')]); !!}
                </div>
            </div>
          </div> --}}
          <div class="col-md-6">
            <div class="form-group">
                <label>State</label>
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-map-marker"></i>
                    </span>
                    <select name="state" id="state" value="" class="form-control">
                      <option value="">-select-</option>
                      <option value="West Bengal">West Bengal</option>
                      <option value="Sikkim">Sikkim</option>
                      <option value="Uttar Pardesh">Uttar Pardesh</option>
                    </select>
                </div>
            </div>
          </div>
  
          <div class="col-md-6">
            <div class="form-group">
                <label>Category</label>
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-map-marker"></i>
                    </span>
                    <select name="" id="" class="form-control">
                      <option value="">-select-</option>
                      <option value="">Category</option>
                    </select>
                </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
                <label>Sub Category</label>
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-map-marker"></i>
                    </span>
                    <select name="" id="" class="form-control">
                      <option value="">-select-</option>
                      <option value="">Sub Category</option>
                    </select>
                </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group chk-pad">
              <label><input type="checkbox" value="1" name="inactive"><p class="chechkbox-p"> Item in Active</p></label>
            </div>
          </div>
        </div>
        

      <div class="col-md-6 custom-column">
        <div class="col-md-12"><h3 class="f-underline">State Sale Tax</h3></div>
        <div class="col-md-12">
          <div class="form-group">
            {!! Form::label('tax', __( 'tax_rate.tax_percent' ) . ':*') !!}
              {!! Form::text('tax', $tax_rate->tax, ['class' => 'form-control', 'required', 'placeholder' => __( 'tax_rate.tax_percent' )]); !!}
          </div>
        </div>
        <div class="col-md-12">
          <div class="form-group">
            {!! Form::label('taxvalue', __( 'tax_rate.tax_value' ) . ':*') !!}
              {!! Form::text('taxvalue', $tax_rate->taxvalue, ['class' => 'form-control', 'required', 'placeholder' => __( 'tax_rate.tax_value' )]); !!}
          </div>
        </div>
        <div class="col-md-12">
          <div class="form-group">
            {!! Form::label('every', __( 'tax_rate.every' ) . ':*') !!}
              {!! Form::text('every', $tax_rate->every, ['class' => 'form-control', 'required', 'placeholder' => __( 'tax_rate.every' )]);!!}
          </div>
        </div>
      </div>

      <div class="col-md-12 custom-column">
        <div class="col-md-12"><h3 class="l-underline">City Sale Tax</h3></div>
        <div class="col-sm-2">
          <div class="form-group">
            {!! Form::label('tax_percent', __( 'tax_rate.tax_percent' ) . ':*') !!}
            {!! Form::text('tax_percent', $tax_rate->tax_percent, ['class' => 'form-control', 'required', 'placeholder' => __( 'tax_rate.tax_percent' )]); !!}
          </div>
        </div>
        <div class="col-sm-2">
          <div class="form-group">
            {!! Form::label('city_tax_value', __( 'tax_rate.tax_value' ) . ':*') !!}
              {!! Form::text('city_tax_value', $tax_rate->city_tax_value, ['class' => 'form-control', 'required', 'placeholder' => __( 'tax_rate.tax_value' )]); !!}
          </div>
        </div>
        <div class="col-sm-2">
          <div class="form-group">
            {!! Form::label('everycity', __( 'tax_rate.every' ) . ':*') !!}
              {!! Form::text('everycity', $tax_rate->everycity, ['class' => 'form-control', 'required', 'placeholder' => __( 'tax_rate.every' )]);!!}
          </div>
        </div>
        <div class="col-sm-2">
          <div class="form-group">
            {!! Form::label('first_item_value', __( 'tax_rate.first_item_value' ) . ':*') !!}
              {!! Form::text('first_item_value', $tax_rate->first_item_value, ['class' => 'form-control', 'required', 'placeholder' => __( 'tax_rate.first_item_value' )]); !!}
          </div>
        </div>
        <div class="col-sm-2">
          <div class="form-group">
            {!! Form::label('second_item_value', __( 'tax_rate.second_item_value' ) . ':*') !!}
              {!! Form::text('second_item_value', $tax_rate->second_item_value, ['class' => 'form-control', 'required', 'placeholder' => __( 'tax_rate.second_item_value' )]);!!}
          </div>
        </div>
        <div class="col-sm-12">
          <div class="form-group">
            {!! Form::label('note', __('tax_rate.note') . ':') !!}
            {!! Form::textarea('note', $tax_rate->note, ['class' => 'form-control' , 'id' => 'note', 'row' => '5']); !!}
          </div>
        </div>
      </div>
    </div>


      {{-- <div class="form-group">
        <div class="checkbox">
          <label>
             {!! Form::checkbox('for_tax_group', 1, false, [ 'class' => 'input_icheck']); !!} @lang( 'lang_v1.for_tax_group_only' )
          </label> @show_tooltip(__('lang_v1.for_tax_group_only_help'))
        </div>
      </div> --}}
    </div>


    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<style>
.modal-body {
    padding: 0px 15px; 
}
.custom-column {
    background-color: rgb(230 230 230 / 33%);
    border: 10px solid white;
    padding: 0px 10px 10px 10px;
}

.f-underline, .l-underline {
    padding-bottom: 3px;
}

.f-underline::after {
    position: absolute;
    content: "";
    height: 2px;
    background-color: currentColor;
    width: 45%;
    margin-left: 12px;
    top: 83%;
    left: 1%;
}
.l-underline::after {
    position: absolute;
    content: "";
    height: 2px;
    background-color: currentColor;
    width: 20%;
    margin-left: 12px;
    top: 83%;
    left: 0%;
}
.m-underline::after {
    position: absolute;
    content: "";
    height: 2px;
    background-color: currentColor;
    width: 60%;
    margin-left: 12px;
    top: 83%;
    left: 1%;
}
.inactive{
  margin-top:20px;
}
#note{
  height:100px;
}
input[type='checkbox'] {
    width:20px;
    height:20px;
    border-radius:2px;   
}
.chechkbox-p {
    margin: -24px;
    margin-left: 30px;
}
.chk-pad {
  padding-top: 25px;
}
.col-sm-2 {
    width: 20%;
}
</style>
<script>
$( "#datepicker" ).datepicker({
dateFormat: 'dd/mm/yy',
    changeMonth: true,
    changeYear: true});
</script>
<script>
$( "#datepicker1" ).datepicker({
dateFormat: 'dd/mm/yy',
    changeMonth: true,
    changeYear: true});
</script>