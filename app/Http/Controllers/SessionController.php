<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Session;
use Carbon\Carbon;

use Stevebauman\Location\Facades\Location;

class SessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $chartData = $this->getChartData();
        return view('session.index');
    }

    public function checkin(Request $request)
    {
        // Kiểm tra xem người dùng có phiên chưa checkout hay không
        $lastSession = Session::where('user_id', auth()->user()->id)->latest('checkin_time')->first();
    
        if ($lastSession && !$lastSession->checkout_time) {
            return redirect('/home')->with(['error' => 'You must checkout before checking in again.']);
        }
    
        // Logic để lấy thông tin tọa độ (latitude và longitude) từ form hoặc các nguồn dữ liệu khác
        $latitude = 10.7769; // Điều này chỉ là giả định, bạn cần lấy từ dữ liệu thực tế
        $longitude = 106.7009; // Điều này chỉ là giả định, bạn cần lấy từ dữ liệu thực tế
    
        // Lấy tọa độ của người dùng từ địa chỉ IP
        $location = Location::get($request->ip());
    
        if ($location) {
            $latitude = $location->latitude;
            $longitude = $location->longitude;
        }
    
        // Lưu thông tin vào database
        $session = new Session();
        $session->user_id = auth()->user()->id;
        $session->user_name = auth()->user()->name;
        $session->checkin_time = now();
        $session->latitude = $latitude;
        $session->longitude = $longitude;
        $session->save();
    
        // Kiểm tra thời gian chạy của phiên hiện tại
        $currentSessionTime = now()->diffInMinutes($session->checkin_time);
    
        // Kiểm tra xem thời gian chạy của phiên hiện tại có vượt quá 10 giờ không
        if ($currentSessionTime > 600) {
            // Nếu vượt quá, hủy bỏ phiên hiện tại và thông báo lỗi
            $session->delete();
            return redirect('/home')->with(['error' => 'You cannot check in for more than 10 hours in a day.']);
        }
    
        // Trả về dữ liệu cho biểu đồ
        $chartData = $this->getChartData();
    
        // Kiểm tra nếu có dữ liệu cho biểu đồ, thì chuyển hướng với thông báo thành công
        if ($chartData) {
            return redirect('/home')->with(['status' => 'Check-in successful!', 'chartData' => $chartData]);
        }
    
        // Nếu không có dữ liệu cho biểu đồ, chỉ chuyển hướng mà không hiển thị thông báo
        return redirect('/home');
    }
    

    
    public function checkout(Request $request)
    {
        $user_id = auth()->id();

        $session = Session::where('user_id', $user_id)
            ->whereNull('checkout_time')
            ->orderBy('checkin_time', 'desc')
            ->first();

        if (!$session) {
            return redirect()->back()->with('error', 'No open session found.');
        }

        $session->checkout_time = now();
        $session->save();

        return redirect()->back()->with('success', 'Checkout successful.');
    }

    public function calculateNextSession()
    {
        $user_id = auth()->id();

        $totalHoursInDay = 24;
        $totalAllowedHours = 10;
        $currentSessions = Session::where('user_id', $user_id)
            ->whereBetween('checkin_time', [now()->subDay(), now()])
            ->get();

        $totalHoursUsed = 0;
        foreach ($currentSessions as $session) {
            $totalHoursUsed += $session->checkin_time->diffInHours($session->checkout_time);
        }

        $remainingHours = $totalAllowedHours - $totalHoursUsed;

        return [
            'remaining_hours' => $remainingHours,
            'next_session_time' => now()->addHours($remainingHours),
        ];
    }
    private function getChartData()
{
    $sessions = Session::where('user_id', auth()->user()->id)
        ->whereNotNull('checkout_time')
        ->get();

    $chartData = [
        'labels' => $sessions->pluck('checkin_time')->map(function ($date) {
            // Kiểm tra nếu $date là một chuỗi, chuyển nó thành đối tượng Carbon
            $date = is_string($date) ? \Carbon\Carbon::parse($date) : $date;

            return $date->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s');
        }),
        'data' => $sessions->pluck('distance'),
        'total_time' => $sessions->map(function ($session) {
            // Tính tổng thời gian cho mỗi phiên (checkout_time - checkin_time)
            $checkoutTime = \Carbon\Carbon::parse($session->checkout_time);
            $checkinTime = \Carbon\Carbon::parse($session->checkin_time);

            return $checkoutTime->diffInMinutes($checkinTime); // Kết quả là phút
        }),
    ];

    return $chartData;
}
public function getNextRunTime($userId)
{
    $lastSession = Session::where('user_id', $userId)->latest('checkin_time')->first();

    if ($lastSession) {
        $checkoutTime = $lastSession->checkout_time ?: now();
        $nextRunTime = Carbon::parse($checkoutTime)->addHours(24);
    } else {
        // Nếu chưa có phiên nào, trả về thời điểm hiện tại + 10 giờ
        $nextRunTime = now()->addHours(10);
    }

    // Kiểm tra nếu thời gian chạy tiếp theo vượt quá 10 giờ trong 1 ngày, sửa lại thành 10 giờ
    if ($nextRunTime->diffInHours(now()) > 10) {
        $nextRunTime = now()->addHours(10);
    }

    return $nextRunTime;
}

public function isAllowedToRun($userId)
{
    $nextRunTime = $this->getNextRunTime($userId);
    $currentTime = now();

    return $currentTime >= $nextRunTime;
}
}
