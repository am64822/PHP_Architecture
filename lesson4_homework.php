<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', E_ALL);


// фабрики MySQLFactory, PostgreSQLFactory, OracleFactory возвращают уже настроенный объект для работы с соответствующей БД. Любой из подобных объектов поддерживает следующие методы:
// Create - создать запись. Аргументы: имя таблицы, массив ['имя столбца' => 'значение']. Возвращаемое значение - true (ok) или false (не-ok) 
// Read - получить запись по id записи. Аргументы: имя таблицы, id записи. Возвращаемое значение - массив ['имя столбца' => 'значение'] или NULL, если записи с интересующим id нет.
// Update - обновить запись по id записи. Аргументы: имя таблицы, id записи, массив ['имя столбца' => 'значение']. Возвращаемое значение - true (ok) или false (не-ok). 
// Delete - удалить запись по id записи. Аргументы: имя таблицы, id записи. Возвращаемое значение - true (ok) или false (не-ok)


// суперкласс для фабрик
abstract class SuperClass {
    public function dbInterfaceCreate($host, $user, $password, $db) {
    }
}

// фабрика для создания объекта для работы с mysql
class MysqlPDO extends superClass {
    public function dbInterfaceCreate($host, $user, $password, $db) {
        return new MysqlDbInterface($host, $user, $password, $db);
    }    
}

// фабрика для создания объекта для работы с PostgreSQL
class PostgreSQLPDO extends superClass {
    public function dbInterfaceCreate($host, $user, $password, $db) {
        return new PostgreDbInterface($host, $user, $password, $db);
    }    
}

// фабрика для создания объекта для работы с Oracle
class OraclePDO extends superClass {
    public function dbInterfaceCreate($host, $user, $password, $db) {
        return new OracleDbInterface($host, $user, $password, $db);
    }    
}

// Интерфейс для объектов, возвращаемых фабриками:
interface Idbobject
{
    public function dbInsert($tabel, $colsVals): bool;
    public function dbRead($tabel, $id): ?array;
    public function dbUpdate($tabel, $id, $colsVals): bool;
    public function dbDelete($tabel, $id): bool;
}


// классы объектов [-продуктов], возвращаемых фабриками
// ------> рабочий пример - для mysql ниже

// Oracle
class OracleDbInterface implements Idbobject {
    private $pdo;
    private $db;
    
    public function __construct($host, $user, $password, $db) {
        // $dsn = ... ;
        // $this->pdo = new PDO($dsn, $user, $password);
        // $this->db = $db;
    }
    
    public function dbInsert($tabel, $colsVals): bool {  
        // ....
        return true; // или false
    }
    
    public function dbRead($tabel, $id): ?array {
        // ....
        return null; // или array
    }
    
    public function dbUpdate($tabel, $id, $colsVals): bool {
        // ....
        return true; // или false        
    }
    
    public function dbDelete($tabel, $id): bool {
        // ....
        return true; // или false       
    }
    
}

// PostgreSQL
class PostgreDbInterface implements Idbobject {
    private $pdo;
    private $db;
    
    public function __construct($host, $user, $password, $db) {
        // $dsn = ... ;
        // $this->pdo = new PDO($dsn, $user, $password);
        // $this->db = $db;
    }
    
    public function dbInsert($tabel, $colsVals): bool {  
        // ....
        return true; // или false
    }
    
    public function dbRead($tabel, $id): ?array {
        // ....
        return null; // или array
    }
    
    public function dbUpdate($tabel, $id, $colsVals): bool {
        // ....
        return true; // или false        
    }
    
    public function dbDelete($tabel, $id): bool {
        // ....
        return true; // или false       
    }
    
}


// mySQL
class MysqlDbInterface implements Idbobject {
    private $pdo;
    private $db;
    
    public function __construct($host, $user, $password, $db) {
        $dsn = 'mysql:host' . $host . ';dbname=' . $db;
        $this->pdo = new PDO($dsn, $user, $password);
        $this->db = $db;
    }
    
    public function dbInsert($tabel, $colsVals): bool {  
        $cols = ''; // список столцов через запятую
        $vals = ''; // список имен значений через запятую. В качестве пар имя-значение в execute будет использоваться массив $colsVals

        foreach ($colsVals as $key => $value) {
            $cols .= $key . ', ';
            $vals .= ':' .$key . ', ';
        }       
        $cols = '(' . substr($cols, 0, -2) . ')'; // убрать ', ' в конце
        $vals = '(' . substr($vals, 0, -2) . ')'; // убрать ', ' в конце
        
        $sql = 'INSERT INTO ' . $this->db . '.' . $tabel .' '. $cols . ' VALUES ' . $vals . ';'; //echo $sql;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($colsVals);
    }
    
    public function dbRead($tabel, $id): ?array {
        $sql = 'SELECT * FROM ' . $this->db . '.' . $tabel . ' WHERE id=' . $id . ';'; //echo $sql;
        $stmt = $this->pdo->query($sql);
        $repl = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($repl)) {
            return null;
        } else {
            return $repl;
        }
    }
    
    public function dbUpdate($tabel, $id, $colsVals): bool {
        if (is_null($this->dbRead($tabel, $id))) { // execute возвращает true даже при попытке обновить несуществующую запись. Пришлось добавить проверку на наличие записи с интересующим id
            return false;
        }
                
        $colsVals['id'] = $id; // В качестве пар имя-значение в execute будет использоваться массив $colsVals, к которому будет добавлена пара id= :id

        $cols_vals = ''; // список столцов и список имен значений через запятую.
        foreach ($colsVals as $key => $value) {
            $cols_vals .= $key . '= :' . $key . ', ';
        }       
        $cols_vals = substr($cols_vals, 0, -2); // убрать ', ' в конце
        
        $sql = 'UPDATE ' . $this->db . '.' . $tabel .' SET '. $cols_vals . ' WHERE id= :id;'; //echo $sql;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($colsVals);        
    }
    
    public function dbDelete($tabel, $id): bool {
        if (is_null($this->dbRead($tabel, $id))) { // execute возвращает true даже при попытке удалить несуществующую запись. Пришлось добавить проверку на наличие записи с интересующим id
            return false;
        }        
        
        $sql = 'DELETE FROM ' . $this->db . '.' . $tabel . ' WHERE id= :id;'; //echo $sql;
        $colsVals['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($colsVals);        
    }
    
}



// -------------  тест работы с mysql ------------------

// новый экземпляр фабрики для создания объекта для работы с mysql
$factoryMySql = new MysqlPDO();
// новый экземпляр [продукта-] интерфейса для работы с БД
$mysqlDbInterface = $factoryMySql->dbInterfaceCreate('127.0.0.1', 'root', 'Popipo!234', 'myDb');


// добавление новой записи - работает
/*if (($mysqlDbInterface->dbInsert('content', ['value' => 'opr'])) == true) {
    echo 'true';
} else {
    echo 'false';
}*/

// получение записи по id - работает
/* var_dump($mysqlDbInterface->dbRead('content', 1)); */

// обновление записи по id - работает
/*if (($mysqlDbInterface->dbUpdate('content', 2, ['value' => 'opr'])) == true) {
    echo 'true';
} else {
    echo 'false';
}*/

// удаление записи по id - работает
/*if (($mysqlDbInterface->dbDelete('content', 7)) == true) {
    echo 'true';
} else {
    echo 'false';
}*/

