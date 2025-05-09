<?php use Collective\Html\HtmlFacade as HTML; ?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv=Content-Type content="text/html; charset=utf-8">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>{{ $title ?? 'Invoice' }}</title>
    {{-- <script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js"></script> --}}
    {{ HTML::script('https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js') }}
</head>

<body>
    {!! $receipt['html_content_pdf2'] !!}
    {{ HTML::script('/pdf_assets/open_invoice_pdf.js') }}
</body>
</html>