<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use App\Models\User;

use App\Mail\WelcomeUserMail;
use App\Mail\SendUserPasswordMail;

use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Two\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use App\Models\DeviceToken;

use Illuminate\Support\Facades\Hash;

use Carbon\Carbon;


 

class AuthController extends Controller
{
  

    public function image_extensions(){
        return ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'bmp', 'webp', 'svg', 'heic', 'heif', 'avif'];
    }

    protected function profileUserPayload(int $userId): array
    {
        $row = User::query()
            ->select(['id', 'name', 'email', 'mobile', 'logo', 'gender', 'country_id', 'city_id', 'created_at'])
            ->whereKey($userId)
            ->first();

        if (!$row) {
            return [];
        }

        $rawLogo = $row->getRawOriginal('logo');
        $createdAt = $row->getRawOriginal('created_at');

        return [
            'id' => $row->id,
            'name' => $row->name,
            'email' => $row->email,
            'mobile' => $row->mobile,
            'logo' => $rawLogo ? url($rawLogo) : null,
            'gender' => $row->gender,
            'country_id' => $row->country_id,
            'city_id' => $row->city_id,
            'created_at' => $createdAt,
            'account_age' => $createdAt ? Carbon::parse($createdAt)->diffForHumans() : null,
        ];
    }

    protected function resolveProfileImageExtension($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        if (in_array($extension, $this->image_extensions(), true)) {
            return $extension === 'jpeg' ? 'jpg' : $extension;
        }

        $mime = strtolower((string) $file->getMimeType());
        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/pjpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
            'image/svg+xml' => 'svg',
            'image/heic' => 'heic',
            'image/heif' => 'heif',
            'image/avif' => 'avif',
        ];

        return $mimeMap[$mime] ?? '';
    }

    protected function storeProfileLogo($file): ?string
    {
        if (!$file) {
            return null;
        }

        if (!$file->isValid()) {
            throw new \RuntimeException($file->getErrorMessage() ?: 'Invalid upload');
        }

        $extension = $this->resolveProfileImageExtension($file);
        if ($extension === '') {
            return null;
        }

        $destinationPath = public_path('uploads/user');
        File::ensureDirectoryExists($destinationPath, 0755, true);

        $fileName = uniqid('profile_', true) . '.' . $extension;
        $file->move($destinationPath, $fileName);

        return 'uploads/user/' . $fileName;
    }

    public function pdf_extensions(){
        return array('pdf');
    }

    public function login(Request $request)
    {
        $lang = app()->getLocale();

        // 🔹 الرسائل المخصصة حسب اللغة
        $messages = [
            'ar' => [
                'flag.required' => 'حقل نوع تسجيل الدخول مطلوب.',
                'flag.in' => 'قيمة نوع تسجيل الدخول يجب أن تكون email أو mobile.',
                'password.required' => 'حقل كلمة المرور مطلوب.',
                'email_or_username.required' => 'حقل البريد الإلكتروني أو اسم المستخدم مطلوب.',
                'mobile.required' => 'حقل رقم الجوال مطلوب.',
            ],
            'en' => [
                'flag.required' => 'The login type field is required.',
                'flag.in' => 'The login type must be either email or mobile.',
                'password.required' => 'The password field is required.',
                'email_or_username.required' => 'The email or username field is required.',
                'mobile.required' => 'The mobile number field is required.',
            ],
        ];

        $msg = $messages[$lang] ?? $messages['en'];

        // ✅ التحقق الأساسي
        $validator = Validator::make($request->all(), [
            'flag'     => 'required|in:email,mobile',
            'password' => 'required|string',
        ], $msg);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 422, 'items', $validator);
        }

        $flag = $request->flag;
        $password = $request->password;

        // ✅ في حال الدخول بالبريد الإلكتروني
        if ($flag === 'email') {
            $validator = Validator::make($request->all(), [
                'email_or_username' => 'required|string',
            ], $msg);

            if ($validator->fails()) {
                return mainResponse(false, '', null, 422, 'items', $validator);
            }

            $user = User::where('email', $request->email_or_username)->first();

            if (!$user) {
                $message = $lang == 'ar'
                    ? 'البريد الإلكتروني غير موجود.'
                    : 'The email address does not exist.';
                return mainResponse(false, $message, null, 404, 'items', '');
            }

        // ✅ في حال الدخول برقم الجوال
        } elseif ($flag === 'mobile') {
            $validator = Validator::make($request->all(), [
                'mobile' => 'required|string',
            ], $msg);

            if ($validator->fails()) {
                return mainResponse(false, '', null, 422, 'items', $validator);
            }

            $user = User::where('mobile', $request->mobile)->first();

            if (!$user) {
                $message = $lang == 'ar'
                    ? 'رقم الجوال غير موجود.'
                    : 'The mobile number does not exist.';
                return mainResponse(false, $message, null, 404, 'items', '');
            }
        }

        // ✅ التحقق من كلمة المرور
        if (!Hash::check($password, $user->password)) {
            $message = $lang == 'ar'
                ? 'كلمة المرور غير صحيحة.'
                : 'The password is incorrect.';
            return mainResponse(false, $message, null, 400, 'items', '');
        }

        // ✅ إنشاء التوكن
        $user->token = $user->createToken('auth_token')->plainTextToken;

        $message = $lang == 'ar'
            ? 'تم تسجيل الدخول بنجاح.'
            : 'Login successful.';

        return mainResponse(true, $message, $user, 200, 'items', '');
    }





   public function register(Request $request)
    {
        $lang = app()->getLocale();

        $messages = [
            'en' => [
                'name.required' => 'The name field is required.',
                'name.min' => 'The name must be at least 3 characters.',
                'name.max' => 'The name may not be greater than 50 characters.',
                'email.required' => 'The email field is required.',
                'email.email' => 'The email must be a valid email address.',
                'email.unique' => 'The email has already been used.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 6 characters.',
                'password.confirmed' => 'Password confirmation does not match.',
                'agree.accepted' => 'You must agree to the terms and conditions.',
                'mobile.required' => 'Mobile number is required.',
                'mobile.min' => 'Mobile number is too short.',
                'mobile.max' => 'Mobile number is too long.',
                'mobile.unique' => 'This mobile number is already in use.',
            ],
            'ar' => [
                'name.required' => 'الاسم مطلوب.',
                'name.min' => 'الاسم يجب أن يحتوي على 3 أحرف على الأقل.',
                'name.max' => 'الاسم لا يجب أن يتجاوز 50 حرفاً.',
                'email.required' => 'البريد الإلكتروني مطلوب.',
                'email.email' => 'يجب إدخال بريد إلكتروني صالح.',
                'email.unique' => 'البريد الإلكتروني مستخدم مسبقاً.',
                'password.required' => 'كلمة المرور مطلوبة.',
                'password.min' => 'كلمة المرور يجب أن لا تقل عن 6 أحرف.',
                'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
                'agree.accepted' => 'يجب الموافقة على الشروط والأحكام.',
                'mobile.required' => 'رقم الجوال مطلوب.',
                'mobile.min' => 'رقم الجوال قصير جداً.',
                'mobile.max' => 'رقم الجوال طويل جداً.',
                'mobile.unique' => 'رقم الجوال مستخدم بالفعل.',
            ],
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'mobile' => 'required|string|min:8|max:20|unique:users,mobile',
            'agree' => 'accepted',
        ], $messages[$lang]);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        try {
            $confirmation_code = 1111;

            $newUser = new User();
            $newUser->name = $request->name;
            $newUser->email = $request->email;
            $newUser->password = bcrypt($request->password);
            $newUser->pwd = $request->password;
            $newUser->mobile = preg_replace('/\D+/', '', (string) $request->mobile);
            if ($request->filled('country_id')) {
                $newUser->country_id = $request->country_id;
            }
            if ($request->filled('city_id')) {
                $newUser->city_id = $request->city_id;
            }
            $newUser->verification = 0;
            $newUser->code = $confirmation_code;
            $newUser->gender = $request->input('gender', 'male');
            $newUser->status = 'active';

            if ($newUser->save()) {
                $message = $lang == 'en'
                    ? 'Account created successfully! Please verify your account through the SMS sent to your phone.'
                    : 'تم إنشاء الحساب بنجاح! يرجى تفعيل حسابك من خلال الرسالة النصية المرسلة إلى هاتفك.';
            } else {
                $message = $lang == 'en'
                    ? 'Account not created. Please try again.'
                    : 'حدث خطأ، يمكنك المحاولة مرة أخرى.';
            }

            return mainResponse(true, $message, auth('sanctum')->user(), 200, 'items', '');

        } catch (\Exception $e) {
            return mainResponse(false, 'حدث خطأ أثناء إنشاء الحساب، يرجى المحاولة لاحقاً.', null, 500, 'items', '');
        }
    }




    public function sendSms($item)
    {
        if(app()->getLocale()=='en'){
            $message = "Brandy acivation code  " . $item->code ;
        }else{
            $message = "كود التحقق من برادي هو " . $item->code;
        }

        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->post('https://your-sms-provider.com/api/send', [
                'form_params' => [
                    'to' => $request->mobile,
                    'message' => $message,
                    'sender' => 'YourAppName',
                    'api_key' => 'YOUR_API_KEY',
                ]
            ]);

            $body = json_decode((string) $response->getBody(), true);

        } catch (\Exception $e) {
            \Log::error('SMS sending failed: ' . $e->getMessage());
        }
    }



    public function verify(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string',
            'code'   => 'required|string',
        ]);

        try {
            $user = User::where('mobile', $request->mobile)
                        ->where('code', $request->code)
                        ->first();


            $m1 = __('common.unvalid_verfied');
            $m2 = __('common.verfied');
            $m3 = __('common.unverfied');

            if (!$user) {
                return mainResponse(false, $m1, null,422 , 'items', '');
            }

            $user->verification = 1; 
            $user->code = null; 
            $user->save();

            $user->token = $user->createToken('auth_token')->plainTextToken;

            return mainResponse(true, $m2, $user, 200, 'items', '');

        } catch (\Exception $e) {
            return mainResponse(false, $m3, null, 500, 'items', '');
        }
    }




    public function resendCode(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string',
        ]);

        try {
            $user = User::where('mobile', $request->mobile)->first();

            if (!$user) {
                return mainResponse(false, __('common.user_not_found'), null, 404, 'items', '');
            }

            $code = rand(1000, 9999);
            $user->code = $code;
            $user->save();

            // $this->sendSMS($user);

            return mainResponse(true, __('common.code_resent_successfully'), null, 200, 'items', '');

        } catch (\Exception $e) {
            return mainResponse(false, __('common.something_wrong'), null, 500, 'items', '');
        }
    }


    

    public function forget(Request $request)
    {
        

        $request->validate([
            'mobile' => 'required|string',
        ]);

        try {
            $user = User::where('mobile', $request->mobile)->first();

            if (!$user) {
                return mainResponse(false, __('common.user_not_found'), null, 404, 'items', '');
            }

            $code = rand(1000, 9999);
            $user->code = $code;
            $user->save();

            // $this->sendSMS($user);

            return mainResponse(true, __('common.code_resent_successfully'), null, 200, 'items', '');

        } catch (\Exception $e) {
            return mainResponse(false, __('common.something_wrong'), null, 500, 'items', '');
        }
    }


   

   public function setupNewPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile'   => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);


         if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }else{




        try {
            $user = User::where('mobile', $request->mobile)
                        ->where('verification', 1) // التأكد أنه تحقق مسبقا
                        ->first();

            if (!$user) {
                return mainResponse(false, __('common.unverfied'), null, 404, 'items', '');
            }

            $user->password = bcrypt($request->password);
            $user->pwd = $request->password;
            $user->save();

            $user->token = $user->createToken('auth_token')->plainTextToken;

            return mainResponse(true, __('common.update'), $user , 200, 'items', '');

        } catch (\Exception $e) {
            return mainResponse(false, __('common.something_wrong'), null, 500, 'items', '');
        }


         }
    }





    public function editInfo(Request $request)
    {


        
        $user = auth('sanctum')->user();

        $messages = [
            'old_password.required' => __('validation.old_password.required'),
            'old_password.current_password' => __('validation.old_password.current_password'),
            'password.min' => __('validation.password.min'),
            'confirmPassword.same' => __('validation.confirmPassword.same'),
            // باقي الرسائل زي ما كانت...
        ];

        $rules = [
            'name'       => 'sometimes|required|string|min:3|max:50',
            'email'      => 'sometimes|required|email|unique:users,email,' . $user->id,
            'mobile'     => 'sometimes|required|string|min:8|max:20|unique:users,mobile,' . $user->id,
            'gender'     => 'sometimes|required|in:male,female',
            'country_id' => 'sometimes|required|exists:items_categories,id',
            'city_id'    => 'sometimes|required|exists:items_categories,id',
            'password'   => 'nullable|min:6',
            'logo'       => 'nullable|file|max:8192',
        ];

        // إذا المستخدم حاب يغير الباسوورد، نخليه required يرسل old_password
        if ($request->filled('password')) {
            $rules['old_password'] = 'required';
        }


        if ($request->filled('password') && $request->password != $request->old_password) {
            
            $message = app()->getLocale() == 'en'
                ? 'password and confirm password not match.'
                : 'الباسورد وتأكيد الباسوورد غير متطابق .';

            return mainResponse(false, $message, null, 203, 'items', '');

        }



        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        if ($request->hasFile('logo')) {
            $extension = $this->resolveProfileImageExtension($request->file('logo'));
            if ($extension === '') {
                $msg = app()->getLocale() === 'ar'
                    ? 'نوع الصورة غير مدعوم. استخدم jpg أو png أو webp أو gif أو svg أو heic.'
                    : 'Unsupported image type. Use jpg, png, webp, gif, svg, or heic.';
                return mainResponse(false, $msg, null, 422, 'items', '');
            }
        }

        // تحقق من الباسوورد القديم إذا كان فيه طلب تغيير باسوورد
        // if ($request->filled('password')) {
        //     if (!Hash::check($request->old_password, $user->password)) {
        //         $msg = app()->getLocale() == 'en'
        //             ? 'The old password is incorrect.'
        //             : 'كلمة المرور القديمة غير صحيحة.';
        //         return mainResponse(false, $msg, null, 203, 'items', '');
        //     }
        //     // إذا صح، نغيّر الباسوورد
        //     $user->password = bcrypt($request->password);
        // } 

        try {
            $user->name       = $request->has('name') ? $request->name : $user->name;
            $user->email      = $request->has('email') ? $request->email : $user->email;
            $user->mobile     = $request->has('mobile')
                ? preg_replace('/\D+/', '', (string) $request->mobile)
                : $user->mobile;
            $user->gender     = $request->has('gender') ? $request->gender : $user->gender;
            $user->country_id = $request->has('country_id') ? $request->country_id : $user->country_id;
            $user->city_id    = $request->has('city_id') ? $request->city_id : $user->city_id;

            if ($request->filled('password')) {
                $user->password = bcrypt($request->password);
                $user->pwd = $request->password;
            }

            if ($request->hasFile('logo')) {
                $logoPath = $this->storeProfileLogo($request->file('logo'));
                if (!$logoPath) {
                    $msg = app()->getLocale() === 'ar'
                        ? 'تعذّر حفظ الصورة. جرّب ملفاً أصغر أو بصيغة أخرى.'
                        : 'Could not save the image. Try a smaller file or another format.';
                    return mainResponse(false, $msg, null, 422, 'items', '');
                }
                $user->logo = $logoPath;
            }

            $user->save();

            $message = app()->getLocale() == 'en'
                ? 'Profile updated successfully.'
                : 'تم تحديث الملف الشخصي بنجاح.';

            $u = $this->profileUserPayload($user->id);

            return mainResponse(true, $message, $u, 200, 'items', '');

        } catch (\Exception $e) {
            $msg = app()->getLocale() === 'ar'
                ? 'تعذّر تحديث الملف. حاول مرة أخرى.'
                : 'Could not update profile. Please try again.';
            return mainResponse(false, $msg, null, 500, 'items', '');
        }
    }





    public function user(Request $request)
    { 

        $user = auth('sanctum')->user();

        if(!$user || $user == null){
            return false;
        }

        $user = User::where('id',$user->id)->first();
        $message = __('common.return');
        return mainResponse(true, $message , $user, 200 , 'items','');
    }

   
    public function logout(Request $request)
    {
        $user = $request->user();
        $lang = app()->getLocale();

        $user->tokens()->delete();

        $message = ($lang == 'ar') 
            ? 'تم تسجيل الخروج بنجاح.' 
            : 'Logged out successfully.';

        return mainResponse(true, $message, null, 200, 'items', '');
    }





    public function updatePassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|confirmed', // حتاج new_password_confirmation
        ]);


        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $user = auth()->user();


        $user->password = bcrypt($request->password);
        $user->pwd = $request->new_password;
        $user->save();

            $message = __('common.update');
            return mainResponse(true, $message , null, 200 , 'items','');
    }




    public function login_facebook(Request $request){

          $token = $request->input('access_token');


        $response = Http::withoutVerifying()->get("https://graph.facebook.com/me?fields=id,name,email&access_token={$token}");

            if ($response->failed()) {
                return mainResponse(false, 'Invalid login' , null, 203 , 'items','');
            }

            $fbUser = $response->json();


            $user = User::firstOrCreate(
                ['facebook_id' => $fbUser['id']],
                [
                    'name' => $fbUser['name'],
                    'email' => $fbUser['email'] ?? "{$fbUser['id']}@facebook.com",
                    'password' => bcrypt(str()->random(16)),
                ]
            );

            $user->token = $user->createToken('facebook-login')->plainTextToken;


            $message = __('auth.login_success'); 

        return mainResponse(true, $message, $user, 200, 'items', '');


    }




    public function login_google(Request $request){


         $idToken = $request->input('id_token');

            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);

            if ($response->failed()) {
                return mainResponse(false, 'Invalid login' , null, 203 , 'items','');
            }

            $googleUser = $response->json();

            $user = \App\Models\User::firstOrCreate(
                ['google_id' => $googleUser['sub']],
                [
                    'name' => $googleUser['name'] ?? 'Unknown',
                    'email' => $googleUser['email'],
                    'password' => bcrypt(str()->random(16)),
                ]
            );

            $user->token = $user->createToken('google-login')->plainTextToken;


        $message = __('auth.login_success'); 
        return mainResponse(true, $message, $user, 200, 'items', '');


    }


    public function userInformations(Request $request)
    {
        $user = User::where('id',$request->user_id)->first();
        $message = __('common.return');
        return mainResponse(true, $message , $user , 200 , 'items','');
    }




    public function storeDeviceToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string',
            'device_type' => 'required|in:android,ios,web',
            // 'app_version' => 'nullable|string'
        ]);

        $user = auth()->user();

        DeviceToken::updateOrCreate(
            ['device_token' => $request->device_token],
            [
                'user_id' => $user->id ?? null,
                'device_type' => $request->device_type,
                // 'app_version' => $request->app_version,
                'ip' => $request->ip(),
                'is_active' => true
            ]
        );

        return mainResponse(true, __('common.create'), null, 200, 'items', '');
    }



    public function userStatus(Request $request)
    { 
        $user = User::findOrFail($request->id);

        $user->online = !$user->online;
        $user->save();

        $message = __('common.return');

        return mainResponse(true, $message, $user, 200, 'items', '');
    }






}

  
   


    







   
