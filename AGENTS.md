<laravel-boost-guidelines>
=== .ai/ forbidden-paths rules ===

# Forbidden paths

Do not read, search, edit, summarize, refactor, or use as reference any files inside deprecated directories.

Forbidden directory names include:

- `deprecated/`
- `depreciated/`
- `legacy/`
- `old/`
- `archive/`
- `archives/`
- `_deprecated/`
- `_legacy/`

If a task appears to require information from one of these directories, ask for confirmation before accessing them.

Never use deprecated files as examples for architecture, naming, patterns, services, controllers, tests, routes, models, Vue components, or database structure.

=== .ai/php-laravel rules ===

## General code instructions

- Do not generate obvious comments above methods, classes, or code blocks.
- Do not add docblocks for variables unless they are needed to help static analysis or clarify a non-obvious type, for example:
  ```php
  /** @var \App\Models\User $currentUser */
  ```
- Generate comments only when they explain why something was written that way.
- For new features, generate Pest automated tests.
- For library documentation, always use Laravel Boost `search-docs` first.
- If the needed library documentation is not available in Laravel Boost `search-docs`, use Context7 automatically to resolve the library ID and fetch the docs.
- If CSS, JavaScript, Vue files, or Tailwind classes are changed, run the frontend build after the changes are finished:
  ```bash
  ./vendor/bin/sail npm run build
  ```

---

## PHP instructions

- Use `match` instead of `switch` whenever possible.
- Generate Enums in `app/Enums`, unless instructed differently.
- If a column value comes from an Enum, use the Enum value as the default in the migration.
- If a model column represents an Enum, cast it to the Enum type in the model.
- Do not create temporary variables like `$currentUser = auth()->user()` when the variable is used only once.
- Use Enum cases or Enum values instead of hardcoded strings whenever an Enum already exists for that domain concept.
- Apply the same Enum preference in controllers, models, migrations, seeders, tests, routes, middleware, configs, and UI defaults.

---

## Laravel instructions

- Always run Laravel-related CLI commands using Sail.
- Never assume global PHP, Composer, or Node execution.

Examples:

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan test
./vendor/bin/sail artisan make:model User
./vendor/bin/sail composer install
./vendor/bin/sail npm run build
```

- Do not chain multiple migration-generating commands with `&&` or `;`.
- Run migration-generating commands separately to avoid identical timestamps.
- Register Eloquent Observers in models using PHP attributes, not in `AppServiceProvider`.

Example:

```php
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    //
}
```

- Keep controllers slim.
- Put reusable or complex business logic in Service classes.
- Use Laravel helpers instead of importing facades when the helper is clear.

Examples:

```php
auth()->id();
redirect()->route('dashboard');
str()->slug($title);
```

- Do not use `whereKey()` or `whereKeyNot()`.
- Prefer explicit fields.

Example:

```php
User::where('id', '!=', auth()->id())->get();
```

- Do not add `::query()` when running simple Eloquent `create()` statements.

Good:

```php
User::create($data);
```

Avoid:

```php
User::query()->create($data);
```

- When adding columns in a migration, update the model's `$fillable` array.
- Do not create controllers with only one method that only returns a Blade view.
- For Blade-only static pages, use `Route::view()`.
- In Blade files, use Laravel directives for form state:
  - Use `@selected()` instead of manually rendering `selected`.
  - Use `@checked()` instead of manually rendering `checked`.
  - Use `@session()` instead of `@if(session())` for flash messages.

---

## Service classes

- Create Service classes in `app/Services/`.
- Use Service classes to encapsulate reusable business logic.
- Services must not contain presentation logic such as Inertia responses, views, redirects, or flash messages.
- Services should return data, models, DTO-like arrays, or throw exceptions.
- Services must be independently testable.
- Avoid coupling Services directly to `request()`, `session()`, or `auth()`.
- Pass required values into Services as parameters.
- If a Service is used in only one controller method, inject it directly into that method.
- If a Service is used in multiple controller methods, inject it in the constructor.

Example:

```php
class PostController
{
    public function store(StorePostRequest $request, PostService $postService): RedirectResponse
    {
        $postService->create(
            data: $request->validated(),
            userId: auth()->id(),
        );

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post created successfully.');
    }
}
```

---

## Model construction rules

- Models must define `$fillable` correctly for all mass-assignable attributes.
- When adding new columns through a migration, update the corresponding model's `$fillable` array.
- Relationships must follow Laravel naming conventions, such as `user()`, `orders()`, and `profile()`.
- Relationship methods must use correct return types, such as `HasMany`, `BelongsTo`, and `HasOne`.
- Define inverse relationships when applicable.
- Do not assume foreign key names.
- Explicitly define foreign keys when they do not follow Laravel conventions.
- If a column represents a domain concept backed by an Enum, cast it using `$casts`.

Example:

```php
use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## Inertia Vue.js instructions

