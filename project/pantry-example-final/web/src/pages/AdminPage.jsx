// What the RA (admin) sees and a resident does not: every account, and the clean-out
// list -- what has gone off, and what belongs to someone who has moved out.
import { Link } from 'react-router-dom';
import { Denied, Loading, LoadError } from '@/components/PageState.jsx';
import StatusBadge from '@/components/StatusBadge.jsx';
import { listItems, listUsers } from '@/api.js';
import { useLoad } from '@/useLoad.js';
import { useSession } from '@/session.jsx';
import { formatAmount, locationName, ownerName } from '@/format.js';
import { EXPIRED } from '@/rules.js';

function AdminView() {
  // Both requests at once; the page is ready when both are. Expired items come from the
  // API's own filter, so the list is right however many pages the shelf has.
  const page = useLoad(() => Promise.all([listItems({ status: 'expired' }), listItems(), listUsers()]), []);

  if (page.status === 'loading') return <Loading />;
  if (page.status === 'denied') return <Denied>Only the RA can see this page.</Denied>;
  if (page.status !== 'ready') return <LoadError onRetry={page.retry}>Could not load the admin page.</LoadError>;

  const [expired, all, users] = page.data;
  const orphans = all.data.filter((i) => i.owner === null && i.status !== EXPIRED);
  const cleanOut = [...expired.data, ...orphans];

  return (
    <div className="space-y-8">
      <section aria-labelledby="cleanout-heading" className="space-y-3">
        <h2 id="cleanout-heading" className="text-xl font-semibold">Clean-out list</h2>
        <p className="text-sm text-muted-foreground">Gone off, or left by someone who moved out. Open one to remove it.</p>
        {cleanOut.length === 0 ? <p>Nothing to clean out.</p> : (
          <ul className="divide-y rounded-xl border bg-background">
            {cleanOut.map((item) => (
              <li key={item.id} className="flex flex-wrap items-center gap-3 p-3">
                <Link to={`/items/${item.id}`} className="font-medium underline-offset-4 hover:underline">{item.name}</Link>
                <StatusBadge status={item.status} />
                <span className="text-sm text-muted-foreground">
                  {formatAmount(item.amount, item.unit)} · {locationName(item.location)} · {ownerName(item.owner)}
                </span>
              </li>
            ))}
          </ul>
        )}
      </section>

      <section aria-labelledby="users-heading" className="space-y-3">
        <h2 id="users-heading" className="text-xl font-semibold">Residents</h2>
        {/* overflow-x-auto: on a phone the table scrolls inside its box, not the whole page. */}
        <div className="overflow-x-auto rounded-xl border bg-background">
          <table className="w-full text-left text-sm">
            <thead className="border-b bg-muted/50">
              <tr><th scope="col" className="p-3">Name</th><th scope="col" className="p-3">Email</th>
                <th scope="col" className="p-3">Role</th><th scope="col" className="p-3 text-right">Items</th></tr>
            </thead>
            <tbody className="divide-y">
              {users.map((u) => (
                <tr key={u.id}>
                  <td className="p-3">{u.name}</td><td className="p-3">{u.email}</td>
                  <td className="p-3">{u.role === 'admin' ? 'RA (admin)' : 'Resident'}</td>
                  <td className="p-3 text-right">{u.items_count}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </section>
    </div>
  );
}

export default function AdminPage() {
  const { user, status } = useSession();
  return (
    <section className="space-y-5">
      <h1 className="text-2xl font-bold tracking-tight">Admin</h1>
      {status === 'checking' ? <Loading />
        : !user ? <Denied showLogin>Log in as the RA to see this page.</Denied>
        : user.role !== 'admin' ? <Denied>Only the RA can see this page.</Denied>
        : <AdminView />}
    </section>
  );
}
