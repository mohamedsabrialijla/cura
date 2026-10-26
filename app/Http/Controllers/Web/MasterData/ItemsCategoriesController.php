<?php

namespace App\Http\Controllers\Web\MasterData;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Language;
use App\Models\ItemsCategories;
use App\Models\ItemsTypes;



class ItemsCategoriesController extends Controller
{
  
     public function __construct() {
        $this->middleware('auth', ['except' => ['login', 'register']]);

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
        // dd($request->all());
      
        $id = auth('admin')->id();

        $validator = Validator::make($request->all(), [
           
            'type' => 'required',
            'order' => 'required',
        ]);


        if($request->type == 'country'){
 
            $validator = Validator::make($request->all(), [
                'code' => 'required',
            ]);

        }


        if($request->type == 'city'){
 
            $validator = Validator::make($request->all(), [
                'country' => 'required',
            ]);

        }



        if($request->type == 'brand'){
 
            $validator = Validator::make($request->all(), [
                'category' => 'required',
            ]);

        }


        if($request->type == 'category'){
 
            $validator = Validator::make($request->all(), [
                'icon' => 'required',
            ]);

        }






        foreach ($this->locales as $locale) {
            $roles['name_' . $locale->lang] = 'required';
            // $roles['description_' . $locale->lang] = 'required';
        }


        if ($validator->fails()) {
            return mainResponse(false, '' , null, 203, 'items',$validator);
        }else{

 
                
                $item = New ItemsCategories;
                $item->type_id = ItemsTypes::where('name',$request->type)->pluck('id')->first();
                $item->category = $request->category;
                $item->country = $request->country;
                $item->auto = $request->auto;
                $item->code = $request->code;
                $item->icon = $request->icon;
                $item->slug = strtoupper(substr($request->get('name_en'), 0, 2));
                $item->order = $request->order;
                $item->created_by = $id;
                
             
                foreach ($this->locales as $locale) {
                    $item->translateOrNew($locale->lang)->name = $request->get('name_' . $locale->lang);
                    // $item->translateOrNew($locale->lang)->description = $request->get('description_' . $locale->lang);
                }


                   if($request->hasFile('logo') && $request->file("logo")!='')
                {
                    if ($request->file("logo")->isValid())
                    {
                        $destinationPath=public_path('uploads/country');

                        $extension=strtolower($request->file("logo")->getClientOriginalExtension());
                        $array= $this->image_extensions();
                        if(in_array($extension,$array))
                        {
                            $fileName=uniqid().'.'.$extension;
                            $request->file("logo")->move($destinationPath, $fileName);
                        }else{
                            return response()->json(['message'=> 'file:jpg,png,jpeg,gif,bmp,pdf', 'code'=>201 , 'status'=>false]);
                        }
                    }
                }
                if(isset($fileName)){$item->logo='uploads/country/'.$fileName;}



                $item->save();

                $message ="Doing successfully";
                return mainResponse(true, $message , $item, 200, 'items','');

               

       
        }
    }



