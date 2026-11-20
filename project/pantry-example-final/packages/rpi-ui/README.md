# @rpi-platform/ui

RPI branding for every app on the platform: Tailwind 4 theme tokens (`bg-rpi-red`, `text-rpi-charcoal`, …), the shadcn variables pointed at the Rensselaer palette, and three shared components: `PlatformBar`, `PlatformFooter`, `StatusPill`.

## Use it in an app

In this repository the apps depend on it by path:

```json
"dependencies": { "@rpi-platform/ui": "file:../../packages/rpi-ui" }
```

In `src/index.css`, after Tailwind and the shadcn base:

```css
@import "@rpi-platform/ui/theme.css";
@source "../node_modules/@rpi-platform/ui/src";
```

Then:

```jsx
import { PlatformBar, PlatformFooter } from '@rpi-platform/ui';

<PlatformBar appName="Floor Pantry" hubUrl={HUB_URL} user={user}
             onLogin="/auth/redirect" onLogout="/auth/logout" />
```

A project in its own repository copies this folder in (`packages/rpi-ui` → `vendor-ui/`) or installs it from a published package. It has no build step; it is plain JSX and CSS.
