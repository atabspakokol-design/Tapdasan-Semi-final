<?php
include 'config.php';

// Handle Add Task
if (isset($_POST['add'])) {
    $task_name = mysqli_real_escape_string($conn, $_POST['task_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $due_date = $_POST['due_date'];

    mysqli_query($conn, "INSERT INTO tasks (task_name, description, status, due_date)
                         VALUES ('$task_name', '$description', 'Pending', '$due_date')");
    header("Location: index.php");
    exit;
}

// Handle Delete Task
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM tasks WHERE id = $id");
    header("Location: index.php");
    exit;
}

// Handle Update Status
if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    mysqli_query($conn, "UPDATE tasks SET status = IF(status = 'Pending', 'Completed', 'Pending') WHERE id = $id");
    header("Location: index.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM tasks ORDER BY due_date ASC");

// Stats for sidebar
$total = mysqli_num_rows($result);
$pending = 0; $completed = 0;
mysqli_data_seek($result, 0);
while ($r = mysqli_fetch_assoc($result)) {
    if ($r['status'] == 'Completed') $completed++; else $pending++;
}
mysqli_data_seek($result, 0);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Arial, sans-serif; }

        body {
            --bg: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            --glass-bg: rgba(255, 255, 255, 0.07);
            --glass-border: rgba(255, 255, 255, 0.12);
            --input-bg: rgba(255, 255, 255, 0.08);
            --input-border: rgba(255, 255, 255, 0.15);
            --text: #fff;
            --text-dim: rgba(255,255,255,0.6);
            --text-faint: rgba(255,255,255,0.4);
            --hover-bg: rgba(255, 255, 255, 0.05);
            --row-border: rgba(255,255,255,0.08);

            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            display: flex;
            overflow-x: hidden;
            transition: background 0.4s, color 0.4s;
        }

        /* ===== LIGHT MODE ===== */
        body.light {
            --bg: linear-gradient(135deg, #e0eafc, #cfdef3);
            --glass-bg: rgba(255, 255, 255, 0.55);
            --glass-border: rgba(255, 255, 255, 0.8);
            --input-bg: rgba(255, 255, 255, 0.7);
            --input-border: rgba(0, 0, 0, 0.1);
            --text: #1e1b4b;
            --text-dim: rgba(30, 27, 75, 0.6);
            --text-faint: rgba(30, 27, 75, 0.4);
            --hover-bg: rgba(255, 255, 255, 0.4);
            --row-border: rgba(0, 0, 0, 0.06);
        }

        /* ===== SIDEBAR (GLASS) ===== */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid var(--glass-border);
            padding: 30px 20px;
            position: fixed;
            left: 0; top: 0; bottom: 0;
            display: flex;
            flex-direction: column;
            transition: background 0.4s, border 0.4s;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo span {
            background: linear-gradient(90deg, #00c9db, #9333ea);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        /* ===== DARK/LIGHT TOGGLE ===== */
        .theme-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            padding: 12px 16px;
            margin-bottom: 20px;
            cursor: pointer;
            user-select: none;
            transition: 0.3s;
        }
        .theme-toggle:hover { box-shadow: 0 0 15px rgba(0, 245, 255, 0.2); }
        .theme-toggle .label { font-size: 14px; color: var(--text-dim); }

        .switch {
            width: 46px;
            height: 24px;
            background: rgba(255,255,255,0.15);
            border-radius: 20px;
            position: relative;
            transition: 0.3s;
        }
        .switch::after {
            content: '';
            position: absolute;
            width: 18px; height: 18px;
            background: #fff;
            border-radius: 50%;
            top: 3px; left: 3px;
            transition: 0.3s;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }
        body.light .switch { background: rgba(0,0,0,0.15); }
        body.light .switch::after { left: 25px; background: #302b63; }
        body:not(.light) .switch::after { content: '🌙'; font-size: 12px; display: flex; align-items: center; justify-content: center; }
        body.light .switch::after { content: '☀️'; font-size: 12px; display: flex; align-items: center; justify-content: center; }

        .nav-item {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 8px;
            color: var(--text-dim);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: 0.3s;
        }
        .nav-item:hover, .nav-item.active {
            background: var(--input-bg);
            color: var(--text);
            box-shadow: 0 0 15px rgba(0, 245, 255, 0.2);
        }

        .stats { margin-top: auto; }
        .stat-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 12px;
            transition: 0.4s;
        }
        .stat-card h4 { font-size: 12px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; }
        .stat-card p { font-size: 28px; font-weight: bold; margin-top: 4px; }
        .stat-total p { color: #00c9db; }
        .stat-pending p { color: #d97706; }
        .stat-done p { color: #059669; }

        /* ===== MAIN CONTENT ===== */
        .main {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
        }

        h2 {
            font-size: 32px;
            margin-bottom: 25px;
        }
        h2 span {
            background: linear-gradient(90deg, #00c9db, #9333ea);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        /* ===== GLASS ===== */
        .glass {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            transition: 0.4s;
        }

        .add-form { padding: 25px; margin-bottom: 35px; }
        .add-form h3 { margin-bottom: 15px; color: var(--text); }

        input, textarea {
            width: 100%;
            padding: 12px 14px;
            margin: 8px 0;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 10px;
            color: var(--text);
            outline: none;
            transition: 0.3s;
        }
        input:focus, textarea:focus {
            border-color: #00c9db;
            box-shadow: 0 0 12px rgba(0, 201, 219, 0.3);
        }
        input::placeholder, textarea::placeholder { color: var(--text-faint); }
        input[type="date"] { color-scheme: dark; }
        body.light input[type="date"] { color-scheme: light; }
        label { font-size: 13px; color: var(--text-dim); }

        button {
            margin-top: 10px;
            padding: 12px 28px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(90deg, #00c9db, #9333ea);
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(147, 51, 234, 0.4);
        }

        h3.section-title { margin: 10px 0 15px; color: var(--text); }

        /* ===== GLASS TABLE ===== */
        table { width: 100%; border-collapse: collapse; overflow: hidden; }
        th, td { padding: 14px 16px; text-align: left; border-bottom: 1px solid var(--row-border); }
        th {
            background: var(--input-bg);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-dim);
        }
        tr:hover td { background: var(--hover-bg); }

        .done { color: #059669; font-weight: bold; }
        .pending { color: #d97706; font-weight: bold; }

        a { text-decoration: none; margin-right: 8px; transition: 0.2s; }
        a:hover { opacity: 0.7; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="logo">⚡ <span>TaskFlow</span></div>

        <!-- DARK/LIGHT TOGGLE -->
        <div class="theme-toggle" onclick="toggleTheme()">
            <span class="label" id="themeLabel">🌙 Dark Mode</span>
            <div class="switch"></div>
        </div>

        <a href="index.php" class="nav-item active">🏠 Dashboard</a>
        <a href="index.php" class="nav-item">📝 All Tasks</a>

        <div class="stats">
            <div class="stat-card stat-total"><h4>Total Tasks</h4><p><?= $total ?></p></div>
            <div class="stat-card stat-pending"><h4>Pending</h4><p><?= $pending ?></p></div>
            <div class="stat-card stat-done"><h4>Completed</h4><p><?= $completed ?></p></div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main">
        <h2>🚀 <span>Task Manager</span></h2>

        <!-- Add Task Form -->
        <form method="POST" class="add-form glass">
            <h3>✨ Add New Task</h3>
            <input type="text" name="task_name" placeholder="Task Name" required>
            <textarea name="description" placeholder="Description" rows="2"></textarea>
            <label>📅 Due Date:</label>
            <input type="date" name="due_date" required>
            <button type="submit" name="add">＋ Add Task</button>
        </form>

        <!-- View Tasks -->
        <h3 class="section-title">📋 All Tasks</h3>
        <div class="glass" style="overflow: hidden;">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Task Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['task_name']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td class="<?= $row['status'] == 'Completed' ? 'done' : 'pending' ?>">
                        <?= $row['status'] ?>
                    </td>
                    <td><?= $row['due_date'] ?></td>
                    <td>
                        <a href="edit.php?id=<?= $row['id'] ?>">✏️ Edit</a>
                        <a href="index.php?toggle=<?= $row['id'] ?>">
                            <?= $row['status'] == 'Pending' ? '✅' : '↩️' ?>
                        </a>
                        <a href="index.php?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this task?')">🗑️</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <script>
        // Load saved theme
        if (localStorage.getItem('theme') === 'light') {
            document.body.classList.add('light');
        }
        updateLabel();

        function toggleTheme() {
            document.body.classList.toggle('light');
            const isLight = document.body.classList.contains('light');
            localStorage.setItem('theme', isLight ? 'light' : 'dark');
            updateLabel();
        }

        function updateLabel() {
            const isLight = document.body.classList.contains('light');
            document.getElementById('themeLabel').textContent = isLight ? '☀️ Light Mode' : '🌙 Dark Mode';
        }
    </script>

</body>
</html>