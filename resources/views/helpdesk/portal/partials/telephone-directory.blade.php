<section class="section telephone-directory" id="telephone-directory" aria-labelledby="telephone-directory-heading">
    <div class="container">
        <div class="telephone-directory-heading">
            <div><span class="portal-eyebrow">CAMPUS CONTACTS</span><h2 id="telephone-directory-heading">Telephone Directory</h2><p>Find an office or unit and its contact numbers.</p></div>
            <div class="directory-trunk"><span>Main trunk line</span><a href="tel:{{ str_replace(' ', '', config('telephone.trunk')) }}"><i class="bi bi-telephone" aria-hidden="true"></i> {{ config('telephone.trunk') }}</a><small>Dial the trunk line, then the office local number.</small></div>
        </div>
        <div class="directory-search" data-directory-search hidden>
            <label for="telephone-search">Search the directory</label>
            <input id="telephone-search" type="search" placeholder="Search office, unit, or local number" maxlength="150" aria-controls="telephone-directory-table" autocomplete="off">
        </div>
        <p class="directory-count" data-directory-count role="status">{{ count(config('telephone.offices')) }} offices and units</p>
        <div class="telephone-table-wrap">
            <table class="telephone-table" id="telephone-directory-table">
                <caption class="sr-only">PMMA office and unit telephone numbers, local extensions, and email addresses</caption>
                <thead><tr><th scope="col">Office / Unit</th><th scope="col">Contact Details</th></tr></thead>
                <tbody>
                @foreach(config('telephone.offices') as $office)
                    <tr data-directory-entry>
                        <th scope="row">{{ $office['name'] }}@if(!empty($office['subtitle']))<small>{{ $office['subtitle'] }}</small>@endif</th>
                        <td>
                            @if(!empty($office['locals']))
                            <div class="directory-contact"><span class="directory-contact-label">Trunk line</span><div>{{ config('telephone.trunk') }} <span class="directory-locals">loc. @foreach($office['locals'] as $local)<a href="tel:{{ str_replace(' ', '', config('telephone.trunk')) }};ext={{ $local }}" aria-label="Call {{ $office['name'] }}{{ !empty($office['subtitle']) ? ' ('.$office['subtitle'].')' : '' }}, local {{ $local }}">{{ $local }}</a>@if(!$loop->last), @endif @endforeach</span></div></div>
                            @endif
                            @foreach(['direct' => 'Direct line', 'mobile' => 'Mobile no.', 'telefax' => 'Telefax'] as $key => $label)
                                @if(!empty($office[$key]))<div class="directory-contact"><span class="directory-contact-label">{{ $label }}</span><a href="tel:{{ str_replace(' ', '', $office[$key]) }}">{{ $office[$key] }}</a></div>@endif
                            @endforeach
                            @if(!empty($office['email']))<div class="directory-contact"><span class="directory-contact-label">Email</span><a href="mailto:{{ $office['email'] }}">{{ $office['email'] }}</a></div>@endif
                            @if(empty($office['locals']) && empty($office['direct']) && empty($office['mobile']) && empty($office['telefax']) && empty($office['email']))<span class="directory-unavailable">Contact number not provided.</span>@endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <p class="directory-empty" data-directory-empty hidden>No offices or units match your search. Try another office name or local number.</p>
    </div>
</section>
