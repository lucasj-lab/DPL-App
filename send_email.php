<?php  fetch("save_payment.php", { ... })
  .then(r => r.json())
  .then(saveResult => {
    if (saveResult.success) {
      // Next, call send_email.php with that file path
      return fetch("send_email.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          recipientEmail: "someone@example.com",
          subject: "Payment PDF",
          message: "See attached receipt!",
          filePath: saveResult.pdfFilepath  // or whatever field
        })
      });
    } else {
      throw new Error("Save payment failed: " + saveResult.error);
    }
  })
  .then(r => r.json())
  .then(emailResult => {
    if (emailResult.success) {
      alert("Email sent successfully!");
    } else {
      alert("Error sending email: " + emailResult.error);
    }
  })
  .catch(err => alert("Error: " + err.message));
 
 
 
 ?>