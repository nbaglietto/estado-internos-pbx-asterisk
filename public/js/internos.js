document.addEventListener('DOMContentLoaded', () => {
  fetch('internos.php')
    .then(res => res.json())
    .then(data => {
      const container = document.getElementById('tarjetas');
      container.innerHTML = '';

      data.forEach(areaData => {
        areaData.internos.forEach(interno => {
          const tarjeta = document.createElement('div');
          tarjeta.className = 'tarjeta';

          // Elegir color del led
          const ledColor = interno.estado.includes('🟢') ? 'estado-verde' : 'estado-rojo';

          tarjeta.innerHTML = `
            <h3>${areaData.area}</h3>
            <p><strong>${interno.nombre}</strong></p>
            <p>Extensión: ${interno.extension}</p>
            <p>
              Estado: ${interno.estado}
              <span class="estado-led ${ledColor}"></span>
            </p>
          `;

          container.appendChild(tarjeta);
        });
      });
    })
    .catch(error => {
      console.error('Error cargando internos:', error);
    });
});
