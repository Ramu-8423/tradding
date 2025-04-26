@extends('admin.layouts.administrator')
@section('title', 'Dashboard')
@section('content')
<section class="section">
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>@if($game_type == 1)Jodi Betlog @elseif($game_type ==2)Crossing Betlog @elseif($game_type==3) Anader bahar @endif </h4>
                       <div class="btn-group  ml-4" role="group" aria-label="Basic example">
                            <a href="{{route('dmtka_betlog', 1)}}" class="btn btn-success">Jodi</a>
                            <a href="{{route('dmtka_betlog', 2)}}" class="btn btn-success ml-1">Crossing</a>
                            <a href="{{route('dmtka_betlog', 3)}}" class="btn btn-success ml-1">Andar Bahar</a>
                        </div>
                    @php
                        $games = [
                            1 => 'MOHALI',
                            2 => 'ROYAL CHALLENGE',
                            3 => 'GHAZIABAD',
                            4 => 'GURGAON',
                            5 => 'DHAN KUBER',
                            6 => 'DELHI BAZAR',
                            7 => 'SHRI GANESH',
                            8 => 'FARIDABAD',
                            9 => 'GALI',
                            10 => 'DESAWAR',
                        ];
                    
                        $selectedGameId = request()->input('game_id');
                        $selectedGameName = $selectedGameId && isset($games[$selectedGameId]) ? $games[$selectedGameId] : 'Select Game';
                    
                        // Button color class based on selection
                        $buttonClass = $selectedGameId ? 'btn-danger' : 'btn-primary';
                    @endphp
                    
                    <form method="POST" action="{{ route('dmtka_betlog', $game_type) }}" class="ml-1">
                        @csrf
                        <div class="dropdown">
                            <button class="btn {{ $buttonClass }} dropdown-toggle" type="button" id="dropdownMenuButton"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ $selectedGameName }}
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                @foreach($games as $id => $name)
                                    <button
                                        class="dropdown-item {{ $selectedGameId == $id ? 'active text-white bg-danger' : '' }}"
                                        type="submit"
                                        name="game_id"
                                        value="{{ $id }}">
                                        {{ $name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </form>

                     <div class="btn-group  ml-1" role="group" aria-label="Basic example">
                            <a href="" class="btn btn-primary">Reset</a>
                        </div>

                    </div>
                    <div class="card-body">
    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
        <table class="table table-striped table-hover" style="width:100%;">
            <thead class="thead-dark" style="position: sticky; top: 0; background: #fff; z-index: 1;">
                <tr>
                    <th>ID</th>
                    <th>Game ID</th>
                    <th>Game Serial No</th>
                    <th>Number</th>
                    <th>Amount</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($betlogs as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>
                        @if($item->game_id == 1) MOHALI
                        @elseif($item->game_id == 2) ROYAL CHALLENGE
                        @elseif($item->game_id == 3) GHAZIABAD
                        @elseif($item->game_id == 4) GURGAON
                        @elseif($item->game_id == 5) DHAN KUBER
                        @elseif($item->game_id == 6) DELHI BAZAR
                        @elseif($item->game_id == 7) SHRI GANESH
                        @elseif($item->game_id == 8) FARIDABAD
                        @elseif($item->game_id == 9) GALI
                        @elseif($item->game_id == 10) DESAWAR
                        @else Unknown
                        @endif
                    </td>
                    <td>{{ $item->game_serial_no }}</td>
                    <td>{{ $item->number }}</td>
                    <td>{{ $item->amount }}</td>
                    <td>{{ $item->created_at }}</td>
                    <td>{{ $item->updated_at }}</td>
                </tr>
                @endforeach

                @if($betlogs->isEmpty())
                <tr>
                    <td colspan="7" class="text-center">No records found.</td>
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
@endsection
