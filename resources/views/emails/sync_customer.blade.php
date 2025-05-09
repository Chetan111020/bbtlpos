<h1 style="color: red;">Welcome to {{ config('business-info.name') }}.</h1>
<h3>Hello {{ $data['contact']['contact_person_1'] }} ({{ $data['contact']['first_name'] }} {{ $data['contact']['contact_id'] }}), </h3>
<h4>Thank you for signing up with {{ config('business-info.name') }}.</h4>

<p><b>Below is the information you need to log in to our website:</b></p>

<p><b>Link to our website:</b> <a href="{{ config('business-info.website_url') }}/my-account/">{{ config('business-info.website_url') }}/my-account/</a></p>
<p><b>Your Username:</b> {{ !empty($data['username']) ? $data['username'] : $data['contact']['contact_id'] }}</p>
<p><b>Password:</b> {{ !empty($data['password']) ? $data['password'] : $data['contact']['contact_id'].'$Esd@123' }} </p>

<p>You can access your account area to view your orders, change your password & more.</p>

<p>Should you have any questions or any concerns, please do not hesitate to reach out to us.</p>

<p><b>Thank you!</b></p>

<p>{{ config('business-info.name') }}</p>
<p>{{ config('business-info.address_line_1') }}</p>
<p>{{ config('business-info.address_line_2') }}</p>
<p>{{ config('business-info.mobile') }} | {{ config('business-info.email') }} | {{ config('business-info.website_url_short') }}</p>