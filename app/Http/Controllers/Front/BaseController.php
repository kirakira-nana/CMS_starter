<?php
/**
 * Date: 2019/4/1 Time: 16:22
 *
 * @author  Nana <seniorninja652@gmail.com>
 * @version v1.0.0
 */

namespace App\Http\Controllers\Front;

use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
