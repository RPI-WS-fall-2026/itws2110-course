// The states every loading page shares (MIDTERM_APPENDIX.md section B). One component
// each, so "loading" and "error" look and read the same on every page.
import { Link } from 'react-router-dom';
import { Button } from '@/components/ui/button';

export function Loading({ children = 'Loading…' }) {
  // role="status": a screen reader announces it without moving focus.
  return <p role="status" className="py-12 text-center text-muted-foreground">{children}</p>;
}

export function LoadError({ children, onRetry }) {
  return (
    <div role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-red-900">
      <p>{children}</p>
      {onRetry && <Button variant="outline" className="mt-3" onClick={onRetry}>Try again</Button>}
    </div>
  );
}

/** For a 401 or 403: say why, and offer the way forward. */
export function Denied({ children, showLogin = false }) {
  return (
    <div className="rounded-xl border bg-muted/50 p-6">
      <p>{children}</p>
      <div className="mt-3 flex gap-3">
        {showLogin && <Button asChild><Link to="/login">Log in</Link></Button>}
        <Button asChild variant="outline"><Link to="/">Back to the shelf</Link></Button>
      </div>
    </div>
  );
}
