@extends('admin.layouts.administrator')

@section('title', 'Dashboard')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>
                            @if($status == 2)Withdraws Success
                            @elseif($status == 1)Withdraws Pending
                            @elseif($status == 3)Withdraws Reject
                            @endif
                        </h4>
                        <div class="btn-group ml-4" role="group">
                            <a href="{{ route('m_withdraw', 1) }}" class="btn btn-warning">Pending</a>
                            <a href="{{ route('m_withdraw', 2) }}" class="btn btn-success ml-1">Success</a>
                            <a href="{{ route('m_withdraw', 3) }}" class="btn btn-danger ml-1">Reject</a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                           <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User ID</th>
									<th>Name</th>
                                    <th>Mobile</th>
                                    <th>Amount</th>
									<th>Order ID</th> 
                                    <th>Status</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->user_id }}</td>
									<td>{{ $item->name }}</td>
                                    <td>{{ $item->mobile }}</td>
                                    <td>{{ $item->amount }}</td>
                                    <td>{{ $item->order_id }}</td>
                                     <td>
                                                    @if($item->status == 1)
                                                        <div class="dropdown">
                                                            <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="statusDropdown{{ $item->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                Pending
                                                            </button>
                                                            <div class="dropdown-menu" aria-labelledby="statusDropdown{{ $item->id }}">
                                                                <a class="dropdown-item text-success" href="{{ route('update.updatewithdraw',                                                                     ['id' => $item->id, 'status' => 2]) }}">Success</a>
                                                                <a class="dropdown-item text-danger" href="{{ route('update.updatewithdraw',                                                                     ['id' => $item->id, 'status' => 3]) }}">Reject</a>
                                                            </div>
                                                        </div>
                                                    @elseif($item->status == 2)
                                                        <span class="btn btn-success btn-sm ">Success</span>
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

<!-- Modal -->

@endsection
