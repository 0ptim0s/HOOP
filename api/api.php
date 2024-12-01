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

        public function Hashing($password){
            $res = sha1($password);
            return $res;
        }

        public function SQLERROR(){
            header('HTTP/1.1 500 Internal Error');
            $data = ["status"=>"500","message"=> "SQL prepare error", "timestamp"=> time()];
            echo json_encode($data);
            die();
        }

        public function SQLEXECUTEEROR(){
            header("HTTP/1.1 500 Internal Error");
            $data = ["status"=>"500","message"=> "SQL execute error", "timestamp"=> time()];
            echo json_encode($data);
            die();
        }

        public function alreadyExists($email, $password){
            $Query = "SELECT * FROM USER WHERE email = ? AND password = ?";
            $hashedPassword = $this->Hashing($password);
            $SQLQuery = $this->db->prepare($Query);
            if($SQLQuery){
            $SQLQuery->bind_param("ss", $email,$hashedPassword);
            $didit = $SQLQuery->execute();
            if($didit){
                $res = $SQLQuery->get_result();
                return ($res->num_rows > 0) ? true : false;
            }else{
                $this->SQLEXECUTEEROR();
            }
        }else{$this->SQLERROR();}
        }

        public function login($email, $password){

            if(!isset($email) || !isset($password) || !$this->isValidPassword($password)){
                header("HTTP/1.1 400 Bad Request");
                $data = ["status"=>"400", "message"=>"Invalid details"];
                echo json_encode($data);
                die();
            }//input validation

            if(!$this->alreadyExists($email, $password)){
                header("HTTP/1.1 200 OK");
                $data = ["status"=>"200", "message"=>"Register Now!"];
                echo json_encode($data);
            }//needs to register

            if($this->alreadyExists($email, $password)){
            $Query = 'SELECT * FROM USER WHERE email = ? AND password = ? ';
            $SQLQuery = $this->db->prepare($Query);
            if($SQLQuery){
                $SQLQuery->bind_param('ss', $email, $password);
                $didit = $SQLQuery->execute();
                if($didit){
                    $res = $SQLQuery->get_result();
                    while($row = $res->fetch_assoc()){
                        $name = $row['name'];
                        $surname = $row['surname'];
                        $USER_ID = $row['USER_ID'];
                        $email = $row['email'];
                        $favourites = $row['Favourites'];

                        $_SESSION['USER_ID'] = $USER_ID;
                        $_SESSION['name'] = $name;
                        $_SESSION['surname'] = $surname;
                        $_SESSION['favourites'] = $favourites;
                        $data = ["status"=>"success","data"=>["email"=>$email, "name"=>$name,""=>$surname,"USER_ID "=>$USER_ID]];
                        header("HTTP/1.1 200 OK");
                        echo json_encode($data);
                    }
                }else{$this->SQLEXECUTEEROR();}
            }else{
                $this->SQLERROR();;
            }
        }//valid user
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