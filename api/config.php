<?php
    class Database{
        private $conn;
        public static function instance(){
            static $instance = null;
            if($instance == null){
                $instance = new Database();
            }
            return $instance;
        }


        private function __construct(){
            $servername = "localhost";
            $username = "root";
            $password = "password";

            $this->conn = new mysqli($servername, $username, $password);

            if($this->conn->connect_error){
                die("Connection failed: ". $this->conn->connect_error);
            }else{
                $this->conn->select_db("HOOP");
            }
        }

        public function __destruct(){
            $this->conn->close();
        }
    }
?>