/**
 * SGPP-UNS - JavaScript de soporte interactivo
 * Universidad Nacional del Santa
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Toggle Sidebar en pantallas táctiles o móviles
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.querySelector('.app-sidebar');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
      sidebar.classList.toggle('show');
    });
  }

  // Cerrar sidebar al hacer click fuera en dispositivos móviles
  document.addEventListener('click', function (e) {
    if (sidebar && sidebar.classList.contains('show')) {
      if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
        sidebar.classList.remove('show');
      }
    }
  });

  // 2. Rellenado rápido de credenciales de demostración en login
  const demoButtons = document.querySelectorAll('.btn-demo-fill');
  demoButtons.forEach(btn => {
    btn.addEventListener('click', function () {
      const u = this.getAttribute('data-user');
      const p = this.getAttribute('data-pass');
      const userInput = document.getElementById('inputUsuario');
      const passInput = document.getElementById('inputPassword');
      if (userInput && passInput) {
        userInput.value = u;
        passInput.value = p;
      }
    });
  });

  // 3. Validación de tamaño de archivo en el cliente (prevención temprana, servidor valida 100%)
  const fileInputs = document.querySelectorAll('input[type="file"][data-max-size]');
  fileInputs.forEach(input => {
    input.addEventListener('change', function () {
      const maxMb = parseFloat(this.getAttribute('data-max-size') || 20);
      const maxBytes = maxMb * 1024 * 1024;
      if (this.files && this.files[0]) {
        if (this.files[0].size > maxBytes) {
          alert(`El archivo supera el tamaño máximo permitido de ${maxMb} MB. Por favor seleccione un archivo menor.`);
          this.value = '';
        }
      }
    });
  });

  // 4. Auto-ocultar alertas temporales después de 6 segundos
  const autoAlerts = document.querySelectorAll('.alert-dismissible:not(.alert-permanent)');
  autoAlerts.forEach(alertEl => {
    setTimeout(() => {
      try {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
        if (bsAlert) bsAlert.close();
      } catch (e) {}
    }, 6000);
  });
});
