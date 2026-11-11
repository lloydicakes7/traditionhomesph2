<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * A document was asked to print without the data the paper requires.
 *
 * The only place a printed document treats missing data as an error: an
 * optional value prints as its blank, but a permit with no permit holder is not
 * a permit, and printing a half-filled sheet would be worse than not printing
 * it. The controller turns this into the preview frame's message naming the
 * missing items.
 */
class MissingDocumentDataException extends RuntimeException
{
    /** @var array<int, string> */
    private $missing;

    /**
     * @param  array<int, string>  $missing  the data keys the document needs
     */
    public function __construct(array $missing)
    {
        $this->missing = array_values($missing);

        parent::__construct('The document cannot be printed without: ' . implode(', ', $this->missing));
    }

    /**
     * The data keys that were not supplied.
     *
     * @return array<int, string>
     */
    public function missing(): array
    {
        return $this->missing;
    }
}
