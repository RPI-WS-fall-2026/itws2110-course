// One item on the shelf. Grown from HW4's PantryCard (shadcn Card, name, "Quantity: N"):
// the same Card, with the best-before status and the owner added, and the name a link to
// the item's page instead of an "Add one" button.
import { Link } from 'react-router-dom';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import StatusBadge from './StatusBadge.jsx';
import { formatAmount, formatDaysLeft, locationName, ownerName } from '@/format.js';

export default function ItemCard({ item }) {
  return (
    // relative + the link's after:absolute inset-0 makes the whole card clickable, while
    // the link text stays the item's name (one link per card, not a link around a card).
    <Card className="relative transition-shadow hover:shadow-md focus-within:ring-2 focus-within:ring-ring">
      <CardHeader className="flex items-start justify-between gap-2">
        <CardTitle className="text-lg">
          <Link to={`/items/${item.id}`} className="outline-none after:absolute after:inset-0">
            {item.name}
          </Link>
        </CardTitle>
        <StatusBadge status={item.status} />
      </CardHeader>
      <CardContent className="space-y-1 text-muted-foreground">
        <p className="text-foreground">{formatAmount(item.amount, item.unit)} · {locationName(item.location)}</p>
        <p>{formatDaysLeft(item.days_left)}</p>
        <p>{ownerName(item.owner)}</p>
      </CardContent>
    </Card>
  );
}
