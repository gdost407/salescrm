<?php

use App\Models\Client;
use App\Models\Company;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\TxnInvoice;
use App\Models\TxnJob;
use App\Models\TxnLedger;
use App\Models\TxnQuotation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    $this->company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $this->owner = User::factory()->for($this->company)->create(['user_type' => 'owner']);
    $this->actingAs($this->owner);
});

function reportWorkbookXml(TestResponse $response): string
{
    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $path = $response->baseResponse->getFile()->getPathname();
    $zip = new ZipArchive;
    try {
        expect($zip->open($path))->toBeTrue();
        foreach (['[Content_Types].xml', '_rels/.rels', 'xl/workbook.xml', 'xl/_rels/workbook.xml.rels', 'xl/worksheets/sheet1.xml'] as $part) {
            expect(simplexml_load_string($zip->getFromName($part)))->not->toBeFalse();
        }

        return $zip->getFromName('xl/worksheets/sheet1.xml');
    } finally {
        $zip->close();
        unlink($path);
    }
}

test('reports and excel exports are company scoped and preserve date filters', function (string $type, string $model, string $date) {
    $attributes = ['company_id' => $this->company->id, $date => '2026-09-20'];
    if ($type !== 'lead') {
        $attributes['client_id'] = Client::factory()->for($this->company)->create()->id;
    }
    $record = $model::factory()->create($attributes);
    $model::factory()->create(array_replace($attributes, [$date => '2026-08-01']));
    $foreign = $model::factory()->create([$date => '2026-09-20']);
    $filters = ['type' => $type, 'date_from' => '2026-09-20', 'date_to' => '2026-09-20'];
    $response = $this->get(route('reports.index', $filters))->assertSuccessful();
    expect($response['records']->total())->toBe(1)->and($response['records']->modelKeys())->toBe([$record->id]);
    $xml = reportWorkbookXml($this->get(route('reports.export', $filters)));
    $sheet = simplexml_load_string($xml);
    expect($sheet->sheetData->row)->toHaveCount(2);
    $this->get(route('dashboard'))->assertSuccessful()->assertSee('Reports')->assertSee(route('reports.index', ['type' => $type]), false);
})->with([
    ['lead', Lead::class, 'created_at'], ['quotation', TxnQuotation::class, 'quotation_date'],
    ['job', TxnJob::class, 'job_date'], ['invoice', TxnInvoice::class, 'invoice_date'],
    ['ledger', TxnLedger::class, 'transaction_date'],
]);

test('exports include every page and keep potentially executable text as text', function () {
    Lead::factory()->count(52)->create(['company_id' => $this->company->id, 'status' => 'New', 'mobile' => '0012345678']);
    Lead::factory()->create(['company_id' => $this->company->id, 'name' => '=1+1', 'company_name' => '<Client & Co>', 'status' => 'New']);
    Lead::factory()->create(['company_id' => $this->company->id, 'status' => 'Converted']);
    Lead::factory()->create(['company_id' => $this->company->id, 'status' => 'New'])->delete();
    $filters = ['type' => 'lead', 'status' => 'New', 'per_page' => 25, 'page' => 2];
    $response = $this->get(route('reports.index', $filters))->assertSuccessful();
    expect($response['records']->total())->toBe(53)->and($response['records']->count())->toBe(25);
    $xml = reportWorkbookXml($this->get(route('reports.export', $filters)));
    $sheet = simplexml_load_string($xml);
    expect($sheet->sheetData->row)->toHaveCount(54);
    expect($xml)->toContain('0012345678', '=1+1', '&lt;Client &amp; Co&gt;')->not->toContain('<f>');
});