- This project uses Inertia.js with Vue.js.
- Do not introduce Livewire, Livewire Volt, Livewire directives, or Livewire form objects in this project.
- Use Inertia pages for interactive screens.
- Use Laravel routes and controllers for Inertia pages, form submissions, redirects, downloads, APIs, and server-side orchestration.
- Pages must live in `resources/js/Pages/`.
- Shared Vue components must live in `resources/js/Components/`.
- Layouts must live in `resources/js/Layouts/`.
- Controllers should usually validate requests, call Services, and return an Inertia response or redirect.
- Do not place interactive page files in `resources/views/pages/`.

---

## Routes and pages

- Use standard Laravel routes with controllers or route closures returning Inertia pages.
- Prefer controllers when the page needs data, permissions, filters, actions, or business flow.
- For simple static pages that only render an Inertia component, a route closure is acceptable.

Example:

```php
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/posts', [PostController::class, 'index'])
    ->name('posts.index');

Route::get('/about', fn () => Inertia::render('About'))
    ->name('about');
```

---

## Controllers with Inertia

- Use `Inertia::render()` to return Inertia pages.
- Page names must match the Vue file path inside `resources/js/Pages`.
- Avoid putting business rules directly in controllers.
- Move growing or reusable logic into Service classes.
- Use Form Requests for reusable or non-trivial validation.

Example:

```php
use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;

class PostController
{
    public function index(): Response
    {
        return Inertia::render('Posts/Index', [
            'posts' => Post::latest()->paginate(10),
        ]);
    }
}
```

---

## Forms

- Use Inertia's `useForm()` helper for Vue form state.
- Validate on the server with Laravel Form Requests when validation is reusable or non-trivial.
- Use validation errors returned by Inertia instead of manually creating custom error handling unless there is a specific reason.
- Keep large forms organized by extracting reusable form sections into Vue components.
- Use `processing`, `errors`, `isDirty`, and other Inertia form state helpers instead of creating duplicate state manually.

Example:

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    title: '',
    content: '',
})

const submit = () => {
    form.post(route('posts.store'))
}
</script>

<template>
    <form @submit.prevent="submit">
        <input v-model="form.title" type="text">

        <p v-if="form.errors.title">
            {{ form.errors.title }}
        </p>

        <textarea v-model="form.content" />

        <p v-if="form.errors.content">
            {{ form.errors.content }}
        </p>

        <button type="submit" :disabled="form.processing">
            Save
        </button>
    </form>
