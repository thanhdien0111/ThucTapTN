<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Session;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $sessions = Session::where('user_id', auth()->user()->id)
        ->whereNotNull('checkout_time')
        ->get();

    // Xử lý dữ liệu để đưa vào biểu đồ
    $chartData = [
        'labels' => $sessions->pluck('checkin_time')->map(function ($date) {
            return \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s');
        }),
        'data' => $sessions->pluck('distance'),
    ];

    return view('home', compact('sessions', 'chartData'));
    }
    public function getChartData()
{
    $sessions = Session::where('user_id', auth()->user()->id)
    ->whereNotNull('checkout_time')
    ->get();

$labels = $sessions->pluck('checkin_time')->map(function ($date) {
    $date = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
    return $date->format('Y-m-d H:i:s');
});

$data = $sessions->map(function ($session) {
    return $this->calculateTotalTime($session);
});

return [
    'labels' => $labels,
    'data' => $data,
];
}

public function getTotalHours($session)
{
    if ($session->checkout_time) {
        return Carbon::parse($session->checkout_time)->diffInHours(Carbon::parse($session->checkin_time));
    }

    return null; // Hoặc bạn có thể trả về một giá trị mặc định khác nếu phiên chưa kết thúc
}

private function calculateTotalTime($session)
{
    if ($session->checkout_time) {
        return \Carbon\Carbon::parse($session->checkout_time)->diffInMinutes(\Carbon\Carbon::parse($session->checkin_time));
    } else {
        return null;
    }
}
}
