<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin</title>
        {{-- favicon  --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png')}}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black/10 flex flex-col items-center justify-center h-screen gap-10">

            @if(isset($logoutMessage))
            <div class="text-green-600 mb-4">
                {{ $logoutMessage }}
            </div>
        @endif

        @if($errors->any())
            <div class="text-red-600 mb-4">
                {{ $errors->first() }}
            </div>
        @endif
        <div><img src="{{ asset('assets/img/gazpetrol.png')}}" alt="Foto" class="w-[184px] h-[52px]"></div>
        <div class="bg-white p-8 w-1/3 rounded-xl shadow-md  mb-[70px]">
            {{-- Login Form --}}
            <form id="loginForm" action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="usuario" class="block mb-2 text-gray-700">Usuario</label>
                    <input type="text" name="usuario" id="usuario" class="w-full border rounded p-2" required>
                </div>
                <div class="mb-4">
                    <label for="contraseña" class="block mb-2 text-gray-700">Contraseña</label>
                    <input type="password" name="contraseña" id="contraseña" class="w-full border rounded p-2" required>
                </div>
                <button type="submit" class="w-full bg-black text-white p-2 rounded hover:scale-105 transition-transform duration-300 ">Ingresar</button>
            </form>

            {{-- Password Reset Form --}}
            <form id="resetForm" action="{{ route('password.email') }}" method="POST" class="hidden">
                @csrf
                <div class="mb-4">
                    <label for="resetEmail" class="block text-gray-700">Email</label>
                    <input type="email" name="email" id="resetEmail" class="w-full border rounded p-2" required>
                </div>
                <button type="submit" class="w-full bg-black text-white p-2 rounded hover:scale-105 transition-transform duration-300">Enviar correo</button>
                <p class="mt-4 text-center text-sm hover:scale-105 transition-transform duration-300">
                    <a href="#" id="showLoginForm" class="">Volver al login</a>
                </p>
            </form>
        </div>

    <script>
        const showReset = document.getElementById('showResetForm');
        const showLogin = document.getElementById('showLoginForm');
        const loginForm = document.getElementById('loginForm');
        const resetForm = document.getElementById('resetForm');

        showReset.addEventListener('click', (e) => {
            e.preventDefault();
            loginForm.classList.add('hidden');
            resetForm.classList.remove('hidden');
        });

        showLogin.addEventListener('click', (e) => {
            e.preventDefault();
            resetForm.classList.add('hidden');
            loginForm.classList.remove('hidden');
        });
    </script>
</body>
</html>