</template>
```

---

## Form Requests

- Use Form Request classes for validation when creating or updating resources.
- Keep authorization and validation rules inside the Form Request when appropriate.
- Do not duplicate the same validation rules across multiple controllers.

Example:

```php
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
        ];
    }
}
```

---

## Vue components

- Use Vue 3 with `<script setup>` by default.
- Keep pages focused on page-level state, layout, and orchestration.
- Extract reusable UI pieces into components under `resources/js/Components/`.
- Avoid duplicating form fields, buttons, modals, tables, alerts, and empty states across pages.
- Use props with explicit defaults when useful.
- Avoid deeply nested component state when data can be passed clearly through props and events.

Example:

```vue
<script setup>
defineProps({
    post: {
        type: Object,
        required: true,
    },
})
</script>
```

---

## Inertia navigation

- Use Inertia's `<Link>` component for internal navigation instead of plain `<a>` tags.
- Use `router.visit()`, `router.get()`, `router.post()`, `router.put()`, `router.patch()`, or `router.delete()` when navigation or actions need to be triggered from JavaScript.
- Preserve state and scroll intentionally when filtering, searching, or paginating.
- Keep filter state synced with the URL when users may share, reload, or return to the page.

Example:

```vue
<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <Link :href="route('posts.index')">
        Posts
    </Link>
</template>
```

Example with filters:

```vue
<script setup>
import { router } from '@inertiajs/vue3'

const applyFilters = (filters) => {
    router.get(route('posts.index'), filters, {
        preserveState: true,
        replace: true,
    })
}
</script>
```

---

## Flash messages and shared props

- Set flash messages in Laravel using `with()`.
- Expose flash messages through shared Inertia props, usually in `HandleInertiaRequests`.
- Display flash messages in Vue layouts or components.
- Keep shared props small and intentional.
- Do not share large datasets globally.
- Shared props are appropriate for authenticated user data, flash messages, app metadata, and small permission maps.

Example:

```php
return redirect()
    ->route('posts.index')
    ->with('success', 'Post updated successfully.');
```

---

## Authorization

- Use Policies for model authorization.
- Do not rely only on hiding buttons in Vue.
- Always authorize server-side in controllers, Form Requests, Policies, or Services where appropriate.
- UI permission props may be passed to Vue to improve the interface, but they are not the source of truth.

---

## Pagination, filters, and search

- Use Laravel pagination and pass paginated data to Inertia pages.
- Prefer query string parameters for filters and search.
- Use Inertia visits with `preserveState` and `replace` when updating filters.
- Keep filter names consistent between the request, controller, and Vue page.

---

## Testing instructions

- For new features, generate Pest automated tests.
- Before writing tests, check the database schema.
- Verify which columns have defaults.
- Verify which columns are nullable.
- Verify foreign key names.
- Read the model file before assuming relationship names.
- Confirm relationship return types and related models.
- Do not assume `user_id` means the relationship is named `user()`.
- Test realistic states based on the actual schema.
- When testing form submissions that redirect back with errors, assert that old input is preserved using `assertSessionHasOldInput()`.
- Test that the correct Inertia page is returned.
- Test important props passed to the page.
- Test form submissions, validation errors, redirects, authorization, and persistence.
- Use Laravel's Inertia testing helpers when available.

Example:

```php
use Inertia\Testing\AssertableInertia as Assert;

it('renders the posts index page', function () {
    $this->get(route('posts.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Posts/Index')
            ->has('posts')
        );
});
```

---

## Things to avoid

- Do not introduce Livewire in this project.
- Do not use Livewire directives such as `wire:model`, `wire:submit`, or `wire:navigate`.
- Do not create files in `app/Livewire/` or `app/Livewire/Forms/`.
- Do not use `Route::livewire()`.
- Do not place interactive Inertia page files in `resources/views/pages/`.
- Do not put business logic inside Vue components when it belongs in Laravel Services.
- Do not duplicate framework documentation here unless it represents a personal coding preference for this project.

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v2
- laravel/ai (AI) - v0
- laravel/framework (LARAVEL) - v13
- laravel/horizon (HORIZON) - v5
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- tightenco/ziggy (ZIGGY) - v2
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- laravel/telescope (TELESCOPE) - v5
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/vue3 (INERTIA_VUE) - v2
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

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

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

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

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v2

- Use all Inertia features from v1 and v2. Check the documentation before making changes to ensure the correct approach.
- New features: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.

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

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

</laravel-boost-guidelines>
