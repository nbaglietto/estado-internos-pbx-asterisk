function actualizarIndicadores() {
    fetch("https://192.168.1.251/api/asterisk.php")
        .then(response => response.json())
        .then(data => {
            document.querySelectorAll('.extension-card').forEach(card => {
                const ext = card.dataset.extension;
                const estado = data[ext]; // buscar estado por número de extensión
                const indicator = card.querySelector('.status-indicator') || crearIndicador(card);
                
                if (estado) {
                    card.dataset.estado = estado;

                    if (estado.includes("Disponible")) {
                        indicator.className = 'status-indicator status-available';
                    } else if (estado.includes("No registrada") || estado.includes("UNAVAILABLE")) {
                        indicator.className = 'status-indicator status-offline';
                    } else {
                        indicator.className = 'status-indicator status-busy';
                    }
                }
            });
        })
        .catch(error => console.error("Error al obtener estados:", error));
}

