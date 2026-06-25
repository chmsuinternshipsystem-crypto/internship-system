<?php

namespace Tests\Feature;

use App\Models\DocumentForwardingBatch;
use App\Models\DocumentForwardingItem;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentForwardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_can_create_immediately_released_batch_with_transmittal_logs(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = Student::query()->create([
            'name' => 'Forward Student',
            'student_number' => '20235501',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'status' => 'deployed',
        ]);
        $requiredDocument = RequiredDocument::query()->create([
            'name' => 'Forwarded Document',
            'is_mandatory' => false,
            'order_index' => 1,
        ]);
        $studentDocument = StudentDocument::query()->create([
            'student_id' => $student->id,
            'required_document_id' => $requiredDocument->id,
            'status' => 'Submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($instructor)->post(route('document-forwarding.store'), [
            'student_document_ids' => [$studentDocument->id],
        ]);

        $response->assertRedirect(route('document-forwarding.index'));
        $batch = DocumentForwardingBatch::query()->first();
        $this->assertNotNull($batch);
        $this->assertSame('released', $batch->status);
        $this->assertDatabaseHas('transmittal_logs', [
            'batch_id' => $batch->id,
            'action_type' => 'forwarded',
            'student_id' => $student->id,
            'required_document_id' => $requiredDocument->id,
        ]);
    }

    public function test_instructor_can_release_scheduled_batch_and_acknowledge_item(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = Student::query()->create([
            'name' => 'Scheduled Student',
            'student_number' => '20235502',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'status' => 'deployed',
        ]);
        $requiredDocument = RequiredDocument::query()->create([
            'name' => 'Scheduled Document',
            'is_mandatory' => false,
            'order_index' => 1,
        ]);
        $studentDocument = StudentDocument::query()->create([
            'student_id' => $student->id,
            'required_document_id' => $requiredDocument->id,
            'status' => 'Submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAs($instructor)->post(route('document-forwarding.store'), [
            'student_document_ids' => [$studentDocument->id],
            'release_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertRedirect(route('document-forwarding.index'));

        $batch = DocumentForwardingBatch::query()->firstOrFail();
        $this->assertSame('scheduled', $batch->status);

        $this->actingAs($instructor)->post(route('document-forwarding.release', $batch))
            ->assertSessionHas('status');
        $batch->refresh();
        $this->assertSame('released', $batch->status);

        $item = DocumentForwardingItem::query()->where('batch_id', $batch->id)->firstOrFail();
        $this->actingAs($instructor)->post(route('document-forwarding.acknowledge', $item))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('transmittal_logs', [
            'batch_id' => $batch->id,
            'item_id' => $item->id,
            'action_type' => 'acknowledged',
        ]);
    }

    public function test_dean_has_read_only_access_for_transmittal_module(): void
    {
        $dean = User::factory()->create(['role' => 'dean']);
        $batch = DocumentForwardingBatch::query()->create([
            'created_by' => $dean->id,
            'release_at' => now(),
            'status' => 'released',
        ]);

        $this->actingAs($dean)->get(route('document-forwarding.index'))->assertOk();
        $this->actingAs($dean)->get(route('document-forwarding.create'))->assertForbidden();
        $this->actingAs($dean)->post(route('document-forwarding.release', $batch))->assertForbidden();
    }
}
