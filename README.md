# Technical test — Organisation Enrichment module

Thanks for taking the time to do this. A few ground rules first:

- **Timebox: ~3 hours.** We would much rather see good judgment about what to skip than completeness. Leaving things out deliberately (and telling us why) scores better than rushing everything in.
- **AI tools are permitted.** Note in your SOLUTION.md where you used them. The follow-up interview will go deep on your reasoning either way.
- This is a standard Drupal 11 project with DDEV. Installing the site is **optional** — the work is judged on the module code and its tests, which run without an installed site.

## The task

We use **Kestrel Data**, a third-party vendor outside our control, to look up UK organisation records. Their API docs are in [`docs/api-spec.md`](docs/api-spec.md), and a local stand-in for their service is bundled in `fake-api/` (DDEV starts it automatically).

Build out the `org_enrichment` module (in `web/modules/custom/`) so that it exposes a **service other custom modules can use** to fetch an organisation's enriched profile — its registration details together with its current officers — for a given organisation ID.

Requirements:

1. Other modules consume this through your service; design its public face as you see fit.
2. Repeated lookups shouldn't hammer the vendor — cache appropriately.
3. Your unit tests must pass via `ddev phpunit` (or `vendor/bin/phpunit`) **without a site install and without network access**. Kernel tests are welcome on top if you think they earn their place — the DDEV values are pre-wired in `phpunit.xml.dist`.

That's the whole brief. How you structure it is up to you — that's most of what we want to talk about afterwards. If installing the site and wiring the service into something visible (a route, block, drush command…) helps you work or demonstrate, go for it — it's not required and earns no extra marks by itself.

## Getting started

### In GitHub Codespaces (zero local setup)

If you'd rather not install anything locally, run the exercise in the browser:

1. Click the green **Code** button above the file list, open the **Codespaces** tab, and create a codespace on this branch.
2. Wait for the container to finish building — DDEV is installed for you automatically.
3. In the Codespace terminal, continue with the DDEV steps below (you're already at the repo root, so no `cd` is needed).

The DDEV commands are identical whether you're in a Codespace or on your own machine.

### With DDEV (recommended)

```bash
ddev start
ddev composer install

# The vendor API stand-in is already running. Inside the container it's at
# http://localhost:8000 (matching the API docs), so this works as-is:
ddev exec curl -s -H "X-Api-Key: kd_test_9f3a71" http://localhost:8000/v1/organisations/RC482913

# From your host machine it's at http://org-enrichment-test.ddev.site:8080

# Run the test suite (one smoke test passes out of the box):
ddev phpunit

# Optional — only if you want a working site/UI:
ddev drush site:install -y
ddev launch
```

### Without DDEV

PHP 8.3+ and Composer locally:

```bash
composer install

# Run the vendor API stand-in (keep it running in a second terminal):
php -S localhost:8000 fake-api/server.php

# Try it:
curl -H "X-Api-Key: kd_test_9f3a71" http://localhost:8000/v1/organisations/RC482913

# Run the test suite:
vendor/bin/phpunit
```

PHPStan is wired up too if you want it: `vendor/bin/phpstan` (or `ddev exec vendor/bin/phpstan`).

## What to submit

A git repo (or zip, minus `vendor/` and `web/core/`) containing:

- Your module code and tests.
- **SOLUTION.md** — short and honest: the decisions you made, trade-offs, anything you deliberately skipped, and what you'd do next with more time. This document matters as much as the code.

## What we look at

How you design the module's boundaries, how you make it testable, the judgment calls you made inside the timebox, and how clearly you communicate them. There is no single right answer.
