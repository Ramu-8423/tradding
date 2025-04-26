@extends('admin.layouts.administrator')

@section('title', 'Dashboard')

@section('content')
        <section class="section">
          <div class="section-body">
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h4>
                         @if($status == 1 || $role_id == 2 || $role_id == 3)
                                Active Vendors
                            @elseif($status == 2 || $role_id == 2 || $role_id == 3)
                                Inactive Vendors
                            @elseif($status == 1)
                                Active Users
                            @else
                                Inactive Users
                            @endif
                    </h4>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                        <thead>
                          <tr>
                            <th>Id</th>
                            <th>Name</th>
                            <th>Email</th>
							  <th>Password</th>
                            <th>Mobile</th>
                            <th>State</th>
                            <th>Wallet</th>
                            <th>Winning Wallet</th>
                            <th>Status</th>
                            <th>Referral Code</th>
                            <th>Referral Id</th>
                            <th>Third Party Wallet</th>
                            <th>Commission</th>
                            <th>Bonus</th>
                            <th>Total Referral Bonus</th>
                            <th>First Recharge</th>
                            <th>Recharge</th>
                            <th>Date Time</th>
                            @if($role_id ==2)
                            <th>Edit</th>
                            @endif
                            <th>Activity</th>
                          </tr>
                        </thead>
                        <tbody>
                           @foreach($data as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->email }}</td>
								<td>{{ $item->password }}</td>
                                <td>{{ $item->mobile }}</td>
                                <td>{{ $item->state }}</td>
                                <td>
                                  <div class="text-center">
                                    <strong>₹{{ $item->wallet }}</strong><br>
                                
                                    <!-- Plus Button -->
                                    <button type="button" class="btn btn-success btn-sm mt-1" data-toggle="modal" 
                                            data-target="#exampleModalCenter"
                                            onclick="setUpdateWallet('{{ $item->wallet }}', '{{ $item->id }}', 1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                
                                    <!-- Minus Button -->
                                    <button type="button" class="btn btn-danger btn-sm mt-1" data-toggle="modal" 
                                            data-target="#exampleModalCenter"
                                            onclick="setUpdateWallet('{{ $item->wallet }}', '{{ $item->id }}', 2)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                                </td>
                                <td>{{ $item->winning_wallet }}</td>
                                <td>
                                     @if($item->status == 1)
                                        <form action="{{ route('toggle_status') }}" method="post">
                                            @csrf
                                            <input type="hidden" name="status" value="0" />
                                            <input type="hidden" name="id" value="{{$item->id}}" />
                                            <input type="submit" value="Active" class="btn btn-success btn-sm">
                                        </form>
                                    @else
                                        <form action="{{ route('toggle_status') }}" method="post">
                                            @csrf
                                            <input type="hidden" name="status" value="1" />
                                            <input type="hidden" name="id" value="{{$item->id}}" />
                                            <input type="submit"   value="Inactive" class="btn btn-danger btn-sm">
                                        </form>
                                    @endif
                                </td>
                               <td>{{ $item->referral_code }}</td>
                                <td>{{ $item->referrer_id }}</td>
                                <td>{{ $item->third_party_wallet }}</td>
                                <td>{{ $item->commission }}</td>
                                <td>{{ $item->bonus }}</td>
                                <td>{{ $item->total_referral_bonus }}</td>
                                <td>{{ $item->first_recharge }}</td>
                                <td>{{ $item->recharge }}</td>
                                <td>{{ $item->created_at }}</td>
                                @if($role_id == 2)
                                <td>
                                    <a href="{{ route('updatevendor', $item->id) }}" class="btn btn-info btn-sm" title="Edit Vendor">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                </td>
                            @endif
                            
                            <td>
                                <a href="{{ route('users_activity', $item->id) }}" class="btn btn-primary btn-sm" title="User Activity">
                                    <i class="fa fa-eye"></i> Activity
                                </a>
                            </td>

                               
                            </tr>
                            @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
        <div class="settingSidebar">
