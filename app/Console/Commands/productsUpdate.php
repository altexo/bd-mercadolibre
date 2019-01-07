<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Products;
use App\Ml_data;
use DateTime;
class productsUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update products price in database';

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
     * @return mixed
     */
    public function handle()
    {
        // $current_date =  new \DateTime();
        // $product = Products::join('provider', 'products.provider_id', '=', 'provider.id')->where('provider.asin', '')
        echo "Update command called";
    }
}
