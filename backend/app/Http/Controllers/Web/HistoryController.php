<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WeatherLog;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $logs = WeatherLog::where('user_id', $request->user()->id)
            ->with('chosenFragrance')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('app.history', compact('logs'));
    }
}
