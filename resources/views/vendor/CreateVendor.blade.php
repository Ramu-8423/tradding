@extends('admin.layouts.administrator')

@section('title', 'User Activity')

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
      <form action="{{ route('addvendor') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="text-center mt-4">
          <h4>Create Vendor</h4>
        </div>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label class="inputfields">Name</label>
              <input type="text" name="name" class="form-control" placeholder="Name" value="{{ old('name') }}">
              @error('name')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
            <div class="form-group col-md-6">
              <label class="inputfields">Email</label>
              <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}">
              @error('email')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label class="inputfields">Mobile</label>
              <input type="number" name="mobile" class="form-control" placeholder="Mobile" value="{{ old('mobile') }}">
              @error('mobile')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>

            <div class="form-group col-md-6">
              <label class="inputfields">Password</label>
              <input type="text" name="password" class="form-control" placeholder="Password" value="{{ old('password') }}">
              @error('password')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-3">
              <label class="inputfields">State</label>
              <select id="inputState" name="state" class="form-control">
                <option disabled {{ old('state') ? '' : 'selected' }}>Choose...</option>
                @foreach($data as $item)
                  <option value="{{ $item->states }}" {{ old('state') == $item->states ? 'selected' : '' }}>
                    {{ $item->states }}
                  </option>
                @endforeach
              </select>
              @error('state')
                <small class="text-danger">{{ $message }}</small>
              @enderror
            </div>
            <div class="form-group col-md-3">
               <label class="inputfields">UPI ID</label>
               <input type="text" name="vendor_upi_id" class="form-control" value="{{ old('vendor_upi_id') }}">
                @error('vendor_upi_id')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
          <div class="form-group col-md-3">
            <label class="inputfields">Vendor QR</label>
            <input type="file" name="vendor_qr" class="form-control" onchange="previewQR(this)">
             @error('vendor_qr')
               <small class="text-danger">{{ $message }}</small>
             @enderror
        </div>
        <div class="form-group col-md-3">
            <img id="qr-preview" src="#" alt="QR Preview" style="display: none; margin-top: 10px; max-width: 50%; height:50 auto; border: 1px solid #ddd; padding: 4px;" />
        </div>
          </div>
        </div>

        <div class="card-footer text-center">
          <button class="btn btn-primary">Submit</button>
        </div>
      </form>
    </div>
  </div>
</section>
<script>
    function previewQR(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                const img = document.getElementById('qr-preview');
                img.src = e.target.result;
                img.style.display = 'block';
            };

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

@endsection
