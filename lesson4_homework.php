<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', E_ALL);


// фабрики MysqlFactory, PostgreFactory, OracleFactory создают следующие продукты:
// InsertOne - создать запись. Аргументы единственного метода: имя таблицы, массив ['имя столбца' => 'значение']. Возвращаемое значение - true (ok) или false (не-ok) 
// ReadOne - получить запись по id записи. Аргументы единственного метода: имя таблицы, id записи. Возвращаемое значение - массив ['имя столбца' => 'значение'] или NULL, если записи с интересующим id нет.
// UpdateOne - обновить запись по id записи. Аргументы единственного метода: имя таблицы, id записи, массив ['имя столбца' => 'значение']. Возвращаемое значение - true (ok) или false (не-ok). 
// DeleteOne - удалить запись по id записи. Аргументы единственного метода: имя таблицы, id записи. Возвращаемое значение - true (ok) или false (не-ok)

// конструктор для фабрики mysql определен непосредственно в абстрактном классе, от которого наследуются фабрики. Конструктор для фабрики PostgreFactory переопределяется в классе PostgreFactory. Аналогично - для Oracle.


// интерфейс для фабрик
interface IAbstractFactory {
    public function dbInsert($table, $colsVals): bool;
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
    
    public function dbInsert($table, $colsVals): bool {
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
    public function dbInsert($table, $colsVals): bool;
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
    
    public function dbInsert($table, $colsVals): bool {
        
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
        return $stmt->execute($colsVals);
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

// новый экземпляр фабрики для работы с mysql
$factoryMySql = new MysqlFactory('127.0.0.1', 'root', 'Popipo!234', 'myDb');

// добавление новой записи - работает
/*if (($factoryMySql->dbInsert('content', ['value' => 'opr'])) == true) {
    echo 'true';
} else {
    echo 'false';
}*/

// получение записи по id - работает
/*var_dump($factoryMySql->dbRead('content', 1));*/


// обновление записи по id - работает
/*if (($factoryMySql->dbUpdate('content', 14, ['value' => 'ddddddd'])) == true) {
    echo 'true';
} else {
    echo 'false';
}*/

// удаление записи по id - работает
/*if (($factoryMySql->dbDelete('content', 14)) == true) {
    echo 'true';
} else {
    echo 'false';
}*/

// -----------------------------------------------------


