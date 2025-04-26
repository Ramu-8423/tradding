@extends('admin.layouts.administrator')

@section('title', 'Banner')

@section('content')
 <style>
            /* Hover effect for each td */
            td:hover {
                background-color: #f0f8ff; /* light blue background on hover */
                cursor: pointer;
            }

            /* Make strong tag blue */
            td strong {
                color: blue;
            }
        </style>


<section class="section">
    <div class="section-body">
        <div class="card">

            {{-- Game Table Inside Card --}}
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

            {{-- Form --}}
            <div class="card-body">
                <form id="betForm" action="{{ route('result_announce') }}" method="POST">
                    @csrf

                    {{-- Game Type Dropdown --}}
                    <div class="form-group">
                        <label for="game_type">Select Game Type</label>
                        <select class="form-control" id="game_type" name="game_type" required>
                            <option value="" disabled selected>Choose Type</option>
                            <option value="1">Single</option>
                            <option value="2">Double</option>
                        </select>
                    </div>

                    {{-- Game ID Dropdown --}}
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

                    {{-- Single Number Input --}}
                    <div class="form-group" id="single_input" style="display: none;">
                        <label for="single_number">Enter Single Digit (0–9)</label>
                        <input type="number" class="form-control" id="single_number" min="0" max="9">
                    </div>

                    {{-- Double Number Input --}}
                    <div class="form-group" id="double_input" style="display: none;">
                        <label for="double_number">Enter Double Digit (00–99)</label>
                        <input type="text" class="form-control" id="double_number" maxlength="2" pattern="\d{2}" placeholder="e.g. 00, 01, ..., 99">
                    </div>

                    {{-- Hidden Input for Actual Submission --}}
                    <input type="hidden" name="number" id="hidden_number">

                    <button type="submit" class="btn btn-primary">Place Bet</button>
                </form>
            </div>
        </div>
    </div>
</section>


<!--table-->

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
                            <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>S/No</th>
                                        <th>Tradding Name</th>
                                        <th>Tradding Type</th>
                                        <th>Single</th>
                                        <th>Double</th>
                                        <th>Announce Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($result as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                         <td>
                                        @if($item->game_id == 1)
                                            Kospi (South Korea)
                                        @elseif($item->game_id == 2)
                                            Hang Seng (Hongkong)
                                        @elseif($item->game_id == 3)
                                            Dax (Germany)
                                        @elseif($item->game_id == 4)
                                            BSE Sensex
                                        @elseif($item->game_id == 5)
                                            Nifty 50
                                        @elseif($item->game_id == 6)
                                            Shanghai Stock Exc (SSE)
                                        @elseif($item->game_id == 7)
                                            Shanghai Stock Exc (SZSE)
                                        @else
                                            Unknown
                                        @endif
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
                                    
                                        <td>{{ $item->single ?? 'N/A'}}</td>
                                        <td>{{ $item->double ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y h:i A') }}</td>

                                    </tr>
                                    @endforeach
                                    @if(count($result) === 0)
                                    <tr>
                                        <td colspan="9" class="text-center">No results found.</td>
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
            singleNumber.value = ''; // clear old values
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

    // Ensure only 1 digit (0–9)
    singleNumber.addEventListener('input', function () {
        let val = this.value.replace(/[^0-9]/g, '');
        this.value = val.length > 1 ? val.slice(0, 1) : val;
    });

    // Ensure only 2 digits (00–99)
    doubleNumber.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 2);
    });

    // Before submit, set the hidden input
    form.addEventListener('submit', function (e) {
        if (gameType.value == '1') {
            hiddenNumber.value = singleNumber.value;
        } else if (gameType.value == '2') {
            hiddenNumber.value = doubleNumber.value;
        }
    });
</script>
@endsection
