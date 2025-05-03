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
              @if($status == 1 && $role_id == 4)
                  Active Users
              @elseif($status == 0 && $role_id == 4)
                  Inactive Users
				@elseif($status == 1 && $role_id == 2)
                  Inactive Vendor
				@elseif($status == 0 && $role_id == 2)
                  Inactive Vendor
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
					<th>Bonus</th>
					<th>Commission</th>
                    <th>Status</th>
                    <th>Referral Code</th>
                    <th>Referral Id</th>
                    <th>Third Party Wallet</th>
                    
                    
                    <th>Total Referral Bonus</th>
                    <th>First Recharge</th>
                    <th>Recharge</th>
                    <th>Date Time</th>
                    @if($role_id == 2)
                    <th>Edit</th>
                    @endif
                    <th>Activity</th>
                  </tr>
                </thead>
                <tbody id="tableBody">
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
                            <strong>₹{{ number_format($item->wallet, 2) }}</strong><br>

                            <!-- Plus Button -->
                            <button type="button" class="btn btn-success btn-sm mt-1" data-toggle="modal" 
                                    data-target="#walletModal"
                                    onclick="setUpdateWallet('{{ $item->wallet }}', '{{ $item->id }}', 1)">
                                <i class="fas fa-plus"></i>
                            </button>

                            <!-- Minus Button -->
                            <button type="button" class="btn btn-danger btn-sm mt-1" data-toggle="modal" 
                                    data-target="#walletModal"
                                    onclick="setUpdateWallet('{{ $item->wallet }}', '{{ $item->id }}', 2)">
                                <i class="fas fa-minus"></i>
                            </button>
                          </div>
                        </td>
                        
						
						
						<td><strong>{{ number_format($item->winning_wallet, 2) }}</strong></td>
						<td><strong>{{ number_format($item->bonus, 2) }}</strong></td>
						<td><strong>{{ number_format($item->commission, 2) }}</strong></td>
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
                                    <input type="submit" value="Inactive" class="btn btn-danger btn-sm">
                                </form>
                            @endif
                        </td>
						
                       <td>{{ $item->referral_code }}</td>
                       <td>{{ $item->referrer_id }}</td>
                       <td>{{ $item->third_party_wallet }}</td>
						
                       
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

<!-- Wallet Modal -->
<div class="modal fade" id="walletModal" tabindex="-1" role="dialog" aria-labelledby="walletModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="walletModalTitle">Wallet Operation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form method="POST" action="{{ route('wallte_operation') }}">
        @csrf
        <div class="modal-body">
          <input type="number" id="modifyResultInput" name="modify_result" class="form-control" placeholder="Enter amount" required>
          <input type="hidden" id="modifyResultId" name="id">
          <input type="hidden" id="walletOperationType" name="type">
        </div>

        <div class="modal-footer bg-whitesmoke br">
          <button type="submit" class="btn btn-success">Save</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- Wallet Update Script -->
<script>
function setUpdateWallet(wallet, id, type) {
    document.getElementById('modifyResultInput').value = '';
    document.getElementById('modifyResultId').value = id;
    document.getElementById('walletOperationType').value = type;

    let title = document.getElementById('walletModalTitle');
    if (type == 1) {
        title.innerText = "Add Wallet";
    } else if (type == 2) {
        title.innerText = "Subtract Wallet";
    } else {
        title.innerText = "Wallet Operation";
    }
}
</script>

@endsection
