<!DOCTYPE html>
<html lang="en-US" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Phoenix Project Management</title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/favicon-32x32.png') }}">
    <script src="{{ asset('vendors/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <link href="{{ asset('vendors/choices/choices.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/dhtmlx-gantt/dhtmlxgantt.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/flatpickr/flatpickr.min.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&amp;display=swap" rel="stylesheet">
    <link href="{{ asset('vendors/simplebar/simplebar.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/theme-rtl.min.css') }}" rel="stylesheet" id="style-rtl">
    <link href="{{ asset('assets/css/theme.min.css') }}" rel="stylesheet" id="style-default">
    <link href="{{ asset('assets/css/user-rtl.min.css') }}" rel="stylesheet" id="user-style-rtl">
    <link href="{{ asset('assets/css/user.min.css') }}" rel="stylesheet" id="user-style-default">
    <script>
      var phoenixIsRTL = window.config.config.phoenixIsRTL;
      if (phoenixIsRTL) {
        document.getElementById('style-default').disabled = true;
        document.getElementById('user-style-default').disabled = true;
        document.documentElement.setAttribute('dir', 'rtl');
      } else {
        document.getElementById('style-rtl').disabled = true;
        document.getElementById('user-style-rtl').disabled = true;
      }
    </script>
    @vite('v1.23.0/src/project-management.js')
</head>
<body>
    <div id="app"></div>
</body>
</html>
