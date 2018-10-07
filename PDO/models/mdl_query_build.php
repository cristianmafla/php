<?php

class Conx {
    //PARAMETROS DE CONEXION
    static private $dsn = 'mysql:host=localhost;dbname=colombia';
    static private $user = 'root';
    static private $pass = '';
    
    public function __construct() {
    }
    
    public function cnx(){
        try {
            //CAPTURADOR DE ERRORES Y CAPTURADOR DE CARACTERES utf8
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
            );
            $dbh = new PDO(self::$dsn, self::$user, self::$pass,$options);
            return $dbh;
        }catch(PDOException $e) {
            $msn = 'connection error: '.$e->getMessage();
            echo $msn;
        }
    }
}
