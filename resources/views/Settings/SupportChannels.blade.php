@extends('admin.layouts.administrator')

@section('title', 'Support Channels')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Support Channels</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Serial No</th>
                                        <th>Icon</th>
                                        <th>Name</th>
                                        <th>Link</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $index => $support)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <img src="{{ $support->image }}" alt="Icon" width="40">
                                        </td>
                                        <td>{{ ucfirst($support->name) }}</td>
                                        <td><a href="{{ $support->link }}" target="_blank">{{ $support->link }}</a></td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" data-toggle="modal"
                                                data-target="#editSupportModal"
                                                onclick="setSupportData('{{ $support->id }}', '{{ $support->name }}', '{{ $support->link }}')">
                                                <i class="fas fa-edit"></i> Edit
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

<!-- Modal -->
<div class="modal fade" id="editSupportModal" tabindex="-1" role="dialog" aria-labelledby="editSupportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <form method="POST" action="{{ route('admin.support.update') }}">
                @csrf

                <input type="hidden" name="id" id="supportId">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Support Channel</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label for="supportName">Name</label>
                        <input type="text" name="name" class="form-control" id="supportName" required>
                    </div>

                    <div class="form-group">
                        <label for="supportLink">Link</label>
                        <input type="url" name="link" class="form-control" id="supportLink" required>
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
    function setSupportData(id, name, link) {
        document.getElementById('supportId').value = id;
        document.getElementById('supportName').value = name;
        document.getElementById('supportLink').value = link;
    }
</script>

@endsection
