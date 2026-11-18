<?php

declare(strict_types=1);

namespace App\Services\SectionEditor;

use Illuminate\Http\UploadedFile;

final readonly class ReviewRoundOpeningInput
{
    public function __construct(
        public UploadedFile $anonymousFile,
        public string $reviewerTitle,
        public string $reviewerAbstract,
        public ?string $note,
    ) {}
}
