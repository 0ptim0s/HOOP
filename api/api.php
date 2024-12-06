<?php
    include_once "config.php";
    session_start();
    error_reporting(0); //reports all errors
    header("Access-Control-Allow-Method: POST");
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
            header('HTTP/1.1 500 Internal Server Error');
            $data = ["status"=>"500","message"=> "SQL prepare error", "timestamp"=> time()];
            echo json_encode($data);
            die();
        }

        public function SQLEXECUTEEROR(){
            header("HTTP/1.1 500 Internal Server Error");
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
            $SQLQuery = $this->db->prepare($Query);
            if($SQLQuery){
            $SQLQuery->bind_param("ss", $email,$password);
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

        public function alreadyExistsDelete($name,$surname,$email){
            $Query = "SELECT * FROM USER WHERE name = ? AND surname = ? AND email = ?";
            $SQLQuery = $this->db->prepare($Query);
            if($SQLQuery){
                $SQLQuery->bind_param("sss",$name,$surname,$email);
                $didit = $SQLQuery->execute();
                if($didit){
                    $res = $SQLQuery->get_result();
                    return ($res->num_rows > 0) ? true : false;
                }else{$this->SQLERROR();}
            }else{$this->SQLERROR();}
        }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        public function Login($email, $password){

            $hashedPassword = $this->Hashing($password);

            if(!isset($email) || !isset($password)){
                $this->BadReq();
            }//input validation

            

            if(!$this->alreadyExists($email, $hashedPassword)){
                header("HTTP/1.1 409 OK");
                $data = ["status"=>"409", "message"=>"Incorrect details"];
                echo json_encode($data);
            }//needs to register
              else{
            $Query = 'SELECT * FROM USER WHERE email = ? AND password = ? ';
            $SQLQuery = $this->db->prepare($Query);
            if($SQLQuery){
                $SQLQuery->bind_param('ss', $email, $hashedPassword);
                $didit = $SQLQuery->execute();
                if($didit){
                    $res = $SQLQuery->get_result();
                    while($row = $res->fetch_assoc()){
                        $name = $row['Name'];
                        $surname = $row['Surname'];
                        $USER_ID = $row['USER_ID'];
                        $email = $row['email'];
                        $favourites = $row['Favourites'];

                        $_SESSION['USER_ID'] = $USER_ID;
                        $_SESSION['name'] = $name;
                        $_SESSION['surname'] = $surname;
                        $_SESSION['favourites'] = $favourites;
                        $data = ["status"=>"200","data"=>["email"=>$email, "name"=>$name,"surname"=>$surname,"USER_ID"=>$USER_ID]];
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
            //DOB must be yyyy-mm-dd
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
                header("HTTP/1.1 422");
                $data = ["status"=> "422","message"=> "User already exists"];
                echo json_encode($data);
            }else{
                $Query = "INSERT INTO USER (name, surname, email, password, DOB) VALUES (?,?,?,?,?)";
                $SQLQuery = $this->db->prepare($Query);
                if($SQLQuery){
                    $SQLQuery->bind_param("sssss",$name, $surname, $email, $hashedPassword, $DOB);
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
            //need to put admin validation

            if(!isset($name) || !isset($surname) || !isset($email)){
                $this->BadReq();
            }//BadRequest
            if($this->alreadyExistsDelete($name,$surname,$email)){
            $Query = "DELETE FROM USER WHERE name = ? AND surname = ? AND email = ?";
            $SQLQuery = $this->db->prepare($Query);
            if($SQLQuery){
                $SQLQuery->bind_param("sss",$name,$surname, $email);
                $didit = $SQLQuery->execute();
                if($didit){
                    $res = $SQLQuery->get_result();
                        $data = ["status"=> "200","data"=> "Successfully deleted " . $name . " " . $surname];
                        header("HTTP/1.1 200 OK");
                        echo json_encode($data);
                }else{$this->SQLEXECUTEEROR();}
            }else{$this->SQLERROR();}
        }//userExists
        else{
            $this->NoResFound();
        }
        }//Delete User

        public function UpdateUser($field, $value, $USER_ID){
            if((!isset($field) && !isset($value)) || !isset($USER_ID)){
                $this->BadReq();
            }

            $Query = "UPDATE USER SET ". $field . " = ? WHERE User_ID = ?";
            $SQLQuery = $this->db->prepare($Query);
            if($SQLQuery){
                $SQLQuery->bind_param("sd",$value,$USER_ID);
                $didit = $SQLQuery->execute();
                if($didit){
                    header("HTTP/1.1 200 OK");
                    $data = ["status"=> "200","data"=> "Updated ". $field . " to " . $value];
                    echo json_encode($data);
                }else{$this->SQLEXECUTEEROR();}
            }else{$this->SQLERROR();}
        }//update user
        ///////////////////////////////////////////////////////////////////////////////////


        public function getMedia(){
            $randNum = 2;//rand(25,75);
            $Query = "SELECT * FROM MEDIA";
            $SQLQuery = $this->db->prepare($Query);
            if($SQLQuery){
                //$SQLQuery->bind_param();
                $didit = $SQLQuery->execute();
                if($didit){
                    $res = $SQLQuery->get_result();
                    while($row = $res->fetch_assoc()){
                        $media[] = $row;
                    }
                    $data = ["status"=> "200","data"=> $media];
                    header("HTTP/1.1 200 OK");
                    echo json_encode($data);
                }else{$this->SQLEXECUTEEROR();}
            }else{$this->SQLERROR();}
        }//getAllMedia





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

        if(isset($input_data) && $input_data["type"] == "SearchUser"){
            $api->SearchUser($input_data['name'],$input_data['surname'],$input_data['email']);
        }

        if(isset($input_data) && $input_data["type"] == "DeleteUser"){
            $api->DeleteUser($input_data['name'],$input_data['surname'],$input_data['email']);
        }

        if(isset($input_data) && $input_data['type'] == 'UpdateUser'){
            $api->UpdateUser($input_data['field'],$input_data['value'],$input_data['USER_ID']);
        }

        //////////////////////////////////////////////////////////////////////////

        if(isset($input_data) && $input_data['type'] == 'GetMedia'){
            $api->getMedia();
        }

    }//different endpoints for POST
    

    if($_SERVER["REQUEST_METHOD"] != "POST"){
        $data = ["status"=>"405", "message"=> $_SERVER['REQUEST_METHOD']." not allowed :("];
        echo json_encode($data);
    }
?>