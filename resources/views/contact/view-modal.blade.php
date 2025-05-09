<div class="modal-dialog modal-xl" role="document">
	<div class="modal-content">
		<div class="modal-header">
		    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		      <h4 class="modal-title" id="modalTitle">{{$contact->name}}</h4>
		</div>
	    <div class="modal-body">
			
		<div class="row">
            <div class="col-md-12">
                <div class="box box-solid">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                            <!-- <strong>{{ $contact->name }}</strong><br><br> -->
                                <h3 class="profile-username">
                                    <i class="fas fa-user-tie"></i>
                                    {{ $contact->name }}
                                    <small>
                                        @if($contact->type == 'both')
                                            {{__('role.customer')}} & {{__('role.supplier')}}
                                        @elseif(($contact->type != 'lead'))
                                            {{__('role.'.$contact->type)}}
                                        @endif
                                    </small>
                                </h3>
                            </div>
                        </div>
                        <div class="row">
                            <div style="border-color: #00acd6;" class="col-md-4 border-right">
                                <strong><i class="fa fa-map-marker margin-r-5"></i> @lang('business.address')</strong>
                                <p class="text-muted">
                                    {!! $contact->contact_address !!}
                                </p>
                                @if($contact->supplier_business_name)
                                    <strong><i class="fa fa-briefcase margin-r-5"></i>
                                        @lang('business.business_name')</strong>
                                    <p class="text-muted">
                                        <a href="">{{ $contact->supplier_business_name }}</a>
                                    </p>
                                @endif

                                <strong><i class="fa fa-mobile margin-r-5"></i> @lang('contact.mobile')</strong>
                                <p class="text-muted">
                                    {{ $contact->mobile }}
                                </p>


                                <strong><i class="fas fa-user-tie"></i> @lang('lang_v1.contact_person_1')</strong>
                                <p class="text-muted">
                                    {{ $contact->contact_person_1 }}
                                </p>

                                <strong><i class="fas fa-user-tie"></i> @lang('lang_v1.contact_person_2')</strong>
                                <p class="text-muted">
                                    {{ $contact->contact_person_2 }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <strong><i class="fa fa-id-card"></i> @lang('lang_v1.tax_id')</strong>
                                <p class="text-muted">
                                    {{ $contact->tax }}
                                </p>

                                <strong><i class="fa fa-id-badge"></i> @lang('business.tobacco_license')</strong>
                                <p class="text-muted">
                                    {{ $contact->tobacco_license_no }}
                                </p>
                                <strong><i class="fas fa-tag"></i> @lang('business.nyc')</strong>
                                <p class="text-muted">
                                    {{ $contact->nyc }}
                                </p>

                                <strong><i class="fas fa-user-tie"></i> @lang('business.sales_rep')</strong>
                                <p class="text-muted">
                                    firstuser Salesrep
                                </p>
                                <strong><i class="fa fa-times-circle"></i> @lang('lang_v1.expiry_date')</strong>
                                <p class="text-muted">
                                    {{ $contact->expiry_date }}
                                </p>


                                <strong><i class="fa fa-users"></i> @lang('business.ref_code')</strong>
                                <p class="text-muted">
                                    <?php
                                    $referralID = \Illuminate\Support\Facades\DB::table('contacts')->where('supplier_business_name', substr(strstr($contact->referal_code, '@'), 1))->first();
                                    ?>
                                    @if($referralID != null)
                                        {{ strtok($contact->referal_code, '@') }}<a
                                                href="{{url('contacts',[$referralID->id]) }}">{{substr(strstr($contact->referal_code, '@'), 0)}}</a>
                                    @else
                                            {{ strtok($contact->referal_code, '@').substr(strstr($contact->referal_code, '@'), 0)}}
                                    @endif
                                </p>
                            </div>
                            <div style="border-color: #00acd6;" class="col-md-4 border-left">
                                <strong><i class="fas fa-user-tie"></i> @lang('business.acc_rep')</strong>
                                <p class="text-muted">
                                    secuser Accountrep
                                </p>
                                @if($contact->landline)
                                    <strong><i class="fa fa-phone margin-r-5"></i> @lang('contact.landline')</strong>
                                    <p class="text-muted">
                                        {{ $contact->landline }}
                                    </p>
                                @endif
                                @if($contact->alternate_number)
                                    <strong><i class="fa fa-phone margin-r-5"></i> @lang('contact.alternate_contact_number')
                                    </strong>
                                    <p class="text-muted">
                                        {{ $contact->alternate_number }}
                                    </p>
                                @endif
                                @if($contact->dob)
                                    <strong><i class="fa fa-calendar margin-r-5"></i> @lang('lang_v1.dob')</strong>
                                    <p class="text-muted">
                                        <a>{{ @format_date($contact->dob) }}</a>
                                    </p>
                                @endif
                                <strong><i class="fa fa-mobile margin-r-5"></i> @lang('contact.whatsapp')</strong>
                                <p class="text-muted">
                                    {{ $contact->whatsapp }}
                                </p>
                                <strong><i class="fa fa-briefcase margin-r-5"></i> @lang('contact.note')</strong>
                                <p class="text-muted">
                                    {{ $contact->note }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>





		
      	<div class="modal-footer">
      		<button type="button" class="btn btn-primary no-print" 
	        aria-label="Print" 
	          onclick="$(this).closest('div.modal').printThis();">
	        <i class="fa fa-print"></i> @lang( 'messages.print' )
	      </button>
	      	<button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
	    </div>
	</div>
</div>
