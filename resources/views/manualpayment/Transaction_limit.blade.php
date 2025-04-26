@extends('admin.layouts.administrator')

@section('title', 'Withdraw Limits')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Withdraw Limits</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Serial No</th>
                                        <th>Limit Types</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($limits as $limit)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $limit->title)) }}</td>
                                        <td><strong>₹{{ $limit->longtext }}</strong></td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" data-toggle="modal"
                                                data-target="#updateLimitModal"
                                                onclick="setLimitValue('{{ $limit->id }}', '{{ $limit->longtext }}', '{{ $limit->title }}')">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </td>
                                        <td>{{ $limit->created_at }}</td>
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

<!-- Modal -->
<div class="modal fade" id="updateLimitModal" tabindex="-1" role="dialog" aria-labelledby="updateLimitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <form method="POST" action="{{ route('update_site_setting') }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Update  <span id="limitTitleText"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="number" id="limitValue" name="longtext" class="form-control" placeholder="Enter value" required>
                    <input type="hidden" id="limitId" name="id">
                </div>

                <div class="modal-footer bg-whitesmoke br">
                    <button type="submit" class="btn btn-success">Update</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
    function setLimitValue(id, value, title) {
        document.getElementById('limitId').value = id;
        document.getElementById('limitValue').value = value;
        document.getElementById('limitTitleText').innerText = title.replace('_', ' ').toUpperCase();
    }
</script>

@endsection
