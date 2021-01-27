<?php

namespace app\index\controller;

use think\Db;
use app\index\controller\Base;
use \think\facade\Config;

require_once './extend/public/parsedown.php';

class Index extends Base{

    public function initialize(){
        parent::initialize();
        $this->pigeonTemplates = $this->getTemplates("index");

    }

    public function index(){
        $page = 1;
        $pageSize = 10;
        if (isset($_REQUEST['page']) && $_REQUEST['page'] != "") {
            $page = $_REQUEST['page'];
        }
        $start = ($page - 1) * $pageSize;
        $searchSQL = "status = 1";
        if (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) {
            $search = $_REQUEST['search'];
            $searchSQL .= " AND (POSITION('$search' IN `content`) OR POSITION('$search' IN `author`))";
        }

        if (input("isMe") == "1") {
            if ($this->getLoginUsername()) {
                $searchSQL .= " AND author = '" . $this->getLoginUsername() . "'";
            }
        }

        if (isset($_REQUEST['time']) && !empty($_REQUEST['time'])) {
            $time = strtotime($_REQUEST['time']);
            // $searchsql = " AND (POSITION('$search' IN `content`) OR POSITION('$search' IN `author`))";
            $searchSQL .= " AND time <= $time ";
        }

        if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
            $id = $_REQUEST['id'];
            // $searchsql = " AND (POSITION('$search' IN `content`) OR POSITION('$search' IN `author`))";
            $searchSQL .= " AND id >$id";
        } else if (isset($_REQUEST['current_id']) && !empty($_REQUEST['current_id'])) {
            $id = $_REQUEST['current_id'];
            $searchSQL .= " AND id <= $id";
        }


        if (empty($this->getLoginUsername())) {
            $searchSQL .= " AND `public`='0' ";
        } else {
            if ($this->isAdmin()) {
                $searchSQL .= " AND (`public`='0' OR `public`='1' OR `public`='2') ";
            } else {
                $searchSQL .= " AND (`public`='0' OR `public`='1' OR (`public`='2' AND `author`='{$this->getLoginUsername()}'))";
            }
        }


        $data = Db::table('posts')->where($searchSQL)->limit($start, $pageSize)->order("id DESC")->select();

        $Markdown = new \Parsedown();
        $Markdown->setBreaksEnabled(false);
        $Markdown->setSafeMode(true);


        $ids = "";
        foreach ($data as $key => $row) {
            $data[$key]["time"] = getDateFormat($row['time']);
            $data[$key]["content"] = $Markdown->text($row['content']);
            $ids .= $data[$key]["id"] . ",";
            if ($this->isAjax()) {
                $data[$key]["isSelf"] = $row["public"] == 2 ? true : false;
                $data[$key]["isAuthor"] = ($this->isAdmin() || $row['author'] == $this->getLoginUsername()) ? true : false;
            }
        }

