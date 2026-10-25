// Key page 1 (pitch question 4): the shelf -- everything in the floor kitchen,
// what goes off first at the top.
//
// Where it comes from:
//   - the grid of cards: HW4's MyPantry (products.map -> one card each, key = the id)
//   - the responsive columns: HW3 (one column on a phone, md:grid-cols-2, lg:grid-cols-3)
//   - the filters: HW5's All / Active / Done -- one value per filter, not a boolean per
//     button (week 6, drill 9). They live in the address (?location=fridge), so the back
//     button and a shared link keep them, and they are sent to the API as query
//     parameters, because with pagination only the server can filter all the rows.
//   - loading / error / empty: MIDTERM_APPENDIX.md section B
import { Link, useLocation, useSearchParams } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import ItemCard from '@/components/ItemCard.jsx';
import { Loading, LoadError } from '@/components/PageState.jsx';
import { listItems } from '@/api.js';
import { useLoad } from '@/useLoad.js';
import { useSession } from '@/session.jsx';
import { can, LOCATIONS } from '@/rules.js';
import { locationName } from '@/format.js';

const LOCATION_OPTIONS = [['', 'Everywhere'], ...LOCATIONS.map((l) => [l, locationName(l)])];
const STATUS_OPTIONS = [['', 'Any date'], ['due-soon', 'Due soon'], ['expired', 'Expired']];

/** A row of buttons that sets one search parameter. aria-pressed tells a screen reader which is on. */
function FilterGroup({ label, name, options, value, onChange }) {
  return (
    <div role="group" aria-label={label} className="flex flex-wrap gap-1">
      {options.map(([optionValue, text]) => (
        <Button key={text} size="sm" variant={value === optionValue ? 'default' : 'outline'}
          aria-pressed={value === optionValue} onClick={() => onChange(name, optionValue)}>
          {text}
        </Button>
      ))}
    </div>
  );
}

export default function ShelfPage() {
  const { user } = useSession();
  const flash = useLocation().state?.flash;       // "Removed Milk from the shelf." after a delete
  const [params, setParams] = useSearchParams();
  const location = params.get('location') ?? '';
  const status = params.get('status') ?? '';

  // [location, status]: load again whenever a filter changes.
  const shelf = useLoad(() => listItems({ location, status }), [location, status]);

  function setFilter(name, value) {
    setParams((prev) => {
      const next = new URLSearchParams(prev);
      if (value) next.set(name, value); else next.delete(name);
      return next;
    });
  }

  const filtered = Boolean(location || status);

  return (
    <section aria-labelledby="shelf-heading" className="space-y-5">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 id="shelf-heading" className="text-2xl font-bold tracking-tight">What's in the kitchen</h1>
          <p className="text-muted-foreground">Third-floor kitchen. What goes off first is at the top.</p>
        </div>
        {can(user, 'create')
          ? <Button asChild><Link to="/items/new">Add an item</Link></Button>
          : <p className="text-sm text-muted-foreground">Log in to add what you put in.</p>}
      </div>

      {flash && <p role="status" className="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-emerald-900">{flash}</p>}

      <div className="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:gap-4">
        <FilterGroup label="Where" name="location" options={LOCATION_OPTIONS} value={location} onChange={setFilter} />
        <FilterGroup label="Best before" name="status" options={STATUS_OPTIONS} value={status} onChange={setFilter} />
      </div>

      {shelf.status === 'loading' && <Loading>Loading the shelf…</Loading>}

      {shelf.status === 'error' && (
        <LoadError onRetry={shelf.retry}>Could not load the shelf. Try again in a minute.</LoadError>
      )}

      {shelf.status === 'ready' && (
        <>
          <p className="text-sm text-muted-foreground" aria-live="polite">
            {shelf.data.meta.total} {shelf.data.meta.total === 1 ? 'item' : 'items'}
          </p>
          {shelf.data.data.length === 0 ? (
            // The empty state, which is not an error: the list loaded and has nothing in it.
            <div className="rounded-xl border border-dashed bg-background p-8 text-center">
              {filtered ? (
                <>
                  <p>Nothing matches these filters.</p>
                  <Button variant="link" onClick={() => setParams({})}>Show everything</Button>
                </>
              ) : <p>The shelf is empty. Add the first thing you put in the kitchen.</p>}
            </div>
          ) : (
            <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              {shelf.data.data.map((item) => (
                <li key={item.id}><ItemCard item={item} /></li>
              ))}
            </ul>
          )}
        </>
      )}
    </section>
  );
}
