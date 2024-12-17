<p>Download the project (or clone using GIT)</p>
<p>Copy .env.example into .env and configure your database credentials</p>
<p>Go to the project's root directory using terminal window/command prompt</p>
<p>Run composer install</p>
<p>Set the application key by running php artisan key:generate --ansi</p>
<p>Run migrations php artisan migrate</p>
<p>Start local server by executing php artisan serve</p>
<p>Visit here http://127.0.0.1:8000/admin or dev to test the application</p>
<p>php artisan shield:generate --all </p>
<p>php artisan shield:super-admin </p>
<p>apakah perlu menjalankan php artisan reverb:start </p>

Perbaikan :

-   [x] bug dan bahasa indonesia => branch/cabang
-   [x] bug dan bahasa indonesia => Departemen
-   [x] bug dan bahasa indonesia => Jabatan
-   [x] bug dan bahasa indonesia => Akuntansi
-   [x] bug dan bahasa indonesia => Jurnal
-   [x] bug dan bahasa indonesia => Buku Besar
-   [x] bug dan bahasa indonesia => laporan keuangan
-   [x] bug => role permission
-   [x] Email Verification port .env dan .env.example
-   [x] bug dan bahasa indonesia => daftar karyawan
-   [x] bug dan bahasa indonesia => daftar jabatan karyawan
-   [x] bug fitur auto create dan update jabatan karyawan
-   [x] bug dan bahasa indonesia => shift
-   [x] bug dan bahasa indonesia => jadwal shift
-   [x] bug dan bahasa indonesia => absensi
-   [x] bug dan bahasa indonesia => cuti
-   [x] bug dan bahasa indonesia => maps
-   [x] bug dan bahasa indonesia => rekruitment
-   [x] bug dan bahasa indonesia => cv
-   [x] bug dan bahasa indonesia => laporan kandidate
-   [x] bug dan bahasa indonesia => laporan kandidat interview

Fitur Admin :

-   [x] fitur login = User untuk melakukan login yang berstatus active
-   [x] fitur register = User untuk melakukan register untuk menjadi user aktif dan masuk ke dalam sistem
-   [x] fitur forgot password = User untuk melakukan reset password
-   [x] fitur reset password = User untuk melakukan reset password
-   [x] fitur profile
-   [x] fitur logout
-   [x] fitur dashboard
-   [x] fitur company/tenant
-   [x] fitur global pencarian
-   [x] fitur cabang
-   [x] fitur departemen
-   [x] fitur jabatan
-   [x] fitur Akun Transfer ke akun lain
-   [x] fitur akutansi
-   [x] fitur jurnal
-   [x] fitur bukuu besar dan transaksi
-   [x] fitur laporan keuangan -> laporan laba rugi, laporan neraca, laporan arus kas
-   [x] fitur role permission
-   [x] fitur daftar karyawan
-   [x] fitur daftar jabatan karyawan
-   [x] fitur shift
-   [x] fitur jadwal shift
-   [x] fitur absensi
-   [x] fitur ijin cuti
-   [x] fitur maps
-   [x] fitur rekruitment
-   [x] fitur cv
-   [x] fitur laporan kandidate
-   [x] fitur laporan kandidat interview

bug :

-   [] Deployment error fitur permission super-admin dan register/login
-   [] Activity log error belum setting per company
-   [] laporan keuangan belum otomatis

Berikut perbedaan komponen-komponen Laravel dalam bahasa Indonesia:

## Laravel Components

### Job

-   Background/queue tasks
-   Suitable for heavy processes like sending emails, generating PDFs
-   Can run asynchronously and delayed

### Service

-   Classes that handle business logic
-   Separates logic from controllers
-   Reusable across different parts
-   Focuses on single business feature/domain

### Trait

-   Collection of reusable methods
-   More flexible than inheritance
-   Can be added to various classes
-   Examples: HasFactory, Notifiable

### Scope

-   Custom query builder for models
-   Simplifies commonly used data filters
-   Examples: whereActive(), latest()

### Observer

-   Handles model lifecycle events
-   Automatically called on create/update/delete
-   Suitable for triggers/hooks

### Request

-   Validates user input
-   Separates validation logic from controllers
-   Supports custom rules and messages

### Event

-   Notifications when something occurs
-   Can trigger multiple listeners
-   Enables loose coupling between components

### Rule

-   Custom validation rules
-   Extends Laravel's built-in validation
-   Reusable across different forms

### Middleware

-   Filters/validates requests before reaching controller
-   Examples: auth, throttle, cors

### Provider

-   Service container registration
-   Application bootstrap
-   Global configuration

### Factory

-   Generates dummy data for testing
-   Simplifies database seeding

### Channel

-   Manages realtime broadcasting
-   Websocket and private channels

### Resource

-   Transforms model data to JSON/API
-   Consistent response formatting

### Policy

-   Manages authorization/permissions
-   Validates user access rights

### Interface

-   Contract for implementation
-   Dependency injection

### Repository

-   Database access abstraction
-   Separates queries from models

### Action

-   Single action classes
-   Reusable business logic
-   Lighter than services

### Helper

-   Global functions
-   Utility functions

### Macro

-   Extends Laravel's built-in functions
-   Custom methods for facades

### Collection

-   Array/data manipulation
-   Method chaining

### Presenter

-   Data display formatting
-   View logic

### Performance Optimizations

-   Implement caching strategies (Redis/Memcached)
-   Use eager loading to prevent N+1 queries
-   Implement query caching for frequently accessed data
-   Use database indexing strategically
-   Implement rate limiting for APIs

### Code Organization

-   Follow PSR standards
-   Use value objects for complex data types
-   Implement DTOs (Data Transfer Objects)
-   Create form requests for complex validations
-   Use enums for static options/states

### Development Practices

-   Write comprehensive tests (Unit, Feature, Browser)
-   Use static analysis tools (PHPStan, Larastan)
-   Implement CI/CD pipelines
-   Use code formatting tools (PHP CS Fixer)
-   Document APIs using OpenAPI/Swagger

### Security Enhancements

-   Implement API authentication (Sanctum/Passport)
-   Use signed URLs for sensitive routes
-   Implement rate limiting
-   Add security headers
-   Regular dependency updates

### Monitoring & Logging

-   Use Laravel Telescope for debugging
-   Implement proper error handling and logging
-   Use monitoring tools (New Relic, Scout APM)
-   Set up error reporting (Sentry, Bugsnag)

### Frontend Optimization

-   Use Laravel Mix/Vite for asset compilation
-   Implement lazy loading
-   Use proper caching headers
-   Optimize images and assets