        if ($this->isAjax()) {
            $this->returnJsonData($data);
            //return view($this->getTemplates("public")."content_page");
        } else {
            $this->assign("data", $data);
            $this->assign("isMe", input("isMe"));

            return $this->fetch($this->pigeonTemplates . "index");
        }

    }


    public function newPost(){

        $isPublic = "";
        if (isset($_REQUEST['ispublic']) && $_REQUEST['ispublic'] != "") {
            $isPublic = $_REQUEST['ispublic'];
        }

        $content = "";
        if (isset($_REQUEST['content']) && !empty($_REQUEST['content'])) {
            $content = $_REQUEST['content'];
        }


        if ($isPublic != '0' && $isPublic != '1' && $isPublic != '2') {
            return $this->returnJsonData("", "信息类型不存在 .", "400");
        }
        $apiUser = $this->checkApiUser();

        $this->chewckContent($content);


        if ($apiUser || !empty($this->getLoginUsername())) {
            $data = array(
                "content" => $content,
                "author" => $this->getLoginUsername(),
                "time" => time(),
                "public" => $isPublic

            );
            $status = Db::table('posts')->insert($data);
            return $this->returnJsonData("", "Successful", "200");
        }

        return $this->returnJsonData("", "Bad Request", "401");

    }


    public function checkApiUser(){

        $apiUser = false;
        if (!$this->getLoginUsername()) {
            if (isset($_REQUEST['token']) && preg_match("/^[A-Za-z0-9]{32}$/", $_REQUEST['token'])) {
                $token = $_REQUEST['token'];
                $rs = $this->checkTokenExist($token);
                if ($rs) {
                    Session::set('user', $rs['username']);
                    $apiUser = true;
                } else {
                    return $this->returnJsonData("", "Permission denied .", "401");
                }
            } else {
                return $this->returnJsonData("", "请先登录。", "401");
            }
        }

        if (!$apiUser) {
            $this->checkSeid();
        }

        return $apiUser;
    }


    public function chewckContent($content){
        $textlen = mb_strlen($content);
        if ($textlen < 1 || $textlen > 1000000) {
            return $this->returnJsonData("", "最少输入 1 个字符，最大输入 100 万个字符，当前已输入：{$textlen}。", "401");
        }
    }


    public function editpost(){

        if (isset($_REQUEST['id']) && preg_match("/^[0-9]{1,10}$/", $_REQUEST['id'])) {

            $id = $_REQUEST['id'];
            $isPublic = "";
            $apiUser = false;
            if (isset($_REQUEST['ispublic']) && $_REQUEST['ispublic'] != "") {
                $isPublic = $_REQUEST['ispublic'];
            }

            $content = "";
            if (isset($_REQUEST['content']) && !empty($_REQUEST['content'])) {
                $content = $_REQUEST['content'];
            }

            $apiUser = $this->checkApiUser();


            $this->checkPublicType($isPublic);

            $condition = array(
                "id" => $id
            );

            $data = Db::table('posts')->where($condition)->find();

            if ($data) {

                if ($data['author'] !== $this->getLoginUsername() && !$this->isAdmin()) {
                    return $this->returnJsonData("", "未找到指定的消息内容，该消息已被删除或者您暂时没有权限查看。", "401");
                }

                $this->chewckContent($content);

                if ($apiUser || !empty($this->getLoginUsername())) {
                    $data = array(
                        "content" => $content,
                        "author" => $this->getLoginUsername(),
                        "time" => time(),
                        "public" => $isPublic
                    );
                    $status = Db::table('posts')->where(array("id" => $id))->update($data);

                    if ($status) {
                        return $this->returnJsonData("", "保存成功", "200");
                    } else {
                        return $this->returnJsonData("", "保存失败", "401");
                    }
                }
            }
        }
        return $this->returnJsonData("", "未找到指定的消息内容。", "401");
    }


    public function checkPublicType($isPublic){
        if ($isPublic != '0' && $isPublic != '1' && $isPublic != '2') {
            $this->returnJsonData("", "信息类型不存在。", "401");
        }
    }


    public function deletepost(){

        if (isset($_REQUEST['id']) && preg_match("/^[0-9]{1,10}$/", $_REQUEST['id'])) {

            $id = $_REQUEST['id'];
            $apiUser = false;

            if (!$this->getLoginUsername()) {
                if (isset($_REQUEST['token']) && preg_match("/^[A-Za-z0-9]{32}$/", $_REQUEST['token'])) {
                    $token = $_REQUEST['token'];
                    $rs = $this->checkTokenExist($token);
                    if ($rs) {
                        Session::set('user', $rs['username']);
                        $apiUser = true;
                    } else {
                        return $this->returnJsonData("", "Permission denied", "401");
                    }
                } else {
                    return $this->returnJsonData("", "请先登录。", "401");
                }
            }


            $condition = array(
                "id" => $id
            );

            $data = Db::table('posts')->where($condition)->find();


            if ($data) {
                if ($data['author'] !== $this->getLoginUsername() && !$this->isAdmin()) {
                    return $this->returnJsonData("", "未找到指定的消息内容，该消息已被删除或者您暂时没有权限查看。", "401");
                }


                if ($apiUser || !empty($this->getLoginUsername())) {
                    $status = Db::table('posts')->where(array("id" => $id))->delete($data);
                    if ($status) {
                        return $this->returnJsonData("", "删除成功", "200");
                    } else {
                        return $this->returnJsonData("", "删除失败", "401");
                    }
                }
            }
        }

        return $this->returnJsonData("", "未找到指定的消息内容。", "401");
    }

    public function changepublic(){

        if (isset($_REQUEST['id']) && preg_match("/^[0-9]{1,10}$/", $_REQUEST['id'])) {

            $id = $_REQUEST['id'];
            $isPublic = "";
            $apiUser = false;
            if (isset($_REQUEST['ispublic']) && $_REQUEST['ispublic'] != "") {
                $isPublic = $_REQUEST['ispublic'];
            }

            if (!$this->getLoginUsername()) {
                if (isset($_REQUEST['token']) && preg_match("/^[A-Za-z0-9]{32}$/", $_REQUEST['token'])) {
                    $token = $_REQUEST['token'];
                    $rs = $this->checkTokenExist($token);
                    if ($rs) {
                        Session::set('user', $rs['username']);
                        $apiUser = true;
                    } else {
                        return $this->returnJsonData("", "Permission denied", "401");
                    }
                } else {
                    return $this->returnJsonData("", "请先登录。", "401");
                }
            }


            if ($isPublic != '0' && $isPublic != '1' && $isPublic != '2') {
                return $this->returnJsonData("", "信息类型不存在。", "401");
            }


            $condition = array(
                "id" => $id
            );

            $data = Db::table('posts')->where($condition)->find();

            if ($data) {
                if ($data['author'] !== $this->getLoginUsername() && !$this->isAdmin()) {
                    return $this->returnJsonData("", "未找到指定的消息内容，该消息已被删除或者您暂时没有权限查看。", "401");
                }

                if ($apiUser || !empty($this->getLoginUsername())) {
                    $data = array(
                        "author" => $this->getLoginUsername(),
                        "time" => time(),
                        "public" => $isPublic
                    );
                    $status = Db::table('posts')->where(array("id" => $id))->update($data);

                    if ($status) {
                        return $this->returnJsonData("", "消息状态修改成功", "200");
                    } else {
                        return $this->returnJsonData("", "消息状态修改失败", "401");
                    }
                }
            }
        }

        return $this->returnJsonData("", "未找到指定的消息内容。", "401");


    }


    public function getMsg(){

        $responseData = array();
        if (isset($_REQUEST['id']) && $_REQUEST['id'] != "") {
            $id = $_REQUEST['id'];
        } else {
            $responseData["status"] = 0;
            $responseData["msg"] = "Bad request";
            echo json_encode($responseData, true);
            exit;
        }
        $condition = array(
            "id" => $id
        );
        $data = Db::table('posts')->where($condition)->find();
        if ($data) {
            return $this->returnJsonData($data,  "Successful", "200");
        }
        return $this->returnJsonData($data,  "消息不存在.", "401");
    }


    public function checkSeid(){
        if ((input('seid') == "" || input('seid') != $this->getSeid()) && (!isset($_REQUEST['seid']) || $_REQUEST['seid'] != $this->getSeid())) {
            $this->returnJsonData("", "CSRF 验证失败，请尝试重新登录。", "401");
        }
    }

}
