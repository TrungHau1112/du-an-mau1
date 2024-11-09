<?php
require_once 'vendor/autoload.php';
require_once 'DBUtil.php'; // Đảm bảo bạn đã có file này để kiểm tra đăng nhập

// Khởi tạo DBUtil và kết nối cơ sở dữ liệu
$clientID = '228946860098-a5qk7rsvd9osmk78r8s2krfqkv32aspv.apps.googleusercontent.com';
$clientSecret = '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';
$redirectUri = 'http://localhost/du-an-mau-1/login.php';

// create Client Request to access Google API
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

$dbUtil = new DBUtil();
$conn = $dbUtil->conn;

// authenticate code from Google OAuth Flow
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        echo 'Error: ' . $token['error'];
        exit;
    }

    if (isset($token['access_token'])) {
        $client->setAccessToken($token['access_token']);
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        $email = $google_account_info->email;
        $name = $google_account_info->name;

        // Sử dụng phương thức kiểm tra email trong DBUtil
        if ($dbUtil->checkIfEmailExists($email)) {
            // Nếu người dùng đã có tài khoản, chuyển đến trang chính
            session_start();
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $name;
            header("Location: index.php");  // Chuyển hướng người dùng tới trang index.php
            exit;
        } else {
            $result = $dbUtil->registerUser($name, $email, ""); // Bỏ qua mật khẩu khi dùng Google đăng ký
            echo $result ? "New record created successfully." : "Error registering user.";
        }
    } else {
        echo 'Không thể lấy access token';
        exit;
    }
} else {
    $login_url = $client->createAuthUrl();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Kiểm tra thông tin đăng nhập trong cơ sở dữ liệu
    $user = $dbUtil->loginUser($email, $password);

    if ($user) {
        // Nếu đăng nhập thành công, chuyển hướng người dùng đến trang chính
        session_start();
        $_SESSION['user_id'] = $user['id'];  // Giả sử bạn lưu id người dùng trong cơ sở dữ liệu
        $_SESSION['user_name'] = $user['name'];
        header("Location: index.php");  // Chuyển hướng đến trang chính sau khi đăng nhập
        exit;
    } else {
        $error = "Sai email hoặc mật khẩu!";
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Đăng nhập</h2>
        
        <!-- Hiển thị thông báo lỗi nếu có -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
            </form>
        </div>

        <hr>
        <div class="text-center">
            <a href="<?php echo htmlspecialchars($login_url); ?>" class="btn btn-danger">Đăng nhập Google</a>
        </div>
        <hr>
        <div class="text-center">
            <a href="register.php" class="btn btn-link">Chưa có tài khoản? Đăng ký</a>
        </div>
        
        <div class="text-center">
            <a href="forgot_password.php" class="btn btn-link">Quên mật khẩu?</a>
        </div>
    </div>
</body>
</html>
