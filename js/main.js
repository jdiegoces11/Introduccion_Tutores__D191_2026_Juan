// =============================================
// MODAL — ABRIR Y CERRAR
// =============================================

/**
 * Abre el modal de agendar con los datos del tutor seleccionado.
 * @param {string} name     - Nombre del tutor
 * @param {string} dept     - Departamento / semestre
 * @param {string} initials - Iniciales para el avatar
 */
function openModal(name, dept, initials) {
  document.getElementById('modalName').textContent = name;
  document.getElementById('modalDept').textContent = dept;
  document.getElementById('modalAvatar').textContent = initials;

  // Resetear slots de hora al abrir
  resetTimeSlots();

  // Mostrar el modal
  document.getElementById('modalOverlay').classList.add('open');
}

/**
 * Cierra el modal.
 */
function closeModal() {
  document.getElementById('modalOverlay').classList.remove('open');
}

// Cerrar modal haciendo clic fuera de él
document.getElementById('modalOverlay').addEventListener('click', function (e) {
  if (e.target === this) closeModal();
});

// Cerrar modal con tecla Escape
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') closeModal();
});


// =============================================
// SLOTS DE HORA
// =============================================

/**
 * Selecciona un slot de hora (si no está ocupado).
 * @param {HTMLElement} el - El elemento slot clickeado
 */
function selectTime(el) {
  if (el.classList.contains('occupied')) return;

  // Quitar selección anterior
  document.querySelectorAll('.time-slot').forEach(function (slot) {
    slot.classList.remove('selected');
  });

  // Seleccionar el clickeado
  el.classList.add('selected');
}

/**
 * Resetea todos los slots al estado inicial.
 */
function resetTimeSlots() {
  document.querySelectorAll('.time-slot').forEach(function (slot) {
    slot.classList.remove('selected');
  });
}


// =============================================
// CONFIRMAR RESERVA
// =============================================

/**
 * Simula la confirmación de la tutoría y muestra un toast.
 */
function confirmBooking() {
  const materia = document.querySelector('.modal-body .form-control').value;
  const horaSeleccionada = document.querySelector('.time-slot.selected');

  // Validación básica
  if (materia === 'Selecciona la materia…') {
    alert('Por favor selecciona una materia.');
    return;
  }

  if (!horaSeleccionada) {
    alert('Por favor selecciona una hora.');
    return;
  }

  // Cerrar modal y mostrar toast
  closeModal();
  showToast('✅ ¡Tutoría agendada correctamente!');
}


// =============================================
// TOAST NOTIFICACIÓN
// =============================================

/**
 * Muestra un mensaje toast temporal.
 * @param {string} mensaje - Texto a mostrar
 */
function showToast(mensaje) {
  const toast = document.getElementById('toast');
  toast.textContent = mensaje;
  toast.classList.add('show');

  setTimeout(function () {
    toast.classList.remove('show');
  }, 3500);
}


// =============================================
// CHIPS DE FILTRO (toggle activo/inactivo)
// =============================================

document.querySelectorAll('.chip').forEach(function (chip) {
  chip.addEventListener('click', function () {
    this.classList.toggle('active');
  });
});
