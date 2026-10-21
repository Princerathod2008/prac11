
<?php
include 'db.php';

if (isset($_POST['add_student'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $course = $_POST['course'];
    $gender = $_POST['gender'];

    $stmt = $conn->prepare(
        "INSERT INTO students (name, email, course, gender)
         VALUES (?, ?, ?, ?)"
    );

    $stmt->bind_param("ssss", $name, $email, $course, $gender);

    if ($stmt->execute()) {
        header("Location: index.php?msg=added");
        exit();
    }

    die("Unable to add student.");
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    header("Location: index.php?msg=deleted");
    exit();
}

$search = trim($_GET['search'] ?? '');
$course_filter = $_GET['course_filter'] ?? '';

$sql = "SELECT * FROM students WHERE 1=1";
$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND (name LIKE ? OR email LIKE ?)";
    $keyword = "%$search%";
    $params[] = $keyword;
    $params[] = $keyword;
    $types .= "ss";
}

if ($course_filter !== '') {
    $sql .= " AND course = ?";
    $params[] = $course_filter;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

function safe($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Student Portal</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f7;
            margin: 0;
            color: #263449;
        }

        header {
            background: #263b63;
            color: white;
            padding: 25px 8%;
        }

        header h1 {
            margin: 0 0 8px;
        }

        header p {
            margin: 0;
            color: #dbe5fa;
        }

        main {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 18px;
        }

        .panel {
            background: white;
            padding: 25px;
            margin-bottom: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 14px #0000000d;
        }

        h2 {
            margin-top: 0;
            color: #263b63;
        }

        .fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        input, select {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #ccd5e1;
            border-radius: 6px;
            background: white;
        }

        button, .action {
            display: inline-block;
            padding: 10px 16px;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .primary {
            background: #3158a4;
            color: white;
            margin-top: 16px;
        }

        .search-btn {
            background: #263b63;
            color: white;
        }

        .reset-btn {
            background: #e7ecf3;
            color: #263449;
        }

        .delete {
            color: #b42318;
            font-weight: bold;
        }

        .edit {
            color: #2456a6;
            font-weight: bold;
        }

        .filter-form {
            display: grid;
            grid-template-columns: 2fr 1fr auto auto;
            gap: 10px;
            margin-bottom: 20px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            min-width: 700px;
        }

        th, td {
            padding: 13px 12px;
            border-bottom: 1px solid #e4e9f0;
            text-align: left;
        }

        th {
            background: #eaf0fa;
            color: #263b63;
        }

        tr:hover td {
            background: #f8faff;
        }

        .notice {
            background: #e4f6e9;
            color: #176536;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .empty {
            text-align: center;
            padding: 25px;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #667085;
        }

        @media (max-width: 650px) {
            .fields, .filter-form {
                grid-template-columns: 1fr;
            }

            header {
                padding: 22px;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>Campus Student Portal</h1>
    <p>Manage student information in one place</p>
</header>

<main>

    <?php if (isset($_GET['msg'])): ?>
        <div class="notice">
            <?php
            if ($_GET['msg'] === 'added') {
                echo "Student added successfully.";
            } elseif ($_GET['msg'] === 'deleted') {
                echo "Student record deleted.";
            } elseif ($_GET['msg'] === 'updated') {
                echo "Student details updated successfully.";
            }
            ?>
        </div>
    <?php endif; ?>

    <section class="panel">
        <h2>Register a Student</h2>

        <form action="index.php" method="POST">
            <div class="fields">
                <input type="text" name="name"
                       placeholder="Enter full name" required>

                <input type="email" name="email"
                       placeholder="Enter email address" required>

                <select name="course" required>
                    <option value="">Choose course</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Web Development">Web Development</option>
                    <option value="Data Science">Data Science</option>
                </select>

                <select name="gender" required>
                    <option value="">Choose gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>

            <button class="primary" type="submit" name="add_student">
                + Add Student
            </button>
        </form>
    </section>

    <section class="panel">
        <h2>Student Directory</h2>

        <form method="GET" action="index.php" class="filter-form">
            <input type="text" name="search"
                   placeholder="Search name or email"
                   value="<?= safe($search) ?>">

            <select name="course_filter">
                <option value="">All Courses</option>

                <option value="Computer Science"
                    <?= $course_filter === 'Computer Science' ? 'selected' : '' ?>>
                    Computer Science
                </option>

                <option value="Web Development"
                    <?= $course_filter === 'Web Development' ? 'selected' : '' ?>>
                    Web Development
                </option>

                <option value="Data Science"
                    <?= $course_filter === 'Data Science' ? 'selected' : '' ?>>
                    Data Science
                </option>
            </select>

            <button type="submit" class="search-btn">Search</button>

            <a href="index.php" class="action reset-btn">Reset</a>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Gender</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= safe($row['id']) ?></td>
                                <td><?= safe($row['name']) ?></td>
                                <td><?= safe($row['email']) ?></td>
                                <td><?= safe($row['course']) ?></td>
                                <td><?= safe($row['gender']) ?></td>

                                <td>
                                    <a class="edit"
                                       href="edit.php?id=<?= (int)$row['id'] ?>">
                                        Edit
                                    </a>

                                    |

                                    <a class="delete"
                                       href="index.php?delete=<?= (int)$row['id'] ?>"
                                       onclick="return confirm('Delete this student?')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty">
                                No student records available.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<footer>
    Campus Student Portal | Student Information Management
</footer>

</body>
</html>
```