function checkNewLeads() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();

  // Sheet1 setup
  const sheet1 = ss.getSheetByName("May26");
  if (sheet1) {
    processSheet(sheet1, {
      fullNameCol: 2,
      emailCol: 4, 
      phoneCol: 5,
      jobTitleCol: 6,
      cityCol: 7,
      stateCol: 8,
      orderCol: 12,
      descCols: [9, 10, 11, 13] 
    });
  }

}

function doPost(e) {

  try {

    var ss =
    SpreadsheetApp.openById(
    "11pDkIsfCognzvJD5hAcdIQBdEd2gJpKcbiXNX_1Q4Kw");

    var sheet =
    ss.getSheets()[0];

    var data =
    JSON.parse(e.postData.contents);

    sheet.appendRow([

      data.Date || "",
      data.Name || "",
      data.Number || "",
      data.Email || "",
      data.WhatsappNumber || "",
      data.CompanyName || "",
      data.City || "",
      data.State || "",
      data.ShopExists || "",
      data.BusinessSize || "",
      data.AapKaStaffKiAnumanitSankhya || "",
      data.Budget || "",
      data.ShopSqFt || "",
      data.NoOfSalesman || "",
      data.SoftwareManual || "",
      data.Executive || "",
      data.Status || "",
      data.SubStatus || "",
      data.DemoDate || "",
      data.Attempt1 || "",
      data.Attempt2 || "",
      data.Attempt3 || "",
      data.Attempt4 || "",
      data.Attempt5 || "",

      "",
      "",
      "",
      "",

      data.FormName || "",

      ""

    ]);

    SpreadsheetApp.flush();

    return ContentService
      .createTextOutput("SUCCESS");

  }
  catch(ex) {

    return ContentService
      .createTextOutput(ex.toString());

  }

}

function processSheet(sheet, mapping) {
  const data = sheet.getDataRange().getValues();
  const statusCol = sheet.getLastColumn();
  const url = "https://app.lockene.us/v1/Webhook/Sheet/223";

  for (let i = 1; i < data.length; i++) {
    const row = data[i];
    const status = row[statusCol - 1];

    if (status === "") {
      const payload = {
        full_name: String(row[mapping.fullNameCol - 1] || "").trim(),
        email: String(row[mapping.emailCol - 1] || "").trim(),
        phone: String(row[mapping.phoneCol - 1] || "").trim(),
        job_title: String(row[mapping.jobTitleCol - 1] || "").trim(),
        city: String(row[mapping.cityCol - 1] || "").trim(),
        state: String(row[mapping.stateCol - 1] || "").trim(),
        order_value: String(row[mapping.orderCol - 1] || "").trim(),
        description:
          "Do you own a clothing shop?: " + String(row[mapping.descCols[0] - 1] || "").trim() + " | " +
          "Area?: " + String(row[mapping.descCols[1] - 1] || "").trim() + " | " +
          "How many salesmen do you have?: " + String(row[mapping.descCols[2] - 1] || "").trim()  + " | " +
          "Shop sq.ft?: " + String(row[mapping.descCols[3] - 1] || "").trim()
      };

      Logger.log("Payload for row " + (i + 1) + ": " + JSON.stringify(payload));

      const options = {
        method: "post",
        contentType: "application/x-www-form-urlencoded",
        payload: payload,
        muteHttpExceptions: true
      };

      try {
        const response = UrlFetchApp.fetch(url, options);
        const code = response.getResponseCode();
        const body = response.getContentText();

        let messageToSave;
        try {
          const json = JSON.parse(body);
          messageToSave = json.message || body;
        } catch (e) {
          messageToSave = body;
        }

        sheet.getRange(i + 1, statusCol).setValue(messageToSave);

        if (code >= 400) {
          Logger.log(`API error (code ${code}): ${body}. Stopping.`);
          return;
        }

      } catch (err) {
        Logger.log(`Failed to send row ${i + 1}: ${err}`);
        sheet.getRange(i + 1, statusCol).setValue("Request failed: " + err.message);
        Logger.log("Stopping.");
        return;
      }
    }
  }
}