    public function edit(Request $request)
    {
      // dd($request->all());
        $id = auth('admin')->id();
        $item = ItemsCategories::query()->findOrFail($request->Item_id);


        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'order' => 'required',
        ]);

        foreach ($this->locales as $locale) {
            $roles['name_' . $locale->lang] = 'required';
            // $roles['description_' . $locale->lang] = 'required';
        }


        if($request->type == 'country'){
 
            $validator = Validator::make($request->all(), [
                'code' => 'required',
            ]);

        }


        if($request->type == 'brand'){
 
            $validator = Validator::make($request->all(), [
                'category' => 'required',
            ]);

        }


        if($request->type == 'city'){
 
            $validator = Validator::make($request->all(), [
                'country' => 'required',
            ]);

        }

        if($request->type == 'category'){
 
            $validator = Validator::make($request->all(), [
                'icon' => 'required',
            ]);

        }



       





        if ($validator->fails()) {
            return mainResponse(false, '' , null, 203, 'items',$validator);
        }else{

 
                
                $item = ItemsCategories::findOrFail($request->Item_id);
                $item->type_id = ItemsTypes::where('name',$request->type)->pluck('id')->first();
                $item->category = $request->category;
                $item->created_by = $id;
                $item->country = $request->country;
                $item->auto = $request->auto;
                $item->code = $request->code;
                $item->icon = $request->icon;
                $item->slug = strtoupper(substr($request->get('name_en'), 0, 2));;
                $item->order = $request->order;



             
                foreach ($this->locales as $locale) {
                    $item->translateOrNew($locale->lang)->name = $request->get('name_' . $locale->lang);
                    // $item->translateOrNew($locale->lang)->description = $request->get('description_' . $locale->lang);
                }


                 if($request->hasFile('logo') && $request->file("logo")!='')
                {
                    if ($request->file("logo")->isValid())
                    {
                        $destinationPath=public_path('uploads/country');

                        $extension=strtolower($request->file("logo")->getClientOriginalExtension());
                        $array= $this->image_extensions();
                        if(in_array($extension,$array))
                        {
                            $fileName=uniqid().'.'.$extension;
                            $request->file("logo")->move($destinationPath, $fileName);
                        }else{
                            return response()->json(['message'=> 'file:jpg,png,jpeg,gif,bmp,pdf', 'code'=>201 , 'status'=>false]);
                        }
                    }
                }
                if(isset($fileName)){$item->logo='uploads/country/'.$fileName;}





                $item->save();

                $message ="Doing successfully";
                return mainResponse(true, $message , $item, 200, 'items','');

               

       
        }
    }


    public function getAll(Request $request)
    {

         $id = auth('admin')->id();

            $items = ItemsCategories::with('type','country','category');


            if ($request->has('search') && !empty($request->search)) {
                $items->where(function ($query) use ($request) {
                    $query->whereTranslationLike('name', '%' . $request->search . '%')
                          ->orWhereHas('country', function ($q) use ($request) {
                              $q->whereTranslationLike('name', '%' . $request->search . '%');
                          });
                });
            }



            if($request->has('type') && !empty($request->type)) {
                $items->whereHas('type', function($query) use ($request) {
                    $query->where('name',$request->type);
                });
            }



            if ($request->has('parent_id') && !empty($request->parent_id)) {
                $items->where(function ($query) use ($request) {
                    $query->where('category',$request->parent_id );
                          
                });
            }


            if ($request->has('parent_id2') && !empty($request->parent_id2)) {
                $items->where(function ($query) use ($request) {
                    $query->where('country',$request->parent_id2 );
                          
                });
            }




            if(isset($request->pagination) && $request->pagination == 1) {
                $items = $items->orderBy('order','Asc')->paginate(10); 
            } else {
                $items = $items->orderBy('order','Asc')->get();
            }

            $message = "success return";

            return mainResponse(true, $message, $items, 200, 'items', '');
    }



    public function getById(Request $request)
    {
        $items = ItemsCategories::with('type','country','category')->where('id',$request->ID)->first();
        $message ="success return";
        return mainResponse(true, $message , $items, 200, 'items',''); 
    }

    public function delete($id)
    {
        if(ItemsCategories::where('id',$id)->delete()){
            $message ="success return";
            return mainResponse(true, $message , '', 200, 'items','');
        }else{
            $message ="Error happens";
            return mainResponse(false, $message , '', 203, 'items','');
        }
        
    }



 
    function deleteAllItem(Request $request){

        $selectedItems = explode(',', request()->query('selectedItems'));
        $action = $request->action ;
        

        try {

            if($action == 'delete'){
            ItemsCategories::whereIn('id',$selectedItems)->delete();
            }elseif ($action == 'active') {
                ItemsCategories::whereIn('id',$selectedItems)->update(['status'=>'active']);
            }else{
                ItemsCategories::whereIn('id',$selectedItems)->update(['status'=>'not_active']);
            }

            $message ="success return";
            return mainResponse(true, $message , '', 200, 'items','');

 
        } catch (Exception $e) {
            $message ="Error happens";
            return mainResponse(false, $message , '', 203, 'items','');
            
        }



    }

    

}