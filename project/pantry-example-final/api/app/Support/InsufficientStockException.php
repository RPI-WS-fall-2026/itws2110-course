<?php

namespace App\Support;

/**
 * Thrown when someone tries to consume more of a product than is in stock.
 *
 * An exception is just a class. This one has no body of its own: `extends`
 * gives it everything RuntimeException has (a message, a stack trace). Its
 * whole job is to have a distinctive *name*, so a `catch` block -- or a test --
 * can tell "not enough in stock" apart from every other thing that can go wrong.
 */
final class InsufficientStockException extends \RuntimeException
{
}
