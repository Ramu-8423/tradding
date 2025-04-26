<div class="main-sidebar sidebar-style-2">
  <aside id="sidebar-wrapper">
    <div class="sidebar-brand">
      <a href="index.html">
        <img alt="image" src="{{ asset('assets/img/logo.png') }}" class="header-logo" />
        <span class="logo-name">Admin</span>
      </a>
    </div>
    
    @php
    $user = session('admin_user');
    
    $role = $user->role_id;
     
    @endphp
   
    <ul class="sidebar-menu">
      @if($role ==1)
      <li class="menu-header text-danger">Users Details</li>
      <li class="dropdown ">
        <a href="{{route('admin.dashboard')}}" class="nav-link"><i class="fas fa-tachometer-alt text-info"></i><span>Dashboard</span></a>
      </li>
      <li><a class="nav-link" href="{{route('users', 1)}}"><i class="fas fa-users text-success"></i><span>Active Players</span></a></li>
      <li><a class="nav-link" href="{{route('users', 0)}}"><i class="fas fa-user-slash text-danger"></i><span>Inactive Players</span></a></li>
      <li class="menu-header text-danger">Vendor Details</li>
      <li><a class="nav-link" href="{{route('vendor', 1)}}"><i class="fas fa-users text-success"></i><span>Active Vendor</span></a></li>
      <li><a class="nav-link" href="{{route('vendor', 0)}}"><i class="fas fa-user-slash text-danger"></i><span>Inactive Vendor</span></a></li>
      <li><a href="{{route('createvendor')}}" class="nav-link"><i class="fas fa-plus"></i><span>Add Vendor</span></a></li>
      <li><a href="{{route('userToVendorPayment',1)}}" class="nav-link"><i class="fas fa-file"></i><span>To Vendor Payment</span></a></li>
      <li class="menu-header text-danger">Matka Result Details</li>
      <li><a class="nav-link" href="{{route('betlive_result')}}"><i class="fas fa-broadcast-tower text-warning"></i><span>Live Game Result</span></a></li>
      <li><a class="nav-link" href="{{route('dmtka_betlog', 1)}}"><i class="fas fa-dice text-primary"style="color: #4e73df !important;"></i><span>Bets info</span></a></li>
      
      
      <li class="menu-header text-danger">Tradding Result Details</li>
      <li><a class="nav-link" href="{{route('add_result')}}"><i class="fas fa-broadcast-tower text-warning"></i><span>Tradding Result</span></a></li>
      <li><a class="nav-link" href="{{route('tradding_betlog', 1)}}"><i class="fas fa-dice text-primary"style="color: #4e73df !important;"></i><span>Bets info</span></a></li>
      
      
      <li class="menu-header text-danger">Payment Details</li>
      <li><a class="nav-link" href="{{route('m_deposite', 1)}}"><i class="fas fa-wallet text-success"></i><span>Payin</span></a></li>
      <li><a class="nav-link" href="{{route('m_withdraw', 1)}}"><i class="fas fa-money-bill-wave text-danger"></i><span>Withdrawal</span></a></li>
      <li><a class="nav-link" href="{{route('Transaction_limit')}}"> <i class="fas fa-gamepad" style="color: #4e73df !important;"></i><span>Transaction Limit</span></a></li>
      <li><a class="nav-link" href="{{route('banner')}}"><i class="fas fa-images text-info"></i><span>Banner</span></a></li>
      
      
      <li><a class="nav-link" href="{{route('Support_Channels')}}"><i class="fas fa-life-ring text-danger"></i><span>Customer Support </span></a></li>
        <li><a class="nav-link" href="{{route('viewUniqueNotification')}}"><i class="fas fa-pen text-danger"></i>
<span>Notification</span></a></li>
      
      
     
      @endif
    @if($role == 2 || $role == 3)
       <li class="menu-header text-danger">Users Details</li>
      <li class="dropdown ">
        <a href="{{route('admin.dashboard')}}" class="nav-link"><i class="fas fa-tachometer-alt text-info"></i><span>Dashboard</span></a>
      </li>
        <li><a href="{{route('userToVendorPayment',1)}}" class="nav-link"><i class="fas fa-file"></i><span>To Vendor Payment</span></a></li>
    @endif
    </ul>
  </aside>
  
<style>
    #sidebar-wrapper {
        max-height: 100vh;
        overflow-y: auto;
        scroll-behavior: smooth;
    }

    .active-sidebar-link {
        background-color: #e0f3ff !important;
        color: red !important;
        font-weight: bold;
        border-left: 3px solid #fc554c;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar-wrapper');
        if (!sidebar) return;

        const scrollPos = sessionStorage.getItem('sidebar-scroll');
        if (scrollPos !== null) {
            sidebar.scrollTop = parseInt(scrollPos, 10);
        }

        sidebar.addEventListener('scroll', function () {
            sessionStorage.setItem('sidebar-scroll', sidebar.scrollTop);
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const currentUrl = window.location.origin + window.location.pathname;

        document.querySelectorAll('.sidebar-menu a.nav-link').forEach(link => {
            if (link.href === currentUrl) {
                link.classList.add('active-sidebar-link');

                const icon = link.querySelector('i');
                if (icon) {
                    icon.style.color = '#007bff';
                }
            }
        });
    });
</script>



</div>
