<?php

declare(strict_types=1);

namespace App\Services\Author;

use Illuminate\Http\UploadedFile;

final readonly class SubmissionIntakeInput
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $checklistItems
     */
    public function __construct(
        public array $data,
        public ?UploadedFile $manuscriptFile,
        public bool $removeManuscript,
        public array $checklistItems,
        public ?string $directBlobPath,
    ) {}
}
