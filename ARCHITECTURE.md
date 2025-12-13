# FST Gestion Examen - Architecture Documentation

## Overview
This application now follows a clean layered architecture pattern: **Controller → Service → Repository**

## Architecture Layers

### 1. Controllers (app/Http/Controllers)
**Responsibility**: Handle HTTP requests and responses
- Validate incoming requests
- Call appropriate service methods
- Return HTTP responses
- **DO NOT** contain business logic
- **DO NOT** directly access models/database

### 2. Services (app/Services)
**Responsibility**: Business logic layer
- Contain all business logic
- Orchestrate operations between multiple repositories
- Handle transactions
- Send notifications
- Transform data for presentation

**Implemented Services:**
- `AttendanceService` - Attendance recording and retrieval
- `StudentService` - Student management
- `ExamService` - Complex exam management with relations
- `ClassroomService` - Classroom and scheduling management
- `SuperviseurService` - Supervisor management
- `ProfesseurService` - Professor management

### 3. Repositories (app/Repositories)
**Responsibility**: Data access layer
- Direct database queries
- CRUD operations
- Complex queries specific to models
- **DO NOT** contain business logic

**Implemented Repositories:**
- `BaseRepository` - Common CRUD operations
- `AttendanceRepository`
- `StudentRepository`
- `ExamRepository`
- `ClassroomRepository`
- `SuperviseurRepository`
- `ProfesseurRepository`

## Directory Structure

```
app/
├── Http/
│   └── Controllers/
│       ├── AttendanceController.php ✓ Refactored
│       ├── StudentController.php ✓ Refactored
│       ├── SuperviseurController.php ✓ Refactored
│       ├── ProfesseurController.php ✓ Refactored
│       ├── ClassroomController.php (to be refactored)
│       └── ExamController.php (to be refactored)
│
├── Services/
│   ├── AttendanceService.php
│   ├── StudentService.php
│   ├── ExamService.php
│   ├── ClassroomService.php
│   ├── SuperviseurService.php
│   ├── ProfesseurService.php
│   ├── ExamNotificationService.php (existing)
│   └── ConcoursNotificationService.php (existing)
│
└── Repositories/
    ├── Contracts/
    │   └── RepositoryInterface.php
    ├── BaseRepository.php
    ├── AttendanceRepository.php
    ├── StudentRepository.php
    ├── ExamRepository.php
    ├── ClassroomRepository.php
    ├── SuperviseurRepository.php
    └── ProfesseurRepository.php
```

## Benefits of This Architecture

1. **Separation of Concerns**: Each layer has a single, well-defined responsibility
2. **Testability**: Easy to unit test each layer independently
3. **Maintainability**: Changes in one layer don't affect others
4. **Reusability**: Services and repositories can be reused across controllers
5. **Scalability**: Easy to add new features following the same pattern

## Example Flow

### Creating an Attendance Record

```
HTTP Request
    ↓
AttendanceController::store()
    ↓ (validates request)
AttendanceService::recordAttendance()
    ↓ (business logic)
AttendanceRepository::updateOrCreate()
    ↓ (database query)
Database
```

## Dependency Injection

Laravel's service container automatically injects dependencies:

```php
// Controller receives Service via constructor
public function __construct(AttendanceService $attendanceService)
{
    $this->attendanceService = $attendanceService;
}

// Service receives Repository via constructor
public function __construct(AttendanceRepository $attendanceRepository)
{
    $this->attendanceRepository = $attendanceRepository;
}

// Repository receives Model via constructor
public function __construct(Attendance $model)
{
    parent::__construct($model);
}
```

## Next Steps

To complete the refactoring:
1. Refactor ClassroomController
2. Refactor ExamController (most complex due to many relationships)
3. Refactor remaining controllers
4. Add comprehensive tests
5. Update API documentation

## Testing

Run tests with:
```bash
php artisan test
```

## Notes

- The existing `ExamNotificationService` and `ConcoursNotificationService` fit well into this architecture
- All new features should follow this pattern
- IDE warnings about "unknown classes" are expected after refactoring (removed direct model imports from controllers)
