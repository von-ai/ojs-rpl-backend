<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleContributor;
use App\Models\Issue;
use App\Models\IssueArticle;
use App\Models\Submission;
use App\Services\LayoutEditor\LayoutEditorService;
use DomainException;
use Illuminate\Support\Str;

class PublicationService
{
    public function publish(Issue $issue, IssueArticle $entry): Article
    {
        $submission = Submission::query()->lockForUpdate()->findOrFail($entry->submission_id);
        if ($submission->journal_id !== $issue->journal_id) {
            throw new DomainException('Naskah dan edisi harus berasal dari jurnal yang sama.');
        }
        $readiness = app(LayoutEditorService::class)->checkPublicationReadiness($submission);
        if (! $readiness['is_ready']) {
            throw new DomainException($submission->submission_number.': '.implode(' ', $readiness['reasons']));
        }
        $article = Article::firstOrCreate(['submission_id' => $submission->id], [
            'journal_id' => $submission->journal_id, 'section_id' => $submission->section_id,
            'slug' => Str::limit(Str::slug($submission->title), 140, '').'-'.strtolower($submission->id),
            'title' => $submission->title, 'abstract' => $submission->abstract,
            'keywords' => $submission->keywords ?? [], 'references' => $submission->references,
            'license' => $submission->journal->license ?: 'CC BY 4.0',
            'status' => 'published', 'published_at' => now(), 'version' => 1,
        ]);
        if ($article->wasRecentlyCreated) {
            foreach ($submission->contributors as $contributor) {
                ArticleContributor::create(['article_id' => $article->id] + $contributor->only(['given_name', 'family_name', 'affiliation', 'orcid', 'position']));
            }
        }
        $galley = $submission->galleys()->where('status', 'active')->where('format', 'pdf')->latest('version')->firstOrFail();
        $article->galleys()->syncWithoutDetaching([$galley->id => ['position' => 1]]);
        $entry->forceFill(['article_id' => $article->id])->save();

        return $article;
    }
}
