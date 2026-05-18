<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('registos.index')" :active="request()->routeIs('registos.index')">
                        Registo de Horas
                    </x-nav-link>

                    <x-nav-link :href="route('horario.index')" :active="request()->routeIs('horario.*')">
                        Horário
                    </x-nav-link>

                    @if (Auth::user()->role === 'admin')
                        <x-nav-link :href="route('registos.admin')" :active="request()->routeIs('registos.admin')">
                            Painel Admin
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Notificações -->
            <div class="hidden sm:flex sm:items-center sm:ml-6" x-data="{ aberto: false }" @click.outside="aberto = false">
                @php $naoLidas = Auth::user()->unreadNotifications->count(); @endphp
                <button @click="aberto = !aberto" class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if ($naoLidas > 0)
                        <span class="absolute top-1 right-1 inline-flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-500 rounded-full">
                            {{ $naoLidas > 9 ? '9+' : $naoLidas }}
                        </span>
                    @endif
                </button>

                <div x-show="aberto" x-transition
                    class="absolute right-16 top-14 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50 overflow-hidden">

                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                        <span class="text-sm font-semibold text-gray-800">Notificações</span>
                        @if ($naoLidas > 0)
                            <form method="POST" action="{{ route('notificacoes.todas-lidas') }}">
                                @csrf
                                <button type="submit" class="text-xs text-indigo-600 hover:underline">Marcar todas como lidas</button>
                            </form>
                        @endif
                    </div>

                    @php $notificacoes = Auth::user()->notifications()->latest()->limit(10)->get(); @endphp

                    @if ($notificacoes->isEmpty())
                        <p class="px-4 py-6 text-sm text-gray-400 text-center">Sem notificações.</p>
                    @else
                        <ul class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
                            @foreach ($notificacoes as $n)
                                @php
                                    $icones = ['atribuida' => '📋', 'estado' => '⚙️', 'comentario' => '💬', 'ficheiro' => '📎'];
                                    $icone  = $icones[$n->data['tipo'] ?? ''] ?? '🔔';
                                @endphp
                                <li class="{{ $n->read_at ? 'bg-white' : 'bg-indigo-50' }}">
                                    <form method="POST" action="{{ route('notificacoes.lida', $n->id) }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-3 hover:bg-gray-50 transition flex items-start gap-3">
                                            <span class="text-lg shrink-0 mt-0.5">{{ $icone }}</span>
                                            <div class="min-w-0">
                                                <p class="text-xs text-gray-700 leading-snug">{{ $n->data['mensagem'] }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
                                            </div>
                                            @if (!$n->read_at)
                                                <span class="shrink-0 w-2 h-2 bg-indigo-500 rounded-full mt-1.5"></span>
                                            @endif
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('registos.index')" :active="request()->routeIs('registos.index')">
                Registo de Horas
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('horario.index')" :active="request()->routeIs('horario.*')">
                Horário
            </x-responsive-nav-link>
            
              <x-responsive-nav-link :href="route('horario.index')" :active="request()->routeIs('tarefas.*')">
                Tarefas
            </x-responsive-nav-link>

            @if (Auth::user()->role == 'admin')
                <x-responsive-nav-link :href="route('registos.admin')" :active="request()->routeIs('registos.admin')">
                    Painel Admin
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
