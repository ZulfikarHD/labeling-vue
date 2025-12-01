# Petty Cash Book App - Project Documentation Structure

This is a recommended folder and file structure for organizing your project documentation.

```
docs/
├── README.md                                 # Main documentation index
│
├── 01-project-overview/
│   ├── project-charter.md                    # Project goals, scope, stakeholders
│   ├── business-requirements.md              # Business needs and objectives
│   ├── glossary.md                           # Terms and definitions
│   └── project-timeline.md                   # High-level timeline and milestones
│
├── 02-requirements/
│   ├── functional-requirements.md            # Detailed feature requirements
│   ├── non-functional-requirements.md        # Performance, security, scalability
│   ├── user-stories.md                       # All user stories compiled
│   └── acceptance-criteria.md                # Success criteria for features
│
├── 03-design/
│   ├── system-architecture.md                # High-level system design
│   ├── database-schema.md                    # ERD and table structures
│   ├── api-design.md                         # API endpoints and specifications
│   ├── ui-ux/
│   │   ├── wireframes/                       # Low-fidelity wireframes
│   │   ├── mockups/                          # High-fidelity designs
│   │   ├── design-system.md                  # Colors, typography, components
│   │   └── user-flows.md                     # User journey diagrams
│   └── security-design.md                    # Security architecture
│
├── 04-technical/
│   ├── setup-guide.md                        # Development environment setup
│   ├── coding-standards.md                   # Code style and conventions
│   ├── git-workflow.md                       # Branching strategy, commit rules
│   ├── testing-strategy.md                   # Testing approach and standards
│   ├── deployment-guide.md                   # Deployment procedures
│   └── infrastructure.md                     # Server, hosting, services used
│
├── 05-api-documentation/
│   ├── api-overview.md                       # API general information
│   ├── authentication.md                     # Auth endpoints and flow
│   ├── endpoints/
│   │   ├── transactions.md                   # Transaction endpoints
│   │   ├── users.md                          # User management endpoints
│   │   ├── categories.md                     # Category endpoints
│   │   ├── reports.md                        # Reporting endpoints
│   │   └── ...
│   ├── error-codes.md                        # API error responses
│   └── postman-collection.json               # Postman collection file
│
├── 06-user-guides/
│   ├── user-manual.md                        # Complete user guide
│   ├── admin-guide.md                        # Admin-specific guide
│   ├── quick-start.md                        # Getting started guide
│   ├── faq.md                                # Frequently asked questions
│   └── troubleshooting.md                    # Common issues and solutions
│
├── 07-development/
│   ├── sprint-planning/
│   │   ├── sprint-01.md                      # Sprint 1 planning notes
│   │   ├── sprint-02.md                      # Sprint 2 planning notes
│   │   └── ...
│   ├── technical-decisions.md                # ADR (Architecture Decision Records)
│   ├── code-review-guidelines.md             # Code review checklist
│   └── changelog.md                          # Version history and changes
│
├── 08-testing/
│   ├── test-plan.md                          # Overall testing strategy
│   ├── test-cases/
│   │   ├── authentication-tests.md           # Auth test cases
│   │   ├── transaction-tests.md              # Transaction test cases
│   │   └── ...
│   ├── bug-reports/                          # Bug tracking and reports
│   └── uat-results.md                        # User acceptance testing results
│
├── 09-operations/
│   ├── monitoring.md                         # Monitoring and logging setup
│   ├── backup-recovery.md                    # Backup and recovery procedures
│   ├── maintenance.md                        # Maintenance procedures
│   ├── scaling-guide.md                      # Scaling strategies
│   └── incident-response.md                  # Incident handling procedures
│
├── 10-meetings/
│   ├── sprint-reviews/
│   │   ├── sprint-01-review.md
│   │   └── ...
│   ├── retrospectives/
│   │   ├── sprint-01-retro.md
│   │   └── ...
│   └── stakeholder-meetings/
│       └── meeting-notes-YYYY-MM-DD.md
│
└── 11-assets/
    ├── diagrams/                             # Architecture diagrams, flowcharts
    ├── screenshots/                          # Application screenshots
    ├── templates/                            # Document templates
    └── presentations/                        # Project presentations

```

---

## Example Document Templates

### 1. README.md (Main Index)

