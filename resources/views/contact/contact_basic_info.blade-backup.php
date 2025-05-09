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
</h3><br>
<strong><i class="fa fa-map-marker margin-r-5"></i> @lang('business.address')</strong>
<p class="text-muted">
    {!! $contact->contact_address !!}
</p>
@if($contact->supplier_business_name)
    <strong><i class="fa fa-briefcase margin-r-5"></i> 
    @lang('business.business_name')</strong>
    <p class="text-muted">
        {{ $contact->supplier_business_name }}
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

<strong><i class="fas fa-user-tie"></i> @lang('lang_v1.fax')</strong>
<p class="text-muted">
    {{ $contact->fax }}
</p>

<strong><i class="fas fa-user-tie"></i> @lang('business.tobacco_license')</strong>
<p class="text-muted">
    {{ $contact->tobacco_license }}
</p>


<strong><i class="fas fa-user-tie"></i> @lang('lang_v1.expiry_date')</strong>
<p class="text-muted">
    {{ $contact->expiry_date }}
</p>


<strong><i class="fas fa-user-tie"></i> @lang('business.ref_code')</strong>
<p class="text-muted">
    {{ $contact->ref_code }}
</p>

<strong><i class="fas fa-user-tie"></i> @lang('business.nyc')</strong>
<p class="text-muted">
    {{ $contact->nyc }}
</p>



@if($contact->landline)
    <strong><i class="fa fa-phone margin-r-5"></i> @lang('contact.landline')</strong>
    <p class="text-muted">
        {{ $contact->landline }}
    </p>
@endif
@if($contact->alternate_number)
    <strong><i class="fa fa-phone margin-r-5"></i> @lang('contact.alternate_contact_number')</strong>
    <p class="text-muted">
        {{ $contact->alternate_number }}
    </p>
@endif
@if($contact->dob)
    <strong><i class="fa fa-calendar margin-r-5"></i> @lang('lang_v1.dob')</strong>
    <p class="text-muted">
        {{ @format_date($contact->dob) }}
    </p>
@endif