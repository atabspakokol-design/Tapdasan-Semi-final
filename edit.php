<?php
include 'config.php';

// Handle Update Task
if (isset($_POST['update'])) {
    $id = (int) $_POST['id'];
    $task_name = mysqli_real_escape_string($conn, $_POST['task_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $status = $_POST['status'];
    $due_date = $_POST['due_date'];

    mysqli_query($conn, "UPDATE tasks 
                         SET task_name = '$task_name', 
                             description = '$description', 
                             status = '$status', 
                             due_date = '$due_date' 
                         WHERE id = $id");
    header("Location: index.php");
    exit;
}

// Fetch the task to edit
$id = (int) $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM tasks WHERE id = $id");

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit;
}

$row = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Arial, sans-serif; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .glass {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 500px;
        }

        h2 {
            font-size: 28px;
            margin-bottom: 25px;
            text-align: center;
        }
        h2 span {
            background: linear-gradient(90deg, #00f5ff, #a855f7);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px 14px;
            margin: 8px 0;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            color: #fff;
            outline: none;
            transition: 0.3s;
            font-size: 14px;
        }
        input:focus, textarea:focus, select:focus {
            border-color: #00f5ff;
            box-shadow: 0 0 12px rgba(0, 245, 255, 0.3);
        }
        input::placeholder, textarea::placeholder { color: rgba(255,255,255,0.4); }
        input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1); }

        select option { background: #302b63; color: #fff; }

        label { font-size: 13px; color: rgba(255,255,255,0.6); }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            font-size: 15px;
        }

        .btn-save {
            background: linear-gradient(90deg, #00f5ff, #a855f7);
            color: #fff;
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(168, 85, 247, 0.5);
        }

        .btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255,255,255,0.8);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        a.back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }
        a.back:hover { color: #00f5ff; }
    </style>
</head>
<body>

    <div class="glass">
        <h2>✏️ <span>Edit Task</span></h2>

        <form method="POST">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">

            <label>Task Name:</label>
            <input type="text" name="task_name" value="<?= htmlspecialchars($row['task_name']) ?>" required>

            <label>Description:</label>
            <textarea name="description" rows="3"><?= htmlspecialchars($row['description']) ?></textarea>

            <label>Status:</label>
            <select name="status">
                <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>⏳ Pending</option>
                <option value="Completed" <?= $row['status'] == 'Completed' ? 'selected' : '' ?>>✅ Completed</option>
            </select>

            <label>Due Date:</label>
            <input type="date" name="due_date" value="<?= $row['due_date'] ?>" required>

            <div class="btn-group">
                <button type="submit" name="update" class="btn-save">💾 Save Changes</button>
                <a href="index.php" class="btn-cancel" style="display:flex;align-items:center;justify-content:center;text-decoration:none;">← Cancel</a>
            </div>
        </form>

        <a href="index.php" class="back">← Back to Task Manager</a>
    </div>

</body>
</html>