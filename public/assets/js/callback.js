if (location.hash && /(?:invite|recovery)_token=/.test(location.hash) && location.pathname !== '/admin/login.php') {
 location.replace('/admin/login.php' + location.hash);
}
