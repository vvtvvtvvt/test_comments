<?php class C_Front extends C_Base
{
    protected $_controller, $_action, $_params, $_body, $uri, $_controllerName;
    private static $instance;

    public static function Instance()
    {
        if(!(self::$instance instanceof self)) {
            self::$instance = new C_Front();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->uri = $request = $_SERVER['REQUEST_URI'];
        $temp = explode('?',$request);
        $request = $temp[0];
        $splits = explode('/', trim($request, '/'));
        if($splits[0]=="admin")
            array_shift($splits);
        //Если в URL контроллер не указан то используюется index
        $this->_controller = !empty($splits[0]) ? $splits[0] : 'index';
        $this->_controllerName = $this->_controller;
        //Если в URL мектод не указан то используюется index
        $this->_action = !empty($splits[1]) ? 'action_'.$splits[1] : 'action_index';
        //Получение параметров
        if (!empty($splits[2])) {
            $keys = $values = array();
            for ($i = 2, $cnt = count($splits); $i < $cnt; $i++) {
                if ($i % 2 == 0) {
                    //Чётное = ключ (параметр)
                    $keys[] = $splits[$i];
                } else {
                    //Значение параметра;
                    $values[] = $splits[$i];
                }
            }
            $this->_params = array_combine($keys, $values);
        }
    }

    public function  route()
    {
        switch ($this->_controller) {
            case 'comments':
                $this->_controller = new C_Comments();
                break;
            default: {
                $this->_controller = new C_Comments();
            }
        }
        $this->_controller->Request($this->_action, $this->_params);
    }

    public function getParams() {
        return $this->_params;
    }
    public function getController() {
        return $this->_controller;
    }
    public function getAction() {
        return $this->_action;
    }
    public function getBody() {
        return $this->_body;
    }
    public function printBody() {
        echo $this->_body;
    }
    public function setBody($body) {
        $this->_body = $body;
    }
    public function getUri(){
        return uri;
    }
    public function getControllerName(){
        return $this->_controllerName;
    }
}
?>