function togglePass(fieldId, btn) {
  const field = document.getElementById(fieldId);
  if (!field) return;
  const icon = btn.querySelector('i');
  if (field.type === 'password') {
    field.type = 'text';
    if (icon) icon.className = 'bi bi-eye-slash';
  } else {
    field.type = 'password';
    if (icon) icon.className = 'bi bi-eye';
  }
}
