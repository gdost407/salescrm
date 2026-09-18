<?php

namespace Database\Factories;

use App\Models\TxnAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TxnAttachment>
 */
class TxnAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_type' => 'quotation',
            'document_id' => \App\Models\TxnQuotation::factory(),
            'company_id' => fn (array $attributes) => \App\Models\TxnQuotation::findOrFail($attributes['document_id'])->company_id,
            'file_name' => 'attachment.pdf',
            'file_path' => 'attachments/attachment.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
        ];
    }
}
