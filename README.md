#  PHP_Laravel12_Schedule_Monitor

![Laravel](https://img.shields.io/badge/Laravel-12-red)
![PHP](https://img.shields.io/badge/PHP-8%2B-blue)
![MySQL](https://img.shields.io/badge/Database-MySQL-orange)
![Spatie](https://img.shields.io/badge/Package-Spatie%20Schedule%20Monitor-green)

---

##  Overview

**PHP_Laravel12_Schedule_Monitor** is a Laravel 12 project that demonstrates how to monitor Laravel scheduled tasks using the **Spatie Schedule Monitor** package.

The system tracks and logs scheduler execution details including:

* Execution time
* Success / failure status
* Missed schedules
* Runtime history

This setup is designed for a **Windows (XAMPP)** development environment using a **MySQL database**.

---

##  Features

*  Laravel 12 Scheduler Monitoring
*  Spatie Schedule Monitor Integration
*  Automatic Task Logging
*  Execution Status Tracking
*  Missed Schedule Detection
*  Runtime History Storage
*  MySQL Database Support
*  Windows (XAMPP) Compatible

---

##  Folder Structure

```
PHP_Laravel12_Schedule_Monitor/
│
├── app/
│   └── Console/
│       └── Commands/
│           └── TestSchedulerCommand.php
│
├── config/
│   └── schedule-monitor.php
│
├── database/
│   └── migrations/
│
├── routes/
│   └── console.php
│
├── .env
└── README.md
```

---

##  Prerequisites

Make sure the following are installed:

* PHP 8+
* Composer
* Laravel CLI
* MySQL
* XAMPP (Windows Environment)

---

##  Installation & Setup

### STEP 1 — Create Laravel Project

```bash
composer create-project laravel/laravel PHP_Laravel12_Schedule_Monitor
```

Run server:

```bash
php artisan serve
```

---

### STEP 2 — Configure Database

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=schedule
DB_USERNAME=root
DB_PASSWORD=
```

---

### STEP 3 — Install Schedule Monitor Package

```bash
composer require spatie/laravel-schedule-monitor
```

---

### STEP 4 — Publish Package Files

```bash
php artisan vendor:publish --provider="Spatie\ScheduleMonitor\ScheduleMonitorServiceProvider"
```

This publishes:

* `config/schedule-monitor.php`
* database migrations

---

### STEP 5 — Run Migration

```bash
php artisan migrate
```

Tables created:

* `monitored_scheduled_tasks`
* `monitored_scheduled_task_log_items`

---

### STEP 6 — Create Test Scheduler Command

```bash
php artisan make:command TestSchedulerCommand
```

**app/Console/Commands/TestSchedulerCommand.php**

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestSchedulerCommand extends Command
{
    protected $signature = 'test:scheduler';
    protected $description = 'Test schedule monitor command';

    public function handle()
    {
        \Log::info('Scheduler executed at '.now());

        $this->info('Scheduler Working Successfully');

        return Command::SUCCESS;
    }
}
```

---

### STEP 7 — Register Scheduler (Laravel 12 Safe Method)

Edit:

```
routes/console.php
```

```php
<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Console\Scheduling\Event;

Artisan::command('test:scheduler', function () {
    $this->info('Scheduler running at '.now());
});

$schedule = Schedule::command('test:scheduler')
    ->everyMinute();

if (method_exists(Event::class, 'monitor')) {
    $schedule->monitor();
}
```

---

### STEP 8 — Clear Cache

```bash
php artisan optimize:clear
```

---

### STEP 9 — Sync Scheduler with Monitor

```bash
php artisan schedule-monitor:sync
```

Expected output:

```
All done! Now monitoring 1 scheduled tasks.
```
<img width="858" height="217" alt="Screenshot 2026-02-23 173430" src="https://github.com/user-attachments/assets/75c04498-dd95-44ef-ac7f-6016edecfada" />

---

### STEP 10 — View Monitored Tasks

```bash
php artisan schedule-monitor:list
```

Example output:

```
test:scheduler (command) Every minute
Started at: --
Finished at: --
```
<img width="1572" height="126" alt="Screenshot 2026-02-23 173822" src="https://github.com/user-attachments/assets/89a212d8-01b9-4cf2-987e-46eb13fdc14f" />

---

### STEP 11 — Run Scheduler Manually

```bash
php artisan schedule:run
```

Output:

```
Running ["artisan" test:scheduler] DONE
```
<img width="1075" height="72" alt="Screenshot 2026-02-23 173438" src="https://github.com/user-attachments/assets/570352b3-a1b5-425d-bab6-562bf0bcf314" />

---

### STEP 12 — Verify Monitoring

Run again:

```bash
php artisan schedule-monitor:list
```

Now shows:

```
Started at: 2026-02-23 XX:XX:XX
Finished at: 2026-02-23 XX:XX:XX
```
<img width="1564" height="137" alt="Screenshot 2026-02-23 173446" src="https://github.com/user-attachments/assets/4ea5ac09-d5b8-4476-b737-a543635cea40" />

---

##  Useful Commands

| Command                             | Description            |
| ----------------------------------- | ---------------------- |
| `php artisan schedule:run`          | Run scheduler manually |
| `php artisan schedule-monitor:list` | Show monitored tasks   |
| `php artisan schedule-monitor:sync` | Sync schedules         |
| `php artisan optimize:clear`        | Clear caches           |

---

