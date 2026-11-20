// One labelled form field with its error message underneath (MIDTERM_APPENDIX.md
// section C). The label's htmlFor and the input's id tie them together, so clicking the
// label focuses the field, and a screen reader reads the label (week 6, drill 5).
import { Input } from '@/components/ui/input';

const CONTROL = 'h-9 w-full rounded-lg border border-input bg-transparent px-2.5 text-base outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-3 aria-invalid:ring-destructive/20 md:text-sm';

/**
 * `errors` is the array for this field from a 422 ({ name: ['The name is required.'] }).
 * `as` picks the control: 'input' (default), 'select' or 'textarea'.
 */
export default function Field({ id, label, hint, errors, as = 'input', children, className = '', ...props }) {
  const errorId = `${id}-error`;
  const hintId = hint ? `${id}-hint` : undefined;
  const describedBy = [errors ? errorId : null, hintId].filter(Boolean).join(' ') || undefined;
  const shared = { id, name: id, 'aria-invalid': Boolean(errors), 'aria-describedby': describedBy, ...props };

  let control;
  if (as === 'select') control = <select className={CONTROL} {...shared}>{children}</select>;
  else if (as === 'textarea') control = <textarea rows={3} className={`${CONTROL} h-auto py-2`} {...shared} />;
  else control = <Input className="h-9" {...shared} />;

  return (
    <div className={`space-y-1.5 ${className}`}>
      <label htmlFor={id} className="block text-sm font-medium">{label}</label>
      {control}
      {hint && <p id={hintId} className="text-xs text-muted-foreground">{hint}</p>}
      {errors && <p id={errorId} className="text-sm text-destructive">{errors[0]}</p>}
    </div>
  );
}
