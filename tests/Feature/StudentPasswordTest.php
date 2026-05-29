<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('requires the current password and updates the student password', function () {
    $studentUser = User::factory()->student()->create([
        'password' => Hash::make('old-secret'),
    ]);

    $this->actingAs($studentUser)
        ->put(route('student.password.update'), [
            'current_password' => 'wrong-secret',
            'password' => 'new-secret',
            'password_confirmation' => 'new-secret',
        ])
        ->assertSessionHasErrors(['current_password']);

    $this->actingAs($studentUser)
        ->put(route('student.password.update'), [
            'current_password' => 'old-secret',
            'password' => 'new-secret',
            'password_confirmation' => 'new-secret',
        ])
        ->assertRedirect(route('student.password.edit'));

    expect(Hash::check('new-secret', $studentUser->fresh()->password))->toBeTrue();
});