```markdown
# Petty Cash Book Application - Documentation

Welcome to the Petty Cash Book application documentation.

## Quick Links

- [Project Overview](01-project-overview/project-charter.md)
- [Setup Guide](04-technical/setup-guide.md)
- [User Manual](06-user-guides/user-manual.md)
- [API Documentation](05-api-documentation/api-overview.md)

## For Developers

- [Coding Standards](04-technical/coding-standards.md)
- [Git Workflow](04-technical/git-workflow.md)
- [Testing Strategy](04-technical/testing-strategy.md)

## For Users

- [Quick Start Guide](06-user-guides/quick-start.md)
- [FAQ](06-user-guides/faq.md)
- [Troubleshooting](06-user-guides/troubleshooting.md)

## For Admins

- [Admin Guide](06-user-guides/admin-guide.md)
- [Deployment Guide](04-technical/deployment-guide.md)
- [Monitoring](09-operations/monitoring.md)

## Project Status

- **Current Version**: 1.0.0
- **Last Updated**: 2024-11-24
- **Project Status**: In Development
```

---

### 2. database-schema.md

```markdown
# Database Schema

## Entity Relationship Diagram

[Insert ERD image here]

## Tables

### users
Stores user account information.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint | NO | AUTO | Primary key |
| name | varchar(255) | NO | - | User full name |
| email | varchar(255) | NO | - | Unique email |
| password | varchar(255) | NO | - | Hashed password |
| department_id | bigint | YES | NULL | FK to departments |
| created_at | timestamp | YES | NULL | Record creation |
| updated_at | timestamp | YES | NULL | Last update |
| deleted_at | timestamp | YES | NULL | Soft delete |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (email)
- INDEX (department_id)

**Relationships:**
- belongsTo: Department
- hasMany: Transactions
- belongsToMany: Roles

---

### transactions
Stores all cash in/out transactions.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint | NO | AUTO | Primary key |
| transaction_number | varchar(50) | NO | - | Unique transaction ID |
| type | enum('in','out') | NO | - | Transaction type |
| amount | decimal(15,2) | NO | - | Transaction amount |
| description | text | NO | - | Transaction details |
| transaction_date | date | NO | - | Date of transaction |
| category_id | bigint | YES | NULL | FK to categories |
| user_id | bigint | NO | - | FK to users (creator) |
| vendor_id | bigint | YES | NULL | FK to vendors |
| status | enum | NO | 'pending' | Transaction status |
| approved_by | bigint | YES | NULL | FK to users (approver) |
| approved_at | timestamp | YES | NULL | Approval timestamp |
| created_at | timestamp | YES | NULL | Record creation |
| updated_at | timestamp | YES | NULL | Last update |
| deleted_at | timestamp | YES | NULL | Soft delete |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (transaction_number)
- INDEX (category_id, user_id, vendor_id, status, transaction_date)

**Relationships:**
- belongsTo: User, Category, Vendor
- hasMany: TransactionAttachments
- hasMany: Approvals

[Continue for all tables...]
```

---

### 3. setup-guide.md

```markdown
# Development Environment Setup Guide

## Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- MySQL 8.0 or PostgreSQL 13+
- Node.js 18+ and NPM
- Git

## Installation Steps

### 1. Clone Repository

```bash
git clone https://github.com/yourcompany/petty-cash-app.git
cd petty-cash-app
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file:

```env
APP_NAME="Petty Cash Book"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=petty_cash
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
```

### 5. Database Setup

```bash
# Create database
mysql -u root -p
CREATE DATABASE petty_cash;
exit;

# Run migrations
php artisan migrate

# Seed database with test data
php artisan db:seed
```

### 6. Storage Link

```bash
php artisan storage:link
```

### 7. Build Assets

```bash
npm run dev
```

### 8. Start Development Server

```bash
php artisan serve
```

Visit: http://localhost:8000

## Default Login Credentials

**Admin Account:**
- Email: admin@pettycash.com
- Password: password

**Cashier Account:**
- Email: cashier@pettycash.com
- Password: password

## Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

## Troubleshooting

### Issue: Permission Denied on Storage

```bash
chmod -R 775 storage bootstrap/cache
```

### Issue: Database Connection Failed

- Verify MySQL is running
- Check database credentials in `.env`
- Ensure database exists

### Issue: Assets Not Loading

```bash
npm run build
php artisan optimize:clear
```
```

---

### 4. api-overview.md

```markdown
# API Documentation

## Base URL

```
Production: https://api.pettycash.com/api/v1
Staging: https://staging-api.pettycash.com/api/v1
Development: http://localhost:8000/api/v1
```

## Authentication

