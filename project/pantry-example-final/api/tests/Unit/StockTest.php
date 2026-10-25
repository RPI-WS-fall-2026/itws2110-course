<?php

// `use` imports a name so you can write Stock instead of App\Stock below.
// TestCase is PHPUnit's base class; every test class extends it.
use App\Support\InsufficientStockException;
use App\Support\Stock;
use PHPUnit\Framework\TestCase;

// Read these before you read src/Stock.php. Each method name is a sentence
// that should be true of a Stock. Together they are the specification.
//
// Every test has the same three beats -- arrange, act, assert -- and tests
// exactly one thing. When one fails, its name tells you what broke.
//
//   arrange  set the scene: build the object, choose the inputs
//   act      the one call the test is about
//   assert   what must now be true
//
// One wrinkle: when the act is expected to THROW, the assertion has to be
// declared before the act -- see test_a_stock_cannot_start_below_zero. The
// beats are the same; only the order on the page changes.
//
// How PHPUnit finds them: phpunit.xml points at tests/unit; any class that
// extends TestCase is a test class; any public method whose name starts with
// `test` is a test. No registration anywhere.

final class StockTest extends TestCase
{
    public function test_a_new_stock_is_empty(): void
    {
        $stock = new Stock();                       // arrange (and this IS the act: construction)

        $this->assertSame(0.0, $stock->amount());   // assert
    }

    public function test_a_stock_can_start_with_an_amount(): void
    {
        $stock = new Stock(2.5);                    // arrange + act: construction is the thing under test

        $this->assertSame(2.5, $stock->amount());   // assert
    }

    public function test_a_stock_cannot_start_below_zero(): void
    {
        // A refusal test. Nothing to arrange -- the input IS the act.
        //
        // The assertion comes BEFORE the act, because the act throws and no
        // line after it would run. expectException() is PHPUnit being told in
        // advance: "the next thing to blow up must be this class". It checks at
        // the end of the method. Arrange, assert, act -- same three beats.
        $this->expectException(InvalidArgumentException::class);   // assert (declared up front)

        new Stock(-1);                                              // act
    }

    public function test_buying_adds_to_the_amount(): void
    {
        $stock = new Stock(2);                       // arrange

        $stock->add(3);                              // act -- a command: returns nothing, changes the object

        // assertSame is `===`: 5.0 (float) is not the same as 5 (int).
        // That strictness is deliberate -- it catches type mistakes too.
        $this->assertSame(5.0, $stock->amount());    // assert -- so we ask a query to see what changed
    }

    public function test_buying_nothing_is_refused(): void
    {
        $stock = new Stock(2);                                      // arrange

        $this->expectException(InvalidArgumentException::class);   // assert (declared up front)
        $this->expectExceptionMessage('greater than zero');         //   a substring of the message is enough

        $stock->add(0);                                             // act
    }

    public function test_buying_a_negative_amount_is_refused(): void
    {
        $stock = new Stock(2);                                      // arrange

        $this->expectException(InvalidArgumentException::class);   // assert (declared up front)

        $stock->add(-1);                                            // act
    }

    public function test_using_some_takes_it_off_the_shelf(): void
    {
        $stock = new Stock(5);                       // arrange

        $stock->consume(2);                          // act

        $this->assertSame(3.0, $stock->amount());    // assert
    }

    public function test_using_all_of_it_leaves_zero(): void
    {
        // A boundary: exactly what is there. Boundaries are where bugs live.
        $stock = new Stock(5);                       // arrange

        $stock->consume(5);                          // act

        $this->assertSame(0.0, $stock->amount());    // assert
    }

    public function test_using_a_negative_amount_is_refused(): void
    {
        $stock = new Stock(5);                                      // arrange

        $this->expectException(InvalidArgumentException::class);   // assert (declared up front)

        $stock->consume(-2);                                        // act
    }

    // ---- Part 2: the tests an AI wrote to your prompt, after you reviewed them.

    public function test_using_more_than_is_in_stock_is_refused(): void
    {
        $stock = new Stock(2);                                          // arrange

        $this->expectException(InsufficientStockException::class);     // assert (declared up front)
        $this->expectExceptionMessage('Not enough in stock');

        $stock->consume(5);                                             // act
    }

    public function test_a_refused_use_leaves_the_amount_unchanged(): void
    {
        $stock = new Stock(2);                                          // arrange

        // Here we DO want the exception to happen and then keep going, so we
        // catch it ourselves -- the act is inside the try -- and assert on the
        // state afterwards. Plain arrange, act, assert, in that order.
        try {
            $stock->consume(5);                                         // act
        } catch (InsufficientStockException) {   // PHP 8: no variable needed if unused
        }

        $this->assertSame(2.0, $stock->amount());                       // assert
    }
}
