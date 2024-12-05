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

        public function BadReq(){
            header("HTTP/1.1 400 Bad Request");
            $data = ["status"=> "400","message"=> "Missing details"];
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
        }//alreadyExists

        public function NoResFound(){
            header("HTTP/1.1 204 No Content");
            $data = ["status"=> "204","message"=> "No Data Found"];
            echo json_encode($data);
        }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        public function Login($email, $password){

            if(!isset($email) || !isset($password) || !$this->isValidPassword($password)){
                $this->BadReq();
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
                $hashedPassword = $this->Hashing($password);
                $SQLQuery->bind_param('ss', $email, $hashedPassword);
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
                        $data = ["status"=>"200","data"=>["email"=>$email, "name"=>$name,""=>$surname,"USER_ID "=>$USER_ID]];
                        header("HTTP/1.1 200 OK");
                        echo json_encode($data);
                    }
                }else{$this->SQLEXECUTEEROR();}
            }else{
                $this->SQLERROR();
            }
        }//valid user
        }//login

        public function Register($name, $surname, $email, $password, $DOB){
            $hashedPassword = $this->Hashing($password);

            if(!$this->isValidPassword($password)){
                header("HTTP/1.1 400 Invalid Password");
                $data = ["status"=>"400","message"=>"Password is invalid"];
                echo json_encode($data);
                die();
            }

            if(!isset($name) || !isset($surname) || !isset($email) || !isset($DOB) || !isset($password)){
                $this->BadReq();
            }

            if($this->alreadyExists($email,$hashedPassword)){
                header("HTTP/1.1 422 OK");
                $data = ["status"=> "422","message"=> "User already exists"];
            }else{
                $Query = "INSTERT INTO USER (name, surname, email, password, DOB) VALUES (?,?,?,?);";
                $SQLQuery = $this->db->prepare($Query);
                if($SQLQuery){
                    $SQLQuery->bind_param("sssss",$name, $surname, $email, $password, $DOB);
                    $didit = $SQLQuery->execute();
                    if($didit){
                        $data = ["status"=> "201","message"=> "Succesfully Registered"];
                        header("HTTP/1.1 201 Created");
                        echo json_encode($data);
                    }else{$this->SQLEXECUTEEROR();}
                }else{$this->SQLERROR();}
            }//register now
        }//register

        public function SearchUser($name, $surname, $email){

            if(!isset($name) && !isset($surname) && !isset($email)){
                $this->BadReq();
            }//if missing all details


            $Query = "SELECT * FROM USER WHERE name LIKE ? OR surname LIKE ? OR email LIKE ?";
            $SQLQuery = $this->db->prepare($Query);
            if($SQLQuery){
                $SQLQuery->bind_param("sss",$name, $surname,$email);
                $didit = $SQLQuery->execute();
                if($didit){
                    $res = $SQLQuery->get_result();
                    if($res->num_rows > 0){
                        while($row = $res->fetch_assoc()){
                            $users[] = $row;
                        }
                        $data = ["status"=> "200","data"=> $users];
                        header("HTTP/1.1 200 OK");
                        echo json_encode($data);
                    }else{$this->NoResFound();}
                }else{$this->SQLEXECUTEEROR();}
            }else{$this->SQLERROR();}
        }//searchUser

        public function DeleteUser($name, $surname, $email){
            $Query = "";
        }//Delete User





    }
    ///////////////////////////////////////////////////////////////////////////
    $api = API::instance();
    $input_data = json_decode(file_get_contents("php://input"),true);

    if($_SERVER['REQUEST_METHOD'] == "POST"){

        if(isset($input_data) && $input_data["type"] == "Login"){
            $api->Login($input_data["email"], $input_data["password"]);
        }

        if(isset($input_data) && $input_data["type"] == "Register"){
            $api->Register($input_data["name"],$input_data["surname"],$input_data["email"],$input_data["password"],$input_data["DOB"]);
        }

    }//different endpoints for POST
    

    if($_SERVER["REQUEST_METHOD"] != "POST"){
        $data = ["status"=>"405", "message"=> $_SERVER['REQUEST_METHOD']." not allowed :("];
        echo json_encode($data);
    }
?>