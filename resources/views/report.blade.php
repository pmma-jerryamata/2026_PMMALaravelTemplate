@extends('layouts.main')
@section('content')
<div class="container mt-4">
    <h2 class="text-center mb-4">Job Requests Report</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped text-center align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Department/Unit</th> <!-- Left-aligned -->
                    @foreach($tasks as $task)
                        <th class="equal-width">{{ $task['name'] }}</th>
                    @endforeach
                    <th class="equal-width bg-secondary text-white">Total</th> <!-- Total column -->
                </tr>
            </thead>
            <tbody>
                <?php $grandTotal = 0; ?> <!-- Grand total counter -->
                @foreach($departments as $department)
                    <?php $rowTotal = 0; ?> <!-- Total per department -->
                    <tr>
                        <td class="dept-column fw-bold">{{ $department['name'] }}</td> <!-- Left-aligned -->
                        @foreach($tasks as $task)
                            <?php $taskCount = 0; ?>
                            @foreach($supports as $support)
                                @if($support['task']->name === $task['name'] && $support['employee']->department->name === $department['name'])
                                    <?php $taskCount++; ?>
                                @endif
                            @endforeach
                            <td class="equal-width">{{ $taskCount }}</td>
                            <?php $rowTotal += $taskCount; ?> <!-- Add to row total -->
                        @endforeach
                        <td class="equal-width fw-bold bg-light">{{ $rowTotal }}</td> <!-- Department total -->
                        <?php $grandTotal += $rowTotal; ?> <!-- Add to grand total -->
                    </tr>
                @endforeach

                <!-- Total Row -->
                <tr class="table-secondary fw-bold">
                    <td class="dept-column">Total</td>
                    <?php $columnTotals = array_fill(0, count($tasks), 0); ?>
                    @foreach($tasks as $index => $task)
                        <?php $columnTotal = 0; ?>
                        @foreach($supports as $support)
                            @if($support['task']->name === $task['name'])
                                <?php $columnTotal++; ?>
                            @endif
                        @endforeach
                        <td class="equal-width">{{ $columnTotal }}</td>
                        <?php $columnTotals[$index] = $columnTotal; ?> <!-- Store total for column -->
                    @endforeach
                    <td class="equal-width bg-dark text-white">{{ $grandTotal }}</td> <!-- Grand total -->
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
    /* Department column aligned left */
    .dept-column {
        width: auto;
        min-width: 180px;
        text-align: left !important;
        padding-left: 15px;
    }

    /* Other columns are evenly spaced */
    .equal-width {
        width: auto;
        min-width: 120px;
        text-align: center;
    }
</style>

@endsection 
<!--
<div class="container">
    <h2 class="text-center my-4">Job Requests Report</h2>
    @foreach($supports as $support)
        <li>{{ $support['employee']->department->name }} {{ $support['task']->name }}</li>
    @endforeach
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Department/Unit</th>
                @foreach($tasks as $task)
                    <th>{{ $task['name'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($departments as $department)
                <tr>
                    <td>{{ $department['name'] }}</td>
                    @foreach($tasks as $task)
                        <td>
                            <?php $ctr=0 ?>
                            @foreach($supports as $support)
                                @if($support['task']->name === $task['name'])
                                    @if($support['employee']->department->name === $department['name'])
                                        <?php $ctr++; ?>
                                    @endif
                                    
                                @endif
                                
                            @endforeach
                            {{  $ctr }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
-->