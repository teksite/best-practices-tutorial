<?php

namespace Modules\Auth\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Actions\AuthTokenAction;
use Modules\Auth\Http\Requests\Auth\ChangeUserRequest;
use Modules\Auth\Http\Requests\Auth\VerifyRequest;
use Modules\Auth\Http\Requests\Notification\ChangeUserPreferenceRequest;
use Modules\User\Actions\MarkVerifyUser;
use Modules\User\Actions\ResetUserPassword;
use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Http\Requests\Auth\CheckUserRequest;
use Modules\Auth\Http\Requests\Auth\ForgotPasswordRequest;
use Modules\Auth\Http\Requests\Auth\LoginRequest;
use Modules\Auth\Http\Requests\Auth\RegisterRequest;
use Modules\Auth\Services\VerificationTokenService;
use Modules\Main\Services\ApiResponse;
use Modules\User\Actions\CreateUser;
use Modules\User\Actions\UpdateUser;
use Modules\User\Models\User;
use Modules\User\Services\NotificationPreferenceService;
use Modules\User\Transformers\NotificationCollection;
use Modules\User\Transformers\NotificationPreferenceResource;
use Modules\User\Transformers\NotificationResource;
use Modules\User\Transformers\UserResource;
use function PHPUnit\Framework\isInstanceOf;

class NotificationController extends Controller
{

    public function index(Request $request)
    {
        $status=$request->query('status' , 0);
        $user= auth()->user();
        if ($status == 0){
            $notifications=$user->unreadNotifications;

        }else{
            $notifications=$user->readNotifications;
            $user->unreadNotifications->markAsRead();

        }

        return NotificationResource::collection($notifications);

    }




}
