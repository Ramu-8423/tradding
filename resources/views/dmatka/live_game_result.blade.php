@extends('admin.layouts.administrator')

@section('title', 'Dashboard')

@section('content')
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
                            <div class="d-flex justify-content-end align-items-center ">
                                <form method="POST" action="" class="form-inline">
                                    @csrf
                                    <input type="date" name="date" class="form-control form-control-sm " />
                                    <button type="submit" class="btn btn-sm btn-primary">Search</button>
                                </form>
                            </div>
                            <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Serial.No</th>
                                        <th>Game Name</th>
                                       
                                        <th>Website Result Time</th>
                                        <th>Real Result Time</th>
                                        <th>Today Result</th>
                                        <th>Modify Result</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($formattedData as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item['gamename'] }}</td>
                                        
                                        <td>{{ str_replace('at ', '', $item['result_time']) }}</td>

                                        <td> 
                                        @php
                                            $cleanTime = str_replace('at ', '', $item['result_time']);
                                            $updatedTime = \Carbon\Carbon::parse($cleanTime)->addMinutes(30)->format('h:i A');
                                        @endphp
                                        {{ $updatedTime }}</td>
                                        <td>{{ $item['result'] }}</td>
                                        <td>
                                            @if($item['result'] == 'XX')
                                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal"
                                                        data-target="#exampleModalCenter"
                                                        onclick="setModifyData('{{ $item['modify_result'] ?? 'N/A' }}', '{{ $item['id'] }}')">
                                                    {{ $item['modify_result'] ?? 'N/A' }}
                                                </button>
                                            @elseif($item['result'] == '--')
                                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                                        data-target="#manualModal"
                                                        onclick="setManualData('{{ $item['id'] }}')">
                                                   No Result Proceed Manually
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-danger btn-sm">
                                                    {{ $item['modify_result'] ?? 'N/A' }}
                                                </button>
                                            @endif
                                        </td>
                                        <td>{{ $item['date'] }}</td>
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

<!-- Modal for Modify Result -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('live_modify_result') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Modify Result</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="text" id="modifyResultInput" name="modify_result" class="form-control"
                        placeholder="Enter result like 12, 55 etc." >
                    <input type="hidden" id="modifyResultId" name="id">
                </div>

                <div class="modal-footer bg-whitesmoke br">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for -- (Manual Result) -->
<div class="modal fade" id="manualModal" tabindex="-1" role="dialog" aria-labelledby="manualModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('manual_result') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="manualModalLabel">Proceed Manual Result</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <p>Are you sure you want to proceed with manual result?</p>

                    <!-- Result Input for Manual Update -->
                    <input type="text" id="manualResultInput" name="result" class="form-control mb-2"
                        placeholder="Enter result like 12, 55 etc." required>

                    <input type="hidden" id="manualResultId" name="id">
                </div>

                <div class="modal-footer bg-whitesmoke br">
                    <button type="submit" class="btn btn-warning">Proceed Result</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function setModifyData(result, id) {
        document.getElementById("modifyResultInput").value = result !== 'N/A' ? result : '';
        document.getElementById("modifyResultId").value = id;
    }

    function setManualData(id) {
        document.getElementById("manualResultId").value = id;
        document.getElementById("manualResultInput").value = '--'; // Default set for now
    }
</script>

@endsection
