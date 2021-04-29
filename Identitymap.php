<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', E_ALL);


/* тест - в самом внизу. Алгоритм(ы) теста
- добавление в БД: добавить новую запись в БД, получить last_insert_id (или 0, если не OK). Если last_insert_id <> 0, то создать новый объект класса Content (имя класса = имени таблицы БД) с id = last_insert_id и соответствующим value, добавить объект в IdentityMap
- чтение по id (например, 3): запросить в IdentityMap объект по интересующему классу (Content) и id (например, 3). Если не null, то вывести на экран сообщение, что объект получен из IdentityMap. Если null, то запросить запись из БД, если успешно, то создать новый объект класса Content с соответствующими id и value, добавить объект в IdentityMap
- повторить чтение по id (например, 3). Сообщение должно указывать на то, что объект найден в IdentityMap
*/



class IdentityMap {
    private $identityMap = [];
    
    public function add(IDomainObject $obj) {
        $key = $this->getGlobalKey(get_class($obj), $obj->getId());
        $this->identityMap[$key] = $obj;
        echo ('<pre>В IdentityMap добавлен объект:<br>');
        var_dump($obj);
        echo ('<br>-------------');
        echo ('</pre><br>');
    }
    
    public function get(string $classname, int $id): ?IDomainObject {
        $key = $this->getGlobalKey($classname, $id);
        if (isset($this->identityMap[$key])) {
            echo ('<pre>Объект ' . $classname.'.'.$id . ' найден в IdentityMap<br>');
            echo ('<br>-------------');
            echo ('</pre><br>');            
            return $this->identityMap[$key];
        }
        echo ('<pre>В IdentityMap объект ' . $classname.'.'.$id . ' не найден<br>');
        echo ('<br>-------------');
        echo ('</pre><br>');      
        return null;
    }
    
    private function getGlobalKey(string $classname, int $id) {
        return sprintf('%s.%d', $classname, $id);
    }
}



// ---------------------

interface IDomainObject {
    public function getId(): int;
}


class Content implements IDomainObject {
    public $id;
    public $value;
    
    public function __construct($id, $value) {
        $this->id = $id;
        $this->value = $value;
    }
    
    public function getId(): int {
        return $this->id;
    }    
}



// фабрики MysqlFactory, PostgreFactory, OracleFactory создают следующие продукты:
// InsertOne - создать запись. Аргументы единственного метода: имя таблицы, массив ['имя столбца' => 'значение']. Возвращаемое значение - true (ok) или false (не-ok) 
// ReadOne - получить запись по id записи. Аргументы единственного метода: имя таблицы, id записи. Возвращаемое значение - массив ['имя столбца' => 'значение'] или NULL, если записи с интересующим id нет.
// UpdateOne - обновить запись по id записи. Аргументы единственного метода: имя таблицы, id записи, массив ['имя столбца' => 'значение']. Возвращаемое значение - true (ok) или false (не-ok). 
// DeleteOne - удалить запись по id записи. Аргументы единственного метода: имя таблицы, id записи. Возвращаемое значение - true (ok) или false (не-ok)

// конструктор для фабрики mysql определен непосредственно в абстрактном классе, от которого наследуются фабрики. Конструктор для фабрики PostgreFactory переопределяется в классе PostgreFactory. Аналогично - для Oracle.


// интерфейс для фабрик
interface IAbstractFactory {
    public function dbInsert($table, $colsVals): int;
    public function dbRead($table, $id): ?array;
    public function dbUpdate($table, $id, $colsVals): bool;
    public function dbDelete($table, $id): bool;
}

// абстрактный класс для фабрик
abstract class AbstractFactory implements IAbstractFactory {
    protected $pdo;
    protected $db;

    // конструктор переопределяется в фабриках для работы с PostgreSQL и Oracle,
    // т.к. для создания PDO для PostgreSQL и Oracle требуются другие dsn 
    public function __construct($host, $user, $password, $db) {
        $dsn = 'mysql:host' . $host . ';dbname=' . $db;
        $this->pdo = new PDO($dsn, $user, $password);
        $this->db = $db;
    }
    
    public function dbInsert($table, $colsVals): int {
        return (new InsertOne($this->pdo, $this->db))->dbInsert($table, $colsVals);
    }
    
    public function dbRead($table, $id): ?array {
        return (new ReadOne($this->pdo, $this->db))->dbRead($table, $id);
    }
    
    public function dbUpdate($table, $id, $colsVals): bool {
        return (new UpdateOne($this->pdo, $this->db))->dbUpdate($table, $id, $colsVals);
    }
    
    public function dbDelete($table, $id): bool {
        return (new DeleteOne($this->pdo, $this->db))->dbDelete($table, $id);
    }
}


// фабрика для создания объекта для работы с mysql
class MysqlFactory extends AbstractFactory {}

// фабрика для создания объекта для работы с PostgreSQL
class PostgreFactory extends AbstractFactory {
    // переопределить конструктор под PostgreSQL
    // в остальном - как для mySQL
}

// фабрика для создания объекта для работы с Oracle
class OracleFactory extends AbstractFactory {
    // переопределить конструктор под Oracle
    // в остальном - как для mySQL
}





// Интерфейсы для продуктов, возвращаемых фабриками:
interface IInsertOne {
    public function dbInsert($table, $colsVals): int;
}

interface IReadOne {
    public function dbRead($table, $id): ?array;
}

interface IUpdateOne {
    public function dbUpdate($table, $id, $colsVals): bool;
}

interface IDeleteOne {
    public function dbDelete($table, $id): bool;
}


// абстрактные классы для продуктов, возвращаемых фабриками
abstract class AInsertOne implements IInsertOne {
    protected $pdo;
    protected $db;
    
