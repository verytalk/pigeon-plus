<?php
namespace app\index\controller;
use think\Db;

use think\facade\Session;

class Login extends Base {

    var $postData = ["username"=>"","password"=>"","seid" =>""];

    public function initialize(){
        parent::initialize();
        $this->pigeonTemplates = $this->getTemplates("login");
    }


    public function index(){
        return $this -> fetch($this->pigeonTemplates."index");
    }


    public function logout(){
        Session::start();
        Session::destroy();
        $this->redirect("index/index/index");
    }


    public function doLogin(){
        $postData = $this->postData;
        if(!isset($_POST["username"])){
            return $this->getMessageErrorType("用户名不能为空",$postData);
        }
        $postData['username'] = $_POST["username"];
        if(!isset($_POST["password"])){
            return $this->getMessageErrorType("密码不能为空",$postData);
        }
        $postData['password'] = $_POST["password"];
        if(!isset($_POST['seid']) || $_POST['seid'] !== Session::get('seid')) {
            return $this->getMessageErrorType("CSRF 验证失败，请尝试重新登录。",$postData);
        }
        $data = $this->checkUserExist($postData['username']);

        if($data['status'] !== '200'){
            switch($data['status']) {
                // todo
                case "401":
                    $error = "您需要先验证邮箱才能登陆，<a href='?s=resendmail&user={$postData['username']}'>点击重新发送邮件</a>。";
                    break;
                case "403":
                    $error = "您的账号已被封禁。";
                    break;
                default:
                    $error = "您的账号为异常状态，请联系管理员。";
            }
            return $this->getMessageErrorType($error,$postData);

        }else{
            if(password_verify($_POST['password'], $data['password'])) {
                $loginIp = $_SERVER['REMOTE_ADDR'];
                $loginInfo = array(
                    "latest_ip" =>$loginIp,
                    "latest_time" =>time(),
                );
                $this->saveLoginInfo($loginInfo,$data['id']);
                Session::set('user' , $data['username']);
                Session::set('email' , $data['email']);
                Session::set('token' , $data['token']);
                $this->redirect("index/index/index");
            } else {
                $error = "用户名或密码错误。";
                return $this->getMessageErrorType($error,$postData);
            }
        }

//        if($pigeon->config['recaptcha_key'] !== '') {
//            if(!isset($_POST['g-recaptcha-response']) || !$pigeon->recaptcha_verify($_POST['g-recaptcha-response'])) {
//                $error = "Recaptcha 验证失败。";
//            }
//        }

    }



    public function saveLoginInfo($data,$id){
        Db::table('users')->where(array("id"=>$id))->update($data);
    }


}
