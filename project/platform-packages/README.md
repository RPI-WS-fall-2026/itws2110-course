# Platform packages

The two shared packages a project needs to run on the course platform
(https://ta.biggermatrix.com). Copy both into a `packages/` folder at the root of your team
repository:

```bash
mkdir -p packages
cp -r ../itws2110-course/project/platform-packages/hub-auth-laravel packages/
cp -r ../itws2110-course/project/platform-packages/rpi-ui packages/
```

| Package | What it is |
|---|---|
| [`hub-auth-laravel/`](hub-auth-laravel/) | `rpi-platform/hub-auth`: log a Laravel app in through the platform hub (OAuth 2.0 authorization code with PKCE). Three routes, one migration |
| [`rpi-ui/`](rpi-ui/) | `@rpi-platform/ui`: RPI branding, the platform bar and footer |

Use them behind the `PRD` switch, so your own login from Homework 9 keeps working everywhere
except on the platform. The worked example is [`../pantry-example-platform/`](../pantry-example-platform/);
the step-by-step guide is *Preparing your app* (`docs/PREPARING_YOUR_APP.md`) in the platform
repository.

These are copies of `packages/` in the platform repository (version 0.1.0). Do not edit them
in your repository: if something needs to change, tell the instructor.
