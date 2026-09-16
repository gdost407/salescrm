# SalesCRM — Codex Project Instructions

## 1. Project Overview

SalesCRM is a Laravel-based CRM application used to manage the complete sales workflow.

Main workflow:

Lead
→ Quotation
→ Job
→ Invoice
→ Payment / Reports

The application also includes:

- Company management
- Staff management
- Lead management
- Lead assignment
- Lead activities and follow-ups
- Quotation management
- Job management
- Invoice management
- Dashboard and analytics
- Reports
- Notifications
- Queue jobs
- Scheduled/Cron jobs
- Webhooks
- Third-party integrations
- Authentication and authorization

This is a production-oriented application. Preserve existing functionality and architecture when making changes.


# 2. Technology Stack

Backend:
- PHP
- Laravel
- MySQL
- Laravel Blade

Frontend:
- HTML5
- CSS
- Bootstrap
- JavaScript
- jQuery
- AJAX

Laravel features may include:

- Eloquent ORM
- Form Requests
- Middleware
- Policies / Gates
- Events
- Listeners
- Notifications
- Jobs
- Queues
- Scheduler
- Commands
- Service classes
- REST APIs
- Webhooks

Do NOT introduce React, Vue, Livewire, Alpine.js, Tailwind CSS, or another frontend framework unless explicitly requested.


# 3. General Working Rules

Always follow these rules:

1. Understand the requested task before modifying code.
2. Inspect only files relevant to the task.
3. Do NOT scan the entire repository unless necessary.
4. Do NOT modify unrelated files.
5. Make the minimum changes necessary to correctly implement the task.
6. Preserve existing functionality.
7. Follow the existing project structure and coding patterns.
8. Prefer existing helpers, services, components, traits, and utilities before creating new ones.
9. Do not over-engineer simple requirements.
10. Do not perform unnecessary refactoring.
11. Do not rename existing classes, methods, variables, routes, database columns, or files unless required.
12. Do not install Composer or NPM packages unless explicitly required.
13. Do not change package versions unless explicitly requested.
14. Do not change environment configuration unnecessarily.
15. Never expose credentials, API keys, tokens, passwords, or secrets.


# 4. Laravel Coding Standards

Follow Laravel conventions and PSR standards.

Prefer:

- Eloquent relationships
- Route model binding where appropriate
- Form Request validation for complex validation
- Middleware for request-level access control
- Policies/Gates for authorization where appropriate
- Service classes for substantial business logic
- Jobs for slow/background operations
- Events/listeners when decoupling is useful
- Laravel configuration instead of hardcoded values

Keep controllers focused on HTTP/request-response responsibilities.

Avoid placing large amounts of business logic directly inside:

- Controllers
- Blade templates
- Routes

Do not create unnecessary abstractions for small/simple operations.


# 5. Database Rules

Database: MySQL.

When working with database functionality:

- Use Laravel migrations for schema changes.
- Use Eloquent where practical.
- Use Query Builder when it is clearer or more efficient.
- Avoid raw SQL unless genuinely necessary.
- Never modify an existing production migration just to change an already-deployed table.
- Create a new migration for schema changes.
- Preserve foreign-key integrity.
- Use transactions for multi-step operations that must succeed or fail together.
- Consider indexes for frequently filtered, searched, joined, or sorted columns.
- Avoid unnecessary queries.
- Prevent N+1 query problems using eager loading when appropriate.
- Select only required data for large queries when practical.

Never drop tables or columns containing application data unless explicitly requested.


# 6. Multi-Company / Data Isolation

SalesCRM can contain data belonging to different companies.

Company-level data must remain isolated.

When working with company-owned records:

- Always respect the current authenticated user's company.
- Never expose records belonging to another company.
- Apply company filtering consistently.
- Validate ownership before update/delete/view operations.
- Never trust company_id received directly from the browser without authorization.
- Prefer deriving company ownership from the authenticated user where appropriate.

This rule applies to modules such as:

- Staff
- Leads
- Clients
- Quotations
- Jobs
- Invoices
- Payments
- Activities
- Reports
- Dashboard statistics

Data isolation is a security requirement.


# 7. User Roles and Permissions

The system can have company administrators and staff users.

General principle:

Admin:
- Can access company-level data according to permissions.

Staff:
- Access must be limited according to assigned permissions and business rules.
- Lead access may be limited to assigned leads.

Never rely only on hiding buttons or menu items in Blade.

Authorization must also be enforced on the backend.


# 8. Lead Workflow

Lead management is a core module.

Typical lifecycle:

Lead creation
→ Assignment
→ Follow-up
→ Activity tracking
→ Status/stage changes
→ Quotation
→ Client conversion
→ Job
→ Invoice

