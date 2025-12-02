## Struktur Folder Dokumentasi

```
docs/
├── README.md                                 # Index dokumentasi utama
│
├── 01-project-overview/
│   ├── project-charter.md                    # Goals, scope, dan stakeholders proyek
│   ├── business-requirements.md              # Business needs dan objectives
│   ├── glossary.md                           # Terms dan definitions (NP, PO, OBC, MMEA, dll)
│   └── project-timeline.md                   # High-level timeline dan milestones
│
├── 02-requirements/
│   ├── functional-requirements.md            # Detailed feature requirements
│   ├── non-functional-requirements.md        # Performance, security, scalability
│   ├── user-stories.md                       # Compiled user stories dari semua sprints
│   ├── acceptance-criteria.md                # Success criteria untuk features
│   └── business-rules.md                     # Critical business rules (SHEETS_PER_RIM, formulas, dll)
│
├── 03-design/
│   ├── system-architecture.md                # High-level system design
│   ├── database-schema.md                    # ERD dan table structures
│   ├── api-design.md                         # API endpoints dan specifications
│   ├── label-generation-logic.md             # Core business logic untuk label generation
│   ├── ui-ux/
│   │   ├── wireframes/                       # Low-fidelity wireframes
│   │   ├── mockups/                          # High-fidelity designs (iOS style)
│   │   ├── design-system.md                  # Colors, typography, iOS design principles
│   │   └── user-flows.md                     # User journey diagrams
│   └── security-design.md                    # Security architecture (Fortify, authentication)
│
├── 04-technical/
│   ├── setup-guide.md                        # Development environment setup
│   ├── coding-standards.md                   # Code style dan conventions (Laravel 12, Vue 3)
│   ├── git-workflow.md                       # Branching strategy, commit rules
│   ├── testing-strategy.md                   # Testing approach (PHPUnit, Feature tests)
│   ├── deployment-guide.md                   # Deployment procedures
│   ├── tech-stack.md                         # Laravel 12, Vue 3, Inertia.js, Tailwind 4
│   └── infrastructure.md                     # Server, hosting, services
│
├── 05-api-documentation/
│   ├── api-overview.md                       # API general information
│   ├── authentication.md                     # Fortify auth endpoints dan flow
│   ├── endpoints/
│   │   ├── production-orders.md              # Production Order endpoints
│   │   ├── labels.md                         # Label processing endpoints
│   │   ├── users.md                          # User management endpoints
│   │   ├── workstations.md                   # Workstation endpoints
│   │   ├── monitoring.md                     # Monitoring dashboard endpoints
│   │   ├── reports.md                        # Reports dan export endpoints
│   │   └── sirine-api.md                     # External SIRINE API integration
│   ├── error-codes.md                        # API error responses
│   └── wayfinder-routes.md                   # Laravel Wayfinder type-safe routes
│
├── 06-user-guides/
│   ├── user-manual.md                        # Complete user guide
│   ├── admin-guide.md                        # Admin-specific guide (user management)
│   ├── operator-guide.md                     # Operator workflow guide
│   ├── quick-start.md                        # Getting started guide
│   ├── faq.md                                # Frequently asked questions
│   └── troubleshooting.md                    # Common issues dan solutions
│
├── 07-development/
│   ├── sprint-planning/
│   │   ├── sprint-01-foundation.md           # Database, Models, Enums
│   │   ├── sprint-02-authentication.md       # Login, Users, Workstations
│   │   ├── sprint-03-external-api.md         # SIRINE API Integration
│   │   ├── sprint-04-label-service.md        # Core Business Logic ⭐
│   │   ├── sprint-05-order-management.md     # Order Besar & Kecil
│   │   ├── sprint-05b-mmea-orders.md         # MMEA workflow
│   │   ├── sprint-06-label-processing.md     # Cetak Label processing
│   │   ├── sprint-07-printing.md             # Print dan Reprint
│   │   ├── sprint-08-monitoring.md           # Dashboard & Team Status
│   │   ├── sprint-09-reports.md              # Reports & Excel Export
│   │   └── sprint-10-polish.md               # Error Handling & Testing
│   ├── technical-decisions.md                # ADR (Architecture Decision Records)
│   ├── code-review-guidelines.md             # Code review checklist
│   └── changelog.md                          # Version history dan changes
│
├── 08-testing/
│   ├── test-plan.md                          # Overall testing strategy
│   ├── test-cases/
│   │   ├── authentication-tests.md           # Auth test cases
│   │   ├── label-generation-tests.md         # Label generation test cases
│   │   ├── order-processing-tests.md         # Order processing test cases
│   │   ├── printing-tests.md                 # Printing test cases
│   │   └── api-integration-tests.md          # SIRINE API test cases
│   ├── bug-reports/                          # Bug tracking dan reports
│   └── uat-results.md                        # User acceptance testing results
│
├── 09-operations/
│   ├── monitoring.md                         # Monitoring dan logging setup
│   ├── backup-recovery.md                    # Backup dan recovery procedures
│   ├── maintenance.md                        # Maintenance procedures
│   ├── scaling-guide.md                      # Scaling strategies
│   └── incident-response.md                  # Incident handling procedures
│
├── 10-meetings/
│   ├── sprint-reviews/
│   │   ├── sprint-01-review.md
│   │   ├── sprint-02-review.md
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
