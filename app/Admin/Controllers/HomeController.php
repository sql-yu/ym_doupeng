<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Card\Cpu;
use App\Admin\Actions\Card\Tickets;
use App\Admin\Metrics\Examples;
use App\Http\Controllers\Controller;
use Dcat\Admin\Http\Controllers\Dashboard;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Metrics\SingleRound;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        $row = function (Row $row){

        };
        return $content
            ->header('Dashboard')
            ->description('Description...')
            ->body($row);
//                function (Row $row)use($cpu) {
//                $row->column(6, function (Column $column)use($cpu) {
//                    $column->row(function (Row $row)use($cpu) {
//                        foreach($cpu as $k=> $c){
//                            $n = $k+1;
//                            $row->column(6, new Cpu("CPU-{$n}",null,$c));
//                        }
//                    });
//                });
//
//                $row->column(6, function (Column $column) {
//                    $column->row(function (Row $row) {
//                        $row->column(10, new Tickets());
//
//                    });
//                });
//
//                }
//                );
    }
}
