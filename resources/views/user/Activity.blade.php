@extends('admin.layouts.administrator')

@section('title', 'User Activity')

@section('content')
<section class="section">
  <div class="section-body">
    {{-- PAYINS TABLE --}}
    <div class="card">
      <div class="card-header"><h4>Payins</h4></div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Amount</th>
                <th>Transaction ID</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($paying as $item)
              <tr>
                <td>{{ $item->amount }}</td>
                <td>{{ $item->transaction_id }}</td>
                <td>
                    @if($item->status == 1)
                        <button class="btn btn-warning btn-sm">Pending</button>
                    @elseif($item->status == 2)
                        <button class="btn btn-success btn-sm">Success</button>
                    @elseif($item->status == 3)
                        <button class="btn btn-danger btn-sm">Reject</button>
                    @else
                        <button class="btn btn-secondary btn-sm">Unknown</button>
                    @endif
                </td>
                <td>{{ $item->created_at }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- WITHDRAWS TABLE --}}
    <div class="card">
      <div class="card-header"><h4>Withdraws</h4></div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Amount</th>
                <th>Order ID</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($withdraws as $item)
              <tr>
                <td>{{ $item->amount }}</td>
                <td>{{ $item->order_id }}</td>
                 <td>
                    @if($item->status == 1)
                        <button class="btn btn-warning btn-sm">Pending</button>
                    @elseif($item->status == 2)
                        <button class="btn btn-success btn-sm">Success</button>
                    @elseif($item->status == 3)
                        <button class="btn btn-danger btn-sm">Reject</button>
                    @else
                        <button class="btn btn-secondary btn-sm">Unknown</button>
                    @endif
                </td>
                <td>{{ $item->created_at }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ALL BETS TABLE --}}
    <div class="card">
      <div class="card-header"><h4>
      @if($game_type == null)
      All Bets 
      @elseif($game_type == 1)
      Jodi Bets
      @elseif($game_type == 2)
      Crossing Game
      @elseif($game_type == 3)
      Ander bahar
      @endif
      </h4>  
            <a href="{{ route('users_activity', $userid) }}" class="btn btn-success">&nbsp;&nbsp;All&nbsp;&nbsp;</a>
           <form action="{{route('users_activity', $userid)}}" method="GET" class="d-inline-block ml-1">
                <div class="btn-group" role="group">
                <button type="submit" name="game_type" value="1" class="btn btn-success">Jodi</button>
                <button type="submit" name="game_type" value="2" class="btn btn-success ml-1">Cross</button>
                <button type="submit" name="game_type" value="3" class="btn btn-success ml-1">Andar bahar</button>
            </div>
          </form>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>ID</th>
                <th>Game ID</th>
                <th>Game_type</th>
                <th>Status</th>
                <th>Number</th>
                <th>Amount</th>
                <th>Win Amount</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($bets as $item)
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
                  @else {{ $item->game_id }}
                  @endif
                </td>
                <td>{{ $item->game_type }}</td>
                <td>
                    @if($item->status == 1)
                        Pending
                    @elseif($item->status == 2)
                        Win
                    @elseif($item->status == 3)
                        Lose
                    @else
                        Unknown
                    @endif
                </td>
                <td>{{ $item->number }}</td>
                <td>{{ $item->amount }}</td>
                <td>{{ $item->win_amount }}</td>
                <td>{{ $item->created_at }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</section>
@endsection
