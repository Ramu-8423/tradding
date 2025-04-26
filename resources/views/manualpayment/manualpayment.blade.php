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
                            @if($status == 2)Payin Success
                            @elseif($status == 1)Payin Pending
                            @elseif($status == 3)Payin Reject
                            @endif
                        </h4>
                        <div class="btn-group ml-4" role="group">
                            <a href="{{ route('m_deposite', 1) }}" class="btn btn-warning">Pending</a>
                            <a href="{{ route('m_deposite', 2) }}" class="btn btn-success ml-1">Success</a>
                            <a href="{{ route('m_deposite', 3) }}" class="btn btn-danger ml-1">Reject</a>
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
									
                                    @foreach($data as $transaction)
                                       
                                            <tr>
                                                <td>{{ $transaction->id }}</td>
                                                <td>{{ $transaction->user_id }}</td>
                                                <td>{{ $transaction->user_name }}</td>
                                                <td>{{ $transaction->user_mobile }}</td>
                                                <td>{{ $transaction->amount }}</td>
                                                <td>{{ $transaction->order_id }}</td>
                                                
                                                <td>
                                                    @if($transaction->status == 1)
                                                       <span class="btn btn-warning btn-sm ">Pending</span>
                                                    @elseif($transaction->status == 2)
                                                        <span class="btn btn-success btn-sm ">Success</span>
                                                    @elseif($transaction->status == 3)
                                                        <span class="btn btn-danger btn-sm">Reject</span>
                                                    @else
                                                        <span class="btn btn-secondary btn-sm">Unknown</span>
                                                    @endif
                                                </td>
                                                <td>{{ $transaction->created_at }}</td>
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

@endsection
