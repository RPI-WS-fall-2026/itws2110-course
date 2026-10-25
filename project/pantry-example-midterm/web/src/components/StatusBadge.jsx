// The best-before label. The words carry the meaning and the colour repeats it, so the
// label still works for someone who cannot tell red from green.
import { DUE_SOON, EXPIRED, FRESH } from '@/rules.js';

const STYLES = {
  [EXPIRED]: 'bg-red-100 text-red-800 ring-red-200',
  [DUE_SOON]: 'bg-amber-100 text-amber-900 ring-amber-200',
  [FRESH]: 'bg-emerald-100 text-emerald-800 ring-emerald-200',
};

export default function StatusBadge({ status }) {
  return (
    <span className={`inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset ${STYLES[status] ?? 'bg-muted text-muted-foreground ring-border'}`}>
      {status}
    </span>
  );
}
