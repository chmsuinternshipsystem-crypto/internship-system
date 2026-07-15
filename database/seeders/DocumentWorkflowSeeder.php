<?php

namespace Database\Seeders;

use App\Models\DocumentWorkflowStep;
use App\Models\DocumentWorkflowTemplate;
use App\Models\RequiredDocument;
use App\Support\InternshipRoles;
use Illuminate\Database\Seeder;

class DocumentWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            'HTE_SIGNATURE_CHAIN' => [
                'name' => 'Document Review Chain',
                'description' => 'Instructor (approve final) → optionally forward to Chairperson (approve final or return)',
                'steps' => [
                    ['role' => InternshipRoles::INSTRUCTOR, 'requires_signature' => false],
                    ['role' => InternshipRoles::CHAIRPERSON, 'requires_signature' => false],
                ],
            ],
            'PLEDGE_CHAIN' => [
                'name' => 'Document Review Chain',
                'description' => 'Instructor (approve final) → optionally forward to Chairperson (approve final or return)',
                'steps' => [
                    ['role' => InternshipRoles::INSTRUCTOR, 'requires_signature' => false],
                    ['role' => InternshipRoles::CHAIRPERSON, 'requires_signature' => false],
                ],
            ],
            'ENDORSEMENT_CHAIN' => [
                'name' => 'Document Review Chain',
                'description' => 'Instructor (approve final) → optionally forward to Chairperson (approve final or return)',
                'steps' => [
                    ['role' => InternshipRoles::INSTRUCTOR, 'requires_signature' => false],
                    ['role' => InternshipRoles::CHAIRPERSON, 'requires_signature' => false],
                ],
            ],
            'HTE_ISSUED_ARCHIVE' => [
                'name' => 'Instructor Archive',
                'description' => 'Instructor review and finalize',
                'steps' => [
                    ['role' => InternshipRoles::INSTRUCTOR, 'requires_signature' => false],
                ],
            ],
            'OPEN_ARCHIVE' => [
                'name' => 'Open Archive',
                'description' => 'Uploaded and archived for internal monitoring',
                'steps' => [
                    ['role' => InternshipRoles::INSTRUCTOR, 'requires_signature' => false],
                ],
            ],
        ];

        $templateIds = [];
        foreach ($templates as $code => $config) {
            $template = DocumentWorkflowTemplate::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $config['name'],
                    'description' => $config['description'],
                    'is_active' => true,
                ]
            );
            $templateIds[$code] = $template->id;

            DocumentWorkflowStep::query()
                ->where('workflow_template_id', $template->id)
                ->delete();

            foreach ($config['steps'] as $index => $step) {
                DocumentWorkflowStep::query()->create([
                    'workflow_template_id' => $template->id,
                    'step_order' => $index + 1,
                    'role' => $step['role'],
                    'can_return' => true,
                    'requires_signature' => (bool) $step['requires_signature'],
                ]);
            }
        }

        // Product policy: student-submitted documents follow Instructor -> Chairperson -> Instructor.
        $documentMappings = [
            'Memorandum of Agreement' => 'HTE_SIGNATURE_CHAIN',
            'Internship Agreement' => 'HTE_SIGNATURE_CHAIN',
            'Parent Consent Form' => 'HTE_SIGNATURE_CHAIN',
            'Pledge of Good Conduct' => 'HTE_SIGNATURE_CHAIN',
            'Endorsement Letter' => 'HTE_SIGNATURE_CHAIN',
            'Acceptance Letter' => 'HTE_SIGNATURE_CHAIN',
            'Enrolment Form' => 'HTE_SIGNATURE_CHAIN',
            'NBI Clearance' => 'HTE_SIGNATURE_CHAIN',
            'Medical Certificate' => 'HTE_SIGNATURE_CHAIN',
            'Application Letter' => 'HTE_SIGNATURE_CHAIN',
            'Resume' => 'HTE_SIGNATURE_CHAIN',
            'Training Plan' => 'HTE_SIGNATURE_CHAIN',
        ];

        foreach ($documentMappings as $documentName => $templateCode) {
            RequiredDocument::query()
                ->where('name', $documentName)
                ->update(['workflow_template_id' => $templateIds[$templateCode] ?? null]);
        }
    }
}
