<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditPublishedLicenses extends Command
{
    protected $signature = 'publications:audit-licenses';

    protected $description = 'Cocokkan lisensi artikel terbit dengan bukti yang tercatat saat penerbitan';

    public function handle(): int
    {
        $counts = ['matched' => 0, 'missing_evidence' => 0, 'license_mismatch' => 0, 'missing_publication_date' => 0];

        $hasEvidenceTable = Schema::hasTable('publication_permissions');

        foreach (Article::where('status', 'published')->lazyById() as $article) {
            $status = 'missing_publication_date';
            if ($article->published_at !== null) {
                $evidence = $hasEvidenceTable ? DB::table('publication_permissions')
                    ->where('submission_id', $article->submission_id)
                    ->where('recorded_at', '<=', $article->published_at)
                    ->latest('id')
                    ->first() : null;

                $status = match (true) {
                    $evidence === null => 'missing_evidence',
                    $evidence->license !== $article->license => 'license_mismatch',
                    default => 'matched',
                };
            }

            $counts[$status]++;
            if ($status !== 'matched') {
                $this->line("{$article->journal_id} | {$article->id} | {$article->submission_id} | {$status}");
            }
        }

        foreach ($counts as $status => $count) {
            $this->line("{$status}: {$count}");
        }

        return self::SUCCESS;
    }
}
