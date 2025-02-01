<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>A/R Payment with Office Dropdown</title>
  <style>
    :root {
      --primary-bg: #f2f2f2;
      --primary-text: #000000;
      --card-bg: #ffffff;
      --input-bg: #ffffff;
      --input-text: #000000;
      --switch-bg-off: #ccc;
      --switch-bg-on: #007BFF;
      --button-bg: #007BFF;
      --button-bg-hover: #0056b3;
    }

    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: var(--primary-bg);
      color: var(--primary-text);
    }

    .container-wrapper {
      width: 100%;
      display: flex;
      justify-content: center;
      margin-top: 40px;
    }

    .container {
      width: 90%;
      max-width: 600px;
    }

    .card {
      background-color: var(--card-bg);
      border-radius: 4px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
      margin-bottom: 20px;
      overflow: hidden;
    }

    .card-content {
      padding: 20px;
    }

    .card-content h2 {
      margin-top: 0;
    }

    .form-row {
      margin-bottom: 15px;
      display: flex;
      flex-direction: column;
    }

    .form-row label {
      margin-bottom: 5px;
      font-weight: bold;
    }

    .form-row input[type="text"],
    .form-row input[list],
    .form-row select,
    .form-row input[type="file"] {
      padding: 8px;
      font-size: 14px;
      background-color: var(--input-bg);
      color: var(--input-text);
      border: 1px solid #ccc;
    }

    .toggle-row {
      display: flex;
      gap: 10px;
    }

    .button-row {
      display: flex;
      gap: 10px;
      margin-top: 20px;
      flex-wrap: wrap;
    }

    button {
      cursor: pointer;
      padding: 10px 20px;
      background-color: var(--button-bg);
      color: #ffffff;
      border: none;
      border-radius: 4px;
      font-size: 14px;
    }
    button:hover {
      background-color: var(--button-bg-hover);
    }

    .switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 24px;
    }
    .switch input {
      position: absolute;
      top: 0;
      left: 0;
      width: 50px;
      height: 24px;
      opacity: 0.01;  /* keep minimal so it can still receive focus */
      cursor: pointer;
    }
    .switch input:focus + .slider {
      outline: 2px dotted #333; 
    }
    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: .4s;
      border-radius: 24px;
    }
    .slider:before {
      position: absolute;
      content: "";
      height: 18px;
      width: 18px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }
    input:checked + .slider {
      background-color: #27c160d9;
    }
    input:checked + .slider:before {
      transform: translateX(26px);
    }

    /* DARK MODE OVERRIDES */
    body.dark-mode {
      --primary-bg: #121212;
      --primary-text: #ffffff;
      --card-bg: #1e1e1e;
      --input-bg: #2c2c2c;
      --input-text: #ffffff;
      --switch-bg-off: #888;
      --switch-bg-on: #66aaff;
      --button-bg: #3b82f6;
      --button-bg-hover: #2563eb;
    }

    /* Split Payment Container */
    .split-payment-container {
      border: 1px solid #ccc;
      background-color: var(--input-bg);
      padding: 10px;
      margin-top: 10px;
      margin-bottom: 10px;
      display: none;
    }
    .split-row {
      display: flex;
      gap: 10px;
      margin-bottom: 10px;
    }
    .split-row select,
    .split-row input[type="text"] {
      width: 150px;
    }
  </style>
