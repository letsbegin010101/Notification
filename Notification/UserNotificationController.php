<?php

namespace App\Http\Controllers;

use App\user;
use App\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Support\Facades\Session;

class UserNotificationController extends Controller
{

    public static function show()


    {
        $user = Auth::user();
        if ($user) {


            $activeGlobalNotification = UserNotification::where('type', 'global')
                                        ->where('expired', 0)
                                        ->first();

            $paginator = UserNotification::where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->paginate(7);

            $notifications = new Collection($paginator->items());

            foreach ($notifications as $notification) { // I should just fetch the original created_At date and update time from javascript.
                $notification->created_at = Carbon::parse($notification->created_at)->diffForHumans();
            }

            if ($activeGlobalNotification) {
                $notifications->push($activeGlobalNotification);
            }
            $newNotificationCount = UserNotification::where('user_id', $user->id)
                ->where('read', 0)
                ->count();

            return ['notifications'=>$notifications,
                'newNotificationsCount' =>$newNotificationCount

            ];
        } else {
            return ['notifications'=>[],
                'newNotificationsCount' => 0
            ];
        }
    }



    public function createNotification($email,$type, $text, $tool, $tool_item_id,$read, $expired,  $url ) //$recipient sounds better than just $email
    {

        if ($type == "global"){
            UserNotification::create([
//                'user_id' => $email,
                'type' => $type,
                'text' => $text,
                'tool' => $tool,
                'tool_item_id' => $tool_item_id,
                'read' => $read,
                'expired' => $expired,
                'url' => $url,
            ]);
        }
        else {
            dump("email : ".$email);

            if (empty($email)){
                dd("email is null");
            }
            if (is_numeric($email)){
                $userId =$email;
                dump("if userid: " .$userId);
            }
            else {
                if (empty($email) || is_null($email)){
                    dd("email is null");
                }
//            $email = stringValue($email);
                dump("else email: " . $email);
                $userId = $this->convertEmailToUserid($email);
                dump("else userid: " .$userId);
            }



            UserNotification::create([
                'user_id' => $userId,
                'type' => $type,
                'text' => $text,
                'tool' => $tool,
                'tool_item_id' => $tool_item_id,
                'read' => $read,
                'expired' => $expired,
                'url' => $url,
            ]);
        }

    }


    function convertEmailToUserid ($email) {

        if (is_object($email) && property_exists($email, 'email')) {
            $email = $email->email;
        }

        return $userId = DB::table('dashboard_user')
            ->where('email', $email)
            ->value('id');

    }




}