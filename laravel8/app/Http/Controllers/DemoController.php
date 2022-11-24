<?php

namespace App\Http\Controllers;
use App\Models\Demo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DemoController extends Controller
{
    
    public function demo(){

        return view('demo');
    }
    public function save(Request $request){
        $obj = new Demo();
        // echo $request->input('email');die;
        $obj->email = $request->input('email');
        $obj->pass =Hash::make($request->input('pswd'));
        $obj->description =$request->input('description');
        $obj->save();
        return redirect('list')->with('message', 'Saved successfully!');
    }
    public function list(){
        $datas = Demo::get();
        // echo "<pre>"; print_r($data);die;
        return view('list',['datas'=>$datas]);
    }
    public function checkapi(){
        return "working";die;
    }
}