</head>
<body>
  <div class="container-wrapper">
    <div class="container">
      <div class="card">
        <div class="card-content">
          <h2>A/R Payment</h2>

          <!-- Dark Mode Toggle -->
          <div class="form-row toggle-row">
            <label class="switch">
              <input type="checkbox" id="darkModeSwitch">
              <span class="slider"></span>
            </label>
            <label for="darkModeSwitch" style="margin-bottom: 0;">Dark Mode?</label>
          </div>

          <!-- ADDITIONS: Date (MM/DD/YYYY), Defendant Name -->
          <div class="form-row" style="flex-direction: row; gap: 20px;">
            <!-- Defendant Name -->
            <div style="flex: 1;">
              <label for="defendantNameInput">Defendant Name:</label>
              <input id="defendantNameInput" type="text" placeholder="Defendant Name">
            </div>
            <!-- Date -->
            <div style="flex: 1;">
              <label for="paymentDateInput">Date:</label><br>
              <input id="paymentDateInput" type="text" placeholder="MM/DD/YYYY">
            </div>
          </div>

          <!-- SINGLE PAYMENT ROW -->
          <div class="form-row" id="singlePaymentRow" style="flex-direction: row; gap: 20px;">
            <!-- Payment Amount -->
            <div style="flex: 1;">
              <label for="paymentAmountInput">Payment Amount ($):</label>
              <input id="paymentAmountInput" type="text" placeholder="$0.00">
            </div>
            <!-- Payment Type -->
            <div style="flex: 1;">
              <label for="paymentTypeSelect">Payment Type:</label><br>
              <select id="paymentTypeSelect">
                <option value="Cash">Cash</option>
                <option value="Check">Check</option>
                <option value="Money Order">Money Order</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Split Payment">Split Payment</option>
              </select>
            </div>
          </div>

          <!-- Split Payment Container -->
          <div class="split-payment-container" id="splitPaymentContainer">
            <p style="margin-top:0; font-weight:bold;">Split Payment Details:</p>
            <div id="splitRows"></div>
            <button type="button" id="addSplitRowBtn" style="margin-top:5px;">+ Add Payment Method</button>
          </div>

          <!-- Toggle Switch for Partial Payment -->
          <div class="form-row toggle-row">
            <label class="switch">
              <input type="checkbox" id="partialPaymentSwitch">
              <span class="slider"></span>
            </label>
            <label for="partialPaymentSwitch" style="margin-bottom: 0;">Partial Payment?</label>
          </div>

          <!-- Actual Amount Due (shown only if partial switch is on) -->
          <div class="form-row" id="partialPaymentRow" style="display: none;">
            <label id="partialPaymentLabel" for="partialPaymentInput">Actual Amount Due:</label>
            <input id="partialPaymentInput" type="text" list="partialPaymentDataList" placeholder="$0.00">
            <datalist id="partialPaymentDataList">
              <!-- Populated in JS (increments of $25) -->
            </datalist>
          </div>

          <!-- Office Dropdown -->
          <div class="form-row">
            <label for="officeSelect">Office:</label>
            <select id="officeSelect">
              <option value="Cobb" selected>Cobb</option>
              <option value="12732">Barrow</option>
              <option value="12733">Bartow</option>
              <option value="12734">Carroll</option>
              <option value="12735">Cherokee</option>
              <option value="12736">Clarke</option>
              <option value="12738">Floyd</option>
              <option value="12739">Gordon</option>
              <option value="12740">Gwinnett</option>
              <option value="12741">Haralson</option>
              <option value="12742">Paulding</option>
              <option value="14385">Pickens</option>
              <option value="12743">Polk</option>
            </select>
          </div>

          <!-- County Selection (Account) -->
          <div class="form-row">
            <label for="countySelect">Account:</label>
            <select id="countySelect">
              <option value="Cobb" selected>Cobb</option>
              <option value="12732">Barrow</option>
              <option value="12733">Bartow</option>
              <option value="12734">Carroll</option>
              <option value="12735">Cherokee</option>
              <option value="12736">Clarke</option>
              <option value="12738">Floyd</option>
              <option value="12739">Gordon</option>
              <option value="12740">Gwinnett</option>
              <option value="12741">Haralson</option>
              <option value="12742">Paulding</option>
              <option value="14385">Pickens</option>
              <option value="12743">Polk</option>
            </select>
          </div>

          <!-- Employee Name -->
          <div class="form-row">
            <label for="employeeNameInput">Employee Name:</label>
            <input id="employeeNameInput" list="employeeNameSelect" placeholder="Choose Employee..." value="Lucas">
            <datalist id="employeeNameSelect">
              <!-- ... big list of employees (omitted for brevity) ... -->
              <option value="Lucas"></option>
              <option value="Adam P."></option>
              <option value="Amber M."></option>
              <!-- etc. -->
            </datalist>
          </div>

          <!-- Image Upload (Receipt) -->
          <div class="form-row">
            <label for="receiptUpload">Receipt Image (optional):</label>
            <input type="file" id="receiptUpload" name="receiptFile" accept="image/*">
          </div>

          <!-- Buttons -->
          <div class="button-row">
            <!-- VISIBLE Button #1: Save -->
            <button id="saveBtn" type="button">Save</button>

            <!-- VISIBLE Button #2: Save and Send -->
            <button id="saveAndSendBtn" type="button">Save and Send</button>

            <!-- HIDDEN Extra Buttons (CSV, XLS, XLSX, Save to PHP) to keep structure but not show -->
            <button id="exportBtn" type="button" style="display:none;">Export to Excel (CSV)</button>
            <button id="saveToPhpBtn" type="button" style="display:none;">Save to PHP/MySQL</button>
            <button id="exportXlsBtn" type="button" style="display:none;">Export to XLS</button>
            <button id="exportXlsxBtn" type="button" style="display:none;">Export to XLSX</button>
          </div>
        </div> <!-- .card-content -->
      </div> <!-- .card -->
    </div> <!-- .container -->
  </div> <!-- .container-wrapper -->

  <!-- 1) Load SheetJS for XLSX export -->
  <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>

  <script>
    // =============== References ===============
    const darkModeSwitch = document.getElementById("darkModeSwitch");
    const officeSelect = document.getElementById("officeSelect");
    const countySelect = document.getElementById("countySelect");
    const employeeNameInput = document.getElementById("employeeNameInput");
    const paymentDateInput = document.getElementById("paymentDateInput");
    const defendantNameInput = document.getElementById("defendantNameInput");

    const paymentTypeSelect = document.getElementById("paymentTypeSelect");
    const singlePaymentRow = document.getElementById("singlePaymentRow");
    const paymentAmountInput = document.getElementById("paymentAmountInput");

    const splitPaymentContainer = document.getElementById("splitPaymentContainer");
    const splitRowsContainer = document.getElementById("splitRows");
    const addSplitRowBtn = document.getElementById("addSplitRowBtn");

    const partialPaymentSwitch = document.getElementById("partialPaymentSwitch");
    const partialPaymentRow = document.getElementById("partialPaymentRow");
    const partialPaymentInput = document.getElementById("partialPaymentInput");
    const partialPaymentLabel = document.getElementById("partialPaymentLabel");
    const partialPaymentDataList = document.getElementById("partialPaymentDataList");

    const receiptUpload = document.getElementById("receiptUpload");

    // Buttons
    const saveBtn = document.getElementById("saveBtn");
    const saveAndSendBtn = document.getElementById("saveAndSendBtn");

    // Hidden buttons
    const exportBtn = document.getElementById("exportBtn");
    const saveToPhpBtn = document.getElementById("saveToPhpBtn");
    const exportXlsBtn = document.getElementById("exportXlsBtn");
    const exportXlsxBtn = document.getElementById("exportXlsxBtn");

    // =============== AUTO-SET DATE IN MM/DD/YYYY ===============
    window.addEventListener("load", function() {
      const today = new Date();
      const yyyy = today.getFullYear();
      let mm = (today.getMonth() + 1).toString().padStart(2, "0");
      let dd = today.getDate().toString().padStart(2, "0");
      paymentDateInput.value = `${mm}/${dd}/${yyyy}`;
    });

    // =============== PARTIAL PAYMENT LIST (increments of 25) ===============
    function populatePartialPaymentDataList() {
      for (let i = 25; i <= 10000; i += 25) {
        const optionElement = document.createElement("option");
        optionElement.value = i.toFixed(2);
        partialPaymentDataList.appendChild(optionElement);
      }
    }
    populatePartialPaymentDataList();

    // =============== Format currency on blur ===============
    function formatToUSD(inputElement) {
      let rawValue = inputElement.value.trim();
      if (!rawValue) {
        inputElement.value = "";
        return;
      }
      rawValue = rawValue.replace(/[^\d.]/g, "");
      const parsedValue = parseFloat(rawValue);
      if (!isNaN(parsedValue)) {
        inputElement.value = parsedValue.toLocaleString("en-US", {
          style: "currency",
          currency: "USD"
        });
      } else {
        inputElement.value = "";
      }
    }
    paymentAmountInput.addEventListener("blur", function() {
      formatToUSD(paymentAmountInput);
    });
    partialPaymentInput.addEventListener("blur", function() {
      formatToUSD(partialPaymentInput);
    });

    // =============== TOGGLE PARTIAL PAYMENT ROW ===============
    partialPaymentSwitch.addEventListener("change", function() {
      if (partialPaymentSwitch.checked) {
        partialPaymentRow.style.display = "block";
        partialPaymentLabel.textContent = "Actual Amount Due:";
      } else {
        partialPaymentRow.style.display = "none";
      }
    });

    // =============== Helper: parse $strings => float ===============
    function parseCurrencyString(currencyString) {
      if (!currencyString) {
        return 0;
      }
      const numericString = currencyString.replace(/[^\d.]/g, "");
      const value = parseFloat(numericString);
      return isNaN(value) ? 0 : value;
    }

    // =============== SPLIT PAYMENT HANDLING ===============
    paymentTypeSelect.addEventListener("change", function() {
      if (paymentTypeSelect.value === "Split Payment") {
        singlePaymentRow.style.display = "none";
        splitPaymentContainer.style.display = "block";
        if (splitRowsContainer.children.length === 0) {
          addSplitRow();
          addSplitRow();
        }
      } else {
        singlePaymentRow.style.display = "block";
        splitPaymentContainer.style.display = "none";
      }
    });

    const paymentTypes = ["Cash", "Check", "Money Order", "Credit Card"];

    addSplitRowBtn.addEventListener("click", function() {
      addSplitRow();
    });

    function addSplitRow() {
      const rowDiv = document.createElement("div");
      rowDiv.classList.add("split-row");

      const selectEl = document.createElement("select");
      paymentTypes.forEach(pt => {
        const opt = document.createElement("option");
        opt.value = pt;
        opt.textContent = pt;
        selectEl.appendChild(opt);
      });

      const amountInput = document.createElement("input");
      amountInput.type = "text";
      amountInput.placeholder = "$0.00";
      amountInput.addEventListener("blur", function() {
        formatToUSD(amountInput);
      });

      const removeBtn = document.createElement("button");
      removeBtn.type = "button";
      removeBtn.textContent = "Remove";
      removeBtn.style.backgroundColor = "#d9534f";
      removeBtn.addEventListener("click", function() {
        rowDiv.remove();
      });

      rowDiv.appendChild(selectEl);
      rowDiv.appendChild(amountInput);
      rowDiv.appendChild(removeBtn);
      splitRowsContainer.appendChild(rowDiv);
    }

    // =============== SAVE LOCALLY ===============
    // (If you still want to store in localStorage, you can keep this function.)
    function saveLocally() {
      let dailyPayments = JSON.parse(localStorage.getItem("dailyPayments")) || [];
      // Gather form data
      const dateValue = paymentDateInput.value.trim();
      const defName   = defendantNameInput.value.trim();
      const officeVal = officeSelect.value;
      const countyVal = countySelect.value;
      const empName   = employeeNameInput.value.trim();

      let totalCash=0, totalCheck=0, totalMO=0, totalCC=0;
      const isSplit = (paymentTypeSelect.value === "Split Payment");
      if (isSplit) {
        const rowDivs = splitRowsContainer.querySelectorAll(".split-row");
        rowDivs.forEach(div => {
          const selectedType = div.querySelector("select").value;
          const amtVal = parseCurrencyString(div.querySelector("input").value);
          switch (selectedType) {
            case "Cash": totalCash += amtVal; break;
            case "Check": totalCheck += amtVal; break;
            case "Money Order": totalMO += amtVal; break;
            case "Credit Card": totalCC += amtVal; break;
          }
        });
      } else {
        const amtVal = parseCurrencyString(paymentAmountInput.value);
        const selectedType = paymentTypeSelect.value;
        switch (selectedType) {
          case "Cash": totalCash = amtVal; break;
          case "Check": totalCheck = amtVal; break;
          case "Money Order": totalMO = amtVal; break;
          case "Credit Card": totalCC = amtVal; break;
        }
      }

      let partialActive = partialPaymentSwitch.checked;
      let partialAmt    = parseCurrencyString(partialPaymentInput.value);
      let actualDue     = partialActive ? partialAmt : (totalCash+totalCheck+totalMO+totalCC);

      const record = {
        date: dateValue,
        defendantName: defName,
        office: officeVal,
        county: countyVal,
        employeeName: empName,
        cash: totalCash,
        check: totalCheck,
        moneyOrder: totalMO,
        creditCard: totalCC,
        partialPayment: partialActive,
        actualAmtDue: actualDue,
        timestamp: new Date().toISOString()
      };
      dailyPayments.push(record);
      localStorage.setItem("dailyPayments", JSON.stringify(dailyPayments));
    }

    // =============== SEND EMAIL (Optional) ===============
    function sendEmail() {
      // If you want to keep your existing email logic...
      const subject = "A/R Payment Information";
      // Build a mailto link or do something else
      // ...
      alert("Send Email triggered (placeholder).");
    }

    // =============== SAVE to PHP/MySQL + Generate PDF ===============
    // Instead of simple fetch JSON, we do a FormData approach so we can send an image.
    function saveToPHPAndGeneratePDF() {
      // 1) Build FormData
      const formData = new FormData();

      // Office, county, employee, date, defendant
      formData.append("office", officeSelect.value);
      formData.append("county", countySelect.value);
      formData.append("employeeName", employeeNameInput.value.trim());
      formData.append("dateValue", paymentDateInput.value.trim());
      formData.append("defendantName", defendantNameInput.value.trim());

      // Payment amounts
      let totalCash=0, totalCheck=0, totalMO=0, totalCC=0;
      const isSplit = (paymentTypeSelect.value === "Split Payment");

      if (isSplit) {
        const rowDivs = splitRowsContainer.querySelectorAll(".split-row");
        rowDivs.forEach(div => {
          const selectedType = div.querySelector("select").value;
          const amtVal = parseCurrencyString(div.querySelector("input").value);
          switch (selectedType) {
            case "Cash": totalCash += amtVal; break;
            case "Check": totalCheck += amtVal; break;
            case "Money Order": totalMO += amtVal; break;
            case "Credit Card": totalCC += amtVal; break;
          }
        });
      } else {
        const amtVal = parseCurrencyString(paymentAmountInput.value);
        const selectedType = paymentTypeSelect.value;
        switch (selectedType) {
          case "Cash": totalCash = amtVal; break;
          case "Check": totalCheck = amtVal; break;
          case "Money Order": totalMO = amtVal; break;
          case "Credit Card": totalCC = amtVal; break;
        }
      }

      formData.append("cash", totalCash);
      formData.append("check", totalCheck);
      formData.append("moneyOrder", totalMO);
      formData.append("creditCard", totalCC);

      // Partial Payment?
      let partialActive = partialPaymentSwitch.checked;
      formData.append("partialPayment", partialActive ? "1" : "0");
      // Actual Amt Due
      let partialAmt = parseCurrencyString(partialPaymentInput.value);
      let actualDue  = partialActive
        ? partialAmt
        : (totalCash + totalCheck + totalMO + totalCC);
      formData.append("actualAmtDue", actualDue);

      // Add image if any
      if (receiptUpload.files.length > 0) {
        formData.append("receiptFile", receiptUpload.files[0]);
      }

      // 2) POST to save_payment.php
      fetch("save_payment.php", {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          console.log("DB Inserted. Payment ID = " + data.paymentId);
          // data.pdfUrl => open in new tab
          if (data.pdfUrl) {
            window.open(data.pdfUrl, "_blank");
          } else {
            alert("Saved, but no PDF URL returned!");
          }
        } else {
          alert("Error saving payment: " + data.error);
        }
      })
      .catch(err => {
        console.error(err);
        alert("Fetch error: " + err.message);
      });
    }

    // =============== Button Handlers ===============
    saveBtn.addEventListener("click", function() {
      // Save to localStorage (optional)
      saveLocally();
      // Save to DB + Generate PDF
      saveToPHPAndGeneratePDF();
      alert("Saved locally + MySQL + PDF generated!");
    });

    saveAndSendBtn.addEventListener("click", function() {
      saveLocally();
      saveToPHPAndGeneratePDF();
      // Then also send email (placeholder):
      sendEmail();
      alert("Saved locally + MySQL + PDF + Email triggered!");
    });

    // Extra hidden button handlers
    exportBtn.addEventListener("click", function() {
      alert("CSV export is placeholder!");
    });
    saveToPhpBtn.addEventListener("click", function() {
      saveToPHPAndGeneratePDF();
    });
    exportXlsBtn.addEventListener("click", function() {
      alert("Export XLS hidden placeholder!");
    });
    exportXlsxBtn.addEventListener("click", function() {
      alert("Export XLSX hidden placeholder!");
    });

    // Dark Mode
    darkModeSwitch.addEventListener("change", function() {
      document.body.classList.toggle("dark-mode", darkModeSwitch.checked);
    });
  </script>
</body>
</html>
