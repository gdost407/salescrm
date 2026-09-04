<!doctype html>
<html lang="en">
<head>
  @include('partials.head')
  <style>
    :root { --ink:#263445; --muted:#697586; --blue:#696cff; --orange:#ffab00; --paper:#f7f8fc; --line:#dfe3ea; }
    body { min-width:320px; background:var(--paper); color:var(--ink); }
    .crm-landing { min-height:100vh; background-color:var(--paper); background-image:linear-gradient(rgba(105,108,255,.045) 1px,transparent 1px),linear-gradient(90deg,rgba(105,108,255,.045) 1px,transparent 1px); background-size:32px 32px; }
    .crm-nav,.crm-hero,.crm-proof,.crm-features,.crm-footer { width:min(1180px,calc(100% - 32px)); margin:auto; }
    .crm-nav { display:flex; align-items:center; justify-content:space-between; padding:24px 0; }
    .crm-brand { display:inline-flex; align-items:center; gap:10px; color:var(--ink); font-size:1.15rem; font-weight:700; text-decoration:none; }
    .crm-mark { display:grid; width:38px; height:38px; place-items:center; border-radius:10px; background:var(--blue); color:#fff; font-size:1.25rem; }
    .crm-actions { display:flex; gap:10px; align-items:center; }
    .crm-hero { display:grid; grid-template-columns:minmax(0,.95fr) minmax(420px,1.05fr); align-items:center; gap:72px; padding:72px 0 78px; }
    .crm-kicker { display:inline-flex; gap:8px; align-items:center; margin-bottom:18px; color:var(--blue); font-size:.76rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase; }
    .crm-kicker:before { width:26px; height:2px; background:var(--orange); content:""; }
    .crm-hero h1 { max-width:620px; margin-bottom:22px; font-size:clamp(2.7rem,5vw,5rem); font-weight:700; letter-spacing:-.045em; line-height:1.02; }
    .crm-hero h1 span { color:var(--blue); }
    .crm-copy { max-width:530px; margin-bottom:30px; color:var(--muted); font-size:1.08rem; line-height:1.75; }
    .crm-hero-buttons { display:flex; flex-wrap:wrap; gap:12px; }
    .crm-preview { padding:16px; border:1px dashed var(--line); background:rgba(255,255,255,.78); box-shadow:0 18px 50px rgba(38,52,69,.1); }
    .crm-window { overflow:hidden; border:1px dashed #cfd5df; background:#fff; }
    .crm-toolbar { display:flex; justify-content:space-between; padding:15px 18px; border-bottom:1px dashed #e0e4eb; }
    .crm-dots { display:flex; gap:5px; }
    .crm-dots i { width:7px; height:7px; border-radius:50%; background:#d9dde5; }
    .crm-label { color:var(--muted); font-size:.72rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
    .crm-window-body { padding:22px; }
    .crm-window-heading { display:flex; align-items:end; justify-content:space-between; margin-bottom:18px; }
    .crm-window-heading strong { display:block; font-size:1.45rem; }
    .crm-window-heading small { color:var(--muted); }
    .crm-pipeline { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
    .crm-column { min-height:218px; padding:10px; border:1px dashed var(--line); background:#fafbfe; }
    .crm-column header { display:flex; justify-content:space-between; margin-bottom:10px; color:var(--muted); font-size:.68rem; font-weight:700; text-transform:uppercase; }
    .crm-column header span { color:var(--blue); }
    .crm-lead { margin-bottom:8px; padding:10px; border:1px dashed #e0e4eb; background:#fff; }
    .crm-lead strong,.crm-lead small { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .crm-lead strong { font-size:.77rem; }
    .crm-lead small { margin-top:4px; color:var(--muted); font-size:.66rem; }
    .crm-proof { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; padding:22px 0 70px; }
    .crm-proof-item { display:flex; align-items:center; gap:12px; padding:18px; border-top:1px dashed var(--line); }
    .crm-proof-item i { color:var(--blue); font-size:1.4rem; }
    .crm-proof-item strong,.crm-proof-item span { display:block; }
    .crm-proof-item strong { font-size:.9rem; }
    .crm-proof-item span { margin-top:3px; color:var(--muted); font-size:.78rem; }
    .crm-features { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; padding-bottom:76px; }
    .crm-feature { padding:25px; border:1px dashed var(--line); background:rgba(255,255,255,.74); }
    .crm-feature i { display:grid; width:42px; height:42px; margin-bottom:22px; place-items:center; border-radius:9px; background:#eeefff; color:var(--blue); font-size:1.25rem; }
    .crm-feature h2 { margin-bottom:10px; font-size:1.05rem; }
    .crm-feature p { margin:0; color:var(--muted); font-size:.88rem; line-height:1.65; }
    .crm-footer { display:flex; justify-content:space-between; gap:16px; padding:20px 0 28px; border-top:1px dashed var(--line); color:var(--muted); font-size:.78rem; }
    @media (max-width:900px) { .crm-hero { grid-template-columns:1fr; gap:38px; padding-top:44px; } }
    @media (max-width:640px) { .crm-nav { padding:16px 0; } .crm-actions .btn-outline-secondary { display:none; } .crm-hero { width:min(calc(100% - 24px),560px); padding:38px 0 46px; } .crm-hero h1 { font-size:2.65rem; } .crm-preview { padding:8px; } .crm-window-body { padding:12px; } .crm-pipeline,.crm-proof,.crm-features { grid-template-columns:1fr; } .crm-column { min-height:auto; } .crm-proof,.crm-features,.crm-footer { width:min(calc(100% - 24px),560px); } .crm-footer { flex-direction:column; } }
  </style>
</head>
<body>
<div class="crm-landing">
  <nav class="crm-nav">
    <a class="crm-brand" href="{{ route('home') }}"><span class="crm-mark"><i class="bx bx-line-chart"></i></span><span>{{ config('app.name', 'Sales CRM') }}</span></a>
    <div class="crm-actions">
      @auth
        <a class="btn btn-primary btn-sm" href="{{ route('dashboard') }}">Open dashboard <i class="bx bx-right-arrow-alt ms-1"></i></a>
      @else
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('login') }}">Log in</a>
        <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Create account <i class="bx bx-right-arrow-alt ms-1"></i></a>
      @endauth
    </div>
  </nav>
  <main>
    <section class="crm-hero">
      <div>
        <div class="crm-kicker">Sales operations, in one place</div>
        <h1>Turn every lead into a <span>clear next step.</span></h1>
        <p class="crm-copy">A focused workspace for capturing leads, moving deals through your pipeline, and keeping every customer conversation in view.</p>
        <div class="crm-hero-buttons">
          @auth
            <a class="btn btn-primary btn-lg" href="{{ route('dashboard') }}">Go to dashboard <i class="bx bx-right-arrow-alt ms-1"></i></a>
          @else
            <a class="btn btn-primary btn-lg" href="{{ route('register') }}">Start building your pipeline <i class="bx bx-right-arrow-alt ms-1"></i></a>
            <a class="btn btn-outline-secondary btn-lg" href="{{ route('login') }}">I already have an account</a>
          @endauth
        </div>
      </div>
      <div class="crm-preview" aria-label="Sales pipeline preview">
        <div class="crm-window">
          <div class="crm-toolbar"><div class="crm-dots"><i></i><i></i><i></i></div><span class="crm-label">Pipeline overview</span></div>
          <div class="crm-window-body">
            <div class="crm-window-heading"><div><small>Today’s workspace</small><strong>Sales pipeline</strong></div><span class="badge bg-label-success">On track</span></div>
            <div class="crm-pipeline">
              <div class="crm-column"><header><span>New</span><span>04</span></header><div class="crm-lead"><strong>Northstar Retail</strong><small>Website inquiry</small></div><div class="crm-lead"><strong>Rakesh Kumar</strong><small>Follow-up today</small></div></div>
              <div class="crm-column"><header><span>Qualified</span><span>07</span></header><div class="crm-lead"><strong>Juneja Traders</strong><small>₹ 25,000 opportunity</small></div><div class="crm-lead"><strong>Acme Industries</strong><small>Meeting scheduled</small></div></div>
              <div class="crm-column"><header><span>Won</span><span>12</span></header><div class="crm-lead"><strong>Riverside Foods</strong><small>Closed this week</small></div><div class="crm-lead"><strong>Vivid Commerce</strong><small>₹ 48,500 value</small></div></div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="crm-proof" aria-label="CRM capabilities">
      <div class="crm-proof-item"><i class="bx bx-target-lock"></i><div><strong>One view of every opportunity</strong><span>Know what needs attention next.</span></div></div>
      <div class="crm-proof-item"><i class="bx bx-group"></i><div><strong>Aligned teams</strong><span>Keep ownership and activity visible.</span></div></div>
      <div class="crm-proof-item"><i class="bx bx-pulse"></i><div><strong>Momentum you can measure</strong><span>Move from intake to close with confidence.</span></div></div>
    </section>
    <section class="crm-features">
      <article class="crm-feature"><i class="bx bx-user-plus"></i><h2>Capture leads quickly</h2><p>Bring in leads from your team, forms, or integrations and give every record a clear owner.</p></article>
      <article class="crm-feature"><i class="bx bx-columns"></i><h2>Move work forward</h2><p>Use the pipeline to see stages, priorities, value, and the next action without digging through pages.</p></article>
      <article class="crm-feature"><i class="bx bx-message-square-detail"></i><h2>Keep the context</h2><p>Log notes, calls, meetings, and follow-ups alongside the lead they belong to.</p></article>
    </section>
  </main>
  <footer class="crm-footer"><span>{{ config('app.name', 'Sales CRM') }}</span><span>Built for focused sales teams.</span></footer>
</div>
@include('partials.foot')
</body>
</html>
