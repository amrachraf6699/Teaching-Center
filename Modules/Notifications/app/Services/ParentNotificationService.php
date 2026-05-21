<?php

namespace Modules\Notifications\Services;

use Modules\Notifications\Models\ParentNotification;
use Modules\People\Models\Student;

class ParentNotificationService
{
    public function createForStudent(Student $student, string $type, string $title, string $body): ParentNotification
    {
        return ParentNotification::create([
            'parent_id' => $student->parent_id,
            'student_id' => $student->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
        ]);
    }
}
