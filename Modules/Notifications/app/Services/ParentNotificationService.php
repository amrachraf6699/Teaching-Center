<?php

namespace Modules\Notifications\Services;

use Modules\Notifications\Models\ParentNotification;
use Modules\People\Models\Student;

class ParentNotificationService
{
    public function createForStudent(Student $student, string $type, string $title, string $body): ParentNotification
    {
        return $this->createForAudience($student, 'parent', $type, $title, $body);
    }

    public function createForAudience(Student $student, string $audience, string $type, string $title, string $body): ParentNotification
    {
        $recipient = match ($audience) {
            'parent' => $student->parent,
            'student' => $student->user,
            default => null,
        };

        abort_if(! $recipient, 422, 'The selected notification recipient is not available.');

        return ParentNotification::create([
            'parent_id' => $student->parent_id,
            'student_id' => $student->id,
            'recipient_user_id' => $recipient->id,
            'recipient_role' => $audience,
            'type' => $type,
            'title' => $title,
            'body' => $body,
        ]);
    }
}
