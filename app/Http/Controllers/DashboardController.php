<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return match ($user->role) {
            'staff' => redirect()->route('pengajuan.index'),
            'spv' => redirect()->route('approval.index', ['role' => 'spv']),
            'manager' => redirect()->route('approval.index', ['role' => 'manager']),
            'direktur' => redirect()->route('approval.index', ['role' => 'direktur']),
            'finance' => redirect()->route('finance.index'),
            default => redirect()->route('login'),
        };
    }
}
