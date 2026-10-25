// Key page 2 (pitch question 4): one item -- how much, where, when it goes
// off, whose it is, and the actions this person is allowed to take on it.
//
// Where it comes from:
//   - the four outcomes (loading, ready, not found, error): MIDTERM_APPENDIX.md section B,
//     "the same pattern on a detail page"
//   - "Use some": inclass/wk3/pantry -- the consume form on Pantry's page, and the rule the
//     week-3 class found missing, "you cannot use more than you have" (rules.consumeStock)
//   - which buttons appear: rules.can(), the "Who may do what" table. Hiding a button is
//     only a convenience; the API refuses the request anyway (403).
import { useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import StatusBadge from '@/components/StatusBadge.jsx';
import { Loading, LoadError } from '@/components/PageState.jsx';
import { deleteItem, getItem, updateItem } from '@/api.js';
import { useLoad } from '@/useLoad.js';
import { useSession } from '@/session.jsx';
import { can, consumeStock, RuleError } from '@/rules.js';
import { formatAmount, formatDate, formatDaysLeft, locationName, ownerName } from '@/format.js';

const backLink = <Link to="/" className="text-sm text-muted-foreground hover:text-foreground">← Back to the shelf</Link>;

/** "Use some": works out the new amount with the Stock rule, then saves it with PUT. */
function UseSome({ item, onUpdated }) {
  const [quantity, setQuantity] = useState('');
  const [message, setMessage] = useState(null);
  const [saving, setSaving] = useState(false);

  async function handleSubmit(event) {
    event.preventDefault();
    setSaving(true);
    try {
      const amount = consumeStock(item.amount, Number(quantity));   // throws "Not enough in stock"
      onUpdated(await updateItem(item.id, { amount }));
      setQuantity('');
      setMessage(null);
    } catch (err) {
      setMessage(err instanceof RuleError ? err.message : err.errors?.amount?.[0] ?? err.message);
    } finally {
      setSaving(false);
    }
  }

  return (
    <form onSubmit={handleSubmit} noValidate className="space-y-1.5">
      <label htmlFor="use-quantity" className="block text-sm font-medium">Use some</label>
      <div className="flex gap-2">
        <Input id="use-quantity" inputMode="decimal" className="h-9 w-28" value={quantity}
          onChange={(e) => setQuantity(e.target.value)} aria-invalid={Boolean(message)}
          aria-describedby={message ? 'use-error' : undefined} />
        <span className="self-center text-sm text-muted-foreground">{item.unit}</span>
        <Button type="submit" variant="secondary" disabled={saving}>Use</Button>
      </div>
      {message && <p id="use-error" className="text-sm text-destructive">{message}</p>}
    </form>
  );
}

/** Delete, with a second click to confirm -- the food is real, the record should not vanish by accident. */
function RemoveItem({ item }) {
  const navigate = useNavigate();
  const [confirming, setConfirming] = useState(false);
  const [message, setMessage] = useState(null);

  async function remove() {
    try {
      await deleteItem(item.id);
      navigate('/', { state: { flash: `Removed ${item.name} from the shelf.` } });
    } catch (err) {
      setMessage(err.message);
    }
  }

  if (!confirming) {
    return <Button variant="destructive" onClick={() => setConfirming(true)}>Remove from shelf</Button>;
  }
  return (
    <div role="group" aria-label="Confirm removal" className="flex flex-wrap items-center gap-2 rounded-lg border border-red-200 bg-red-50 p-3">
      <span className="text-sm text-red-900">Remove {item.name} for good?</span>
      <Button variant="destructive" onClick={remove}>Yes, remove it</Button>
      <Button variant="ghost" onClick={() => setConfirming(false)}>Cancel</Button>
      {message && <p role="alert" className="w-full text-sm text-destructive">{message}</p>}
    </div>
  );
}

export default function ItemPage() {
  const { id } = useParams();                     // /items/:id -- the appendix's useParams()
  const { user } = useSession();
  // [id]: load again if the page is showing a different item.
  const page = useLoad(() => getItem(id), [id]);

  if (page.status === 'loading') return <>{backLink}<Loading /></>;
  if (page.status === 'not-found') {
    return (
      <div className="space-y-4">
        {backLink}
        <p className="rounded-xl border bg-background p-6">There is no item {id}. It may have been removed on clean-out day.</p>
      </div>
    );
  }
  if (page.status !== 'ready') {
    return <div className="space-y-4">{backLink}<LoadError onRetry={page.retry}>Could not load this item.</LoadError></div>;
  }

  const item = page.data;
  const mayUpdate = can(user, 'update', item);
  const mayDelete = can(user, 'delete', item);

  return (
    <article className="space-y-6">
      {backLink}
      <header className="flex flex-wrap items-center gap-3">
        <h1 className="text-3xl font-bold tracking-tight">{item.name}</h1>
        <StatusBadge status={item.status} />
      </header>

      {/* A description list: label/value pairs, the right element for "Amount: 1 L". */}
      <dl className="grid gap-x-8 gap-y-4 rounded-xl border bg-background p-5 sm:grid-cols-2">
        <div><dt className="text-sm text-muted-foreground">Amount</dt><dd className="text-lg">{formatAmount(item.amount, item.unit)}</dd></div>
        <div><dt className="text-sm text-muted-foreground">Where</dt><dd className="text-lg">{locationName(item.location)}</dd></div>
        <div>
          <dt className="text-sm text-muted-foreground">Best before</dt>
          <dd className="text-lg">{item.best_before ? formatDate(item.best_before) : '—'}</dd>
          <dd className="text-sm text-muted-foreground">{formatDaysLeft(item.days_left)}</dd>
        </div>
        <div><dt className="text-sm text-muted-foreground">Owner</dt><dd className="text-lg">{ownerName(item.owner)}</dd></div>
        <div><dt className="text-sm text-muted-foreground">Added</dt><dd>{formatDate(item.created_at)}</dd></div>
        <div><dt className="text-sm text-muted-foreground">Last changed</dt><dd>{formatDate(item.updated_at)}</dd></div>
        {item.notes && <div className="sm:col-span-2"><dt className="text-sm text-muted-foreground">Notes</dt><dd>{item.notes}</dd></div>}
      </dl>

      {(mayUpdate || mayDelete) ? (
        <section aria-label="Actions" className="flex flex-col gap-4 rounded-xl border bg-background p-5 sm:flex-row sm:items-end sm:justify-between">
          {mayUpdate && <UseSome item={item} onUpdated={(updated) => page.setData(updated)} />}
          <div className="flex flex-wrap gap-2">
            {mayUpdate && <Button asChild variant="outline"><Link to={`/items/${item.id}/edit`}>Edit</Link></Button>}
            {mayDelete && <RemoveItem item={item} />}
          </div>
        </section>
      ) : (
        <p className="text-sm text-muted-foreground">
          {!user ? 'Log in to change your own items.'
            : item.owner ? `Only ${item.owner.name} or the RA can change this item.`
            : 'Only the RA can change this item: its owner has moved out.'}
        </p>
      )}
    </article>
  );
}
