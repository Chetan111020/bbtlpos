<!DOCTYPE html>
<html>
<head>
    <meta http-equiv=Content-Type content="text/html; charset=utf-8">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>{{$title ?? 'Invoice'}}</title>
</head>
<body>
    {!! $receipt['html_content_pdf'] !!}
</body>
</html>