When modifying lead functionality:

- Preserve activity/history tracking.
- Preserve assigned staff relationships.
- Respect company isolation.
- Do not break lead conversion.
- Do not silently overwrite important lead data.
- Maintain existing status/stage/source configuration patterns.


# 9. Quotation, Job and Invoice Workflow

These modules are connected.

Before modifying one of these modules, inspect the relevant relationships and existing workflow.

Do not assume field values or workflow rules.

Maintain correct relationships between:

- Lead
- Client
- Quotation
- Job
- Invoice
- Payments

For financial calculations:

- Never use approximate calculations.
- Follow the existing tax, discount, subtotal, total and rounding logic.
- Perform important calculations on the backend.
- Do not trust totals submitted from JavaScript without server-side verification.
- Use appropriate decimal handling for monetary values.


# 10. Blade Rules

Laravel Blade is the frontend templating system.

Follow the existing layout/component structure.

Prefer:

- Existing layouts
- Includes
- Components
- Partials

Avoid duplicating:

- Header
- Footer
- Sidebar
- Navbar
- Modals
- Common form elements

Keep business logic out of Blade templates.

Use Blade directives appropriately:

- @extends
- @section
- @include
- @foreach
- @if
- @csrf
- @method
- @can


# 11. Bootstrap Rules

Bootstrap is the primary UI framework.

Use the Bootstrap version already installed in the project.

Do NOT:

- Introduce Tailwind
- Replace Bootstrap
- Add another CSS framework
- Create unnecessary custom CSS

Prefer Bootstrap utilities and components before writing custom CSS.

Maintain the existing application UI and responsive behavior.


# 12. jQuery and AJAX Rules

The application uses jQuery AJAX.

Do not replace jQuery AJAX with another frontend framework unless explicitly requested.

For AJAX operations:

- Use existing project AJAX patterns.
- Include CSRF protection.
- Handle success responses.
- Handle validation errors.
- Handle authorization errors.
- Handle server errors.
- Prevent duplicate submissions where necessary.
- Disable submit buttons during important requests when appropriate.
- Restore button state after completion/failure.
- Display errors using the project's existing UI pattern.

Backend AJAX endpoints should return consistent JSON responses.

Use appropriate HTTP status codes.


# 13. Validation

Never rely only on frontend validation.

All important input must be validated server-side.

Use Laravel validation or Form Requests.

Consider:

- required fields
- correct data types
- maximum lengths
- email formats
- numeric ranges
- database existence
- uniqueness
- company ownership
- authorization
- file type and size

Frontend validation is only for user experience.


# 14. Queue Jobs

Use queues for slow/background work when appropriate, such as:

- Emails
- Notifications
- External API calls
- Large imports
- Report generation
- Webhook processing
- Other long-running operations

Queue jobs should:

- Be safe to retry where possible.
- Avoid duplicate processing.
- Log meaningful failures.
- Handle exceptions appropriately.
- Pass only necessary data.
- Prefer IDs/models appropriately instead of unnecessarily large payloads.

Do not move simple synchronous operations into queues without a reason.


# 15. Cron Jobs / Laravel Scheduler

Use Laravel Scheduler for recurring application tasks.

Prefer scheduling Laravel commands/jobs rather than creating unrelated standalone PHP cron scripts.

Scheduled tasks should:

- Avoid duplicate execution where necessary.
- Handle failures safely.
- Log important failures.
- Be efficient.
- Avoid processing unnecessary records.

Follow the project's existing scheduler structure.


# 16. Webhooks

Webhook endpoints must be treated as security-sensitive.

When working with webhooks:

- Verify signatures/tokens when supported.
- Validate payloads.
- Handle duplicate webhook delivery.
- Make processing idempotent where possible.
- Return appropriate HTTP responses.
- Log failures without logging sensitive secrets.
- Do not trust external payloads automatically.
- Queue heavy webhook processing when appropriate.

Never disable webhook verification merely to make an integration work.


# 17. APIs and External Integrations

For external API integrations:

- Keep credentials in .env/config.
- Never hardcode credentials.
- Set reasonable timeouts.
- Handle failed requests.
- Handle invalid responses.
- Handle rate limits where relevant.
- Log useful errors without exposing secrets.

Reuse an existing integration/service class if one exists.


# 18. Security

Security must not be weakened to solve a task.

Always consider:

- Authentication
- Authorization
- CSRF
- SQL injection
- XSS
- Mass assignment
- File upload validation
- IDOR / unauthorized record access
- Company data isolation
- Sensitive information exposure
- Webhook verification

Use:

