<?php

namespace App\Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Models\Reports;

class IndexController extends Controller
{


    public function getModel()
    {
        return new Reports;
    }


}