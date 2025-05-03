@extends('admin.layouts.administrator')

@section('title', 'Banner')

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
            {{-- Game Table --}}
            <div class="card-header" style="overflow-x: auto;">
                <div style="min-width: 700px;">
                    <table border="1" style="width:100%; text-align:center;">
                        <tr>
                            @foreach ($tradding_games as $game)
                                <td>
                                    <strong>{{ $game->live_game }}</strong><br>
                                    Close: {{ date('h:i A', strtotime($game->game_close_time)) }}<br>
                                    Result: {{ date('h:i A', strtotime($game->game_result_time)) }}
                                </td>
                            @endforeach
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Result Form --}}
            <div class="card-body">
                <form id="betForm" action="{{ route('result_announce') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="game_type">Select Game Type</label>
                        <select class="form-control" id="game_type" name="game_type" required>
                            <option value="" disabled selected>Choose Type</option>
                            <option value="1">Single</option>
                            <option value="2">Double</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="game_id">Select Game</label>
                        <select class="form-control" id="game_id" name="game_id" required>
                            <option value="" disabled selected>Choose Game</option>
                            <option value="1">Kospi (South Korea)</option>
                            <option value="2">Hang Seng (Hongkong)</option>
                            <option value="3">Dax (Germany)</option>
                            <option value="4">BSE Sensex</option>
                            <option value="5">Nifty 50</option>
                            <option value="6">Shanghai Stock Exc (SSE)</option>
                            <option value="7">Shanghai Stock Exc (SZSE)</option>
                        </select>
                    </div>
                    <div class="form-group" id="single_input" style="display: none;">
                        <label for="single_number">Enter Single Digit (0–9)</label>
                        <input type="number" class="form-control" id="single_number" min="0" max="9">
                    </div>
                    <div class="form-group" id="double_input" style="display: none;">
                        <label for="double_number">Enter Double Digit (00–99)</label>
                        <input type="text" class="form-control" id="double_number" maxlength="2" pattern="\d{2}" placeholder="e.g. 00, 01, ..., 99">
                    </div>
                    <input type="hidden" name="number" id="hidden_number">
                    <button type="submit" class="btn btn-primary">Add Result</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Result Table -->
<section class="section"> 
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Live Game Result</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Game/No</th>
                                        <th>Tradding Name</th>
                                        <th>Tradding Type</th>
                                        <th>Single</th>
                                        <th>Double</th>
										<th>Status</th>
                                        <th>Action</th>
										<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Result Time</th>
										
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($result as $index => $item)
                                    <tr>
                                        <td>{{ $item->game_id }}</td>
                                        <td>
                                            @switch($item->game_id)
                                                @case(1) Kospi (South Korea) @break
                                                @case(2) Hang Seng (Hongkong) @break
                                                @case(3) Dax (Germany) @break
                                                @case(4) BSE Sensex @break
                                                @case(5) Nifty 50 @break
                                                @case(6) Shanghai Stock Exc (SSE) @break
                                                @case(7) Shanghai Stock Exc (SZSE) @break
                                                @default Unknown
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($item->game_type == 1)
                                                Single
                                            @elseif($item->game_type == 2)
                                                Double
                                            @else
                                                Unknown
                                            @endif
                                        </td>
                                        <td>{{ $item->single ?? 'N/A' }}</td>
                                        <td>{{ $item->double ?? 'N/A' }}</td>
										<td>
										@if($item->status == 0)
											<a href="{{ route('result.edit', $item->id) }}" class="btn btn-sm btn-success">
												<i class="fa fa-edit text-danger"></i>Pending</a>
										@elseif($item->status == 1)
											<a href="" class="btn btn-sm btn-danger">Complete&nbsp;&nbsp;</a>
										@endif
										</td>
                                        <td>
											
											@php
											 $resultPlus2 = \Carbon\Carbon::parse($item->result_time)->addMinutes(2)->format('H:i:s');
										    @endphp
										
										 @if($item->comment)
											<a href="https://root.mahajong.club/tradding_result_cron/{{ $item->game_id }}" 
											   class="btn btn-sm btn-warning" style="font-size: 10px;">
											   {{ $item->comment }}
											</a>
										 @elseif($item->status == 1)
											<a href="#" class="btn btn-sm btn-danger">
												<i class="fas fa-check-circle"></i>&nbsp;Complete
											</a>
										   @elseif($item->status == 0 &&  $item->result_time > $current_time)
											<a href="#" class="btn btn-sm btn-success">
												<i class="fas fa-spinner fa-spin text-danger"></i>&nbsp;Pending
											</a>
										  @elseif($item->status == 0 && $resultPlus2 < $current_time)
											<a href="https://root.mahajong.club/tradding_result_cron/{{ $item->game_id }}" 
											   class="btn btn-sm btn-warning" style="font-size: 10px;">
											   No Result Proceed Manually
											</a>
									       @else
									       <a href="#" class="btn btn-sm btn-success">
												<i class="fas fa-spinner fa-spin text-danger"></i>&nbsp;Pending
											</a>
										@endif

                                        </td>
										
										<td>{{ \Carbon\Carbon::createFromFormat('H:i:s', $item->result_time)->format('h:i A') }}</td>
										

                                    </tr>
                                    @endforeach
                                    @if(count($result) === 0)
                                    <tr>
                                        <td colspan="7" class="text-center">No results found.</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
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

    gameType.addEventListener('change', function () {
        if (this.value == '1') {
            singleInput.style.display = 'block';
            doubleInput.style.display = 'none';
            singleNumber.value = '';
            doubleNumber.value = '';
        } else if (this.value == '2') {
            singleInput.style.display = 'none';
            doubleInput.style.display = 'block';
            singleNumber.value = '';
            doubleNumber.value = '';
        } else {
            singleInput.style.display = 'none';
            doubleInput.style.display = 'none';
        }
    });

    singleNumber.addEventListener('input', function () {
        let val = this.value.replace(/[^0-9]/g, '');
        this.value = val.length > 1 ? val.slice(0, 1) : val;
    });

    doubleNumber.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2);
    });

    form.addEventListener('submit', function () {
        if (gameType.value == '1') {
            hiddenNumber.value = singleNumber.value;
        } else if (gameType.value == '2') {
            hiddenNumber.value = doubleNumber.value;
        }
    });
</script>
@endsection
