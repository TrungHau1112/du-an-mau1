<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php"); // Redirect if not an admin
    exit;
}

include "DBUtil.php";
$db = new DBUtil();

// Fetch the product details
if (!isset($_GET['id'])) {
    header("Location: admin.php"); // Redirect if no product ID is provided
    exit;
}

$product_id = $_GET['id'];
$product = $db->selectOne('SELECT * FROM products WHERE id = :id', ['id' => $product_id]);

// Fetch categories
$categories = $db->select('SELECT * FROM categories');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/"; // Directory to store uploaded files
        $target_file = $target_dir . basename($_FILES["image_file"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is an actual image or fake image
        $check = getimagesize($_FILES["image_file"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            echo "Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["image_file"]["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
                echo "The file ". htmlspecialchars(basename($_FILES["image_file"]["name"])). " has been uploaded.";
                $data['image'] = $target_file; // Use the uploaded file path
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    } else {
        $data['image'] = $product['image']; // Keep the old image path if no new file uploaded
    }

    $data['name'] = $_POST['name'];
    $data['description'] = $_POST['description'];
    $data['price'] = $_POST['price'];
    $data['category_id'] = $_POST['category_id'];

    $db->update('products', $data, 'id = :id', ['id' => $product_id]);
    header("Location: admin.php"); // Redirect after editing product
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            margin: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        table {
            width: 100%;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        input[type="text"], input[type="number"], textarea, select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus, input[type="number"]:focus, textarea:focus, select:focus {
            border-color: #4CAF50;
            outline: none;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #45a049;
        }
        img {
            max-width: 100px;
        }
        .image-preview {
            max-width: 100px;
            margin-top: 10px;
        }
    </style>
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('image_preview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</head>
<body>
<h1>Sửa Sản Phẩm</h1>
<form method="post" enctype="multipart/form-data">
    <table class="table table-bordered">
        <tr>
            <th>Trường</th>
            <th>Nhập</th>
        </tr>
        <tr>
            <td>Tên Sản Phẩm</td>
            <td><input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required></td>
        </tr>
        <tr>
            <td>Mô Tả</td>
            <td><textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea></td>
        </tr>
        <tr>
            <td>Giá</td>
            <td><input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required></td>
        </tr>
        <tr>
            <td>Danh Mục</td>
            <td>
                <select name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= ($category['id'] == $product['category_id']) ? 'selected' : '' ?>><?= $category['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Tải Lên Hình Ảnh</td>
            <td>
                <input type="file" name="image_file" accept="image/*" onchange="previewImage(event)">
                <img id="image_preview" class="image-preview" src="<?= htmlspecialchars($product['image']) ?>" alt="Image Preview">
            </td>
        </tr>
        <tr>
            <td></td>
            <td><button type="submit">Lưu Thay Đổi</button></td>
        </tr>
    </table>
</form>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>