<!-- Settings Sidebar -->
    <div class="settingSidebar">
          <a href="javascript:void(0)" class="settingPanelToggle"> <i class="fa fa-spin fa-cog"></i>
          </a>
          <div class="settingSidebar-body ps-container ps-theme-default">
            <div class=" fade show active">
              <div class="setting-panel-header">Setting Panel
              </div>
              <div class="p-15 border-bottom">
                <h6 class="font-medium m-b-10">Select Layout</h6>
                <div class="selectgroup layout-color w-50">
                  <label class="selectgroup-item">
                    <input type="radio" name="value" value="1" class="selectgroup-input-radio select-layout" checked>
                    <span class="selectgroup-button">Light</span>
                  </label>
                  <label class="selectgroup-item">
                    <input type="radio" name="value" value="2" class="selectgroup-input-radio select-layout">
                    <span class="selectgroup-button">Dark</span>
                  </label>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <h6 class="font-medium m-b-10">Sidebar Color</h6>
                <div class="selectgroup selectgroup-pills sidebar-color">
                  <label class="selectgroup-item">
                    <input type="radio" name="icon-input" value="1" class="selectgroup-input select-sidebar">
                    <span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip"
                      data-original-title="Light Sidebar"><i class="fas fa-sun"></i></span>
                  </label>
                  <label class="selectgroup-item">
                    <input type="radio" name="icon-input" value="2" class="selectgroup-input select-sidebar" checked>
                    <span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip"
                      data-original-title="Dark Sidebar"><i class="fas fa-moon"></i></span>
                  </label>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <h6 class="font-medium m-b-10">Color Theme</h6>
                <div class="theme-setting-options">
                  <ul class="choose-theme list-unstyled mb-0">
                    <li title="white" class="active">
                      <div class="white"></div>
                    </li>
                    <li title="cyan">
                      <div class="cyan"></div>
                    </li>
                    <li title="black">
                      <div class="black"></div>
                    </li>
                    <li title="purple">
                      <div class="purple"></div>
                    </li>
                    <li title="orange">
                      <div class="orange"></div>
                    </li>
                    <li title="green">
                      <div class="green"></div>
                    </li>
                    <li title="red">
                      <div class="red"></div>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <div class="theme-setting-options">
                  <label class="m-b-0">
                    <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input"
                      id="mini_sidebar_setting">
                    <span class="custom-switch-indicator"></span>
                    <span class="control-label p-l-10">Mini Sidebar</span>
                  </label>
                </div>
              </div>
              <div class="p-15 border-bottom">
                <div class="theme-setting-options">
                  <label class="m-b-0">
                    <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input"
                      id="sticky_header_setting">
                    <span class="custom-switch-indicator"></span>
                    <span class="control-label p-l-10">Sticky Header</span>
                  </label>
                </div>
              </div>
              <div class="mt-4 mb-4 p-3 align-center rt-sidebar-last-ele">
                <a href="#" class="btn btn-icon icon-left btn-primary btn-restore-theme">
                  <i class="fas fa-undo"></i> Restore Default
                </a>
              </div>
            </div>
          </div>
        </div> 

			
			
			
 <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Wallet Operation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="POST" action="{{ route('wallte_operation') }}">
                @csrf
                <div class="modal-body">
                    <input type="number" id="modifyResultInput" name="modify_result" class="form-control" placeholder="Enter amount">
                    <input type="hidden" id="modifyResultId" name="id" value="">
                    <input type="hidden" id="walletOperationType" name="type" value="">
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </form>

        </div>
    </div>
</div>


<script>
function setUpdateWallet(value, id, type) {
    document.getElementById("modifyResultInput").value = '';
    document.getElementById("modifyResultId").value = id;
    document.getElementById("walletOperationType").value = type;

    // Set dynamic modal title
    let modalTitle = document.getElementById("exampleModalCenterTitle");
    if (type == 1) {
        modalTitle.innerText = "Add Wallet";
    } else if (type == 2) {
        modalTitle.innerText = "Subtract Wallet";
    } else {
        modalTitle.innerText = "Wallet Operation";
    }
}
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const sortUpButton = document.querySelector('#sortUp');  // Aero Up Button
    const sortDownButton = document.querySelector('#sortDown');  // Aero Down Button
    const tableBody = document.querySelector('#tableBody');  // Table body to sort
    
    sortUpButton.addEventListener('click', function() {
        sortTable('asc');
    });
    
    sortDownButton.addEventListener('click', function() {
        sortTable('desc');
    });
    
    function sortTable(order) {
        let rows = Array.from(tableBody.rows);
        rows.sort((rowA, rowB) => {
            const idA = parseInt(rowA.cells[0].innerText);
            const idB = parseInt(rowB.cells[0].innerText);
            
            if (order === 'desc') {
                return idB - idA;
            } else {
                return idA - idB;
            }
        });
        
        rows.forEach(row => tableBody.appendChild(row)); // Reorder the rows
    }
});

</script>


@endsection