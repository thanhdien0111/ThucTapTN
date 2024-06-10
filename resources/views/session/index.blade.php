<!-- resources/views/session/index.blade.php -->

<html>
<head>
    <title>Driving Sessions</title>
</head>
<body>
    <h1>Driving Sessions</h1>

    @if(session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div style="color: red;">{{ session('error') }}</div>
    @endif

    <form action="{{ url('/checkin') }}" method="post">
        @csrf
        <button type="submit">Checkin</button>
    </form>

    <form action="{{ url('/checkout') }}" method="post">
        @csrf
        <button type="submit">Checkout</button>
    </form>

    <!-- Thêm biểu đồ hiển thị thời gian đã lái tại đây -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>
