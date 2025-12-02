# Label Generator System - Dokumentasi

Selamat datang di dokumentasi Label Generator System yang merupakan aplikasi untuk mengelola production order dan label generation dalam proses produksi, yaitu: registrasi PO, verifikasi, cetak label, printing, dan monitoring yang terintegrasi dengan SIRINE API untuk data validation.

## Quick Links

- [Project Overview](01-project-overview/project-charter.md)
- [Setup Guide](04-technical/setup-guide.md)
- [User Manual](06-user-guides/user-manual.md)
- [API Documentation](05-api-documentation/api-overview.md)

## Untuk Developer

- [Tech Stack](04-technical/tech-stack.md) - Laravel 12, Vue 3, Inertia.js, Tailwind 4
- [Setup Guide](04-technical/setup-guide.md) - Development environment setup
- [Coding Standards](04-technical/coding-standards.md) - Indonesian style documentation
- [Git Workflow](04-technical/git-workflow.md) - Branching dan commit conventions
- [Testing Strategy](04-technical/testing-strategy.md) - PHPUnit testing approach
- [Sprint Planning](07-development/sprint-planning/) - Development roadmap

## Untuk User

- [Quick Start Guide](06-user-guides/quick-start.md) - Panduan memulai aplikasi
- [Operator Guide](06-user-guides/operator-guide.md) - Workflow operator
- [FAQ](06-user-guides/faq.md) - Frequently asked questions
- [Troubleshooting](06-user-guides/troubleshooting.md) - Common issues

## Untuk Admin

- [Admin Guide](06-user-guides/admin-guide.md) - User management guide
- [Deployment Guide](04-technical/deployment-guide.md) - Deployment procedures
- [Monitoring](09-operations/monitoring.md) - Monitoring dan logging

## Project Status

- **Current Version**: 1.0.0
- **Last Updated**: 2025-12-02
- **Project Status**: In Development
- **Tech Stack**: Laravel 12, Vue 3, Inertia.js v2, Tailwind 4
- **Developer**: Zulfikar Hidayatullah (+62 857-1583-8733)

## Struktur Dokumentasi

```
docs/
├── 01-project-overview/     # Project charter, requirements, glossary
├── 02-requirements/         # Functional & non-functional requirements
├── 03-design/              # System architecture, database, UI/UX
├── 04-technical/           # Setup, coding standards, deployment
├── 05-api-documentation/   # API endpoints & specifications
├── 06-user-guides/         # User manuals & guides
├── 07-development/         # Sprint planning & technical decisions
├── 08-testing/             # Test plans & test cases
├── 09-operations/          # Monitoring, backup, maintenance
├── 10-meetings/            # Sprint reviews & retrospectives
└── 11-assets/              # Diagrams, screenshots, templates
```

## Fitur Utama Aplikasi

### Order Management
- **Register PO Order Besar** dengan SIRINE API verification
- **Register PO Order Kecil** untuk fast processing
- **MMEA Orders** dengan special handling (no cut side)
- **Order Verification** workflow dengan team assignment

### Label Processing
- **Automatic Label Generation** berdasarkan business rules
- **Processing Priority**: Inschiet first, left before right
- **Inspector Assignment** dengan dual inspector support
- **Session Management** untuk tracking inspection work

### Printing
- **Print Labels** dengan label template
- **Reprint Labels** dengan permission control
- **Batch Printing** untuk multiple labels

### Monitoring & Reports
- **Team Status Dashboard** untuk monitoring production
- **Employee Production Report** untuk tracking individual performance
- **Order Progress Tracking** dengan real-time updates
- **Excel Export** untuk reporting needs

## Tech Stack

### Backend
- **Laravel 12** - PHP Framework
- **Laravel Fortify** - Authentication
- **Laravel Wayfinder** - Type-safe routes
- **PHP 8.4.1** - Programming language
- **MySQL** - Database

### Frontend
- **Vue 3** - JavaScript framework
- **Inertia.js v2** - SPA without API
- **Tailwind CSS 4** - Utility-first CSS
- **Vite** - Build tool

### Development Tools
- **Laravel Pint** - Code formatter
- **PHPUnit** - Testing framework
- **Yarn** - Package manager
- **Git** - Version control

## Business Rules Overview

### Constants
```
SHEETS_PER_RIM = 1000
INSCHIET_RIM = 999
```

### Order Types
- **Regular Order**: 2 labels per rim (Left + Right), dengan inschiet handling
- **MMEA Order**: 1 label per rim, no cut side, no inschiet

### Processing Priority
1. Inschiet first (Rim 999)
2. Regular rims ascending
3. Left before right

## Contributing

Untuk berkontribusi pada dokumentasi:

1. Follow Indonesian documentation style dari `.cursor/rules/documentation-writing-styles.mdc`
2. Update dokumentasi saat ada code changes
3. Include examples dan use cases yang clear
4. Test code examples sebelum commit
5. Submit pull request untuk review

## Support

Untuk bantuan atau pertanyaan:

**Developer**: Zulfikar Hidayatullah  
**Phone**: +62 857-1583-8733  
**Email**: [contact email]

---

**Last Updated**: 2025-12-02  
**Version**: 1.0.0  
**Status**: In Development

