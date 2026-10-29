(() => {
 const hash = new URLSearchParams(location.hash.slice(1));
 const invite = hash.get('invite_token'), recovery = hash.get('recovery_token');
 const token = invite || recovery;
 if (!token) return;
 history.replaceState(null, '', '/admin/login.php');
 document.getElementById('auth-token').value = token;
 document.getElementById('auth-action').value = invite ? 'accept-invite' : 'recover';
 const email = document.querySelector('#auth-email input'); email.required = false;
 document.getElementById('auth-email').hidden = true;
 document.getElementById('auth-password').hidden = false;
 const password = document.getElementById('password'); password.required = true; password.minLength = 10; password.autocomplete = 'new-password';
 document.getElementById('auth-submit').textContent = 'Set password';
})();
