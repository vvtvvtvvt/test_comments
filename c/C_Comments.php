<?php

class C_Comments extends C_Base
{
    private $mPages; // модель работы с данными из которой будем получать данные


    public function __construct(){
        parent::__construct();
        $this->mPages = new M_Comments("Comments",5);
    }

    //перед отображением страницы
    public function before(){
        $this->needLogin = false;
        parent::before();
    }

    //Выводит список
    public function action_index(){
        $this->title="Комментарии";
        $cooments = $this->mPages->get_all();
        $this->content = $this->Template('v/v_comments_page.php', array('comments'=>$this->Template('v/v_comments.php', array('comments' => $cooments, 'controller'=>$this->front_c->getControllerName()))));
    }
    //Выводит список
    public function action_add(){
        $this->title="Комментарии::Добавление";
        $parent = isset($this->params["parent"]) ? (int)$this->params["parent"] : 0;
        $level = isset($this->params["level"]) ? (int)$this->params["level"] : 0;
        $ajax = isset($this->params["ajax"]) ? (int)$this->params["ajax"] : 0;
        $last_inser_id=0;
        $date=0;
        $err_mass =  array();
        if($ajax==0) {
            if ($this->isPost()) {
                $err_mass = $this->mPages->validate($_POST['name'], $_POST['text'], $level, $parent);
                if (count($err_mass) == 0) {
                    if ($this->mPages->add($_POST['name'], $_POST['text'], $parent, $level, $last_inser_id, $date)) {
                        $this->redirect('/comments/');
                    } else {
                        array_push($err_mass, "Максимальная глубина комменатриев равна 5");
                    }
                }
            }
            $comments = $this->mPages->get_all();
            $this->content = $this->Template('v/v_comments_page.php', array('comments' => $this->Template('v/v_comments_add.php', array('comments' => $comments, 'level' => $level, 'parent' => $parent, 'err' => $err_mass, 'controller' => $this->front_c->getControllerName()))));
        }
        else{
            $err_mass = $this->mPages->validate($_POST['name'], $_POST['text'], $level, $parent);
            if (count($err_mass) == 0) {
                if ($this->mPages->add($_POST['name'], $_POST['text'], $parent, $level, $last_inser_id, $date)) {
                    $otvet = array("answer"=>"yes","date"=>date('m.d.y H:i', $date),"last_id"=>$last_inser_id);
                    $this->front_c->setBody(json_encode($otvet));
                }
                else {
                    $otvet = array("answer"=>"no","err"=>$err_mass);
                    $this->front_c->setBody(json_encode($otvet));
                }
            }
            else {
                $otvet = array("answer" => "no", "err" =>$err_mass);
                $this->front_c->setBody(json_encode($otvet));
            }
        }
    }
    //Выводит список
    public function action_del(){
        $this->title="Комментарии::Удаление";

        $id = isset($this->params["id"]) ? (int)$this->params["id"] : 0;
        $level = isset($this->params["level"]) ? (int)$this->params["level"] : 0;
        $this->mPages->delete_with_suns($level, $id);
        $ajax = isset($this->params["ajax"]) ? (int)$this->params["ajax"] : 0;
        if($ajax==0) {
            $this->redirect('/comments/');
        }
        else{
            $this->front_c->setBody("yes");
        }
    }

}
?>
