<?php
$token = isset($_GET['token']) ? $_GET['token'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Password Reset</title>
  <link rel="stylesheet" href="css/password-reset.css">
  <style>
    .password-wrapper {
      position: relative;
      width: 100%;
      margin-bottom: 15px;
    }
    
    .password-input-container {
      position: relative;
      width: 100%;
    }
    
    .password-input-container input {
      width: 100%;
      padding-right: 15px;
    }
    
    .password-toggle-wrapper {
      display: flex;
      align-items: center;
      margin-top: 5px;
      font-size: 12px;
    }
    
    .toggle-checkbox {
      width: 15px;
      height: 15px;
      cursor: pointer;
      margin-right: 5px;
      accent-color: #3C50B2;
    }
    
    .toggle-label {
      cursor: pointer;
      user-select: none;
      color: #666;
      font-size: 12px;
    }

    .password-requirements {
      margin: 10px 0;
      padding: 10px;
      background-color: #f5f5f5;
      border-radius: 5px;
      font-size: 11px;
      display: none;
    }

    .password-requirements.show {
      display: block;
    }

    .requirement {
      margin: 4px 0;
      color: #666;
      transition: color 0.3s ease;
    }

    .requirement.met {
      color: #4CAF50;
    }

    .requirement::before {
      content: "✗ ";
      font-weight: bold;
      color: #f44336;
    }

    .requirement.met::before {
      content: "✓ ";
      color: #4CAF50;
    }

    .password-match-error {
      color: #f44336;
      font-size: 12px;
      margin-top: 5px;
      display: none;
    }

    .password-match-error.show {
      display: block;
    }

    .password-match-success {
      color: #4CAF50;
      font-size: 12px;
      margin-top: 5px;
      display: none;
    }

    .password-match-success.show {
      display: block;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>Password Reset</h2>
    <p>Enter your new password below.</p>

    <form method="post" action="process-password-reset.php" id="resetForm">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

      <label for="password">New Password</label>
      <div class="password-wrapper">
        <div class="password-input-container">
          <input type="password" name="password" id="password" placeholder="Enter new password" required>
        </div>
        <div class="password-toggle-wrapper">
          <input type="checkbox" id="toggle-password" class="toggle-checkbox">
          <label for="toggle-password" class="toggle-label">Show password</label>
        </div>
        <div class="password-requirements" id="passwordRequirements">
          <div class="requirement" id="req-length">At least 8 characters</div>
          <div class="requirement" id="req-lowercase">One lowercase letter</div>
          <div class="requirement" id="req-uppercase">One uppercase letter</div>
          <div class="requirement" id="req-number">One number</div>
          <div class="requirement" id="req-symbol">One special character (!@#$%^&*)</div>
        </div>
      </div>

      <label for="password_confirmation">Repeat Password</label>
      <div class="password-wrapper">
        <div class="password-input-container">
          <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Repeat new password" required>
        </div>
        <div class="password-toggle-wrapper">
          <input type="checkbox" id="toggle-confirm-password" class="toggle-checkbox">
          <label for="toggle-confirm-password" class="toggle-label">Show password</label>
        </div>
        <div class="password-match-error" id="passwordMatchError">Passwords do not match</div>
        <div class="password-match-success" id="passwordMatchSuccess">✓ Passwords match</div>
      </div>

      <input type="submit" value="Confirm">
    </form>

    <div class="links">
      <p><a href="login.php">Back to Login?</a></p>
      <p><a href="TICKETIX NI CLAIRE.php">Back to Home?</a></p>
    </div>
  </div>

  <script>
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const requirementsDiv = document.getElementById('passwordRequirements');
    const matchError = document.getElementById('passwordMatchError');
    const matchSuccess = document.getElementById('passwordMatchSuccess');
    const form = document.getElementById('resetForm');

    // Requirements elements
    const reqLength = document.getElementById('req-length');
    const reqLower = document.getElementById('req-lowercase');
    const reqUpper = document.getElementById('req-uppercase');
    const reqNumber = document.getElementById('req-number');
    const reqSymbol = document.getElementById('req-symbol');

    let allRequirementsMet = false;

    // Show requirements when password field is focused
    passwordInput.addEventListener('focus', function() {
      requirementsDiv.classList.add('show');
    });

    // Validate password requirements in real-time
    passwordInput.addEventListener('input', function() {
      const password = this.value;

      // Show requirements as soon as user starts typing
      if (password.length > 0) {
        requirementsDiv.classList.add('show');
      }

      // Check each requirement
      const hasLength = password.length >= 8;
      const hasLower = /[a-z]/.test(password);
      const hasUpper = /[A-Z]/.test(password);
      const hasNumber = /\d/.test(password);
      const hasSymbol = /[!@#$%^&*(),.?":{}|<>]/.test(password);

      // Update requirement classes
      reqLength.classList.toggle('met', hasLength);
      reqLower.classList.toggle('met', hasLower);
      reqUpper.classList.toggle('met', hasUpper);
      reqNumber.classList.toggle('met', hasNumber);
      reqSymbol.classList.toggle('met', hasSymbol);

      // Check if all requirements are met
      allRequirementsMet = hasLength && hasLower && hasUpper && hasNumber && hasSymbol;

      // Hide requirements if all are met
      if (allRequirementsMet) {
        requirementsDiv.classList.remove('show');
      }

      // Check password match
      checkPasswordMatch();
    });

    // Hide requirements when field is empty and loses focus
    passwordInput.addEventListener('blur', function() {
      if (passwordInput.value.length === 0) {
        requirementsDiv.classList.remove('show');
      }
    });

    // Check password match
    confirmInput.addEventListener('input', checkPasswordMatch);

    function checkPasswordMatch() {
      const password = passwordInput.value;
      const confirm = confirmInput.value;

      if (confirm.length > 0) {
        if (password === confirm) {
          matchError.classList.remove('show');
          matchSuccess.classList.add('show');
        } else {
          matchSuccess.classList.remove('show');
          matchError.classList.add('show');
        }
      } else {
        matchError.classList.remove('show');
        matchSuccess.classList.remove('show');
      }
    }

    // Form validation before submit
    form.addEventListener('submit', function(e) {
      const password = passwordInput.value;
      const confirm = confirmInput.value;

      if (!allRequirementsMet) {
        e.preventDefault();
        requirementsDiv.classList.add('show');
        alert('Please ensure your password meets all requirements.');
        return false;
      }

      if (password !== confirm) {
        e.preventDefault();
        alert('Passwords do not match.');
        return false;
      }
    });

    // Password visibility toggle - Checkbox version
    (function() {
      // Toggle for new password
      const togglePassword = document.getElementById('toggle-password');
      
      if (togglePassword && passwordInput) {
        togglePassword.addEventListener('change', function() {
          passwordInput.type = this.checked ? 'text' : 'password';
        });
      }

      // Toggle for confirm password
      const toggleConfirmPassword = document.getElementById('toggle-confirm-password');
      
      if (toggleConfirmPassword && confirmInput) {
        toggleConfirmPassword.addEventListener('change', function() {
          confirmInput.type = this.checked ? 'text' : 'password';
        });
      }
    })();
  </script>

</body>
</html>