<?php

namespace Modules\Notifications\Services;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Modules\Notifications\Notifications\PortalNotification;
use Modules\Notifications\Support\PortalNotificationData;
use Modules\People\Models\Student;

class PortalNotificationService
{
    public function createForStudent(Student $student, string $type, string $title, string $body): DatabaseNotification
    {
        return $this->createForAudience($student, 'parent', $type, $title, $body);
    }

    public function createForAudience(Student $student, string $audience, string $type, string $title, string $body): DatabaseNotification
    {
        return $this->create($student, $audience, $type, null, $title, $body);
    }

    public function createOnceForAudience(Student $student, string $audience, string $type, string $referenceKey, string $title, string $body): DatabaseNotification
    {
        return $this->create($student, $audience, $type, $referenceKey, $title, $body);
    }

    private function create(Student $student, string $audience, string $type, ?string $referenceKey, string $title, string $body): DatabaseNotification
    {
        $recipient = $this->resolveRecipient($student, $audience);
        $payload = $this->payload($student, $audience, $type, $referenceKey, $title, $body);

        if ($referenceKey) {
            $existing = $this->existingNotification($recipient, $referenceKey);

            if ($existing) {
                return $existing;
            }
        }

        $recipient->notify(new PortalNotification($payload));

        return $this->latestNotification($recipient, $payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Student $student, string $audience, string $type, ?string $referenceKey, string $title, string $body): array
    {
        return [
            'audience' => $audience,
            'body' => $body,
            'parent_id' => $student->parent_id,
            'reference_key' => $referenceKey,
            'student_code' => $student->code,
            'student_id' => $student->id,
            'student_name' => $student->name,
            'title' => $title,
            'type' => $type,
        ];
    }

    private function resolveRecipient(Student $student, string $audience): User
    {
        $recipient = match ($audience) {
            'parent' => $student->parent,
            'student' => $student->user,
            default => null,
        };

        abort_if(! $recipient, 422, 'The selected notification recipient is not available.');

        return $recipient;
    }

    private function existingNotification(User $recipient, string $referenceKey): ?DatabaseNotification
    {
        return $recipient->notifications()
            ->where('type', PortalNotificationData::type())
            ->where('data->reference_key', $referenceKey)
            ->latest('created_at')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function latestNotification(User $recipient, array $payload): DatabaseNotification
    {
        return $recipient->notifications()
            ->where('type', PortalNotificationData::type())
            ->where('data->student_id', $payload['student_id'])
            ->where('data->audience', $payload['audience'])
            ->where('data->type', $payload['type'])
            ->when($payload['reference_key'], fn ($query, $referenceKey) => $query->where('data->reference_key', $referenceKey))
            ->latest('created_at')
            ->firstOrFail();
    }
}
