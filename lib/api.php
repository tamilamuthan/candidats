<?php
        require_once(dirname(__DIR__)."/config.php");
 	require_once("Rest.inc.php");
	
	class API extends REST {
	
		public $data = "";
                public $input = array();
		
		const DB_SERVER = DATABASE_HOST;
		const DB_USER = DATABASE_USER;
		const DB_PASSWORD = DATABASE_PASS;
		const DB = DATABASE_NAME;

		private $db = NULL;
		private $mysqli = NULL;
		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect();					// Initiate Database connection
		}
		
		/*
		 *  Connect to Database
		*/
		private function dbConnect(){
			$this->mysqli = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
		}
		
		/*
		 * Dynmically call the method based on the query string
		 */
		public function processApi(){
                    if($this->get_request_method() != "POST" && $this->get_request_method() != "DELETE")
                    {
                        $this->response('',406);
                        return false;
                    }
                    $this->input=json_decode(file_get_contents("php://input"),true);
                    return true;
		}
                
                public function getInput()
                {
                    return $this->input;
                }
                
                public function &getInstance()
                {
                    static $api = null;
                    if(is_null($api))
                    {
                        $api = new API;
                    }
                    return $api;
                }
				
		private function login(){
			/*if($this->get_request_method() != "POST"){
				$this->response('',406);
			}*/
                        $customer = json_decode(file_get_contents("php://input"),true);
			$email = $customer['email'];		
			$password = $customer['pwd'];
			if(!empty($email) and !empty($password)){
				if(filter_var($email, FILTER_VALIDATE_EMAIL)){
					$query="SELECT uid, name, email FROM users WHERE email = '$email' AND password = '".$password."' LIMIT 1";
					$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

					if($r->num_rows > 0) {
						$result = $r->fetch_assoc();	
						// If success everythig is good send header as "OK" and user details
						$this->response($this->json($result), 200);
					}
					$this->response('', 204);	// If no records "No Content" status
				}
			}
			
			$error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
			$this->response($this->json($error), 400);
		}
		
		private function roles(){
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$query="SELECT distinct c.customerNumber, c.customerName, c.email, c.address, c.city, c.state, c.postalCode, c.country FROM angularcode_customers c order by c.customerNumber desc";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}
                
                private function updateRole()
                {
                    $objDB=DatabaseConnection::getInstance();
                    $customer = $api->getInput();
                    $id = (int)$customer['id'];
                    $column_names = array('customerName', 'email', 'city', 'address', 'country');
                    $keys = array_keys($customer['customer']);
                    $columns = '';
                    $values = '';
                    foreach($column_names as $desired_key){ // Check the customer received. If key does not exist, insert blank into the array.
                       if(!in_array($desired_key, $keys)) {
                                    $$desired_key = '';
                            }else{
                                    $$desired_key = $customer['customer'][$desired_key];
                            }
                            $columns = $columns.$desired_key."='".$$desired_key."',";
                    }
                    $query = "UPDATE angularcode_customers SET ".trim($columns,',')." WHERE customerNumber=$id";
                    if(!empty($customer)){
                            $r = $objDB->query($query);
                            $success = array('status' => "Success", "msg" => "Customer ".$id." Updated Successfully.", "data" => $customer);
                            $this->response($this->json($success),200);
                    }else
                            $this->response('',204);	// "No Content" status
                }
                
		private function role(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$id = (int)$this->_request['id'];
			if($id > 0){	
				$query="SELECT distinct c.customerNumber, c.customerName, c.email, c.address, c.city, c.state, c.postalCode, c.country FROM angularcode_customers c where c.customerNumber=$id";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				if($r->num_rows > 0) {
					$result = $r->fetch_assoc();	
					$this->response($this->json($result), 200); // send user details
				}
			}
			$this->response('',204);	// If no records "No Content" status
		}
		
		
		
		
                
                

                /**
                 * navigate from leaf node to root node
                 * @param type $parentid
                 * @param type $arrChild
                 * @param type $callback
                 * @param type $isOnlyLeaf
                 */
                private function recursiveNavigation($parentid,$arrChild,$callback, $isOnlyLeaf=false)
                {
                    $arrChildren=array();
                    foreach($arrChild as $ind=>$arrSubData)
                    {
                        Logger::getLogger("AuieoATS")->info("At api.php:recursiveNavigation. Inside foreach");
                        if($isOnlyLeaf)
                        {
                            Logger::getLogger("AuieoATS")->info("At api.php:recursiveNavigation:foreach:If");
                            if(!empty($arrSubData["nodes"]))
                            {
                                Logger::getLogger("AuieoATS")->info("At api.php:recursiveNavigation:foreach:If:If");
                                $this->recursiveNavigation($arrSubData["id"],$arrSubData["nodes"],$callback,$isOnlyLeaf);
                            }
                            else
                            {
                                Logger::getLogger("AuieoATS")->info("At api.php:recursiveNavigation:foreach:If:Else");
                                $callback($arrSubData,$arrChild);
                            }
                        }
                        else
                        {
                            Logger::getLogger("AuieoATS")->info("At api.php:recursiveNavigation:foreach:else");
                            if(!empty($arrSubData["nodes"]))
                            {
                                Logger::getLogger("AuieoATS")->info("At api.php:recursiveNavigation:foreach:else:if");
                                $this->recursiveNavigation($arrSubData["id"],$arrSubData["nodes"],$callback,$isOnlyLeaf);
                            }
                            $callback($arrSubData,$parentid);
                        }
                    }
                }
                
                private function getSyncTree($arrData,$id)
                {
                    if($arrData)
                    foreach($arrData as $ind=>$arrSubData)
                    {
                        if($arrSubData["id"]==$id)
                        {
                            return $arrSubData;
                        }
                        else
                        {
                            return $this->getSyncTree($arrSubData,$id);
                        }
                    }
                    return false;
                }
                
                
		
		/*
		 *	Encode array into JSON
		*/
		public function json($data){
			if(is_array($data)){
				return json_encode($data);
			}
		}
	}
	
?>