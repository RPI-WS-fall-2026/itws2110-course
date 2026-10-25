<?php

// Who may do what (PROJECT_v3.pdf, Part 1), checked cell by cell against ItemPolicy.
// No database: the users and the item are built in memory and never saved.

use App\Models\Item;
use App\Models\User;
use App\Policies\ItemPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ItemPolicyTest extends TestCase
{
    private function person(int $id, string $role = 'user'): User
    {
        return (new User)->forceFill(['id' => $id, 'role' => $role]);
    }

    private function itemOwnedBy(?int $userId): Item
    {
        return (new Item)->forceFill(['id' => 10, 'user_id' => $userId]);
    }

    /** What the policy answers: before() first, then the method, as Laravel's Gate does. */
    private function allows(User $user, string $action, Item $item): bool
    {
        $policy = new ItemPolicy;
        return $policy->before($user) ?? ($action === 'create' ? $policy->create($user) : $policy->$action($user, $item));
    }

    public static function table(): array
    {
        // [who, action, allowed?] -- the item belongs to user 1.
        return [
            'a resident may create' => [2, 'user', 'create', true],
            'a resident may not update someone else\'s' => [2, 'user', 'update', false],
            'a resident may not delete someone else\'s' => [2, 'user', 'delete', false],
            'the owner may update' => [1, 'user', 'update', true],
            'the owner may not delete' => [1, 'user', 'delete', false],
            'the RA may create' => [3, 'admin', 'create', true],
            'the RA may update anyone\'s' => [3, 'admin', 'update', true],
            'the RA may delete' => [3, 'admin', 'delete', true],
        ];
    }

    #[DataProvider('table')]
    public function test_who_may_do_what(int $id, string $role, string $action, bool $expected): void
    {
        $this->assertSame($expected, $this->allows($this->person($id, $role), $action, $this->itemOwnedBy(1)));
    }

    public function test_an_item_whose_owner_moved_out_can_be_changed_by_the_ra_only(): void
    {
        $orphan = $this->itemOwnedBy(null);
        $this->assertFalse($this->allows($this->person(1), 'update', $orphan));
        $this->assertTrue($this->allows($this->person(3, 'admin'), 'update', $orphan));
    }
}
