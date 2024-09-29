<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Carbon;
use App\Models\CompanyUser;
use Illuminate\Http\Request;
use App\Models\ManagementSDM\Leave;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ManagementSDM\Schedule;
use App\Models\ManagementSDM\Attendance;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function getAttendanceToday()
    {
        $userId = Auth::user()->id;
        $today = now()->toDateString();
        $currentMonth = now()->month;

        $attendanceToday = Attendance::select('check_in', 'check_out')
                                ->where('user_id', $userId)
                                ->whereDate('created_at', $today)
                                ->first();

        $attendanceThisMonth = Attendance::select('check_in', 'check_out', 'created_at')
                                ->where('user_id', $userId)
                                ->whereMonth('created_at', $currentMonth)
                                ->get()
                                ->map(function ($attendance) {
                                    return [
                                        'check_in' => $attendance->check_in,
                                        'check_out' => $attendance->check_out,
                                        'date' => $attendance->created_at->toDateString()
                                    ];
                                });

        return response()->json([
            'success' => true,
            'data' => [
                'today' => $attendanceToday,
                'this_month' => $attendanceThisMonth
            ],
            'message' => 'Success get attendance today'
        ]);
    }

    public function getSchedule()
    {
        $schedule = Schedule::with(['branch', 'shift'])
                        ->where('user_id', Auth::user()->id)
                        ->first();
        $today = Carbon::today()->format('Y-m-d');
        $approvedLeave = Leave::where('user_id', Auth::user()->id)
                            ->where('status', 'approved')
                            ->whereDate('start_date', '<=', $today)
                            ->whereDate('end_date', '>=', $today)
                            ->exists();

        if ($approvedLeave) {
            return response()->json([
                'success' => true,
                'message' => 'Anda tidak dapat melakukan presensi karena sedang cuti',
                'data' => null
            ]);
        }

        if ($schedule->is_banned) {
            return response()->json([
                'success' => false,
                'message' => 'You are banned',
                'data' => null
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Success get schedule',
                'data' => $schedule
            ]);
        }
        
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'data' => $validator->errors()
            ], 422);
        }

        $schedule = Schedule::where('user_id', Auth::user()->id)->first();

        $company = CompanyUser::where('user_id', Auth::user()->id)->first();

        $today = Carbon::today()->format('Y-m-d');
        $approvedLeave = Leave::where('user_id', Auth::user()->id)
                            ->where('status', 'approved')
                            ->whereDate('start_date', '<=', $today)
                            ->whereDate('end_date', '>=', $today)
                            ->exists();

        if ($approvedLeave) {
            return response()->json([
                'success' => true,
                'message' => 'Anda tidak dapat melakukan presensi karena sedang cuti',
                'data' => null
            ]);
        }

        if ($schedule) {
            $attedance = Attendance::where('user_id', Auth::user()->id)
                            ->whereDate('created_at', date('Y-m-d'))->first();
            if (!$attedance) {
                $attedance = Attendance::create([
                    'company_id' => $company->company_id,
                    'user_id' => Auth::user()->id,
                    'branch_id' => $schedule->branch_id,
                    'employee_id' => $schedule->employee_id,
                    'schedule_id' => $schedule->id,
                    'date' => date('Y-m-d'),
                    'schedules_latitude' => $schedule->branch->latitude,
                    'schedules_longitude' => $schedule->branch->longitude,
                    'schedules_check_in' => $schedule->shift->start_time,
                    'schedules_check_out' => $schedule->shift->end_time,
                    'latitude_check_in' => $request->latitude,
                    'longitude_check_in' => $request->longitude,
                    'check_in' => Carbon::now()->toTimeString(),
                    'status' => 'present',
                    'note' => 'Presensi Masuk' . ' ' . Carbon::now()->toTimeString(),
                ]);
            } else {
                $attedance->update([
                    'latitude_check_out' => $request->latitude,
                    'longitude_check_out' => $request->longitude,
                    'check_out' => Carbon::now()->toTimeString(),
                    'status' => 'present',
                    'note' => 'Presensi Pulang' . ' ' . Carbon::now()->toTimeString(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Success store attendance',
                'data' => $attedance
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No schedule found',
                'data' => null
            ]);
        }
    }

    public function getAttendanceByMonthYear($month, $year)
    {
        $validator = Validator::make(['month' => $month, 'year' => $year], [
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2023|max:'.date('Y')
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'data' => $validator->errors()
            ], 422);
        }

        $userId = Auth::user()->id;
        $attendanceList = Attendance::select('check_in', 'check_out', 'created_at')
                                ->where('user_id', $userId)
                                ->whereMonth('created_at', $month)
                                ->whereYear('created_at', $year)
                                ->get()
                                ->map(function ($attendance) {
                                    return [
                                        'check_in' => $attendance->check_in,
                                        'check_out' => $attendance->check_out,
                                        'date' => $attendance->created_at->toDateString()
                                    ];
                                });

        return response()->json([
            'success' => true,
            'data' => $attendanceList,
            'message' => 'Success get attendance by month and year'
        ]);

    }

    public function banned()
    {
        $schedule = Schedule::where('user_id', Auth::user()->id)->first();
        if ($schedule) {
            $schedule->update([
                'is_banned' => true
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Success banned schedule',
            'data' => $schedule
        ]);
    }
    
    public function getImage()
    {
        $user = Auth::user()->id;
        return response()->json([
            'success' => true,
            'message' => 'Success get image',
            // 'data' => $user->image_url
            'data' => $user->image
        ]);
    }

}
