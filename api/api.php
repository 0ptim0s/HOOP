<?php
    include_once "config.php";
    session_start();
    error_reporting(E_ALL); //reports all errors
    header("Access-Control=Allow-Method: POST");
    header("Content-Type: application/json; charset=utf-8");

    class API{
        private $db;

        public static function instance(){
            static $instance = null;
            if($instance == null){
                $instance = new API();
            }
            return $instance;
        }

        public function __construct(){
            $this->db = Database::instance();
        }

        public function isValidPassword($password){
            $passwordRegex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
            if(preg_match($passwordRegex, $password) == 0){
                return false;
            }
            return true;
        }




    }
    ///////////////////////////////////////////////////////////////////////////
    $api = API::instance();
    $input_data = json_decode(file_get_contents("php://input"),true);

    if($_SERVER['REQUEST_METHOD'] == "POST"){



    }//different endpoints for POST
    

    if($_SERVER["REQUEST_METHOD"] != "POST"){
        $data = ["status"=>"405", "message"=> $_SERVER['REQUEST_METHOD']." not allowed :("];
        echo json_encode($data);
    }
?>