/**
 * EduPredict - Student Performance Prediction System
 * Capstone Prototype Interactive Logic
 */

document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('login-form');

  if (!loginForm) {
    return;
  }

  // DOM Elements
  const usernameInput = document.getElementById('username');
  const passwordInput = document.getElementById('password');
  const rememberMeCheckbox = document.getElementById('remember-me');
  const togglePasswordBtn = document.getElementById('toggle-password');
  const eyeIcon = document.getElementById('eye-icon');
  const loginButton = document.getElementById('login-button');
  const btnText = loginButton.querySelector('.btn-text');
  const btnSpinner = loginButton.querySelector('.btn-spinner');
  const arrowIcon = loginButton.querySelector('.arrow-icon');
  const statusBanner = document.getElementById('status-banner');
  const statusMessage = document.getElementById('status-message');
  const usernameError = document.getElementById('username-error');
  const passwordError = document.getElementById('password-error');
  const forgotPasswordLink = document.getElementById('forgot-password-link');
  const credPills = document.querySelectorAll('.cred-pill');

  // Eye SVG paths for toggle
  const eyeOpenSvg = `
    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
    <circle cx="12" cy="12" r="3"/>
  `;
  const eyeClosedSvg = `
    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
    <line x1="1" y1="1" x2="23" y2="23"/>
  `;

  // 1. Load remembered username if exists
  try {
    const savedUser = localStorage.getItem('edupredict_remembered_user');
    if (savedUser) {
      usernameInput.value = savedUser;
      rememberMeCheckbox.checked = true;
    }
  } catch (e) {
    console.warn('LocalStorage not accessible:', e);
  }

  // 2. Password Visibility Toggle
  togglePasswordBtn.addEventListener('click', () => {
    const isPassword = passwordInput.getAttribute('type') === 'password';
    if (isPassword) {
      passwordInput.setAttribute('type', 'text');
      eyeIcon.innerHTML = eyeClosedSvg;
      togglePasswordBtn.setAttribute('title', 'Hide password');
      togglePasswordBtn.setAttribute('aria-label', 'Hide password');
    } else {
      passwordInput.setAttribute('type', 'password');
      eyeIcon.innerHTML = eyeOpenSvg;
      togglePasswordBtn.setAttribute('title', 'Show password');
      togglePasswordBtn.setAttribute('aria-label', 'Show password');
    }
    passwordInput.focus();
  });

  // 3. Quick Credential Pills for Capstone Evaluation
  credPills.forEach(pill => {
    pill.addEventListener('click', () => {
      const user = pill.getAttribute('data-user');
      const pass = pill.getAttribute('data-pass');
      
      usernameInput.value = user;
      passwordInput.value = pass;
      
      clearErrors();
      
      // Visual feedback highlight on input fields
      usernameInput.style.backgroundColor = '#EBF2FE';
      passwordInput.style.backgroundColor = '#EBF2FE';
      setTimeout(() => {
        usernameInput.style.backgroundColor = '';
        passwordInput.style.backgroundColor = '';
      }, 400);

      showStatus(`Loaded credentials for ${user}`, 'info');
    });
  });

  // 4. Input clear errors on typing
  usernameInput.addEventListener('input', () => {
    if (usernameInput.classList.contains('invalid')) {
      usernameInput.classList.remove('invalid');
      usernameError.textContent = '';
    }
  });

  passwordInput.addEventListener('input', () => {
    if (passwordInput.classList.contains('invalid')) {
      passwordInput.classList.remove('invalid');
      passwordError.textContent = '';
    }
  });

  // 5. Forgot Password Handler (Capstone Guidance)
  forgotPasswordLink.addEventListener('click', (e) => {
    e.preventDefault();
    showStatus('Capstone Prototype: Password recovery is managed by the Faculty System Admin.', 'error');
  });

  // 6. Form Submission & Capstone Authentication Simulation
  loginForm.addEventListener('submit', (e) => {
    e.preventDefault();
    clearErrors();

    const username = usernameInput.value.trim();
    const password = passwordInput.value.trim();

    let hasError = false;

    if (!username) {
      usernameInput.classList.add('invalid');
      usernameError.textContent = 'Please enter your username or faculty ID.';
      hasError = true;
    }

    if (!password) {
      passwordInput.classList.add('invalid');
      passwordError.textContent = 'Please enter your password.';
      hasError = true;
    }

    if (hasError) {
      showStatus('Please provide both username and password to proceed.', 'error');
      return;
    }

    // Handle "Remember me"
    try {
      if (rememberMeCheckbox.checked) {
        localStorage.setItem('edupredict_remembered_user', username);
      } else {
        localStorage.removeItem('edupredict_remembered_user');
      }
    } catch (e) {
      // Ignored if private browsing / cookies disabled
    }

    // Set Loading State
    setLoading(true);
    showStatus('Connecting to EduPredict ML Classification Engine...', 'info');

    // Simulate ML Engine & Capstone Verification
    setTimeout(() => {
      setLoading(false);
      showStatus('Authentication successful! Redirecting to Dashboard...', 'success');
      
      // Pulse animation on the card
      const card = document.querySelector('.login-card');
      card.style.transform = 'scale(1.01)';
      setTimeout(() => {
        card.style.transform = '';
      }, 300);

      // Seamless redirect to the Dashboard screen
      setTimeout(() => {
        window.location.href = 'dashboard.html';
      }, 700);

    }, 1000);
  });

  // Helper Functions
  function setLoading(isLoading) {
    if (isLoading) {
      loginButton.disabled = true;
      btnSpinner.classList.remove('hidden');
      arrowIcon.style.display = 'none';
      btnText.textContent = 'Signing in...';
    } else {
      loginButton.disabled = false;
      btnSpinner.classList.add('hidden');
      arrowIcon.style.display = '';
      btnText.textContent = 'Login to EduPredict';
    }
  }

  function showStatus(message, type) {
    statusBanner.classList.remove('hidden', 'success', 'error');
    statusMessage.textContent = message;

    if (type === 'success') {
      statusBanner.classList.add('success');
    } else if (type === 'error') {
      statusBanner.classList.add('error');
    } else {
      statusBanner.classList.add('success'); // default soft styled
    }
  }

  function clearErrors() {
    usernameInput.classList.remove('invalid');
    passwordInput.classList.remove('invalid');
    usernameError.textContent = '';
    passwordError.textContent = '';
  }
});
