<?php
require_once 'vendor/autoload.php';
include_once('DBUtil.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $otp = $_POST['otp'];
    $dbHelper = new DBUtil();
    $errors = [];

    // Kiểm tra OTP
    $storedOtp = $dbHelper->select("SELECT otp FROM users WHERE email = ?", [$email]);
    if (empty($storedOtp) || $storedOtp[0]['otp'] !== $otp) {
        $errors['otp'] = "OTP không hợp lệ.";
    }

    // Kiểm tra mật khẩu
    if (strlen($password) < 6) {
        $errors['password'] = "Mật khẩu phải có ít nhất 6 ký tự.";
    }

    if (empty($errors)) {
        // Cập nhật mật khẩu mới không mã hóa
        $dbHelper->update('users', ['password' => $password], "email = :email", [':email' => $email]);
        echo "Đặt lại mật khẩu thành công. <a href='login.php'>Đăng nhập</a>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <title>Đặt lại mật khẩu</title>
</head>

<style>
    .container {
        height: 200vh;
    }

    .form-group {
        width: 600px;
        height: auto;
        margin: 0 auto;
        translate: 0 80px;
        background-image: linear-gradient(to top right, blue, brown);
        border-radius: 50px;
        padding: 50px;
    }

    h1 {
        color: white;
        font-weight: 500;
        font-family: "Oswald", sans-serif;
        text-align: center;
        font-size: 30px;
        margin-bottom: 20px;
    }

    .user{
        /* background-color: #711D6D; */
        box-shadow: 1px 3px 20px 1px;
        border-radius: 30px;
        padding: 50px;
        width: 300px;
        margin-left: auto;
        margin-right: auto;
        display: block;
        margin-bottom: 0;
    }

    .btn-primary {
        margin-left: auto;
        margin-right: auto;
        display: block;
        font-family: "Oswald", sans-serif;
        width: 200px;
        font-size: 20px;
        margin-top: 30px;
    }
</style>

<body>
    <div class="container">
        <div class="form-group">
            <form action="" method="post" class="user">
                <h1>Đặt lại mật khẩu</h1>
                <input type="email" readonly name="email" class="form-control form-control-user mb-3" value="<?php echo isset($_GET['email']) ? $_GET['email'] : ''; ?>" placeholder="Email" />
                <input type="text" name="otp" class="form-control form-control-user mb-3" placeholder="Nhập mã OTP 6 số" />
                <?php if (isset($errors['otp'])) echo "<div class='text-danger'>{$errors['otp']}</div>"; ?>
                <input type="password" name="password" class="form-control form-control-user mb-3" placeholder="Mật khẩu mới" />
                <?php if (isset($errors['password'])) echo "<div class='text-danger'>{$errors['password']}</div>"; ?>
                <button type="submit" name="action" value="reset" class="btn btn-primary btn-user btn-block">Đặt lại mật khẩu</button>
            </form>
        </div>
    </div>
</body>
</html>
