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