    public function __construct($pdo, $db) {
        $this->pdo = $pdo;
        $this->db = $db;
    }
    
    public function dbInsert($table, $colsVals): int {
        
        /*echo('<pre><br>');
        var_dump($this->pdo);
        echo('<br>');
        var_dump($table);
        echo('<br>');
        var_dump($colsVals);
        echo('<br>');*/
        
        $cols = ''; // список столцов через запятую
        $vals = ''; // список имен значений через запятую. В качестве пар имя-значение в execute будет использоваться массив $colsVals

        foreach ($colsVals as $key => $value) {
            $cols .= $key . ', ';
            $vals .= ':' .$key . ', ';
        }       
        $cols = '(' . substr($cols, 0, -2) . ')'; // убрать ', ' в конце
        $vals = '(' . substr($vals, 0, -2) . ')'; // убрать ', ' в конце
        
        $sql = 'INSERT INTO ' . $this->db . '.' . $table .' '. $cols . ' VALUES ' . $vals . ';'; //echo $sql;
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($colsVals) == false) {
            return 0;
        } else {
            return $this->pdo->lastInsertId();
        }
    }
}

abstract class AReadOne implements IReadOne {
    protected $pdo;
    protected $db;
    
    public function __construct($pdo, $db) {
        $this->pdo = $pdo;
        $this->db = $db;
    }
    
    public function dbRead($table, $id): ?array {
        $sql = 'SELECT * FROM ' . $this->db . '.' . $table . ' WHERE id=' . $id . ';'; //echo $sql;
        $stmt = $this->pdo->query($sql);
        $repl = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($repl)) {
            return null;
        } else {
            return $repl;
        }
    }
}

abstract class AUpdateOne implements IUpdateOne {
    protected $pdo;
    protected $db;
    
    public function __construct($pdo, $db) {
        $this->pdo = $pdo;
        $this->db = $db;
    }
    
    public function dbUpdate($table, $id, $colsVals): bool {
        
        // execute возвращает true даже при попытке обновить несуществующую запись. Пришлось добавить проверку на наличие записи с интересующим id
        
        if (is_null((new ReadOne($this->pdo, $this->db))->dbRead($table, $id))) { // execute возвращает true даже при попытке обновить несуществующую запись. Пришлось добавить проверку на наличие записи с интересующим id
            return false;
        }
                
        $colsVals['id'] = $id; // В качестве пар имя-значение в execute будет использоваться массив $colsVals, к которому будет добавлена пара id= :id

        $cols_vals = ''; // список столцов и список имен значений через запятую.
        foreach ($colsVals as $key => $value) {
            $cols_vals .= $key . '= :' . $key . ', ';
        }       
        $cols_vals = substr($cols_vals, 0, -2); // убрать ', ' в конце
        
        $sql = 'UPDATE ' . $this->db . '.' . $table .' SET '. $cols_vals . ' WHERE id= :id;'; //echo $sql;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($colsVals);        
    }
}

abstract class ADeleteOne implements IDeleteOne {
    protected $pdo;
    protected $db;
    
    public function __construct($pdo, $db) {
        $this->pdo = $pdo;
        $this->db = $db;
    }
    
    public function dbDelete($table, $id): bool {
        // execute возвращает true даже при попытке удалить несуществующую запись. Пришлось добавить проверку на наличие записи с интересующим id
        
        if (is_null((new ReadOne($this->pdo, $this->db))->dbRead($table, $id))) { // execute возвращает true даже при попытке обновить несуществующую запись. Пришлось добавить проверку на наличие записи с интересующим id
            return false;
        }
        
        $sql = 'DELETE FROM ' . $this->db . '.' . $table . ' WHERE id= :id;'; //echo $sql;
        $colsVals['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($colsVals);          
    }
}


// классы для продуктов, возвращаемых фабриками
class InsertOne extends AInsertOne {}
class ReadOne extends AReadOne {}
class UpdateOne extends AUpdateOne {}
class DeleteOne extends ADeleteOne {}



// -------------  тест работы с mysql ------------------

// новый экземпляр IdentityMap
$idMap = new IdentityMap();

// новый экземпляр фабрики для работы с mysql
$factoryMySql = new MysqlFactory('127.0.0.1', 'root', 'Popipo!234', 'myDb');

// добавление новой записи в БД, при успехе - добавление в IdentityMap
echo ('<p>Добавление новой записи в БД, при успехе - добавление в IdentityMap</p>');

$value = 'opr';
$last_insert_id = $factoryMySql->dbInsert('content', ['value' => $value]);

if ($last_insert_id != 0) {
    // новый экземпляр Content, добавление в IdentityMap
    echo ('<pre>');
    echo ('Запись с id='. $last_insert_id . ' добавлена в БД');
    echo ('</pre><br>');     
    $idMap->add(new Content($last_insert_id, $value));
} else {
    echo ('Ошибка при добавлении новой записи в БД');
}


// получение записи по id = 3
echo ('<p>Получение записи по id</p>');
$id = 3;
$entryFromIdMap = $idMap->get('Content', $id); // попытка чтения из IdentityMap
if (is_null($entryFromIdMap)) {
    $entryFromDb = $factoryMySql->dbRead('content', $id);
    if(is_null($entryFromDb)) {
        echo ('<pre>');
        echo ('Запись с id='. $id . ' отсутствует и в БД, и в IdentityMap');
        echo ('</pre><br>');
    } else {
        echo ('<pre>');
        echo ('Запись с id='. $id . ' найдена в БД');
        echo ('</pre><br>');        
        $idMap->add(new Content($entryFromDb['id'], $entryFromDb['value']));
    }
}
// повторное получение записи по id=3
$id = 3;
$entryFromIdMap = $idMap->get('Content', $id); // попытка чтения из IdentityMap

// -----------------------------------------------------