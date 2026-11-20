// @rpi-platform/ui -- the pieces every app on the platform shares.
//
//   <PlatformBar appName="Floor Pantry" hubUrl="https://projects.example.edu"
//                user={user} onLogin={...} onLogout={...} />
//   <PlatformFooter />
//
// Apps keep their own header and navigation under the bar; the bar is what makes fifteen
// separate deployments feel like one platform: the same red line, the same way back to the
// landing page, the same account controls in the same corner.

export const BRAND = {
  institution: 'Rensselaer Polytechnic Institute',
  shortName: 'RPI',
  platform: 'RPI Project Platform',
  program: 'Information Technology and Web Science',
  red: '#d6001c',
};

/** A small wordmark: a red block with "RPI" and the platform name. Not the official seal. */
export function RpiMark({ className = '' }) {
  return (
    <span className={`inline-flex items-stretch overflow-hidden rounded-sm text-xs font-bold tracking-wide ${className}`} aria-hidden="true">
      <span className="bg-rpi-red px-1.5 py-0.5 text-white">RPI</span>
    </span>
  );
}

/**
 * The platform bar. `user` is { name, role } or null. `onLogin` / `onLogout` may be a
 * function or an href; the hub itself passes functions, project apps pass the hrefs of
 * the hub-auth routes (/auth/redirect, /auth/logout).
 */
export function PlatformBar({ appName, hubUrl = '/', user = null, onLogin, onLogout, hubLabel = BRAND.platform, children }) {
  const action = (handler, label, primary = false) => {
    const cls = `rounded px-2.5 py-1 text-xs font-semibold transition-colors ${
      primary ? 'bg-rpi-red text-white hover:bg-rpi-red-dark' : 'text-white/90 hover:bg-white/10 hover:text-white'}`;
    return typeof handler === 'string'
      ? <a href={handler} className={cls}>{label}</a>
      : <button type="button" onClick={handler} className={cls}>{label}</button>;
  };

  return (
    <div className="bg-rpi-charcoal text-white">
      <div className="mx-auto flex max-w-6xl flex-wrap items-center gap-x-3 gap-y-1 px-4 py-1.5 text-sm">
        <a href={hubUrl} className="inline-flex items-center gap-2 font-semibold tracking-tight hover:underline">
          <RpiMark />
          <span>{hubLabel}</span>
        </a>
        {appName && (
          <>
            <span aria-hidden="true" className="text-white/40">/</span>
            <span className="font-medium text-white/90">{appName}</span>
          </>
        )}
        <div className="ml-auto flex items-center gap-2">
          {children}
          {user ? (
            <>
              <span className="text-white/80" data-testid="platform-user">
                {user.name}{user.role === 'admin' && <span className="ml-1 text-white/50">(admin)</span>}
              </span>
              {onLogout && action(onLogout, 'Log out')}
            </>
          ) : (
            onLogin && action(onLogin, 'Log in', true)
          )}
        </div>
      </div>
      <div className="rpi-rule" />
    </div>
  );
}

export function PlatformFooter({ children }) {
  return (
    <footer className="mt-16 border-t bg-rpi-paper">
      <div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-6 text-xs text-rpi-gray">
        <p>
          <span className="font-semibold text-rpi-charcoal">{BRAND.institution}</span>
          <span className="mx-2" aria-hidden="true">·</span>{BRAND.program}
        </p>
        <p className="flex flex-wrap gap-4">
          {children}
          <span>Student-built. Not an official Rensselaer service.</span>
        </p>
      </div>
    </footer>
  );
}

/** A status pill for a project: planned, coming soon, live, archived. Words first, colour second. */
export function StatusPill({ status }) {
  const styles = {
    live: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
    planned: 'bg-amber-50 text-amber-900 ring-amber-200',
    coming_soon: 'bg-sky-50 text-sky-900 ring-sky-200',
    archived: 'bg-rpi-mist text-rpi-gray ring-rpi-stone/40',
  };
  return (
    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset ${styles[status] ?? styles.archived}`}>
      {String(status).replace('_', ' ')}
    </span>
  );
}
