<?php

namespace App\Http\Controllers\User;

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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
        $locale = app()->getLocale();

        $messages = [
            'flag.required'     => $locale == 'ar' ? 'طريقة الدخول مطلوبة' : 'Login method is required',
            'flag.in'           => $locale == 'ar' ? 'طريقة الدخول غير صالحة' : 'Invalid login method',
            'password.required' => $locale == 'ar' ? 'كلمة المرور مطلوبة' : 'Password is required',
            'email_or_username.required' => $locale == 'ar' ? 'البريد الإلكتروني أو اسم المستخدم مطلوب' : 'Email or username is required',
            'mobile.required'   => $locale == 'ar' ? 'رقم الجوال مطلوب' : 'Mobile number is required',
        ];

        // التحقق الأساسي
        $validator = Validator::make($request->all(), [
            'flag'     => 'required|in:email,mobile',
            'password' => 'required|string',
        ], $messages);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 422, 'items', $validator);
        }

        $flag = $request->flag;
        $password = $request->password;

        // تحديد نوع الدخول
        if ($flag === 'email') {
            $validator = Validator::make($request->all(), [
                'email_or_username' => 'required|string',
            ], $messages);

            if ($validator->fails()) {
                return mainResponse(false, '', null, 422, 'items', $validator);
            }

            $user = User::where('email', $request->email_or_username)
                        ->first();

            if (!$user) {
                $message = $locale == 'ar' ? 'البريد الإلكتروني غير موجود' : 'The email address does not exist';
                return mainResponse(false, $message, null, 404, 'items', '');
            }

        } elseif ($flag === 'mobile') {
            $validator = Validator::make($request->all(), [
                'mobile' => 'required|string',
            ], $messages);

            if ($validator->fails()) {
                return mainResponse(false, '', null, 422, 'items', $validator);
            }

            $user = User::where('mobile', $request->mobile)
                        ->first();

            if (!$user) {
                $message = $locale == 'ar' ? 'رقم الجوال غير موجود' : 'The mobile number does not exist';
                return mainResponse(false, $message, null, 404, 'items', '');
            }
        }

        // التحقق من كلمة المرور
        if (!Hash::check($password, $user->password)) {
            $message = $locale == 'ar' ? 'كلمة المرور غير صحيحة' : 'The password is incorrect';
            return mainResponse(false, $message, null, 400, 'items', '');
        }

        // إنشاء التوكن وتسجيل الدخول بنجاح
        $user->token = $user->createToken('auth_token')->plainTextToken;

        $message = $locale == 'ar' ? 'تم تسجيل الدخول بنجاح' : 'Login successful';
        return mainResponse(true, $message, $user, 200, 'items', '');
    }




   public function register(Request $request)
    {
        // dd($request->all());

        $messages_ar = [
            'name.required' => 'الاسم مطلوب.',
            'name.string' => 'الاسم يجب أن يكون نصًا.',
            'name.min' => 'الاسم يجب أن لا يقل عن :min أحرف.',
            'name.max' => 'الاسم يجب أن لا يزيد عن :max أحرف.',

            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique' => 'هذا البريد الإلكتروني مستخدم بالفعل.',

            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'كلمة المرور يجب أن لا تقل عن :min أحرف.',
            'confirmPassword.required' => 'تأكيد كلمة المرور مطلوب.',
            'confirmPassword.same' => 'تأكيد كلمة المرور غير متطابق.',

            'mobile.required' => 'رقم الجوال مطلوب.',
            'mobile.string' => 'رقم الجوال غير صالح.',
            'mobile.min' => 'رقم الجوال قصير جداً.',
            'mobile.max' => 'رقم الجوال طويل جداً.',
            'mobile.unique' => 'رقم الجوال مستخدم بالفعل.',

            'terms.accepted' => 'يجب الموافقة على الشروط والأحكام.',
        ];

        $messages_en = [
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a valid string.',
            'name.min' => 'Name must be at least :min characters.',
            'name.max' => 'Name may not be greater than :max characters.',

            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'This email is already in use.',

            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least :min characters.',
            'confirmPassword.required' => 'Password confirmation is required.',
            'confirmPassword.same' => 'Password confirmation does not match.',

            'mobile.required' => 'Mobile number is required.',
            'mobile.string' => 'Mobile number is invalid.',
            'mobile.min' => 'Mobile number is too short.',
            'mobile.max' => 'Mobile number is too long.',
            'mobile.unique' => 'This mobile number is already in use.',

            'terms.accepted' => 'You must accept the terms and conditions.',
        ];

        // اختيار اللغة المناسبة
        $messages = app()->getLocale() === 'ar' ? $messages_ar : $messages_en;

        // التحقق
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'string', 'min:8'],
            'confirmPassword' => ['required', 'same:password'],
            'mobile' => 'required|string|min:8|max:20|unique:users,mobile',
            'terms' => 'accepted',
        ], $messages);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        } else {
            try {
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
                $newUser->verification = 1;
                $newUser->code = null;
                $newUser->gender = $request->input('gender', 'male');
                $newUser->status = 'active';

                if (!$newUser->save()) {
                    $message = app()->getLocale() == 'en'
                        ? 'Account not created. Please try again.'
                        : 'حدث خطأ، يمكنك إعادة المحاولة مرة أخرى';

                    return mainResponse(false, $message, null, 500, 'items', '');
                }

                $newUser->token = $newUser->createToken('auth_token')->plainTextToken;

                $message = app()->getLocale() == 'en'
                    ? 'Account created successfully.'
                    : 'تم إنشاء الحساب بنجاح.';

                return mainResponse(true, $message, $newUser, 200, 'items', '');
            } catch (\Exception $e) {
                dd($e);
                return mainResponse(false, 'An error occurred while creating your account. Please try again later.', null, 500, 'items', '');
            }
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
            'mobile' => 'required',
            'code'   => 'required',
        ]);


        $validator = Validator::make($request->all(), [
             'mobile' => 'required',
            'code'   => 'required',
        ]);


        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

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
            'mobile' => 'required',
        ]);

        try {
            $user = User::where('mobile', $request->mobile)->first();

            if (!$user) {
                return mainResponse(false, __('common.user_not_found'), null, 404, 'items', '');
            }

            // $code = rand(1000, 9999);
            $code = 1111;
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

            // $code = rand(1000, 9999);
            $code = 1111;
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
        // dd($request->all());
        
        $user = auth()->user();

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
            'mobile2'     => 'sometimes|required|string|min:8|max:20|unique:users,mobile,' . $user->id,
            'gender'     => 'sometimes|required|in:male,female',
            'country_id' => 'sometimes|required|exists:items_categories,id',
            'city_id'    => 'sometimes|required|exists:items_categories,id',
            'password'   => 'nullable|min:6',
            'logo'       => 'nullable|file|max:8192',
        ];

        // إذا المستخدم حاب يغير الباسوورد، نخليه required يرسل old_password
        if ($request->filled('password')) {
            $rules['oldPassword'] = 'required';
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
        //     if (!Hash::check($request->oldPassword, $user->password)) {
        //         $msg = app()->getLocale() == 'en'
        //             ? 'The old password is incorrect.'
        //             : 'كلمة المرور القديمة غير صحيحة.';
        //         return mainResponse(false, $msg, null, 203, 'items', '');
        //     }
        //     // إذا صح، نغيّر الباسوورد
        //     $user->password = bcrypt($request->password);
        //     $user->pwd = $request->password;
        // }

        try {
            $user->name       = $request->has('name') ? $request->name : $user->name;
            $user->email      = $request->has('email') ? $request->email : $user->email;
            $user->mobile     = $request->has('mobile2')
                ? preg_replace('/\D+/', '', (string) $request->mobile2)
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
            Log::error('editInfo failed', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $msg = app()->getLocale() === 'ar'
                ? 'تعذّر تحديث الملف. حاول مرة أخرى.'
                : 'Could not update profile. Please try again.';
            if (config('app.debug')) {
                $msg .= ' (' . $e->getMessage() . ')';
            }
            return mainResponse(false, $msg, null, 500, 'items', '');
        }
    }





    public function user(Request $request)
    {
        $id = auth()->id();
        $user = User::where('id',$id)->first();
        $message = __('common.return');
        return mainResponse(true, $message , $items, 200 , 'items','');
    }

   
    public function logout(Request $request)
    {
        $user = $request->user();
        // dd($user);
        
        if ($user->tokens()->delete()) {
        $message = 'logged out successfully';
            return mainResponse(true, $message , null, 200 , 'items','');
        } else {
        $message = 'logged out successfully';
            return mainResponse(true, $message , null, 200 , 'items','');
        }
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


    public function getAuth(){
        $user = auth()->user();
        $items = $this->profileUserPayload($user->id);
        $message ="ٌreturn Item successfully";
        return mainResponse(true, $message , $items, 200, 'items','');

    }

    /**
     * طلب تحقق نفاذ عبر Semati API (نفس منطق package saudi-nafath-integration).
     * POST مع id_number → يستدعي semati.sa ويرجع transId و random لعرضه على المستخدم.
     */
    public function nafathRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_number' => 'required|string|min:10|max:20',
        ], [
            'id_number.required' => __('رقم الهوية مطلوب'),
            'id_number.min' => __('رقم الهوية 10 أرقام على الأقل'),
        ]);
        if ($validator->fails()) {
            return mainResponse(false, $validator->errors()->first(), null, 422, 'items', $validator);
        }

        $apiKey = config('services.nafath.api_key');
        $callbackUrl = config('services.nafath.callback_url');
        $authorizeUrl = config('services.nafath.authorize_url');
        if (!empty($authorizeUrl)) {
            $authorizeUrl = rtrim($authorizeUrl, '/') . '/';
        } else {
            $urlBase = preg_replace('#^https?://#i', '', trim(config('services.nafath.url_base', 'test')));
            // إذا كان url_base يحتوي المسار (مثل .../nafath/.../authorize) استخدمه كما هو بدون إلحاق المسار مرة أخرى
            if (str_contains($urlBase, 'nafath') && str_contains($urlBase, 'authorize')) {
                $authorizeUrl = 'https://' . trim($urlBase, '/') . '/';
            } else {
                $authorizeUrl = 'https://' . trim($urlBase, '/') . '/nafath/api/v1/client/authorize/';
            }
        }

        if (empty($apiKey) || empty($callbackUrl)) {
            $msg = 'إعداد نفاذ غير مكتمل. ضع في .env: NAFATH_API_KEY و NAFATH_CALLBACK_URL (مثال: https://www.brandy.sa/nafath/post-callback)';
            return mainResponse(false, $msg, null, 400, 'items', '');
        }

        $appId = config('services.nafath.app_id', '');
        $headers = [
            'Authorization' => 'apikey ' . $apiKey,
            'Content-Type' => 'application/json',
        ];
        if (!empty($appId)) {
            $headers['X-App-Id'] = $appId;
            $headers['App-Id'] = $appId;
        }

        $body = [
            'id' => $request->id_number,
            'action' => 'SpRequest',
            'service' => 'Login',
            'callbackUrl' => $callbackUrl,
        ];
        if (!empty($appId)) {
            $body['appId'] = $appId;
        }

        try {
            $response = Http::withHeaders($headers)->post($authorizeUrl, $body);

            $statusCode = $response->status();
            $body = $response->json();

            if ($statusCode !== 200) {
                Log::error('Nafath API Error', ['response' => $body]);
                return mainResponse(false, $body['error'] ?? 'Nafath request failed', null, $statusCode ?: 500, 'items', '');
            }

            $transId = $body['transId'] ?? null;
            $random = $body['random'] ?? null;
            if (!$transId || $random === null) {
                return mainResponse(false, 'Invalid response from Nafath', null, 500, 'items', '');
            }

            Cache::put('nafath_trans_' . $transId, [
                'user_id' => auth()->id(),
                'id_number' => $request->id_number,
                'random' => $random,
                'status' => 'pending',
            ], 600);

            return mainResponse(true, 'Nafath request sent', [
                'trans_id' => $transId,
                'random' => $random,
            ], 200, 'items', '');
        } catch (\Exception $e) {
            Log::error('Nafath Exception', ['message' => $e->getMessage()]);
            return mainResponse(false, 'Nafath error: ' . $e->getMessage(), null, 500, 'items', '');
        }
    }

    /**
     * فحص حالة طلب نفاذ (لـ polling من الواجهة).
     * GET auth/nafath/check/{transId}
     */
    public function nafathCheck(Request $request, $transId)
    {
        $key = 'nafath_trans_' . $transId;
        $data = Cache::get($key);
        if (!$data) {
            return mainResponse(false, 'Transaction not found or expired', null, 404, 'items', '');
        }
        if ($data['user_id'] != auth()->id()) {
            return mainResponse(false, 'Unauthorized', null, 403, 'items', '');
        }
        return mainResponse(true, 'OK', [
            'status' => $data['status'],
            'random' => $data['random'] ?? null,
        ], 200, 'items', '');
    }

    /**
     * إرجاع حالة توثيق نفاذ للمستخدم الحالي (للاستدعاء من Vue بعد العودة أو عند فتح الصفحة).
     * GET /user/auth/nafath/status
     * Response: { code: 200, status: true, items: { nafath_verified: bool, national_id: string|null } }
     */
    public function nafathStatus(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return mainResponse(false, 'Unauthenticated', null, 401, 'items', '');
        }
        return mainResponse(true, 'OK', [
            'nafath_verified' => (bool) ($user->nafath_verified ?? false),
            'national_id' => $user->national_id ?? null,
        ], 200, 'items', '');
    }

    /**
     * نفاذ (Semati) يستدعي هذا الرابط بـ POST ويرسل JWT في response.
     * فك JWT ← transId, status ← تحديث المستخدم وحالة الـ cache للـ polling.
     */
    public function nafathPostCallback(Request $request)
    {
        try {
            $jwt = $request->input('response');
            if (!$jwt) {
                Log::warning('Nafath callback: missing response');
                return response('Bad Request', 400);
            }
            $payload = $this->nafathDecodeJwt($jwt);
            if (!$payload) {
                return response('Invalid token', 400);
            }
            $transId = $payload->transId ?? null;
            $status = $payload->status ?? null;
            $nationalId = $payload->nationalId ?? $payload->national_id ?? null;

            if (!$transId) {
                return response('Invalid token', 400);
            }

            $key = 'nafath_trans_' . $transId;
            $data = Cache::get($key);
            if (!$data) {
                Log::warning('Nafath callback: no record for transId', ['transId' => $transId]);
                return response('No login found', 404);
            }

            $newStatus = ($status === 'COMPLETED' || $status === 'completed') ? 'completed' : 'rejected';
            Cache::put($key, array_merge($data, ['status' => $newStatus]), 600);

            $user = User::find($data['user_id']);
            if ($user) {
                $user->nafath_verified = ($newStatus === 'completed');
                if ($nationalId) {
                    $user->national_id = $nationalId;
                } elseif (!empty($data['id_number'])) {
                    $user->national_id = $data['id_number'];
                }
                $user->save();
            }

            return response('TRS', 200);
        } catch (\Exception $e) {
            Log::error('Nafath callback exception', ['message' => $e->getMessage()]);
            return response('TRS', 200);
        }
    }

    private function nafathDecodeJwt($token)
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) < 2) return null;
            $payload = $parts[1];
            $payload = strtr($payload, '-_', '+/');
            return json_decode(base64_decode($payload));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Nafath redirects the user here after authentication (لو استخدموا redirect بدل POST).
     * Return HTML that notifies opener and closes popup.
     */
    public function nafathCallbackPage(Request $request)
    {
        $params = $request->query();
        $locale = app()->getLocale();
        $returnUrl = rtrim(config('app.url'), '/') . '/' . $locale . '/add-ad';
        return response()->view('nafath.callback', [
            'params' => $params,
            'status' => $request->input('status', 'success'),
            'national_id' => $request->input('national_id', $request->input('nationalId', '')),
            'return_url' => $returnUrl,
        ], 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Save Nafath result for the authenticated user (national_id, verified).
     */
    public function nafathSave(Request $request)
    {
        $request->validate([
            'national_id' => 'required|string|max:20',
            'verified' => 'boolean',
        ]);
        $user = $request->user();
        if (!$user) {
            return mainResponse(false, 'Unauthenticated', null, 401, 'items', '');
        }
        $user->national_id = $request->national_id;
        $user->nafath_verified = (bool) $request->get('verified', true);
        $user->save();
        return mainResponse(true, 'تم حفظ بيانات التحقق', $user->only(['id', 'national_id', 'nafath_verified']), 200, 'items', '');
    }

    //  public function login_facebook(Request $request){

    //       $token = $request->input('access_token');


    //     $response = Http::withoutVerifying()->get("https://graph.facebook.com/me?fields=id,name,email&access_token={$token}");

    //         if ($response->failed()) {
    //             return mainResponse(false, 'Invalid login' , null, 203 , 'items','');
    //         }

    //         $fbUser = $response->json();


    //         $user = User::firstOrCreate(
    //             ['facebook_id' => $fbUser['id']],
    //             [
    //                 'name' => $fbUser['name'],
    //                 'email' => $fbUser['email'] ?? "{$fbUser['id']}@facebook.com",
    //                 'password' => bcrypt(str()->random(16)),
    //             ]
    //         );

    //         $user->token = $user->createToken('facebook-login')->plainTextToken;


    //         $message = __('auth.login_success'); 

    //     return mainResponse(true, $message, $user, 200, 'items', '');


    // }




    // public function login_google(Request $request){


    //      $idToken = $request->input('id_token');

    //         $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
    //             'id_token' => $idToken,
    //         ]);

    //         if ($response->failed()) {
    //             return mainResponse(false, 'Invalid login' , null, 203 , 'items','');
    //         }

    //         $googleUser = $response->json();

    //         $user = \App\Models\User::firstOrCreate(
    //             ['google_id' => $googleUser['sub']],
    //             [
    //                 'name' => $googleUser['name'] ?? 'Unknown',
    //                 'email' => $googleUser['email'],
    //                 'password' => bcrypt(str()->random(16)),
    //             ]
    //         );

    //         $user->token = $user->createToken('google-login')->plainTextToken;


    //     $message = __('auth.login_success'); 
    //     return mainResponse(true, $message, $user, 200, 'items', '');


    // }






}

  
   


    







   
