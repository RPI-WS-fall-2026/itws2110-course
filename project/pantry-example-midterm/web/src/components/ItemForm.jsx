// The add / edit form for an item. MIDTERM_APPENDIX.md section C, for six fields.
//
// What it does not do: check the rules itself. The messages come from the API's 422
// (the fake one now, Laravel's in week 12), so there is one copy of each rule -- in
// rules.js now, in a Laravel Form Request later -- and the form cannot disagree with it.
//
// It does not know whether it is adding or editing, or where the data goes: the page
// passes `initial` and `onSubmit`, so one form serves both pages.
import { useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import Field from './Field.jsx';
import { LOCATIONS, UNITS } from '@/rules.js';
import { locationName } from '@/format.js';

const FIELDS = ['name', 'amount', 'unit', 'location', 'best_before', 'notes'];

/** Inputs hold strings; an item from the API has numbers and nulls. */
function toFormValues(item) {
  return {
    name: item?.name ?? '',
    amount: item ? String(item.amount) : '',
    unit: item?.unit ?? 'items',
    location: item?.location ?? 'fridge',
    best_before: item?.best_before ?? '',
    notes: item?.notes ?? '',
  };
}

export default function ItemForm({ initial, onSubmit, submitLabel = 'Save', cancelTo }) {
  // One object for all six fields, so one change handler serves every input.
  const [values, setValues] = useState(() => toFormValues(initial));
  const [errors, setErrors] = useState({});     // the "errors" object from a 422
  const [saving, setSaving] = useState(false);
  const formRef = useRef(null);

  const change = (event) => {
    const { name, value } = event.target;
    setValues((prev) => ({ ...prev, [name]: value }));   // HW4's spread: copy, then overwrite one
  };

  async function handleSubmit(event) {
    event.preventDefault();   // HW4 write-up, question 4: without this the page reloads
    setSaving(true);
    try {
      await onSubmit(values);
      setErrors({});
    } catch (err) {
      if (err?.status === 422) {
        setErrors(err.errors);
        // Move focus to the first field with a problem, so a keyboard or screen-reader
        // user lands on it instead of hunting for the red text.
        const first = FIELDS.find((f) => err.errors[f]);
        if (first) formRef.current?.querySelector(`[name="${first}"]`)?.focus();
      } else {
        setErrors({ form: [err?.message ?? 'Could not save. Try again.'] });
      }
    } finally {
      setSaving(false);
    }
  }

  return (
    <form ref={formRef} onSubmit={handleSubmit} noValidate className="space-y-4">
      {errors.form && (
        <p role="alert" className="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-900">{errors.form[0]}</p>
      )}

      <Field id="name" label="Name" value={values.name} onChange={change} errors={errors.name}
        autoComplete="off" />

      {/* Two columns from the sm breakpoint up, one on a phone (HW3). */}
      <div className="grid gap-4 sm:grid-cols-2">
        <Field id="amount" label="Amount" value={values.amount} onChange={change} errors={errors.amount}
          inputMode="decimal" />
        <Field id="unit" label="Unit" as="select" value={values.unit} onChange={change} errors={errors.unit}>
          {UNITS.map((u) => <option key={u} value={u}>{u}</option>)}
        </Field>
      </div>

      <div className="grid gap-4 sm:grid-cols-2">
        <Field id="location" label="Where" as="select" value={values.location} onChange={change} errors={errors.location}>
          {LOCATIONS.map((l) => <option key={l} value={l}>{locationName(l)}</option>)}
        </Field>
        <Field id="best_before" label="Best before" type="date" value={values.best_before} onChange={change}
          errors={errors.best_before} hint="Leave empty for things that keep (salt, coffee)." />
      </div>

      <Field id="notes" label="Notes" as="textarea" value={values.notes} onChange={change} errors={errors.notes}
        hint="Optional. Which shelf, or whether others may use it." />

      <div className="flex items-center gap-3">
        <Button type="submit" disabled={saving}>{saving ? 'Saving…' : submitLabel}</Button>
        {cancelTo && <Button asChild variant="ghost"><Link to={cancelTo}>Cancel</Link></Button>}
      </div>
    </form>
  );
}
