@extends('admin.layouts.administrator')

@section('title', 'Dashboard')

@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Bank Details</h4>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>UPI ID</th>
                    <th>Name</th>
                    <th>Created At</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody id="tableBody">
                  @foreach($data as $item)
                    <tr>
                      <td>{{ $item->id }}</td>
                      <td>{{ $item->userid }}</td>
                      <td>{{ $item->upi_id }}</td>
                      <td>{{ $item->name }}</td>
                      <td>{{ $item->created_at }}</td>
                      <td>
                        <button class="btn btn-sm btn-primary" onclick="setUpdateWallet('{{ $item->upi_id }}', '{{ $item->id }}', 3)" data-toggle="modal" data-target="#walletModal">
                          Edit
                        </button>
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
        <h5 class="modal-title" id="walletModalTitle">Edit UPI ID</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form method="POST" action="{{ route('update_bank_detail') }}">
        @csrf
        <div class="modal-body">
          <input type="text" id="modifyResultInput" name="upi_id" class="form-control" placeholder="Enter UPI ID" required>
          <input type="hidden" id="modifyResultId" name="id">
        </div>

        <div class="modal-footer bg-whitesmoke br">
          <button type="submit" class="btn btn-success">Save</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- Edit UPI ID Script -->
<script>
function setUpdateWallet(upi, id, type) {
    document.getElementById('modifyResultInput').value = upi;
    document.getElementById('modifyResultId').value = id;

    let title = document.getElementById('walletModalTitle');
    if (type == 3) {
        title.innerText = "Edit UPI ID";
    }
}
</script>

@endsection
