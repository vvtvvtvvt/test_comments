<?php
// модель для работы с деревьями
class M_Tree
{
    private $db; //объект для работы базой данных
    private $parent_name; //имя поля в таблицы в котором хранится id родителя
    private $level_name; //имя поля в таблице в котором хранится уровень сложенности элемента дерева
    private $table_name; // имя таблицы в которой хранится дерево
    private $id_name;// имя поля в таблицы в котором хранится уникальный идентификатор элемента

    //конструктор
    public function __construct($table_name, $id_name="id", $parent_name="id_parent",$level_name="level")
    {
        $this->db = M_MSQL::Instance();

        $this->parent_name = $parent_name;
        $this->level_name = $level_name;
        $this->table_name = $table_name;
        $this->id_name="id";
    }

    //Возвращает массив состоящий из элементов дерева
    //fields - поля которые должны содержаться в результатах запроса, перечисленные через запятую в строке
    //order_by - поле по которому будет производится сортировка результатов.
    // part_of_sql_arter_from - строка с остальными SQL оператороми которые должны идти после FROM имя таблицы
    public function get_all($fields, $order_by, $part_of_sql_arter_from){
        $sql = "SELECT ".$this->id_name.", ".$this->level_name.", ".$this->parent_name.", ".$fields." FROM ".$this->table_name." ".$part_of_sql_arter_from."ORDER BY ".$order_by;

        $result = $this->db->Select($sql);
        $arr_tree = array();
        foreach($result as $item){
            if(empty($arr_tree[$item->{$this->parent_name}]))
                $arr_tree[$item->{$this->parent_name}]= array();
            $arr_tree[$item->{$this->parent_name}][] = $item;
        }

        return $arr_tree;
    }

    //Добавляет элемент дерева в таблицу
    //parent - идентификатор родительского элемента
    //mass_fields - массив описывающий добавляемый элемент (имя поля/значение)
    //Вернёт true в случае успешного добавления
    public function  add($parent, $mass_fields, &$last_inser_id){
        $mass_fields[$this->parent_name] = $parent;
        $id = $this->db->Insert($this->table_name, $mass_fields);
        if($id==$parent){//Если каким то образом получилось, что у добавляемого элемента родитель равен идентификатору, то нужно удалить такой элемент.
            $this->db->Delete($this->table_name,$this->id_name, $id);
            return false;
        }
        $last_inser_id = $id;
        return true;
    }

    //Удаляет элемент дерева из таблицы бд.
    // id - идентификатор удаляемого элемента
    // $level - уровень удаляемого элемента.
    public function delete_with_suns($id, $level){
        $sql = "SELECT ".$this->id_name.", ".$this->level_name.", ".$this->parent_name." FROM ".$this->table_name." WHERE ".$this->level_name."> ? ORDER BY ".$this->level_name;
        $result = $this->db->Select($sql, $level);
        $id*=1;
        $str_del_id= "?";
        $itog_mass_del_id = array();

        array_push($itog_mass_del_id, $id);
        $mass_del_id = array();
        $mass_temp = array();
        array_push($mass_del_id, $id);
        $level_work = $level+1;
        foreach($result as $item){
            if($level_work!=$item->{$this->level_name}){
                $level_work=$item->{$this->level_name};
                $mass_del_id = $mass_temp;
                $mass_temp  = array();
            }
            for($i=0; $i<count($mass_del_id);++$i){
                if($item->{$this->parent_name}==$mass_del_id[$i]) {
                    array_push($mass_temp, $item->{$this->id_name});
                    $str_del_id=$str_del_id.", ?";
                    array_push($itog_mass_del_id, $item->{$this->id_name});
                }
            }

        }

        return $this->db->ExecuteSQL("DELETE FROM ".$this->table_name." WHERE ".$this->id_name." IN( ".$str_del_id." )", $itog_mass_del_id);
    }
}
?>
