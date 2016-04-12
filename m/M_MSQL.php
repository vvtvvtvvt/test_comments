<?php
class M_MSQL{
	private static $instance;	// экземпляр класса
	private $PDO;
	private $result;
	private $prepare = array();
	//
	// Получение экземпляра класса
	// результат	- экземпляр класса MSQL
	//
	public static function Instance()
	{
		if (self::$instance == null)
			self::$instance = new M_MSQL();

		return self::$instance;
	}
	private function __construct() {
		try {
			$DB_DSN = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
			$this->PDO = new PDO($DB_DSN, DB_USER, DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
			$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

		}
		catch(PDOException $e){
			echo "Ошибка. Неудалось подключиться к базе данных.";
			file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
		}
	}
	//вспомогательные функции
	public function ExecuteSQL($sql, $args=null, $clear=true){

		try {
			if(!isset($this->prepare[$sql]))
				$this->prepare[$sql]  = $this->PDO->prepare($sql);
			if(!is_null($args)&&count($args)!=0) {
				$this->prepare[$sql]->execute($args);
			}
			else {
				$this->prepare[$sql]->execute();
			}
			$this->result= $this->prepare[$sql];
			return true;
		}
		catch(PDOException $e) {
			$param="";
			for($i=0; $i<count( $args); ++$i){
				$param=$param.$args[$i];
			}
			echo "Ошибка. Неудалось выполнить запрос:".$sql.", параметры:". $param.".";
			file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
			return false;
		}
	}


	//Функции для низкоуровневой работы с базой.
	public function StartTranzaktion(){
		$this->PDO->beginTransaction();
	}
	public function EndTranzaktion(){
		$this->PDO->commit();
	}
	public function OtkatTranzaktion(){
		$this->PDO->rollBack();
	}

	//Функция принимает SQL запрос, вместе с параметрами и выполняет его.
	public function query($sql) {
		$args = func_get_args();
		$sql = array_shift($args);

		return $this->ExecuteSQL($sql, $args );
	}
	//функция возвращает объект из одной строки результатов последнего выполненного запроса
	public function Get1Result() {
		$link2 = $this->result;
		return $link2->fetch(PDO::FETCH_OBJ);
	}
	//Возвращает все результаты последнего запроса, в виде массива объектов.
	public function GetAllResult() {
		$result = array();
		while($res = $this->Get1Result()){
			$result[] = $res;
		}

		return $result;
	}

	//Более высокоуровневые методы для работы с базой

	//
	// Выборка строк
	// $query    	- полный текст SQL запроса
	// результат	- массив выбранных объектов
	//ВНИМАНИЕ: $query подставляется в запрос без проверок, поэтому они не должны на прямую зависить
	//от клиентских данных
	public function Select($query)
	{
		$args = func_get_args();
		$sql = array_shift($args);
		//var_dump($args);
		if($this->ExecuteSQL($sql, $args )){
			return $this->GetAllResult();
		}
	}

	//
	// Вставка строки
	// $table 		- имя таблицы
	// $object 		- ассоциативный массив с парами вида "имя столбца - значение"
	// результат	- идентификатор новой строки
	//ВНИМАНИЕ: $table подставляется в запрос без проверок, поэтому они не должны на прямую зависить
	//от клиентских данных
	public function Insert($table, $object)
	{
		$columns = array();
		$values = array();
		$plasholders = array();

		foreach ($object as $key => $value)
		{
			$key = self::clearstr(trim($this->PDO->quote($key . ''),"'"));
			$columns[] = $key.'';
			$plasholders[]="?";
			if ($value === null)
			{
				$values[] = 'NULL';
			}
			else
			{
				$values[] = self::clearstr($value);
			}
		}

		$SQLcolumns = implode(',', $columns);
		$SQLplasholder = implode(',',  $plasholders);

		$query = "INSERT INTO $table ($SQLcolumns) VALUES ($SQLplasholder)";
		$result = $this->ExecuteSQL($query, $values);

		if (!$result){
			echo "Ошибка не удалось добавить запись";
			die();
		}

		return $this->PDO->lastInsertId();
	}

	public function LastInsertId(){
		return $this->PDO->lastInsertId();
	}

	//
	// Изменение строк
	// $table 		- имя таблицы
	// $object - асоциативный массив параметров которые нужно изменить
	// $where		- условие вида id = ?, где вместо вопроса будет подставлен парметр  $valueWhere
	// $valueWhere  - взначение подставляемое в $where
	// результат	- число обработных строк
	//ВНИМАНИЕ: $table и $where подставляются в запрос без проверок, поэтому они не должны на прямую зависить
	//от клиентских данных
	public function Update($table, $object, $where, $valueWhere)
	{
		$valueWhere = self::clearstr($valueWhere, false);
		$sets = array();
		$params = array();

		foreach ($object as $key => $value)
		{
			$key = self::clearstr(trim($this->PDO->quote($key),"'"));

			$sets[] = "$key = ?";

			if ($value === null)
			{
				$params[] = "NULL";
			}
			else
			{
				$params[] = self::clearstr($value);
			}
		}
		$params[] = $valueWhere;
		$sets_s = implode(',', $sets);
		$query = "UPDATE $table SET $sets_s WHERE $where";

		$result = $this->ExecuteSQL($query, $params);

		if (!$result){
			echo "Ошибка не удалось редактировать запись";
			die();
		}

		return $this->result->rowCount();
	}

	//
	// Удаление строк
	// $table 		- имя таблицы
	// $where		- условие вида id = ?, где вместо вопроса будет подставлен парметр  $valueWhere
	// $valueWhere  - взначение подставляемое в $where
	// результат	- число удаленных строк
	//ВНИМАНИЕ: $table и $where подставляются в запрос без проверок, поэтому они не должны на прямую зависить
	//от клиентских данных
	public function Delete($table, $where, $valueWhere)
	{
		//$valueWhere = self::clearstr($valueWhere, false);
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		$query = "DELETE FROM ".$table." WHERE ".$where;

		$result = $this->ExecuteSQL($query, $args);

		if (!$result) {
			echo "Ошибка не удалось удалить запись";
			die();
		}
		return $this->result->rowCount();
	}



	public static function clearstr($str, $clear=true, $convert=true)
	{
		if($clear)
			$str = strip_tags($str);
		if($convert)
			$str = htmlspecialchars($str);
		return $str;
	}

}
?>