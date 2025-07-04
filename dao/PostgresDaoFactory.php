<?php

include_once('DaoFactory.php');
include_once('PostgresUsuarioDao.php');
include_once('PostgresProdutoDao.php');
include_once('PostgresFornecedorDao.php');
include_once('PostgresPedidoDao.php');

class PostgresDaofactory extends DaoFactory {

    // specify your own database credentials
    private $host = "localhost";
    private $db_name = "eCommerce";
    private $port = "5432";
    private $username = "postgres";
    private $password = "ucs";
    public $conn;
  
    // get the database connection
    public function getConnection(){
  
        $this->conn = null;
  
        try{
            $this->conn = new PDO("pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name, $this->username, $this->password);
            //$this->conn = new PDO("pgsql:host=localhost;port=5432;dbname=PHP_tutorial", $this->username, $this->password);
            
            // Configurações básicas do PDO
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
    
      }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }

    public function getUsuarioDao() {
        return new PostgresUsuarioDao($this->getConnection());

    }

    public function getProdutoDao() {
        return new PostgresProdutoDao($this->getConnection());
    }

    public function getFornecedorDao() {
        return new PostgresFornecedorDao($this->getConnection());
    }

    public function getPedidoDao(){
        return new PostgresPedidoDao($this->getConnection());
    }
    
}
?>