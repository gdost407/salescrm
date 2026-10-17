<nav
  class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
  id="layout-navbar">
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
      <i class="bx bx-menu bx-sm"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <!-- Search -->
    @if (auth()->user()->hasPermission('view_own_leads'))
    <div class="position-relative flex-shrink-0" style="width: min(360px, 45vw);">
      <form action="{{ route('sales-all-list') }}" method="GET" class="d-flex align-items-center" data-navbar-lead-search>
        <button class="btn btn-link text-body p-0 me-2" type="submit" aria-label="Search leads"><i class="bx bx-search fs-4 lh-0"></i></button>
        <input type="search" name="search" value="{{ request('search') }}" class="form-control border-0 shadow-none" placeholder="Search leads..." aria-label="Search leads" autocomplete="off" aria-controls="navbar-lead-search-results" aria-expanded="false">
      </form>
      <div id="navbar-lead-search-results" class="list-group position-absolute top-100 start-0 w-100 shadow bg-white d-none" style="z-index: 1090; max-height: 65vh; overflow-y: auto; background-color: #fff;" role="listbox"></div>
    </div>
    @endif
    <!-- /Search -->

    <ul class="navbar-nav flex-row align-items-center ms-auto">
      <!-- User -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center gap-3" href="#" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Open your account menu">
          <div class="text-end">
            <span class="fw-semibold d-block">{{ \Illuminate\Support\Str::before(trim(auth()->user()->name), ' ') }}</span>
            <small class="text-body-secondary">{{ auth()->user()->roleLabel() }}</small>
          </div>
          @include('partials.user-avatar')
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="{{ route('settings.profile') }}">
              <div class="d-flex">
                <div class="flex-shrink-0 me-3">
                  @include('partials.user-avatar')
                </div>
                <div class="flex-grow-1">
                  <span class="fw-semibold d-block">{{ auth()->user()->name }}</span>
                  <small class="text-muted">{{ auth()->user()->roleLabel() }}</small>
                </div>
              </div>
            </a>
          </li>
          <li>
            <div class="dropdown-divider"></div>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('settings.profile') }}">
              <i class="bx bx-user me-2"></i>
              <span class="align-middle">My Profile</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('settings.profile') }}#change-password">
              <i class="bx bx-lock-alt me-2"></i>
              <span class="align-middle">Change Password</span>
            </a>
          </li>
          @if (auth()->user()->is_active && auth()->user()->user_type === 'owner' && auth()->user()->company_id)
          <li>
            <a class="dropdown-item" href="{{ route('subscription.index') }}">
              <i class="bx bx-credit-card me-2"></i>
              <span class="align-middle">Subscription &amp; Billing</span>
            </a>
          </li>
          @endif

          <li>
            <div class="dropdown-divider"></div>
          </li>
          <li>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
              @csrf
              <button type="submit" class="dropdown-item">
                <i class="bx bx-power-off me-2"></i>
                <span class="align-middle">Log Out</span>
              </button>
            </form>
          </li>
        </ul>
      </li>
      <!--/ User -->
    </ul>
  </div>
</nav>

@if (auth()->user()->hasPermission('view_own_leads'))
@push('scripts')
<script>
  (() => {
    const form = document.querySelector('[data-navbar-lead-search]');
    const input = form?.querySelector('input[name="search"]');
    const results = document.getElementById('navbar-lead-search-results');
    if (!form || !input || !results) return;

    let timeout;
    let controller;
    const endpoint = @json(route('sales-leads.search'));

    const hideResults = () => {
      results.classList.add('d-none');
      input.setAttribute('aria-expanded', 'false');
    };

    const showMessage = (message) => {
      results.replaceChildren();
      const item = document.createElement('div');
      item.className = 'list-group-item text-muted small bg-white';
      item.textContent = message;
      results.append(item);
      results.classList.remove('d-none');
      input.setAttribute('aria-expanded', 'true');
    };

    input.addEventListener('input', () => {
      window.clearTimeout(timeout);
      controller?.abort();
      const search = input.value.trim();
      if (search.length < 2) {
        hideResults();
        return;
      }

      timeout = window.setTimeout(async () => {
        controller = new AbortController();
        const query = new URLSearchParams({ search });
        try {
          const response = await fetch(`${endpoint}?${query}`, {
            headers: { Accept: 'application/json' },
            signal: controller.signal,
          });
          if (!response.ok) throw new Error('Search unavailable.');
          const data = await response.json();
          results.replaceChildren();
          if (data.results.length === 0) {
            showMessage('No matching leads found.');
            return;
          }

          data.results.forEach((lead) => {
            const link = document.createElement('a');
            link.className = 'list-group-item list-group-item-action bg-white';
            link.href = lead.url;
            link.setAttribute('role', 'option');
            const name = document.createElement('div');
            name.className = 'fw-semibold';
            name.textContent = lead.name;
            const detail = document.createElement('small');
            detail.className = 'd-block text-muted';
            detail.textContent = [lead.company, lead.contact, lead.status].filter(Boolean).join(' · ');
            link.append(name, detail);
            results.append(link);
          });
          results.classList.remove('d-none');
          input.setAttribute('aria-expanded', 'true');
        } catch (error) {
          if (error.name !== 'AbortError') showMessage('Unable to search leads.');
        }
      }, 250);
    });

    document.addEventListener('click', (event) => {
      if (!form.parentElement.contains(event.target)) hideResults();
    });
    input.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') hideResults();
    });
  })();
</script>
@endpush
@endif
