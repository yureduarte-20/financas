<x-app-layout>
    <x-sidebar>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-dark-surface overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-dark-text dark:text-gray-100 flex items-center justify-between">
                    <div>
                        Você está logado, {{ auth()->user()->name }}!
                    </div>
                    <!-- Exemplo de como usar o botão de logout -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <x-button color="danger" type="submit">Sair</x-button>
                    </form>
                </div>
            </div>
        </div>
    </x-sidebar>
</x-app-layout>