<?php
// Every PHP file starts with the <?php tag. Anything outside the tag is sent to
// the browser as-is, which is how PHP mixes with HTML in public/index.php.
// A file that is *only* code, like this one, opens the tag and never closes it.
// (Fun fact: the closing tag ends PHP mode even inside a comment, which is why
// this comment does not show you what it looks like.)

// A namespace is a folder for names. This class is really "App\Stock", so it
// cannot collide with some other library's "Stock". composer.json maps the
// App\ namespace to the src/ directory -- that is how `new Stock()` in another
// file finds this one without a single `require`.
namespace App\Support;

/**
 * How much of one product is on the shelf. Pure logic: no database, no HTTP.
 *
 * Both the web page and the JSON API end up here when someone buys or uses
 * something, so a rule enforced in this class is enforced everywhere.
 *
 * A comment that starts with /** is a "docblock". Editors and tools read it.
 */
final class Stock            // `final`: nothing may extend this class. Say what you mean.
{
    /**
     * The constructor runs when you write `new Stock(...)`.
     *
     * `private float $amount = 0.0` in the parameter list is "constructor
     * property promotion": it declares a private property called $amount,
     * types it as a float, gives it a default, and assigns the argument to it
     * -- four things in one line. Every `$this->amount` below is that property.
     */
    public function __construct(private float $amount = 0.0)
    {
        if ($amount < 0) {
            // `throw` stops the function right here. Whoever called us decides
            // what to do about it. InvalidArgumentException is built into PHP;
            // the leading backslash means "the global one, not App\...".
            throw new \InvalidArgumentException('Stock cannot start below zero');
        }
    }

    /** `: float` after the parentheses is the return type. PHP checks it. */
    public function amount(): float
    {
        return $this->amount;
    }

    /** Buying some. `: void` means this method returns nothing. */
    public function add(float $quantity): void
    {
        $this->assertPositive($quantity);
        $this->amount += $quantity;      // same as  $this->amount = $this->amount + $quantity
    }

    /** Using some up. */
    public function consume(float $quantity): void
    {
        $this->assertPositive($quantity);
        if ($quantity > $this->amount) {
            throw new InsufficientStockException('Not enough in stock');
        }
        $this->amount -= $quantity;
       
    }

    /**
     * `private`: only code inside this class can call it. It is a helper, not
     * part of what the class promises to the outside world.
     */
    private function assertPositive(float $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero');
        }
    }
}
