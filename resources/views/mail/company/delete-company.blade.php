<x-mail::message>

{{-- En-tête personnalisé --}}
# {{ __('mail.delete_company.greeting', ['name' => $user->name]) }}

{{ __('mail.delete_company.intro', ['company' => $company->name]) }}

---

{{-- Bloc d'alerte : délai de rétention --}}
<x-mail::panel>
⏳ **{{ __('mail.delete_company.retention_title') }}**

{{ __('mail.delete_company.retention_body', [
    'days' => $retentionDays,
    'date' => $deletionDate,
]) }}
</x-mail::panel>

---

### {{ __('mail.delete_company.what_happens_title') }}

{{ __('mail.delete_company.what_happens_intro') }}

<x-mail::table>
| # | {{ __('mail.delete_company.table_data') }} | {{ __('mail.delete_company.table_status') }} |
|:--|:---|:---|
| 1 | {{ __('mail.delete_company.data_employees') }} | ⚠️ {{ __('mail.delete_company.status_lost') }} |
| 2 | {{ __('mail.delete_company.data_payrolls') }} | ⚠️ {{ __('mail.delete_company.status_lost') }} |
| 3 | {{ __('mail.delete_company.data_documents') }} | ⚠️ {{ __('mail.delete_company.status_lost') }} |
| 4 | {{ __('mail.delete_company.data_settings') }} | ⚠️ {{ __('mail.delete_company.status_lost') }} |
| 5 | {{ __('mail.delete_company.data_account') }} | ✅ {{ __('mail.delete_company.status_kept') }} |
</x-mail::table>

---

### {{ __('mail.delete_company.recover_title') }}

{{ __('mail.delete_company.recover_body') }}

<x-mail::button :url="config('app.url')" color="primary">
    {{ __('mail.delete_company.cta') }}
</x-mail::button>

---

{{ __('mail.delete_company.support_text') }}
[{{ __('mail.delete_company.support_link_label') }}](mailto:{{ config('mail.support_address', 'support@' . parse_url(config('app.url'), PHP_URL_HOST)) }})

{{ __('mail.delete_company.farewell') }}

**{{ config('app.name') }}**

---

<small>
{{ __('mail.delete_company.legal_notice', [
    'company' => $company->name,
    'date'    => now()->translatedFormat('d F Y \à H:i'),
]) }}
</small>

</x-mail::message>