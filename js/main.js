// Forces Academy LMS — shared front-end behaviour

document.addEventListener('DOMContentLoaded', function () {

  // Show/hide password toggles
  document.querySelectorAll('.toggle-pass').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(btn.dataset.target);
      if (!input) return;
      var isHidden = input.type === 'password';
      input.type = isHidden ? 'text' : 'password';
      btn.textContent = isHidden ? 'Hide' : 'Show';
    });
  });

  // Live "passwords match" hint on the registration form
  var pass = document.getElementById('password');
  var confirm = document.getElementById('confirm_password');
  var matchHint = document.getElementById('match-hint');

  if (pass && confirm && matchHint) {
    var checkMatch = function () {
      if (!confirm.value) {
        matchHint.textContent = '';
        matchHint.className = 'hint';
        return;
      }
      if (pass.value === confirm.value) {
        matchHint.textContent = 'Passwords match.';
        matchHint.className = 'hint match';
      } else {
        matchHint.textContent = 'Passwords do not match yet.';
        matchHint.className = 'hint error';
      }
    };
    pass.addEventListener('input', checkMatch);
    confirm.addEventListener('input', checkMatch);
  }

  // Disable submit button after click to avoid duplicate submissions
  document.querySelectorAll('form[data-guard]').forEach(function (form) {
    form.addEventListener('submit', function () {
      var btn = form.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.textContent = btn.dataset.loadingText || 'Please wait...';
      }
    });
  });

});
