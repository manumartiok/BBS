@php

@endphp
<aside id="sidebar" class="w-64 bg-gray-800 text-white min-h-screen p-4 sidebar-transition overflow-hidden">
  <div>
    <div class="flex flex-col items-center mb-4">
     <a href="{{route('adm.dashboard')}}"><img src="" alt="Logo" class="h-[40px] mb-[10px]"></a> 
      <h2 class="text-xl font-bold ml-2">Administrador</h2>
    </div>
  </div>
  <hr class="mx-3 my-4">
  
  <!-- Web Links -->
  <div class="mb-6">
    <ul>
      <li class="group">
        <button class="flex justify-between items-center w-full py-2 px-3 hover:bg-gray-700 rounded transition focus:outline-none" onclick="toggleSubMenu(this)">
          <span><i class="fas fa-home mr-2"></i>Home</span>
          <i class="fas fa-chevron-down transition-transform duration-300 group-[.open]:rotate-180"></i>
        </button>
        <ul class="submenu hidden pl-8 mt-1 space-y-1">
          {{-- <li><a href="{{route('adm.home-ban')}}" class="block py-1 px-2 hover:bg-gray-600 rounded">Banner</a></li>
          <li><a href="{{route('adm.home')}}" class="block py-1 px-2 hover:bg-gray-600 rounded">Contenido</a></li> --}}
        </ul>
      </li>
      <li class="group">
        <button class="flex justify-between items-center w-full py-2 px-3 hover:bg-gray-700 rounded transition focus:outline-none" onclick="toggleSubMenu(this)">
          <span><i class="fas fa-info-circle mr-2"></i>Nosotros</span>
          <i class="fas fa-chevron-down transition-transform duration-300 group-[.open]:rotate-180"></i>
        </button>
        <ul class="submenu hidden pl-8 mt-1 space-y-1">
          <li><a href="{{route('adm.banner', 'nosotros')}}" class="block py-1 px-2 hover:bg-gray-600 rounded">Banner</a></li>
          <li><a href="{{ route('adm.nosotros') }}" class="block py-1 px-2 hover:bg-gray-600 rounded">Contenido</a></li>
        </ul>
      </li>
      <li class="group">
        <button class="flex justify-between items-center w-full py-2 px-3 hover:bg-gray-700 rounded transition focus:outline-none" onclick="toggleSubMenu(this)">
          <span><i class="fas fa-box mr-2"></i>Productos</span>
          <i class="fas fa-chevron-down transition-transform duration-300 group-[.open]:rotate-180"></i>
        </button>
        <ul class="submenu hidden pl-8 mt-1 space-y-1">
          <li><a href="{{route('adm.banner', 'productos')}}" class="block py-1 px-2 hover:bg-gray-600 rounded">Banner</a></li>
          <li><a href="{{route('adm.marca')}}" class="block py-1 px-2 hover:bg-gray-600 rounded">Marcas</a></li>
          <li><a href="{{ route('adm.producto') }}" class="block py-1 px-2 hover:bg-gray-600 rounded">Productos</a></li>
        </ul>
      </li>
      <li class="group">
        <button class="flex justify-between items-center w-full py-2 px-3 hover:bg-gray-700 rounded transition focus:outline-none" onclick="toggleSubMenu(this)">
          <span><i class="fas fa-building mr-2"></i>Novedades</span>
          <i class="fas fa-chevron-down transition-transform duration-300 group-[.open]:rotate-180"></i>
        </button>
        <ul class="submenu hidden pl-8 mt-1 space-y-1">
          <li><a href="{{route('adm.banner', 'novedades')}}" class="block py-1 px-2 hover:bg-gray-600 rounded">Banner</a></li>
          <li><a href="{{route('adm.novedade')}}" class="block py-1 px-2 hover:bg-gray-600 rounded">Contenido</a></li>
        </ul>
      </li>
      <li class="group">
        <button class="flex justify-between items-center w-full py-2 px-3 hover:bg-gray-700 rounded transition focus:outline-none" onclick="toggleSubMenu(this)">
          <span><i class="fas fa-envelope mr-2"></i>Contacto</span>
          <i class="fas fa-chevron-down transition-transform duration-300 group-[.open]:rotate-180"></i>
        </button>
        <ul class="submenu hidden pl-8 mt-1 space-y-1">
          <li><a href="{{route('adm.banner', 'contacto')}}" class="block py-1 px-2 hover:bg-gray-600 rounded">Banner</a></li>
          <li><a href="{{route('adm.contacto')}}" class="block py-1 px-2 hover:bg-gray-600 rounded">Contenido</a></li>
        </ul>
      </li>
      {{-- make:modulo: los módulos nuevos se agregan arriba de esta línea (no borrar) --}}
      <li class="group">
        <button class="flex justify-between items-center w-full py-2 px-3 hover:bg-gray-700 rounded transition focus:outline-none" onclick="toggleSubMenu(this)">
          <span><i class="fas fa-dollar mr-2"></i>Solicitar presupuesto</span>
          <i class="fas fa-chevron-down transition-transform duration-300 group-[.open]:rotate-180"></i>
        </button>
        <ul class="submenu hidden pl-8 mt-1 space-y-1">
          <li><a href="" class="block py-1 px-2 hover:bg-gray-600 rounded">Banner</a></li>
        </ul>
      </li>
      <li class="group">
        <button class="flex justify-between items-center w-full py-2 px-3 hover:bg-gray-700 rounded transition focus:outline-none" onclick="toggleSubMenu(this)">
          <span><i class="fas fa-lock mr-2"></i>Zona privada</span>
          <i class="fas fa-chevron-down transition-transform duration-300 group-[.open]:rotate-180"></i>
        </button>
        <ul class="submenu hidden pl-8 mt-1 space-y-1">
         <li><a href="{{route('adm.web-usuarios')}}" class="block py-1 px-2 hover:bg-gray-600 rounded">Usuarios</a></li>
          {{-- <li><a href="{{route('adm.carrito')}}" class="block py-1 px-2 hover:bg-gray-600 rounded">Carrito</a></li> --}}
          <li><a href="{{route('adm.info')}}" class="block py-1 px-2 hover:bg-gray-600 rounded">Info. de pagos</a></li>
         <li><a href="{{route('adm.lista')}}" class="block py-1 px-2 hover:bg-gray-600 rounded">Lista de precios</a></li>
         <li><a href="{{route('adm.pedidos-historico')}}" class="block py-1 px-2 hover:bg-gray-600 rounded">Histórico de pedidos</a></li>
        </ul>
      </li>
    </ul>
  </div>

  <hr class="mx-3 my-4">

  <!-- Configuración -->
  <div>
    <ul>
      <li><a href="{{route('adm.newsletter')}}" class="block py-2 px-3 hover:bg-gray-700 rounded transition"><i class="fa-solid fa-newspaper mr-2"></i>Newsletter</a></li>
      <li><a href="{{route('adm.logo')}}" class="block py-2 px-3 hover:bg-gray-700 rounded transition"><i class="fas fa-image mr-2"></i>Logos</a></li>
      <li><a href="{{route('adm.usuarios')}}" class="block py-2 px-3 hover:bg-gray-700 rounded transition"><i class="fas fa-users mr-2"></i>Usuarios</a></li>
      <li><a href="{{route('adm.metadatos')}}" class="block py-2 px-3 hover:bg-gray-700 rounded transition"><i class="fas fa-cog mr-2"></i>Metadatos</a></li>
    </ul>
  </div>

</aside>


  <script>
  function toggleSubMenu(button) {
    const li = button.parentElement;
    const submenu = li.querySelector('.submenu');
    submenu.classList.toggle('hidden');
    li.classList.toggle('open');
  }
</script>