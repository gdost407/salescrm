@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
      <div class="col-12">
          <div class="card rounded-2">
              <div class="card-header ps-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                  <div>
                      <h5 class="mb-1">Integrate Google Forms & Webhooks with CRM</h5>
                      <p class="mb-0 text-body-secondary">Automatically capture leads from Google Forms or any external app using Webhook API.</p>
                  </div>
                  <a href="{{ route('integration-api-token') }}" class="btn btn-outline-primary btn-sm">
                      <i class="bx bx-key me-1"></i> Get API Token
                  </a>
              </div>
              <div class="card-body p-3">
                  <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                      <i class="bx bx-info-circle fs-4 me-2"></i>
                      <div>
                          <strong>Webhook Endpoint:</strong> <code class="user-select-all">{{ url('webhook/v1/lead/create') }}</code><br>
                          <strong>Authentication:</strong> Pass your API token in the <code>Authorization: Bearer YOUR_API_TOKEN</code> header or as <code>api_token</code> parameter.
                      </div>
                  </div>

                  <div class="timeline-small">
                      <div class="d-flex mb-4">
                          <div class="timeline-round m-r-30 timeline-line-1 bg-primary" style="display: flex; justify-content: center; align-items: center; width: 50px; height: 50px; border-radius: 50%; background-color: #007bff; color: white; font-size: 24px; font-weight: bold;">
                              1
                          </div>
                          <div class="flex-grow-1 ms-3">
                              <h6>Google Form Creation<span class="pull-right f-14"></span></h6>
                              <p>Go to 
                                  <a href="https://docs.google.com/forms" target="_blank" rel="noopener noreferrer">https://docs.google.com/forms</a>
                                   and create your lead capture form with fields like Name, Email, Mobile, Company, etc.
                              </p>
                              <img src="<?= asset('assets/Web-Fsm/images/blog/sheet-1.png');?>" alt="API Image 1" style="width: 100%;">
                          </div>
                      </div>

                      <div class="d-flex mb-4">
                          <div class="timeline-round m-r-30 timeline-line-1 bg-primary" style="display: flex; justify-content: center; align-items: center; width: 50px; height: 50px; border-radius: 50%; background-color: #007bff; color: white; font-size: 24px; font-weight: bold;">
                              2
                          </div>
                          <div class="flex-grow-1 ms-3">
                              <h6>Link Google Form to Google Sheet<span class="pull-right f-14"></span></h6>
                              <p>Click on <b>'Responses'</b> tab, then click <b>'Link to Sheets'</b> and create a new Google Sheet to store responses.</p>
                              <img src="<?= asset('assets/Web-Fsm/images/blog/sheet-2.png');?>" alt="API Image 1" style="width: 100%;">
                          </div>
                      </div>

                      <div class="d-flex mb-4">
                          <div class="timeline-round m-r-30 timeline-line-1 bg-primary" style="display: flex; justify-content: center; align-items: center; width: 50px; height: 50px; border-radius: 50%; background-color: #007bff; color: white; font-size: 24px; font-weight: bold;">
                              3
                          </div>
                          <div class="flex-grow-1 ms-3">
                              <h6>Navigate to Apps Script<span class="pull-right f-14"></span></h6>
                              <p>Open the linked Google Sheet. From the top menu, click <b>'Extensions'</b> &gt; <b>'Apps Script'</b>.</p>
                              <img src="<?= asset('assets/Web-Fsm/images/blog/sheet-3.png');?>" alt="API Image 1" style="width: 100%;">
                          </div>
                      </div>

                      <div class="d-flex mb-4">
                          <div class="timeline-round m-r-30 timeline-line-1 bg-primary" style="display: flex; justify-content: center; align-items: center; width: 50px; height: 50px; border-radius: 50%; background-color: #007bff; color: white; font-size: 24px; font-weight: bold;">
                              4
                          </div>
                          <div class="flex-grow-1 ms-3">
                              <h6>Paste Webhook Integration Code<span class="pull-right f-14"></span></h6>
                              <p>Remove any existing code in the Apps Script editor, paste the script below, replace <code>YOUR_API_TOKEN</code> with your actual token from the <a href="{{ route('integration-api-token') }}" target="_blank">API Token page</a>, and press <b>Ctrl + S</b> to save:</p>
                              
                              <pre class="bg-dark text-light p-3 rounded" style="text-align: left;"><code class="font-monospace text-light">function onFormSubmit(e) {
    const data = e.values;

    // Replace with your actual API Token generated from CRM API Token page
    const apiToken = "YOUR_API_TOKEN";

    const payload = {
        name: data[1],         // Column B: Full Name (Required)
        email: data[2],        // Column C: Email Address
        mobile: data[3],       // Column D: Mobile Number
        company_name: data[4] || "", // Column E: Company Name (if present)
        source: "Google Form"  // Lead Source tag
    };

    const options = {
        method: "POST",
        contentType: "application/json",
        headers: {
            "Authorization": "Bearer " + apiToken
        },
        payload: JSON.stringify(payload),
        muteHttpExceptions: true
    };

    const url = "{{ url('webhook/v1/lead/create') }}";
    const response = UrlFetchApp.fetch(url, options);
    Logger.log(response.getContentText());
}</code></pre>
                              
                              <img src="<?= asset('assets/Web-Fsm/images/blog/sheet-4.png');?>" alt="API Image 1" style="width: 100%;" class="my-3">
                              
                              <h6 class="mt-4 mb-2">Webhook API Payload Parameters</h6>
                              <p class="text-body-secondary mb-3">Below is the complete list of accepted keys when calling <code>webhook/v1/lead/create</code>:</p>
                              
                              <div class="table-responsive">
                                  <table class="table table-bordered align-middle text-start quote-table">
                                      <thead>
                                          <tr style="background: #f5f5f5;">
                                              <th>Parameter / Key</th>
                                              <th>Status</th>
                                              <th>Description</th>
                                              <th>Example Value</th>
                                          </tr>
                                      </thead>
                                      <tbody>
                                          <tr>
                                              <td><code class="fw-bold">name</code></td>
                                              <td><span class="badge bg-danger">Required</span></td>
                                              <td>Full name of the lead contact</td>
                                              <td>John Doe</td>
                                          </tr>
                                          <tr>
                                              <td><code>email</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>Valid email address</td>
                                              <td>john@example.com</td>
                                          </tr>
                                          <tr>
                                              <td><code>mobile</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>Primary mobile / phone number</td>
                                              <td>+11234567890</td>
                                          </tr>
                                          <tr>
                                              <td><code>alternate_mobile</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>Secondary phone number</td>
                                              <td>+19876543210</td>
                                          </tr>
                                          <tr>
                                              <td><code>company_name</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>Lead's company or business name</td>
                                              <td>Acme Corporation</td>
                                          </tr>
                                          <tr>
                                              <td><code>job_title</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>Job title / designation</td>
                                              <td>Marketing Manager</td>
                                          </tr>
                                          <tr>
                                              <td><code>deal_amount</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>Estimated deal value (numeric, default: 0)</td>
                                              <td>5000</td>
                                          </tr>
                                          <tr>
                                              <td><code>stage</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>Pipeline stage (default: system default stage)</td>
                                              <td>Lead</td>
                                          </tr>
                                          <tr>
                                              <td><code>status</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>Lead status (default: system default status)</td>
                                              <td>New</td>
                                          </tr>
                                          <tr>
                                              <td><code>source</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>Lead origin source (default: "Webhook")</td>
                                              <td>Google Form</td>
                                          </tr>
                                          <tr>
                                              <td><code>priority</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>Priority level: <code>low</code>, <code>medium</code>, <code>high</code>, <code>urgent</code></td>
                                              <td>high</td>
                                          </tr>
                                          <tr>
                                              <td><code>address</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>Street address</td>
                                              <td>123 Business Way</td>
                                          </tr>
                                          <tr>
                                              <td><code>city</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>City name</td>
                                              <td>New York</td>
                                          </tr>
                                          <tr>
                                              <td><code>state</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>State or province</td>
                                              <td>NY</td>
                                          </tr>
                                          <tr>
                                              <td><code>country</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>Country name</td>
                                              <td>United States</td>
                                          </tr>
                                          <tr>
                                              <td><code>pincode</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>Postal / Zip code</td>
                                              <td>10001</td>
                                          </tr>
                                          <tr>
                                              <td><code>description</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>Lead description or form message</td>
                                              <td>Interested in product demo.</td>
                                          </tr>
                                          <tr>
                                              <td><code>notes</code></td>
                                              <td><span class="badge bg-secondary">Optional</span></td>
                                              <td>Internal notes</td>
                                              <td>Submitted via contact form.</td>
                                          </tr>
                                      </tbody>
                                  </table>
                              </div>
                          </div>
                      </div>

                      <div class="d-flex mb-4">
                          <div class="timeline-round m-r-30 timeline-line-1 bg-primary" style="display: flex; justify-content: center; align-items: center; width: 50px; height: 50px; border-radius: 50%; background-color: #007bff; color: white; font-size: 24px; font-weight: bold;">
                              5
                          </div>
                          <div class="flex-grow-1 ms-3">
                              <h6>Add Triggers<span class="pull-right f-14"></span></h6>
                              <p>Click on <b>'Triggers'</b> from the left sidebar menu (clock icon), then click <b>'Add Trigger'</b>.</p>
                              <p>For adding the trigger, select event type <b>'On form submit'</b> and function <code>onFormSubmit</code> as shown in the screenshot below, then click <b>'Save'</b>.</p>
                              <p>Follow the Google authorization prompt:</p>
                              <p>1. Select your Google account and click on <b>Advanced</b></p>
                              <p>2. Click on <b>'Go to {your project name} (unsafe)'</b></p>
                              <p>3. Click <b>'Allow'</b> to grant necessary permissions.</p>
                              <img src="<?= asset('assets/Web-Fsm/images/blog/sheet-5.png');?>" alt="API Image 5" style="width: 100%;" class="mb-3">
                              <p>Make sure your trigger settings match the image below:</p>
                              <img src="<?= asset('assets/Web-Fsm/images/blog/sheet-6.png');?>" alt="API Image 6" style="width: 100%;">
                          </div>
                      </div>

                      <div class="d-flex mb-4">
                          <div class="timeline-round m-r-30 timeline-line-1 bg-primary" style="display: flex; justify-content: center; align-items: center; width: 50px; height: 50px; border-radius: 50%; background-color: #007bff; color: white; font-size: 24px; font-weight: bold;">
                              6
                          </div>
                          <div class="flex-grow-1 ms-3">
                              <h6>Publish Google Form<span class="pull-right f-14"></span></h6>
                              <p>Open your Google Form editor and click on the <b>'Send'</b> or <b>'Publish'</b> button to get the shareable link.</p>
                              <img src="<?= asset('assets/Web-Fsm/images/blog/sheet-7.png');?>" alt="API Image 7" style="width: 100%;">
                          </div>
                      </div>

                      <div class="d-flex">
                          <div class="timeline-round m-r-30 timeline-line-1 bg-primary" style="display: flex; justify-content: center; align-items: center; width: 50px; height: 50px; border-radius: 50%; background-color: #007bff; color: white; font-size: 24px; font-weight: bold;">
                              7
                          </div>
                          <div class="flex-grow-1 ms-3">
                              <h6>Collect Leads Automatically<span class="pull-right f-14"></span></h6>
                              <p>Share your Google Form link via Email, Social Media, or Website. Whenever someone submits the form, the lead will be created instantly inside your CRM!</p>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>
@endsection
