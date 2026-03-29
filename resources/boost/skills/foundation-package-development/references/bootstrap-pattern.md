# Bootstrap Pattern

Foundation consumer packages should follow one predictable bootstrap path.

## Service Provider Shape

Use `Spatie\LaravelPackageTools\PackageServiceProvider`.

- `configurePackage(Package $package): void`
  - Define package name and package resources such as config files.
- `packageRegistered(): void`
  - Bind package services and default implementations.
- `packageBooted(): void`
  - Register the package descriptor through `PlatformPackageRegistrar`.
  - Add explicit boot-time behavior that truly belongs to package boot.

## Descriptor Shape

Create one package descriptor class that implements `PlatformPackage` and only the approved foundation capability contracts the package really exposes.

Keep the descriptor:

- declarative
- stable
- small
- free of hidden runtime side effects

## Registration Pattern

```php
public function packageBooted(): void
{
    $this->app->make(PlatformPackageRegistrar::class)
        ->register(new AccessPlatformPackage);
}
```

Use explicit registration. Do not hide platform registration inside unrelated managers or listeners.

## Design Rules

- Keep host integration outside the package unless explicitly required.
- Keep container bindings separate from descriptor declarations.
- Use package-owned runtime services for synchronization, auditing, caching, and user workflows.
- Keep stable cross-package identifiers technical and predictable.
