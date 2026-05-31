<?php

use Inertia\Testing\AssertableInertia as Assert;

it('stores English as the session locale', function () {
    $this->post(route('locale.switch'), ['locale' => 'en'])
        ->assertRedirect()
        ->assertSessionHas('locale', 'en');
});

it('stores Arabic as the session locale', function () {
    $this->post(route('locale.switch'), ['locale' => 'ar'])
        ->assertRedirect()
        ->assertSessionHas('locale', 'ar');
});

it('rejects unsupported locales', function () {
    $this->post(route('locale.switch'), ['locale' => 'fr'])
        ->assertSessionHasErrors('locale');
});

it('shares locale props with inertia responses', function () {
    $this->withSession(['locale' => 'ar'])
        ->get(route('login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'ar')
            ->where('direction', 'rtl')
            ->has('availableLocales', 2)
            ->where('routes.localeSwitch', route('locale.switch')));
});
