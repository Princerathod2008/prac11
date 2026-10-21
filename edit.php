<?php
include 'db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    die("Invalid student ID.");
}

$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Student record not found.");
}

$student = $result->fetch_assoc();

function safe($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$error = "";

if (isset($_POST['update_student'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $course = $_POST['course'];
    $gender = $_POST['gender'];

    $allowed_courses = [
        "Computer Science",
        "Web Development",
        "Data Science"
    ];

    $allowed_genders = ["Male", "Female"];

    if (
        $name === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !in_array($course, $allowed_courses, true) ||
        !in_array($gender, $allowed_genders, true)
    ) {
        $error = "Please enter valid student details.";
        $student['name'] = $name;
        $student['email'] = $email;
        $student['course'] = $course;
        $student['gender'] = $gender;
    } else {
        $stmt = $conn->prepare(
            "UPDATE students
             SET name = ?, email = ?, course = ?, gender = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "ssssi",
            $name,
            $email,
            $course,
            $gender,
            $id
        );

        if ($stmt->execute()) {
            header("Location: index.php?msg=updated");
            exit();
        }

        $error = "Unable to update student details.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Student</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f7;
            margin: 0;
            padding: 25px 15px;
            color: #263449;
        }

        .edit-card {
            max-width: 560px;
            margin: 35px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 18px #00000010;
        }

        h1 {
            color: #263b63;
            margin-top: 0;
        }

        p {
            color: #667085;
        }

        label {
            display: block;
            font-weight: bold;
            margin: 17px 0 7px;
        }

        input, select {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #ccd5e1;
            border-radius: 6px;
            background: white;
        }

        button, .cancel {
            display: inline-block;
            margin-top: 22px;
            padding: 12px 18px;
            border: 0;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        button {
            background: #3158a4;
            color: white;
        }

        .cancel {
            background: #e7ecf3;
            color: #263449;
            margin-left: 8px;
        }

        .error {
            background: #fff0f0;
            color: #a21d1d;
            padding: 12px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

<div class="edit-card">
    <h1>Update Student</h1>
    <p>Change the required details and save your changes.</p>

    <?php if ($error !== ""): ?>
        <div class="error"><?= safe($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="edit.php?id=<?= (int)$id ?>">

        <label for="name">Student Name</label>
        <input id="name" type="text" name="name"
               value="<?= safe($student['name']) ?>" required>

        <label for="email">Email Address</label>
        <input id="email" type="email" name="email"
               value="<?= safe($student['email']) ?>" required>

        <label for="course">Course</label>
        <select id="course" name="course" required>
            <option value="Computer Science"
                <?= $student['course'] === 'Computer Science' ? 'selected' : '' ?>>
                Computer Science
            </option>

            <option value="Web Development"
                <?= $student['course'] === 'Web Development' ? 'selected' : '' ?>>
                Web Development
            </option>

            <option value="Data Science"
                <?= $student['course'] === 'Data Science' ? 'selected' : '' ?>>
                Data Science
            </option>
        </select>

        <label for="gender">Gender</label>
        <select id="gender" name="gender" required>
            <option value="Male"
                <?= $student['gender'] === 'Male' ? 'selected' : '' ?>>
                Male
            </option>

            <option value="Female"
                <?= $student['gender'] === 'Female' ? 'selected' : '' ?>>
                Female
            </option>
        </select>

        <button type="submit" name="update_student">
            Save Changes
        </button>

        <a href="index.php" class="cancel">Cancel</a>

    </form>
</div>

</body>
</html>