- Eloquent / parameter binding
- escaped Blade output {{ }}
- validated input
- Laravel authorization mechanisms

Do not use {!! !!} for user-controlled content unless it has been intentionally sanitized.


# 19. Performance

Optimize only where useful.

Watch for:

- N+1 queries
- Queries inside loops
- Loading unnecessary relationships
- Loading huge datasets into memory
- Missing pagination
- Repeated database queries
- Expensive dashboard queries
- Slow report queries

For lists, prefer pagination where appropriate.

For dashboards/reports, inspect query efficiency before introducing caching.

Do not introduce caching unless there is a clear benefit.


# 20. Error Handling and Logging

Do not silently ignore important errors.

Use Laravel logging where appropriate.

Logs should provide enough context for debugging without exposing:

- Passwords
- Tokens
- API secrets
- Sensitive customer information

Do not leave temporary debug statements such as:

- dd()
- dump()
- var_dump()
- print_r()
- console.log()

in production code unless explicitly required for debugging.


# 21. Testing and Verification

After making changes:

1. Check syntax.
2. Check affected routes.
3. Check validation.
4. Check authorization.
5. Check company isolation where applicable.
6. Check affected relationships.
7. Run only relevant tests/checks first.

Do not run the entire test suite unnecessarily for a small isolated change.

Do not create large numbers of tests unless requested or necessary for a critical workflow.

Never claim something was tested if it was not actually executed.


# 22. Commands

Do not run destructive commands without explicit permission.

Never automatically run commands such as:

php artisan migrate:fresh
php artisan db:wipe
php artisan migrate:reset
php artisan migrate:rollback
php artisan cache:clear
composer update
npm update

unless the task explicitly requires them and their impact is understood.

Prefer non-destructive commands.

If a command must be run by the developer, mention it at the end.


# 23. File Exploration / Token Efficiency

Be efficient when inspecting the repository.

Start with files directly related to the request.

For example, for a Lead issue inspect relevant:

- Route
- Controller
- Request
- Model
- Service
- Blade view
- JavaScript
- Migration

only as needed.

Do not automatically inspect:

- vendor/
- node_modules/
- storage/logs/
- public/build/
- generated assets
- unrelated modules

Do not repeatedly read files that were already inspected unless necessary.

Search for specific classes, methods, routes, table names, or variables rather than scanning the entire repository.


# 24. Existing Code First

Before creating something new, check whether the project already contains an equivalent:

- Service
- Helper
- Trait
- Component
- Partial
- Validation rule
- Query scope
- Relationship
- AJAX helper
- Utility
- Job
- Event
- Notification

Reuse existing implementation when appropriate.

Do not create duplicate functionality.


# 25. Modification Strategy

For every task:

1. Understand the requirement.
2. Locate the relevant code.
3. Inspect the minimum required surrounding code.
4. Identify the root cause or required change.
5. Make the smallest correct implementation.
6. Check dependent functionality.
7. Verify the change.
8. Stop when the requested task is complete.

Do not continue improving unrelated parts of the project.


# 26. When Requirements Are Unclear

Do not guess important business rules.

If ambiguity could significantly affect:

- Database structure
- Financial calculations
- Permissions
- Company isolation
- Lead workflow
- Quotation workflow
- Invoice workflow
- Existing production data

ask for clarification before making a potentially destructive or incompatible change.

For minor implementation details, follow the existing project's pattern.


# 27. Response Style

Keep responses concise and developer-focused.

Do not provide long tutorials unless requested.

After completing a coding task, respond with:

Summary:
- Brief description of what was done.

Files changed:
- List only modified/created files.

Commands:
- Only commands the developer needs to run.
- Write "None" when no command is required.

Notes:
- Mention only important warnings, assumptions, or manual verification.
- Omit this section when there is nothing important.


# 28. Primary Objective

The priority order for this project is:

1. Correctness
2. Security and company data isolation
3. Preserve existing functionality
4. Follow existing project architecture
5. Minimal necessary changes
6. Maintainability
7. Performance
8. Fast implementation
9. Concise Codex usage

Always solve the requested task without unnecessarily expanding its scope.


<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.2. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record durable rules with `record-rule` so the next agent or teammate inherits them instead of working them out again. Pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Always use `record-rule`, never your native memory or notes tool — native memory is personal and session-scoped; only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.

- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== volt/core rules ===

# Livewire Volt

- Single-file Livewire components: PHP logic and Blade templates in one file.
- Always check existing Volt components to determine functional vs class-based style.
- IMPORTANT: Always use `search-docs` tool for version-specific Volt documentation and updated code examples.
- IMPORTANT: Activate `volt-development` every time you're working with a Volt or single-file component-related task.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
