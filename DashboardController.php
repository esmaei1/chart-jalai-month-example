<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Tag;
use App\User;
use App\Viewpoint;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PharIo\Manifest\RequiresElementTest;

class DashboardController extends Controller
{
	public function index(Request $request)
	{
	    $current_year = $year = jdate(date('Y-m-d'))->getYear();;

	    return view('admin.index', [
	        'current_year' => $current_year
		]);
    }

    public function getCommentsInYearByMonth(Request $request)
    {
        $year = $request->year;
        // if request year is null get current year
        if (is_null($year)){
            $year = jdate(date('Y-m-d'))->getYear();
        }

        $comments_count = [];

        for ($i = 1; $i <= 12; $i++){
            $month = $i;
            $day = 1;
            $count_days_in_month = Verta::parse($year . '-' . $month . '-' . $day)->daysInMonth;

            $month_begin_array = Verta::getGregorian($year, $month, $day);
            $month_begin = $month_begin_array[0] . '-' . $month_begin_array[1] . '-' . $month_begin_array[2] . ' ' . '00:00:00';
            $month_end_array = Verta::getGregorian($year, $month, $count_days_in_month);
            $month_end = $month_end_array[0] . '-' . $month_end_array[1] . '-' . $month_end_array[2] . ' ' . '23:59:59';

            $comments_count[$i - 1] = Viewpoint::where('created_at', '>=', $month_begin)
                ->where('created_at', '<=', $month_end)
                ->get()
                ->count();
        }

        $data = [];
        $data['comments_count'] = $comments_count;
        $data['month_names'] = $this->monthNames();

        return $data;
    }

    public function monthNames()
    {
        return [
            'فروردین',
            'اردیبهشت',
            'خرداد',
            'تیر',
            'مرداد',
            'شهریور',
            'مهر',
            'آبان',
            'آذر',
            'دی',
            'بهمن',
            'اسفند'
        ];

    }

    public function getCommentsInMontByDay(Request $request)
    {
        $day = 1;
        $month = $request->month;
        $year = $request->year;

        $now = Verta::now();
        if (is_null($month)){
            $month = $now->month;
        }
        if (is_null($year)){
            $year = $now->year;
        }

        $count_days_in_month = Verta::parse($year . '-' . $month . '-' . $day)->daysInMonth;
        $month_begin_array = Verta::getGregorian($year, $month, $day);
        $month_begin = $month_begin_array[0] . '-' . $month_begin_array[1] . '-' . $month_begin_array[2] . ' ' . '00:00:00';
        $month_end_array = Verta::getGregorian($year, $month, $count_days_in_month);
        $month_end = $month_end_array[0] . '-' . $month_end_array[1] . '-' . $month_end_array[2] . ' ' . '23:59:59';

        $comments = DB::table('comments')->selectRaw('date(created_at) as date, count(*) as number_of_comments')
            ->where('created_at', '>=', $month_begin)
            ->where('created_at', '<=', $month_end)
            ->groupBy('date')
            ->get();

        $comments_array = [];
        foreach ($comments as $comment){
            $date  = Verta::instance($comment->date);
            $comments_array[(int)$date->day] = $comment->number_of_comments;
        }

        $data = [];
        $data['comments'] = $comments;
        $comments_count = [];
        $days = [];

        for ($i = 1; $i <= $count_days_in_month; $i++){
            $days[] = $i;
            if (isset($comments_array[$i])){
                $comments_count[] = $comments_array
                [$i];
            }else{
                $comments_count[]  = 0;
            }
        }



        $data['days'] = $days;
        $data['comments_count'] = $comments_count;

        return $data;
    }
}