test('report access requires module permissions and own-lead access is preserved in exports', function () {
    app(RolePermissionSeeder::class)->seedCompany($this->company->id);
    $role = Role::create(['company_id' => $this->company->id, 'name' => 'Report reader', 'slug' => 'report-reader', 'status' => true]);
    $role->permissions()->sync(Permission::where('company_id', $this->company->id)->where('slug', 'view_own_leads')->pluck('id'));
    $staff = User::factory()->for($this->company)->create(['user_type' => 'staff', 'role_id' => $role->id]);
    $own = Lead::factory()->create(['company_id' => $this->company->id, 'assigned_to' => $staff->id, 'name' => 'Visible assigned lead']);
    $hidden = Lead::factory()->create(['company_id' => $this->company->id, 'name' => 'Private unassigned lead']);
    $this->actingAs($staff);
    $this->get(route('reports.index', ['type' => 'lead']))->assertSuccessful()->assertSee($own->name)->assertDontSee($hidden->name);
    $xml = reportWorkbookXml($this->get(route('reports.export', ['type' => 'lead'])));
    expect($xml)->toContain($own->name)->not->toContain($hidden->name);
    foreach (['quotation', 'job', 'invoice', 'ledger'] as $type) {
        $this->getJson(route('reports.index', ['type' => $type]))->assertForbidden();
        $this->getJson(route('reports.export', ['type' => $type]))->assertForbidden();
    }
    $this->actingAs(User::factory()->for($this->company)->create(['user_type' => 'staff', 'role_id' => null]));
    $this->getJson(route('reports.index', ['type' => 'lead']))->assertForbidden();
    $this->getJson(route('reports.export', ['type' => 'lead']))->assertForbidden();
});

test('report filters validate dates ownership and pagination on screen and export', function () {
    $foreignClient = Client::factory()->create();
    $foreignStaff = User::factory()->for(Company::factory())->create();
    foreach (['reports.index', 'reports.export'] as $route) {
        $this->getJson(route($route, ['type' => 'invoice', 'client_id' => $foreignClient->id, 'date_from' => '2026-09-20', 'date_to' => '2026-09-01', 'per_page' => 10000]))
            ->assertUnprocessable()->assertJsonValidationErrors(['client_id', 'date_to', 'per_page']);
        $this->getJson(route($route, ['type' => 'lead', 'assigned_to' => $foreignStaff->id, 'q' => ['bad']]))
            ->assertUnprocessable()->assertJsonValidationErrors(['assigned_to', 'q']);
    }
    $this->get('/reports/unknown')->assertNotFound();
});

test('module specific filters combine and amounts remain numeric in Excel', function () {
    $client = Client::factory()->for($this->company)->create(['name' => 'Acme customer']);
    $match = TxnInvoice::factory()->create(['company_id' => $this->company->id, 'client_id' => $client->id, 'status' => 'issued', 'total_amount' => '118.25', 'paid_amount' => '0.00', 'balance_amount' => '118.25']);
    TxnInvoice::factory()->create(['company_id' => $this->company->id, 'client_id' => $client->id, 'status' => 'draft']);
    $filter = ['type' => 'invoice', 'q' => 'Acme', 'client_id' => $client->id, 'status' => 'issued'];
    $response = $this->get(route('reports.index', $filter))->assertSuccessful();
    expect($response['records']->modelKeys())->toBe([$match->id]);
    $xml = reportWorkbookXml($this->get(route('reports.export', $filter)));
    expect($xml)->toContain('t="n"><v>118.25</v>');
    $entry = TxnLedger::factory()->create(['company_id' => $this->company->id, 'client_id' => $client->id, 'entry_type' => 'credit', 'transaction_type' => 'adjustment']);
    TxnLedger::factory()->create(['company_id' => $this->company->id, 'client_id' => $client->id, 'entry_type' => 'debit']);
    $response = $this->get(route('reports.index', ['type' => 'ledger', 'entry_type' => 'credit', 'transaction_type' => 'adjustment']))->assertSuccessful();
    expect($response['records']->modelKeys())->toBe([$entry->id]);
});

test('empty reports export a valid header-only workbook', function () {
    $this->get(route('reports.index', ['type' => 'quotation']))->assertSuccessful()->assertSee('No records match these filters.');
    $xml = reportWorkbookXml($this->get(route('reports.export', ['type' => 'quotation'])));
    expect(simplexml_load_string($xml)->sheetData->row)->toHaveCount(1);
});
