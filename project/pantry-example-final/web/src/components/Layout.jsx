// The frame around every page: the header with the logged-in state, and the page below.
// <Outlet /> is where React Router puts the page for the current address.
import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import { useSession } from '@/session.jsx';
import { can } from '@/rules.js';

const navClass = ({ isActive }) =>
  `rounded-md px-2 py-1 text-sm ${isActive ? 'bg-muted font-medium text-foreground' : 'text-muted-foreground hover:text-foreground'}`;

export default function Layout() {
  const { user, logout } = useSession();
  const navigate = useNavigate();

  async function handleLogout() {
    await logout();
    navigate('/');
  }

  return (
    <div className="min-h-screen bg-stone-50">
      <header className="border-b bg-background">
        {/* flex-wrap: on a 375px phone the account controls drop to a second row instead
            of pushing the page sideways (HW3, breakpoints). */}
        <div className="mx-auto flex max-w-5xl flex-wrap items-center gap-x-4 gap-y-2 px-4 py-3">
          <Link to="/" className="text-lg font-semibold tracking-tight">Floor Pantry</Link>
          <nav aria-label="Main" className="flex items-center gap-1">
            <NavLink to="/" end className={navClass}>Shelf</NavLink>
            {can(user, 'create') && <NavLink to="/items/new" className={navClass}>Add an item</NavLink>}
            {user?.role === 'admin' && <NavLink to="/admin" className={navClass}>Admin</NavLink>}
          </nav>
          <div className="ml-auto flex items-center gap-2 text-sm">
            {user ? (
              <>
                <span data-testid="signed-in-as">
                  <span className="font-medium">{user.name}</span>
                  {user.role === 'admin' && <span className="ml-1 text-muted-foreground">(RA)</span>}
                </span>
                <Button variant="outline" size="sm" onClick={handleLogout}>Log out</Button>
              </>
            ) : (
              <>
                <Button asChild variant="ghost" size="sm"><Link to="/login">Log in</Link></Button>
                <Button asChild size="sm"><Link to="/register">Register</Link></Button>
              </>
            )}
          </div>
        </div>
      </header>
      <main className="mx-auto max-w-5xl px-4 py-6">
        <Outlet />
      </main>
    </div>
  );
}
