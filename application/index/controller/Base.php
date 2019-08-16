<?php

namespace app\index\controller;

class Base{

   public function ToJson($data,$msg,$code){
       $data = ['data'=>$data,'msg'=>$msg,'code'=>$code];
       return json($data);
   }

}