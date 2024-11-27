<?php
ob_start();
include 'adu.php';  // Nếu cần, bao gồm file chứa các chức năng cần thiết
session_start();
include_once 'DBUtil.php';  // Bao gồm class DBUtil để xử lý CSDL

$dbHelper = new DBUtil();
$categories = $dbHelper->select('SELECT * FROM categories');  // Lấy tất cả danh mục từ CSDL

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        // Thêm danh mục
        if (empty($_POST['category_name'])) {
            echo "<script>
                    alert('Vui lòng nhập tên danh mục.');
                  </script>";
        } else {
            $categoryName = $_POST['category_name'];

            // Kiểm tra xem danh mục đã tồn tại chưa
            $existingCategory = $dbHelper->select('SELECT * FROM categories WHERE name = :name', ['name' => $categoryName]);

            if (count($existingCategory) > 0) {
                echo "<script>
                        alert('Danh mục đã tồn tại!');
                      </script>";
            } else {
                $data = ['name' => $categoryName];
                if ($dbHelper->insert('categories', $data)) {
                    echo "<script>
                            alert('Thêm danh mục thành công!');
                            window.location.href = 'categories.php';
                          </script>";
                    exit;
                } else {
                    echo "<script>
                            alert('Không thể thêm danh mục. Vui lòng thử lại.');
                          </script>";
                }
            }
        }
    } elseif (isset($_POST['edit_category'])) {
        // Cập nhật danh mục
        if (empty($_POST['category_name'])) {
            echo "<script>
                    alert('Vui lòng nhập tên danh mục.');
                  </script>";
        } else {
            $id = $_POST['category_id'];
            $categoryName = $_POST['category_name'];

            // Kiểm tra xem tên danh mục mới đã tồn tại chưa, trừ danh mục hiện tại
            $existingCategory = $dbHelper->select('SELECT * FROM categories WHERE name = :name AND id != :id', [
                'name' => $categoryName,
                'id' => $id
            ]);

            if (count($existingCategory) > 0) {
                echo "<script>
                        alert('Danh mục với tên này đã tồn tại!');
                      </script>";
            } else {
                $data = ['name' => $categoryName];
                if ($dbHelper->update('categories', $data, 'id = :id', ['id' => $id])) {
                    echo "<script>
                            alert('Cập nhật danh mục thành công!');
                            window.location.href = 'categories.php';
                          </script>";
                    exit;
                } else {
                    echo "<script>
                            alert('Không thể cập nhật danh mục. Vui lòng thử lại.');
                          </script>";
                }
            }
        }
    } elseif (isset($_POST['delete_category'])) {
        // Xóa danh mục
        $id = $_POST['category_id'];
        if ($dbHelper->delete('categories', 'id = :id', ['id' => $id])) {
            echo "<script>
                    alert('Xóa danh mục thành công!');
                    window.location.href = 'categories.php';
                  </script>";
            exit;
        } else {
            echo "<script>
                    alert('Không thể xóa danh mục. Vui lòng thử lại.');
                  </script>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản Trị Viên</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Cải thiện kiểu bảng */
        .table th, .table td {
            transition: background-color 0.3s ease, transform 0.3s ease;
            padding: 15px;
            text-align: center;
        }

        .table tr:hover {
            background-color: #e8f1f5;
            transform: scale(1.02);
        }

        .table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        .table td {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }

        .table tr:nth-child(even) td {
            background-color: #f2f2f2;
        }

        /* Hiệu ứng cho nút */
        .btn {
            transition: background-color 0.3s ease, transform 0.2s ease;
            border-radius: 5px;
            padding: 10px 20px;
        }

        .btn:hover {
            background-color: #0056b3;
            transform: scale(1.1);
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Hiệu ứng cho modal */
        .modal-content {
            transition: transform 0.3s ease;
        }

        .modal.show .modal-content {
            transform: scale(1.05);
        }

        /* Tăng cường hiệu ứng focus trên các input */
        input[type="text"], textarea {
            transition: border 0.3s ease, box-shadow 0.3s ease;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="text"]:focus, textarea:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        /* Cải thiện modal khi hover */
        .modal-header, .modal-footer {
            background-color: #007bff;
            color: white;
        }

        .modal-header h5 {
            font-size: 1.2rem;
        }

        /* Hiệu ứng khi hover lên các nút trong modal */
        .modal-footer button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        /* Thêm hiệu ứng cho các tiêu đề */
        h3 {
            font-size: 24px;
            color: #333;
            text-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Hiệu ứng transition cho các button và form */
        button, .form-group input {
            transition: background-color 0.3s, color 0.3s;
        }

        /* Hiệu ứng khi hover lên button thêm danh mục */
        button[type="submit"]:hover {
            background-color: #28a745;
            color: white;
            transform: scale(1.05);
        }

        /* Tạo hiệu ứng khi hover lên dòng trong bảng */
        .table tr:hover td {
            transform: scale(1.03);
            box-shadow: 0px 3px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="text-right mb-3">
            <a href="logout.php" class="btn btn-danger">Đăng Xuất</a>
        </div>

        <h3>Thêm Danh Mục</h3>
        <form method="post" class="mb-4" onsubmit="return validateCategoryForm()">
            <div class="form-group">
                <input type="text" name="category_name" class="form-control" placeholder="Tên danh mục" required>
            </div>
            <button type="submit" name="add_category" class="btn btn-primary">Thêm Danh Mục</button>
        </form>

        <h3>Danh Sách Danh Mục</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Tên Danh Mục</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= htmlspecialchars($category['name']) ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?= $category['id'] ?>">Sửa</button>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                            <button type="submit" name="delete_category" class="btn btn-danger btn-sm">Xóa</button>
                        </form>
                    </td>
                </tr>

                <!-- Modal sửa danh mục -->
                <div class="modal fade" id="editModal<?= $category['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $category['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel<?= $category['id'] ?>">Sửa Danh Mục</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form method="post">
                                <div class="modal-body">
                                    <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                    <div class="form-group">
                                        <input type="text" name="category_name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                    <button type="submit" name="edit_category" class="btn btn-primary">Cập Nhật</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function validateCategoryForm() {
            const categoryName = document.querySelector('input[name="category_name"]');
            if (categoryName.value.trim() === "") {
                alert("Vui lòng nhập tên danh mục.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
