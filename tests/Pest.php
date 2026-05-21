<?php

use Tests\TestCase;

uses(TestCase::class)->in('Feature');

beforeEach(function (): void {
    $this->withoutVite();
});
