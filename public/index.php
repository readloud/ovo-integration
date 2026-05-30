<?php
// Simple UI for examples
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>OVOID UI</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container">
    <h1>OVOID UI</h1>

    <section>
      <h2>Login (2FA)</h2>
      <form id="loginForm">
        <input name="action" type="hidden" value="login">
        <label>Phone: <input name="phone" required></label>
        <label>Device ID (optional): <input name="device_id"></label>
        <button type="submit">Send OTP</button>
      </form>

      <form id="verifyForm">
        <input name="action" type="hidden" value="verify">
        <label>Ref ID: <input name="refId" required></label>
        <label>OTP: <input name="otp" required></label>
        <label>Phone: <input name="phone2" required></label>
        <label>Device ID: <input name="device_id2" required></label>
        <button type="submit">Verify</button>
      </form>
    </section>

    <section>
      <h2>Actions</h2>
      <div class="actions">
        <button data-action="profile">Get Profile</button>
        <button data-action="balance">Get Balance</button>
        <button data-action="transactions">Transactions</button>
        <button data-action="notifications">Notifications</button>
        <button data-action="transfer-form">Transfer (show)</button>
      </div>

      <form id="transferForm" style="display:none">
        <input name="action" type="hidden" value="transfer">
        <label>To phone: <input name="to" required></label>
        <label>Amount: <input name="amount" type="number" required></label>
        <label>Message: <input name="message"></label>
        <button type="submit">Send Transfer</button>
      </form>
    </section>

    <section>
      <h2>Output</h2>
      <pre id="output"></pre>
    </section>
  </div>

  <script src="assets/app.js"></script>
</body>
</html>
