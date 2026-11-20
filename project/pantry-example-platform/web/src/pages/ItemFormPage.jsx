// The form's two pages: /items/new (create) and /items/:id/edit (update). One component,
// because they differ only in where the starting values come from and which call saves.
//
// The form is on its own page, so it does not need the appendix's `onCreated`: after a
// save we go to the item's page, which loads it again from api.js (appendix section C,
// last paragraph).
import { Link, useNavigate, useParams } from 'react-router-dom';
import ItemForm from '@/components/ItemForm.jsx';
import { Denied, Loading, LoadError } from '@/components/PageState.jsx';
import { createItem, getItem, updateItem } from '@/api.js';
import { useLoad } from '@/useLoad.js';
import { useSession } from '@/session.jsx';
import { can } from '@/rules.js';

function Frame({ title, children }) {
  return (
    <section className="mx-auto max-w-xl space-y-5">
      <Link to="/" className="text-sm text-muted-foreground hover:text-foreground">← Back to the shelf</Link>
      <h1 className="text-2xl font-bold tracking-tight">{title}</h1>
      {children}
    </section>
  );
}

export function NewItemPage() {
  const { user, status } = useSession();
  const navigate = useNavigate();

  if (status === 'checking') return <Frame title="Add an item"><Loading /></Frame>;
  if (!can(user, 'create')) {
    return <Frame title="Add an item"><Denied showLogin>Log in to add what you put in the kitchen.</Denied></Frame>;
  }

  async function save(values) {
    const item = await createItem(values);      // throws the 422 the form shows
    navigate(`/items/${item.id}`);
  }

  return (
    <Frame title="Add an item">
      <div className="rounded-xl border bg-background p-5">
        <ItemForm onSubmit={save} submitLabel="Add to shelf" cancelTo="/" />
      </div>
    </Frame>
  );
}

export function EditItemPage() {
  const { id } = useParams();
  const { user, status } = useSession();
  const navigate = useNavigate();
  const page = useLoad(() => getItem(id), [id]);

  if (status === 'checking' || page.status === 'loading') return <Frame title="Edit item"><Loading /></Frame>;
  if (page.status === 'not-found') return <Frame title="Edit item"><Denied>There is no item {id}.</Denied></Frame>;
  if (page.status !== 'ready') return <Frame title="Edit item"><LoadError onRetry={page.retry}>Could not load this item.</LoadError></Frame>;
  if (!user) return <Frame title="Edit item"><Denied showLogin>Log in to edit your items.</Denied></Frame>;
  if (!can(user, 'update', page.data)) {
    return <Frame title="Edit item"><Denied>Only the owner or the RA can change {page.data.name}.</Denied></Frame>;
  }

  async function save(values) {
    await updateItem(id, values);
    navigate(`/items/${id}`);
  }

  return (
    <Frame title={`Edit ${page.data.name}`}>
      <div className="rounded-xl border bg-background p-5">
        {/* key: if the id changes, start a fresh form instead of keeping the old item's
            typing (week 6, drill 12: reset with a key). */}
        <ItemForm key={page.data.id} initial={page.data} onSubmit={save} submitLabel="Save changes" cancelTo={`/items/${id}`} />
      </div>
    </Frame>
  );
}
