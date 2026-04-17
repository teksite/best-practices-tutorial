<?php

namespace Modules\User\Http\Controllers\Api\V1\Preferences;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Modules\Auth\Http\Requests\CheckUserFormRequest;
use Modules\Main\Services\ResponseJson;
use Modules\User\Models\User;
use Modules\User\Services\NotificationPreferencesService;

class PreferencesController extends Controller
{
    protected NotificationPreferencesService $service;
    protected User|Authenticatable $user;

    public function __construct()
    {
        $user = auth('sanctum')->user();
        $this->user = $user;
        $this->service = new NotificationPreferencesService($user);
    }

    public function index()
    {
        return ResponseJson::success($this->service->getPreferences());
    }

    public function update(Request $request)
    {
        try {
            ResponseJson::Success([],
                trans('main::messages.overall.update_success')
            );
        } catch (\Exception $exception) {
            return ResponseJson::Failed(
                [
                    'serever' => $exception->getMessage(),
                ],
                trans('main::messages.overall.server_wrong'),
            );
        }
    }
}
