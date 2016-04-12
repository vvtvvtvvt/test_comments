<?php
//модель описывает дерево комментариев
class M_Comments extends M_Tree
{
    private  $max_level;// максимально допустимый уровень сложенности комментариев

    //конструктор
    public function __construct($table_name, $max_level=-1, $id_name="id", $parent_name="id_parent",$level_name="level")
    {
        parent::__construct($table_name, $id_name="id", $parent_name="id_parent",$level_name="level");
        $this->max_level = $max_level;
    }

    //Возвращает массив состоящий из элементов дерева
    //fields - поля которые должны содержаться в результатах запроса, перечисленные через запятую в строке
    //order_by - поле по которому будет производится сортировка результатов.
    // part_of_sql_arter_from - строка с остальными SQL оператороми которые должны идти после FROM имя таблицы
    public function get_all($fields="user_name, content, publication_date", $order_by="publication_date DESC", $part_of_sql_arter_from=""){
        return parent::get_all($fields, $order_by, $part_of_sql_arter_from);
    }

    //Добавляет комментарий в таблицу
    //user - имя пользователя добавившего комментарий
    //content - текст комментария
    //id_parent - идентификатор родительского комментария
    // уровень сложенности коментария
    public function  add($user, $content, $id_parent, $level, &$last_inser_id, &$date){
        ++$level;
        $date = date('U');
        if($level<=$this->max_level)
            return parent::add($id_parent, array("content"=>$content, "user_name"=>$user, "level"=>$level,  "publication_date"=>$date), $last_inser_id);
        else
            return null;
    }

    //Удаляет комментарии вместе с дочерними
    //level - уровень вложенности удаляемого комментария
    //id - идентификатор удаляемого комментария
    public function delete_with_suns($level, $id="id"){
        return parent::delete_with_suns($id, $level);
    }

    //валидация параметров из формы
    //возвращает массив ошибок
    public function validate(&$name, &$text, &$level, &$id){
        $err_mass=array();
        $name = trim(preg_replace('/\s{2,}/', ' ', $name));
        $text = trim(preg_replace('/\s{2,}/', ' ', $text));

        if(is_null($name)||$name==""){
            array_push($err_mass, "Имя пользователя не может быть пустым");
        }

        if(is_null($text)||$text==""){
            array_push($err_mass, "Текст сообщения не может быть пустым");
        }
        if(!(is_numeric($level)&&is_numeric($id))){
            array_push($arr_mass, "Не удалось добавить комментарий. Обратитесь в службу потдержки.");
        }
        return $err_mass;
    }
}
?>