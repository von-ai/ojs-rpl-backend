<?php

declare(strict_types=1);

namespace App\Http\Requests\Author;

use App\Models\Submission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSubmissionRequest extends FormRequest
{
    /**
     * Mengizinkan otorisasi request pembuatan pengajuan naskah baru.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalisasi dan sanitasi data input sebelum validasi dijalankan.
     */
    protected function prepareForValidation(): void
    {
        // Normalisasi kata kunci jika dikirim dalam bentuk string JSON atau pemisah koma
        if ($this->has('keywords') && is_string($this->input('keywords'))) {
            $rawKeywords = $this->input('keywords');
            $decoded = json_decode($rawKeywords, true);

            if (is_array($decoded)) {
                $this->merge(['keywords' => array_values(array_filter(array_map(fn ($value) => is_string($value) ? trim($value) : $value, $decoded)))]);
            } else {
                $this->merge(['keywords' => array_values(array_filter(array_map('trim', explode(',', $rawKeywords))))]);
            }
        }

        // Normalisasi kontributor jika dikirim sebagai string JSON dari form
        if ($this->has('contributors') && is_string($this->input('contributors'))) {
            $decodedContributors = json_decode($this->input('contributors'), true);
            if (is_array($decodedContributors)) {
                $this->merge(['contributors' => $decodedContributors]);
            }
        }

        if ($this->has('contributors') && is_array($this->input('contributors'))) {
            $normalizedContributors = [];
            foreach ($this->input('contributors') as $c) {
                if (is_array($c)) {
                    if (empty($c['given_name']) && ! empty($c['family_name'])) {
                        $c['given_name'] = $c['family_name'];
                        $c['family_name'] = null;
                    } elseif (empty($c['given_name']) && ! empty($c['name'])) {
                        $parts = explode(' ', trim((string) $c['name']), 2);
                        $c['given_name'] = $parts[0];
                        $c['family_name'] = $parts[1] ?? null;
                    }
                    $normalizedContributors[] = $c;
                }
            }
            $this->merge(['contributors' => $normalizedContributors]);
        }

        // Normalisasi action default
        if (! $this->has('action')) {
            $this->merge(['action' => 'submit']);
        }
    }

    /**
     * Aturan validasi pengajuan naskah
     *
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        $isSubmit = $this->input('action') === 'submit';

        $draftId = $this->input('draft_id');
        $hasExistingManuscript = $isSubmit && is_string($draftId) && $draftId !== ''
            && Submission::whereKey($draftId)
                ->where('author_id', $this->user()->id)
                ->where('status', 'draft')
                ->whereHas('files', fn ($query) => $query->where('genre', 'manuscript'))
                ->exists();

        $rules = [
            'action' => ['required', 'string', 'in:draft,submit'],
            'draft_id' => ['nullable', 'string', 'max:50'],
            'existing_manuscript_id' => ['nullable', 'string', 'max:50'],
            'remove_manuscript' => ['nullable'],
            'section_id' => $isSubmit ? ['required', 'string', 'max:50'] : ['nullable', 'string', 'max:50'],
            'title' => $isSubmit ? ['required', 'string', 'min:1', 'max:500'] : ['nullable', 'string', 'max:500'],
            'abstract' => $isSubmit ? ['required', 'string', 'min:1', 'max:10000'] : ['nullable', 'string', 'max:10000'],
            'keywords' => $isSubmit ? ['required', 'array', 'min:1', 'max:20'] : ['nullable', 'array', 'max:20'],
            'keywords.*' => ['nullable', 'string', 'max:100'],
            'subject_area' => ['nullable', 'string', 'max:255'],
            'references' => ['nullable', 'string', 'max:100000'],
            'comments_for_editor' => ['nullable', 'string', 'max:2000'],
            'checklist_items' => ['nullable', 'array'],
            'checklist_items.*' => ['nullable', 'string'],
        ];

        if ($isSubmit) {
            $rules['author_guidelines_accepted'] = ['accepted'];
            $rules['manuscript_file'] = ($hasExistingManuscript && ! $this->boolean('remove_manuscript'))
                ? ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:20480']
                : ['required', 'file', 'mimes:pdf,doc,docx', 'max:20480'];
            $rules['contributors'] = ['required', 'array', 'min:1', 'max:100'];
            $rules['contributors.*.given_name'] = ['required', 'string', 'min:1', 'max:100'];
            $rules['contributors.*.family_name'] = ['nullable', 'string', 'max:100'];
            $rules['contributors.*.email'] = ['required', 'email', 'max:255'];
            $rules['contributors.*.affiliation'] = ['required', 'string', 'min:1', 'max:255'];
            $rules['contributors.*.orcid_id'] = ['nullable', 'string', 'regex:/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/'];
            $rules['contributors.*.sequence'] = ['nullable', 'integer', 'min:1'];
            $rules['contributors.*.corresponding'] = ['required'];
        } else {
            // Mode Draf: aturan fleksibel untuk mendukung penyimpanan progresif kapan saja
            $rules['author_guidelines_accepted'] = ['nullable'];
            $rules['manuscript_file'] = ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:20480'];
            $rules['contributors'] = ['nullable', 'array', 'max:100'];
            $rules['contributors.*.family_name'] = ['nullable', 'string', 'max:100'];
            $rules['contributors.*.given_name'] = ['nullable', 'string', 'max:100'];
            $rules['contributors.*.email'] = ['nullable', 'email', 'max:255'];
            $rules['contributors.*.affiliation'] = ['nullable', 'string', 'max:255'];
            $rules['contributors.*.orcid_id'] = ['nullable', 'string', 'regex:/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/'];
            $rules['contributors.*.sequence'] = ['nullable', 'integer', 'min:1'];
            $rules['contributors.*.corresponding'] = ['nullable'];
        }

        return $rules;
    }

    /**
     * Konfigurasi validator tambahan untuk kepatuhan ketat kontrak OpenAPI 1.0.0.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('title') && is_string($this->input('title'))) {
                $titleWords = count(preg_split('/\s+/', trim((string) $this->input('title')), -1, PREG_SPLIT_NO_EMPTY));
                if ($titleWords > 20) {
                    $validator->errors()->add(
                        'title',
                        "Judul naskah maksimal 20 kata (saat ini terisi {$titleWords} kata)."
                    );
                }
            }

            if ($this->filled('abstract') && is_string($this->input('abstract'))) {
                $abstractWords = count(preg_split('/\s+/', trim((string) $this->input('abstract')), -1, PREG_SPLIT_NO_EMPTY));
                if ($abstractWords > 250) {
                    $validator->errors()->add(
                        'abstract',
                        "Abstrak naskah maksimal 250 kata (saat ini terisi {$abstractWords} kata)."
                    );
                }
            }

            if ($this->input('action') === 'submit') {
                $contributors = $this->input('contributors', []);

                if (is_array($contributors)) {
                    $correspondingCount = 0;
                    foreach ($contributors as $contributor) {
                        $isCorresponding = false;
                        if (isset($contributor['corresponding'])) {
                            $isCorresponding = filter_var($contributor['corresponding'], FILTER_VALIDATE_BOOLEAN);
                        }
                        if ($isCorresponding) {
                            $correspondingCount++;
                        }
                    }

                    if ($correspondingCount !== 1) {
                        $validator->errors()->add(
                            'contributors',
                            'Tepat satu penulis wajib ditandai sebagai Penulis Korespondensi (Corresponding Author).'
                        );
                    }
                }
            }
        });
    }

    /**
     * Pesan kesalahan validasi dalam Bahasa Indonesia profesional.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'action.in' => 'Aksi pengajuan tidak valid (harus draf atau kirim resmi).',
            'section_id.required' => 'Seksi jurnal wajib dipilih sebelum melanjutkan.',
            'title.required' => 'Judul naskah wajib diisi.',
            'title.max' => 'Judul naskah maksimal 500 karakter.',
            'abstract.required' => 'Abstrak naskah wajib diisi.',
            'abstract.max' => 'Abstrak naskah maksimal 10.000 karakter.',
            'keywords.required' => 'Minimal satu kata kunci wajib dicantumkan.',
            'keywords.min' => 'Minimal satu kata kunci wajib dicantumkan.',
            'keywords.max' => 'Maksimal 20 kata kunci yang diperbolehkan.',
            'keywords.*.max' => 'Setiap kata kunci maksimal 100 karakter.',
            'author_guidelines_accepted.accepted' => 'Anda wajib menyetujui seluruh ketentuan etika dan panduan penulisan naskah BRIDA.',
            'manuscript_file.required' => 'Berkas manuskrip utama wajib diunggah sebelum mengirim naskah.',
            'manuscript_file.mimes' => 'Format berkas naskah harus berupa .pdf, .docx, atau .doc.',
            'manuscript_file.max' => 'Ukuran berkas naskah terlalu besar. Batas maksimal yang diperbolehkan adalah 20 MB.',
            'contributors.required' => 'Daftar kontributor naskah wajib diisi.',
            'contributors.min' => 'Minimal satu kontributor/penulis naskah wajib dicantumkan.',
            'contributors.*.family_name.required' => 'Nama belakang/lengkap kontributor wajib diisi.',
            'contributors.*.email.required' => 'Alamat email kontributor wajib diisi.',
            'contributors.*.email.email' => 'Format email kontributor tidak valid.',
            'contributors.*.affiliation.required' => 'Afiliasi institusi kontributor wajib diisi.',
            'contributors.*.orcid_id.regex' => 'Format ORCID iD tidak valid (contoh: 0000-0002-1825-0097).',
        ];
    }
}
