# ⚡ TaskFlow — Task Manager

A modern **Task Manager web application** featuring a futuristic **glassmorphism UI**, sidebar dashboard with live stats, and **Dark/Light Mode toggle**. Available in two versions:

- 🐘 **Classic PHP + MySQL** (procedural, ready to run on XAMPP)
- 🚀 **Laravel + MySQL** (MVC version)

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![UI](https://img.shields.io/badge/UI-Glassmorphism-00f5ff)

---

## ✨ Features

- 📝 **CRUD Operations** — Create, Read, Update, and Delete tasks
- 🎨 **Futuristic Glassmorphism Design** — blur effects, neon accents, gradient backgrounds
- 🖥️ **Sidebar Dashboard** — Total / Pending / Completed task statistics (live from database)
- 🌙 **Dark / Light Mode Toggle** — with `localStorage` persistence (remembers your choice)
- ✅ **One-Click Status Toggle** — switch tasks between Pending and Completed instantly
- 📅 **Due Date Tracking** — tasks sorted by due date automatically
- 📱 **Responsive Design** — works on desktop and mobile

---

## 🗂️ Project Structure

### Classic PHP Version
```
taskflow/
├── index.php      # Main dashboard (tasks table, add form, sidebar, stats)
├── edit.php       # Edit task page (glassmorphism design)
├── config.php     # Database connection
└── README.md
```

### Laravel Version (structure overview)
```
taskflow-laravel/
├── app/
│   ├── Http/Controllers/TaskController.php
│   └── Models/Task.php
├── database/migrations/xxxx_create_tasks_table.php
├── resources/views/
│   ├── layouts/app.blade.php
│   ├── tasks/index.blade.php
│   └── tasks/edit.blade.php
└── routes/web.php
```

---

## 🛠️ Requirements

### Classic PHP Version
- **PHP** >= 7.4 (tested on PHP 8.x)
- **MySQL** / MariaDB
- **XAMPP** / WAMP / Laragon (for local development)

### Laravel Version
- **PHP** >= 8.1 (with extensions: `bcmath`, `ctype`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`)
- **Composer**
- **Node.js** & **NPM** (for Vite / asset building)
- **MySQL** / MariaDB

---

## 🚀 Setup Option 1 — Classic PHP + MySQL (XAMPP)

### 1. Clone or copy the project

Place the project folder inside your web server directory:

```
C:\xampp\htdocs\taskflow
```

### 2. Create the database

Open **phpMyAdmin** (http://localhost/phpmyadmin) and run:

```sql
CREATE DATABASE taskflow;

CREATE TABLE taskflow.tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_name VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('Pending', 'Completed') DEFAULT 'Pending',
    due_date DATE NOT NULL
);
```

### 3. Configure the database connection

Edit `config.php`:

```php
<?php
$conn = mysqli_connect('localhost', 'root', '', 'taskflow');

if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}
?>
```

> Default XAMPP credentials: user `root`, empty password.

### 4. Run the app

1. Start **Apache** and **MySQL** in the XAMPP Control Panel
2. Open your browser and visit:

```
http://localhost/taskflow/index.php
```

---

## 🚀 Setup Option 2 — Laravel Version

### 1. Create the Laravel project

```bash
composer create-project laravel/laravel taskflow-laravel
cd taskflow-laravel
```

### 2. Configure the environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=taskflow
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Create the tasks table (migration)

```bash
php artisan make:model Task -m
```

Edit the generated migration file (`database/migrations/xxxx_create_tasks_table.php`):

```php
public function up(): void
{
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->string('task_name');
        $table->text('description')->nullable();
        $table->enum('status', ['Pending', 'Completed'])->default('Pending');
        $table->date('due_date');
        $table->timestamps();
    });
}
```

Run the migration:

```bash
php artisan migrate
```

### 4. Create the Task model

Edit `app/Models/Task.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['task_name', 'description', 'status', 'due_date'];
}
```

### 5. Create the controller

```bash
php artisan make:controller TaskController --resource
```

Edit `app/Http/Controllers/TaskController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::orderBy('due_date', 'asc')->get();
        $total = $tasks->count();
        $pending = $tasks->where('status', 'Pending')->count();
        $completed = $tasks->where('status', 'Completed')->count();

        return view('tasks.index', compact('tasks', 'total', 'pending', 'completed'));
    }

    public function store(Request $request)
    {
        Task::create($request->validate([
            'task_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
        ]));

        return redirect()->route('tasks.index');
    }

    public function edit(Task $task)
    {
        return view('tasks.edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $task->update($request->validate([
            'task_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Pending,Completed',
            'due_date' => 'required|date',
        ]));

        return redirect()->route('tasks.index');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index');
    }

    public function toggle(Task $task)
    {
        $task->update([
            'status' => $task->status === 'Pending' ? 'Completed' : 'Pending',
        ]);

        return redirect()->route('tasks.index');
    }
}
```

### 6. Add the routes

Edit `routes/web.php`:

```php
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::resource('tasks', TaskController::class);
Route::get('tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
```

### 7. Create the Blade views

Copy the glassmorphism UI into these Blade templates:

- `resources/views/tasks/index.blade.php` — sidebar, stats cards, add form, tasks table
- `resources/views/tasks/edit.blade.php` — edit form
- Move the CSS to `public/css/style.css` (or `resources/css/app.css` if using Vite)

### 8. Run the Laravel dev server

```bash
php artisan serve
```

Visit: `http://localhost:8000/tasks`

---

## 📖 Usage

| Action | How |
|--------|-----|
| ➕ Add task | Fill in the form at the top, click **Add Task** |
| ✏️ Edit task | Click the ✏️ icon on any row |
| ✅ Complete / ↩️ Pending | Click the toggle icon on any row |
| 🗑️ Delete task | Click the 🗑️ icon (with confirmation) |
| 🌙 Dark / ☀️ Light mode | Click the toggle switch in the sidebar |

---

## 🧩 Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend (Classic) | PHP (procedural, MySQLi) |
| Backend (Laravel) | Laravel 10.x (MVC, Eloquent ORM) |
| Database | MySQL / MariaDB |
| Frontend | HTML5, CSS3 (Glassmorphism, CSS Variables, Flexbox) |
| Templating (Laravel) | Blade |
| State | `localStorage` for theme persistence |

---

## 📸 Screenshots

> Add screenshots of the dashboard (dark mode & light mode) here.

```
screenshots/
├── dashboard-dark.png
├── dashboard-light.png
└── edit-task.png
```

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).

---

<p align="center">Made with 💜 by the TaskFlow Team</p>
