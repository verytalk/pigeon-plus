<?php
namespace app\index\controller;
use think\Db;
use think\facade\Config;


//require_once __ROOT__."/vendor/util/smtp.php";
//use vendor\util\smtp;



class Register extends Base {

    var $postData = ["username"=>"","email"=> "","password"=>""];

    public function initialize(){
        parent::initialize();

        $this->assign("postData",$this->postData);
        $this->pigeonTemplates = $this->getTemplates("register");

        if(Config::get("site.enable_registe")){
            $this->assign("enable_registe",true);
        }else{
            $this->assign("enable_registe",false);
            $this->getMessageErrorType("抱歉，本站暂不开放注册。",$this->postData);
        }
    }

    public function index(){
        return $this -> fetch($this->pigeonTemplates."index");
    }


//    public function test(){
//        $smtp = new smtp(Config::get("site.smtp.host"),Config::get("site.smtp.port"), true, Config::get("site.smtp.user"), Config::get("site.smtp.pass"));
//        $smtp->debug = false;
//        $to = "";
//        $mailsubject = "";
//        $mailbody = "";
//        $mailtype = "";
//        $smtp->sendmail($to, Config::get("site.smtp.name"), $mailsubject, $mailbody, $mailtype);
//
//    }

    public function doRegister(){

        $postData = $this->postData;

        if(!isset($_POST["username"])){
            return $this->getMessageErrorType("用户名不能为空",$postData);
        }
        $postData['username'] = $_POST["username"];

        if(!isset($_POST["email"])){
            return $this->getMessageErrorType("Email不能为空",$postData);
        }
        $postData['email'] = $_POST["email"];
        if(!isset($_POST["password"])){
            return $this->getMessageErrorType("密码不能为空",$postData);
        }
        $postData['password'] = $_POST["password"];

        if(!preg_match("/^[A-Za-z0-9\_\-]+$/", $_POST['username'])) {
            return $this->getMessageErrorType("用户名格式错误",$postData);
        }
        if(!preg_match("/^[a-zA-Z0-9]+([-_.][a-zA-Z0-9]+)*@([a-zA-Z0-9]+[-.])+([a-z]{2,5})$/ims", $_POST['email'])) {
            return $this->getMessageErrorType("Email格式错误",$postData);
        }

        if($this->checkUserExist($postData['username'])){
            return $this->getMessageErrorType("用户名已经存在",$postData);
        }

        if($this->checkEmailExist($postData['email'])){
            return $this->getMessageErrorType("email已经存在",$postData);
        }

        return $this->addUser($postData,$postData);

    }




    public function addUser($data,$postData){
        $token = md5(sha1($data['username'] . $data['password'] . $data['email'] . mt_rand(0, 99999999) . time()));
        $password = password_hash($data['password'], PASSWORD_BCRYPT);
        // todo
        $registerIp = $_SERVER['REMOTE_ADDR'];

        // todo
//        if(Config::get("site.smtp.enable") ) {
//            $ust = '401';
//            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
//            $siteurl = "{$http_type}{$_SERVER['HTTP_HOST']}/?s=checkmail&token={$token}";
//            $pigeon->sendMail($email, "验证您的 {$pigeon->config['sitename']} 账号", "<p>您好，感谢您注册 {$pigeon->config['sitename']}。</p><p>请点击以下链接验证您的账号：</p><p><a href='{$siteurl}'>{$siteurl}</a></p><p>如果以上链接无法点击，请复制到浏览器地址栏中打开。</p><p>如果您没有注册本站账号，请忽略此邮件。</p>");
//            $needVerify = "系统已发送一封邮件到您的邮箱，请点击邮件中的链接完成验证。";
//        }

        $ust = '200';
        $data['password'] = $password;
        $data['token'] = $token;
        $data['registe_time'] = time();
        $data['registe_ip'] = $registerIp;
        $data['status'] = $ust;
        $data['permission'] = "user";

        $status = Db::table('users')->insert($data);

        if($status){
            return $this->getMessageSuccessType("注册成功",$postData);
        }

        return $this->getMessageErrorType("注册失败",$postData);

    }









}
