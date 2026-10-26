<?php

namespace App\Http\Controllers\Web\Setting;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Page;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Notification;
use App\Models\DeviceToken;
use App\Services\FcmNotificationService;


class NotificationController extends Controller
{
    



    public function __construct()
    {
        $this->locales = Language::all();
        view()->share([
            'locales' => $this->locales,

        ]);
    }


 

    public function image_extensions(){
        return array('jpg','png','jpeg','gif','bmp');
    }

   


  

public function create(Request $request)
{
    $validator = Validator::make($request->all(), [
        "title" => "required|string",
        "message" => "required|string",
        "target_type" => "required|in:all,user",
        "users" => "required_if:target_type,user|array",
        "users.*" => "exists:users,id",
    ]);

    if ($validator->fails()) {
        return mainResponse(false, "", null, 203, "items", $validator);
    }

    $tokens = $request->target_type === "all"
        ? DeviceToken::pluck("device_token")->toArray()
        : DeviceToken::whereIn("user_id", $request->users)->pluck("device_token")->toArray();

    if (empty($tokens)) {
        return mainResponse(false, __("common.no_tokens_found"), "", 200, "items", "");
    }

    $userIds = $request->target_type === "all" ? User::pluck('id')->toArray() : $request->users;
    $successCount = FcmNotificationService::sendPanelNotification(
        $userIds,
        $request->title,
        $request->message
    );



    $notification = new Notification();
    $notification->title = $request->title;
    $notification->body = $request->message;
    $notification->target_type = $request->target_type;
    $notification->users = $request->target_type === "user" ? json_encode($request->users) : null;
    $notification->save();

    foreach ($userIds as $userId) {
        DB::table("notification_user")->insert([
            "user_id" => $userId,
            "notification_id" => $notification->id,
            "read" => 0,
            "created_at" => now(),
            "updated_at" => now(),
        ]);
    }

    return mainResponse(true, __("common.success"), ["sent" => $successCount, "total" => count($tokens)], 200, "items", "");
}





   

    public function getAll(Request $request)
    {




// $serviceAccount = json_decode(file_get_contents(storage_path('app/firebase-service-account.json')), true);

//  $jwt = \Firebase\JWT\JWT::encode([
//         "iss" => $serviceAccount['client_email'],
//         "scope" => "https://www.googleapis.com/auth/firebase.messaging",
//         "aud" => "https://oauth2.googleapis.com/token",
//         "iat" => time(),
//         "exp" => time() + 3600
//     ], $serviceAccount['private_key'], 'RS256');

//     $tokenResponse = Http::asForm()->withOptions([
//         'verify' => false,  // ⬅ هنا نعطل SSL
//     ])->post('https://oauth2.googleapis.com/token', [
//         'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
//         'assertion' => $jwt,
//     ]);

//     if (!$tokenResponse->successful()) {
//         return mainResponse(false, "Cannot get FCM access token", "", 500, "items", "");
//     }

//     $accessToken = $tokenResponse->json()['access_token'];

// $deviceToken = "d17jB9uZ-Eyxm_APgJMcZy:APA91bEq08xRnHaFNltfDk9yuxgMUG_S-Xk2CLQbDN_GgTPL3cigfj3bRC0NInVxTRElU1FyIrD6qjFT-Nn7ECNzdYy_Le8YZZKa25ThdY6n5bNBKSGsz44"; // توكن جهاز المستخدم

// $data = [
//     "message" => [
//         "token" => $deviceToken,
//         "notification" => [
//             "title" => "Test Title",
//             "body" => "Test message",
//         ],
//         "data" => [
//             "click_action" => "FLUTTER_NOTIFICATION_CLICK"
//         ]
//     ]
// ];

// $response = Http::withHeaders([
//     "Authorization" => "Bearer $accessToken",
//     "Content-Type" => "application/json",
// ])->withOptions([
//     "verify" => false, // ⬅ تعطيل SSL مؤقتًا
// ])->post("https://fcm.googleapis.com/v1/projects/{$serviceAccount['project_id']}/messages:send", $data);

// dd($response);

























         $id = auth('sanctum')->id();

            $items = Notification::query();

            if($request->has('search') && !empty($request->search)) {
                $items->where(function($query) use ($request) {
                    $query->whereTranslationLike('name','%'.$request->search.'%')
                          ->orWhereTranslationLike('description', '%' . $request->search . '%');
                });
            }

            if(isset($request->pagination) && $request->pagination == 1) {
                $items = $items->orderBy('id','DESC')->paginate(10); 
            } else {
                $items = $items->orderBy('id','DESC')->get();
            }

            $message = "success return";

            return mainResponse(true, $message, $items, 200, 'items', '');
    }



   
    public function delete($id)
    {
        if(Notification::where('id',$id)->delete()){
            $message ="success return";
            return mainResponse(true, $message , '', 200, 'items','');
        }else{
            $message ="Error happens";
            return mainResponse(false, $message , '', 203, 'items','');
        }
        
    }

    

}