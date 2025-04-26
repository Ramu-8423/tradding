@extends('admin.layouts.administrator')

@section('title', 'Notification')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Notification</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Notification</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $data->id }}</td>
                                        <td>{{ Str::limit($data->notification, 100) }}</td>
                                        <td>{{ $data->created_at }}</td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editNotificationModal"
                                                onclick="setNotificationData('{{ $data->id }}', `{{ $data->notification }}`)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
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
<div class="modal fade" id="editNotificationModal" tabindex="-1" role="dialog" aria-labelledby="editNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.notification.update') }}">
                @csrf

                <input type="hidden" name="id" id="notificationId">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Notification</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
    <div class="form-group">
        <label for="notificationText"><strong>Notification Message</strong></label>
        <textarea 
            name="notification" 
            class="form-control" 
            id="notificationText" 
            rows="10" 
            style="resize: vertical; min-height: 200px;" 
            required
        ></textarea>
    </div>
</div>


                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function setNotificationData(id, notification) {
        document.getElementById('notificationId').value = id;
        document.getElementById('notificationText').value = notification;
    }
</script>

@endsection