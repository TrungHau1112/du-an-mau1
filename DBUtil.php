<?php
include "Database.php";

define("HOST", "localhost");
define("DB_NAME", "du-an-mau1");
define("USERNAME", "root");
define("PASSWORD", "");

class DBUtil
{
    public $conn = null;

    function __construct()
    {
        try {
            $this->conn = new PDO("mysql:host=" . HOST . ";dbname=" . DB_NAME, USERNAME, PASSWORD);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
        }
    }

    // Phương thức kiểm tra email
    public function checkIfEmailExists($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function savePasswordResetToken($email, $token, $expire) {
        $sql = "INSERT INTO password_resets (email, token, expire) VALUES (:email, :token, :expire)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":expire", $expire);
        return $stmt->execute();
    }
    // Phương thức đăng ký người dùng mới
    public function registerUser($name, $email, $password, $role = 'user') {
        try {
            // Cập nhật câu lệnh SQL để thêm trường 'role'
            $query = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
            $stmt = $this->conn->prepare($query);
    
            // Liên kết các tham số với giá trị
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password); // Lưu mật khẩu bình thường
            $stmt->bindParam(':role', $role); // Liên kết tham số 'role'
    
            // Thực thi truy vấn
            if ($stmt->execute()) {
                return true; // Thành công
            } else {
                return false; // Lỗi khi thực thi
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    public function loginUser($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email AND password = :password LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC); // Trả về thông tin người dùng nếu đăng nhập thành công, nếu không trả về null
    }
    

    public function select($sql, $params = [])
    {
        if ($this->conn == null) {
            die("Không thể kết nối cơ sở dữ liệu");
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetchAll();
    }

    public function insert($table, $data)
    {
        if ($this->conn == null) {
            die("Không thể kết nối cơ sở dữ liệu");
        }
        $keys = array_keys($data);
        $fields = implode(", ", $keys);
        $placeholders = ":" . implode(", :", $keys);
        $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    public function update($table, $data, $condition, $conditionParams = [])
    {
        if ($this->conn == null) {
            die("Không thể kết nối cơ sở dữ liệu");
        }

        $updateFields = [];
        foreach ($data as $key => $value) {
            $updateFields[] = "$key = :$key";
        }
        $updateFields = implode(", ", $updateFields);
        $sql = "UPDATE $table SET $updateFields WHERE $condition";
        $stmt = $this->conn->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        // Bind condition parameters if any
        foreach ($conditionParams as $key => $value) {
            $paramKey = strpos($key, ':') === 0 ? $key : ":$key";
            $stmt->bindValue($paramKey, $value);
        }

        $stmt->execute();
        return $stmt->rowCount();
    }

    public function delete($table, $condition, $conditionParams = [])
    {
        if ($this->conn == null) {
            die("Không thể kết nối cơ sở dữ liệu");
        }
        $sql = "DELETE FROM $table WHERE $condition";
        $stmt = $this->conn->prepare($sql);

        foreach ($conditionParams as $key => $value) {
            $paramKey = strpos($key, ':') === 0 ? $key : ":$key";
            $stmt->bindValue($paramKey, $value);
        }

        $stmt->execute();
        return $stmt->rowCount();
    }
}

