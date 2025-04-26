@extends('admin.layouts.administrator')

@section('title', 'Feedback')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Feedback List</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                             <table class="table table-striped table-hover" id="" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User ID</th>
                                        <th>Feedback</th>
										<th>Admin Reply</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->user_id }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($item->feedback, 30) }}</td>
										<td>{{$item->admin_reply }}</td>
                                        <td>
                                           <button 
												class="btn btn-primary btn-sm view-feedback-btn" 
												data-toggle="modal" 
												data-target="#feedbackModal" 
												data-id="{{ $item->id }}"
												data-feedback="{{ $item->feedback }}"
												data-admin-reply="{{ $item->admin_reply }}">
												View
											</button>
                                          <form action="{{ route('admin.feedback.delete', $item->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this feedback?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">
            Delete
        </button>
    </form>
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

<!-- Feedback Modal -->
<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" role="dialog" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="{{ route('admin.feedback.reply') }}" method="POST">
            @csrf
            <input type="hidden" name="feedback_id" id="modalFeedbackId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Feedback & Admin Reply</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="mb-3">
                        <strong>User Feedback:</strong>
                        <div id="feedbackText" style="white-space: pre-wrap;"></div>
                    </div>
                    <div class="form-group">
                        <label for="adminReplyText">Admin Reply</label>
                        <textarea name="admin_reply" id="adminReplyText" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Submit Reply</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- JavaScript for setting feedback -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const buttons = document.querySelectorAll(".view-feedback-btn");

        buttons.forEach(btn => {
            btn.addEventListener("click", function () {
                const feedback = this.getAttribute("data-feedback");
                const adminReply = this.getAttribute("data-admin-reply") || '';
                const id = this.getAttribute("data-id");

                document.getElementById("feedbackText").textContent = feedback;
                document.getElementById("adminReplyText").value = adminReply;
                document.getElementById("modalFeedbackId").value = id;
            });
        });
    });
</script>


@endsection
