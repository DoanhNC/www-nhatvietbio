<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Response;
use App\Models\EPostCategory;


class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * Convert SQL with ? placeholders into a full SQL string with bindings.
     */
    protected function toSqlWithBindings(string $sql, array $bindings): string
    {
        foreach ($bindings as $binding) {
            if (is_null($binding)) {
                $rep = 'NULL';
            } elseif ($binding instanceof \DateTimeInterface) {
                $rep = "'" . $binding->format('Y-m-d H:i:s') . "'";
            } elseif (is_bool($binding)) {
                $rep = $binding ? '1' : '0';
            } elseif (is_numeric($binding) && !is_string($binding)) {
                $rep = (string)$binding;
            } else {
                // không escape HTML để nhìn dấu nháy thật trong Network/Response
                $rep = "'" . addslashes((string)$binding) . "'";
            }

            // thay đúng thứ tự: pattern, replacement, subject, limit
            $sql = preg_replace('/\?/', $rep, $sql, 1);
        }
        return $sql;
    }

    /**
     * Format microtime float -> H:i:s.mmm (giờ:phút:giây.mili)
     */
    protected function fmtTs(float $t): string
    {
        $sec = (int)$t;
        $ms  = (int)round(($t - $sec) * 1000);
        return date('H:i:s', $sec) . sprintf('.%03d', $ms);
    }

    public function boot(): void
    {
        // View Composer để share menu categories cho layout web
        View::composer('layouts.web', function ($view) {
            $currentLang = session('locale', 'vi');
            $menuCategories = EPostCategory::getTreeForMenu($currentLang);
            $view->with('menuCategories', $menuCategories);
        });

        // thực hiện debug
        if (request()->query('debug') != 1) {
            return;
        }

        $queries = [];

        DB::listen(function ($query) use (&$queries) {
            // Laravel cung cấp $query->time (ms). Listen chạy sau khi query xong.
            $end    = microtime(true);
            $time_s = ((float)$query->time) / 1000.0;
            $begin  = $end - $time_s;

            $queries[] = [
                'begin' => $begin,
                'end'   => $end,
                'time'  => $time_s,
                'sql'   => $this->toSqlWithBindings($query->sql, $query->bindings),
            ];
        });

        // Ghi đè nội dung response để Network->Response chỉ còn block debug
        Event::listen(RequestHandled::class, function (RequestHandled $event) use (&$queries) {
            if (empty($queries)) {
                return;
            }

            $out = '';
            foreach ($queries as $q) {
                $out .= "<hr>\n";
                // $out .= "begin: " . $this->fmtTs($q['begin']) . "<br />\n";
                $out .= "(mysql): " . $q['sql'] . "<br />\n";
                // $out .= "end: " . $this->fmtTs($q['end']) . "<br />\n";
                $out .= "time: " . number_format($q['time'], 9, '.', '') . "<br />\n"; // giây, 9 chữ số thập phân
            }

            $event->response->setContent($out);
            $event->response->headers->set('Content-Type', 'text/html; charset=utf-8');
        });
    }
}
