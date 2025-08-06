<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Order;
use App\Models\Parnt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function showStatistics()
    {
        $this->authorize('manage_users');
        $coursesCount = Course::count();
        $usersCount = User::count();
        $parentsCount = Parnt::count();
        // $ordersCount = Order::count();
        $paidOrdersCount = Order::where('status', 'paid')->count();

        // إجمالي مبيعات الكلية
        $totalSales = Order::join('courses', 'orders.course_id', '=', 'courses.id')
            ->where('orders.status', 'paid')
            ->sum('courses.price');

        // مبيعات كل كورس على حدة
        $salesPerCourse = Course::withCount(['orders as paid_orders_count' => function ($query) {
            $query->where('status', 'paid');
        }])->get(['id', 'nameOfCourse', 'price'])->map(function ($course) {
            return [
                'id' => $course->id,
                'name' => $course->nameOfCourse,
                'price' => $course->price,
                'paid_orders_count' => $course->paid_orders_count,
                'total_sales' => $course->paid_orders_count * $course->price,
            ];
        });

        $gradesStatistics = Grade::withCount([
            'users as students_count', 
            'courses as courses_count',
            'courses as lessons_count' => function ($query) {
                $query->withCount('lessons');
            },
            'courses as exams_count' => function ($query) {
                $query->withCount('exams');
            },
        ])->get()->map(function ($grade) {
            return [
                'grade_id' => $grade->id,
                'grade_name' => $grade->grade,
                'students_count' => $grade->students_count,
                'courses_count' => $grade->courses_count,
                'lessons_count' => $grade->lessons_count,
                'exams_count' => $grade->exams_count,
            ];
        });


        // إحصائيات مبيعات الكتب
        $booksStats = DB::table('shipment_books')
            ->select('book_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(quantity * price) as total_revenue'))
            ->groupBy('book_id')
            ->get()
            ->map(function ($stat) {
                $book = \App\Models\Book::find($stat->book_id);
                return [
                    'book_id' => $stat->book_id,
                    'book_title' => $book ? $book->title : null,
                    'total_quantity' => $stat->total_quantity,
                    'total_revenue' => $stat->total_revenue
                ];
            });

        $totalBooksRevenue = $booksStats->sum('total_revenue');
        $totalAllSales = $totalSales + $totalBooksRevenue;

        $statistics = [
            'General_statistics' => [
                'Courses_count' => $coursesCount,
                'Users_count' => $usersCount,
                'Parents_count' => $parentsCount,
                'Paid_orders_count' => $paidOrdersCount,
                'Total_sales' => $totalSales,
                'Books_total_revenue' => $totalBooksRevenue,
                'All_total_sales' => $totalAllSales,
            ],
            'Grades_statistics' => $gradesStatistics,
            'Courses_sales' => $salesPerCourse,
            'Books_statistics' => $booksStats,
        ];

        return response()->json($statistics);
    }
}

