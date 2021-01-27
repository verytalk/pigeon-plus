<?php

namespace app\index\controller;

use \think\Controller;
use think\Db;
use \think\facade\Config;
use think\facade\Session;

require_once './extend/public/extend.php';


class Base extends Controller
{

    var $pigeonTemplates;

    public function initialize()
    {
        parent::initialize();

        if (!Session::has('seid')) {
            Session::set("seid", guid());
        }

        $this->assign("message", '');
        $this->assign("seid", $this->getSeid());
        $this->assign("user", $this->getLoginUsername());
        $this->assign("token", Session::get("token"));
        $this->assign("pigeonTemplates", $this->pigeonTemplates);
        $this->assign("siteConfig", Config::get("site."));
        $this->assign("isAdmin", $this->isAdmin());

    }


    public function getSeid()
    {
        return Session::get("seid");
    }

    public function getLoginUsername()
    {
        return Session::get("user");
    }

    /**
     *
     */
    public function isAdmin()
    {
        $username = Session::get(USER_SESSION);
        if ($username) {
            $data = Db::table('users')->where(array("username" => $username))->find();
            return $data ? ($data['permission'] == 'root' || $data['permission'] == 'admin') : false;
        }
        return false;
    }


    public function isAjax()
    {

        return isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1;

    }


    public function returnJsonData($data, $msg = "", $status = "200")
    {
        $returnData = array(
            "data" => $data,
            "status" => $status,
            "msg" => $msg
        );
        //return json($returnData);
        echo json_encode($returnData);
        exit;

    }


    public function getTemplates($className)
    {
        $this->pigeonTemplates = "pigeon";
        return $this->pigeonTemplates . "/" . $className . ":";
    }


    public function getMessageSuccessType($content, $postData = array())
    {
        $message = ["alert" => "success", "content" => $content];
        $this->assign("message", $message);
        $this->assign("postData", $postData);
        return $this->fetch($this->pigeonTemplates . "index");
    }

    public function getMessageErrorType($content, $postData)
    {
        $message = ["alert" => "danger", "content" => $content];
        $this->assign("message", $message);
        $this->assign("postData", $postData);
        return $this->fetch($this->pigeonTemplates . "index");
    }

    public function checkUserExist($username)
    {
        $data = Db::table('users')->where(array("username" => $username))->find();
        return $data;
    }

    public function checkTokenExist($token)
    {
        $data = Db::table('users')->where(array("token" => $token))->find();
        return $data;
    }

    public function checkEmailExist($email)
    {
        $data = Db::table('users')->where(array("email" => $email))->find();
        return $data;
    }


}