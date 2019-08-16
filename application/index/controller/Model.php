<?php

namespace app\index\controller;
use lib\Mails\Mail;
use lib\WxPay\WxPay;
use Qcloud\SendSmS;
use think\Db;
use app\index\controller\Base;
use think\Session;
use think\Request;
class Model extends Base {

    /**
     * 登录 模型
     */
    public function Login(){
        $username = input("username",'');
        $password = input("password",'');
        $code = input("code",0);//验证码
        //此处选择验证码处理逻辑
        $user_info = Db::table("创建的表名")->where("username",$username)
            ->where("password",$password)
            ->field("此处填写需要返回的信息")->find();
        if($user_info==""){
            return $this->ToJson('','用户名或密码错误',5);
        }
        Session::set($user_info['user_id'],$user_info);
        return $this->ToJson($user_info,'登录成功',1);
    }

    /**
     * 注册 模型
     */
    public function Register(){
        $username = input("username",'');
        $password = input("password",'');
        //此处为获取填写的相关信息 或者相关逻辑代码
        $list = [
            'username'=>$username,
            'password'=>$password
            // ...
        ];
        $res = Db::table("插入到的数据表名")->insert($list);
        if ($res){
            return $this->ToJson('','注册成功',1);
        }else{
            return $this->ToJson('','注册失败',5);
        }
    }

    /**
     * 退出 模型
     */
    public function Logout(){
        $id =  input('id',0);
        Session::delete($id);
    }

    /**
     * 更新用户信息 模型
     */
    public function UpdateInfo(){
        $request = Request::instance();
        $post = $request->post();//获取post的内容
        $id = input("id",0);
        //此处编写逻辑代码
        $list = [
            'username'=>$post['username']
            //其他字段
        ];

        $res = Db::table("更新数据的表名")->where('id',$id)->update($list);

        if($res){
            return $this->ToJson('','更新成功',1);
        }else{
            return $this->ToJson('','更新失败',5);
        }
    }

    /**
     * 获取列表页  分页 模型
     */
    public function GetList(){
        $row = input('row',10);
        $page = input('page',1);
        //此处接收其他参数和逻辑判断

        $list = Db::table("需要查询的表名")->where("查询条件")->paginate($row);

        if ($list){
            return $this->ToJson($list,'',1);
        }
        else{
            return $this->ToJson('','没有数据',5);
        }
    }

    /**
     * 获取详细信息 原型
     */
    public function GetDetail(){
        $id = input('id',0);
        //此处接收其他字段 和处理逻辑判断

        $res = Db::table("需要查询的表名")->where("查询条件")->find($id);

        if ($res){
            return $this->ToJson($res,'',1);
        } else{
            return $this->ToJson('','没有数据',5);
        }
    }
    /**
     * 图片上传   传统post表单上传原型
     */

    public function UploadImg(){
        $file = request()->file('image');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
           $img_url = $info->getSaveName();
           return $this->ToJson($img_url,'',1);
        }else{
            // 上传失败获取错误信息
            $err =  $file->getError();
            return $this->ToJson('',$err,5);
        }
    }

    /**
     * base64 方法上传图片  原型
     */
    public function Base64Img(){

        $base64_image_content = input('img',''); //base64 图片内容
        $path = 'uploads';
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
            $type = $result[2];
            $new_file = $path."/".date('Ymd',time())."/";
            if(!file_exists($new_file)){
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file,0700);
            }
            $new_file = $new_file.time().".{$type}";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
                return $this->ToJson(['url'=>$new_file],'',1);
            }else{
                return $this->ToJson('','上传失败',5);
            }
        }else{
            return $this->ToJson('','上传失败',5);
        }
    }

    /**
     * 生成二维码
     */
    public function Qrcode(){
        require(EXTEND_PATH."lib/QRcode/Qrcodes.class.php");
        $qr = new \QRcodes();
        $img = $qr->create_Code('二维码内容',EXTEND_PATH."lib/QRcode/logo.png");

        return $this->ToJson(['url'=>$img],'',1);
    }

    /**
     * 发送短信验证码
     */

    public function SendSmS(){
        $send = new SendSmS();
        $phone = "15347974139";
        $params = [522522,10];
        Session::set($phone,$params);//建议开启Redis
        $result = $send->Send($phone,$params);
        $res = json_decode($result,true);
        if ($res['result']==0){
            return $this->ToJson('','发送成功',1);
        }
        return $this->ToJson('','发送失败',5);
    }

    /**
     * 发送邮件 模型
     */
    public function SendMail(){
        require(EXTEND_PATH."lib/Mail/Mail.class.php");
        $mail = new \Mail();
//        $mail = new Mail();
       $res = $mail->Send("2294900768@qq.com","测试邮件","hahahhahaha,这是我的测试邮件");
       if ($res){
           return $this->ToJson('','发送成功',1);
       }
       else{
           return $this->ToJson('','发送失败',5);
       }
    }

    /**
     * 微信登录获取code
     */

    private $appid="wx006e93b1dfa94fe2";
    private $secret= "b9c6db96811c574120c2fd4917fee9f2";

//    private $appid = "wx01f4700faf414773";
//    private $secret = "e40981e738c4e035f84bb43c1cd69eaa";

    public function getCode(){
        $code = input("code", "");//微信code
        $hot = "https://api.weixin.qq.com/sns/jscode2session?appid=";
        $hot .= $this->appid . "&secret=";
        $hot .= $this->secret . "&js_code=";
        $hot .= $code . "&grant_type=authorization_code";
        $wr = $this->LocalCurl($hot);
        dump($wr);
//        return $this->ToJson('', '微信授权失败', 5);
        /**
         * array(2) {
                ["session_key"] => string(24) "vXJZuDlsqZhEMUfsvPhTBA=="
                ["openid"] => string(28) "o6NMq5NbzC-ARdct4ahPHgMarkCs"
            }
         */


    }
    public function LocalCurl($host)
    {
        $ch = curl_init();
        // 2. 设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $host);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // 3. 执行并获取HTML文档内容
        $output = curl_exec($ch);

        if ($output) {
            curl_close($ch);
            //echo $output;
            return json_decode($output, true);
        } else {
            $error = curl_errno($ch);
            //echo "$ret".$ret;die();
            echo $error;
            curl_close($ch);
        }

    }

    /**
     * 获取微信端getAccessToken
     */
    public function GetAccessToken(){
        $hot = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential";
        $hot .= "&appid={$this->appid}&secret={$this->secret}";
        $wr = $this->LocalCurl($hot);
        dump($wr);
        /**
         * array(2) {
                ["access_token"] => string(157) "24_pKy4_Lrc-Zpyvu8H-Jt9ZVoDIybrHbj7r38lMDq5EPA_XpqMDAndWmF6ZPHyNb7duD4zuFHw_Nthsuxa8KTByTtgGVzrrK-BKyFT_TIA_0iJs6VM2jTsWrbmp6g4P1cAuX-BoWaCzOHrL4U5NMNaABAFEF"
                ["expires_in"] => int(7200)
         * }
         */
    }


    /**
     * 微信支付 操作
     */
    public function Wxpay(){
        $pay = new WxPay();
        $pay->UnifiedOrder();//统一下单地址
    }

    /**
     * 微信退款操作
     */
    public function Refund(){
        $pay = new \lib\RefundPay\WxPay();
        $list = [
            'out_trade_no'=>"992019031517462900025853",
            "out_refund_no"=>"992019031517",
            "total_fee"=>1,
            "refund_fee"=>1
        ];
        $pay->Refund($list);
    }
}