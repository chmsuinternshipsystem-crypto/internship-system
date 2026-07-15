<?php

namespace App\Services;

use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DocumentWorkflowEngine
{
    private array $allowedActionCache = [];
    private array $returnTargetCache = [];

    private function steps(StudentDocument $studentDocument): Collection
    {
        return $studentDocument->workflowTemplate?->steps ?? collect();
    }

    /** Automatically transition "received" items to "under_review" when opened by holder. */
    public function autoStartReview(StudentDocument $studentDocument, User $actor): bool
    {
        if ((string) $studentDocument->workflow_status !== 'received') {
            return false;
        }
        if ((string) $studentDocument->current_holder_role !== (string) $actor->role) {
            return false;
        }
        if (! in_array('review', $this->allowedActions($studentDocument, (string) $actor->role), true)) {
            return false;
        }

        $this->applyAction($studentDocument, $actor, 'review');

        return true;
    }

    public function initialize(StudentDocument $studentDocument, ?User $actor = null, ?string $note = null): void
    {
        $steps = $this->steps($studentDocument);

        if ($steps->isEmpty()) {
            return;
        }

        $firstStep = $steps->sortBy('step_order')->first();

        if (! $firstStep) {
            return;
        }

        if (! $studentDocument->current_step_order || ! $studentDocument->current_holder_role) {
            $studentDocument->current_step_order = (int) $firstStep->step_order;
            $studentDocument->current_holder_role = (string) $firstStep->role;
            $studentDocument->next_step_role = $this->nextRole($studentDocument);
            $studentDocument->workflow_status = 'received';
            $studentDocument->last_action_at = now();
            $studentDocument->save();

            if ($note !== null) {
                $studentDocument->actions()->create([
                    'actor_id' => $actor?->id,
                    'actor_role' => $actor?->role,
                    'action' => 'initialize',
                    'from_status' => null,
                    'to_status' => 'received',
                    'note' => $note,
                    'metadata' => null,
                    'acted_at' => now(),
                ]);
            }
        } elseif (blank($studentDocument->next_step_role)) {
            $resolvedNext = $this->nextRole($studentDocument);
            if (filled($resolvedNext)) {
                $studentDocument->next_step_role = $resolvedNext;
                $studentDocument->save();
            }
        }
    }

    public function allowedActions(StudentDocument $studentDocument, string $actorRole): array
    {
        $key = $studentDocument->id . '|' . $actorRole;
        if (isset($this->allowedActionCache[$key])) {
            return $this->allowedActionCache[$key];
        }

        if ($studentDocument->current_holder_role !== $actorRole) {
            return $this->allowedActionCache[$key] = [];
        }
        if (in_array($studentDocument->workflow_status, ['completed', 'rejected'], true)) {
            return $this->allowedActionCache[$key] = [];
        }

        if (! $studentDocument->workflow_template_id) {
            return $this->allowedActionCache[$key] = $this->simpleAllowedActions($studentDocument, $actorRole);
        }

        $steps = $this->steps($studentDocument);
        $currentStep = $steps->firstWhere('step_order', (int) $studentDocument->current_step_order);

        if (! $currentStep) {
            return [];
        }

        $status = (string) ($studentDocument->workflow_status ?? 'received');
        $actions = [];

        if ($status === 'received') {
            $initialActions = ['review', 'approve', 'forward', 'return_for_revision'];
            if ($currentStep->requires_signature) {
                $initialActions[] = 'sign';
            }

            return $initialActions;
        }

        $currentOrder = (int) $studentDocument->current_step_order;
        $hasPreviousStep = $steps->contains(fn ($s) => (int) $s->step_order < $currentOrder);
        if ($hasPreviousStep) {
            $actions[] = 'return_for_revision';
        } elseif ((int) $currentStep->step_order === 1) {
            $actions[] = 'return_for_revision';
        }

        $hasNextStep = $steps->contains(fn ($s) => (int) $s->step_order > $currentOrder);

        if ($currentStep->requires_signature) {
            if ($status === 'pending_review') {
                $actions[] = 'review';
            }
            $actions[] = 'sign';
        } else {
            $actions[] = 'approve';
            if ($hasNextStep) {
                $actions[] = 'forward';
            }
        }

        return $this->allowedActionCache[$key] = $actions;
    }

    private function simpleAllowedActions(StudentDocument $studentDocument, string $actorRole): array
    {
        $status = (string) ($studentDocument->workflow_status ?? 'received');
        $actions = [];

        $actions[] = 'approve';
        $actions[] = 'return_for_revision';

        if ($actorRole === 'instructor') {
            $nextRole = (string) ($studentDocument->next_step_role ?? '');
            if ($nextRole !== '' || $status === 'received') {
                $actions[] = 'forward';
            }
        }

        return $actions;
    }

    /**
     * @return array<int, array{step_order:int,role:string}>
     */
    public function allowedReturnTargets(StudentDocument $studentDocument, string $actorRole): array
    {
        $key = $studentDocument->id . '|' . $actorRole;
        if (isset($this->returnTargetCache[$key])) {
            return $this->returnTargetCache[$key];
        }

        if (! $studentDocument->workflow_template_id) {
            return $this->returnTargetCache[$key] = [];
        }

        if (! in_array('return_for_revision', $this->allowedActions($studentDocument, $actorRole), true)) {
            return $this->returnTargetCache[$key] = [];
        }

        $steps = $this->steps($studentDocument);
        $currentOrder = (int) $studentDocument->current_step_order;

        return $this->returnTargetCache[$key] = $steps
            ->filter(fn ($s) => (int) $s->step_order < $currentOrder)
            ->sortByDesc('step_order')
            ->map(fn ($step) => [
                'step_order' => (int) $step->step_order,
                'role' => (string) $step->role,
            ])
            ->values()
            ->all();
    }

    public function applyAction(
        StudentDocument $studentDocument,
        User $actor,
        string $action,
        ?string $note = null,
        ?int $returnStepOrder = null
    ): void {
        $allowed = $this->allowedActions($studentDocument, (string) $actor->role);
        if (! in_array($action, $allowed, true)) {
            throw new InvalidArgumentException('Action is not allowed at this workflow step.');
        }

        if ($action === 'return_for_revision' && blank($note)) {
            throw new InvalidArgumentException('A note is required for return actions.');
        }

        DB::transaction(function () use ($studentDocument, $actor, $action, $note, $returnStepOrder): void {
            $fromStatus = (string) ($studentDocument->workflow_status ?? 'received');
            $toStatus = $fromStatus;

            if ($action === 'review') {
                $toStatus = 'under_review';
            } elseif ($action === 'return_for_revision') {
                $steps = $this->steps($studentDocument);

                if ($steps->isNotEmpty()) {
                    $currentOrder = (int) $studentDocument->current_step_order;
                    $previousSteps = $steps->filter(fn ($s) => (int) $s->step_order < $currentOrder);

                    if ($returnStepOrder !== null) {
                        $previousSteps = $previousSteps->filter(fn ($s) => (int) $s->step_order === $returnStepOrder);
                    }

                    $previousStep = $previousSteps->sortByDesc('step_order')->first();

                    if ($previousStep) {
                        $studentDocument->current_step_order = (int) $previousStep->step_order;
                        $studentDocument->current_holder_role = (string) $previousStep->role;
                        $studentDocument->next_step_role = (string) $actor->role;
                    }
                } else {
                    $studentDocument->current_holder_role = 'instructor';
                    $studentDocument->next_step_role = 'chairperson';
                }
                $toStatus = 'for_revision';
            } elseif ($action === 'forward') {
                $steps = $this->steps($studentDocument);

                if ($steps->isNotEmpty()) {
                    $currentOrder = (int) $studentDocument->current_step_order;
                    $nextStep = $steps
                        ->filter(fn ($s) => (int) $s->step_order > $currentOrder)
                        ->sortBy('step_order')
                        ->first();

                    if ($nextStep) {
                        $studentDocument->current_step_order = (int) $nextStep->step_order;
                        $studentDocument->current_holder_role = (string) $nextStep->role;
                        $studentDocument->next_step_role = $this->nextRole($studentDocument);
                        $toStatus = 'pending_review';
                    } else {
                        $studentDocument->next_step_role = null;
                        $toStatus = 'completed';
                    }
                } else {
                    $studentDocument->current_holder_role = 'chairperson';
                    $studentDocument->next_step_role = null;
                    $toStatus = 'pending_review';
                }
            } elseif (in_array($action, ['approve', 'sign'], true)) {
                $studentDocument->next_step_role = null;
                $toStatus = 'completed';
            }

            $studentDocument->workflow_status = $toStatus;
            $studentDocument->last_action_at = now();

            if (in_array($toStatus, ['completed'], true)) {
                $studentDocument->status = 'Submitted';
                $studentDocument->submitted_at = $studentDocument->submitted_at ?: now();
                $studentDocument->verified_by = $actor->id;
            } elseif (in_array($toStatus, ['rejected', 'for_revision'], true)) {
                $studentDocument->status = 'Missing';
                $studentDocument->verified_by = $actor->id;
            } elseif (in_array($toStatus, ['received', 'under_review', 'pending_review'], true)) {
                $studentDocument->status = 'Pending';
            }

            $studentDocument->save();

            $studentDocument->actions()->create([
                'actor_id' => $actor->id,
                'actor_role' => $actor->role,
                'action' => $action,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => $note,
                'metadata' => $returnStepOrder !== null ? ['return_step_order' => $returnStepOrder] : null,
                'acted_at' => now(),
            ]);
        });
    }

    private function nextRole(StudentDocument $studentDocument): ?string
    {
        $steps = $this->steps($studentDocument);
        $currentOrder = (int) $studentDocument->current_step_order;

        $nextStep = $steps
            ->filter(fn ($s) => (int) $s->step_order > $currentOrder)
            ->sortBy('step_order')
            ->first();

        return $nextStep ? (string) $nextStep->role : null;
    }
}
