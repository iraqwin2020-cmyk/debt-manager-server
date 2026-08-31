<?php

use App\Models\Debtor;

it('blocks a tenant from viewing another tenant\'s debtor by guessing its id', function () {
    ['user' => $userA] = tenantWithOwner();
    ['tenant' => $tenantB] = tenantWithOwner();

    $otherDebtor = Debtor::create([
        'tenant_id' => $tenantB->id,
        'name' => 'عميل المستأجر الآخر',
        'phone' => '07709998888',
    ]);

    $this->actingAs($userA)
        ->get(route('app.debtors.show', $otherDebtor->id))
        ->assertNotFound();
});

it('blocks a tenant from editing another tenant\'s debtor by guessing its id', function () {
    ['user' => $userA] = tenantWithOwner();
    ['tenant' => $tenantB] = tenantWithOwner();

    $otherDebtor = Debtor::create([
        'tenant_id' => $tenantB->id,
        'name' => 'عميل المستأجر الآخر',
        'phone' => '07709997777',
    ]);

    $this->actingAs($userA)
        ->patch(route('app.debtors.update', $otherDebtor->id), [
            'name' => 'محاولة تعديل',
            'phone' => '07709997777',
        ])
        ->assertNotFound();

    expect($otherDebtor->fresh()->name)->toBe('عميل المستأجر الآخر');
});

it('only lists debtors belonging to the currently authenticated tenant', function () {
    ['tenant' => $tenantA, 'user' => $userA] = tenantWithOwner();
    ['tenant' => $tenantB] = tenantWithOwner();

    Debtor::create(['tenant_id' => $tenantA->id, 'name' => 'عميل أ', 'phone' => '07701231111']);
    Debtor::create(['tenant_id' => $tenantB->id, 'name' => 'عميل ب', 'phone' => '07701232222']);

    $response = $this->actingAs($userA)->get(route('app.debtors.index'));

    $response->assertInertia(fn ($page) => $page
        ->has('debtors.data', 1)
        ->where('debtors.data.0.name', 'عميل أ')
    );
});

it('blocks downloading another tenant\'s id document image by guessing the debtor id', function () {
    ['user' => $userA] = tenantWithOwner();
    ['tenant' => $tenantB] = tenantWithOwner();

    $otherDebtor = Debtor::create([
        'tenant_id' => $tenantB->id,
        'name' => 'عميل بمستمسك',
        'phone' => '07709996666',
        'id_document_images' => ['id-documents/fake.jpg'],
    ]);

    $this->actingAs($userA)
        ->get(route('app.debtors.id-document', [$otherDebtor->id, 0]))
        ->assertNotFound();
});