All API requests require authentication using Bearer tokens.

### Obtaining Token

```http
POST /auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**

```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer",
  "expires_in": 3600
}
```

### Using Token

```http
GET /transactions
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

## Response Format

### Success Response

```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

### Error Response

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {
      "email": ["The email field is required."]
    }
  }
}
```

## Pagination

List endpoints return paginated results:

```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 150,
    "last_page": 10
  },
  "links": {
    "first": "http://api.../transactions?page=1",
    "last": "http://api.../transactions?page=10",
    "prev": null,
    "next": "http://api.../transactions?page=2"
  }
}
```

## Rate Limiting

- **Authenticated**: 60 requests per minute
- **Unauthenticated**: 10 requests per minute

## Endpoints

See individual endpoint documentation:

- [Authentication](endpoints/authentication.md)
- [Transactions](endpoints/transactions.md)
- [Categories](endpoints/categories.md)
- [Users](endpoints/users.md)
- [Reports](endpoints/reports.md)
```

---

### 5. coding-standards.md

```markdown
# Coding Standards

## PHP Code Style

We follow **PSR-12** coding standard with Laravel conventions.

### Naming Conventions

**Classes**: PascalCase
```php
class TransactionController
```

**Methods**: camelCase
```php
public function createTransaction()
```

**Variables**: camelCase
```php
$transactionAmount = 100;
```

**Constants**: UPPER_SNAKE_CASE
```php
const MAX_UPLOAD_SIZE = 5242880;
```

**Database Tables**: plural, snake_case
```
transactions, user_roles, cash_balances
```

**Database Columns**: snake_case
```
transaction_date, created_at, user_id
```

## Laravel Best Practices

### Controllers

- Keep controllers thin
- Use Form Requests for validation
- Return consistent response format

```php
public function store(StoreTransactionRequest $request)
{
    $transaction = $this->transactionService->create($request->validated());
    
    return response()->json([
        'success' => true,
        'data' => new TransactionResource($transaction),
        'message' => 'Transaction created successfully'
    ], 201);
}
```

### Models

- Use fillable or guarded
- Define relationships
- Use accessors/mutators for data formatting

```php
class Transaction extends Model
{
    protected $fillable = ['amount', 'description', 'type'];
    
    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Services

- Business logic goes in service classes
- Keep methods focused and single-purpose

```php
class TransactionService
{
    public function create(array $data): Transaction
    {
        DB::beginTransaction();
        
        try {
            $transaction = Transaction::create($data);
            $this->updateBalance($transaction);
            
            DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
```

## JavaScript/Vue Code Style

- Use ES6+ syntax
- Component names: PascalCase
- Props: camelCase
- Events: kebab-case

## Git Commit Messages

Format: `type(scope): subject`

**Types:**
- feat: New feature
- fix: Bug fix
- docs: Documentation
- style: Formatting
- refactor: Code restructuring
- test: Adding tests
- chore: Maintenance

**Examples:**
```
feat(transactions): add receipt upload feature
fix(auth): resolve login redirect issue
docs(api): update authentication documentation
```

## Code Review Checklist

- [ ] Code follows PSR-12 standards
- [ ] Tests are included and passing
- [ ] Documentation is updated
- [ ] No hardcoded credentials
- [ ] Proper error handling
- [ ] Database queries are optimized
- [ ] Security best practices followed
```

---

## How to Maintain Documentation

### 1. Keep It Updated
- Update docs when code changes
- Review during code reviews
- Quarterly documentation audits

### 2. Version Control
- Keep docs in the same repository as code
- Tag documentation versions with releases

### 3. Collaboration
- Use pull requests for documentation changes
- Assign documentation tasks in sprints
- Regular documentation reviews

### 4. Tools Recommendation

**For Documentation:**
- **Markdown files** in Git repository (simple, version-controlled)
- **GitBook** (beautiful, searchable documentation)
- **Confluence** (team collaboration)
- **Notion** (flexible, all-in-one)

**For API Documentation:**
- **Postman Collections**
- **Swagger/OpenAPI**
- **Laravel API Documentation Generator**

**For Diagrams:**
- **Draw.io** (free, powerful)
- **Lucidchart** (professional)
- **Mermaid** (code-based diagrams)
- **dbdiagram.io** (database schemas)

### 5. Automation

```bash
# Generate API documentation
php artisan scribe:generate

# Generate code documentation
phpdoc -d src/ -t docs/api

# Database schema export
php artisan schema:dump
```
