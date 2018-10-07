<?php
/*===================================================================
CONEXION A LA BASE DE DATOS "cristia2_bd"
=====================================================================*/
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

class DB {
public function __construct() {
}

/*=======================================================================
===
FUNCION PARA CONSULTAR LA BD
* DB::Sw('tabla', array('key_item'=>'val_item'),
array('field','field'))
=========================================================================
=*/
public function Sw($table, $item = NULL,$data = NULL) {
$field = NULL;
$data_error = 'Error al solicitar los datos';
$data_not_res = 'Sin resultados de consulta';
if($data){
$field = implode(',',$data);
}else{
$field = "*";
}
if(!$item){
$query = "SELECT $field FROM $table";
$stmt = Conx::cnx()->prepare($query);
if($stmt->execute()){
$res = array(
'state' => TRUE,
'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
);
}else{
$res = array(
'state' => FALSE,
'data' => $data_error
);
}
}else{
foreach ($item as $key => $value) {
$exe_data[":$key"] = $value;
$param_where = "$key= :$key";
}
$query = "SELECT $field FROM $table WHERE $param_where";

$stmt = Conx::cnx()->prepare($query);
if($stmt->execute($exe_data)){
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
if($fields){
$res = array(
'state' => TRUE,
'data' => $fields
);
}else{
$res = array(
'state' => FALSE,
'data' => $data_not_res
);
}
}else{
$res = array(
'state' => FALSE,
'data' => $data_error
);
}
}
return $res;
$stmt -> close();
$stmt = null;
}

/*=======================================================================
===========
FUNCION PARA INSERTAR Y MODIFICAR LA BD
* DB::Sw('tabla', array('key_item'=>'val_item'),
array('key_field','val_field'))
=========================================================================
=========*/
public function IUw($table,$item = NULL, $data = NULL) {
$data_insert = 'Se inserto correctamente.';
$data_insert_error = 'Error al insertar los datos.';
$data_update = 'Se actualizó correctamente.';
$data_update_error = 'Error al actualizar los datos.';
if($item){
$data_not_reg = "No existe el registro que desea
modificar: ".key($item)." = ".implode(' ',$item);
}
$query = NULL;
if(!$item){
$fields = implode(',',array_keys($data));
$ref_fields = implode(', :',array_keys($data));
$ref_fields = ":$ref_fields";

$query = "INSERT INTO $table ($fields) VALUES
($ref_fields)";
$stmt = Conx::cnx()->prepare($query);
if($stmt->execute($data)){
$res = array(
'state' => TRUE,
'data' => $data_insert
);
}else {
$res = array(
'state' => FALSE,
'data' => $data_insert_error
);
}
}else{
$data_verf = self::Sw($table,$item,NULL);
if($data_verf['state']){
$field_and_ref = NULL;
foreach ($data as $key => $value) {
$exe_data[":$key"] = $value;
$field_and_ref.= "$key= :$key, ";
}
$field_and_ref = substr($field_and_ref,0,-2);
foreach ($item as $key => $value) {
$exe_data[":$key"] = $value;
$param_where = "$key= :$key";
}
$query = "UPDATE $table SET $field_and_ref WHERE
$param_where";
$stmt = Conx::cnx()->prepare($query);
if( $stmt->execute($exe_data)){
$res = array(
'state' => TRUE,
'data' => $data_update
);
}else {
$res = array(
'state' => FALSE,
'data' => $data_update_error
);
}
}else{
$res = array(
'state' => FALSE,
'data' => $data_not_reg
);
}
}
return $res;

$stmt -> close();
$stmt = null;
}

/*=======================================================================
=========================
FUNCION PARA ELIMINAR CAMPOS DE UNA TABLA Y LA TABLA COMPLETA
* DB::Dw('tabla',array('key_item' => 'val_item'),TRUE ||
FALSE)
nota:
* si se pasa el 4 parametro como TRUE se eliminan todos los
campos de la tabla con un TRUNCATE
=========================================================================
=======================*/
public function Dw($table, $item = NULL, $delete_table = NULL){
$res = NULL;
$data_dlt_ok = 'Se elimino correctamente.';
$data_dlt_error = 'Error al eliminar el registro';
$data_dlt_full_reg = "Se eliminaron todos los registros en la
tabla: ( $table )";
$data_dlt_error_full_reg = "Error al eliminar los registros
en la tabla: ( $table )";
$data_error_query = 'Error de consulta : ERROR:PARAM';
if($item){
$data_not_reg = 'No existe el registro que desea
eliminar: '.key($item)." = ".implode(' ',$item);
}
if($item && !$delete_table){
foreach ($item as $key => $value) {
$data_delete[":$key"] = $value;
$item_ref = "$key= :$key";
}
$query = "DELETE FROM $table WHERE $item_ref";
$stmt = Conx::cnx()->prepare($query);
$const = self::Sw('t_usuarios',$item,NULL);
if($const['state']){
if($stmt->execute($data_delete)){
$res = array(
'state' => TRUE,
'data' => $data_dlt_ok
);
}else{
$res = array(
'state' => FALSE,
'data' => $data_dlt_error
);
}

}else{
$res = array(
'state' => FALSE,
'data' => $data_not_reg
);
}
}else if($delete_table && !$item){
$query = "TRUNCATE TABLE $table";
$stmt = Conx::cnx()->prepare($query);
if($stmt->execute()){
$res = array(
'state' => TRUE,
'data' => $data_dlt_full_reg
);
}else{
$res = array(
'state' => FALSE,
'data' => $data_dlt_error_full_reg
);
}
}else if($delete_table && $item){
$res = array(
'state' => FALSE,
'data' => $data_error_query
);
}
return $res;
$stmt -> close();
$stmt = null;
}
}
