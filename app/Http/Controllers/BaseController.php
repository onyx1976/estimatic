<?php

namespace App\Http\Controllers;

use App\Services\System\SettingsService;
use Illuminate\Routing\Controller as Controller;

/**
 * Base controller for shared functionality
 *
 * @method static create(array $array)
 * @method static find(mixed $id)
 */
class BaseController extends Controller
{
    /* Shared settings access across controllers */
    public function __construct(
        protected SettingsService $settings
    ) {
        /* Access in child controllers as $this->settings */
    }
}
