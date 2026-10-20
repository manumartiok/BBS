{{-- Scripts compartidos por el banner y los módulos generados con make:modulo --}}
<script>
    // Vista previa de una imagen al elegir el archivo
    function previsualizarImagen(input) {
        const contenedor = input.closest('.campo-archivo');
        const img = contenedor ? contenedor.querySelector('img.vista-previa') : null;
        const file = input.files[0];

        if (img && file) {
            img.src = URL.createObjectURL(file);
            img.classList.remove('hidden');
        }
    }

    // Listas repetibles: agrega una fila usando el <template> de la lista
    function agregarFila(idLista) {
        const contenedor = document.getElementById(idLista);
        const template = document.getElementById(idLista + '-template');
        const i = parseInt(contenedor.dataset.siguiente || '0');

        contenedor.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__i__', i));
        contenedor.dataset.siguiente = i + 1;
    }

    function quitarFila(boton) {
        boton.closest('.fila-lista').remove();
    }
</script>
