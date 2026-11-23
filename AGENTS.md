# Poultry Farmer - Codex Project Instructions

## Project
- Project root: D:\Projects\PoultryFarmer
- GitHub: cryptokev051/PoultryFarmer
- Theme source: theme/poultryfarmer/
- Starter plugin: plugins/poultryfarmer-starter/
- Local preview: http://poultryfarmer.local/
- Local WordPress uses Windows directory junctions to the project source.
- The project source is authoritative. Never edit a duplicate theme copy.

## Development principles
- Make the smallest safe change that completes the request.
- Work on the requested objective only.
- Reuse existing WordPress functionality, code and tests.
- Inspect relevant files first. Do not read the entire repository unnecessarily.
- Do not repeat completed investigations without a specific reason.
- Do not refactor unrelated code.
- Prefer native WordPress blocks, Pages, Posts and Categories.
- Keep site content editable in WordPress.
- Keep reusable design in the theme and business logic in plugins.
- Preserve approved branding, layouts and SEO URL structure.

## Testing
- Use Local WordPress as the primary development environment.
- Use the existing Playwright Local test configuration.
- Test changed functionality and one relevant regression case.
- Run wider tests only when changes affect shared components or a release is being prepared.
- Never report untested behavior as PASS.
- Distinguish actual defects from test-environment problems.
- Avoid repeated screenshots and reports unless they add useful evidence.
- Run PHP syntax checks for modified PHP files.
- Use existing build scripts for installable packages.

## Safety
- Never modify production sites, AgriRadar or BusinessPlans.co.ke.
- Never copy real customers, orders or payment credentials.
- Never weaken starter setup safeguards to bypass compatibility checks.
- Back up the Local database before significant changes.
- Obtain explicit authorization before destructive database operations, changing global site settings, or submitting test orders.
- Never enable live payment processing without approval.
- Keep credentials, databases, generated files and local runtime data out of Git.
- Preserve existing owner content and WordPress settings unless the task explicitly authorizes changing them.

## Git and releases
- Use the existing Git repository.
- Check Git status before editing.
- Do not commit, push, merge or force-push without approval.
- Keep theme and plugin ZIPs independent of Local.
- Do not package database files, credentials or development artifacts.

## Communication and efficiency
- Keep responses short and practical.
- Do not produce lengthy reports unless requested.
- Report what changed, what was tested and what remains blocked.
- Do not repeatedly ask questions already answered in project documentation.
- Treat minor non-blocking issues as follow-up work.
- Stop when the requested task is complete.
- If an essential safety check fails, stop and explain the blocker.
