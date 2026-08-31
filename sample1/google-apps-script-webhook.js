// Google Apps Script webhook for LynxCom school Starter portal sample
// Deploy as Web App, then set the deployed URL as SCHOOL_GOOGLE_SHEET_WEBHOOK_URL on the server.
const SPREADSHEET_ID = '11-pSg4ZocQLlRDjejd4lSGuqq17TxfEwVgcoR5A0k1s';
function doPost(e) {
  const payload = JSON.parse(e.postData.contents || '{}');
  const formType = payload.form_type;
  const row = payload.row;
  const allowed = ['Admissions','Fee_Payments','Expenses','Attendance','Students','Portal_Submissions_Log'];
  if (!allowed.includes(formType) || !Array.isArray(row)) {
    return ContentService.createTextOutput(JSON.stringify({ok:false,error:'Invalid payload'})).setMimeType(ContentService.MimeType.JSON);
  }
  const ss = SpreadsheetApp.openById(SPREADSHEET_ID);
  const sh = ss.getSheetByName(formType);
  sh.appendRow(row);
  return ContentService.createTextOutput(JSON.stringify({ok:true, sheet:formType})).setMimeType(ContentService.MimeType.JSON);
}
