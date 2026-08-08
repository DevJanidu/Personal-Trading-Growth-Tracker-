<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Every request in this app is expected to come from the Next.js SPA
     * (ARCHITECTURE.md §3 — Sanctum SPA cookie auth). Sanctum's
     * EnsureFrontendRequestsAreStateful middleware only starts a session for
     * requests whose Referer/Origin matches a configured stateful domain, so
     * tests need that header present too or `$request->session()` never
     * initializes.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Referer', 'http://localhost:3000');
    }
}
