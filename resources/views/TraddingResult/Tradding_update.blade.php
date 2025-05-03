@extends('admin.layouts.administrator')

@section('title', 'Update Result')

@section('content')
<style>
    td:hover {
        background-color: #f0f8ff;
        cursor: pointer;
    }
    td strong {
        color: blue;
    }
</style>
<section class="section">
    <div class="section-body">
        <div class="card">
            <div class="card-header">
                <h4>Edit Game Result</h4>
            </div>

            <div class="card-body">
                <form id="betForm" action="{{ route('result.update', $result->id) }}" method="POST">
                    @csrf
                    @method('PUT')

			<div class="form-group">
				<label for="game_type">Select Game Type</label>
				<select class="form-control" id="game_type" name="game_type" disabled>
					<option value="1" {{ $result->game_type == 1 ? 'selected' : '' }}>Single</option>
					<option value="2" {{ $result->game_type == 2 ? 'selected' : '' }}>Double</option>
				</select>
				<input type="hidden" name="game_type" value="{{ $result->game_type }}">
			</div>

			<div class="form-group">
				<label for="game_id">Select Game</label>
				<select class="form-control" id="game_id" name="game_id" disabled>
					@foreach($tradding_games as $game)
						<option value="{{ $game->id }}" {{ $result->game_id == $game->id ? 'selected' : '' }}>
							{{ $game->live_game }}
						</option>
					@endforeach
				</select>
				<input type="hidden" name="game_id" value="{{ $result->game_id }}">
			</div>


                    <div class="form-group" id="single_input" style="{{ $result->game_type == 1 ? '' : 'display:none;' }}">
                        <label for="single_number">Enter Single Digit (0–9)</label>
                        <input type="text" class="form-control" id="single_number" name="single_number"
                               value="{{ $result->single }}" maxlength="2" pattern="^(\d{1}|--)$"
                               placeholder="0–9 or --">
                    </div>

                    <div class="form-group" id="double_input" style="{{ $result->game_type == 2 ? '' : 'display:none;' }}">
                        <label for="double_number">Enter Double Digit (00–99)</label>
                        <input type="text" class="form-control" id="double_number" name="double_number"
                               value="{{ $result->double }}" maxlength="2" pattern="\d{2}"
                               placeholder="00–99">
                    </div>

                    <input type="hidden" name="number" id="hidden_number">

                    <button type="submit" class="btn btn-primary">Update Result</button>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- JavaScript --}}
<script>
    const gameType = document.getElementById('game_type');
    const singleInput = document.getElementById('single_input');
    const doubleInput = document.getElementById('double_input');
    const singleNumber = document.getElementById('single_number');
    const doubleNumber = document.getElementById('double_number');
    const hiddenNumber = document.getElementById('hidden_number');
    const form = document.getElementById('betForm');

    function toggleInputs() {
        if (gameType.value === '1') {
            singleInput.style.display = 'block';
            doubleInput.style.display = 'none';
            doubleNumber.value = '';
        } else if (gameType.value === '2') {
            singleInput.style.display = 'none';
            doubleInput.style.display = 'block';
            singleNumber.value = '';
        } else {
            singleInput.style.display = 'none';
            doubleInput.style.display = 'none';
        }
    }

    gameType.addEventListener('change', toggleInputs);

    singleNumber.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9\-]/g, '').slice(0, 2);
    });

    doubleNumber.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2);
    });

    form.addEventListener('submit', function () {
        if (gameType.value === '1') {
            hiddenNumber.value = singleNumber.value;
        } else if (gameType.value === '2') {
            hiddenNumber.value = doubleNumber.value;
        }
    });

    window.addEventListener('DOMContentLoaded', toggleInputs);
</script>
<script>
	// Restrict single input to only one digit (0–9)
singleNumber.addEventListener('input', function () {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 1);
});

// Restrict double input to exactly two digits (00–99)
doubleNumber.addEventListener('input', function () {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2);
});

</script>
@endsection
