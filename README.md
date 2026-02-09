# 🚀 Glow Starter Kit

This is a **Filament v4 Starter Kit** for **Laravel 12**, designed to accelerate the development of Filament-powered applications.

Preview:
![](https://raw.githubusercontent.com/ercogx/laravel-filament-starter-kit/main/preview-white.png)
Dark Mode:
![](https://raw.githubusercontent.com/ercogx/laravel-filament-starter-kit/main/preview.png)

## Compatibility

| Starter Kit                                                            | Filament Version                                        |
|------------------------------------------------------------------------|---------------------------------------------------------|
| [1.x](https://github.com/Ercogx/laravel-filament-starter-kit/tree/1.x) | [3.x](https://github.com/filamentphp/filament/tree/3.x) |
| **2.x**                                                                | **4.x**                                                 |


## 📦 Installation

You need the Laravel Installer if it is not yet installed.

```bash
composer global require laravel/installer
```

Now you can create a new project using the Laravel Filament Starter Kit.

```bash
laravel new test-kit --using=ercogx/laravel-filament-starter-kit
```

> If you want a Filament v3 (not recommended) ```laravel new test-kit --using=ercogx/laravel-filament-starter-kit:1.8.0```

## Sail for Docker Dev

composer require laravel/sail --dev
php artisan sail:install

- pick what you need should then use mysql

## ⚙️ Setup

1️⃣ **Database Configuration**

By default, this starter kit uses **SQLite**. If you’re okay with this, you can skip this step. If you prefer **MySQL**, follow these steps:

- Update your database credentials in `.env`
- Run migrations: `php artisan migrate`
- (Optional) delete the existing database file: ```rm database/database.sqlite```

2️⃣ Create Filament Admin User
```bash
sail php artisan make:filament-user

create admin and abAdmin```

3️⃣ Run Database Seeder (includes user creation, role assignment, and shield setup)
```bash
# Option 1: Fresh migration and seed
sail php artisan migrate:fresh --seed

# Option 2: Use the custom command
sail php artisan db:reset-seed --fresh
```

This will create:
- Admin user: `admin@example.com` / `password` (super_admin role)
- Clients: `dev1`, `qa2`
- For each client: admin user and regular user with appropriate roles and associations

sail npm install
sail npm run build

4️⃣ (Optional) If you need to manually assign super admin role
```bash which ever user admin is and proper panel
sail php artisan shield:super-admin --user=1 --panel=pop
```

5️⃣ (Optional) Generate Permissions (already done by seeder)
```bash
sail php artisan shield:generate --all --ignore-existing-policies --panel=pop
```

## 🌟Panel Include 

- [Shield](https://filamentphp.com/plugins/bezhansalleh-shield) Access management to your Filament Panel's Resources, Pages & Widgets through spatie/laravel-permission.
- [Backgrounds](https://filamentphp.com/plugins/swisnl-backgrounds) Beautiful backgrounds for Filament auth pages.
- [Logger](https://filamentphp.com/plugins/z3d0x-logger) Extensible activity logger for filament that works out-of-the-box.
- [Nord Theme](https://filamentphp.com/plugins/andreia-bohner-nord-theme) Beautiful Nord theme with subdued palette
- [Breezy](https://filamentphp.com/plugins/jeffgreco-breezy) My Profile page.

> More will be added when the relevant plugins release support for v4

## 🧑‍💻Development Include

- [barryvdh/laravel-debugbar](https://github.com/barryvdh/laravel-debugbar) The most popular debugging tool for Laravel, providing detailed request and query insights.
- [larastan/larastan](https://github.com/larastan/larastan) A PHPStan extension for Laravel, configured at level 5 for robust static code analysis.
- [plannr/laravel-fast-refresh-database](https://github.com/PlannrCrm/laravel-fast-refresh-database) 🚀 Refresh your test databases faster than you've ever seen before

The `composer check` script runs **tests, PHPStan, and Pint** for code quality assurance:
```bash
composer check
```

## 📜 License

This project is open-source and licensed under the MIT License.

## 💡 Contributing

We welcome contributions! Feel free to open issues, submit PRs, or suggest improvements.


### 🚀 Happy Coding with Laravel & Filament! 🎉
