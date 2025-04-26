@extends('admin.layouts.administrator')

@section('title', 'User to Vendor Payments')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>User to Vendor</h4>
                          <h4>
                            @if($status == 2)Payin Success
                            @elseif($status == 1)Payin Pending
                            @elseif($status == 3)Payin Reject
                            @endif
                        </h4>
                        <div class="btn-group ml-4" role="group">
                            <a href="{{ route('userToVendorPayment', 1) }}" class="btn btn-warning">Pending</a>
                            <a href="{{ route('userToVendorPayment', 2) }}" class="btn btn-success ml-1">Success</a>
                            <a href="{{ route('userToVendorPayment', 3) }}" class="btn btn-danger ml-1">Reject</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Vendor ID</th>
                                        <th>User ID</th>
                                        <th>Amount</th>
                                        <th>Transaction ID</th>
                                        <th>Screenshot</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->vendor_id }}</td>
                                            <td>{{ $item->user_id }}</td>
                                            <td>{{ $item->request_amount }}</td>
                                            <td>{{ $item->transaction_id ?? 'N/A' }}</td>
                                            <td>
                                                @if($item->screeshot)
                                                    @php
                                                        $imageUrl = asset($item->screeshot);
                                                    @endphp
                                                    <button type="button"
                                                            class="btn btn-link p-0"
                                                            style="border: none;"
                                                            data-toggle="modal"
                                                            data-target="#imageModal"
                                                            data-image="{{ $imageUrl }}">
                                                        <i class="fas fa-image fa-2x" style="color: #007bff;"></i>
                                                    </button>
                                                @else
                                                    No Image
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->status == 1)
                                                    <div class="dropdown">
                                                        <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="statusDropdown{{ $item->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            Pending
                                                        </button>
                                                        <div class="dropdown-menu" aria-labelledby="statusDropdown{{ $item->id }}">
                                                            <a class="dropdown-item text-success" href="{{ route('update.updatewithdraws', ['id' => $item->id, 'status' => 2]) }}">Success</a>
                                                            <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#rejectModal" data-item-id="{{ $item->id }}">Reject</a>
                                                        </div>
                                                    </div>
                                                @elseif($item->status == 2)
                                                    <span class="btn btn-success btn-sm">Success</span>
                                                @elseif($item->status == 3)
                                                    <span class="btn btn-danger btn-sm">Reject</span>
                                                @else
                                                    <span class="btn btn-secondary btn-sm">Unknown</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->created_at }}</td>
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

<!-- Screenshot Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Screenshot</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" style="max-height: 500px;" alt="Screenshot">
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('update.rejectWithdraw') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Payment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="item_id" id="rejectItemId">
                    <div class="form-group">
                        <label for="rejectionReason">Reason for Rejection</label>
                        <textarea class="form-control" name="reason" id="rejectionReason" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        $('#imageModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var imageUrl = button.data('image');
            $('#modalImage').attr('src', imageUrl);
        });

        $('#rejectModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var itemId = button.data('item-id');
            $('#rejectItemId').val(itemId);
        });
    });
</script>

@endsection
