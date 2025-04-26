@extends('admin.layouts.administrator')

@section('title', 'Banner')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">

          <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="padding: 20px 25px; background-color: #f8f9fa;">
    <h4 class="mb-2" style="font-weight: 600; font-size: 20px; color: #343a40;">Banner</h4>

    <form method="POST" action="{{ route('admin.banner.add') }}" enctype="multipart/form-data" class="d-flex align-items-center" style="gap: 10px;">
        @csrf
        <input type="file" name="image" required 
            style="padding: 8px 14px; border: 1px solid #ced4da; border-radius: 6px; background-color: #fff; font-size: 14px; max-width: 250px;">
        
        <button type="submit" class="btn btn-success d-flex align-items-center"
            style="padding: 8px 16px; font-size: 14px; border-radius: 6px; font-weight: 500;">
            <i class="fas fa-plus mr-2"></i> Add Banner
        </button>
    </form>
</div>


                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Serial No</th>
                                        <th>Banner Image</th>
                                        <th>Date Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $index => $banner)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <img src="{{ $banner->images }}" alt="Banner" width="100" class="mt-1">
                                        </td>
                                        <td>{{ $banner->datetime }}</td>
                                        <td>
                                            <!-- Edit Button -->
                                            <button class="btn btn-primary btn-sm" data-toggle="modal"
                                                data-target="#updateLimitModal"
                                                onclick="setBannerData('{{ $banner->id }}', '{{ $banner->images }}')">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>

                                            <!-- Delete Form -->
                                            <form method="POST" action="{{ route('admin.banner.delete', $banner->id) }}"
                                                style="display:inline-block;" onsubmit="return confirm('Are you sure to delete this banner?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Delete
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

<!-- Edit Modal -->
<div class="modal fade" id="updateLimitModal" tabindex="-1" role="dialog" aria-labelledby="updateLimitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

           <form method="POST" action="{{ route('admin.banner.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Update Banner Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body text-center">
                    <input type="hidden" name="id" id="bannerId">
                    <div class="mb-3">
                        <img id="bannerPreview" src="" alt="Preview" width="200" class="img-thumbnail">
                    </div>

                    <input type="file" name="image" class="form-control" required>
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
    function setBannerData(id, imageUrl) {
        document.getElementById('bannerId').value = id;
        document.getElementById('bannerPreview').src = imageUrl;
    }
</script>

@endsection
