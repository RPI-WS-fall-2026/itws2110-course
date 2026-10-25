// The frame around every page: the header, and the page below.
// <Outlet /> is where React Router puts the page for the current address.
import { Link, NavLink, Outlet } from 'react-router-dom';
import { useSession } from '@/session.jsx';
import { can } from '@/rules.js';

const navClass = ({ isActive }) =>
  `rounded-md px-2 py-1 text-sm ${isActive ? 'bg-muted font-medium text-foreground' : 'text-muted-foreground hover:text-foreground'}`;

export default function Layout() {
  const { user } = useSession();

  return (
    <div className="min-h-screen bg-stone-50">
      <header className="border-b bg-background">
        {/* flex-wrap: on a 375px phone the name drops to a second row instead of pushing
            the page sideways (HW3, breakpoints). */}
        <div className="mx-auto flex max-w-5xl flex-wrap items-center gap-x-4 gap-y-2 px-4 py-3">
          <Link to="/" className="text-lg font-semibold tracking-tight">Floor Pantry</Link>
          <nav aria-label="Main" className="flex items-center gap-1">
            <NavLink to="/" end className={navClass}>Shelf</NavLink>
            {can(user, 'create') && <NavLink to="/items/new" className={navClass}>Add an item</NavLink>}
          </nav>
          {/* No log in until week 12: the prototype acts as one person (see api.js, ?as=). */}
          <p className="ml-auto text-sm text-muted-foreground" data-testid="acting-as">
            {user
              ? <>Acting as <span className="font-medium text-foreground">{user.name}</span>{user.role === 'admin' && ' (RA)'}</>
              : 'Not logged in'}
          </p>
        </div>
      </header>
      <main className="mx-auto max-w-5xl px-4 py-6">
        <Outlet />
      </main>
    </div>
  );
}
