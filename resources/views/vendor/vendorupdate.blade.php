@extends('admin.layouts.administrator')

@section('title', 'Edit Vendor')

@section('content')
<section class="section">
  <style>
    .inputfields {
        font-size: 18px !important;
    }
    .text-danger {
        font-size: 13px;
    }
</style>
  <div class="section-body">
    <div class="card">
     <form action="{{ route('updatevendor.post', $vendor->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="text-center mt-4">
          <h4>Edit Vendor</h4>
        </div>

        <div class="card-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label class="inputfields">Name</label>
              <input type="text" name="name" class="form-control" value="{{ old('name', $vendor->name) }}">
            </div>

            <div class="form-group col-md-6">
              <label class="inputfields">Email</label>
              <input type="email" name="email" class="form-control" value="{{ old('email', $vendor->email) }}">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label class="inputfields">Mobile</label>
              <input type="number" name="mobile" class="form-control" value="{{ old('mobile', $vendor->mobile) }}">
            </div>

            <div class="form-group col-md-6">
              <label class="inputfields">Password</label>
              <input type="text" name="password" class="form-control" value="{{ old('password', $vendor->password) }}">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label class="inputfields">State</label>
              <select name="state" class="form-control">
                @foreach($states as $state)
                  <option value="{{ $state->states }}" {{ old('state', $vendor->state) == $state->states ? 'selected' : '' }}>
                    {{ $state->states }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="form-group col-md-4">
              <label class="inputfields">UPI ID</label>
              <input type="text" name="vendor_upi_id" class="form-control" value="{{ old('vendor_upi_id', $vendor->vendor_upi_id) }}">
            </div>

            <div class="form-group col-md-4">
  <label class="inputfields">Vendor QR</label>
  <input type="file" name="vendor_qr" class="form-control" onchange="previewQR(this)">
  <br>

  @if($vendor->vendor_qr)
    <img src="{{ asset($vendor->vendor_qr) }}" alt="QR" width="100">
  @endif

  <img id="qr-preview" src="#" style="display: none; width: 100px;" />
</div>
          </div>
        </div>

        <div class="card-footer text-center">
          <button type="submit" class="btn btn-primary">Update Vendor</button>
        </div>
      </form>
    </div>
  </div>
</section>

<script>
  function previewQR(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const img = document.getElementById('qr-preview');
        img.src = e.target.result;
        img.style.display = 'block';
      };
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>

@endsection
