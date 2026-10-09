<?php

test('halaman utama mengarahkan tamu ke halaman login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});

test('halaman utama mengarahkan user login ke dashboard sesuai role', function () {
    $admin = akunAdmin();

    $this->actingAs($admin)->get('/')->assertRedirect(route('admin.dashboard'));
});
