<!-- resources/views/home.blade.php -->

@extends('layouts.app')

@section('content')

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <p>Welcome to the Driving School Dashboard. You are logged in!</p>

                        <div class="mt-4">
                            <a href="{{ url('/checkin') }}" class="btn btn-success">Checkin</a>
                            <a href="{{ url('/checkout') }}" class="btn btn-danger">Checkout</a>
                        </div>

                        <div class="mt-4">
                      <div class="mt-4">
    <h5>Your Sessions:</h5>
    <ul>
    @forelse($sessions->sortBy('checkin_time') as $session)
        <li>
            {{ \Carbon\Carbon::parse($session->checkin_time)->format('Y-m-d H:i:s') }}
            - {{ $session->checkout_time ?: 'In progress' }}
            - Distance: {{ $session->distance }} km
            - User: {{ $session->user_name }}
            @if($session->checkout_time)
                - Total Time: {{ \Carbon\Carbon::parse($session->checkout_time)->diffInMinutes(\Carbon\Carbon::parse($session->checkin_time)) }} minutes
            @else
                - Total Time: In progress
            @endif
        </li>
    @empty
        <li>No sessions yet</li>
    @endforelse
</ul>

</div>


<div class="mt-4">
    <canvas id="myPieChart" width="400" height="200"></canvas>
</div>

@isset($chartData)
                            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                            <script>
                                var ctx = document.getElementById('myPieChart').getContext('2d');
                                var myPieChart = new Chart(ctx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: {!! json_encode($chartData['labels']) !!},
                                        datasets: [{
                                            data: {!! json_encode($chartData['data']) !!},
                                            backgroundColor: [
                                                'rgba(255, 99, 132, 0.8)',
                                                'rgba(54, 162, 235, 0.8)',
                                                'rgba(255, 206, 86, 0.8)',
                                                'rgba(75, 192, 192, 0.8)',
                                                'rgba(153, 102, 255, 0.8)',
                                                'rgba(255, 159, 64, 0.8)'
                                                // Thêm màu sắc khác nếu có nhiều hơn
                                            ],
                                        }],
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        tooltips: {
                                            callbacks: {
                                                label: function(tooltipItem, data) {
                                                    var dataset = data.datasets[tooltipItem.datasetIndex];
                                                    var currentValue = dataset.data[tooltipItem.index];
                                                    return 'Total Time: ' + currentValue + ' hours';
                                                }
                                            }
                                        }
                                    }
                                });
                            </script>
                        @endisset


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
