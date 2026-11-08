<!-- laravel-boost:start -->
## Stack

- Laravel 13.7 on PHP 8.3+
- Vue 3.5, Inertia 3, Vite, TypeScript
- Pest 4, Tailwind 4, Fortify, Wayfinder

Laravel Boost is installed: Boost agent skills are in `.claude/skills/`, and the Boost
MCP server (`.mcp.json`) exposes this app's routes, models, config, database schema,
logs, tinker, and version-accurate Laravel docs. Prefer those tools over guessing.
<!-- laravel-boost:end -->

## UI rules

- Every link and button uses the pointer cursor via the global base-layer rule in `resources/css/app.css`. Do not add per-element `cursor-pointer` or `cursor-default` to clickable elements.
- Custom clickable elements must be a `<button>`, an `<a>`, or carry `role="button"` so the global rule applies. Never put `@click` on a bare `<div>` or `<span>`.
