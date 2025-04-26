<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Otika - Admin Dashboard Template</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="{{ asset('assets/css/app.min.css') }}">
  
  <!-- Template CSS -->
  <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
  
  <!-- Custom style CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
  <link rel='shortcut icon' type='image/x-icon' href="{{ asset('assets/img/favicon.ico') }}" />
  <style>
     .settingSidebar .settingPanelToggle {
    background: #6777ef;
    padding: 10px 15px;
    color: #fff;
    position: absolute;
    top:70%;
    left: -40px;
    width:10px;
    border-radius: 10px 0 0 10px;
  </style>
          <style>
       th{
    white-space: nowrap; 
  
    text-overflow: ellipsis;
}td{
    white-space: nowrap; 
     
    text-overflow: ellipsis;
}
  .buttons-csv {
    display: none !important;
   
  }
   .buttons-copy{
      display: none !important;   
    }
    .buttons-print{
         display: none !important;
    }
</style>
</head>

<body>
  <div class="loader"></div>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      
@if(!session('admin_logged_in'))
    <script>
        window.location.href = "{{ route('admin.login') }}";
    </script>
@endif
      
          @include('admin.layouts.header')

            <!-- Include Sidebar -->
            @include('admin.layouts.sidebar')
      <!-- Main Content -->
       <div class="main-content">
            @if(session()->has('message'))
                <div class="alert {{ session('status') == 1 ? 'alert-success' : 'alert-danger' }}">
                    {{ session('message') }}
                </div>
            @endif
            @if(session()->has('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            
            @if(session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
             @if ($errors->any())
               <div class="alert alert-danger d-flex">
                   <ul class="mb-0">
                       @foreach ($errors->all() as $error)
                          {{ $error }}
                       @endforeach
                   </ul>
               </div>
             @endif

          @yield('content')
         </div>  
      <footer class="main-footer">
        <div class="footer-left">
          <a href="templateshub.net">𝓜𝓐𝓗𝓐𝓙𝓞𝓝𝓖</a>
        </div>
        <div class="footer-right"></div>
      </footer>
    </div>
  </div>
<script>
    setTimeout(() => {
        document.querySelectorAll(".alert").forEach(alert => alert.remove());
    }, 3000); // 3 seconds delay
</script>
  <!-- General JS Scripts -->
  <script src="{{ asset('assets/js/app.min.js') }}"></script>
  <!-- JS Libraries -->
  <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
  <script src="{{ asset('assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
  <script src="{{ asset('assets/bundles/datatables/export-tables/dataTables.buttons.min.js') }}"></script>
  <script src="{{ asset('assets/bundles/datatables/export-tables/buttons.flash.min.js') }}"></script>
  <script src="{{ asset('assets/bundles/datatables/export-tables/jszip.min.js') }}"></script>
  <script src="{{ asset('assets/bundles/datatables/export-tables/pdfmake.min.js') }}"></script>
  <script src="{{ asset('assets/bundles/datatables/export-tables/vfs_fonts.js') }}"></script>
  <script src="{{ asset('assets/bundles/datatables/export-tables/buttons.print.min.js') }}"></script>
  <script src="{{ asset('assets/js/page/datatables.js') }}"></script>
  
  <!-- Template JS File -->
  <script src="{{ asset('assets/js/scripts.js') }}"></script>
  
  <!-- Custom JS File -->
  <script src="{{ asset('assets/js/custom.js') }}"></script>

</body>
</html>
