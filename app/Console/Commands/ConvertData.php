<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Exports\ResumeExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Rs\JsonLines\JsonLines;

class ConvertData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catch:convert-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting....');

        $results = (new JsonLines())->delineFromFile(env('URL_FILE'));

        $arrayInputData = json_decode($results);

        $arrayInputData = collect($arrayInputData)->filter(function ($value, $key) {
            return count($value->items) > 0;
        })->all();

        $arrayOutputData = [];
        if(!empty($arrayInputData)) {
            foreach ($arrayInputData as $key => $value) {
                $totalPrice = collect($value->items)->sum('unit_price');

                $discountPrice = 0;

                $totalOrderValue = $totalPrice - $discountPrice;

                $averagePrice = collect($value->items)->avg('unit_price');
                $totalUnits = collect($value->items)->sum('quantity');

                $arrayOutputData[] = [
                    'order_id' => $value->order_id,
                    'order_datetime' => Carbon::parse($value->order_date)->setTimezone('UTC')->toIso8601String(),
                    'total_order_value' => $totalOrderValue,
                    'average_unit_price' => $averagePrice,
                    'distinct_unit_count' => count($value->items),
                    'total_units_count' => $totalUnits,
                    'customer_state' => $value->customer->shipping_address->state,
                ];
            }
        }

        $progress = Excel::store(new ResumeExport($arrayOutputData), '/public/output.csv');

        if($progress) {
            $this->info('Task done');
        }
    }
}
