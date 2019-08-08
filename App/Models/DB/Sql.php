<?php 

namespace Model\DB;
use PDO;

class Sql {

    private $db_driver;
    private $db_host;
    private $db_port;
    private $db_name;
    private $db_user_name;
    private $db_user_password;
    private $db_options;

    private $conn;

	public function __construct()
	{
        $this->db_driver = getenv('DB_CONNECTION');
        $this->db_host = getenv('DB_HOST');
        $this->db_port = getenv('DB_PORT');
        $this->db_name = getenv('DB_DATABASE');
        $this->db_user_name = getenv('DB_USERNAME');
        $this->db_user_password = getenv('DB_PASSWORD');
        $this->db_options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
        );

        try {
            $this->conn = new PDO(
                $this->db_driver.":dbname=".$this->db_name.";host=".$this->db_host.';port='.$this->db_port,
                $this->db_user_name,
                $this->db_user_password
            );
        } catch (\PDOException $e) {
            var_dump($e->getMessage());
            die;
        }

	}

	private function setParams($statement, $parameters = array())
	{

		foreach ($parameters as $key => $value) {
			
			$this->bindParam($statement, $key, $value);

		}

	}

	private function bindParam($statement, $key, $value)
	{

		$statement->bindParam($key, $value);

	}

	public function query($rawQuery, $params = array())
	{

		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

	}

	public function select($rawQuery, $params = array()):array
	{
                
		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_ASSOC);

	}

}